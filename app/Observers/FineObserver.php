<?php

namespace App\Observers;

use App\Models\Fine;
use App\Helpers\ActivityLogger;

class FineObserver
{
    /**
     * Handle the Fine "updated" event - specifically when payment is made
     */
    public function updated(Fine $fine): void
    {
        // Check if status changed to paid
        if ($fine->isDirty('status')) {
            $oldStatus = $fine->getOriginal('status');
            $newStatus = $fine->status;
            
            // If status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                if ($fine->student) {
                    $bookName = '';
                    if ($fine->issuedBook && $fine->issuedBook->book) {
                        $bookName = $fine->issuedBook->book->title;
                    }
                    
                    ActivityLogger::logFinePayment(
                        $fine->student,
                        (float) $fine->amount,
                        $bookName
                    );
                }
            }
        }
    }

    /**
     * Handle the Fine "created" event - when fine is applied
     */
    public function created(Fine $fine): void
    {
        if ($fine->student) {
            $bookName = '';
            if ($fine->issuedBook && $fine->issuedBook->book) {
                $bookName = $fine->issuedBook->book->title;
            }
            
            ActivityLogger::logStudentActivity(
                $fine->student,
                'fine_applied',
                "Fine of ₹{$fine->amount} applied" . ($bookName ? " for '{$bookName}'" : '')
            );
        }
    }
}
