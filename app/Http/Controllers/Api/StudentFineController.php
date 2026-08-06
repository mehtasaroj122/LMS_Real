<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Services\Concerns\DeduplicatesFineRecords;
use App\Services\StudentFineSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentFineController extends Controller
{
    use ResolvesApiUsers;
    use DeduplicatesFineRecords;

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $items = $this->collapseDuplicateFineRecords(
            $this->baseQuery($student->id)->latest()->get()
        );

        return $this->paginateFineItems($items, $request);
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

        return $this->paginateFineItems($allItems, $request);
    }

    public function paid(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $items = $this->collapseDuplicateFineRecords(
            $this->baseQuery($student->id)
                ->where('status', 'paid')
                ->latest()
                ->get()
        );

        return $this->paginateFineItems($items, $request);
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

        $fines = $this->collapseFineRecordsQuery(Fine::query()->where('student_id', $student->id));
        $pendingItems = $studentFineSummary->pendingItems($student);

        return response()->json([
            'total_fines' => $fines->count(),
            'pending_fines' => $pendingItems->count(),
            'paid_fines' => $fines->where('status', 'paid')->count(),
            'pending_amount' => (float) $pendingItems->sum(fn (Fine $fine) => (float) $fine->amount),
            'paid_amount' => (float) $fines->where('status', 'paid')->sum('amount'),
            'total_amount' => (float) $fines->sum('amount'),
        ]);
    }

    protected function paginateFineItems($items, Request $request): AnonymousResourceCollection
    {
        $perPage = $this->perPage($request);
        $page = $request->integer('page', 1);
        $paginated = $items->forPage($page, $perPage);

        return FineResource::collection($paginated)
            ->additional([
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $items->count(),
                ],
            ]);
    }

    protected function baseQuery(int $studentId)
    {
        return Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->where('student_id', $studentId);
    }
}
