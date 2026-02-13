<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get("database/JSON/books.json");
        $books = collect(json_decode($json, true));

        $books->each(function ($book) {
            Book::create([
                'category_id' => $book['category_id'],
                'title' => $book['title'],
                'author' => $book['author'],
                'publisher' => $book['publisher'],
                'isbn' => $book['isbn'],
                'total_copies' => $book['total_copies'],
                'available_copies' => $book['available_copies'],
                'condition' => $book['condition'],
                'description' => $book['description'],
                'cover_image' => $book['cover_image'],
                'shelf_no' => $book['shelf_no'],
                'status' => $book['status'],
            ]);
        });
    }
}
