<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\IncludesProfilePhoto;
use App\Http\Resources\IssueResource;
use App\Http\Resources\NotificationResource;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\StudentFineSummaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    use ResolvesApiUsers;
    use IncludesProfilePhoto;

    public function __invoke(Request $request, StudentFineSummaryService $studentFineSummary): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $activeIssuesQuery = IssuedBook::query()
            ->where('student_id', $student->id)
            ->whereNull('return_date');

        $returnedIssuesQuery = IssuedBook::query()
            ->where('student_id', $student->id)
            ->whereNotNull('return_date');

        $currentlyIssued = (clone $activeIssuesQuery)
            ->with(['student.user', 'student.department', 'book.category'])
            ->latest()
            ->limit(5)
            ->get();

        $dueSoon = (clone $activeIssuesQuery)
            ->with(['student.user', 'student.department', 'book.category'])
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $latestNotifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(5)
            ->get();

        $totalRequests = BookRequest::query()
            ->where('student_id', $student->id)
            ->count();

        $approvedRequests = BookRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->count();

        $rejectedRequests = BookRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'rejected')
            ->count();

        $activeRequests = BookRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->count();

        $currentUsage = (clone $activeIssuesQuery)->count();
        $privileges = $this->privileges($student, $currentUsage);
        $user = $student->user;
        $yearOfStudy = $student->created_at
            ? now()->year - $student->created_at->year + 1
            : 1;

        return response()->json([
            'message' => 'Student dashboard loaded successfully.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $user?->name,
                    'roll_no' => $student->roll_no,
                    'email' => $user?->email,
                    'phone' => $user?->phone,
                    'faculty' => $student->department?->name,
                    'semester' => $student->semester,
                    'year_of_study' => max(1, $yearOfStudy),
                    'status' => $user?->status,
                    'profile_photo' => $user?->profile_photo,
                    'profile_photo_url' => $this->profilePhotoUrl($user?->profile_photo),
                ],
                'stats' => [
                    'issued_books' => $currentUsage,
                    'returned_books' => (clone $returnedIssuesQuery)->count(),
                    'pending_fines' => $studentFineSummary->pendingAmount($student),
                    'active_requests' => $activeRequests,
                    'total_requests' => $totalRequests,
                    'approved_requests' => $approvedRequests,
                    'rejected_requests' => $rejectedRequests,
                ],
                'currently_issued' => IssueResource::collection($currentlyIssued),
                'due_soon' => IssueResource::collection($dueSoon),
                'latest_notifications' => NotificationResource::collection($latestNotifications),
                'privileges' => $privileges,
            ],
        ]);
    }

    protected function privileges(Student $student, int $currentUsage): array
    {
        $setting = $this->activeFineSetting();
        $privilege = $student->privileges;

        $maxBooks = (int) ($privilege?->max_books ?: $setting->max_books_per_student);
        $borrowDays = (int) ($privilege?->issue_duration_days ?: $setting->issue_duration_days);
        $finePerDay = (float) ($privilege?->per_day_fine ?: $setting->per_day_fine);
        $borrowingAllowed = (bool) ($privilege?->borrowing_allowed ?? true);

        return [
            'max_books' => $maxBooks,
            'borrow_days' => $borrowDays,
            'fine_per_day' => $finePerDay,
            'borrowing_status' => $borrowingAllowed ? 'active' : 'restricted',
            'current_usage' => $currentUsage,
            'remaining_books' => max(0, $maxBooks - $currentUsage),
            'is_custom' => $privilege !== null,
            'setting_type' => $privilege !== null ? 'custom' : 'default',
        ];
    }
}
