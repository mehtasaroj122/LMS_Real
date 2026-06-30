<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\Fine;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-admin');

        $selectedFineTrendPeriod = $this->resolveFineTrendPeriod($request->query('fine_period'));

        /* =========================
         | BASIC COUNTS
         |=========================*/

        // Total books (all records)
        $totalBooks = Book::count();

        // Total physical copies across all book records
        $totalBookCopies = Book::sum('total_copies');

        // Available copies (real availability)
        $availableBooks = Book::sum('available_copies');

        // Total students
        $totalStudents = Student::count();

        // Total users across all portal roles
        $totalUsers = User::count();
        $userRoleCounts = User::query()
            ->selectRaw('LOWER(role) as role, COUNT(*) as total')
            ->groupByRaw('LOWER(role)')
            ->pluck('total', 'role');

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

        $fineTrendViewData = $this->fineTrendViewData($selectedFineTrendPeriod);

        // Recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();


        /* =========================
         | PASS TO VIEW
         |=========================*/
        return view('Admin.dashboard', array_merge(
            $fineTrendViewData,
            compact(
                'totalBooks',
                'totalBookCopies',
                'availableBooks',
                'issuedBooks',
                'overdueBooks',
                'reservedBooks',
                'totalStudents',
                'totalUsers',
                'userRoleCounts',
                'pendingFines',
                'collectedFines',
                'waivedFines',
                'fineStatusLegend',
                'pendingRequestsCount',
                'todayActivities',
                'totalCategories',
                'pendingFinesList',
                'recentActivities'
            )
        ));
    }

    public function fineTrend(Request $request)
    {
        Gate::authorize('access-admin');

        $selectedFineTrendPeriod = $this->resolveFineTrendPeriod($request->query('fine_period'));

        return response()->json([
            'html' => view('Admin.partials.dashboard.fine-trend-card', $this->fineTrendViewData($selectedFineTrendPeriod))->render(),
            'period' => $selectedFineTrendPeriod,
        ]);
    }

    private function fineTrendPeriodOptions(): array
    {
        return [
            '7days' => 'Last 7 Days',
            '30days' => 'Last 30 Days',
            '12months' => 'Last 12 Months',
        ];
    }

    private function resolveFineTrendPeriod(?string $period): string
    {
        $fineTrendPeriodOptions = $this->fineTrendPeriodOptions();

        if (! is_string($period) || ! array_key_exists($period, $fineTrendPeriodOptions)) {
            return '12months';
        }

        return $period;
    }

    private function fineTrendViewData(string $selectedFineTrendPeriod): array
    {
        $fineTrendPeriodOptions = $this->fineTrendPeriodOptions();

        return array_merge(
            $this->buildFineTrendData($selectedFineTrendPeriod, $fineTrendPeriodOptions[$selectedFineTrendPeriod]),
            [
                'fineTrendPeriodOptions' => $fineTrendPeriodOptions,
                'selectedFineTrendPeriod' => $selectedFineTrendPeriod,
            ]
        );
    }

    private function buildFineTrendData(string $period, string $periodLabel): array
    {
        $studentFines = Fine::query()->whereHas('student.user', function ($query) {
            $query->whereRaw('LOWER(role) = ?', ['student']);
        });

        if ($period === '7days' || $period === '30days') {
            $dayCount = $period === '7days' ? 7 : 30;
            $days = collect(range($dayCount - 1, 0))->map(function (int $offset) {
                return Carbon::now()->startOfDay()->subDays($offset);
            });

            $startDate = $days->first()->copy()->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $generatedFines = (clone $studentFines)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($fine) {
                    return Carbon::parse($fine->created_at)->format('Y-m-d');
                });

            $collectedFines = (clone $studentFines)
                ->whereRaw('LOWER(status) = ?', ['paid'])
                ->whereNotNull('paid_on')
                ->whereBetween('paid_on', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($fine) {
                    return Carbon::parse($fine->paid_on)->format('Y-m-d');
                });

            $waivedFines = (clone $studentFines)
                ->whereRaw('LOWER(status) = ?', ['waived'])
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($fine) {
                    return Carbon::parse($fine->updated_at)->format('Y-m-d');
                });

            $labels = $days->map(function (Carbon $day) {
                return $day->format('M j, Y');
            })->all();

            $axisLabels = $days->map(function (Carbon $day, int $index) use ($dayCount) {
                if ($dayCount === 7) {
                    return $day->format('M j');
                }

                if ($index === 0 || $index === $dayCount - 1 || $index % 5 === 0) {
                    return $day->format('M j');
                }

                return '';
            })->all();

            $pendingSeries = $days->map(function (Carbon $day) use ($generatedFines) {
                $key = $day->format('Y-m-d');

                return round((float) ($generatedFines->get($key, collect())->filter(function ($fine) {
                    return strtolower((string) $fine->status) === 'pending';
                })->sum('amount')), 2);
            })->all();

            $collectedSeries = $days->map(function (Carbon $day) use ($collectedFines) {
                return round((float) ($collectedFines->get($day->format('Y-m-d'), collect())->sum('amount')), 2);
            })->all();

            $waivedSeries = $days->map(function (Carbon $day) use ($waivedFines) {
                return round((float) ($waivedFines->get($day->format('Y-m-d'), collect())->sum('amount')), 2);
            })->all();
        } else {
            $months = collect(range(11, 0))->map(function (int $offset) {
                return Carbon::now()->startOfMonth()->subMonths($offset);
            });

            $startMonth = $months->first()->copy()->startOfMonth();
            $endMonth = Carbon::now()->endOfMonth();

            $generatedFines = (clone $studentFines)
                ->whereBetween('created_at', [$startMonth, $endMonth])
                ->get()
                ->groupBy(function ($fine) {
                    return Carbon::parse($fine->created_at)->format('Y-m');
                });

            $collectedFines = (clone $studentFines)
                ->whereRaw('LOWER(status) = ?', ['paid'])
                ->whereNotNull('paid_on')
                ->whereBetween('paid_on', [$startMonth, $endMonth])
                ->get()
                ->groupBy(function ($fine) {
                    return Carbon::parse($fine->paid_on)->format('Y-m');
                });

            $waivedFines = (clone $studentFines)
                ->whereRaw('LOWER(status) = ?', ['waived'])
                ->whereBetween('updated_at', [$startMonth, $endMonth])
                ->get()
                ->groupBy(function ($fine) {
                    return Carbon::parse($fine->updated_at)->format('Y-m');
                });

            $labels = $months->map(function (Carbon $month) {
                return $month->format('M Y');
            })->all();

            $axisLabels = $months->map(function (Carbon $month) {
                return $month->format('M');
            })->all();

            $pendingSeries = $months->map(function (Carbon $month) use ($generatedFines) {
                $key = $month->format('Y-m');

                return round((float) ($generatedFines->get($key, collect())->filter(function ($fine) {
                    return strtolower((string) $fine->status) === 'pending';
                })->sum('amount')), 2);
            })->all();

            $collectedSeries = $months->map(function (Carbon $month) use ($collectedFines) {
                return round((float) ($collectedFines->get($month->format('Y-m'), collect())->sum('amount')), 2);
            })->all();

            $waivedSeries = $months->map(function (Carbon $month) use ($waivedFines) {
                return round((float) ($waivedFines->get($month->format('Y-m'), collect())->sum('amount')), 2);
            })->all();
        }

        return [
            'fineTrendLabels' => $labels,
            'fineTrendAxisLabels' => $axisLabels,
            'fineTrendPending' => $pendingSeries,
            'fineTrendCollected' => $collectedSeries,
            'fineTrendWaived' => $waivedSeries,
            'fineTrendMax' => max(
                100,
                (int) ceil(max(array_merge($pendingSeries, $collectedSeries, $waivedSeries, [0])) / 100) * 100
            ),
            'fineTrendSubtitle' => 'Fine generation and collection trend over the ' . strtolower($periodLabel),
        ];
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
