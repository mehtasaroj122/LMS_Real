<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
use App\Models\Fine;
use App\Models\IssuedBook;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentBookController extends Controller
{
    use ResolvesApiUsers;

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $issues = $this->baseQuery($student->id)
            ->latest()
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function current(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $issues = $this->baseQuery($student->id)
            ->whereNull('return_date')
            ->latest()
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function history(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $issues = $this->baseQuery($student->id)
            ->whereNotNull('return_date')
            ->latest('return_date')
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function dueSoon(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $issues = $this->baseQuery($student->id)
            ->whereNull('return_date')
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
            ->orderBy('due_date')
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function summary(Request $request): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $baseQuery = IssuedBook::query()->where('student_id', $student->id);
        $fineQuery = Fine::query()->where('student_id', $student->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => [
                    'total_issued' => (int) (clone $baseQuery)->count(),
                    'currently_borrowed' => (int) (clone $baseQuery)->whereNull('return_date')->count(),
                    'overdue_books' => (int) (clone $baseQuery)
                        ->whereNull('return_date')
                        ->whereDate('due_date', '<', today())
                        ->count(),
                    'pending_fine' => (float) (clone $fineQuery)->where('status', 'pending')->sum('amount'),
                    'paid_fines' => (float) (clone $fineQuery)->where('status', 'paid')->sum('amount'),
                ]
            ]
        ]);
    }

    protected function baseQuery(int $studentId)
    {
        return IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category', 'fine'])
            ->where('student_id', $studentId);
    }
}
