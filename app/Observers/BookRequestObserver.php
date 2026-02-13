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
        if ($bookRequest->isDirty('status')) {
            $oldStatus = $bookRequest->getOriginal('status');
            $newStatus = $bookRequest->status;
            
            if ($bookRequest->student && $bookRequest->book) {
                $action = 'pending';
                
                if ($newStatus === 'approved') {
                    $action = 'approved';
                } elseif ($newStatus === 'rejected') {
                    $action = 'rejected';
                } elseif ($newStatus === 'issued') {
                    $action = 'issued';
                }
                
                ActivityLogger::logBookRequest(
                    $bookRequest->student,
                    $action,
                    $bookRequest->book->title ?? 'Unknown Book'
                );
            }
        }
    }
}
