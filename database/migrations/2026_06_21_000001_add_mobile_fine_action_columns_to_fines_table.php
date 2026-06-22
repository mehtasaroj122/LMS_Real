<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            if (! Schema::hasColumn('fines', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid_on');
            }

            if (! Schema::hasColumn('fines', 'paid_by')) {
                $table->foreignId('paid_by')
                    ->nullable()
                    ->after('paid_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('fines', 'waive_reason')) {
                $table->text('waive_reason')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('fines', 'waived_at')) {
                $table->timestamp('waived_at')->nullable()->after('waive_reason');
            }

            if (! Schema::hasColumn('fines', 'waived_by')) {
                $table->foreignId('waived_by')
                    ->nullable()
                    ->after('waived_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            if (Schema::hasColumn('fines', 'paid_by')) {
                $table->dropForeign(['paid_by']);
                $table->dropColumn('paid_by');
            }

            if (Schema::hasColumn('fines', 'waived_by')) {
                $table->dropForeign(['waived_by']);
                $table->dropColumn('waived_by');
            }

            foreach (['paid_at', 'waive_reason', 'waived_at'] as $column) {
                if (Schema::hasColumn('fines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
