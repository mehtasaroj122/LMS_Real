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
        $issuedBook->loadMissing(['student.user', 'book']);

        // Log book issued activity
        if ($issuedBook->student && $issuedBook->book) {
            ActivityLogger::logBookIssued(
                $issuedBook->student,
                $issuedBook->book->title ?? 'Unknown Book',
                [
                    'issued_book_id' => $issuedBook->id,
                    'book_id' => $issuedBook->book_id,
                    'isbn' => $issuedBook->book?->isbn,
                    'issue_date' => optional($issuedBook->issue_date)->toDateString(),
                    'due_date' => optional($issuedBook->due_date)->toDateString(),
                    'issued_by_user_id' => $issuedBook->issued_by,
                ]
            );
        }
    }

    /**
     * Handle the IssuedBook "updated" event.
     */
    public function updated(IssuedBook $issuedBook): void
    {
        $issuedBook->loadMissing(['student.user', 'book']);

        // Check if book was just returned
        if ($issuedBook->wasChanged('return_date') && $issuedBook->return_date !== null) {
                // Log book returned activity
                if ($issuedBook->student && $issuedBook->book) {
                    ActivityLogger::logBookReturned(
                        $issuedBook->student,
                        $issuedBook->book->title ?? 'Unknown Book',
                        [
                            'issued_book_id' => $issuedBook->id,
                            'book_id' => $issuedBook->book_id,
                            'isbn' => $issuedBook->book?->isbn,
                            'condition' => $issuedBook->condition,
                            'fine_amount' => (float) ($issuedBook->fine_amount ?? 0),
                            'return_date' => optional($issuedBook->return_date)->toDateString(),
                        ]
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

        // Check if status changed to lost or damaged
        if ($issuedBook->wasChanged('status')) {
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
