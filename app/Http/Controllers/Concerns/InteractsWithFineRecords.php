<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Fine;
use Illuminate\Validation\ValidationException;

trait InteractsWithFineRecords
{
    protected function loadFineRecord(string $id): Fine
    {
        return Fine::with(['student.user', 'student.privileges', 'issuedBook.book'])->findOrFail($id);
    }

    protected function ensureFineIsActionable(Fine $fine): void
    {
        if (strtolower((string) $fine->status) !== 'pending') {
            throw ValidationException::withMessages([
                'fine' => 'Only pending fines can be updated from this page.',
            ]);
        }
    }

    protected function buildFineHistoryMetadata(Fine $fine, array $overrides = []): array
    {
        $book = $fine->issuedBook?->book;

        return array_filter([
            'fine_id' => $fine->id,
            'issued_book_id' => $fine->issued_book_id,
            'book_title' => $book?->title,
            'isbn' => $book?->isbn,
            'days_late' => (int) ($fine->days_late ?? 0),
            'status' => strtolower((string) $fine->status),
            ...$overrides,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
