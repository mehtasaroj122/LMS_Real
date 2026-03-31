<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\FineController;
use App\Http\Controllers\Admin\BookRequestController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\AccountLockController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\BookRequestController as StaffBookRequestController;
use App\Http\Controllers\Staff\FineController as StaffFineController;
use App\Http\Controllers\Staff\IssueBookController;
use App\Http\Controllers\Staff\BookManagementController;
use App\Http\Controllers\Staff\ActivityLogController as StaffActivityLogController;
use App\Http\Controllers\Staff\StudentsController as StaffStudentsController;
use App\Http\Controllers\Staff\SettingController as StaffSettingController;
use App\Http\Controllers\Staff\ReturnBookController;
use App\Http\Controllers\Staff\BookDeletionRequestController;
use App\Http\Controllers\Staff\NotificationController as StaffNotificationController;

use App\Http\Controllers\student\DashboardController;
use App\Http\Controllers\student\SearchBookController;
use App\Http\Controllers\student\ProfileController as StudentProfileController;
use App\Http\Controllers\student\MyRequestsController;
use App\Http\Controllers\student\MyFinesController;
use App\Http\Controllers\student\MyBooksController;
use App\Http\Controllers\student\NotificationController as StudentNotificationController;
use App\Http\Controllers\PasswordChangeController;



Route::get('/', function () {
    return view('LandingPage');
});

Route::get('/lan', function () {
    return view('LandingPage');
});

Route::get('/lan2', function () {
    return view('LandingPage2');
});

Route::get('/lan3', function () {
    return view('LandingPage3');
});

Route::get('/login1', function () {
    return view('Loginpage');
});

// admin route
Route::middleware(['auth', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/users/data', [UserController::class, 'getUsersData'])->name('users.data');
        Route::get('/users/stats', [UserController::class, 'getStats'])->name('users.stats');

        Route::get('/books/data', [BookController::class, 'getBooksData'])->name('books.data');
        Route::get('/books/stats', [BookController::class, 'getBookStats'])->name('books.stats');
        Route::post('/books/validate-field', [BookController::class, 'validateField'])->name('books.validate-field');
        Route::post('/categories', [BookController::class, 'createCategory'])->name('categories.store');

        Route::get('/book-requests/data', [BookRequestController::class, 'getRequestsData'])->name('book-requests.data');
        Route::get('/book-requests/stats', [BookRequestController::class, 'getRequestStats'])->name('book-requests.stats');

        Route::get('/students/data', [StudentController::class, 'getStudentsData'])->name('students.data');
        Route::get('/students/stats', [StudentController::class, 'getStudentsStats'])->name('students.stats');
        Route::post('/students/validate-field', [StudentController::class, 'validateField'])->name('students.validate-field');
        Route::get('/students/{student}/edit-data', [StudentController::class, 'getStudentEditData'])->name('students.edit-data');
        Route::post('/students/{student}/reset-password', [StudentController::class, 'resetPassword'])
            ->name('students.reset-password');
        Route::post('/students/{student}/deactivate', [StudentController::class, 'deactivate'])
            ->name('students.deactivate');
        Route::post('/students/{student}/activate', [StudentController::class, 'activate'])
            ->name('students.activate');
        Route::put('/students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])
            ->name('students.toggle-status');
        Route::post('/students/{student}/change-role', [StudentController::class, 'changeRole'])
            ->name('students.change-role');

        Route::resource('books', BookController::class);
        Route::post('/users/validate-field', [UserController::class, 'validateField'])->name('users.validate-field');
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/users/{user}/details', [UserController::class, 'getDetails'])->name('users.details');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])
            ->name('users.status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');

        Route::resource('students', StudentController::class);
        Route::get('/students/{student}/fines', [StudentController::class, 'getStudentFines'])->name('students.fines');
        Route::get('/students/{student}/receipt', [StudentController::class, 'generateReceipt'])->name('students.receipt');
        Route::get('/students/{student}/privileges', [StudentController::class, 'getPrivileges'])->name('students.privileges');
        Route::get('/students/{student}/activity-logs', [StudentController::class, 'getStudentActivityLogs'])->name('students.activity-logs');
        Route::post('/students/{student}/privileges', [StudentController::class, 'savePrivileges'])->name('students.privileges.save');
        Route::resource('transactions', TransactionController::class)->only(['index']);
        Route::get('/transactions/students/search', [TransactionController::class, 'getStudents'])->name('transactions.students');
        Route::get('/transactions/books/available', [TransactionController::class, 'getAvailableBooks'])->name('transactions.books');
        Route::get('/transactions/books/issued', [TransactionController::class, 'getIssuedBooks'])->name('transactions.issued-books');
        Route::post('/transactions/issue', [TransactionController::class, 'issueBooks'])->name('transactions.issue');
        Route::post('/transactions/return', [TransactionController::class, 'returnBooks'])->name('transactions.return');
        Route::resource('fines', FineController::class)->only(['index', 'update']);
        Route::get('/fines/data/list', [FineController::class, 'getFinesData'])->name('fines.data');
        Route::post('/fines/{fine}/mark-as-paid', [FineController::class, 'markAsPaid'])->name('fines.mark-as-paid');
        Route::post('/fines/{fine}/waive', [FineController::class, 'waive'])->name('fines.waive');
        Route::post('/fines/{fine}/send-email', [FineController::class, 'sendEmailNotification'])->name('fines.send-email');
        Route::post('/fines/{fine}/adjust', [FineController::class, 'adjustFine'])->name('fines.adjust');
        Route::get('/fines/{fine}/history', [FineController::class, 'getFineHistory'])->name('fines.history');
        Route::get('/fines/student/{student}/list', [FineController::class, 'studentFines'])->name('fines.student');
        Route::get('/fines/dashboard/summary', [FineController::class, 'dashboard'])->name('fines.dashboard');
        Route::get('/fines/overdue/books', [FineController::class, 'overdueBooksSummary'])->name('fines.overdue');
        Route::resource('book-requests', BookRequestController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/users/{user}/activity-logs', [UserController::class, 'userActivityLogs'])
            ->name('users.activity-logs');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.update-password');
        Route::put('/settings/library', [SettingController::class, 'updateLibrarySettings'])->name('settings.update-library');
        Route::post('/settings/remove-photo', [SettingController::class, 'removePhoto'])->name('settings.remove-photo');

        // Account Lock Management Routes
        Route::prefix('account-locks')->name('account-locks.')->group(function () {
            Route::get('/', [AccountLockController::class, 'index'])->name('index');
            Route::post('/unlock', [AccountLockController::class, 'unlock'])->name('unlock');
            Route::post('/unlock-all', [AccountLockController::class, 'unlockAll'])->name('unlock-all');
            Route::post('/settings', [AccountLockController::class, 'updateSettings'])->name('settings');
        });

        // Notification Routes for Admin
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('delete-all-read');
            Route::delete('/{notificationId}', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });





// Staff Routes
Route::middleware(['auth', 'can:access-staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/dashboard/list-data', [StaffDashboardController::class, 'loadMoreList'])
            ->name('dashboard.list-data');

        Route::get('/book-management', [BookManagementController::class, 'index'])->name('book-management.index');
        // Book management AJAX and resource routes (staff)
        Route::get('/books/data', [BookManagementController::class, 'getBooksData'])->name('books.data');
        Route::get('/books/stats', [BookManagementController::class, 'getBookStats'])->name('books.stats');
        Route::post('/books/validate-field', [BookManagementController::class, 'validateField'])->name('books.validate-field');
        Route::post('/categories', [BookManagementController::class, 'createCategory'])->name('categories.store');
        Route::post('/books/categories', [BookManagementController::class, 'createCategory'])->name('books.categories.store');
        Route::resource('books', BookManagementController::class);
        Route::post('/books/{book}/request-deletion', [BookDeletionRequestController::class, 'store'])->name('books.request-deletion');
        
        // Transaction routes for issue book feature
        Route::get('/issue-book', [IssueBookController::class, 'index'])->name('issue-book.index');
        Route::get('/transactions/students', [IssueBookController::class, 'getStudents'])->name('transactions.students');
        Route::get('/transactions/books', [IssueBookController::class, 'getAvailableBooks'])->name('transactions.books');
        Route::get('/transactions/issued-books', [IssueBookController::class, 'getIssuedBooks'])->name('transactions.issued-books');
        Route::post('/transactions/issue', [IssueBookController::class, 'issueBooks'])->name('transactions.issue');
        
        // Transaction routes for return book feature
        Route::get('/return-book', [ReturnBookController::class, 'index'])->name('return-book.index');
        Route::post('/transactions/return', [ReturnBookController::class, 'returnBooks'])->name('transactions.return');
        
        // Fine management routes
        Route::get('/fines', [StaffFineController::class, 'index'])->name('fines.index');
        Route::get('/fines/data', [StaffFineController::class, 'getFinesData'])->name('fines.data');
        Route::post('/fines/{fine}/mark-as-paid', [StaffFineController::class, 'markAsPaid'])->name('fines.mark-as-paid');
        Route::post('/fines/{fine}/waive', [StaffFineController::class, 'waive'])->name('fines.waive');
        Route::post('/fines/{fine}/send-email', [StaffFineController::class, 'sendEmailNotification'])->name('fines.send-email');
        
        // Book request management routes
        Route::get('/book-requests', [StaffBookRequestController::class, 'index'])->name('book-requests.index');
        Route::get('/book-requests/data', [StaffBookRequestController::class, 'getRequestsData'])->name('book-requests.data');
        Route::get('/book-requests/stats', [StaffBookRequestController::class, 'getRequestStats'])->name('book-requests.stats');
        Route::get('/book-requests/next', [StaffBookRequestController::class, 'getNextPending'])->name('book-requests.next');
        Route::resource('book-requests', StaffBookRequestController::class)->only(['store', 'update']);
        
        // Student management routes
        Route::get('/students', [StaffStudentsController::class, 'index'])->name('students.index');
        Route::get('/students/data', [StaffStudentsController::class, 'getStudentsData'])->name('students.data');
        Route::get('/students/stats', [StaffStudentsController::class, 'getStudentsStats'])->name('students.stats');
        Route::post('/students/{student}/deactivate', [StaffStudentsController::class, 'deactivate'])->name('students.deactivate');
        Route::post('/students/{student}/activate', [StaffStudentsController::class, 'activate'])->name('students.activate');
        Route::resource('students', StaffStudentsController::class)->only(['show']);
        
        Route::get('/activity-logs', [StaffActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/settings', [StaffSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [StaffSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/check-email', [StaffSettingController::class, 'checkEmail'])->name('settings.check-email');
        Route::match(['post','put'], '/settings/photo', [StaffSettingController::class, 'photo'])->name('settings.photo');
        Route::put('/settings/password', [StaffSettingController::class, 'password'])->name('settings.password');
        Route::post('/settings/remove-photo', [StaffSettingController::class, 'removePhoto'])->name('settings.remove-photo');

        // Notification Routes for Staff
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [StaffNotificationController::class, 'index'])->name('index');
            Route::get('/unread', [StaffNotificationController::class, 'unread'])->name('unread');
            Route::get('/unread-count', [StaffNotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/{notificationId}/read', [StaffNotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [StaffNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/delete-all-read', [StaffNotificationController::class, 'deleteAllRead'])->name('delete-all-read');
            Route::delete('/{notificationId}', [StaffNotificationController::class, 'destroy'])->name('destroy');
        });

    });




// Student Routes
Route::middleware(['auth', 'can:access-student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/search', [SearchBookController::class, 'index'])
            ->name('search');

        Route::post('/book-request', [SearchBookController::class, 'requestBook'])
            ->name('book-request');

        Route::get('/profile', [StudentProfileController::class, 'index'])
            ->name('profile');

        Route::post('/profile/update-personal-info', [StudentProfileController::class, 'updatePersonalInfo'])
            ->name('profile.update-personal-info');

        Route::post('/profile/upload-photo', [StudentProfileController::class, 'uploadPhoto'])
            ->name('profile.upload-photo');

        Route::post('/profile/remove-photo', [StudentProfileController::class, 'removePhoto'])
            ->name('profile.remove-photo');

        Route::post('/profile/update-password', [StudentProfileController::class, 'updatePassword'])
            ->name('profile.update-password');

        Route::get('/profile/edit', [StudentProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::get('/my-requests', [MyRequestsController::class, 'index'])
            ->name('requests');

        Route::post('/my-requests/{id}/cancel', [MyRequestsController::class, 'cancelRequest'])
            ->name('cancel-request');

        Route::get('/my-fines', [MyFinesController::class, 'index'])
            ->name('fines');

        Route::get('/my-books', [MyBooksController::class, 'index'])
            ->name('my-books');

        // Notification Routes
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [StudentNotificationController::class, 'index'])
                ->name('index');
            Route::get('/unread', [StudentNotificationController::class, 'unread'])
                ->name('unread');
            Route::get('/unread-count', [StudentNotificationController::class, 'unreadCount'])
                ->name('unread-count');
            Route::post('/{notificationId}/read', [StudentNotificationController::class, 'markAsRead'])
                ->name('mark-read');
            Route::post('/mark-all-read', [StudentNotificationController::class, 'markAllAsRead'])
                ->name('mark-all-read');
            Route::post('/delete-all-read', [StudentNotificationController::class, 'deleteAllRead'])
                ->name('delete-all-read');
            Route::delete('/{notificationId}', [StudentNotificationController::class, 'destroy'])
                ->name('destroy');
        });

    });

//User Dashboard (Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password change routes
    Route::get('/change-password', [PasswordChangeController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [PasswordChangeController::class, 'updatePassword'])->name('password.update');
    
    // Student privileges endpoint - accessible by admin and staff
    Route::get('/admin/students/{student}/privileges', [StudentController::class, 'getPrivileges'])->name('api.students.privileges');
});
require __DIR__ . '/auth.php';
