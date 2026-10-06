<?php

namespace App\Services;

use App\Helpers\ActivityLogger;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PhysicalBookCopyDeletionService
{
    public const DELETE_SELECTED = 'delete_selected';
    public const DELETE_AVAILABLE = 'delete_available';
    public const DELETE_DAMAGED = 'delete_damaged';
    public const DELETE_LOST = 'delete_lost';
    public const DELETE_ALL_ELIGIBLE = 'delete_all_eligible';

    public const ACTIONS = [
        self::DELETE_SELECTED,
        self::DELETE_AVAILABLE,
        self::DELETE_DAMAGED,
        self::DELETE_LOST,
        self::DELETE_ALL_ELIGIBLE,
    ];

    public function __construct(private readonly PhysicalBookCopyService $copies)
    {
    }

    /**
     * The single source of truth for physical-copy deletion eligibility.
     * Borrowing history is deliberately preserved because issued_books uses
     * a nullable foreign key and a hard delete would sever its copy reference.
     */
    public function eligibility(BookCopy $copy): array
    {
        $activeIssue = $copy->relationLoaded('activeIssue')
            ? $copy->activeIssue
            : $copy->activeIssue()->with('student.user')->first();
        $status = strtolower(trim((string) $copy->status));

        if ($activeIssue || in_array($status, ['issued', 'borrowed', 'currently issued', 'checked out'], true)) {
            $borrower = $activeIssue?->student?->user?->name;

            return [
                'eligible' => false,
                'code' => 'currently_borrowed',
                'reason' => $borrower
                    ? "Currently borrowed by {$borrower}"
                    : 'Currently borrowed',
            ];
        }

        $historyCount = array_key_exists('issued_books_count', $copy->getAttributes())
            ? (int) $copy->issued_books_count
            : $copy->issuedBooks()->count();

        if ($historyCount > 0) {
            return [
                'eligible' => false,
                'code' => 'borrowing_history',
                'reason' => 'Protected because borrowing history must be preserved',
            ];
        }

        return ['eligible' => true, 'code' => null, 'reason' => null];
    }

    public function preview(Book $book, string $action, array $copyIds = []): array
    {
        $copyIds = $this->normalizeIds($copyIds);
        $copies = $this->candidateQuery($book, $action, $copyIds)->get();

        return $this->summarize($book, $action, $copyIds, $copies);
    }

    public function delete(Book $book, string $action, array $copyIds = [], ?string $reason = null): array
    {
        $copyIds = $this->normalizeIds($copyIds);

        $result = DB::transaction(function () use ($book, $action, $copyIds): array {
            // Issuing also locks the physical-copy row. Re-locking and then
            // re-evaluating here closes the page-load/submit race window.
            $lockedCopies = $this->candidateQuery($book, $action, $copyIds)
                ->lockForUpdate()
                ->get();
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
            $summary = $this->summarize($lockedBook, $action, $copyIds, $lockedCopies);
            $eligibleIds = collect($summary['eligible'])->pluck('id')->all();

            foreach (array_chunk($eligibleIds, 500) as $idChunk) {
                BookCopy::query()
                    ->where('book_id', $lockedBook->id)
                    ->whereIn('id', $idChunk)
                    ->delete();
            }

            $this->copies->refreshBookCounters($lockedBook);

            $summary['deleted_count'] = count($eligibleIds);
            $summary['deleted_copy_ids'] = $eligibleIds;
            $summary['deleted_accession_numbers'] = collect($summary['eligible'])
                ->pluck('accession_number')->values()->all();
            $summary['summary'] = $this->inventorySummary($lockedBook);

            return $summary;
        }, 3);

        if ($result['deleted_count'] > 0) {
            $this->logDeletion($book, $action, $result, $reason);
        }

        unset($result['eligible']);

        return $result;
    }

    private function candidateQuery(Book $book, string $action, array $copyIds): Builder
    {
        if (! in_array($action, self::ACTIONS, true)) {
            throw ValidationException::withMessages(['action' => 'Select a valid bulk action.']);
        }

        if ($action === self::DELETE_SELECTED && $copyIds === []) {
            throw ValidationException::withMessages(['copy_ids' => 'Select at least one physical copy.']);
        }

        return BookCopy::query()
            ->where('book_id', $book->id)
            ->when($action === self::DELETE_SELECTED, fn (Builder $query) => $query->whereIn('id', $copyIds))
            ->when($action === self::DELETE_AVAILABLE, fn (Builder $query) => $query->where('status', 'available'))
            ->when($action === self::DELETE_DAMAGED, fn (Builder $query) => $query->where('condition', 'damaged'))
            ->when($action === self::DELETE_LOST, fn (Builder $query) => $query->where(function (Builder $status): void {
                $status->where('status', 'lost')->orWhere('condition', 'lost');
            }))
            ->withCount('issuedBooks')
            ->with(['activeIssue.student.user'])
            ->orderBy('id');
    }

    private function summarize(Book $book, string $action, array $copyIds, Collection $copies): array
    {
        $eligible = [];
        $skipped = [];
        $borrowedCount = 0;
        $historyCount = 0;

        foreach ($copies as $copy) {
            $eligibility = $this->eligibility($copy);
            $item = [
                'id' => $copy->id,
                'accession_number' => $copy->accession_number,
                'status' => $copy->status,
                'condition' => $copy->condition,
            ];

            if ($eligibility['eligible']) {
                $eligible[] = $item;
                continue;
            }

            $skipped[] = $item + [
                'code' => $eligibility['code'],
                'reason' => $eligibility['reason'],
            ];
            $borrowedCount += $eligibility['code'] === 'currently_borrowed' ? 1 : 0;
            $historyCount += $eligibility['code'] === 'borrowing_history' ? 1 : 0;
        }

        $invalidCount = $action === self::DELETE_SELECTED
            ? max(0, count($copyIds) - $copies->count())
            : 0;

        return [
            'action' => $action,
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
            ],
            'requested_count' => $action === self::DELETE_SELECTED ? count($copyIds) : $copies->count(),
            'matched_count' => $copies->count(),
            'eligible_count' => count($eligible),
            'borrowed_count' => $borrowedCount,
            'history_protected_count' => $historyCount,
            'other_protected_count' => $historyCount + $invalidCount,
            'skipped_count' => count($skipped) + $invalidCount,
            'invalid_count' => $invalidCount,
            'eligible' => $eligible,
            'eligible_accession_numbers' => collect($eligible)->pluck('accession_number')->values()->all(),
            'skipped' => $skipped,
            'inventory' => $this->inventorySummary($book),
        ];
    }

    private function inventorySummary(Book $book): array
    {
        $counts = BookCopy::query()
            ->where('book_id', $book->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'available' => (int) ($counts['available'] ?? 0),
            'issued' => (int) ($counts['issued'] ?? 0),
            'lost' => (int) ($counts['lost'] ?? 0),
            'damaged' => (int) ($counts['damaged'] ?? 0),
        ];
    }

    private function normalizeIds(array $copyIds): array
    {
        return collect($copyIds)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function logDeletion(Book $book, string $action, array $result, ?string $reason): void
    {
        $actor = auth()->user();
        $actionLabel = match ($action) {
            self::DELETE_SELECTED => 'Delete Selected Copies',
            self::DELETE_AVAILABLE => 'Delete Available Copies',
            self::DELETE_DAMAGED => 'Delete Damaged Copies',
            self::DELETE_LOST => 'Delete Lost Copies',
            default => 'Delete All Eligible Copies',
        };
        $metadataLimit = 100;

        ActivityLogger::logActivity(
            'bulk_copies_deleted',
            sprintf(
                '%s %s deleted %d physical %s of "%s"; %d protected %s remained.',
                $actor?->name ?? 'System',
                ucfirst((string) ($actor?->role ?? 'user')),
                $result['deleted_count'],
                $result['deleted_count'] === 1 ? 'copy' : 'copies',
                $book->title,
                $result['skipped_count'],
                $result['skipped_count'] === 1 ? 'copy' : 'copies'
            ),
            'book',
            'book',
            $book->id,
            [
                'book_id' => $book->id,
                'book_title' => $book->title,
                'action_type' => $action,
                'action_label' => $actionLabel,
                'requested' => $result['requested_count'],
                'deleted' => $result['deleted_count'],
                'skipped' => $result['skipped_count'],
                'borrowed_protected' => $result['borrowed_count'],
                'history_protected' => $result['history_protected_count'],
                'reason' => $reason,
                'deleted_copy_ids' => array_slice($result['deleted_copy_ids'], 0, $metadataLimit),
                'deleted_accession_numbers' => array_slice($result['deleted_accession_numbers'], 0, $metadataLimit),
                'skipped_copies' => array_slice($result['skipped'], 0, $metadataLimit),
                'metadata_truncated' => max(0, $result['deleted_count'] - $metadataLimit),
            ]
        );
    }
}
