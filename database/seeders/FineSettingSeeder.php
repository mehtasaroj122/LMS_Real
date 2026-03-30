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
            FineSetting::defaults()
        );
    }
}
