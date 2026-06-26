<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\staff as Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeDeletionUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' Delete User',
        'email' => "{$role}-delete-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeDeletionDepartment(): Department
{
    $unique = Str::lower(Str::random(6));

    return Department::create([
        'name' => 'Department ' . $unique,
        'code' => Str::upper($unique),
        'status' => 'active',
    ]);
}

function makeDeletionStudentUser(array $userOverrides = []): User
{
    $user = makeDeletionUser('student', $userOverrides);

    Student::create([
        'user_id' => $user->id,
        'department_id' => makeDeletionDepartment()->id,
        'student_id' => 'STU-' . Str::upper(substr(Str::random(6), 0, 6)),
        'roll_no' => 'ROLL-' . Str::upper(substr(Str::random(6), 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Hostel Block',
    ]);

    return $user->fresh()->load('student');
}

function makeDeletionStaffUser(array $userOverrides = []): User
{
    $user = makeDeletionUser('staff', $userOverrides);

    Staff::create([
        'user_id' => $user->id,
        'department_id' => makeDeletionDepartment()->id,
        'staff_id' => 'STF-' . Str::upper(substr(Str::random(6), 0, 6)),
        'designation' => 'Librarian',
        'join_date' => now()->subYear()->toDateString(),
    ]);

    return $user->fresh()->load('staff');
}

function makeDeletionBook(): Book
{
    $unique = Str::lower(Str::random(6));
    $category = Category::create([
        'name' => 'Category ' . $unique,
        'description' => 'Test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => 'Book ' . $unique,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-' . Str::upper(Str::random(10)),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);
}

function makeDeletionIssuedBook(Student $student, ?int $issuedBy = null, array $overrides = []): IssuedBook
{
    $book = makeDeletionBook();

    return IssuedBook::create(array_merge([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $issuedBy,
        'issue_date' => now()->subDays(5)->toDateString(),
        'due_date' => now()->addDays(5)->toDateString(),
        'return_date' => null,
        'status' => 'issued',
        'condition' => 'good',
        'fine_amount' => 0,
    ], $overrides));
}

test('delete account requires exact uppercase DELETE confirmation text', function () {
    $staffUser = makeDeletionStaffUser([
        'email' => 'staff-confirmation@example.com',
    ]);

    $response = $this
        ->actingAs($staffUser)
        ->deleteJson(route('account.delete'), [
            'confirmation_text' => 'delete',
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors('confirmation_text')
        ->assertJsonPath('errors.confirmation_text.0', 'Please type DELETE to confirm');

    expect($staffUser->fresh())->not->toBeNull();
});

test('admin cannot delete their own account', function () {
    $admin = makeDeletionUser('admin', [
        'email' => 'admin-delete@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->deleteJson(route('account.delete'), [
            'confirmation_text' => 'DELETE',
        ]);

    $response
        ->assertForbidden()
        ->assertJsonPath('message', 'You cannot delete your account because you are an administrator.')
        ->assertJsonPath('detail', 'Please contact another administrator for this action.');

    expect($admin->fresh())->not->toBeNull();
});

test('student cannot delete account while books are still issued', function () {
    $studentUser = makeDeletionStudentUser([
        'email' => 'student-issued@example.com',
    ]);

    makeDeletionIssuedBook($studentUser->student);

    $response = $this
        ->actingAs($studentUser)
        ->deleteJson(route('account.delete'), [
            'confirmation_text' => 'DELETE',
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors('account')
        ->assertJsonPath('errors.account.0', 'Please return all issued books and clear pending fines before deleting your account.');

    expect($studentUser->fresh())->not->toBeNull();
    expect($studentUser->student()->exists())->toBeTrue();
});

test('student cannot delete account while pending fines exist', function () {
    $studentUser = makeDeletionStudentUser([
        'email' => 'student-fine@example.com',
    ]);

    $issuedBook = makeDeletionIssuedBook($studentUser->student, null, [
        'return_date' => now()->toDateString(),
        'status' => 'returned',
    ]);

    Fine::create([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $studentUser->student->id,
        'amount' => 25,
        'days_late' => 2,
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($studentUser)
        ->deleteJson(route('account.delete'), [
            'confirmation_text' => 'DELETE',
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors('account')
        ->assertJsonPath('errors.account.0', 'Please return all issued books and clear pending fines before deleting your account.');

    expect($studentUser->fresh())->not->toBeNull();
});

test('eligible student can delete their own account', function () {
    $studentUser = makeDeletionStudentUser([
        'email' => 'student-eligible@example.com',
    ]);

    $studentId = $studentUser->student->id;

    $response = $this
        ->actingAs($studentUser)
        ->deleteJson(route('account.delete'), [
            'confirmation_text' => 'DELETE',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('redirect', url('/'))
        ->assertJsonPath('redirect_delay', 3000);

    $this->assertGuest();
    expect(User::find($studentUser->id))->toBeNull();
    expect(Student::find($studentId))->toBeNull();
    expect(ActivityLog::query()->where('action', 'account_deleted')->where('resource_id', $studentUser->id)->exists())
        ->toBeTrue();
});

test('staff account deletion clears issued_by references before removing the account', function () {
    $staffUser = makeDeletionStaffUser([
        'email' => 'staff-history@example.com',
    ]);
    $studentUser = makeDeletionStudentUser([
        'email' => 'student-history@example.com',
    ]);

    $issuedBook = makeDeletionIssuedBook($studentUser->student, $staffUser->id);
    $staffProfileId = $staffUser->staff->id;

    $response = $this
        ->actingAs($staffUser)
        ->deleteJson(route('account.delete'), [
            'confirmation_text' => 'DELETE',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertGuest();
    expect(User::find($staffUser->id))->toBeNull();
    expect(Staff::find($staffProfileId))->toBeNull();
    expect($issuedBook->fresh()?->issued_by)->toBeNull();
    expect(ActivityLog::query()->where('action', 'account_deleted')->where('resource_id', $staffUser->id)->exists())
        ->toBeTrue();
});
