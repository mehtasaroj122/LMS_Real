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
        Schema::create('fine_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('per_day_fine', 10, 2)->default(5.00);
            $table->integer('grace_period_days')->default(2);
            $table->decimal('max_fine_amount', 10, 2)->default(500.00);
            $table->decimal('lost_book_penalty', 10, 2)->default(1000.00);
            $table->decimal('damaged_book_penalty', 10, 2)->default(250.00);
            $table->decimal('fair_condition_penalty', 10, 2)->default(50.00);
            $table->integer('issue_duration_days')->default(14);
            $table->integer('max_books_per_student')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fine_settings');
    }
};
