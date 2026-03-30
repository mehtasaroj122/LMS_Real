<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\book as Book;
use App\Models\category as Category;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\student as Student;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-admin');

        $filters = $this->resolveFilters($request);

        $inventoryReport = $this->buildInventoryReport($filters);
        $transactionsReport = $this->buildTransactionsReport($filters);
        $finesReport = $this->buildFinesReport($filters);
        $usersReport = $this->buildUsersReport($filters);
        $overdueReport = $this->buildOverdueReport();

        $reportCharts = [
            'inventory' => $inventoryReport['charts'],
            'transactions' => $transactionsReport['charts'],
            'fines' => $finesReport['charts'],
            'users' => $usersReport['charts'],
            'overdue' => $overdueReport['charts'],
        ];

        return view('Admin.ReportsDynamic', [
            'filters' => $filters,
            'inventoryReport' => $inventoryReport,
            'transactionsReport' => $transactionsReport,
            'finesReport' => $finesReport,
            'usersReport' => $usersReport,
            'overdueReport' => $overdueReport,
            'reportCharts' => $reportCharts,
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $allowedReportTypes = ['inventory', 'transactions', 'fines', 'users', 'overdue'];
        $allowedTimePeriods = ['7days', '30days', '90days', 'year', 'custom'];

        $reportType = $request->string('report_type')->toString();
        $reportType = in_array($reportType, $allowedReportTypes, true) ? $reportType : 'inventory';

        $timePeriod = $request->string('time_period')->toString();
        $timePeriod = in_array($timePeriod, $allowedTimePeriods, true) ? $timePeriod : '30days';

        $now = Carbon::now();
        $startDateInput = trim((string) $request->input('start_date', ''));
        $endDateInput = trim((string) $request->input('end_date', ''));

        switch ($timePeriod) {
            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case '90days':
                $startDate = $now->copy()->subDays(89)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'year':
                $startDate = $now->copy()->subYear()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'custom':
                $startDate = $this->parseDateInput($startDateInput)?->startOfDay() ?? $now->copy()->startOfMonth()->startOfDay();
                $endDate = $this->parseDateInput($endDateInput)?->endOfDay() ?? $now->copy()->endOfDay();

                if ($startDate->gt($endDate)) {
                    [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
                }

                $startDateInput = $startDate->toDateString();
                $endDateInput = $endDate->toDateString();
                break;
            case '30days':
            default:
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
        }

        return [
            'reportType' => $reportType,
            'timePeriod' => $timePeriod,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'startDateInput' => $startDateInput,
            'endDateInput' => $endDateInput,
            'periodLabel' => $this->formatPeriodLabel($timePeriod, $startDate, $endDate),
            'periodDays' => $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1,
            'showCustomRange' => $timePeriod === 'custom',
        ];
    }

    private function buildInventoryReport(array $filters): array
    {
        $categoryBreakdown = Category::withCount('books')
            ->orderByDesc('books_count')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => (int) $category->books_count,
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();

        $conditionCounts = Book::query()
            ->selectRaw('LOWER(`condition`) as condition_name, COUNT(*) as total')
            ->groupBy('condition')
            ->pluck('total', 'condition_name');

        $conditionBreakdown = collect([
            ['key' => 'new', 'label' => 'New', 'badge_class' => 'badge-info'],
            ['key' => 'good', 'label' => 'Good', 'badge_class' => 'badge-success'],
            ['key' => 'damaged', 'label' => 'Damaged', 'badge_class' => 'badge-warning'],
        ])->map(function (array $condition) use ($conditionCounts) {
            $condition['count'] = (int) ($conditionCounts[$condition['key']] ?? 0);

            return $condition;
        })->filter(fn (array $condition) => $condition['count'] > 0)->values();

        return [
            'stats' => [
                'total_books' => Book::count(),
                'total_copies' => (int) Book::sum('total_copies'),
                'available_copies' => (int) Book::sum('available_copies'),
                'current_issued' => IssuedBook::whereNull('return_date')->count(),
                'recent_additions' => Book::whereBetween('created_at', [$filters['startDate'], $filters['endDate']])->count(),
            ],
            'category_rows' => $categoryBreakdown->all(),
            'condition_rows' => $conditionBreakdown->all(),
            'charts' => [
                'category' => [
                    'labels' => $categoryBreakdown->pluck('name')->all(),
                    'data' => $categoryBreakdown->pluck('count')->all(),
                ],
                'condition' => [
                    'labels' => $conditionBreakdown->pluck('label')->all(),
                    'data' => $conditionBreakdown->pluck('count')->all(),
                ],
            ],
        ];
    }

    private function buildTransactionsReport(array $filters): array
    {
        $issuesInRange = IssuedBook::with('book')
            ->whereBetween('issue_date', [$filters['startDate']->toDateString(), $filters['endDate']->toDateString()])
            ->get();

        $returnsInRange = IssuedBook::query()
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [$filters['startDate']->toDateString(), $filters['endDate']->toDateString()])
            ->get();

        $mostIssuedBooks = $issuesInRange
            ->filter(fn (IssuedBook $issuedBook) => $issuedBook->book !== null)
            ->groupBy('book_id')
            ->map(function (Collection $items, $bookId) {
                $book = $items->first()->book;

                return [
                    'book_id' => $bookId,
                    'title' => $book?->title ?? 'Unknown Book',
                    'author' => $book?->author ?: 'Unknown',
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $averageIssueDuration = round((float) ($returnsInRange->avg(function (IssuedBook $issuedBook) {
            if (!$issuedBook->issue_date || !$issuedBook->return_date) {
                return 0;
            }

            return $issuedBook->issue_date->diffInDays($issuedBook->return_date);
        }) ?? 0), 1);

        return [
            'stats' => [
                'books_issued' => $issuesInRange->count(),
                'books_returned' => $returnsInRange->count(),
                'currently_issued' => IssuedBook::whereNull('return_date')->count(),
                'average_issue_duration' => $averageIssueDuration,
            ],
            'most_issued_books' => $mostIssuedBooks->take(5)->all(),
            'activity_chart_title' => $filters['periodDays'] > 90 ? 'Activity Trend' : 'Daily Activity Trend',
            'activity_chart_description' => $filters['periodDays'] > 90
                ? 'Issue and return activity recorded within ' . $filters['periodLabel']
                : 'Daily book issues and returns within ' . $filters['periodLabel'],
            'charts' => [
                'monthly_circulation' => $this->buildMonthlyCirculationChart(),
                'borrowed_books' => [
                    'labels' => $mostIssuedBooks->take(10)->pluck('title')->all(),
                    'data' => $mostIssuedBooks->take(10)->pluck('count')->all(),
                ],
                'activity' => $this->buildIssueReturnRangeChart($filters['startDate'], $filters['endDate']),
            ],
        ];
    }

    private function buildFinesReport(array $filters): array
    {
        $studentFineQuery = $this->studentFineQuery();

        $generatedInRange = (clone $studentFineQuery)
            ->whereBetween('created_at', [$filters['startDate'], $filters['endDate']])
            ->sum('amount');

        $collectedInRange = (clone $studentFineQuery)
            ->whereRaw('LOWER(status) = ?', ['paid'])
            ->whereNotNull('paid_on')
            ->whereBetween('paid_on', [$filters['startDate'], $filters['endDate']])
            ->sum('amount');

        $pendingInRange = (clone $studentFineQuery)
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereBetween('created_at', [$filters['startDate'], $filters['endDate']])
            ->sum('amount');

        $waivedInRange = (clone $studentFineQuery)
            ->whereRaw('LOWER(status) = ?', ['waived'])
            ->whereBetween('updated_at', [$filters['startDate'], $filters['endDate']])
            ->sum('amount');

        $topDefaulters = (clone $studentFineQuery)
            ->with('student.user')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->get()
            ->groupBy('student_id')
            ->map(function (Collection $fines) {
                $student = $fines->first()?->student;

                return [
                    'student_name' => $student?->user?->name ?? 'Unknown Student',
                    'student_id' => $student?->roll_no ?? 'N/A',
                    'pending_amount' => round((float) $fines->sum('amount'), 2),
                ];
            })
            ->sortByDesc('pending_amount')
            ->take(5)
            ->values();

        $collectionOverviewChart = $this->buildFineCollectionChart();

        return [
            'stats' => [
                'generated' => round((float) $generatedInRange, 2),
                'collected' => round((float) $collectedInRange, 2),
                'pending' => round((float) $pendingInRange, 2),
                'waived' => round((float) $waivedInRange, 2),
            ],
            'top_defaulters' => $topDefaulters->all(),
            'charts' => [
                'collection_overview' => $collectionOverviewChart,
                'efficiency' => [
                    'labels' => $collectionOverviewChart['labels'],
                    'generated' => $collectionOverviewChart['generated'],
                    'collected' => $collectionOverviewChart['collected'],
                ],
            ],
        ];
    }

    private function buildUsersReport(array $filters): array
    {
        $roleLabels = collect([
            'admin' => 'Admin',
            'staff' => 'Staff',
            'student' => 'Student',
        ]);

        $roleCounts = User::query()
            ->selectRaw('LOWER(role) as role_name, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role_name');

        $usersByRole = $roleLabels->map(function (string $label, string $role) use ($roleCounts) {
            return [
                'role' => $label,
                'count' => (int) ($roleCounts[$role] ?? 0),
            ];
        })->values();

        $mostActiveReaders = Student::with('user')
            ->withCount([
                'issuedBooks as issued_books_count' => function (Builder $query) use ($filters) {
                    $query->whereBetween('issue_date', [$filters['startDate']->toDateString(), $filters['endDate']->toDateString()]);
                },
            ])
            ->get()
            ->filter(fn (Student $student) => ($student->issued_books_count ?? 0) > 0)
            ->sortByDesc('issued_books_count')
            ->take(5)
            ->values()
            ->map(function (Student $student) {
                return [
                    'student_name' => $student->user?->name ?? 'Unknown Student',
                    'issued_count' => (int) ($student->issued_books_count ?? 0),
                ];
            });

        return [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::whereRaw('LOWER(status) = ?', ['active'])->count(),
                'inactive_users' => User::whereRaw('LOWER(status) = ?', ['inactive'])->count(),
                'new_users' => User::whereBetween('created_at', [$filters['startDate'], $filters['endDate']])->count(),
            ],
            'users_by_role' => $usersByRole->all(),
            'most_active_readers' => $mostActiveReaders->all(),
            'charts' => [
                'users_by_role' => [
                    'labels' => $usersByRole->pluck('role')->all(),
                    'data' => $usersByRole->pluck('count')->all(),
                ],
                'activity_by_role' => $this->buildUserActivityChart($filters['startDate'], $filters['endDate']),
            ],
        ];
    }

    private function buildOverdueReport(): array
    {
        $today = Carbon::today();

        $overdueBooks = IssuedBook::with(['student.user', 'book', 'fine'])
            ->whereNull('return_date')
            ->whereDate('due_date', '<', $today)
            ->get()
            ->map(function (IssuedBook $issuedBook) use ($today) {
                $daysOverdue = $issuedBook->due_date->diffInDays($today);
                $fineAmount = (float) ($issuedBook->fine?->amount ?? $issuedBook->fine_amount ?? 0);

                return [
                    'student_name' => $issuedBook->student?->user?->name ?? 'Unknown Student',
                    'book_title' => $issuedBook->book?->title ?? 'Unknown Book',
                    'days_overdue' => $daysOverdue,
                    'fine_amount' => round($fineAmount, 2),
                    'severity_color' => $this->overdueSeverityColor($daysOverdue),
                    'due_month_key' => $issuedBook->due_date->format('Y-m'),
                ];
            })
            ->sortByDesc('days_overdue')
            ->values();

        $distributionBuckets = collect([
            ['label' => '1-7 Days', 'min' => 1, 'max' => 7],
            ['label' => '8-14 Days', 'min' => 8, 'max' => 14],
            ['label' => '15-21 Days', 'min' => 15, 'max' => 21],
            ['label' => '22-30 Days', 'min' => 22, 'max' => 30],
            ['label' => '31+ Days', 'min' => 31, 'max' => PHP_INT_MAX],
        ])->map(function (array $bucket) use ($overdueBooks) {
            $bucket['count'] = $overdueBooks->filter(function (array $book) use ($bucket) {
                return $book['days_overdue'] >= $bucket['min'] && $book['days_overdue'] <= $bucket['max'];
            })->count();

            return $bucket;
        });

        $criticalOverdue = $overdueBooks->filter(fn (array $book) => $book['days_overdue'] >= 30)->count();
        $averageDaysOverdue = round((float) ($overdueBooks->avg('days_overdue') ?? 0), 1);
        $totalFineAmount = round((float) $overdueBooks->sum('fine_amount'), 2);

        return [
            'stats' => [
                'total_overdue' => $overdueBooks->count(),
                'critical_overdue' => $criticalOverdue,
                'total_fine_amount' => $totalFineAmount,
                'average_days_overdue' => $averageDaysOverdue,
            ],
            'overdue_books' => $overdueBooks->all(),
            'charts' => [
                'distribution' => [
                    'labels' => $distributionBuckets->pluck('label')->all(),
                    'data' => $distributionBuckets->pluck('count')->all(),
                ],
                'trend' => $this->buildOverdueTrendChart($overdueBooks),
            ],
        ];
    }

    private function buildMonthlyCirculationChart(): array
    {
        $months = $this->monthSequence(12);
        $startMonth = $months->first()->copy()->startOfMonth();

        $issuesByMonth = IssuedBook::query()
            ->whereDate('issue_date', '>=', $startMonth)
            ->get()
            ->groupBy(fn (IssuedBook $issuedBook) => $issuedBook->issue_date->format('Y-m'));

        $returnsByMonth = IssuedBook::query()
            ->whereNotNull('return_date')
            ->whereDate('return_date', '>=', $startMonth)
            ->get()
            ->groupBy(fn (IssuedBook $issuedBook) => $issuedBook->return_date->format('Y-m'));

        return [
            'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->all(),
            'issues' => $months->map(fn (Carbon $month) => $issuesByMonth->get($month->format('Y-m'), collect())->count())->all(),
            'returns' => $months->map(fn (Carbon $month) => $returnsByMonth->get($month->format('Y-m'), collect())->count())->all(),
        ];
    }

    private function buildIssueReturnRangeChart(Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());

        $issuesByDay = IssuedBook::query()
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->countBy(fn (IssuedBook $issuedBook) => $issuedBook->issue_date->format('Y-m-d'));

        $returnsByDay = IssuedBook::query()
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->countBy(fn (IssuedBook $issuedBook) => $issuedBook->return_date->format('Y-m-d'));

        $labels = [];
        $issueSeries = [];
        $returnSeries = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('M j');
            $issueSeries[] = (int) ($issuesByDay[$key] ?? 0);
            $returnSeries[] = (int) ($returnsByDay[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'issues' => $issueSeries,
            'returns' => $returnSeries,
        ];
    }

    private function buildFineCollectionChart(): array
    {
        $months = $this->monthSequence(12);
        $startMonth = $months->first()->copy()->startOfMonth();

        $studentFineQuery = $this->studentFineQuery();

        $generatedByMonth = (clone $studentFineQuery)
            ->where('created_at', '>=', $startMonth)
            ->get()
            ->groupBy(fn (Fine $fine) => $fine->created_at->format('Y-m'));

        $collectedByMonth = (clone $studentFineQuery)
            ->whereRaw('LOWER(status) = ?', ['paid'])
            ->whereNotNull('paid_on')
            ->whereDate('paid_on', '>=', $startMonth)
            ->get()
            ->groupBy(fn (Fine $fine) => $fine->paid_on->format('Y-m'));

        $pendingByMonth = $generatedByMonth->map(function (Collection $fines) {
            return $fines->filter(fn (Fine $fine) => strtolower((string) $fine->status) === 'pending');
        });

        return [
            'labels' => $months->map(fn (Carbon $month) => $month->format('M'))->all(),
            'generated' => $months->map(function (Carbon $month) use ($generatedByMonth) {
                return round((float) $generatedByMonth->get($month->format('Y-m'), collect())->sum('amount'), 2);
            })->all(),
            'collected' => $months->map(function (Carbon $month) use ($collectedByMonth) {
                return round((float) $collectedByMonth->get($month->format('Y-m'), collect())->sum('amount'), 2);
            })->all(),
            'pending' => $months->map(function (Carbon $month) use ($pendingByMonth) {
                return round((float) $pendingByMonth->get($month->format('Y-m'), collect())->sum('amount'), 2);
            })->all(),
        ];
    }

    private function buildUserActivityChart(Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay());
        $roles = ['admin', 'staff', 'student'];

        $activityLogs = ActivityLog::with('user:id,role')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($activity) => $activity->created_at->format('Y-m-d'));

        $labels = [];
        $series = [
            'admin' => [],
            'staff' => [],
            'student' => [],
        ];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $logsForDay = $activityLogs->get($key, collect());
            $labels[] = $date->format('M j');

            foreach ($roles as $role) {
                $series[$role][] = $logsForDay->filter(function ($activity) use ($role) {
                    $activityRole = strtolower((string) ($activity->user_role ?: $activity->user?->role ?: ''));

                    return $activityRole === $role;
                })->count();
            }
        }

        return [
            'labels' => $labels,
            'admin' => $series['admin'],
            'staff' => $series['staff'],
            'student' => $series['student'],
        ];
    }

    private function buildOverdueTrendChart(Collection $overdueBooks): array
    {
        $months = $this->monthSequence(12);
        $startMonth = $months->first()->copy()->startOfMonth();
        $today = Carbon::today();

        $overdueIncidents = IssuedBook::query()
            ->whereDate('due_date', '>=', $startMonth)
            ->get()
            ->filter(function (IssuedBook $issuedBook) use ($today) {
                if ($issuedBook->return_date !== null) {
                    return $issuedBook->return_date->gt($issuedBook->due_date);
                }

                return $issuedBook->due_date->lt($today);
            })
            ->groupBy(fn (IssuedBook $issuedBook) => $issuedBook->due_date->format('Y-m'));

        $criticalCurrentByMonth = $overdueBooks
            ->filter(fn (array $book) => $book['days_overdue'] >= 30)
            ->groupBy('due_month_key');

        return [
            'labels' => $months->map(fn (Carbon $month) => $month->format('M'))->all(),
            'total' => $months->map(fn (Carbon $month) => $overdueIncidents->get($month->format('Y-m'), collect())->count())->all(),
            'critical' => $months->map(fn (Carbon $month) => $criticalCurrentByMonth->get($month->format('Y-m'), collect())->count())->all(),
        ];
    }

    private function studentFineQuery(): Builder
    {
        return Fine::query()->whereHas('student.user', function (Builder $query) {
            $query->whereRaw('LOWER(role) = ?', ['student']);
        });
    }

    private function monthSequence(int $count): Collection
    {
        return collect(range($count - 1, 0))
            ->map(fn (int $index) => Carbon::now()->startOfMonth()->subMonths($index));
    }

    private function parseDateInput(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function formatPeriodLabel(string $timePeriod, Carbon $startDate, Carbon $endDate): string
    {
        return match ($timePeriod) {
            '7days' => 'Last 7 Days',
            '30days' => 'Last 30 Days',
            '90days' => 'Last 90 Days',
            'year' => 'Last Year',
            'custom' => $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y'),
            default => 'Last 30 Days',
        };
    }

    private function overdueSeverityColor(int $daysOverdue): string
    {
        if ($daysOverdue >= 30) {
            return '#ef4444';
        }

        if ($daysOverdue >= 15) {
            return '#f97316';
        }

        return '#fbbf24';
    }
}
