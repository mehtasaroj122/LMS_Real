<?php

namespace App\Observers;

use App\Models\IssuedBook;
use App\Helpers\ActivityLogger;
use App\Services\FineCalculator;

class IssuedBookObserver
{
    /**
     * Handle the IssuedBook "created" event.
     */
    public function created(IssuedBook $issuedBook): void
    {
        // Log book issued activity
        if ($issuedBook->student) {
            ActivityLogger::logBookIssued(
                $issuedBook->student,
                $issuedBook->book->title ?? 'Unknown Book'
            );
        }
    }

    /**
     * Handle the IssuedBook "updated" event.
     */
    public function updated(IssuedBook $issuedBook): void
    {
        // Check if book was just returned
        if ($issuedBook->isDirty('return_date')) {
            $oldReturnDate = $issuedBook->getOriginal('return_date');
            $newReturnDate = $issuedBook->return_date;
            
            // If return_date changed from null to something, book was returned
            if ($oldReturnDate === null && $newReturnDate !== null) {
                // Log book returned activity
                if ($issuedBook->student) {
                    ActivityLogger::logBookReturned(
                        $issuedBook->student,
                        $issuedBook->book->title ?? 'Unknown Book'
                    );
                }

                // Calculate and apply fine if book was returned late
                $fineCalculator = new FineCalculator();
                $fine = $fineCalculator->applyFine($issuedBook);
                
                if ($fine && $fine->amount > 0) {
                    // Log fine activity
                    ActivityLogger::log(
                        $issuedBook->student->id,
                        'student',
                        'fine_generated',
                        "Fine of ₹{$fine->amount} generated for overdue book: {$issuedBook->book->title}",
                        'Fine'
                    );
                }
            }
        }

        // Check if status changed to lost or damaged
        if ($issuedBook->isDirty('status')) {
            $oldStatus = $issuedBook->getOriginal('status');
            $newStatus = $issuedBook->status;

            $fineCalculator = new FineCalculator();

            if ($newStatus === 'lost' && $oldStatus !== 'lost') {
                $fine = $fineCalculator->applyLostBookPenalty($issuedBook);
                
                if ($issuedBook->student) {
                    ActivityLogger::log(
                        $issuedBook->student->id,
                        'student',
                        'lost_book_penalty',
                        "Lost book penalty of ₹{$fine->amount} applied for: {$issuedBook->book->title}",
                        'Fine'
                    );
                }
            }

            if ($newStatus === 'damaged' && $oldStatus !== 'damaged') {
                $fine = $fineCalculator->applyDamagedBookPenalty($issuedBook);
                
                if ($issuedBook->student) {
                    ActivityLogger::log(
                        $issuedBook->student->id,
                        'student',
                        'damaged_book_penalty',
                        "Damaged book penalty of ₹{$fine->amount} applied for: {$issuedBook->book->title}",
                        'Fine'
                    );
                }
            }
        }
    }
}
