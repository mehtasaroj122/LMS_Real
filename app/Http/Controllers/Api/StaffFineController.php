<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffFinePaymentRequest;
use App\Http\Requests\Api\StaffFineWaiveRequest;
use App\Models\Fine;
use App\Services\FineManagement\FineManagementActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StaffFineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $fines = $this->filteredQuery($request)
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json([
            'success' => true,
            'message' => 'Fines fetched successfully.',
            'data' => $fines->getCollection()->map(fn (Fine $fine) => $this->finePayload($fine))->values(),
            'meta' => [
                'current_page' => $fines->currentPage(),
                'last_page' => $fines->lastPage(),
                'per_page' => $fines->perPage(),
                'total' => $fines->total(),
            ],
        ]);
    }

    public function show(int $fine): JsonResponse
    {
        $fineModel = Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->findOrFail($fine);

        return response()->json([
            'success' => true,
            'message' => 'Fine fetched successfully.',
            'data' => $this->finePayload($fineModel),
        ]);
    }

    public function pay(StaffFinePaymentRequest $request, int $fine, FineManagementActionService $service): JsonResponse
    {
        $fineModel = Fine::query()
            ->with(['student.user', 'student.privileges', 'issuedBook.book.category'])
            ->findOrFail($fine);

        if ($request->filled('payment_method')) {
            $fineModel->forceFill(['payment_method' => $request->input('payment_method')])->save();
        }

        try {
            $updatedFine = $service->markAsPaid($fineModel, [
                'notify_student' => true,
                'log_email' => true,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: 'Only pending fines can be updated.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fine marked as paid successfully.',
            'data' => $this->finePayload($updatedFine->loadMissing(['student.user', 'student.department', 'issuedBook.book.category'])),
        ]);
    }

    public function waive(StaffFineWaiveRequest $request, int $fine, FineManagementActionService $service): JsonResponse
    {
        $fineModel = Fine::query()
            ->with(['student.user', 'student.privileges', 'issuedBook.book.category'])
            ->findOrFail($fine);

        try {
            $updatedFine = $service->waive($fineModel, $request->string('reason')->toString(), [
                'notify_student' => true,
                'log_email' => true,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: 'Unable to waive this fine.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fine waived successfully.',
            'data' => $this->finePayload($updatedFine->loadMissing(['student.user', 'student.department', 'issuedBook.book.category'])),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? $request->query('search') ?? ''));

        return Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->query('status')))
            ->when($request->filled('student'), fn ($builder) => $builder->where('student_id', $request->query('student')))
            ->when($request->filled('student_id'), fn ($builder) => $builder->where('student_id', $request->query('student_id')))
            ->when($request->filled('date'), fn ($builder) => $builder->whereDate('created_at', $request->query('date')))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($fineQuery) use ($query) {
                    $fineQuery
                        ->where('fines.id', 'like', "%{$query}%")
                        ->orWhere('remarks', 'like', "%{$query}%")
                        ->orWhereHas('student', function ($studentQuery) use ($query) {
                            $studentQuery
                                ->where('student_id', 'like', "%{$query}%")
                                ->orWhere('roll_no', 'like', "%{$query}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"));
                        })
                        ->orWhereHas('issuedBook.book', fn ($bookQuery) => $bookQuery->where('title', 'like', "%{$query}%")->orWhere('isbn', 'like', "%{$query}%"));
                });
            });
    }

    private function finePayload(Fine $fine): array
    {
        $issue = $fine->issuedBook;
        $book = $issue?->book;
        $student = $fine->student;

        return [
            'id' => $fine->id,
            'fine_id' => $fine->id,
            'student_id' => $fine->student_id,
            'student_name' => $student?->user?->name,
            'student_roll_no' => $student?->roll_no,
            'book_id' => $book?->id,
            'book_title' => $book?->title,
            'isbn' => $book?->isbn,
            'amount' => (float) $fine->amount,
            'status' => $fine->status,
            'reason' => $fine->remarks,
            'remarks' => $fine->remarks,
            'days_late' => (int) $fine->days_late,
            'issue_id' => $fine->issued_book_id,
            'due_date' => optional($issue?->due_date)->toDateString(),
            'paid_date' => optional($fine->paid_on)->toDateString(),
            'payment_method' => $fine->payment_method,
            'created_at' => optional($fine->created_at)->toDateTimeString(),
            'updated_at' => optional($fine->updated_at)->toDateTimeString(),
        ];
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
