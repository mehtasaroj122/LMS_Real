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
use App\Jobs\SendBookReturnedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ReturnBookController extends Controller
{
    public function index()
    {
        Gate::authorize('access-staff');
        $fineCalculator = new FineCalculator();
        $fineSettings = $fineCalculator->getSettings();
        return view('Staff.ReturnBook', compact('fineSettings'));
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

    public function returnBooks(Request $request)
    {
        Gate::authorize('access-staff');
        
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
            
            // Load student relationships needed for privilege calculations
            $student->load('privileges');
            
            $fineCalculator = new FineCalculator();
            $fineSetting = FineSetting::resolveActive();
            
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
                
                // Update BookRequest status to returned
                $bookRequest = \App\Models\BookRequest::where('student_id', $student->id)
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
                
                // Log activity
                ActivityLogger::logBookReturned($student, $issuedBook->book->title, [
                    'isbn' => $issuedBook->book->isbn,
                    'condition' => $condition,
                    'fine_amount' => $bookFine,
                ]);
                
                // Send notification to student
                $notificationType = ($bookFine > 0) ? 'fine.created' : 'book.returned';
                $title = ($bookFine > 0) ? 'Book Returned with Fine' : 'Book Returned Successfully';
                $message = ($bookFine > 0) 
                    ? "Your return of '{$issuedBook->book->title}' has been processed. Fine amount: ₹{$bookFine}"
                    : "Your return of '{$issuedBook->book->title}' has been accepted.";
                
                Notification::notify(
                    user: $student->user,
                    type: $notificationType,
                    title: $title,
                    message: $message,
                    data: [
                        'book_id' => $issuedBook->book_id,
                        'issued_book_id' => $issuedBook->id,
                        'condition' => $condition,
                        'fine_amount' => $bookFine,
                    ],
                    relatedModel: 'IssuedBook',
                    relatedId: $issuedBook->id
                );
                
                // Queue email to send 3 seconds later
                // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
                if ($student->user->email) {
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
}
