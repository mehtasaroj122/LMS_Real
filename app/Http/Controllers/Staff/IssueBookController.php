<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\book as Book;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\Notification;
use App\Helpers\ActivityLogger;
use App\Services\FineCalculator;
use App\Notifications\BookIssuedNotification;
use App\Jobs\SendBookIssuedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
        $students = Student::selectRaw('DISTINCT students.*')
            ->with('user', 'department')
            ->whereHas('user', function($q) { $q->where('role', 'student'); })
            ->when($query, function($q) use ($query) {
                return $q->where('user_id', 'like', "%$query%")
                    ->orWhereHas('user', function($subQuery) use ($query) {
                        $subQuery->where('name', 'like', "%$query%")->orWhere('email', 'like', "%$query%");
                    })
                    ->orWhereHas('department', function($subQuery) use ($query) {
                        $subQuery->where('name', 'like', "%$query%");
                    });
            })
            ->limit(15)
            ->get()
            ->map(function($student) {
                $issuedCount = IssuedBook::where('student_id', $student->id)->whereNull('return_date')->count();
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'studentId' => $student->user_id,
                    'roll_no' => $student->roll_no,
                    'email' => $student->user->email,
                    'department' => $student->department->name ?? 'N/A',
                    'issued' => $issuedCount,
                    'maxBooks' => $student->max_books_allowed ?? 5,
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
            
            $issuedBookIds = IssuedBook::where('student_id', $studentId)->whereNull('return_date')->pluck('book_id')->toArray();
            $booksQuery = Book::with('category')->where('available_copies', '>', 0)->whereNotIn('id', $issuedBookIds);
            if ($query) {
                $booksQuery->where(function($q) use ($query) {
                    $q->where('title', 'like', "%$query%")->orWhere('isbn', 'like', "%$query%")->orWhere('author', 'like', "%$query%");
                });
            }
            $books = $booksQuery->limit(15)->get()->map(function($book) {
                return ['id' => $book->id, 'title' => $book->title, 'isbn' => $book->isbn, 'author' => $book->author ?? 'Unknown', 'category' => $book->category->name ?? 'N/A'];
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
        
        $issuedBooks = $query->with('book')
            ->get()
            ->map(function($issued) {
                $overdueDays = max(0, Carbon::parse($issued->due_date)->diffInDays(Carbon::now()));
                
                return [
                    'id' => $issued->id,
                    'bookId' => $issued->book_id,
                    'book_title' => $issued->book->title,
                    'title' => $issued->book->title,
                    'author' => $issued->book->author ?? 'Unknown',
                    'isbn' => $issued->book->isbn,
                    'issue_date' => $issued->issue_date->format('Y-m-d'),
                    'due_date' => $issued->due_date->format('Y-m-d'),
                    'overdue_days' => $overdueDays,
                    'is_overdue' => $overdueDays > 0,
                    'returned' => !is_null($issued->return_date),
                ];
            });
        
        return response()->json($issuedBooks);
    }

    public function issueBooks(Request $request)
    {
        Gate::authorize('access-staff');
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
            $fineCalculator = new FineCalculator();
            
            // Get effective issue duration for this student (per-student override or global default)
            $issueDuration = $this->getEffectiveIssueDuration($student);
            
            foreach ($bookIds as $bookId) {
                $issuedBook = IssuedBook::create([
                    'book_id' => $bookId,
                    'student_id' => $student->id,
                    'issued_by' => Auth::id(),
                    'issue_date' => Carbon::now(),
                    'due_date' => Carbon::now()->addDays($issueDuration),
                    'status' => 'issued',
                ]);
                $book = Book::findOrFail($bookId);
                $book->decrement('available_copies');
                
                // Update BookRequest status to issued if it exists
                $bookRequest = \App\Models\BookRequest::where('student_id', $student->id)
                    ->where('book_id', $bookId)
                    ->where('status', 'approved')
                    ->first();
                
                if ($bookRequest) {
                    $bookRequest->update([
                        'status' => 'issued',
                        'processed_date' => Carbon::now(),
                    ]);
                }
                
                ActivityLogger::logBookIssued($student, $book->title, [
                    'isbn' => $book->isbn,
                    'issued_date' => $issuedBook->issue_date,
                    'due_date' => $issuedBook->due_date,
                ]);
                
                // Send notification to student
                Notification::notify(
                    user: $student->user,
                    type: 'book.issued',
                    title: 'Book Issued Successfully',
                    message: "You have been issued '{$book->title}' by {$book->author}",
                    data: [
                        'book_id' => $book->id,
                        'issued_book_id' => $issuedBook->id,
                        'issue_date' => $issuedBook->issue_date,
                        'due_date' => $issuedBook->due_date,
                    ],
                    relatedModel: 'IssuedBook',
                    relatedId: $issuedBook->id
                );
                
                // Queue email to send 3 seconds later
                if ($student->user->email) {
                    SendBookIssuedEmail::dispatch(
                        $student->user->email,
                        $student->user->name,
                        $book->title,
                        $book->author ?? 'Unknown',
                        $issuedBook->issue_date->format('Y-m-d'),
                        $issuedBook->due_date->format('Y-m-d')
                    );
                }
                
                $issuedCount++;
            }
            return response()->json([
                'success' => true,
                'message' => "Successfully issued $issuedCount book(s) to {$student->user->name}",
                'issued_count' => $issuedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error issuing books: ' . $e->getMessage()], 500);
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
        $fineSetting = FineSetting::where('is_active', true)->first();
        return $fineSetting->issue_duration_days ?? 14;
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
