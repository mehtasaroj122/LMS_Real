<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/students.json");
        $students = collect(json_decode($json, true));

        $students->each(function ($student) {
            Student::create([
                'user_id' => $student['user_id'],
                'department_id' => $student['department_id'],
                'roll_no' => $student['roll_no'],
                'batch' => $student['batch'],
                'semester' => $student['semester'],
                'address' => $student['address'],
            ]);
        });
    }
}
