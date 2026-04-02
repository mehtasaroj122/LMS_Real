<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fine_settings', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('renewal_duration_days');
            $table->string('logo_fallback_text', 10)->default('LMS')->after('logo_path');
        });

        DB::table('fine_settings')
            ->whereNull('logo_fallback_text')
            ->update(['logo_fallback_text' => 'LMS']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fine_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'logo_fallback_text']);
        });
    }
};
