<?php

namespace Database\Seeders;

use App\Models\Fine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/fines.json");
        $fines = collect(json_decode($json, true));

        $fines->each(function ($fine) {
            Fine::updateOrCreate([
                'issued_book_id' => $fine['issued_book_id'],
            ], [
                'student_id' => $fine['student_id'],
                'amount' => $fine['amount'],
                'days_late' => $fine['days_late'],
                'status' => $fine['status'],
                'paid_on' => $fine['paid_on'],
                'payment_method' => $fine['payment_method'],
                'remarks' => $fine['remarks'],
            ]);
        });
    }
}
