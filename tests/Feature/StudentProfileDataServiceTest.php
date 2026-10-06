<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentManagement\StudentProfileDataService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeStudentProfileDataStudent(): Student
{
    $unique = Str::lower(Str::random(8));

    $user = User::forceCreate([
        'role' => 'student',
        'name' => 'Profile Data Student ' . $unique,
        'email' => "profile-data-{$unique}@example.com",
        'phone' => '95' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $department = Department::create([
        'name' => 'Profile Data Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'SPD-' . Str::upper(substr($unique, 0, 6)),
        'roll_no' => 'SPD-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);
}

function makeStudentProfileDataBook(string $title): Book
{
    $unique = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'Profile Data Category ' . $unique,
        'description' => 'Test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => $title,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-SPD-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);
}

function makeStudentProfileDataIssue(Student $student, string $title, int $daysOverdue): IssuedBook
{
    return IssuedBook::create([
        'book_id' => makeStudentProfileDataBook($title)->id,
        'student_id' => $student->id,
        'issue_date' => now()->subDays($daysOverdue + 14)->toDateString(),
        'due_date' => now()->subDays($daysOverdue)->toDateString(),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);
}

test('staff student profile shows overdue fine amounts and fine management rows', function () {
    FineSetting::create([
        'per_day_fine' => 30,
        'grace_period_days' => 0,
        'max_fine_amount' => 1000,
        'is_active' => true,
    ]);

    $student = makeStudentProfileDataStudent();
    $overdueIssue = makeStudentProfileDataIssue($student, 'Staff Detail Overdue Fine Book', 5);

    $profile = app(StudentProfileDataService::class)->buildStaffProfile($student);
    $storedFine = Fine::where('issued_book_id', $overdueIssue->id)->first();

    expect($profile['summary']['pendingFineTotal'])->toBe(150.0)
        ->and($profile['books'][0]['fineAmount'])->toBe(150.0)
        ->and($profile['books'][0]['fineStatus'])->toBe('unpaid')
        ->and($profile['fines'])->toHaveCount(1)
        ->and($profile['fines'][0]['bookName'])->toBe('Staff Detail Overdue Fine Book')
        ->and($profile['fines'][0]['amount'])->toBe(150.0)
        ->and($profile['fines'][0]['status'])->toBe('pending')
        ->and($storedFine)->not->toBeNull()
        ->and($storedFine->status)->toBe('pending');
});

test('staff student details renders the shared issued book details interaction', function () {
    $staff = User::forceCreate([
        'role' => 'staff',
        'name' => 'Profile Staff',
        'email' => 'profile-staff@example.com',
        'phone' => '9800000001',
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);
    $student = makeStudentProfileDataStudent();
    makeStudentProfileDataIssue($student, 'Shared Modal Book', 3);

    $response = $this->actingAs($staff)->get(route('staff.students.show', $student));

    $response->assertOk()
        ->assertSee('Academic Information')
        ->assertSee('Contact Information')
        ->assertSee('Account Information')
        ->assertSee('<th scope="col">Actions</th>', false)
        ->assertSee('data-book-view=', false)
        ->assertSee('View issued book details for', false)
        ->assertSee('Issued Book Details')
        ->assertSee('admin/JS/issued-book-details.js', false)
        ->assertSee('admin/CSS/issued-book-details.css', false);
});

test('admin student details keeps using the shared issued book modal after the staff redesign', function () {
    $admin = User::forceCreate([
        'role' => 'admin',
        'name' => 'Profile Admin',
        'email' => 'profile-admin@example.com',
        'phone' => '9800000002',
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);
    $student = makeStudentProfileDataStudent();
    makeStudentProfileDataIssue($student, 'Admin Shared Modal Book', 2);
    \Illuminate\Support\Facades\View::share('libraryBranding', \App\Support\LibraryBranding::resolve());

    $response = $this->actingAs($admin)->get(route('admin.students.show', $student));

    $response->assertOk()
        ->assertSee('Issued Book Details')
        ->assertSee('admin/JS/issued-book-details.js', false)
        ->assertSee('admin/CSS/issued-book-details.css', false);
});
