<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activeLoan = fn ($query) => $query->selectRaw('1')->from('issued_books')
            ->whereColumn('issued_books.book_copy_id', 'book_copies.id')
            ->whereNull('issued_books.return_date');

        DB::table('book_copies')
            ->where(function ($query) use ($activeLoan): void {
                $query->where(fn ($copies) => $copies->where('status', 'issued')->whereNotExists($activeLoan))
                    ->orWhere(fn ($copies) => $copies->where('status', 'available')->whereExists($activeLoan));
            })
            ->orderBy('id')
            ->chunkById(200, function ($copies): void {
                foreach ($copies as $candidate) {
                    DB::transaction(function () use ($candidate): void {
                        $copy = DB::table('book_copies')->where('id', $candidate->id)->lockForUpdate()->first();
                        if (! $copy || ! in_array($copy->status, ['available', 'issued'], true)) {
                            return;
                        }

                        $active = DB::table('issued_books')->where('book_copy_id', $copy->id)
                            ->whereNull('return_date')->lockForUpdate()->exists();
                        $status = $active ? 'issued' : (in_array($copy->condition, ['lost', 'damaged'], true) ? $copy->condition : 'available');
                        if ($status === $copy->status) {
                            return;
                        }

                        $book = DB::table('books')->where('id', $copy->book_id)->lockForUpdate()->first();
                        DB::table('book_copies')->where('id', $copy->id)->update([
                            'status' => $status,
                            'updated_at' => now(),
                        ]);
                        $total = DB::table('book_copies')->where('book_id', $copy->book_id)->count();
                        $available = DB::table('book_copies')->where('book_id', $copy->book_id)->where('status', 'available')->count();
                        // Keep explicit catalogue restrictions while refreshing legacy counters.
                        DB::table('books')->where('id', $copy->book_id)->update([
                            'total_copies' => $total,
                            'available_copies' => $available,
                            'status' => in_array($book->status, ['inactive', 'withdrawn'], true)
                                ? $book->status : ($available > 0 ? 'available' : 'unavailable'),
                        ]);
                    }, 3);
                }
            });
    }

    public function down(): void
    {
        // Data repair: restoring stale flags would make circulation inconsistent again.
    }
};
