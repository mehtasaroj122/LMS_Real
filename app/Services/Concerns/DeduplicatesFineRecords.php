<?php

namespace App\Services\Concerns;

use App\Models\Fine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait DeduplicatesFineRecords
{
    /**
     * Collapse multiple fine records that reference the same issued book down
     * to a single "keeper" record so the same book is never shown twice.
     */
    protected function collapseDuplicateFineRecords(Collection $fines): Collection
    {
        return $fines
            ->groupBy(fn (Fine $fine) => (string) ($fine->issued_book_id ?? 'fine-' . $fine->id))
            ->map(fn (Collection $duplicates) => $duplicates
                ->sort(fn (Fine $first, Fine $second) => $this->compareFineRecords($first, $second))
                ->first())
            ->values();
    }

    /**
     * Count the logical number of fine records (one per issued book) for a
     * filtered query without loading every row into memory.
     */
    protected function distinctIssuedBookCount(Builder $query): int
    {
        $base = (clone $query)->reorder()->toBase();
        $base->limit = null;
        $base->offset = null;

        $result = $base
            ->selectRaw('COUNT(DISTINCT issued_book_id) AS aggregate')
            ->first();

        return (int) ($result->aggregate ?? 0);
    }

    /**
     * Load only the columns needed for de-duplication and collapse the result.
     */
    protected function collapseFineRecordsQuery(Builder $query): Collection
    {
        $fines = (clone $query)
            ->select([
                'fines.id',
                'fines.issued_book_id',
                'fines.student_id',
                'fines.amount',
                'fines.days_late',
                'fines.status',
                'fines.updated_at',
            ])
            ->get();

        return $this->collapseDuplicateFineRecords($fines);
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
}
