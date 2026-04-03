<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MyFinesController extends Controller
{
    public function index()
    {
        Gate::authorize('access-student');

        $user = Auth::user();
        $student = Student::with('department')->where('user_id', $user->id)->first();

        if (!$student) {
            return view('Student.MyFines', [
                'student' => null,
                'finesJson' => json_encode([]),
                'outstandingAmount' => 0,
                'paidAmount' => 0,
                'waivedAmount' => 0,
                'overdueCount' => 0,
            ]);
        }

        // Get all fines for the student
        $allFines = Fine::where('student_id', $student->id)
            ->with([
                'issuedBook' => function($q) {
                    $q->with('book');
                }
            ])
            ->latest('created_at')
            ->get();

        // Calculate statistics
        $outstandingAmount = $allFines->where('status', 'unpaid')->sum('amount');
        $paidAmount = $allFines->where('status', 'paid')->sum('amount');
        $waivedAmount = $allFines->where('status', 'waived')->sum('amount');
        
        // Count overdue books (unpaid fines with days_late > 0)
        $overdueCount = $allFines->where('status', 'unpaid')->where('days_late', '>', 0)->count();

        // Transform fines data for JavaScript
        $finesJson = json_encode($allFines->map(function($fine) {
            $issuedBook = $fine->issuedBook;
            $book = $issuedBook ? $issuedBook->book : null;
            
            // Determine fine reason based on condition or status
            $fineReason = 'overdue';
            if ($issuedBook && $issuedBook->condition === 'damaged') {
                $fineReason = 'damage';
            } elseif ($issuedBook && $issuedBook->condition === 'fair') {
                $fineReason = 'fair';
            } elseif ($issuedBook && $issuedBook->condition === 'lost') {
                $fineReason = 'lost';
            }

            // Format due date
            $dueDate = $issuedBook && $issuedBook->due_date 
                ? $issuedBook->due_date->format('M d, Y')
                : 'N/A';

            // Calculate days overdue
            $daysOverdue = $fine->days_late > 0 ? $fine->days_late . ' days' : '—';

            return [
                'id' => $fine->id,
                'bookTitle' => $book ? $book->title : 'Unknown Book',
                'fineReason' => $fineReason,
                'dueDate' => $dueDate,
                'daysOverdue' => $daysOverdue,
                'fineAmount' => '₹' . $fine->amount,
                'status' => $fine->status,
                'lastUpdated' => $fine->updated_at->format('Y-m-d'),
            ];
        })->toArray());

        return view('Student.MyFines', [
            'student' => $student,
            'finesJson' => $finesJson,
            'outstandingAmount' => $outstandingAmount,
            'paidAmount' => $paidAmount,
            'waivedAmount' => $waivedAmount,
            'overdueCount' => $overdueCount,
        ]);
    }
}
