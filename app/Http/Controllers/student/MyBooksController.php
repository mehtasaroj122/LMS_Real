<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\IssuedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MyBooksController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();
        $student = Student::with('department')->where('user_id', $user->id)->first();

        if (!$student) {
            return view('Student.MyBooks', [
                'student' => null,
                'issuedBooksJson' => json_encode([]),
                'totalIssued' => 0,
                'currentlyBorrowed' => 0,
                'overdueBooks' => 0,
                'totalFine' => 0,
            ]);
        }

        // Get all issued books (both current and returned)
        $allIssuedBooks = IssuedBook::where('student_id', $student->id)
            ->with(['book' => function($q) {
                $q->with('category');
            }, 'fine'])
            ->get();

        // Calculate stats
        $totalIssued = $allIssuedBooks->count();
        $currentlyBorrowed = $allIssuedBooks->whereNull('return_date')->count();
        $overdueBooks = $allIssuedBooks->whereNull('return_date')->filter(function($book) {
            return $book->due_date < now();
        })->count();

        // Calculate total unpaid fines
        $totalFine = $allIssuedBooks->filter(function($book) {
            return $book->fine && $book->fine->status === 'pending';
        })->sum(function($book) {
            return $book->fine ? $book->fine->amount : 0;
        });

        // Transform books data for JavaScript
        $issuedBooksJson = json_encode($allIssuedBooks->map(function($issuedBook) {
            return [
                'id' => $issuedBook->id,
                'title' => $issuedBook->book->title,
                'isbn' => 'ISBN: ' . $issuedBook->book->isbn,
                'author' => $issuedBook->book->author,
                'category' => $issuedBook->book->category ? $issuedBook->book->category->name : 'uncategorized',
                'issueDate' => $issuedBook->issue_date->format('M d, Y'),
                'dueDate' => $issuedBook->due_date->format('M d, Y'),
                'returnDate' => $issuedBook->return_date ? $issuedBook->return_date->format('M d, Y') : null,
                'status' => $issuedBook->return_date ? 'returned' : ($issuedBook->due_date < now() ? 'overdue' : $this->calculateStatus($issuedBook)),
                'fine' => $issuedBook->fine ? ($issuedBook->fine->amount > 0 ? '₹' . $issuedBook->fine->amount : 'No Fine') : 'No Fine',
                'fineStatus' => $issuedBook->fine ? ($issuedBook->fine->status === 'paid' ? 'paid' : ($issuedBook->fine->status === 'pending' ? 'unpaid' : 'waived')) : 'none'
            ];
        })->toArray());

        return view('Student.MyBooks', [
            'student' => $student,
            'issuedBooksJson' => $issuedBooksJson,
            'totalIssued' => $totalIssued,
            'currentlyBorrowed' => $currentlyBorrowed,
            'overdueBooks' => $overdueBooks,
            'totalFine' => $totalFine,
        ]);
    }

    private function calculateStatus($issuedBook)
    {
        $totalDays = $issuedBook->issue_date->diffInDays($issuedBook->due_date);
        $elapsedDays = $issuedBook->issue_date->diffInDays(now());
        
        if ($totalDays == 0) {
            return 'issued';
        }

        $percentagePassed = ($elapsedDays / $totalDays) * 100;

        return $percentagePassed >= 80 ? 'due-soon' : 'issued';
    }
}
