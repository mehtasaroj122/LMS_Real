<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\PhysicalCopyException;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\Notification;
use App\Services\FineCalculator;
use App\Services\PhysicalBookCopyService;
use App\Services\NotificationService;
use App\Services\StudentIssuePrivilegeService;
use App\Jobs\SendBookIssuedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IssueBookController extends Controller
{
    public function index()
    {
        Gate::authorize('access-staff');
        $fineCalculator = new FineCalculator();
        $fineSettings = $fineCalculator->getSettings();
        return view('Staff.IssueBook', compact('fineSettings'));
    }

    public function getStudents(Request $request)
    {
        Gate::authorize('access-staff');
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
            ->whereHas('user', function($q) { $q->where('role', 'student'); })
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
                    || $privileges->grace_period_days !== null
                    || $privileges->max_fine_amount !== null
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

    public function getAvailableBooks(Request $request)
    {
        try {
            Gate::authorize('access-staff');
            $query = $request->input('query', '');
            $studentId = $request->input('studentId');
            if (!$studentId) return response()->json([], 400);
            
            $issuedBookIds = IssuedBook::where('student_id', $studentId)->whereNull('return_date')
                ->pluck('book_id')->map(fn ($id) => (int) $id)->all();
            $booksQuery = Book::with('category');
            if ($query) {
                $booksQuery->where(function($q) use ($query) {
                    $q->where('title', 'like', "%$query%")
                        ->orWhere('publisher', 'like', "%$query%")
                        ->orWhere('author', 'like', "%$query%");
                });
            }
            $books = $booksQuery->with(['copies' => function ($copyQuery) {
                $copyQuery->withCount(['issuedBooks as active_issues_count' => fn ($issued) => $issued->whereNull('return_date')])
                    ->orderBy('accession_number');
            }])->limit(15)->get()->map(function($book) use ($issuedBookIds) {
                $alreadyIssued = in_array((int) $book->id, $issuedBookIds, true);
                return [
                    'id' => $book->id,
                    'already_issued_to_student' => $alreadyIssued,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                    'author' => $book->author ?? 'Unknown',
                    'publisher' => $book->publisher ?? 'N/A',
                    'category' => $book->category->name ?? 'N/A',
                    'total_copies' => (int) $book->total_copies,
                    'available_copies' => $book->copies->filter(fn (BookCopy $copy) =>
                        $copy->circulationStatus() === 'available'
                        && $copy->book_type !== 'reference'
                        && ! in_array($copy->condition, ['damaged', 'lost'], true)
                    )->count(),
                    'copies' => $book->copies->map(fn (BookCopy $copy) => [
                        'id' => $copy->id,
                        'book_id' => $copy->book_id,
                        'already_issued_to_student' => $alreadyIssued,
                        'accession_number' => $copy->accession_number,
                        'book_type' => $copy->book_type,
                        'status' => $copy->circulationStatus(),
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
            \Log::error('Error fetching books (staff): ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getIssuedBooks(Request $request)
    {
        Gate::authorize('access-staff');
        
        $studentId = $request->input('studentId');
        if (!$studentId) return response()->json([], 400);
        
        $query = IssuedBook::where('student_id', $studentId)
            ->whereNull('return_date');
        
        $countOnly = $request->boolean('countOnly');
        
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
                    'bookCopyId' => $issued->book_copy_id,
                    'accessionNumber' => $issued->bookCopy?->accession_number,
                    'bookTitle' => $issued->book->title,
                    'book_title' => $issued->book->title,
                    'title' => $issued->book->title,
                    'author' => $issued->book->author ?? 'Unknown',
                    'isbn' => $issued->book->isbn,
                    'issueDate' => $issued->issue_date->format('Y-m-d'),
                    'issue_date' => $issued->issue_date->format('Y-m-d'),
                    'dueDate' => $issued->due_date->format('Y-m-d'),
                    'due_date' => $issued->due_date->format('Y-m-d'),
                    'overdueDays' => $overdueDays,
                    'overdue_days' => $overdueDays,
                    'isOverdue' => $overdueDays > 0,
                    'is_overdue' => $overdueDays > 0,
                    'returned' => !is_null($issued->return_date),
                ];
            });
        
        return response()->json($issuedBooks);
    }

    public function getPrivileges(int $student, StudentIssuePrivilegeService $privilegeService)
    {
        Gate::authorize('access-staff');

        $studentModel = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($student);
        $effective = $privilegeService->getPrivileges($studentModel);
        $defaults = FineSetting::resolveActive();
        $raw = $studentModel->privileges;

        $hasPrivilegeOverride = $raw !== null && (
            $raw->max_books !== null
            || $raw->issue_duration_days !== null
            || $raw->per_day_fine !== null
            || $raw->grace_period_days !== null
            || $raw->max_fine_amount !== null
            || $raw->borrowing_allowed === false
        );

        return response()->json([
            'success' => true,
            'privileges' => [
                'max_books' => $raw?->max_books,
                'issue_duration_days' => $raw?->issue_duration_days,
                'per_day_fine' => $raw?->per_day_fine,
                'borrowing_allowed' => $raw ? (bool) ($raw->borrowing_allowed ?? true) : true,
                'grace_period_days' => $raw?->grace_period_days,
                'max_fine_amount' => $raw?->max_fine_amount,
            ],
            'defaults' => [
                'max_books' => (int) $defaults->max_books_per_student,
                'issue_duration_days' => (int) $defaults->issue_duration_days,
                'per_day_fine' => (float) $defaults->per_day_fine,
                'borrowing_allowed' => true,
                'grace_period_days' => (int) $defaults->grace_period_days,
                'max_fine_amount' => (float) $defaults->max_fine_amount,
            ],
            'effective' => [
                'max_books' => $effective['max_books'],
                'issue_duration_days' => $effective['duration_days'],
                'per_day_fine' => $effective['fine_rate'],
                'borrowing_allowed' => $effective['allowed'],
                'grace_period_days' => $raw?->grace_period_days ?? $defaults->grace_period_days,
                'max_fine_amount' => $raw?->max_fine_amount ?? $defaults->max_fine_amount,
            ],
            'has_custom_overrides' => $hasPrivilegeOverride,
            'hasPrivilegeOverride' => $hasPrivilegeOverride,
        ]);
    }

    public function issueBooks(
        Request $request,
        PhysicalBookCopyService $copyService,
        NotificationService $notifications,
        StudentIssuePrivilegeService $privilegeService
    )
    {
        Gate::authorize('access-staff');
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
            $requestedCount = count($copyIds) > 0 ? count($copyIds) : count($legacyBookIds);

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
                $created = [];

                // Lock in a stable order so concurrent staff requests cannot
                // issue the same physical copy or deadlock on different orders.
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

                    if ($copies->pluck('book_id')->unique()->count() !== $copies->count()) {
                        throw new PhysicalCopyException(
                            'Only one copy of each book can be selected. Remove the extra copies and try again.',
                            422,
                            'duplicate_book_id'
                        );
                    }

                    foreach ($copyIds as $copyId) {
                        $targets[] = ['copy' => $copies->get($copyId), 'book' => null];
                    }
                } else {
                    // Compatibility for older staff clients that only know a
                    // book id. When physical copies exist, still resolve one
                    // real copy and send it through the same copy-aware issue
                    // service. The title-only fallback is limited to legacy
                    // books that have no book_copies rows at all.
                    foreach ($legacyBookIds as $bookId) {
                        $book = Book::query()->lockForUpdate()->findOrFail($bookId);
                        $copy = BookCopy::query()
                            ->where('book_id', $book->id)
                            ->whereIn('status', ['available', 'issued'])
                            ->whereDoesntHave('issuedBooks', fn ($issued) => $issued->whereNull('return_date'))
                            ->where('book_type', '!=', 'reference')
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->first();

                        if ($copy) {
                            $targets[] = ['copy' => $copy, 'book' => null];
                            continue;
                        }

                        if (BookCopy::query()->where('book_id', $book->id)->exists()) {
                            abort(response()->json(['success' => false, 'message' => "The book '{$book->title}' is not available."], 409));
                        }

                        $targets[] = ['copy' => null, 'book' => $book];
                    }
                }

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
                    $bookRequest = \App\Models\BookRequest::query()
                        ->where('student_id', $student->id)
                        ->where('book_id', $issue->book_id)
                        ->where('status', 'approved')
                        ->first();
                    $bookRequest?->update([
                        'status' => 'issued',
                        'processed_date' => now(),
                    ]);


                    if ($student->user?->email) {
                        try {
                            SendBookIssuedEmail::dispatch(
                                $student->user->email,
                                $student->user->name,
                                $issue->book?->title,
                                $issue->book?->author ?? 'Unknown',
                                $issue->issue_date->format('Y-m-d'),
                                $issue->due_date->format('Y-m-d')
                            );
                        } catch (\Throwable $e) {
                            \Log::warning('Unable to queue book issued email: ' . $e->getMessage(), ['issued_book_id' => $issue->id]);
                        }
                    }

                    $created[] = $issue;
                }

                return $created;
            });
            return response()->json([
                'success' => true,
                'message' => "Successfully issued " . count($issuedBooks) . ' book(s).',
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
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Error issuing books.'], 422);
        }
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
