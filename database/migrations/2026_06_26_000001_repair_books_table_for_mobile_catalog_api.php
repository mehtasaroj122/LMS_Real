<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair older hosted databases whose books table was created before the
     * current mobile catalog API columns existed.
     */
    public function up(): void
    {
        if (! Schema::hasTable('books')) {
            return;
        }

        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('books', 'title')) {
                $table->string('title', 200)->default('Untitled')->after('category_id');
            }

            if (! Schema::hasColumn('books', 'author')) {
                $table->string('author', 150)->nullable()->after('title');
            }

            if (! Schema::hasColumn('books', 'publisher')) {
                $table->string('publisher', 150)->nullable()->after('author');
            }

            if (! Schema::hasColumn('books', 'isbn')) {
                $table->string('isbn', 20)->nullable()->after('publisher');
            }

            if (! Schema::hasColumn('books', 'total_copies')) {
                $table->integer('total_copies')->default(1)->after('isbn');
            }

            if (! Schema::hasColumn('books', 'available_copies')) {
                $table->integer('available_copies')->default(0)->after('total_copies');
            }

            if (! Schema::hasColumn('books', 'condition')) {
                $table->string('condition', 20)->default('good')->after('available_copies');
            }

            if (! Schema::hasColumn('books', 'description')) {
                $table->text('description')->nullable()->after('condition');
            }

            if (! Schema::hasColumn('books', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('description');
            }

            if (! Schema::hasColumn('books', 'shelf_no')) {
                $table->string('shelf_no', 50)->nullable()->after('cover_image');
            }

            if (! Schema::hasColumn('books', 'status')) {
                $table->string('status', 20)->default('available')->after('shelf_no');
            }

            if (! Schema::hasColumn('books', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('books', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (
            Schema::hasColumn('books', 'isbn')
            && Schema::hasColumn('books', 'accession_no')
        ) {
            DB::table('books')
                ->whereNull('isbn')
                ->update(['isbn' => DB::raw('accession_no')]);
        }

        if (
            Schema::hasColumn('books', 'shelf_no')
            && Schema::hasColumn('books', 'location')
        ) {
            DB::table('books')
                ->whereNull('shelf_no')
                ->update(['shelf_no' => DB::raw('location')]);
        }
    }

    public function down(): void
    {
        // Intentionally no-op: this migration repairs production schema drift,
        // while the original create_books_table migration owns these columns.
    }
};
