<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentFineSummaryService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeStudentFineSummaryStudent(): Student
{
    $unique = Str::lower(Str::random(8));

    $user = User::forceCreate([
        'role' => 'student',
        'name' => 'Fine Summary Student ' . $unique,
        'email' => "fine-summary-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $department = Department::create([
        'name' => 'Fine Summary Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'FSS-' . Str::upper(substr($unique, 0, 6)),
        'roll_no' => 'ROLL-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);
}

function makeStudentFineSummaryBook(string $title): Book
{
    $unique = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'Fine Summary Category ' . $unique,
        'description' => 'Test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => $title,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-FSS-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);
}

function makeStudentFineSummaryIssue(Student $student, string $title, int $daysOverdue): IssuedBook
{
    return IssuedBook::create([
        'book_id' => makeStudentFineSummaryBook($title)->id,
        'student_id' => $student->id,
        'issue_date' => now()->subDays($daysOverdue + 14)->toDateString(),
        'due_date' => now()->subDays($daysOverdue)->toDateString(),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);
}

test('student fine summary combines pending fines with current overdue book fines once', function () {
    FineSetting::create([
        'per_day_fine' => 10,
        'grace_period_days' => 0,
        'max_fine_amount' => 500,
        'is_active' => true,
    ]);

    $student = makeStudentFineSummaryStudent();
    $pendingIssue = makeStudentFineSummaryIssue($student, 'Stored Pending Fine', 5);
    $overdueIssue = makeStudentFineSummaryIssue($student, 'Ungenerated Overdue Fine', 4);
    $paidButStillOverdueIssue = makeStudentFineSummaryIssue($student, 'Still Overdue After Paid Fine', 3);
    $linkedPendingIssue = makeStudentFineSummaryIssue($student, 'Linked Pending Fine', 2);
    $otherStudent = makeStudentFineSummaryStudent();

    Fine::create([
        'issued_book_id' => $pendingIssue->id,
        'student_id' => $student->id,
        'amount' => 125,
        'days_late' => 5,
        'status' => 'pending',
    ]);

    Fine::create([
        'issued_book_id' => $paidButStillOverdueIssue->id,
        'student_id' => $student->id,
        'amount' => 10,
        'days_late' => 1,
        'status' => 'paid',
        'paid_on' => now()->subDay()->toDateString(),
    ]);

    Fine::create([
        'issued_book_id' => $linkedPendingIssue->id,
        'student_id' => $otherStudent->id,
        'amount' => 200,
        'days_late' => 2,
        'status' => 'pending',
    ]);

    $summary = app(StudentFineSummaryService::class);

    expect($summary->pendingAmount($student))->toBe(395.0)
        ->and($summary->pendingItems($student))->toHaveCount(4)
        ->and($summary->openOverdueBookCount($student))->toBe(4)
        ->and($summary->displayFineForIssue($overdueIssue->fresh(['student.privileges', 'fine'])))->toMatchArray([
            'amount' => 40.0,
            'status' => 'unpaid',
        ]);
});
