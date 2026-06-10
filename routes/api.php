<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| These endpoints are reserved for the Kotlin Android application. Every
| endpoint returns JSON and uses Sanctum bearer tokens, except login.
|
*/

// Authenticate a mobile user and return a Sanctum bearer token.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Manually return a 401 JSON response for Android/Postman security testing.
Route::get('/test-unauthorized', [AuthController::class, 'testUnauthorized']);

Route::middleware('auth:sanctum')->group(function () {
    // Revoke the current mobile bearer token.
    Route::post('/logout', [AuthController::class, 'logout']);

    // Return the authenticated user's profile and linked student/staff record.
    Route::get('/profile', [AuthController::class, 'profile']);

    // List all books with Android-friendly inventory field names.
    Route::get('/books', [BookController::class, 'index']);

    // Search books by title, author, publisher, ISBN/accession number, or category.
    Route::get('/books/search', [BookController::class, 'search']);

    // List books that currently have at least one available copy.
    Route::get('/books/available', [BookController::class, 'available']);

    // List books in a category by category id or category name.
    Route::get('/books/category/{category}', [BookController::class, 'category']);

    // Show one book record.
    Route::get('/books/{id}', [BookController::class, 'show'])->whereNumber('id');

    // List all registered students with user and department data.
    Route::get('/students', [StudentController::class, 'index']);

    // Search students by name, roll number, email, phone, or faculty/department.
    Route::get('/students/search', [StudentController::class, 'search']);

    // Show one student record.
    Route::get('/students/{id}', [StudentController::class, 'show'])->whereNumber('id');

    // Issue one or more books to a student.
    Route::post('/issues', [IssueController::class, 'store']);

    // List issued book transactions.
    Route::get('/issues', [IssueController::class, 'index']);

    // Return an issued book and calculate any applicable fine.
    Route::post('/issues/return/{id}', [IssueController::class, 'returnBook'])->whereNumber('id');

    // List issue transactions for a specific student.
    Route::get('/issues/student/{studentId}', [IssueController::class, 'studentIssues'])->whereNumber('studentId');

    // Show one issue transaction.
    Route::get('/issues/{id}', [IssueController::class, 'show'])->whereNumber('id');

    // Return dashboard totals for Android home screens.
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // List active issues whose due date has passed.
    Route::get('/overdue', [IssueController::class, 'overdue']);

    // List all fine records.
    Route::get('/fines', [IssueController::class, 'fines']);

    // List fine records for a specific student.
    Route::get('/fines/student/{id}', [IssueController::class, 'studentFines'])->whereNumber('id');
});
