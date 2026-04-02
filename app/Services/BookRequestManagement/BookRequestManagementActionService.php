<?php

namespace App\Services\BookRequestManagement;

use App\Models\BookRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookRequestManagementActionService
{
    public function create(array $validated): BookRequest
    {
        $existingRequest = BookRequest::query()
            ->where('student_id', $validated['student_id'])
            ->where('book_id', $validated['book_id'])
            ->whereIn('status', ['pending', 'approved', 'issued'])
            ->first();

        if ($existingRequest) {
            throw ValidationException::withMessages([
                'book_id' => 'This student already has an active request for this book. Please wait until the book is returned before creating a new request.',
            ]);
        }

        $bookRequest = BookRequest::create([
            'student_id' => $validated['student_id'],
            'book_id' => $validated['book_id'],
            'request_date' => now(),
            'status' => 'pending',
        ]);

        return $bookRequest->load(['student.user', 'book']);
    }

    public function updateStatus(
        BookRequest $bookRequest,
        string $status,
        string $processedBy,
        bool $notifyAdmin = false
    ): BookRequest {
        $this->ensurePending($bookRequest);

        $bookRequest->loadMissing(['student.user', 'book']);

        $bookRequest->update([
            'status' => $status,
            'processed_by' => $processedBy,
            'processed_date' => now(),
        ]);

        $bookRequest->refresh()->loadMissing(['student.user', 'book']);

        $this->notifyStudent($bookRequest, $status);

        if ($notifyAdmin) {
            $this->notifyAdmin($bookRequest, $status);
        }

        if ($bookRequest->student?->user?->email) {
            \Log::info('Book request status email would have been sent to: ' . $bookRequest->student->user->email);
        }

        return $bookRequest;
    }

    public function bulkUpdateStatus(
        array $requestIds,
        string $status,
        string $processedBy,
        bool $notifyAdmin = false
    ): array {
        $normalizedIds = collect($requestIds)
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->unique()
            ->values();

        /** @var Collection<int, BookRequest> $requests */
        $requests = BookRequest::query()
            ->with(['student.user', 'book'])
            ->whereIn('id', $normalizedIds->all())
            ->get()
            ->keyBy('id');

        $updated = [];
        $skipped = [];

        foreach ($normalizedIds as $requestId) {
            $bookRequest = $requests->get($requestId);

            if (!$bookRequest) {
                $skipped[] = [
                    'id' => $requestId,
                    'reason' => 'missing',
                ];
                continue;
            }

            if (strtolower((string) $bookRequest->status) !== 'pending') {
                $skipped[] = [
                    'id' => $requestId,
                    'reason' => 'not_pending',
                ];
                continue;
            }

            $updated[] = $this->updateStatus($bookRequest, $status, $processedBy, $notifyAdmin);
        }

        return [
            'updated' => $updated,
            'updated_count' => count($updated),
            'skipped' => $skipped,
            'skipped_count' => count($skipped),
        ];
    }

    protected function ensurePending(BookRequest $bookRequest): void
    {
        if (strtolower((string) $bookRequest->status) !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Only pending book requests can be updated from this page.',
            ]);
        }
    }

    protected function notifyStudent(BookRequest $bookRequest, string $status): void
    {
        if (!$bookRequest->student?->user) {
            return;
        }

        $notificationType = $status === 'approved' ? 'request.approved' : 'request.rejected';
        $title = $status === 'approved' ? 'Request Approved' : 'Request Rejected';
        $message = $status === 'approved'
            ? "Your request for '{$bookRequest->book?->title}' has been approved!"
            : "Your request for '{$bookRequest->book?->title}' has been rejected.";

        Notification::notify(
            user: $bookRequest->student->user,
            type: $notificationType,
            title: $title,
            message: $message,
            data: [
                'request_id' => $bookRequest->id,
                'book_id' => $bookRequest->book_id,
                'status' => $status,
                'book_title' => $bookRequest->book?->title,
            ],
            relatedModel: 'BookRequest',
            relatedId: $bookRequest->id
        );
    }

    protected function notifyAdmin(BookRequest $bookRequest, string $status): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        if (!$admin) {
            return;
        }

        Notification::notify(
            user: $admin,
            type: 'request.pending',
            title: 'Book Request Processed',
            message: "Request from {$bookRequest->student?->user?->name} for '{$bookRequest->book?->title}' has been {$status}",
            data: [
                'request_id' => $bookRequest->id,
                'status' => $status,
                'student_id' => $bookRequest->student_id,
                'book_id' => $bookRequest->book_id,
            ],
            relatedModel: 'BookRequest',
            relatedId: $bookRequest->id
        );
    }
}
