<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Services\StudentFineSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentFineController extends Controller
{
    use ResolvesApiUsers;

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        return FineResource::collection(
            $this->baseQuery($student->id)
                ->latest()
                ->paginate($this->perPage($request))
        );
    }

    public function pending(Request $request, StudentFineSummaryService $studentFineSummary): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $allItems = $studentFineSummary->pendingItems($student)
            ->sortByDesc(function ($item) {
                return optional($item->issuedBook->due_date)->timestamp ?? now()->timestamp;
            })
            ->values();

        // Manual pagination
        $perPage = $this->perPage($request);
        $page = $request->get('page', 1);
        $paginated = $allItems->forPage($page, $perPage);

        return FineResource::collection($paginated)
            ->additional([
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $allItems->count(),
                ]
            ]);
    }

    public function paid(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        return FineResource::collection(
            $this->baseQuery($student->id)
                ->where('status', 'paid')
                ->latest()
                ->paginate($this->perPage($request))
        );
    }

    public function show(Request $request, int $id): FineResource|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $fine = Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->findOrFail($id);

        if ((int) $fine->student_id !== (int) $student->id) {
            return $this->forbid();
        }

        return new FineResource($fine);
    }

    public function summary(Request $request, StudentFineSummaryService $studentFineSummary): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $baseQuery = Fine::query()->where('student_id', $student->id);
        $pendingItems = $studentFineSummary->pendingItems($student);

        return response()->json([
            'total_fines' => (int) (clone $baseQuery)->count(),
            'pending_fines' => $pendingItems->count(),
            'paid_fines' => (int) (clone $baseQuery)->where('status', 'paid')->count(),
            'pending_amount' => (float) $pendingItems->sum(fn (Fine $fine) => (float) $fine->amount),
            'paid_amount' => (float) (clone $baseQuery)->where('status', 'paid')->sum('amount'),
            'total_amount' => (float) (clone $baseQuery)->sum('amount'),
        ]);
    }

    protected function baseQuery(int $studentId)
    {
        return Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->where('student_id', $studentId);
    }
}
