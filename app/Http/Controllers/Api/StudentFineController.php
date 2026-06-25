<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Services\FineCalculator;
use Carbon\Carbon;
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

    public function pending(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        // Get all explicit pending fines
        $pendingFines = $this->baseQuery($student->id)
            ->where('status', 'pending')
            ->get();

        // Get overdue books without pending fines
        $overdueBooks = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category', 'fine'])
            ->where('student_id', $student->id)
            ->whereNull('return_date')
            ->whereDate('due_date', '<', today())
            ->get()
            ->filter(function ($issue) {
                // Only include if no pending fine exists
                return !$issue->fine || $issue->fine->status !== 'pending';
            });

        // Convert overdue books to virtual Fine objects
        $fineCalculator = new FineCalculator();
        $overdueAsFines = $overdueBooks->map(function ($issuedBook) use ($fineCalculator) {
            $fineCalculation = $fineCalculator->calculateFine($issuedBook);

            // Create virtual Fine object
            $fine = new Fine();
            $fine->id = null;
            $fine->issued_book_id = $issuedBook->id;
            $fine->student_id = $issuedBook->student_id;
            $fine->amount = $fineCalculation ? (float) $fineCalculation['amount'] : 0.0;
            $fine->days_late = $fineCalculation ? (int) $fineCalculation['days_late'] : 0;
            $fine->status = 'pending';
            $fine->remarks = $fineCalculation && $fineCalculation['is_within_grace'] ? 'Within grace period' : null;
            // Set relationships
            $fine->setRelation('issuedBook', $issuedBook);
            $fine->setRelation('student', $issuedBook->student);

            return $fine;
        });

        // Combine pending fines and overdue books, then sort by due date (latest first)
        $allItems = $pendingFines->merge($overdueAsFines)
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

    public function summary(Request $request): JsonResponse
    {
        $student = $this->authenticatedStudent($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $baseQuery = Fine::query()->where('student_id', $student->id);

        return response()->json([
            'total_fines' => (int) (clone $baseQuery)->count(),
            'pending_fines' => (int) (clone $baseQuery)->where('status', 'pending')->count(),
            'paid_fines' => (int) (clone $baseQuery)->where('status', 'paid')->count(),
            'pending_amount' => (float) (clone $baseQuery)->where('status', 'pending')->sum('amount'),
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
