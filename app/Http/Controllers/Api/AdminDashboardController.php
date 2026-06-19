<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\BookRequestResource;
use App\Http\Resources\Concerns\IncludesProfilePhoto;
use App\Http\Resources\IssueResource;
use App\Models\ActivityLog;
use App\Models\book as Book;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    use ResolvesApiUsers;
    use IncludesProfilePhoto;

    public function __invoke(Request $request): JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, 'admin')) {
            return $forbidden;
        }

        $latestIssues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->latest()
            ->limit(5)
            ->get();

        $latestRequests = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->latest('request_date')
            ->limit(5)
            ->get();

        $latestLogs = ActivityLog::query()
            ->latest()
            ->limit(10)
            ->get();

        $user = $request->user();

        return response()->json([
            'message' => 'Admin dashboard loaded successfully.',
            'data' => [
                'profile_photo' => $user?->profile_photo,
                'profile_photo_url' => $this->profilePhotoUrl($user?->profile_photo),
                'total_books' => (int) Book::query()->sum('total_copies'),
                'total_students' => (int) Student::query()->count(),
                'total_staff' => (int) User::query()->where('role', 'staff')->count(),
                'issued_books' => (int) IssuedBook::query()->whereNull('return_date')->count(),
                'returned_books' => (int) IssuedBook::query()->whereNotNull('return_date')->count(),
                'overdue_books' => (int) IssuedBook::query()
                    ->whereNull('return_date')
                    ->whereDate('due_date', '<', today())
                    ->count(),
                'pending_fines' => (float) Fine::query()->where('status', 'pending')->sum('amount'),
                'pending_requests' => (int) BookRequest::query()->where('status', 'pending')->count(),
                'latest_issues' => IssueResource::collection($latestIssues),
                'latest_requests' => BookRequestResource::collection($latestRequests),
                'latest_activity_logs' => ActivityLogResource::collection($latestLogs),
            ],
        ]);
    }
}
