<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use Carbon\Carbon;

class FineCalculator
{
    protected $fineSetting;

    public function __construct()
    {
        // Get active fine setting or create default if not exists
        $this->fineSetting = FineSetting::where('is_active', true)->first();
        
        if (!$this->fineSetting) {
            // Create default fine setting
            $this->fineSetting = FineSetting::create([
                'per_day_fine' => 5.00,
                'grace_period_days' => 2,
                'max_fine_amount' => 500.00,
                'lost_book_penalty' => 1000.00,
                'damaged_book_penalty' => 250.00,
                'fair_condition_penalty' => 50.00,
                'issue_duration_days' => 14,
                'max_books_per_student' => 5,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Calculate fine for an overdue book
     */
    public function calculateFine(IssuedBook $issuedBook): ?array
    {
        // If book already returned, no fine calculation needed
        if ($issuedBook->return_date) {
            return null;
        }

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($issuedBook->due_date)->startOfDay();

        // Check if book is overdue
        if ($today <= $dueDate) {
            return null;
        }

        // Calculate days late (absolute difference)
        $daysLate = abs($today->diffInDays($dueDate));
        $gracePeriod = $this->fineSetting->grace_period_days;

        // If within grace period, no fine
        if ($daysLate <= $gracePeriod) {
            return [
                'days_late' => $daysLate,
                'amount' => 0,
                'is_within_grace' => true,
            ];
        }

        // Days beyond grace period
        $chargeable_days = $daysLate - $gracePeriod;
        
        // Get effective per-day fine for this student (respecting per-student overrides)
        $perDayFine = $this->getEffectivePerDayFine($issuedBook->student);
        
        // Calculate fine amount
        $amount = (int)($chargeable_days * $perDayFine);
        
        // Cap at maximum fine amount
        $amount = min($amount, $this->fineSetting->max_fine_amount);

        return [
            'days_late' => $daysLate,
            'amount' => $amount,
            'is_within_grace' => false,
            'chargeable_days' => $chargeable_days,
        ];
    }

    /**
     * Create or update fine for issued book
     */
    public function applyFine(IssuedBook $issuedBook): ?Fine
    {
        $fineCalculation = $this->calculateFine($issuedBook);

        if (!$fineCalculation) {
            return null;
        }

        // If within grace period and fine is 0, update IssuedBook but don't create fine record
        if ($fineCalculation['is_within_grace'] && $fineCalculation['amount'] == 0) {
            $issuedBook->update(['fine_amount' => 0]);
            return null;
        }

        // Check if fine record already exists
        $fine = Fine::where('issued_book_id', $issuedBook->id)->first();

        if ($fine) {
            // Update existing fine
            $fine->update([
                'amount' => $fineCalculation['amount'],
                'days_late' => $fineCalculation['days_late'],
            ]);
        } else {
            // Create new fine record
            $fine = Fine::create([
                'issued_book_id' => $issuedBook->id,
                'student_id' => $issuedBook->student_id,
                'amount' => $fineCalculation['amount'],
                'days_late' => $fineCalculation['days_late'],
                'status' => 'pending',
            ]);
        }

        // Update IssuedBook fine_amount
        $issuedBook->update(['fine_amount' => $fineCalculation['amount']]);

        return $fine;
    }

    /**
     * Calculate fine for lost book
     */
    public function applyLostBookPenalty(IssuedBook $issuedBook): Fine
    {
        $fine = Fine::create([
            'issued_book_id' => $issuedBook->id,
            'student_id' => $issuedBook->student_id,
            'amount' => $this->fineSetting->lost_book_penalty,
            'days_late' => 0,
            'status' => 'pending',
            'remarks' => 'Lost book penalty',
        ]);

        $issuedBook->update([
            'status' => 'lost',
            'fine_amount' => $this->fineSetting->lost_book_penalty,
        ]);

        return $fine;
    }

    /**
     * Calculate fine for damaged book
     */
    public function applyDamagedBookPenalty(IssuedBook $issuedBook): Fine
    {
        $fine = Fine::create([
            'issued_book_id' => $issuedBook->id,
            'student_id' => $issuedBook->student_id,
            'amount' => $this->fineSetting->damaged_book_penalty,
            'days_late' => 0,
            'status' => 'pending',
            'remarks' => 'Damaged book penalty',
        ]);

        $issuedBook->update([
            'status' => 'damaged',
            'fine_amount' => $this->fineSetting->damaged_book_penalty,
        ]);

        return $fine;
    }

    /**
     * Mark fine as paid
     */
    public function markAsPaid(Fine $fine, string $paymentMethod = 'cash'): Fine
    {
        $fine->update([
            'status' => 'paid',
            'paid_on' => Carbon::now(),
            'payment_method' => $paymentMethod,
        ]);

        return $fine;
    }

    /**
     * Get pending fines for a student
     */
    public function getStudentPendingFines($studentId): float
    {
        return Fine::where('student_id', $studentId)
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * Get settings
     */
    public function getSettings(): FineSetting
    {
        return $this->fineSetting;
    }

    /**
     * Get effective per-day fine for a student (per-student override or global default)
     */
    private function getEffectivePerDayFine($student): float
    {
        // Check if student's privileges have a per-day fine override
        if ($student && $student->privileges && $student->privileges->per_day_fine) {
            return (float) $student->privileges->per_day_fine;
        }

        // Fall back to global setting
        return (float) $this->fineSetting->per_day_fine;
    }
}
