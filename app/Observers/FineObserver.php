<?php

namespace App\Observers;

use App\Models\Fine;
use App\Models\FineSetting;
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
                        $bookName,
                        [
                            'fine_id' => $fine->id,
                            'issued_book_id' => $fine->issued_book_id,
                            'amount' => (float) $fine->amount,
                            'new_amount' => (float) $fine->amount,
                            'days_late' => (int) ($fine->days_late ?? 0),
                            'payment_method' => $fine->payment_method ?? 'cash',
                            'book_title' => $bookName,
                            'isbn' => $fine->issuedBook?->book?->isbn,
                            'status' => strtolower((string) $fine->status),
                        ]
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
                "Fine of ₹{$fine->amount} applied" . ($bookName ? " for '{$bookName}'" : ''),
                'fine',
                [
                    'fine_id' => $fine->id,
                    'issued_book_id' => $fine->issued_book_id,
                    'amount' => (float) $fine->amount,
                    'new_amount' => (float) $fine->amount,
                    'original_amount' => (float) $fine->amount,
                    'days_late' => (int) ($fine->days_late ?? 0),
                    'per_day_rate' => $this->resolvePerDayRate($fine),
                    'book_title' => $bookName,
                    'isbn' => $fine->issuedBook?->book?->isbn,
                    'status' => strtolower((string) $fine->status),
                    'remarks' => $fine->remarks,
                ]
            );
        }
    }

    protected function resolvePerDayRate(Fine $fine): float
    {
        if ($fine->student?->privileges?->per_day_fine !== null) {
            return (float) $fine->student->privileges->per_day_fine;
        }

        $fineSetting = FineSetting::where('is_active', true)->first() ?? FineSetting::first();

        return (float) ($fineSetting?->per_day_fine ?? 5);
    }
}
