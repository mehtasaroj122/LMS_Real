<?php

namespace Database\Seeders;

use App\Models\StudentPrivilege;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StudentPrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/StudentPrivileges.json");
        $privileges = collect(json_decode($json, true));

        $privileges->each(function ($privilege) {
            StudentPrivilege::create([
                'student_id' => $privilege['student_id'],
                'max_books' => $privilege['max_books'],
                'issue_duration_days' => $privilege['issue_duration_days'],
                'per_day_fine' => $privilege['per_day_fine'],
                'borrowing_allowed' => $privilege['borrowing_allowed'],
            ]);
        });
    }
}
