<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookRequestController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IssueController;
use App\Http\Controllers\Api\LibrarySettingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StaffDashboardController;
use App\Http\Controllers\Api\StudentBookController;
use App\Http\Controllers\Api\StudentBookRequestController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Http\Controllers\Api\StudentFineController;
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

// Public password reset endpoints for Android.
Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

// Manually return a 401 JSON response for Android/Postman security testing.
Route::get('/test-unauthorized', [AuthController::class, 'testUnauthorized']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth/session helpers.
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/auth/check', [AuthController::class, 'check']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto']);
    Route::get('/profile/delete-eligibility', [ProfileController::class, 'deleteEligibility']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // Catalog APIs available to all authenticated roles.
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('/books/available', [BookController::class, 'available']);
    Route::get('/books/category/{category}', [BookController::class, 'category']);
    Route::get('/books/{id}', [BookController::class, 'show'])->whereNumber('id');
    Route::get('/categories', [CategoryController::class, 'index']);

    // Existing general dashboard totals for Android home screens.
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Authenticated notification management.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/count', [NotificationController::class, 'count']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->whereNumber('id');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');

    Route::get('/library/settings', LibrarySettingController::class);

    Route::middleware('role:student')->group(function () {
        Route::get('/student/dashboard', StudentDashboardController::class);
        Route::get('/student/my-books', [StudentBookController::class, 'index']);
        Route::get('/student/my-books/summary', [StudentBookController::class, 'summary']);
        Route::get('/student/my-books/current', [StudentBookController::class, 'current']);
        Route::get('/student/my-books/history', [StudentBookController::class, 'history']);
        Route::get('/student/my-books/due-soon', [StudentBookController::class, 'dueSoon']);

        Route::get('/student/requests', [StudentBookRequestController::class, 'index']);
        Route::post('/student/requests', [StudentBookRequestController::class, 'store']);
        Route::get('/student/requests/summary', [StudentBookRequestController::class, 'summary']);
        Route::get('/student/requests/{id}', [StudentBookRequestController::class, 'show'])->whereNumber('id');
        Route::post('/student/requests/{id}/cancel', [StudentBookRequestController::class, 'cancel'])->whereNumber('id');

        Route::get('/student/fines', [StudentFineController::class, 'index']);
        Route::get('/student/fines/pending', [StudentFineController::class, 'pending']);
        Route::get('/student/fines/paid', [StudentFineController::class, 'paid']);
        Route::get('/student/fines/summary', [StudentFineController::class, 'summary']);
        Route::get('/student/fines/{id}', [StudentFineController::class, 'show'])->whereNumber('id');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/search', [StudentController::class, 'search']);
        Route::get('/students/{id}', [StudentController::class, 'show'])->whereNumber('id');

        Route::post('/issues', [IssueController::class, 'store']);
        Route::get('/issues', [IssueController::class, 'index']);
        Route::post('/issues/return/{id}', [IssueController::class, 'returnBook'])->whereNumber('id');
        Route::get('/issues/student/{studentId}', [IssueController::class, 'studentIssues'])->whereNumber('studentId');
        Route::get('/issues/{id}', [IssueController::class, 'show'])->whereNumber('id');

        Route::get('/overdue', [IssueController::class, 'overdue']);
        Route::get('/fines', [IssueController::class, 'fines']);
        Route::get('/fines/student/{id}', [IssueController::class, 'studentFines'])->whereNumber('id');

        Route::get('/staff/dashboard', StaffDashboardController::class);
        Route::get('/book-requests', [BookRequestController::class, 'index']);
        Route::get('/book-requests/{id}', [BookRequestController::class, 'show'])->whereNumber('id');
        Route::post('/book-requests/{id}/approve', [BookRequestController::class, 'approve'])->whereNumber('id');
        Route::post('/book-requests/{id}/reject', [BookRequestController::class, 'reject'])->whereNumber('id');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', AdminDashboardController::class);
        Route::get('/activity-logs', ActivityLogController::class);
    });
});
