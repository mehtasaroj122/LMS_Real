<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issued_books', function (Blueprint $table) {
            $table->foreignId('book_copy_id')
                ->nullable()
                ->after('book_id')
                ->constrained('book_copies')
                ->nullOnDelete();

            $table->index(['book_copy_id', 'return_date']);
        });

        $copyMap = DB::table('book_copies')
            ->orderBy('book_id')
            ->orderBy('id')
            ->get()
            ->groupBy('book_id');

        DB::table('issued_books')
            ->orderBy('book_id')
            ->orderBy('issue_date')
            ->orderBy('id')
            ->get()
            ->groupBy('book_id')
            ->each(function ($issues, $bookId) use ($copyMap): void {
                $copies = $copyMap->get($bookId, collect())->values();
                $availableAt = $copies->mapWithKeys(fn ($copy) => [$copy->id => null]);

                foreach ($issues as $issue) {
                    $issueDate = (string) $issue->issue_date;
                    $copy = $copies->first(function ($candidate) use ($availableAt, $issueDate) {
                        $releasedAt = $availableAt->get($candidate->id);

                        return $releasedAt === null || $releasedAt <= $issueDate;
                    });

                    // A malformed legacy overlap should not leave historical rows
                    // without a physical-copy reference. Reuse the first copy only
                    // for that exceptional history-preservation case.
                    $copy ??= $copies->first();

                    if (! $copy) {
                        return;
                    }

                    DB::table('issued_books')
                        ->where('id', $issue->id)
                        ->update(['book_copy_id' => $copy->id]);

                    $availableAt->put($copy->id, $issue->return_date ?: null);
                }
            });

        // Derive copy status from the latest known transaction while retaining
        // the original books table values for compatibility with existing pages.
        DB::table('book_copies')->orderBy('id')->chunkById(100, function ($copies): void {
            foreach ($copies as $copy) {
                $activeIssue = DB::table('issued_books')
                    ->where('book_copy_id', $copy->id)
                    ->whereNull('return_date')
                    ->latest('issue_date')
                    ->first();

                if ($activeIssue) {
                    DB::table('book_copies')->where('id', $copy->id)->update([
                        'status' => 'issued',
                        'updated_at' => now(),
                    ]);
                    continue;
                }

                $lastIssue = DB::table('issued_books')
                    ->where('book_copy_id', $copy->id)
                    ->whereNotNull('return_date')
                    ->latest('return_date')
                    ->first();

                $status = match ($lastIssue?->condition) {
                    'lost' => 'lost',
                    'damaged' => 'damaged',
                    default => 'available',
                };

                DB::table('book_copies')->where('id', $copy->id)->update([
                    'status' => $status,
                    'condition' => $lastIssue?->condition ?: $copy->condition,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('issued_books', function (Blueprint $table) {
            $table->dropForeign(['book_copy_id']);
            $table->dropIndex(['book_copy_id', 'return_date']);
            $table->dropColumn('book_copy_id');
        });
    }
};
