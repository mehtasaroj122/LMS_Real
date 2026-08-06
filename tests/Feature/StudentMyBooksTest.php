<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Support\LibraryBranding;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

beforeEach(function () {
    View::share('libraryBranding', LibraryBranding::resolve());
});

function makeMyBooksStudent(): Student
{
    $unique = Str::lower(Str::random(8));

    $user = User::forceCreate([
        'role' => 'student',
        'name' => 'My Books Student ' . $unique,
        'email' => "my-books-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $department = Department::create([
        'name' => 'My Books Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'MBB-' . Str::upper(substr($unique, 0, 6)),
        'roll_no' => 'ROLL-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);
}

function makeMyBooksBook(string $title): Book
{
    $unique = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'My Books Category ' . $unique,
        'description' => 'Test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => $title,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-MBB-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);
}

function makeMyBooksIssue(Student $student, Book $book, int $daysOverdue): IssuedBook
{
    return IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issue_date' => now()->subDays($daysOverdue + 14)->toDateString(),
        'due_date' => now()->subDays($daysOverdue)->toDateString(),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);
}

test('my books page does not mark a book with a waived fine as overdue', function () {
    FineSetting::create([
        'per_day_fine' => 10,
        'grace_period_days' => 0,
        'max_fine_amount' => 500,
        'is_active' => true,
    ]);

    $student = makeMyBooksStudent();
    $issue = makeMyBooksIssue($student, makeMyBooksBook('Waived Overdue Book'), 6);

    Fine::create([
        'issued_book_id' => $issue->id,
        'student_id' => $student->id,
        'amount' => 60,
        'days_late' => 6,
        'status' => 'waived',
        'waive_reason' => 'Approved waiver',
        'waived_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($student->user)->get(route('student.my-books'));
    $response->assertOk();

    $books = json_decode($response->viewData('issuedBooksJson'), true);

    expect($response->viewData('overdueBooks'))->toBe(0)
        ->and($books)->toHaveCount(1)
        ->and($books[0]['status'])->toBe('issued')
        ->and($books[0]['fineStatus'])->toBe('waived');
});

test('my books page still marks a book with an unpaid fine as overdue', function () {
    FineSetting::create([
        'per_day_fine' => 10,
        'grace_period_days' => 0,
        'max_fine_amount' => 500,
        'is_active' => true,
    ]);

    $student = makeMyBooksStudent();
    $issue = makeMyBooksIssue($student, makeMyBooksBook('Unpaid Overdue Book'), 6);

    Fine::create([
        'issued_book_id' => $issue->id,
        'student_id' => $student->id,
        'amount' => 60,
        'days_late' => 6,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($student->user)->get(route('student.my-books'));
    $response->assertOk();

    $books = json_decode($response->viewData('issuedBooksJson'), true);

    expect($response->viewData('overdueBooks'))->toBe(1)
        ->and($books)->toHaveCount(1)
        ->and($books[0]['status'])->toBe('overdue')
        ->and($books[0]['fineStatus'])->toBe('unpaid');
});
