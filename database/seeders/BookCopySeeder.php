<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BookCopySeeder extends Seeder
{
    public function run(): void
    {
        $path = 'database/JSON/book_copies.json';
        if (! File::exists($path)) {
            throw new \RuntimeException("Missing {$path}.");
        }

        $copies = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $bookIds = Book::query()->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $accessions = [];
        $highestAccession = 0;

        foreach ($copies as $copy) {
            $bookId = (int) ($copy['book_id'] ?? 0);
            $accession = strtoupper(trim((string) ($copy['accession_number'] ?? '')));

            if (! $bookIds->has($bookId)) {
                throw new \RuntimeException("Book copy {$accession} references missing book {$bookId}.");
            }

            if ($accession === '' || isset($accessions[$accession])) {
                throw new \RuntimeException("Duplicate or missing accession number: {$accession}.");
            }

            if (! preg_match('/^ACC-(\d{6})$/', $accession, $matches)) {
                throw new \RuntimeException("Invalid accession number: {$accession}.");
            }

            $accessions[$accession] = true;
            $highestAccession = max($highestAccession, (int) $matches[1]);

            BookCopy::query()->updateOrCreate(
                ['accession_number' => $accession],
                [
                    'book_id' => $bookId,
                    'entry_date' => $copy['entry_date'] ?? null,
                    'book_type' => $copy['book_type'] ?? 'borrowing',
                    'status' => $copy['status'] ?? 'available',
                    'shelf_location' => $copy['shelf_location'] ?? null,
                    'condition' => $copy['condition'] ?? 'good',
                    'remarks' => $copy['remarks'] ?? null,
                ],
            );
        }

        DB::table('accession_sequences')->updateOrInsert(
            ['prefix' => 'ACC'],
            ['next_number' => $highestAccession, 'updated_at' => now(), 'created_at' => now()],
        );

        Book::query()->each(function (Book $book): void {
            $total = $book->copies()->count();
            $available = $book->copies()->where('status', 'available')->count();
            $book->forceFill([
                'total_copies' => $total,
                'available_copies' => $available,
                'status' => $available > 0 ? 'available' : 'unavailable',
            ])->saveQuietly();
        });
    }
}
