<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the legacy counters for existing APIs/reports, but make zero
        // the valid state for a title that has not received physical copies yet.
        Schema::table('books', function (Blueprint $table): void {
            $table->integer('total_copies')->default(0)->change();
            $table->integer('available_copies')->default(0)->change();
        });

        // Reconcile legacy counters with the authoritative copy records after
        // the copy/history migrations have run.
        DB::table('books')->orderBy('id')->chunkById(100, function ($books): void {
            foreach ($books as $book) {
                $total = (int) DB::table('book_copies')->where('book_id', $book->id)->count();
                $available = (int) DB::table('book_copies')
                    ->where('book_id', $book->id)
                    ->where('status', 'available')
                    ->count();

                DB::table('books')->where('id', $book->id)->update([
                    'total_copies' => $total,
                    'available_copies' => $available,
                    'status' => $available > 0 ? 'available' : 'unavailable',
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->integer('total_copies')->default(null)->change();
            $table->integer('available_copies')->default(null)->change();
        });
    }
};
