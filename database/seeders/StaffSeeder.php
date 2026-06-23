<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/staff.json");
        $staff = collect(json_decode($json, true));

        $staff->each(function ($member) {
            staff::create([
                'user_id' => $member['user_id'],
                'staff_id' => $member['staff_id'] ?? ('STAFF-' . str_pad((string) $member['user_id'], 6, '0', STR_PAD_LEFT)),
                'department_id' => $member['department_id'],
                'designation' => $member['designation'],
                'join_date' => $member['join_date'],
            ]);
        });
    }
}
