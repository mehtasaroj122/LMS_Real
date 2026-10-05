<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accession_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10)->unique();
            $table->unsignedBigInteger('next_number')->default(0);
            $table->timestamps();
        });

        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('accession_number', 32)->unique();
            $table->date('entry_date')->nullable();
            $table->string('book_type', 30)->default('borrowing');
            $table->string('status', 30)->default('available');
            $table->string('shelf_location', 100)->nullable();
            $table->string('condition', 30)->default('good');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'status']);
            $table->index('book_type');
        });

        DB::table('accession_sequences')->insert([
            'prefix' => 'ACC',
            'next_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Legacy records only identify a title and a quantity, not individual copies.
        // Preserve the declared quantity without inventing copy-specific history.
        $nextAccession = 0;
        DB::table('books')
            ->select(['id', 'total_copies', 'condition', 'shelf_no', 'created_at'])
            ->orderBy('id')
            ->chunkById(100, function ($books) use (&$nextAccession): void {
                $rows = [];

                foreach ($books as $book) {
                    $quantity = max(0, (int) $book->total_copies);
                    $condition = in_array($book->condition, ['new', 'good', 'damaged'], true)
                        ? $book->condition
                        : 'good';

                    for ($copyNumber = 0; $copyNumber < $quantity; $copyNumber++) {
                        $nextAccession++;
                        $rows[] = [
                            'book_id' => $book->id,
                            'accession_number' => 'ACC-' . str_pad((string) $nextAccession, 6, '0', STR_PAD_LEFT),
                            'entry_date' => $book->created_at ? substr((string) $book->created_at, 0, 10) : null,
                            'book_type' => 'borrowing',
                            'status' => 'available',
                            'shelf_location' => $book->shelf_no,
                            'condition' => $condition,
                            'remarks' => 'Created during physical-copy migration.',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('book_copies')->insert($rows);
                }
            });

        DB::table('accession_sequences')
            ->where('prefix', 'ACC')
            ->update(['next_number' => $nextAccession, 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('accession_sequences');
    }
};
