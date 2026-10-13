<?php

namespace App\Services;

use App\Exceptions\PhysicalCopyException;
use App\Helpers\ActivityLogger;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhysicalBookCopyArchiveService
{
    public function __construct(
        private readonly PhysicalBookCopyService $copies,
        private readonly PhysicalBookCopyDeletionService $deletions
    ) {}

    public function preview(Book $book, array $copyIds): array
    {
        return $this->summarize($book, $copyIds, $this->candidates($book, $copyIds)->get());
    }

    public function archive(Book $book, array $copyIds, ?string $reason = null): array
    {
        return DB::transaction(function () use ($book, $copyIds, $reason): array {
            // Issuing takes the same copy lock before the book lock. Recheck the
            // active loan after locking, including changes since the preview.
            $lockedCopies = $this->candidates($book, $copyIds)->lockForUpdate()->get();
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
            $result = $this->summarize($lockedBook, $copyIds, $lockedCopies);
            $ids = $result['eligible_copy_ids'];

            if ($ids !== []) {
                BookCopy::query()->where('book_id', $book->id)->whereIn('id', $ids)->update([
                    'archived_at' => now(),
                    'archived_by' => auth()->id(),
                    'archive_reason' => $reason,
                ]);
                $this->copies->refreshBookCounters($lockedBook);
                $this->log('copies_archived', $lockedBook, $result['eligible_accession_numbers'], $reason);
            }

            $result['archived_count'] = count($ids);

            return $result;
        }, 3);
    }

    public function restore(BookCopy $copy): BookCopy
    {
        return DB::transaction(function () use ($copy): BookCopy {
            $lockedCopy = BookCopy::withTrashed()->lockForUpdate()->findOrFail($copy->id);
            $book = Book::query()->lockForUpdate()->findOrFail($lockedCopy->book_id);
            if (! $lockedCopy->trashed()) {
                throw new PhysicalCopyException('This copy is already in active inventory.', 409, 'copy_not_archived');
            }
            if ($lockedCopy->issuedBooks()->whereNull('return_date')->exists()) {
                throw new PhysicalCopyException('A borrowed copy cannot be restored. Return it first.', 409, 'currently_borrowed');
            }

            $reason = $lockedCopy->archive_reason;
            $lockedCopy->forceFill([
                'archived_by' => null,
                'archive_reason' => null,
                'status' => match ($lockedCopy->condition) {
                    'lost' => 'lost',
                    'damaged' => 'damaged',
                    default => 'available',
                },
            ]);
            $lockedCopy->restore();
            $this->copies->refreshBookCounters($book);
            $this->log('copy_restored', $book, [$lockedCopy->accession_number], $reason);

            return $lockedCopy->refresh();
        }, 3);
    }

    private function candidates(Book $book, array $copyIds): Builder
    {
        return BookCopy::query()->where('book_id', $book->id)->whereIn('id', $copyIds)
            ->with('activeIssue.student.user')->orderBy('id');
    }

    private function summarize(Book $book, array $copyIds, Collection $copies): array
    {
        $eligibleIds = [];
        $accessions = [];
        $skipped = [];
        foreach ($copies as $copy) {
            $eligibility = $this->deletions->archiveEligibility($copy);
            if ($eligibility['eligible']) {
                $eligibleIds[] = $copy->id;
                $accessions[] = $copy->accession_number;
            } else {
                $skipped[] = [
                    'id' => $copy->id,
                    'accession_number' => $copy->accession_number,
                    'code' => $eligibility['code'],
                    'reason' => $eligibility['reason'],
                ];
            }
        }
        $invalidCount = count(array_unique($copyIds)) - $copies->count();

        return [
            'book' => ['id' => $book->id, 'title' => $book->title, 'isbn' => $book->isbn],
            'requested_count' => count(array_unique($copyIds)),
            'matched_count' => $copies->count(),
            'eligible_count' => count($eligibleIds),
            'eligible_copy_ids' => $eligibleIds,
            'eligible_accession_numbers' => $accessions,
            'borrowed_count' => count($skipped),
            'other_protected_count' => $invalidCount,
            'invalid_count' => $invalidCount,
            'skipped_count' => count($skipped) + $invalidCount,
            'skipped' => $skipped,
            'inventory' => ['total' => BookCopy::query()->where('book_id', $book->id)->count()],
        ];
    }

    private function log(string $action, Book $book, array $accessions, ?string $reason): void
    {
        $verb = $action === 'copies_archived' ? 'archived' : 'restored';
        ActivityLogger::logActivity(
            $action,
            sprintf('%s %s %d physical copy/copies of "%s".', auth()->user()?->name ?? 'System', $verb, count($accessions), $book->title),
            'book',
            'book',
            $book->id,
            [
                'book_id' => $book->id,
                'count' => count($accessions),
                'accession_numbers' => array_slice($accessions, 0, 100),
                'reason' => $reason,
            ]
        );
    }
}
