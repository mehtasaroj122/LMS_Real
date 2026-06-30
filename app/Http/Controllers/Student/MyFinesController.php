<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentFineSummaryService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MyFinesController extends Controller
{
    public function index(StudentFineSummaryService $studentFineSummary)
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

        $allFines = $studentFineSummary->allFinesWithOpenOverdues($student);

        // Calculate statistics
        $outstandingAmount = $studentFineSummary->pendingAmount($student);
        $paidAmount = $allFines->where('status', 'paid')->sum('amount');
        $waivedAmount = $allFines->where('status', 'waived')->sum('amount');
        $outstandingCount = $allFines->where('status', 'pending')->count();
        $overdueCount = $studentFineSummary->openOverdueBookCount($student);

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
                'status' => $fine->status === 'pending' ? 'unpaid' : $fine->status,
                'lastUpdated' => optional($fine->updated_at)->format('Y-m-d') ?? now()->format('Y-m-d'),
            ];
        })->toArray());

        return view('Student.MyFines', [
            'student' => $student,
            'finesJson' => $finesJson,
            'outstandingAmount' => $outstandingAmount,
            'outstandingCount' => $outstandingCount,
            'paidAmount' => $paidAmount,
            'waivedAmount' => $waivedAmount,
            'overdueCount' => $overdueCount,
        ]);
    }
}
