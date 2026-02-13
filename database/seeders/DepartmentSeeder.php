<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Facades\File;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/departments.json");
        $departments = collect(json_decode($json, true));

        $departments->each(function ($department) {
           Department::create([
             'name' => $department['name'],
             'code' => $department['code'],
             'status' => $department['status'],
           ]);
        });
    }
}
