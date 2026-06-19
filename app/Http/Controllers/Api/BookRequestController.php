<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookRequestRejectRequest;
use App\Http\Resources\BookRequestResource;
use App\Models\BookRequest;
use App\Models\Notification;
use App\Services\BookRequestManagement\BookRequestManagementActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class BookRequestController extends Controller
{
    use ResolvesApiUsers;

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, ['staff', 'admin'])) {
            return $forbidden;
        }

        $requests = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('request_date')
            ->paginate($this->perPage($request));

        return BookRequestResource::collection($requests);
    }

    public function show(Request $request, int $id): BookRequestResource|JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, ['staff', 'admin'])) {
            return $forbidden;
        }

        return new BookRequestResource(
            BookRequest::query()
                ->with(['student.user', 'student.department', 'book.category'])
                ->findOrFail($id)
        );
    }

    public function approve(Request $request, int $id, BookRequestManagementActionService $service): JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, ['staff', 'admin'])) {
            return $forbidden;
        }

        return $this->updateStatus($request, $id, 'approved', $service);
    }

    public function reject(BookRequestRejectRequest $request, int $id, BookRequestManagementActionService $service): JsonResponse
    {
        if ($forbidden = $this->ensureRole($request, ['staff', 'admin'])) {
            return $forbidden;
        }

        return $this->updateStatus($request, $id, 'rejected', $service, $request->input('remarks'));
    }

    protected function updateStatus(
        Request $request,
        int $id,
        string $status,
        BookRequestManagementActionService $service,
        ?string $remarks = null
    ): JsonResponse {
        $bookRequest = BookRequest::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->findOrFail($id);

        try {
            $bookRequest = $service->updateStatus(
                $bookRequest,
                $status,
                (string) ($request->user()->name ?? 'System'),
                true
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($remarks && $status === 'rejected') {
            $this->attachRemarksToLatestRejectionNotification($bookRequest, $remarks);
        }

        return response()->json([
            'message' => $status === 'approved'
                ? 'Book request approved successfully.'
                : 'Book request rejected successfully.',
            'data' => new BookRequestResource($bookRequest->fresh(['student.user', 'student.department', 'book.category'])),
        ]);
    }

    protected function attachRemarksToLatestRejectionNotification(BookRequest $bookRequest, string $remarks): void
    {
        $notification = Notification::query()
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
        $data['remarks'] = $remarks;

        $notification->update([
            'message' => trim($notification->message . ' ' . $remarks),
            'data' => $data,
        ]);
    }
}
