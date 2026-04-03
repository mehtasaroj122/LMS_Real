<?php

use App\Jobs\SendFineEmail;
use App\Jobs\SendOverdueReminderEmail;
use App\Models\book as Book;
use App\Models\category as Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function makeReminderTestUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' Reminder ' . $unique,
        'email' => "{$role}-reminder-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeReminderIssuedBook(array $issuedOverrides = [], array $fineOverrides = []): array
{
    $unique = Str::lower(Str::random(8));
    $issuer = makeReminderTestUser('admin', [
        'email' => "issuer-reminder-{$unique}@example.com",
    ]);

    $studentUser = makeReminderTestUser('student', [
        'email' => "student-reminder-{$unique}@example.com",
    ]);

    $department = Department::create([
        'name' => 'Reminder Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'department_id' => $department->id,
        'roll_no' => 'REM-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Pokhara, Nepal',
    ]);

    $category = Category::create([
        'name' => 'Reminder Category ' . $unique,
        'description' => 'Reminder test category',
    ]);

    $book = Book::create([
        'category_id' => $category->id,
        'title' => 'Reminder Book ' . $unique,
        'author' => 'Author ' . $unique,
        'publisher' => 'Publisher ' . $unique,
        'isbn' => 'ISBN-REM-' . Str::upper($unique),
        'total_copies' => 3,
        'available_copies' => 1,
        'condition' => 'good',
        'description' => 'Reminder test book',
        'shelf_no' => 'R-' . Str::upper(substr($unique, 0, 4)),
        'status' => 'available',
    ]);

    $issuedBook = IssuedBook::create(array_merge([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $issuer->id,
        'issue_date' => now()->subDays(12),
        'due_date' => now()->subDays(5),
        'return_date' => null,
        'status' => 'issued',
        'condition' => 'good',
        'fine_amount' => 75,
        'remarks' => 'Reminder test issue',
    ], $issuedOverrides));

    $fine = Fine::create(array_merge([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => 75,
        'days_late' => 5,
        'status' => 'pending',
        'remarks' => null,
    ], $fineOverrides));

    return [$issuedBook, $fine];
}

test('overdue reminder command queues overdue book email jobs', function () {
    Queue::fake();

    makeReminderIssuedBook();

    Artisan::call('notifications:overdue-reminders');

    Queue::assertPushed(SendOverdueReminderEmail::class, 1);
});

test('fine reminder command queues pending fine email jobs', function () {
    Queue::fake();

    makeReminderIssuedBook();

    Artisan::call('notifications:fine-reminders');

    Queue::assertPushed(SendFineEmail::class, 1);
});
