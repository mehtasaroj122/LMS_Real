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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('book_overdue')->default(true);
            $table->boolean('book_due_soon')->default(true);
            $table->boolean('fine_created')->default(true);
            $table->boolean('fine_reminder')->default(true);
            $table->boolean('request_status_change')->default(true);
            $table->boolean('new_book_available')->default(true);
            $table->boolean('payment_confirmation')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
