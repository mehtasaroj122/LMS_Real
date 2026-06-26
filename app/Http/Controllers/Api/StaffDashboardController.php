<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookRequestResource;
use App\Http\Resources\Concerns\IncludesProfilePhoto;
use App\Http\Resources\IssueResource;
use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StaffDashboardController extends Controller
{
    use ResolvesApiUsers;
    use IncludesProfilePhoto;

    public function __invoke(Request $request): JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, ['staff', 'admin'])) {
            return $forbidden;
        }

        $today = today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $latestIssues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->latest()
            ->limit(5)
            ->get();

        $latestRequests = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->where('status', 'pending')
            ->latest('request_date')
            ->limit(5)
            ->get();

        $dueTodayBooks = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->whereNull('return_date')
            ->whereDate('due_date', $today)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $overdueBooks = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->whereNull('return_date')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $user = $request->user();
        $user?->loadMissing('staff.department');

        $currentlyIssued = (int) IssuedBook::query()->whereNull('return_date')->count();
        $dueTodayCount = (int) IssuedBook::query()
            ->whereNull('return_date')
            ->whereDate('due_date', $today)
            ->count();
        $overdueCount = (int) IssuedBook::query()
            ->whereNull('return_date')
            ->whereDate('due_date', '<', $today)
            ->count();
        $availableBooks = (int) Book::query()->sum('available_copies');
        $totalBooks = (int) Book::query()->sum('total_copies');
        $issuedThisWeek = (int) IssuedBook::query()
            ->whereBetween('issue_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();
        $returnedThisWeek = (int) IssuedBook::query()
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();
        $weeklyMovement = $issuedThisWeek + $returnedThisWeek;
        $peakActivityDay = $this->peakActivityDay($weekStart, $weekEnd);

        return response()->json([
            'success' => true,
            'message' => 'Staff dashboard loaded successfully.',
            'data' => [
                'staff' => [
                    'id' => $user?->id,
                    'name' => $user?->name,
                    'email' => $user?->email,
                    'role' => $user?->role,
                    'staff_id' => $user?->staff?->staff_id,
                    'department' => $user?->staff?->department?->name,
                    'designation' => $user?->staff?->designation,
                ],
                'profile_photo' => $user?->profile_photo,
                'profile_photo_url' => $this->profilePhotoUrl($user?->profile_photo),
                'total_books' => $totalBooks,
                'total_students' => (int) Student::query()->count(),
                'issued_books' => $currentlyIssued,
                'returned_books' => (int) IssuedBook::query()->whereNotNull('return_date')->count(),
                'overdue_books' => $overdueCount,
                'pending_fines' => (float) Fine::query()->where('status', 'pending')->sum('amount'),
                'pending_requests' => (int) BookRequest::query()->where('status', 'pending')->count(),
                'summary' => [
                    'issued_books' => $currentlyIssued,
                    'currently_issued' => $currentlyIssued,
                    'due_today' => $dueTodayCount,
                    'overdue_books' => $overdueCount,
                    'overdue' => $overdueCount,
                    'pending_requests' => (int) BookRequest::query()->where('status', 'pending')->count(),
                    'pending_fines_amount' => (float) Fine::query()->where('status', 'pending')->sum('amount'),
                ],
                'circulation' => [
                    'available_books' => $availableBooks,
                    'issued_on_time' => max($currentlyIssued - $dueTodayCount - $overdueCount, 0),
                    'due_today' => $dueTodayCount,
                    'overdue' => $overdueCount,
                    'total_trackable_copies' => $totalBooks,
                ],
                'weekly_activity' => [
                    'issued_this_week' => $issuedThisWeek,
                    'returned_this_week' => $returnedThisWeek,
                    'peak_activity_day' => $peakActivityDay,
                    'average_per_day' => round($weeklyMovement / 7, 1),
                ],
                'pending_requests_list' => $this->formatRequests($latestRequests),
                'latest_pending_requests' => $this->formatRequests($latestRequests),
                'due_today' => $this->formatIssues($dueTodayBooks),
                'overdue_books_list' => $this->formatIssues($overdueBooks, includeOverdueDays: true),
                'top_overdue_books' => $this->formatIssues($overdueBooks, includeOverdueDays: true),
                'recent_issues' => $this->formatIssues($latestIssues, includeTimeAgo: true),
                'latest_issues' => IssueResource::collection($latestIssues),
                'latest_requests' => BookRequestResource::collection($latestRequests),
            ],
        ]);
    }

    private function formatRequests($requests): array
    {
        return $requests->map(fn (BookRequest $request) => [
            'id' => $request->id,
            'book_title' => $request->book?->title,
            'student_name' => $request->student?->user?->name,
            'student_id' => $request->student?->roll_no,
            'symbol_no' => $request->student?->roll_no,
            'request_date' => optional($request->request_date)->toDateTimeString(),
            'status' => $request->status,
        ])->values()->all();
    }

    private function formatIssues($issues, bool $includeOverdueDays = false, bool $includeTimeAgo = false): array
    {
        return $issues->map(function (IssuedBook $issue) use ($includeOverdueDays, $includeTimeAgo) {
            $data = [
                'id' => $issue->id,
                'issue_id' => $issue->id,
                'book_title' => $issue->book?->title,
                'student_name' => $issue->student?->user?->name,
                'student_id' => $issue->student?->roll_no,
                'due_date' => optional($issue->due_date)->toDateString(),
                'issued_date' => optional($issue->issue_date)->toDateString(),
            ];

            if ($includeOverdueDays) {
                $data['days_overdue'] = $issue->due_date
                    ? max(0, Carbon::parse($issue->due_date)->diffInDays(today()))
                    : 0;
            }

            if ($includeTimeAgo) {
                $data['time_ago'] = $issue->created_at?->diffForHumans();
            }

            return $data;
        })->values()->all();
    }

    private function peakActivityDay(Carbon $weekStart, Carbon $weekEnd): ?string
    {
        $activityByDay = collect();

        IssuedBook::query()
            ->whereBetween('issue_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get(['issue_date'])
            ->each(function (IssuedBook $issue) use ($activityByDay) {
                $day = Carbon::parse($issue->issue_date)->format('l');
                $activityByDay[$day] = ($activityByDay[$day] ?? 0) + 1;
            });

        IssuedBook::query()
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get(['return_date'])
            ->each(function (IssuedBook $issue) use ($activityByDay) {
                $day = Carbon::parse($issue->return_date)->format('l');
                $activityByDay[$day] = ($activityByDay[$day] ?? 0) + 1;
            });

        return $activityByDay->sortDesc()->keys()->first();
    }
}
