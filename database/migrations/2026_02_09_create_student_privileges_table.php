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
        Schema::create('student_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('max_books')->nullable()->comment('Override max books per student');
            $table->integer('issue_duration_days')->nullable()->comment('Override issue duration in days');
            $table->decimal('per_day_fine', 8, 2)->nullable()->comment('Override fine per day');
            $table->boolean('borrowing_allowed')->default(true)->comment('Can student borrow books');
            $table->timestamps();
            
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_privileges');
    }
};
