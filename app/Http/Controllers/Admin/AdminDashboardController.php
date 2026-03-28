<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\book;
use App\Models\category;
use App\Models\Fine;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-admin');
        /* =========================
         | BASIC COUNTS
         |=========================*/

        // Total books (all records)
        $totalBooks = Book::count();

        // Available copies (real availability)
        $availableBooks = Book::sum('available_copies');

        // Total students
        $totalStudents = Student::count();

        // Book categories
        $totalCategories = Category::count();


        /* =========================
         | ISSUE & OVERDUE LOGIC
         |=========================*/

        // Currently issued books (not returned)
        $issuedBooks = IssuedBook::where('status', 'issued')->count();

        // Overdue books (due date passed & not returned)
        $overdueBooks = IssuedBook::where('status', 'issued')
            ->whereDate('due_date', '<', Carbon::today())
            ->count();


        /* =========================
         | FINES LOGIC
         |=========================*/

        // Pending fines (not paid) - only for students to match fines view logic
        $pendingFines = Fine::whereHas('student.user', function ($q) {
            $q->where('role', 'student');
        })->whereRaw('LOWER(status) = ?', ['pending'])->sum('amount');

        // Collected fines (paid) - only for students
        $collectedFines = Fine::whereHas('student.user', function ($q) {
            $q->where('role', 'student');
        })->whereRaw('LOWER(status) = ?', ['paid'])->sum('amount');


        /* =========================
         | TODAY'S ACTIVITY
         |=========================*/

        $todayActivities = ActivityLog::whereDate('created_at', Carbon::today())
            ->count();

        // Pending book requests (for admin overview)
        $pendingRequestsCount = BookRequest::where('status', 'pending')->count();


        /* =========================
         | RECENT DATA (Lists)
         |=========================*/

        // Pending fines list (for dashboard display)
        $pendingFinesList = Fine::with(['student.user', 'issuedBook.book'])
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();


        /* =========================
         | PASS TO VIEW
         |=========================*/
        return view('Admin.dashboard', compact(
            'totalBooks',
            'availableBooks',
            'issuedBooks',
            'overdueBooks',
            'totalStudents',
            'pendingFines',
            'collectedFines',
            'pendingRequestsCount',
            'todayActivities',
            'totalCategories',
            'pendingFinesList',
            'recentActivities'
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
