<?php

namespace Database\Seeders;

use App\Models\BookCopy;
use App\Models\IssuedBook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IssuedBookSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get('database/JSON/IssuedBooks.json');
        $issuedBooks = collect(json_decode($json, true, 512, JSON_THROW_ON_ERROR));

        $issuedBooks->each(function (array $issue): void {
            $copy = BookCopy::query()
                ->where('accession_number', $issue['book_copy_accession'] ?? '')
                ->first();

            if (! $copy || (int) $copy->book_id !== (int) $issue['book_id']) {
                throw new \RuntimeException("Issue {$issue['id']} references an invalid physical copy.");
            }

            $condition = $issue['condition'] ?? null;

            IssuedBook::query()->updateOrCreate(['id' => (int) $issue['id']], [
                'book_id' => (int) $issue['book_id'],
                'book_copy_id' => $copy->id,
                'student_id' => (int) $issue['student_id'],
                'issued_by' => $issue['issued_by'] ?? null,
                'issue_date' => $issue['issue_date'],
                'due_date' => $issue['due_date'],
                'return_date' => $issue['return_date'],
                'status' => $issue['status'],
                'condition' => $condition,
                'fine_amount' => $issue['fine_amount'],
                'remarks' => $issue['remarks'],
            ]);

            $copy->update([
                'status' => $issue['return_date']
                    ? (in_array($condition, ['lost', 'damaged'], true) ? $condition : 'available')
                    : 'issued',
                'condition' => $condition ?: $copy->condition,
            ]);
        });
    }
}
