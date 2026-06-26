<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\Book;
use App\Models\IssuedBook;
use App\Models\BookRequest;
use App\Models\Fine;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    private const DASHBOARD_LIST_BATCH = 10;

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

        $overdues = $this->overdueBooksQuery($today)
            ->limit(self::DASHBOARD_LIST_BATCH)
            ->get();

        $overdues->each(function (IssuedBook $issuedBook) {
            $issuedBook->setAttribute(
                'dashboard_overdue_days',
                $this->resolveOverdueDays($issuedBook->due_date)
            );
        });

        $pendingRequestsBaseQuery = $this->pendingRequestsQuery();

        $pendingRequests = (clone $pendingRequestsBaseQuery)
            ->orderBy('request_date', 'desc')
            ->limit(self::DASHBOARD_LIST_BATCH)
            ->get();

        $pendingRequestsCount = (clone $pendingRequestsBaseQuery)->count();

        // Count only fines that are actually pending (exclude paid/waived)
        // Match the logic used in Staff\FineController::getFinesData by restricting to student users
        $pendingFinesAmount = Fine::whereHas('student.user', function($q) {
            $q->where('role', 'student');
        })->whereRaw('LOWER(status) = ?', ['pending'])->sum('amount');

        $availableBooks = Book::sum('available_copies');
        $issuedOnTime = max($currentlyIssued - $overdueCount - $dueToday, 0);
        $circulationTotal = $availableBooks + $currentlyIssued;

        $circulationData = [
            'labels' => ['Available', 'Issued On Time', 'Due Today', 'Overdue'],
            'data' => [$availableBooks, $issuedOnTime, $dueToday, $overdueCount],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        ];

        $circulationOverview = [
            'total' => $circulationTotal,
            'available' => $availableBooks,
            'issued' => $currentlyIssued,
            'issuedOnTime' => $issuedOnTime,
            'dueToday' => $dueToday,
            'overdue' => $overdueCount,
            'attentionNeeded' => $dueToday + $overdueCount,
            'availabilityRate' => $circulationTotal > 0
                ? (int) round(($availableBooks / $circulationTotal) * 100)
                : 0,
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

        $movementsPerDay = array_map(
            fn (int $issued, int $returned): int => $issued + $returned,
            $issuedPerDay,
            $returnedPerDay
        );

        $issuedTotal = array_sum($issuedPerDay);
        $returnedTotal = array_sum($returnedPerDay);
        $movementsTotal = array_sum($movementsPerDay);
        $peakActivityCount = !empty($movementsPerDay) ? max($movementsPerDay) : 0;
        $peakActivityIndex = array_search($peakActivityCount, $movementsPerDay, true);

        $activityOverview = [
            'issuedTotal' => $issuedTotal,
            'returnedTotal' => $returnedTotal,
            'movementsTotal' => $movementsTotal,
            'averagePerDay' => count($days) > 0 ? round($movementsTotal / count($days), 1) : 0,
            'peakDay' => [
                'label' => $movementsTotal > 0 && $peakActivityIndex !== false ? ($days[$peakActivityIndex] ?? '—') : '—',
                'count' => $peakActivityCount,
            ],
        ];

        $dueTodayList = $this->dueTodayQuery($today)
            ->limit(self::DASHBOARD_LIST_BATCH)
            ->get();

        $recentlyIssuedBaseQuery = $this->recentlyIssuedQuery();

        $recentlyIssued = (clone $recentlyIssuedBaseQuery)
            ->latest('issue_date')
            ->limit(self::DASHBOARD_LIST_BATCH)
            ->get();

        $recentlyIssuedCount = (clone $recentlyIssuedBaseQuery)->count();

        return view('Staff.dashboard', compact(
            'currentlyIssued',
            'dueToday',
            'overdueCount',
            'overdues',
            'pendingRequests',
            'pendingRequestsCount',
            'pendingFinesAmount',
            'circulationData',
            'circulationOverview',
            'activityData',
            'activityOverview',
            'dueTodayList',
            'recentlyIssued',
            'recentlyIssuedCount'
        ));
    }

    public function loadMoreList(Request $request)
    {
        Gate::authorize('access-staff');

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:pending_requests,due_today,recent_issues,overdue_books'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);

        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? self::DASHBOARD_LIST_BATCH);
        $today = Carbon::today();

        [$items, $total] = match ($validated['type']) {
            'pending_requests' => $this->getPendingRequestsBatch($offset, $limit),
            'due_today' => $this->getDueTodayBatch($today, $offset, $limit),
            'recent_issues' => $this->getRecentIssuesBatch($offset, $limit),
            'overdue_books' => $this->getOverdueBooksBatch($today, $offset, $limit),
        };

        return response()->json([
            'success' => true,
            'items' => $items,
            'total' => $total,
            'nextOffset' => $offset + count($items),
            'hasMore' => ($offset + count($items)) < $total,
        ]);
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

    private function pendingRequestsQuery()
    {
        return BookRequest::with(['book', 'student.user'])
            ->whereHas('student.user', function ($query) {
                $query->where('role', 'student');
            })
            ->where('status', 'pending');
    }

    private function dueTodayQuery(Carbon $today)
    {
        return IssuedBook::with(['book', 'student.user'])
            ->whereNull('return_date')
            ->whereDate('due_date', $today->toDateString())
            ->orderBy('due_date', 'asc');
    }

    private function overdueBooksQuery(Carbon $today)
    {
        return IssuedBook::with(['book', 'student.user'])
            ->whereNull('return_date')
            ->whereDate('due_date', '<', $today->toDateString())
            ->orderBy('due_date', 'asc');
    }

    private function recentlyIssuedQuery()
    {
        return IssuedBook::with(['book', 'student.user'])
            ->whereNotNull('issue_date');
    }

    private function getPendingRequestsBatch(int $offset, int $limit): array
    {
        $query = $this->pendingRequestsQuery();
        $total = (clone $query)->count();

        $items = (clone $query)
            ->orderBy('request_date', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (BookRequest $request) => [
                'id' => $request->id,
                'book' => [
                    'title' => $request->book?->title ?? 'Untitled',
                ],
                'student' => [
                    'name' => $request->student?->user?->name ?? 'Unknown',
                    'student_id' => $request->student?->student_id
                        ?: $request->student?->roll_no
                        ?: 'No ID',
                ],
                'request_date' => $request->request_date?->format('M d, Y') ?? 'N/A',
            ])
            ->values()
            ->all();

        return [$items, $total];
    }

    private function getDueTodayBatch(Carbon $today, int $offset, int $limit): array
    {
        $query = $this->dueTodayQuery($today);
        $total = (clone $query)->count();

        $items = (clone $query)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (IssuedBook $issuedBook) => [
                'book_title' => $issuedBook->book?->title ?? 'Untitled',
                'student_name' => $issuedBook->student?->user?->name ?? 'Unknown',
                'due_badge' => 'Due today',
                'due_date' => $issuedBook->due_date?->format('M d, Y') ?? 'N/A',
            ])
            ->values()
            ->all();

        return [$items, $total];
    }

    private function getRecentIssuesBatch(int $offset, int $limit): array
    {
        $query = $this->recentlyIssuedQuery();
        $total = (clone $query)->count();

        $items = (clone $query)
            ->latest('issue_date')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (IssuedBook $issuedBook) => [
                'book_title' => $issuedBook->book?->title ?? 'Untitled',
                'student_name' => $issuedBook->student?->user?->name ?? 'Unknown',
                'time_ago' => $issuedBook->issue_date?->diffForHumans() ?? 'Recently',
                'issued_on' => $issuedBook->issue_date?->format('M d, Y') ?? 'N/A',
            ])
            ->values()
            ->all();

        return [$items, $total];
    }

    private function getOverdueBooksBatch(Carbon $today, int $offset, int $limit): array
    {
        $query = $this->overdueBooksQuery($today);
        $total = (clone $query)->count();

        $items = (clone $query)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (IssuedBook $issuedBook) => [
                'book_title' => $issuedBook->book?->title ?? 'Untitled',
                'student_name' => $issuedBook->student?->user?->name ?? 'Unknown',
                'due_date' => $issuedBook->due_date?->format('M d, Y') ?? 'N/A',
                'days_overdue' => $this->resolveOverdueDays($issuedBook->due_date),
            ])
            ->values()
            ->all();

        return [$items, $total];
    }

    private function resolveOverdueDays($dueDate): int
    {
        if (!$dueDate) {
            return 0;
        }

        return max(
            0,
            (int) round(
                Carbon::parse($dueDate)
                    ->startOfDay()
                    ->diffInDays(Carbon::today()->startOfDay())
            )
        );
    }
}
