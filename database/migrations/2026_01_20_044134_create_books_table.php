<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('author', 150)->nullable();
            $table->string('publisher', 150)->nullable();
            $table->string('isbn', 20)->unique();
            $table->integer('total_copies');
            $table->integer('available_copies');
            $table->enum('condition', ['new', 'good', 'damaged'])->default('good');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('shelf_no', 50)->nullable();
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
