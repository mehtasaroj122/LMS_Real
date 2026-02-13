<?php

namespace Database\Seeders;

use App\Models\BookRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BookRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/BookRequests.json");
        $requests = collect(json_decode($json, true));

        $requests->each(function ($request) {
            BookRequest::create([
                'student_id' => $request['student_id'],
                'book_id' => $request['book_id'],
                'request_date' => $request['request_date'],
                'status' => $request['status'],
            ]);
        });
    }
}
