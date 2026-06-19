<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
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

        return response()->json([
            'currently_issued' => (int) (clone $baseQuery)->whereNull('return_date')->count(),
            'returned_books' => (int) (clone $baseQuery)->whereNotNull('return_date')->count(),
            'overdue_books' => (int) (clone $baseQuery)
                ->whereNull('return_date')
                ->whereDate('due_date', '<', today())
                ->count(),
            'due_soon' => (int) (clone $baseQuery)
                ->whereNull('return_date')
                ->whereDate('due_date', '>=', today())
                ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
                ->count(),
        ]);
    }

    protected function baseQuery(int $studentId)
    {
        return IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->where('student_id', $studentId);
    }
}
