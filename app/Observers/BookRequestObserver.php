<?php

namespace App\Observers;

use App\Models\BookRequest;
use App\Helpers\ActivityLogger;

class BookRequestObserver
{
    /**
     * Handle the BookRequest "updated" event
     */
    public function updated(BookRequest $bookRequest): void
    {
        // Check if status changed
        if (!$bookRequest->wasChanged('status')) {
            return;
        }

        $bookRequest->loadMissing(['student.user', 'book']);

        $newStatus = strtolower((string) $bookRequest->status);

        if ($bookRequest->student && $bookRequest->book) {
            $action = match ($newStatus) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                'issued' => 'issued',
                'returned' => 'returned',
                'cancelled' => 'cancelled',
                'pending' => 'pending',
                default => $newStatus !== '' ? $newStatus : 'updated',
            };

            ActivityLogger::logBookRequest(
                $bookRequest->student,
                $action,
                $bookRequest->book->title ?? 'Unknown Book',
                [
                    'request_id' => $bookRequest->id,
                    'book_id' => $bookRequest->book_id,
                    'status' => $newStatus,
                    'processed_by_name' => $bookRequest->processed_by,
                    'processed_date' => optional($bookRequest->processed_date)->toDateTimeString(),
                ]
            );
        }
    }
}
