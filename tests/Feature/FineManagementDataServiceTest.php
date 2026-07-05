<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Services\FineManagement\FineManagementDataService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeFineManagementStudent(): Student
{
    $unique = Str::lower(Str::random(8));

    $user = User::forceCreate([
        'role' => 'student',
        'name' => 'Fine Management Student ' . $unique,
        'email' => "fine-management-{$unique}@example.com",
        'phone' => '97' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $department = Department::create([
        'name' => 'Fine Management Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'FMD-' . Str::upper(substr($unique, 0, 6)),
        'roll_no' => 'FMD-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);
}

function makeFineManagementStaffUser(): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate([
        'role' => 'staff',
        'name' => 'Fine Management Staff ' . $unique,
        'email' => "fine-management-staff-{$unique}@example.com",
        'phone' => '96' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);
}

function makeFineManagementBook(string $title): Book
{
    $unique = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'Fine Management Category ' . $unique,
        'description' => 'Test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => $title,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-FMD-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);
}

function makeFineManagementIssue(Student $student, string $title, int $daysOverdue): IssuedBook
{
    return IssuedBook::create([
        'book_id' => makeFineManagementBook($title)->id,
        'student_id' => $student->id,
        'issue_date' => now()->subDays($daysOverdue + 14)->toDateString(),
        'due_date' => now()->subDays($daysOverdue)->toDateString(),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);
}

test('fine management listing includes overdue books without existing fine records', function () {
    FineSetting::create([
        'per_day_fine' => 25,
        'grace_period_days' => 0,
        'max_fine_amount' => 1000,
        'is_active' => true,
    ]);

    $student = makeFineManagementStudent();
    $missingFineIssue = makeFineManagementIssue($student, 'Overdue Book Without Fine Row', 4);
    $existingFineIssue = makeFineManagementIssue($student, 'Book With Existing Fine Row', 2);

    Fine::create([
        'issued_book_id' => $existingFineIssue->id,
        'student_id' => $student->id,
        'amount' => 80,
        'days_late' => 2,
        'status' => 'pending',
    ]);

    $listing = app(FineManagementDataService::class)->getListingData([
        'per_page' => 10,
    ]);

    $titles = collect($listing['fines'])->pluck('bookTitle');
    $createdFine = Fine::where('issued_book_id', $missingFineIssue->id)->first();

    expect($listing['pagination']['total'])->toBe(2)
        ->and($titles)->toContain('Overdue Book Without Fine Row')
        ->and($titles)->toContain('Book With Existing Fine Row')
        ->and($createdFine)->not->toBeNull()
        ->and((float) $createdFine->amount)->toBe(100.0)
        ->and($listing['stats']['pending'])->toBe(180.0)
        ->and($listing['stats']['overdue_count'])->toBe(2);
});

test('staff fines data endpoint includes overdue books without existing fine records', function () {
    FineSetting::create([
        'per_day_fine' => 20,
        'grace_period_days' => 0,
        'max_fine_amount' => 1000,
        'is_active' => true,
    ]);

    $staff = makeFineManagementStaffUser();
    $student = makeFineManagementStudent();
    $missingFineIssue = makeFineManagementIssue($student, 'Staff Visible Overdue Without Fine Row', 3);

    $response = $this
        ->actingAs($staff)
        ->getJson(route('staff.fines.data', ['per_page' => 10]));

    $createdFine = Fine::where('issued_book_id', $missingFineIssue->id)->first();

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('fines.0.bookTitle', 'Staff Visible Overdue Without Fine Row')
        ->assertJsonPath('fines.0.fineAmount', 60);

    expect($createdFine)->not->toBeNull()
        ->and($createdFine->status)->toBe('pending');
});
