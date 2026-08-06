<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentFineSummaryService
{
    public function __construct(
        private readonly FineCalculator $fineCalculator
    ) {
    }

    public function pendingAmount(Student $student): float
    {
        return (float) $this->pendingItems($student)->sum(fn (Fine $fine) => (float) $fine->amount);
    }

    public function pendingItems(Student $student): Collection
    {
        $pendingFines = $this->studentFineQuery($student)
            ->with(['issuedBook.book'])
            ->where('status', 'pending')
            ->get();

        return $this->collapseDuplicateFineRecords($pendingFines)
            ->concat($this->virtualOverdueFines($student))
            ->values();
    }

    public function allFinesWithOpenOverdues(Student $student): Collection
    {
        $fines = $this->studentFineQuery($student)
            ->with(['issuedBook.book'])
            ->latest('created_at')
            ->get();

        return $this->collapseDuplicateFineRecords($fines)
            ->concat($this->virtualOverdueFines($student))
            ->sortByDesc(fn (Fine $fine) => $fine->created_at?->timestamp
                ?? $fine->issuedBook?->due_date?->timestamp
                ?? 0)
            ->values();
    }

    public function openOverdueBookCount(Student $student): int
    {
        return IssuedBook::query()
            ->where('student_id', $student->id)
            ->whereNull('return_date')
            ->whereDate('due_date', '<', today())
            ->count();
    }

    public function syncPendingOpenOverdueFines(Student $student): Collection
    {
        return IssuedBook::query()
            ->with(['student.privileges', 'book', 'fine'])
            ->where('student_id', $student->id)
            ->whereNull('return_date')
            ->whereDate('due_date', '<', today())
            ->get()
            ->filter(fn (IssuedBook $issuedBook) => !$issuedBook->fine)
            ->map(function (IssuedBook $issuedBook) {
                $calculation = $this->fineCalculator->calculateFine($issuedBook);

                if (!$calculation || (float) ($calculation['amount'] ?? 0) <= 0) {
                    return null;
                }

                return $this->fineCalculator->applyFine($issuedBook);
            })
            ->filter()
            ->values();
    }

    public function displayFineForIssue(IssuedBook $issuedBook): array
    {
        $fine = $issuedBook->fine;

        if ($fine?->status === 'pending') {
            return [
                'amount' => (float) $fine->amount,
                'status' => 'unpaid',
            ];
        }

        if ($this->isOpenOverdue($issuedBook)) {
            $calculation = $this->fineCalculator->calculateFine($issuedBook);
            $amount = (float) ($calculation['amount'] ?? 0);

            if ($amount > 0) {
                return [
                    'amount' => $amount,
                    'status' => 'unpaid',
                ];
            }
        }

        if ($fine) {
            return [
                'amount' => (float) $fine->amount,
                'status' => match ($fine->status) {
                    'pending' => 'unpaid',
                    'paid' => 'paid',
                    'waived' => 'waived',
                    default => 'none',
                },
            ];
        }

        return [
            'amount' => 0.0,
            'status' => 'none',
        ];
    }

    private function virtualOverdueFines(Student $student): Collection
    {
        return IssuedBook::query()
            ->with(['student.privileges', 'book', 'fine'])
            ->where('student_id', $student->id)
            ->whereNull('return_date')
            ->whereDate('due_date', '<', today())
            ->whereDoesntHave('fine', fn (Builder $query) => $query->where('status', 'pending'))
            ->get()
            ->map(function (IssuedBook $issuedBook) {
                $calculation = $this->fineCalculator->calculateFine($issuedBook);

                $fine = new Fine();
                $fine->id = null;
                $fine->issued_book_id = $issuedBook->id;
                $fine->student_id = $issuedBook->student_id;
                $fine->amount = (float) ($calculation['amount'] ?? 0);
                $fine->days_late = (int) ($calculation['days_late'] ?? 0);
                $fine->status = 'pending';
                $fine->remarks = ($calculation['is_within_grace'] ?? false) ? 'Within grace period' : 'Current overdue fine';
                $fine->created_at = $issuedBook->due_date;
                $fine->updated_at = now();
                $fine->setRelation('issuedBook', $issuedBook);
                $fine->setRelation('student', $issuedBook->student);

                return $fine;
            })
            ->values();
    }

    private function studentFineQuery(Student $student): Builder
    {
        return Fine::query()
            ->where(function (Builder $query) use ($student) {
                $query
                    ->where('student_id', $student->id)
                    ->orWhereHas('issuedBook', function (Builder $issueQuery) use ($student) {
                        $issueQuery->where('student_id', $student->id);
                    });
            });
    }

    private function collapseDuplicateFineRecords(Collection $fines): Collection
    {
        return $fines
            ->groupBy(fn (Fine $fine) => (string) ($fine->issued_book_id ?? 'fine-' . $fine->id))
            ->map(fn (Collection $duplicates) => $duplicates
                ->sort(fn (Fine $first, Fine $second) => $this->compareFineRecords($first, $second))
                ->first())
            ->values();
    }

    private function compareFineRecords(Fine $first, Fine $second): int
    {
        return [
            $this->fineRecordPriority($second),
            (int) ($second->days_late ?? 0),
            (float) ($second->amount ?? 0),
            $second->updated_at?->timestamp ?? 0,
            (int) ($second->id ?? 0),
        ] <=> [
            $this->fineRecordPriority($first),
            (int) ($first->days_late ?? 0),
            (float) ($first->amount ?? 0),
            $first->updated_at?->timestamp ?? 0,
            (int) ($first->id ?? 0),
        ];
    }

    private function fineRecordPriority(Fine $fine): int
    {
        return match (strtolower((string) $fine->status)) {
            'paid' => 4,
            'waived' => 3,
            'pending' => 2,
            default => 1,
        };
    }

    private function isOpenOverdue(IssuedBook $issuedBook): bool
    {
        return $issuedBook->return_date === null
            && $issuedBook->due_date
            && $issuedBook->due_date->startOfDay()->lt(today());
    }
}
