<?php

namespace Database\Seeders;

use App\Models\IssuedBook;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IssuedBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/IssuedBooks.json");
        $issuedBooks = collect(json_decode($json, true));

        $issuedBooks->each(function ($issue) {
            IssuedBook::create([
                'book_id' => $issue['book_id'],
                'student_id' => $issue['student_id'],
                'issued_by' => $issue['issued_by'],
                'issue_date' => $issue['issue_date'],
                'due_date' => $issue['due_date'],
                'return_date' => $issue['return_date'],
                'status' => $issue['status'],
                'fine_amount' => $issue['fine_amount'],
                'remarks' => $issue['remarks'],
            ]);
        });
    }
}
