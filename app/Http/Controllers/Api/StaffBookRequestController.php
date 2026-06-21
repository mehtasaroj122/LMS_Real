<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffBookRequestRejectRequest;
use App\Models\BookRequest;
use App\Services\BookRequestManagement\BookRequestManagementActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StaffBookRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = $this->filteredQuery($request)
            ->latest('request_date')
            ->paginate($this->perPage($request));

        return response()->json([
            'success' => true,
            'message' => 'Book requests fetched successfully.',
            'data' => $requests->getCollection()->map(fn (BookRequest $bookRequest) => $this->requestPayload($bookRequest))->values(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function show(int $bookRequest): JsonResponse
    {
        $requestModel = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->findOrFail($bookRequest);

        return response()->json([
            'success' => true,
            'message' => 'Book request fetched successfully.',
            'data' => $this->requestPayload($requestModel),
        ]);
    }

    public function approve(Request $request, int $bookRequest, BookRequestManagementActionService $service): JsonResponse
    {
        $requestModel = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->findOrFail($bookRequest);

        if ((int) ($requestModel->book?->available_copies ?? 0) <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Requested book is not available.',
            ], 409);
        }

        try {
            $updated = $service->updateStatus(
                $requestModel,
                'approved',
                (string) ($request->user()->name ?? 'System'),
                true
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: 'Unable to approve this request.',
                'errors' => $exception->errors(),
            ], 422);
        }

        ActivityLogger::logBookRequest($updated->student, 'approved', $updated->book?->title ?? 'Unknown Book', [
            'book_request_id' => $updated->id,
            'book_id' => $updated->book_id,
            'source' => 'mobile_staff_api',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Book request approved successfully.',
            'data' => $this->requestPayload($updated->fresh(['student.user', 'student.department', 'book.category'])),
        ]);
    }

    public function reject(StaffBookRequestRejectRequest $request, int $bookRequest, BookRequestManagementActionService $service): JsonResponse
    {
        $requestModel = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->findOrFail($bookRequest);

        try {
            $updated = $service->updateStatus(
                $requestModel,
                'rejected',
                (string) ($request->user()->name ?? 'System'),
                true
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: 'Unable to reject this request.',
                'errors' => $exception->errors(),
            ], 422);
        }

        $reason = trim($request->string('reason')->toString());

        if ($reason !== '') {
            $this->appendReasonToNotification($updated, $reason);
        }

        ActivityLogger::logBookRequest($updated->student, 'rejected', $updated->book?->title ?? 'Unknown Book', [
            'book_request_id' => $updated->id,
            'book_id' => $updated->book_id,
            'reason' => $reason ?: null,
            'source' => 'mobile_staff_api',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Book request rejected successfully.',
            'data' => $this->requestPayload($updated->fresh(['student.user', 'student.department', 'book.category'])),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? $request->query('search') ?? ''));

        return BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->query('status')))
            ->when($request->filled('student_id'), fn ($builder) => $builder->where('student_id', $request->query('student_id')))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($requestQuery) use ($query) {
                    $requestQuery
                        ->where('book_requests.id', 'like', "%{$query}%")
                        ->orWhereHas('student', function ($studentQuery) use ($query) {
                            $studentQuery
                                ->where('student_id', 'like', "%{$query}%")
                                ->orWhere('roll_no', 'like', "%{$query}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"));
                        })
                        ->orWhereHas('book', fn ($bookQuery) => $bookQuery->where('title', 'like', "%{$query}%")->orWhere('isbn', 'like', "%{$query}%"));
                });
            });
    }

    private function requestPayload(BookRequest $bookRequest): array
    {
        $student = $bookRequest->student;
        $book = $bookRequest->book;

        return [
            'id' => $bookRequest->id,
            'request_id' => $bookRequest->id,
            'student_id' => $bookRequest->student_id,
            'student_name' => $student?->user?->name,
            'student_roll_no' => $student?->roll_no,
            'department' => $student?->department?->name,
            'book_id' => $bookRequest->book_id,
            'book_title' => $book?->title,
            'author' => $book?->author,
            'isbn' => $book?->isbn,
            'available_copies' => (int) ($book?->available_copies ?? 0),
            'status' => $bookRequest->status,
            'request_date' => optional($bookRequest->request_date)->toDateTimeString(),
            'processed_by' => $bookRequest->processed_by,
            'processed_date' => optional($bookRequest->processed_date)->toDateTimeString(),
        ];
    }

    private function appendReasonToNotification(BookRequest $bookRequest, string $reason): void
    {
        $notification = \App\Models\Notification::query()
            ->where('user_id', $bookRequest->student?->user_id)
            ->where('related_model', 'BookRequest')
            ->where('related_id', $bookRequest->id)
            ->where('type', 'request.rejected')
            ->latest()
            ->first();

        if (! $notification) {
            return;
        }

        $data = $notification->data ?? [];
        $data['reason'] = $reason;

        $notification->update([
            'message' => trim($notification->message . ' ' . $reason),
            'data' => $data,
        ]);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
