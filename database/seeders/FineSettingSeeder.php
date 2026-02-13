<?php

namespace Database\Seeders;

use App\Models\FineSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FineSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default fine settings if not exists
        FineSetting::firstOrCreate(
            ['is_active' => true],
            [
                'per_day_fine' => 5.00,
                'grace_period_days' => 2,
                'max_fine_amount' => 500.00,
                'lost_book_penalty' => 1000.00,
                'damaged_book_penalty' => 250.00,
                'fair_condition_penalty' => 50.00,
                'issue_duration_days' => 14,
                'max_books_per_student' => 5,
            ]
        );
    }
}
