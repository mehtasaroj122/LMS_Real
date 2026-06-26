<?php

namespace App\Services\BookRequestManagement;

use App\Jobs\SendBookRequestStatusEmail;
use App\Models\BookRequest;
use App\Services\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookRequestManagementActionService
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

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

        $this->notifications->notifyBookRequestStatusChanged($bookRequest, $status);

        if ($notifyAdmin) {
            $this->notifications->notifyBookRequestProcessedAdmin($bookRequest, $status);
        }

        if ($bookRequest->student?->user?->email) {
            try {
                SendBookRequestStatusEmail::dispatch(
                    $bookRequest->student->user->email,
                    $bookRequest->student->user->name ?? 'Student',
                    $bookRequest->book?->title ?? 'Requested Book',
                    $status
                );

                \Log::info('Queued book request status email', [
                    'request_id' => $bookRequest->id,
                    'email' => $bookRequest->student->user->email,
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Unable to queue book request status email: ' . $e->getMessage(), [
                    'request_id' => $bookRequest->id,
                    'status' => $status,
                ]);
            }
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

}
