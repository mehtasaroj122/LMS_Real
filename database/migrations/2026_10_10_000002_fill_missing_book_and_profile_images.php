<?php

use App\Services\MissingImageBackfill;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(MissingImageBackfill::class)->run();
    }

    public function down(): void
    {
        // Preserve image data across rollbacks, including later user photo changes.
    }
};
