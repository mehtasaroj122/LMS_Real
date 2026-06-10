<?php

namespace App\Services\FineManagement;

use App\Models\Fine;
use App\Support\ProfilePhoto;
use Illuminate\Database\Eloquent\Builder;

class FineManagementDataService
{
    public function getListingData(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $query = $this->buildFilteredQuery($filters);

        $paginated = $query->paginate($filters['per_page'], ['*'], 'page', $filters['page']);

        return [
            'fines' => $paginated->getCollection()
                ->map(fn (Fine $fine) => $this->transformFine($fine))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'stats' => $this->buildStats($filters),
        ];
    }

    public function getExportData(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $collection = $this->buildFilteredQuery($filters)->get();

        return [
            'fines' => $collection
                ->map(fn (Fine $fine) => $this->transformFine($fine))
                ->values()
                ->all(),
            'meta' => [
                'count' => $collection->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    protected function normalizeFilters(array $filters): array
    {
        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => strtolower((string) ($filters['status'] ?? 'all')),
            'sort' => strtolower((string) ($filters['sort'] ?? 'date-desc')),
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'min_amount' => $this->normalizeNumericFilter($filters['min_amount'] ?? null),
            'max_amount' => $this->normalizeNumericFilter($filters['max_amount'] ?? null),
            'page' => max(1, (int) ($filters['page'] ?? 1)),
            'per_page' => max(1, min(100, (int) ($filters['per_page'] ?? 10))),
        ];
    }

    protected function normalizeNumericFilter(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    protected function baseQuery(): Builder
    {
        return Fine::query()
            ->with(['student.user', 'issuedBook.book'])
            ->whereHas('student.user', function (Builder $query) {
                $query->where('role', 'student');
            });
    }

    protected function buildFilteredQuery(array $filters): Builder
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort']);

        return $query;
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearchFilter($query, $filters['search']);
        $this->applyStatusFilter($query, $filters['status']);
        $this->applyDueDateFilter($query, $filters['date_from'], $filters['date_to']);
        $this->applyAmountFilter($query, $filters['min_amount'], $filters['max_amount']);
    }

    protected function applySearchFilter(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $subQuery) use ($search) {
            $subQuery->whereHas('student.user', function (Builder $userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('student', function (Builder $studentQuery) use ($search) {
                $studentQuery->where('roll_no', 'like', "%{$search}%");
            })->orWhereHas('issuedBook.book', function (Builder $bookQuery) use ($search) {
                $bookQuery->where('title', 'like', "%{$search}%");
            })->orWhere('remarks', 'like', "%{$search}%")
                ->orWhere('amount', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
        });
    }

    protected function applyStatusFilter(Builder $query, string $status): void
    {
        if ($status === '' || $status === 'all') {
            return;
        }

        if ($status === 'overdue') {
            $this->applyOverdueConstraint($query);
            return;
        }

        $query->where('status', $status);
    }

    protected function applyDueDateFilter(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        if (!$dateFrom && !$dateTo) {
            return;
        }

        $query->whereHas('issuedBook', function (Builder $issuedBookQuery) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $issuedBookQuery->whereDate('due_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $issuedBookQuery->whereDate('due_date', '<=', $dateTo);
            }
        });
    }

    protected function applyAmountFilter(Builder $query, ?float $minAmount, ?float $maxAmount): void
    {
        if ($minAmount !== null) {
            $query->where('amount', '>=', $minAmount);
        }

        if ($maxAmount !== null) {
            $query->where('amount', '<=', $maxAmount);
        }
    }

    protected function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'date-asc' => $query->orderBy('created_at', 'asc'),
            'amount-asc' => $query->orderBy('amount', 'asc')->orderBy('created_at', 'desc'),
            'amount-desc' => $query->orderBy('amount', 'desc')->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    protected function buildStats(array $filters): array
    {
        $statsQuery = $this->baseQuery();
        $this->applyFilters($statsQuery, $filters);

        return [
            'total' => round((float) (clone $statsQuery)->sum('amount'), 2),
            'collected' => round((float) (clone $statsQuery)->where('status', 'paid')->sum('amount'), 2),
            'pending' => round((float) (clone $statsQuery)->where('status', 'pending')->sum('amount'), 2),
            'waived' => round((float) (clone $statsQuery)->where('status', 'waived')->sum('amount'), 2),
            'count' => (clone $statsQuery)->count(),
            'paid_count' => (clone $statsQuery)->where('status', 'paid')->count(),
            'unpaid_count' => (clone $statsQuery)->where('status', 'pending')->count(),
            'waived_count' => (clone $statsQuery)->where('status', 'waived')->count(),
            'overdue_count' => $this->countOverdue(clone $statsQuery),
        ];
    }

    protected function countOverdue(Builder $query): int
    {
        $this->applyOverdueConstraint($query);

        return $query->count();
    }

    protected function applyOverdueConstraint(Builder $query): void
    {
        $query->whereHas('issuedBook', function (Builder $issuedBookQuery) {
            $issuedBookQuery->where('due_date', '<', now())
                ->whereNull('return_date');
        });
    }

    protected function transformFine(Fine $fine): array
    {
        $profilePhoto = $fine->student?->user?->profile_photo;
        $studentAvatar = ProfilePhoto::resolveUrl($profilePhoto);

        $status = strtolower((string) ($fine->status ?? 'pending'));
        $issuedBook = $fine->issuedBook;
        $dueDate = $issuedBook?->due_date;
        $isOverdue = $dueDate !== null
            && $dueDate->isPast()
            && $issuedBook?->return_date === null;

        return [
            'id' => $fine->id,
            'fineId' => 'FN-' . str_pad((string) $fine->id, 6, '0', STR_PAD_LEFT),
            'studentId' => $fine->student?->roll_no ?? 'N/A',
            'studentName' => $fine->student?->user?->name ?? 'Unknown',
            'studentAvatar' => $studentAvatar,
            'bookTitle' => $issuedBook?->book?->title ?? 'Unknown',
            'dueDate' => $dueDate?->format('M d, Y') ?? 'N/A',
            'dueDateRaw' => $dueDate?->format('Y-m-d'),
            'daysOverdue' => (int) ($fine->days_late ?? 0),
            'fineAmount' => (float) $fine->amount,
            'status' => $status,
            'statusLabel' => ucfirst($status),
            'createdAt' => $fine->created_at?->format('M d, Y') ?? 'N/A',
            'remarks' => $fine->remarks ?? '',
            'isOverdue' => $isOverdue,
        ];
    }
}
