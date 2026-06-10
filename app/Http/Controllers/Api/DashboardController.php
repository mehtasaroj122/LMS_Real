<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\book as Book;
use App\Models\IssuedBook;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_books' => (int) Book::query()->sum('total_copies'),
            'total_students' => (int) Student::query()->count(),
            'issued_books' => (int) IssuedBook::query()->whereNull('return_date')->count(),
            'returned_books' => (int) IssuedBook::query()->whereNotNull('return_date')->count(),
            'overdue_books' => (int) IssuedBook::query()
                ->whereNull('return_date')
                ->whereDate('due_date', '<', today())
                ->count(),
        ], 200);
    }
}
