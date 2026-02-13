<?php

namespace Database\Seeders;

use App\Models\category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/categories.json");
        $categories = collect(json_decode($json, true));

        $categories->each(function ($category) {
            Category::create([
                'name' => $category['name'],
                'description' => $category['description'],
            ]);
        });
    }
}
