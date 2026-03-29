<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\Notification;
use App\Jobs\SendBookIssuedEmail;
use App\Jobs\SendBookReturnedEmail;
use App\Helpers\ActivityLogger;
use App\Services\FineCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
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
        $fineSetting = FineSetting::where('is_active', true)->first() ?? FineSetting::first();
        $defaultMaxBooks = $fineSetting->max_books_per_student ?? 5;
        
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
                    'hasPrivilegeOverride' => $privileges !== null,
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
                ->where('available_copies', '>', 0)
                ->whereNotIn('id', $issuedBookIds);
            
            // Add search filter if provided
            if ($query) {
                $booksQuery->where(function($q) use ($query) {
                    $q->where('title', 'like', "%$query%")
                        ->orWhere('isbn', 'like', "%$query%")
                        ->orWhere('author', 'like', "%$query%");
                });
            }
            
            $books = $booksQuery->limit(15)
                ->get()
                ->map(function($book) {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'isbn' => $book->isbn,
                        'author' => $book->author ?? 'Unknown',
                        'category' => $book->category->name ?? 'N/A',
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
        
        $issuedBooks = $query->with('book')
            ->get()
            ->map(function($issued) {
                $overdueDays = max(0, Carbon::parse($issued->due_date)->diffInDays(Carbon::now()));
                
                return [
                    'id' => $issued->id,
                    'bookId' => $issued->book_id,
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
    public function issueBooks(Request $request)
    {
        Gate::authorize('access-admin');
        
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'book_ids' => 'required|array|min:1|max:5',
            'book_ids.*' => 'exists:books,id',
        ]);
        
        try {
            $student = Student::findOrFail($request->student_id);
            
            // Check if student is allowed to borrow
            if (!$this->isStudentAllowedToBorrow($student)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This student is not allowed to borrow books at this time.'
                ], 403);
            }
            
            $bookIds = $request->book_ids;
            $issuedCount = 0;
            $issuedBooks = [];
            
            $fineCalculator = new FineCalculator();
            
            // Get effective issue duration for this student (per-student override or global default)
            $issueDuration = $this->getEffectiveIssueDuration($student);
            
            foreach ($bookIds as $bookId) {
                // Create issued book record
                $issuedBook = IssuedBook::create([
                    'book_id' => $bookId,
                    'student_id' => $student->id,
                    'issued_by' => Auth::id(),
                    'issue_date' => Carbon::now(),
                    'due_date' => Carbon::now()->addDays($issueDuration),
                    'status' => 'issued',
                ]);
                
                // Update book availability
                $book = Book::findOrFail($bookId);
                $book->decrement('available_copies');
                
                // Update BookRequest status to 'issued'
                $bookRequest = BookRequest::where('student_id', $student->id)
                    ->where('book_id', $bookId)
                    ->where('status', 'approved')
                    ->first();
                if ($bookRequest) {
                    $bookRequest->update([
                        'status' => 'issued',
                        'processed_date' => Carbon::now(),
                    ]);
                }
                
                // Log activity
                ActivityLogger::logBookIssued($student, $book->title, [
                    'isbn' => $book->isbn,
                    'issued_date' => $issuedBook->issue_date,
                    'due_date' => $issuedBook->due_date,
                ]);
                
                $issuedBooks[] = $book->title;
                $issuedCount++;
            }
            
            // Notify student about book issue
            if ($student->user) {
                $bookTitles = implode(', ', $issuedBooks);
                Notification::notify(
                    user: $student->user,
                    type: 'book.issued',
                    title: 'Book(s) Issued',
                    message: count($issuedBooks) . " book(s) have been issued to you: " . $bookTitles,
                    data: [
                        'student_id' => $student->id,
                        'issued_count' => $issuedCount,
                        'due_date' => Carbon::now()->addDays($issueDuration)->format('Y-m-d'),
                    ],
                    relatedModel: 'IssuedBook',
                    relatedId: $issuedCount
                );
                
                // Queue email to send 3 seconds later for each book
                // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
                foreach ($issuedBooks as $title) {
                    $book = Book::where('title', $title)->first();
                    if ($book) {
                        // SendBookIssuedEmail::dispatch(
                        //     $student->user->email,
                        //     $student->user->name,
                        //     $title,
                        //     $book->author ?? 'Unknown',
                        //     Carbon::now()->format('Y-m-d'),
                        //     Carbon::now()->addDays($issueDuration)->format('Y-m-d')
                        // );
                        \Log::info('Book issued email would have been sent to: ' . $student->user->email);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully issued $issuedCount book(s) to {$student->user->name}",
                'issued_count' => $issuedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error issuing books: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return books from a student
     */
    public function returnBooks(Request $request)
    {
        Gate::authorize('access-admin');
        
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'issued_book_ids' => 'required|array|min:1',
            'issued_book_ids.*' => 'exists:issued_books,id',
            'condition' => 'required|in:good,fair,damaged,lost',
        ]);
        
        try {
            $student = Student::findOrFail($request->student_id);
            $condition = $request->condition;
            $returnedCount = 0;
            $totalFine = 0;
            $returnedBooks = [];
            
            $fineCalculator = new FineCalculator();
            $fineSetting = FineSetting::where('is_active', true)->first();
            
            foreach ($request->issued_book_ids as $issuedBookId) {
                $issuedBook = IssuedBook::findOrFail($issuedBookId);
                $bookFine = 0;
                
                // Calculate fine based on condition
                if ($condition === 'lost') {
                    // Lost book penalty (no overdue fine added)
                    $bookFine = $fineSetting->lost_book_penalty ?? 0;
                    Fine::create([
                        'issued_book_id' => $issuedBook->id,
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
                    
                    Fine::create([
                        'issued_book_id' => $issuedBook->id,
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
                    
                    Fine::create([
                        'issued_book_id' => $issuedBook->id,
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
                            Fine::create([
                                'issued_book_id' => $issuedBook->id,
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
                if ($condition !== 'lost' && $condition !== 'damaged') {
                    $issuedBook->book->increment('available_copies');
                }
                
                $totalFine += $bookFine;
                $returnedBooks[] = $issuedBook->book->title;
                
                // Log activity
                ActivityLogger::logBookReturned($student, $issuedBook->book->title, [
                    'isbn' => $issuedBook->book->isbn,
                    'condition' => $condition,
                    'fine_amount' => $bookFine,
                ]);
                
                // Queue email to send 3 seconds later
                // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
                if ($student->user) {
                    // SendBookReturnedEmail::dispatch(
                    //     $student->user->email,
                    //     $student->user->name,
                    //     $issuedBook->book->title,
                    //     $condition,
                    //     $bookFine
                    // );
                    \Log::info('Book returned email would have been sent to: ' . $student->user->email);
                }
                
                $returnedCount++;
            }
            
            // Notify student about book return
            if ($student->user) {
                $bookTitles = implode(', ', $returnedBooks);
                $fineMessage = $totalFine > 0 ? " A fine of Rs. {$totalFine} has been applied." : '';
                Notification::notify(
                    user: $student->user,
                    type: 'book.returned',
                    title: 'Book(s) Returned',
                    message: count($returnedBooks) . " book(s) have been accepted: " . $bookTitles . $fineMessage,
                    data: [
                        'student_id' => $student->id,
                        'returned_count' => $returnedCount,
                        'total_fine' => $totalFine,
                        'condition' => $condition,
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
        $fineSetting = FineSetting::where('is_active', true)->first();
        return $fineSetting->issue_duration_days ?? 14;
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
        $fineSetting = FineSetting::where('is_active', true)->first();
        return $fineSetting->per_day_fine ?? 10;
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
