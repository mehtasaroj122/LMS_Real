<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class FineCalculator
{
    protected $fineSetting;

    public function __construct()
    {
        $this->fineSetting = FineSetting::resolveActive();

        if (! $this->fineSetting->exists) {
            $this->fineSetting->save();
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
        $amount = (int) ($chargeable_days * $perDayFine);

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
     * Calculate the complete server-side fine for a return condition.
     */
    public function calculateReturnFine(
        IssuedBook $issuedBook,
        string $condition,
        ?Carbon $returnDate = null
    ): array {
        $returnedOn = ($returnDate ?? now())->copy()->startOfDay();
        $dueDate = Carbon::parse($issuedBook->due_date)->startOfDay();
        $overdueDays = $returnedOn->greaterThan($dueDate)
            ? (int) $dueDate->diffInDays($returnedOn)
            : 0;
        $graceDays = (int) ($this->fineSetting->grace_period_days ?? 0);
        $chargeableDays = max($overdueDays - $graceDays, 0);
        $perDayFine = $this->getEffectivePerDayFine($issuedBook->student);
        $overdueFine = (float) min(
            $chargeableDays * $perDayFine,
            (float) ($this->fineSetting->max_fine_amount ?? 0)
        );
        $conditionFine = match ($condition) {
            'lost' => (float) ($this->fineSetting->lost_book_penalty ?? 0),
            'damaged' => (float) ($this->fineSetting->damaged_book_penalty ?? 0),
            'fair' => (float) ($this->fineSetting->fair_condition_penalty ?? 0),
            default => 0.0,
        };
        $totalFine = $condition === 'lost'
            ? $conditionFine
            : $overdueFine + $conditionFine;

        return [
            'amount' => (float) $totalFine,
            'total_fine' => (float) $totalFine,
            'days_late' => $condition === 'lost' ? 0 : $overdueDays,
            'overdue_days' => $overdueDays,
            'grace_days' => $graceDays,
            'chargeable_overdue_days' => $chargeableDays,
            'overdue_fine' => $condition === 'lost' ? 0.0 : $overdueFine,
            'condition_fine' => $conditionFine,
            'condition' => $condition,
            'remarks' => match ($condition) {
                'lost' => 'Lost book penalty',
                'damaged' => 'Damaged book penalty + overdue fine',
                'fair' => 'Fair condition penalty + overdue fine',
                default => 'Overdue fine',
            },
        ];
    }

    public function applyReturnFine(IssuedBook $issuedBook, array $calculation): ?Fine
    {
        if ((float) ($calculation['amount'] ?? 0) <= 0) {
            return null;
        }

        return $this->saveFineForIssue($issuedBook, [
            'amount' => (float) $calculation['amount'],
            'days_late' => (int) ($calculation['days_late'] ?? 0),
            'status' => 'pending',
            'remarks' => $calculation['remarks'] ?? 'Return fine',
        ]);
    }

    /**
     * Create or update fine for issued book
     */
    public function applyFine(IssuedBook $issuedBook): ?Fine
    {
        $fineCalculation = $this->calculateFine($issuedBook);

        if (! $fineCalculation) {
            return null;
        }

        // If within grace period and fine is 0, update IssuedBook but don't create fine record
        if ($fineCalculation['is_within_grace'] && $fineCalculation['amount'] == 0) {
            $issuedBook->update(['fine_amount' => 0]);

            return null;
        }

        $fine = $this->saveFineForIssue($issuedBook, [
            'amount' => $fineCalculation['amount'],
            'days_late' => $fineCalculation['days_late'],
            'status' => 'pending',
        ]);

        // Update IssuedBook fine_amount
        $issuedBook->update(['fine_amount' => $fineCalculation['amount']]);

        return $fine;
    }

    /**
     * Calculate fine for lost book
     */
    public function applyLostBookPenalty(IssuedBook $issuedBook): Fine
    {
        $fine = $this->saveFineForIssue($issuedBook, [
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
        $fine = $this->saveFineForIssue($issuedBook, [
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

    private function saveFineForIssue(IssuedBook $issuedBook, array $attributes): Fine
    {
        $values = [
            'student_id' => $issuedBook->student_id,
            ...$attributes,
        ];

        try {
            return DB::transaction(function () use ($issuedBook, $values) {
                $fine = Fine::where('issued_book_id', $issuedBook->id)
                    ->lockForUpdate()
                    ->first();

                if ($fine) {
                    if ($this->shouldLeaveExistingFineUntouched($fine, $values)) {
                        return $fine;
                    }

                    $fine->fill($values);
                    $fine->save();

                    return $fine;
                }

                return Fine::create([
                    'issued_book_id' => $issuedBook->id,
                    ...$values,
                ]);
            });
        } catch (QueryException $exception) {
            $fine = Fine::where('issued_book_id', $issuedBook->id)->first();

            if (! $fine) {
                throw $exception;
            }

            if ($this->shouldLeaveExistingFineUntouched($fine, $values)) {
                return $fine;
            }

            $fine->fill($values);
            $fine->save();

            return $fine;
        }
    }

    private function shouldLeaveExistingFineUntouched(Fine $fine, array $values): bool
    {
        return ($values['status'] ?? null) === 'pending'
            && in_array(strtolower((string) $fine->status), ['paid', 'waived'], true);
    }
}
