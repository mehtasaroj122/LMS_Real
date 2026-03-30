<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\book;
use App\Models\IssuedBook;
use App\Models\BookRequest;
use App\Models\Fine;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-staff');
        $today = Carbon::today();

        $currentlyIssued = IssuedBook::whereNull('return_date')->count();

        $dueToday = IssuedBook::whereNull('return_date')
            ->whereDate('due_date', $today->toDateString())
            ->count();

        $overdueCount = IssuedBook::whereNull('return_date')
            ->whereDate('due_date', '<', $today->toDateString())
            ->count();

        $overdues = IssuedBook::with(['book', 'student.user'])
            ->whereNull('return_date')
            ->whereDate('due_date', '<', $today->toDateString())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $pendingRequestsBaseQuery = BookRequest::with(['book', 'student.user'])
            ->whereHas('student.user', function ($query) {
                $query->where('role', 'student');
            })
            ->where('status', 'pending');

        $pendingRequests = (clone $pendingRequestsBaseQuery)
            ->orderBy('request_date', 'desc')
            ->limit(5)
            ->get();

        $pendingRequestsCount = (clone $pendingRequestsBaseQuery)->count();

        // Count only fines that are actually pending (exclude paid/waived)
        // Match the logic used in Staff\FineController::getFinesData by restricting to student users
        $pendingFinesAmount = Fine::whereHas('student.user', function($q) {
            $q->where('role', 'student');
        })->whereRaw('LOWER(status) = ?', ['pending'])->sum('amount');

        $availableBooks = book::sum('available_copies');
        $circulationData = [
            'labels' => ['Currently Issued', 'Available', 'Overdue', 'Due Today'],
            'data' => [$currentlyIssued, $availableBooks, $overdueCount, $dueToday],
            'colors' => ['#3b82f6', '#10b981', '#ef4444', '#f59e0b'],
        ];

        $days = [];
        $issuedPerDay = [];
        $returnedPerDay = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            $days[] = $date->format('D');
            $issuedPerDay[] = IssuedBook::whereDate('issue_date', $date)->count();
            $returnedPerDay[] = IssuedBook::whereDate('return_date', $date)->count();
        }

        $activityData = [
            'labels' => $days,
            'issued' => $issuedPerDay,
            'returned' => $returnedPerDay,
        ];

        $dueTodayList = IssuedBook::with(['book', 'student.user'])
            ->whereNull('return_date')
            ->whereDate('due_date', $today->toDateString())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        $recentlyIssued = IssuedBook::with(['book', 'student.user'])
            ->whereNotNull('issue_date')
            ->latest('issue_date')
            ->limit(5)
            ->get();

        return view('Staff.dashboard', compact(
            'currentlyIssued',
            'dueToday',
            'overdueCount',
            'overdues',
            'pendingRequests',
            'pendingRequestsCount',
            'pendingFinesAmount',
            'circulationData',
            'activityData',
            'dueTodayList',
            'recentlyIssued'
        ));
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
}
