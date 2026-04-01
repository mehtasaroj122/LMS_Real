<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\FineSetting;
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

        // Effective privilege settings
        $studentPrivilege = $student?->privilege;
        $globalSettings = FineSetting::resolveActive();

        $maxBooks = $studentPrivilege?->max_books
            ?? $globalSettings->max_books_per_student
            ?? 5;

        $privilegeSettings = [
            'max_books' => $maxBooks,
            'issue_duration_days' => $studentPrivilege?->issue_duration_days
                ?? $globalSettings->issue_duration_days
                ?? 14,
            'per_day_fine' => $studentPrivilege?->per_day_fine
                ?? $globalSettings->per_day_fine
                ?? 5.00,
            'borrowing_allowed' => $studentPrivilege?->borrowing_allowed ?? true,
            'is_custom' => $studentPrivilege !== null,
            'books_issued' => $booksIssuedCount,
            'remaining_slots' => max(0, $maxBooks - $booksIssuedCount),
        ];

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

        // Request status overview
        $requestStatusCounts = $student
            ? $student->bookRequests()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        // Notifications
        $notifications = $user->notifications()->latest()->get();

        // CHART 1: Monthly Activity (Last 30 days)
        $activityStartDate = Carbon::today()->subDays(29)->startOfDay();
        $activityDates = collect(range(29, 0))->map(
            fn (int $daysAgo) => Carbon::today()->subDays($daysAgo)->startOfDay()
        );

        $issuedByDate = $student
            ? $student->issuedBooks()
                ->whereDate('issue_date', '>=', $activityStartDate->toDateString())
                ->selectRaw('DATE(issue_date) as activity_date, COUNT(*) as total')
                ->groupBy('activity_date')
                ->pluck('total', 'activity_date')
            : collect();

        $returnedByDate = $student
            ? $student->issuedBooks()
                ->whereNotNull('return_date')
                ->whereDate('return_date', '>=', $activityStartDate->toDateString())
                ->selectRaw('DATE(return_date) as activity_date, COUNT(*) as total')
                ->groupBy('activity_date')
                ->pluck('total', 'activity_date')
            : collect();

        $activityLabels = $activityDates
            ->map(fn (Carbon $date) => $date->format('M j'))
            ->all();

        $monthlyIssued = $activityDates
            ->map(fn (Carbon $date) => (int) ($issuedByDate[$date->toDateString()] ?? 0))
            ->all();

        $monthlyReturned = $activityDates
            ->map(fn (Carbon $date) => (int) ($returnedByDate[$date->toDateString()] ?? 0))
            ->all();

        $monthlyActivity = [
            'labels' => $activityLabels,
            'issued' => $monthlyIssued,
            'returned' => $monthlyReturned,
        ];

        $movementsTotal = array_sum($monthlyIssued) + array_sum($monthlyReturned);
        $activityOverview = [
            'issued_total' => array_sum($monthlyIssued),
            'returned_total' => array_sum($monthlyReturned),
            'movements_total' => $movementsTotal,
        ];

        $requestStatusMeta = collect([
            'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
            'approved' => ['label' => 'Approved', 'color' => '#8b5cf6'],
            'issued' => ['label' => 'Issued', 'color' => '#d946ef'],
            'returned' => ['label' => 'Returned', 'color' => '#5bf707'],
            'rejected' => ['label' => 'Rejected', 'color' => '#ef4444'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#64748b'],
        ]);
        $requestStatusBuckets = collect([
            'pending' => (int) $requestStatusCounts->get('pending', 0),
            'approved' => (int) $requestStatusCounts->get('approved', 0),
            'issued' => (int) $requestStatusCounts->get('issued', 0),
            'returned' => (int) $requestStatusCounts->get('returned', 0),
            'rejected' => (int) $requestStatusCounts->get('rejected', 0),
            'cancelled' => (int) $requestStatusCounts->get('cancelled', 0),
        ]);

        $requestStatusSummary = $requestStatusMeta
            ->map(function (array $meta, string $status) use ($requestStatusBuckets) {
                return [
                    'label' => $meta['label'],
                    'color' => $meta['color'],
                    'count' => $requestStatusBuckets->get($status, 0),
                ];
            })
            ->filter(fn (array $item) => $item['count'] > 0)
            ->values();

        $requestStatusChart = [
            'labels' => $requestStatusSummary->pluck('label')->all(),
            'data' => $requestStatusSummary->pluck('count')->all(),
            'colors' => $requestStatusSummary->pluck('color')->all(),
        ];
        $totalRequests = $requestStatusBuckets->sum();
        $activeRequests = $requestStatusBuckets->only(['pending', 'approved', 'issued'])->sum();
        $returnedRequests = (int) $requestStatusBuckets->get('returned', 0);
        $closedRequests = $requestStatusBuckets->only(['rejected', 'cancelled'])->sum();
        $topRequestStatusKey = $requestStatusBuckets->sortDesc()->keys()->first();
        $topRequestStatusMeta = $topRequestStatusKey ? $requestStatusMeta->get($topRequestStatusKey) : null;
        $requestOverview = [
            'total' => $totalRequests,
            'active' => $activeRequests,
            'returned' => $returnedRequests,
            'closed' => $closedRequests,
            'completion_rate' => $totalRequests > 0 ? (int) round(($returnedRequests / $totalRequests) * 100) : 0,
            'top_status' => [
                'label' => $topRequestStatusMeta['label'] ?? 'No requests',
                'count' => $topRequestStatusKey ? (int) $requestStatusBuckets->get($topRequestStatusKey, 0) : 0,
                'color' => $topRequestStatusMeta['color'] ?? '#8b5cf6',
            ],
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
            'activityOverview' => $activityOverview,
            'requestStatusChart' => $requestStatusChart,
            'requestOverview' => $requestOverview,
            'dueSoon' => $dueSoon,
            'privilegeSettings' => $privilegeSettings,
            'yearOfStudy' => $yearOfStudy,
        ]);
    }
}
