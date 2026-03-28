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
use Illuminate\Support\Collection;
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

        // Reserved books (pending requests)
        $reservedBooks = BookRequest::whereRaw('LOWER(status) = ?', ['pending'])->count();

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

        // Waived fines (waived) - only for students
        $waivedFines = Fine::whereHas('student.user', function ($q) {
            $q->where('role', 'student');
        })->whereRaw('LOWER(status) = ?', ['waived'])->sum('amount');

        $fineStatusLegend = [
            [
                'label' => 'Pending',
                'amount' => (float) $pendingFines,
                'color' => '#ef4444',
            ],
            [
                'label' => 'Collected',
                'amount' => (float) $collectedFines,
                'color' => '#10b981',
            ],
            [
                'label' => 'Waived',
                'amount' => (float) $waivedFines,
                'color' => '#f59e0b',
            ],
        ];

        $fineStatusTotal = collect($fineStatusLegend)->sum('amount');
        $fineStatusLegend = collect($fineStatusLegend)
            ->map(function ($item) use ($fineStatusTotal) {
                $item['percentage'] = $fineStatusTotal > 0
                    ? round(($item['amount'] / $fineStatusTotal) * 100, 1)
                    : 0;

                return $item;
            })
            ->all();


        /* =========================
         | TODAY'S ACTIVITY
         |=========================*/

        $todayActivities = ActivityLog::whereDate('created_at', Carbon::today())
            ->count();

        // Pending book requests (for admin overview)
        $pendingRequestsCount = $reservedBooks;


        /* =========================
         | RECENT DATA (Lists)
         |=========================*/

        // Pending fines list (for dashboard display)
        $pendingFinesList = Fine::with(['student.user', 'issuedBook.book'])
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $fineReportYearStart = Carbon::now()->startOfYear();
        $fineReportYearEnd = $fineReportYearStart->copy()->endOfYear();

        $monthlyFineMonths = collect(range(0, 11))->map(function ($monthIndex) use ($fineReportYearStart) {
            return $fineReportYearStart->copy()->addMonths($monthIndex);
        });

        $generatedFinesByMonth = Fine::whereHas('student.user', function ($q) {
                $q->where('role', 'student');
            })
            ->whereBetween('created_at', [$fineReportYearStart, $fineReportYearEnd])
            ->get()
            ->groupBy(function ($fine) {
                return Carbon::parse($fine->created_at)->format('Y-m');
            });

        $collectedFinesByMonth = Fine::whereHas('student.user', function ($q) {
                $q->where('role', 'student');
            })
            ->whereRaw('LOWER(status) = ?', ['paid'])
            ->whereNotNull('paid_on')
            ->whereBetween('paid_on', [$fineReportYearStart, $fineReportYearEnd])
            ->get()
            ->groupBy(function ($fine) {
                return Carbon::parse($fine->paid_on)->format('Y-m');
            });

        $waivedFinesByMonth = Fine::whereHas('student.user', function ($q) {
                $q->where('role', 'student');
            })
            ->whereRaw('LOWER(status) = ?', ['waived'])
            ->whereBetween('updated_at', [$fineReportYearStart, $fineReportYearEnd])
            ->get()
            ->groupBy(function ($fine) {
                return Carbon::parse($fine->updated_at)->format('Y-m');
            });

        $monthlyFineLabels = [];
        $monthlyPendingFines = [];
        $monthlyCollectedFines = [];
        $monthlyWaivedFines = [];

        foreach ($monthlyFineMonths as $month) {
            $key = $month->format('Y-m');
            $monthlyFineLabels[] = $month->format('M');
            $monthlyPendingFines[] = round((float) ($generatedFinesByMonth->get($key, collect())->filter(function ($fine) {
                return strtolower($fine->status) === 'pending';
            })->sum('amount')), 2);
            $monthlyCollectedFines[] = round((float) ($collectedFinesByMonth->get($key, collect())->sum('amount')), 2);
            $monthlyWaivedFines[] = round((float) ($waivedFinesByMonth->get($key, collect())->sum('amount')), 2);
        }

        $monthlyFineMax = max(
            100,
            (int) ceil(max(array_merge($monthlyPendingFines, $monthlyCollectedFines, $monthlyWaivedFines, [0])) / 100) * 100
        );

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
            'reservedBooks',
            'totalStudents',
            'pendingFines',
            'collectedFines',
            'waivedFines',
            'fineStatusLegend',
            'pendingRequestsCount',
            'todayActivities',
            'totalCategories',
            'pendingFinesList',
            'recentActivities',
            'monthlyFineLabels',
            'monthlyPendingFines',
            'monthlyCollectedFines',
            'monthlyWaivedFines',
            'monthlyFineMax'
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
