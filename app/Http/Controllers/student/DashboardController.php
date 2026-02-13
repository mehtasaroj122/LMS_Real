<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('access-student');
        $user = auth()->user();
        $student = $user->student;
        $department = $student ? $student->department : null;

        // Stats
        $booksIssuedCount = $student ? $student->issuedBooks()->whereNull('return_date')->count() : 0;
        $booksReturnedCount = $student ? $student->issuedBooks()->whereNotNull('return_date')->count() : 0;
        $pendingFines = $student ? $student->issuedBooks()->whereHas('fine', function($q) {
            $q->where('status', 'pending');
        })->with(['fine' => function($q) {
            $q->where('status', 'pending');
        }])->get()->sum(function($issuedBook) {
            return $issuedBook->fine ? $issuedBook->fine->amount : 0;
        }) : 0;
        $activeRequestsCount = $student ? $student->bookRequests()->where('status', 'pending')->count() : 0;

        // Issued Books (limit 3 for dashboard)
        $issuedBooks = $student ? $student->issuedBooks()->whereNull('return_date')->with('book', 'fine')->latest('issue_date')->take(3)->get() : collect();

        // Pending Requests (limit 3 for dashboard)
        $pendingRequests = $student ? $student->bookRequests()->whereIn('status', ['pending', 'approved'])->with('book')->latest('request_date')->take(3)->get() : collect();

        // Notifications (limit 4)
        $notifications = $user->notifications()->latest()->take(4)->get();

        return view('Student.Dashboard', [
            'student' => $student,
            'user' => $user,
            'department' => $department,
            'booksIssuedCount' => $booksIssuedCount,
            'booksReturnedCount' => $booksReturnedCount,
            'pendingFines' => $pendingFines,
            'activeRequestsCount' => $activeRequestsCount,
            'issuedBooks' => $issuedBooks,
            'pendingRequests' => $pendingRequests,
            'notifications' => $notifications,
        ]);
    }
}
