<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

        // Issued Books
        $issuedBooks = $student ? $student->issuedBooks()
            ->whereNull('return_date')
            ->with('book', 'fine')
            ->orderBy('due_date', 'asc')
            ->get() : collect();

        // Pending Requests (limit 3 for dashboard)
        $pendingRequests = $student ? $student->bookRequests()->whereIn('status', ['pending', 'approved'])->with('book')->latest('request_date')->take(3)->get() : collect();

        // Notifications (limit 4)
        $notifications = $user->notifications()->latest()->take(4)->get();

        // CHART 1: Monthly Activity (Last 6 months)
        $months = [];
        $monthlyIssued = [];
        $monthlyReturned = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::today()->subMonths($i);
            $months[] = $date->format('M');

            $monthlyIssued[] = $student ? $student->issuedBooks()
                ->whereYear('issue_date', $date->year)
                ->whereMonth('issue_date', $date->month)
                ->count() : 0;

            $monthlyReturned[] = $student ? $student->issuedBooks()
                ->whereNotNull('return_date')
                ->whereYear('return_date', $date->year)
                ->whereMonth('return_date', $date->month)
                ->count() : 0;
        }

        $monthlyActivity = [
            'labels' => $months,
            'issued' => $monthlyIssued,
            'returned' => $monthlyReturned,
        ];

        // CHART 2: Reading by Category (books already returned)
        $booksByCategory = collect();
        if ($student) {
            $booksByCategory = $student->issuedBooks()
                ->with('book.category')
                ->whereNotNull('return_date')
                ->get()
                ->groupBy(function ($issuedBook) {
                    return optional(optional($issuedBook->book)->category)->name ?? 'Uncategorized';
                })
                ->map->count()
                ->sortDesc();
        }

        $readingCategories = [
            'labels' => $booksByCategory->keys()->take(5)->toArray(),
            'data' => $booksByCategory->values()->take(5)->toArray(),
            'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        ];

        // TABLE DATA: Due Soon (next 3 days)
        $dueSoon = collect();
        if ($student) {
            $dueSoon = $student->issuedBooks()
                ->with('book')
                ->whereNull('return_date')
                ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
                ->whereDate('due_date', '>=', Carbon::today())
                ->orderBy('due_date', 'asc')
                ->limit(5)
                ->get();
        }

        // Additional student info
        $yearOfStudy = $student ? (now()->year - $student->created_at->year + 1) : 1;

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
            'monthlyActivity' => $monthlyActivity,
            'readingCategories' => $readingCategories,
            'dueSoon' => $dueSoon,
            'yearOfStudy' => $yearOfStudy,
        ]);
    }
}
