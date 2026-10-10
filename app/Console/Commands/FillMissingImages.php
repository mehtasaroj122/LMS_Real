<?php

namespace App\Console\Commands;

use App\Services\MissingImageBackfill;
use Illuminate\Console\Command;

class FillMissingImages extends Command
{
    protected $signature = 'media:fill-missing-images {--dry-run : Preview counts without changing images}';

    protected $description = 'Fill missing book images and sample profile portraits while preserving existing photos';

    public function handle(MissingImageBackfill $images): int
    {
        $counts = $images->run((bool) $this->option('dry-run'));
        $this->table(['Image type', $this->option('dry-run') ? 'Would update' : 'Updated'], [
            ['Matched book covers', $counts['book_covers']],
            ['Generic book photographs', $counts['generic_book_photos']],
            ['Sample profile portraits', $counts['profile_photos']],
        ]);

        return self::SUCCESS;
    }
}
