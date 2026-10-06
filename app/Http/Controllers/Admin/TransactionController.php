<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\Notification;
use App\Jobs\SendBookIssuedEmail;
use App\Jobs\SendBookReturnedEmail;
use App\Services\FineCalculator;
use App\Services\PhysicalBookCopyService;
use App\Services\NotificationService;
use App\Services\StudentIssuePrivilegeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-admin');
        
        // Get fine settings for display
        $fineCalculator = new FineCalculator();
        $fineSettings = $fineCalculator->getSettings();
        
        return view('Admin.Transactions', [
            'fineSettings' => $fineSettings
        ]);
    }

    /**
     * Get students data for search (AJAX)
     */
    public function getStudents(Request $request)
    {
        Gate::authorize('access-admin');
        
        $query = $request->input('query', '');
        $fineSetting = FineSetting::resolveActive();
        $defaultMaxBooks = $fineSetting->max_books_per_student;
        
        $students = Student::selectRaw('DISTINCT students.*')
            ->with('user', 'department', 'privileges')
            ->withCount([
                'issuedBooks as active_issued_books_count' => function ($q) {
                    $q->whereNull('return_date');
                }
            ])
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            })
            ->when($query, function($q) use ($query) {
                return $q->where(function ($studentQuery) use ($query) {
                    $studentQuery
                        ->where('students.id', 'like', "%$query%")
                        ->orWhere('students.roll_no', 'like', "%$query%")
                        ->orWhereHas('user', function($subQuery) use ($query) {
                            $subQuery->where('name', 'like', "%$query%")
                                ->orWhere('email', 'like', "%$query%");
                        })
                        ->orWhereHas('department', function($subQuery) use ($query) {
                            $subQuery->where('name', 'like', "%$query%");
                        });
                    });
            })
            ->limit(15)
            ->get()
            ->unique('id')
            ->values()
            ->map(function($student) use ($defaultMaxBooks) {
                $issuedCount = $student->active_issued_books_count ?? 0;
                $privileges = $student->privileges;
                $maxBooks = $privileges->max_books ?? $defaultMaxBooks;
                $canIssueMore = max(0, $maxBooks - $issuedCount);
                $hasPrivilegeOverride = $privileges !== null && (
                    $privileges->max_books !== null
                    || $privileges->issue_duration_days !== null
                    || $privileges->per_day_fine !== null
                    || $privileges->borrowing_allowed === false
                );
                
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'studentId' => $student->roll_no ?? $student->id,
                    'roll_no' => $student->roll_no,
                    'email' => $student->user->email,
                    'department' => $student->department->name ?? 'N/A',
                    'issued' => $issuedCount,
                    'maxBooks' => $maxBooks,
                    'borrowingAllowed' => $privileges->borrowing_allowed ?? true,
                    'hasPrivilegeOverride' => $hasPrivilegeOverride,
                    'canIssueMore' => $canIssueMore,
                ];
            });
        
        return response()->json($students);
    }

    /**
     * Get available books for issue (AJAX)
     */
    public function getAvailableBooks(Request $request)
    {
        try {
            Gate::authorize('access-admin');
            
            $query = $request->input('query', '');
            $studentId = $request->input('studentId');
            
            if (!$studentId) {
                return response()->json([], 400);
            }
            
            // Get already issued books to exclude
            $issuedBookIds = IssuedBook::where('student_id', $studentId)
                ->whereNull('return_date')
                ->pluck('book_id')
                ->toArray();
            
            // Build query
            $booksQuery = Book::with('category')
                ->whereNotIn('id', $issuedBookIds);
            
            // Add search filter if provided
            if ($query) {
                $booksQuery->where(function($q) use ($query) {
                    $q->where('title', 'like', "%$query%")
                        ->orWhere('publisher', 'like', "%$query%")
                        ->orWhere('author', 'like', "%$query%");
                });
            }
            
            $books = $booksQuery->with(['copies' => function ($copyQuery) {
                    $copyQuery->orderBy('accession_number');
                }])->limit(15)
                ->get()
                ->map(function($book) {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'isbn' => $book->isbn,
                        'author' => $book->author ?? 'Unknown',
                        'publisher' => $book->publisher ?? 'N/A',
                        'category' => $book->category->name ?? 'N/A',
                        'total_copies' => (int) $book->total_copies,
                        'available_copies' => $book->copies->filter(fn (BookCopy $copy) =>
                            $copy->status === 'available'
                            && $copy->book_type !== 'reference'
                            && $copy->condition !== 'damaged'
                        )->count(),
                        'copies' => $book->copies->map(fn (BookCopy $copy) => [
                            'id' => $copy->id,
                            'book_id' => $copy->book_id,
                            'accession_number' => $copy->accession_number,
                            'book_type' => $copy->book_type,
                            'status' => $copy->status,
                            'condition' => $copy->condition,
                            'shelf_location' => $copy->shelf_location,
                            'book' => [
                                'id' => $book->id,
                                'title' => $book->title,
                                'author' => $book->author,
                                'isbn' => $book->isbn,
                                'category' => $book->category->name ?? 'N/A',
                            ],
                        ])->values(),
                    ];
                });
            
            return response()->json($books);
        } catch (\Exception $e) {
            \Log::error('Error fetching available books: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get issued books for return (AJAX)
     */
    public function getIssuedBooks(Request $request)
    {
        Gate::authorize('access-admin');
        
        $studentId = $request->input('studentId');
        $countOnly = $request->boolean('countOnly');
        
        $query = IssuedBook::where('student_id', $studentId)
            ->whereNull('return_date');
        
        // If only count is needed, return immediately (lightweight query)
        if ($countOnly) {
            return response()->json(['count' => $query->count()]);
        }
        
        $issuedBooks = $query->with(['book', 'bookCopy'])
            ->get()
            ->map(function($issued) {
                $overdueDays = max(0, Carbon::parse($issued->due_date)->diffInDays(Carbon::now()));
                
                return [
                    'id' => $issued->id,
                    'bookId' => $issued->book_id,
                    'accessionNumber' => $issued->bookCopy?->accession_number,
                    'bookTitle' => $issued->book->title,
                    'title' => $issued->book->title,
                    'author' => $issued->book->author ?? 'Unknown',
                    'isbn' => $issued->book->isbn,
                    'issueDate' => $issued->issue_date->format('Y-m-d'),
                    'dueDate' => $issued->due_date->format('Y-m-d'),
                    'overdueDays' => $overdueDays,
                    'isOverdue' => $overdueDays > 0,
                ];
            });
        
        return response()->json($issuedBooks);
    }

    /**
     * Issue books to a student
     */
    public function issueBooks(
        Request $request,
        PhysicalBookCopyService $copyService,
        NotificationService $notifications,
        StudentIssuePrivilegeService $privilegeService
    )
    {
        Gate::authorize('access-admin');

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'book_copy_ids' => 'required_without:book_ids|array|min:1|max:50',
            'book_copy_ids.*' => 'integer|distinct|exists:book_copies,id',
            'book_ids' => 'required_without:book_copy_ids|array|min:1|max:50',
            'book_ids.*' => 'integer|distinct|exists:books,id',
        ]);

        try {
            $copyIds = array_values(array_map('intval', $request->input('book_copy_ids', [])));
            $legacyBookIds = array_values(array_map('intval', $request->input('book_ids', [])));
            $requestedCount = $copyIds !== [] ? count($copyIds) : count($legacyBookIds);

            $issuedBooks = DB::transaction(function () use ($copyIds, $legacyBookIds, $requestedCount, $request, $copyService, $notifications, $privilegeService): array {
                $student = Student::query()
                    ->lockForUpdate()
                    ->with(['user', 'department', 'privileges'])
                    ->findOrFail($request->integer('student_id'));
                $issueCheck = $privilegeService->canIssue($student, $requestedCount);

                if (! $issueCheck['allowed']) {
                    abort(response()->json([
                        'success' => false,
                        'message' => $issueCheck['message'],
                        'data' => ['privileges' => $issueCheck['privileges']],
                    ], 422));
                }

                $issueDate = Carbon::parse($issueCheck['privileges']['issue_date']);
                $dueDate = Carbon::parse($issueCheck['privileges']['due_date']);
                $targets = [];

                if ($copyIds !== []) {
                    $copies = BookCopy::query()
                        ->whereIn('id', $copyIds)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    if ($copies->count() !== count($copyIds)) {
                        abort(response()->json(['success' => false, 'message' => 'One or more selected book copies were not found.'], 422));
                    }

                    foreach ($copyIds as $copyId) {
                        $targets[] = ['copy' => $copies->get($copyId), 'book' => null];
                    }
                } else {
                    foreach ($legacyBookIds as $bookId) {
                        $book = Book::query()->lockForUpdate()->findOrFail($bookId);
                        $copy = BookCopy::query()
                            ->where('book_id', $book->id)
                            ->where('status', 'available')
                            ->where('book_type', '!=', 'reference')
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->first();

                        if ($copy) {
                            $targets[] = ['copy' => $copy, 'book' => null];
                        } elseif (BookCopy::query()->where('book_id', $book->id)->exists()) {
                            abort(response()->json(['success' => false, 'message' => "The book '{$book->title}' is not available."], 409));
                        } else {
                            $targets[] = ['copy' => null, 'book' => $book];
                        }
                    }
                }

                $created = [];
                foreach ($targets as $target) {
                    if ($target['copy']) {
                        $issue = $copyService->issue($student, $target['copy']->accession_number, Auth::user(), [
                            'issue_date' => $issueDate,
                            'due_date' => $dueDate,
                            'remarks' => $request->input('remarks'),
                        ]);
                    } else {
                        $book = $target['book'];
                        $issue = IssuedBook::create([
                            'book_id' => $book->id,
                            'student_id' => $student->id,
                            'issued_by' => Auth::id(),
                            'issue_date' => $issueDate,
                            'due_date' => $dueDate,
                            'status' => 'issued',
                            'remarks' => $request->input('remarks'),
                        ])->fresh(['student.user', 'student.department', 'book.category', 'bookCopy']);
                        $book->decrement('available_copies');
                    }

                    $notifications->notifyBookIssued($issue);
                    $bookRequest = BookRequest::query()
                        ->where('student_id', $student->id)
                        ->where('book_id', $issue->book_id)
                        ->where('status', 'approved')
                        ->first();
                    $bookRequest?->update([
                        'status' => 'issued',
                        'processed_date' => now(),
                    ]);
                    $created[] = $issue;
                }

                return $created;
            });

            return response()->json([
                'success' => true,
                'message' => 'Successfully issued ' . count($issuedBooks) . ' book(s).',
                'issued_count' => count($issuedBooks),
                'issued_books' => collect($issuedBooks)->map(fn (IssuedBook $issue) => [
                    'issue_id' => $issue->id,
                    'book_id' => $issue->book_id,
                    'book_copy_id' => $issue->book_copy_id,
                    'accession_number' => $issue->bookCopy?->accession_number,
                    'title' => $issue->book?->title,
                    'due_date' => optional($issue->due_date)->toDateString(),
                ])->values(),
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Error issuing books.',
            ], 422);
        }
    }

    /**
     * Return books from a student
     */
    public function returnBooks(Request $request, PhysicalBookCopyService $copyService)
    {
        Gate::authorize('access-admin');
        
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'issued_book_ids' => 'required|array|min:1',
            'issued_book_ids.*' => 'exists:issued_books,id|distinct',
            'condition' => 'required|in:good,fair,damaged,lost',
        ]);
        
        try {
            $student = Student::findOrFail($request->student_id);
            $condition = $request->condition;
            $returnedCount = 0;
            $totalFine = 0;
            $returnedBooks = [];
            $returnedAccessions = [];
            
            $fineCalculator = new FineCalculator();
            $fineSetting = FineSetting::resolveActive();
            
            foreach ($request->issued_book_ids as $issuedBookId) {
                $issuedBook = IssuedBook::with(['book', 'bookCopy'])->findOrFail($issuedBookId);
                $bookFine = 0;
                
                // Calculate fine based on condition
                if ($condition === 'lost') {
                    // Lost book penalty (no overdue fine added)
                    $bookFine = $fineSetting->lost_book_penalty ?? 0;
                    Fine::updateOrCreate([
                        'issued_book_id' => $issuedBook->id,
                    ], [
                        'student_id' => $issuedBook->student_id,
                        'amount' => $bookFine,
                        'days_late' => 0,
                        'status' => 'paid',
                        'remarks' => 'Lost book penalty',
                    ]);
                } elseif ($condition === 'damaged') {
                    // Damaged book penalty + overdue fine
                    $overdueFine = $fineCalculator->calculateFine($issuedBook);
                    $damageCharge = $fineSetting->damaged_book_penalty ?? 0;
                    $bookFine = ($overdueFine['amount'] ?? 0) + $damageCharge;
                    
                    Fine::updateOrCreate([
                        'issued_book_id' => $issuedBook->id,
                    ], [
                        'student_id' => $issuedBook->student_id,
                        'amount' => $bookFine,
                        'days_late' => $overdueFine['days_late'] ?? 0,
                        'status' => 'paid',
                        'remarks' => 'Damaged book penalty + overdue fine',
                    ]);
                } elseif ($condition === 'fair') {
                    // Fair condition penalty + overdue fine
                    $overdueFine = $fineCalculator->calculateFine($issuedBook);
                    $fairCharge = $fineSetting->fair_condition_penalty ?? 0;
                    $bookFine = ($overdueFine['amount'] ?? 0) + $fairCharge;
                    
                    Fine::updateOrCreate([
                        'issued_book_id' => $issuedBook->id,
                    ], [
                        'student_id' => $issuedBook->student_id,
                        'amount' => $bookFine,
                        'days_late' => $overdueFine['days_late'] ?? 0,
                        'status' => 'paid',
                        'remarks' => 'Fair condition penalty + overdue fine',
                    ]);
                } else {
                    // Good condition - only overdue fine
                    $overdueFine = $fineCalculator->calculateFine($issuedBook);
                    if ($overdueFine) {
                        $bookFine = $overdueFine['amount'] ?? 0;
                        if ($bookFine > 0) {
                            Fine::updateOrCreate([
                                'issued_book_id' => $issuedBook->id,
                            ], [
                                'student_id' => $issuedBook->student_id,
                                'amount' => $bookFine,
                                'days_late' => $overdueFine['days_late'] ?? 0,
                                'status' => 'paid',
                                'remarks' => 'Overdue fine',
                            ]);
                        }
                    }
                }
                
                // Update issued book status
                $issuedBook->update([
                    'return_date' => Carbon::now(),
                    'status' => 'returned',
                    'condition' => $condition,
                    'fine_amount' => $bookFine,
                ]);

                if ($issuedBook->bookCopy) {
                    $issuedBook->bookCopy->update([
                        'status' => match ($condition) {
                            'lost' => 'lost',
                            'damaged' => 'damaged',
                            default => 'available',
                        },
                        'condition' => $condition,
                    ]);
                    $copyService->refreshBookCounters($issuedBook->book);
                }
                
                // Update BookRequest status to 'returned'
                $bookRequest = BookRequest::where('student_id', $issuedBook->student_id)
                    ->where('book_id', $issuedBook->book_id)
                    ->where('status', 'issued')
                    ->first();
                if ($bookRequest) {
                    $bookRequest->update([
                        'status' => 'returned',
                        'processed_date' => Carbon::now(),
                    ]);
                }
                
                // Update book availability
                if (! $issuedBook->bookCopy && $condition !== 'lost' && $condition !== 'damaged') {
                    $issuedBook->book->increment('available_copies');
                }
                
                $totalFine += $bookFine;
                $returnedBooks[] = $issuedBook->book->title;
                if ($issuedBook->bookCopy?->accession_number) {
                    $returnedAccessions[] = $issuedBook->bookCopy->accession_number;
                }
                
                if ($student->user) {
                    try {
                        SendBookReturnedEmail::dispatch(
                            $student->user->email,
                            $student->user->name,
                            $issuedBook->book->title,
                            $condition,
                            (float) $bookFine
                        );
                    } catch (\Throwable $e) {
                        \Log::warning('Unable to queue book returned email: ' . $e->getMessage(), [
                            'issued_book_id' => $issuedBook->id,
                        ]);
                    }
                }
                
                $returnedCount++;
            }
            
            // Notify student about book return
            if ($student->user) {
                $bookTitles = implode(', ', $returnedBooks);
                $fineMessage = $totalFine > 0 ? " A fine of रु {$totalFine} has been applied." : '';
                Notification::notify(
                    user: $student->user,
                    type: 'book.returned',
                    title: 'Book(s) Returned',
                    message: count($returnedBooks) . " book(s) have been accepted" . ($returnedAccessions !== [] ? ' (' . implode(', ', $returnedAccessions) . ')' : '') . ': ' . $bookTitles . $fineMessage,
                    data: [
                        'student_id' => $student->id,
                        'returned_count' => $returnedCount,
                        'total_fine' => $totalFine,
                        'condition' => $condition,
                        'accession_numbers' => $returnedAccessions,
                    ],
                    relatedModel: 'IssuedBook',
                    relatedId: $returnedCount
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully returned $returnedCount book(s) from {$student->user->name}",
                'returned_count' => $returnedCount,
                'total_fine' => $totalFine,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error returning books: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get effective issue duration for a student (per-student override or global default)
     */
    private function getEffectiveIssueDuration(Student $student): int
    {
        // Check if student has privilege overrides
        if ($student->privileges && $student->privileges->issue_duration_days) {
            return $student->privileges->issue_duration_days;
        }

        // Fall back to global fine settings
        return FineSetting::resolveActive()->issue_duration_days;
    }

    /**
     * Get effective per-day fine for a student (per-student override or global default)
     */
    private function getEffectivePerDayFine(Student $student): float
    {
        // Check if student has privilege overrides
        if ($student->privileges && $student->privileges->per_day_fine) {
            return $student->privileges->per_day_fine;
        }

        // Fall back to global fine settings
        return FineSetting::resolveActive()->per_day_fine;
    }

    /**
     * Check if student is allowed to borrow
     */
    private function isStudentAllowedToBorrow(Student $student): bool
    {
        // Check if student has privilege overrides
        if ($student->privileges && !$student->privileges->borrowing_allowed) {
            return false;
        }

        return true;
    }
}
