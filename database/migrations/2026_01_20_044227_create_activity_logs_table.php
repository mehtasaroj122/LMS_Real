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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('user_email')->nullable();
            $table->string('action');
            $table->string('action_category')->default('general');
            $table->string('status')->default('completed');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('browser')->nullable();
            $table->string('device_type')->nullable();
            $table->text('metadata')->nullable();
            $table->string('resource_type')->nullable();
            $table->string('resource_id')->nullable();
            $table->string('affected_user_id')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index('user_id');
            $table->index('action');
            $table->index('action_category');
            $table->index('user_role');
            $table->index(['created_at', 'user_role']);
            $table->index(['created_at', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
