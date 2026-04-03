<?php

use App\Models\Department;
use App\Models\FineSetting;
use App\Models\Student;
use App\Models\StudentPrivilege;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makePrivilegeAdminUser(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'role' => 'admin',
        'name' => 'Privilege Admin',
        'email' => 'privilege-admin@example.com',
        'phone' => '9800001111',
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Current!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ], $overrides));
}

function makePrivilegeStudentRecord(array $overrides = []): Student
{
    $studentUser = User::forceCreate([
        'role' => 'student',
        'name' => 'Student Privilege',
        'email' => 'student-privilege@example.com',
        'phone' => '9800002222',
        'address' => 'Pokhara, Nepal',
        'password' => Hash::make('Student!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ]);

    $department = Department::query()->create([
        'name' => 'Computer Science',
        'code' => 'CS',
    ]);

    return Student::query()->create(array_merge([
        'user_id' => $studentUser->id,
        'department_id' => $department->id,
        'roll_no' => 'STU-1001',
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Lakeside, Pokhara',
    ], $overrides));
}

test('admin can reset student privileges back to defaults and clear stored overrides', function () {
    $admin = makePrivilegeAdminUser();
    $student = makePrivilegeStudentRecord();

    FineSetting::query()->create([
        'is_active' => true,
        'max_books_per_student' => 6,
        'issue_duration_days' => 21,
        'per_day_fine' => 12.5,
        'grace_period_days' => 3,
        'max_fine_amount' => 750,
    ]);

    StudentPrivilege::query()->create([
        'student_id' => $student->id,
        'max_books' => 9,
        'issue_duration_days' => 28,
        'per_day_fine' => 25.5,
        'borrowing_allowed' => false,
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.students.privileges.reset', $student));

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Library privileges reset to default settings')
        ->assertJsonPath('defaults.max_books', 6)
        ->assertJsonPath('defaults.issue_duration_days', 21)
        ->assertJsonPath('defaults.per_day_fine', 12.5)
        ->assertJsonPath('effective.max_books', 6)
        ->assertJsonPath('effective.issue_duration_days', 21)
        ->assertJsonPath('effective.per_day_fine', 12.5)
        ->assertJsonPath('effective.borrowing_allowed', true)
        ->assertJsonPath('privileges.max_books', null)
        ->assertJsonPath('privileges.issue_duration_days', null)
        ->assertJsonPath('privileges.per_day_fine', null)
        ->assertJsonPath('has_custom_overrides', false);

    expect(StudentPrivilege::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('saving privilege settings that match defaults removes redundant overrides', function () {
    $admin = makePrivilegeAdminUser([
        'email' => 'privilege-admin-2@example.com',
        'phone' => '9800001112',
    ]);
    $student = makePrivilegeStudentRecord([
        'roll_no' => 'STU-1002',
    ]);

    FineSetting::query()->create([
        'is_active' => true,
        'max_books_per_student' => 5,
        'issue_duration_days' => 14,
        'per_day_fine' => 10,
        'grace_period_days' => 2,
        'max_fine_amount' => 500,
    ]);

    StudentPrivilege::query()->create([
        'student_id' => $student->id,
        'max_books' => 8,
        'issue_duration_days' => 20,
        'per_day_fine' => 18,
        'borrowing_allowed' => false,
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.students.privileges.save', $student), [
            'max_books' => 5,
            'issue_duration_days' => 14,
            'per_day_fine' => 10,
            'borrowing_allowed' => true,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('effective.max_books', 5)
        ->assertJsonPath('effective.issue_duration_days', 14)
        ->assertJsonPath('effective.per_day_fine', 10)
        ->assertJsonPath('effective.borrowing_allowed', true)
        ->assertJsonPath('has_custom_overrides', false);

    expect(StudentPrivilege::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('saving custom privilege overrides returns effective settings without crashing', function () {
    $admin = makePrivilegeAdminUser([
        'email' => 'privilege-admin-3@example.com',
        'phone' => '9800001113',
    ]);
    $student = makePrivilegeStudentRecord([
        'roll_no' => 'STU-1003',
    ]);

    FineSetting::query()->create([
        'is_active' => true,
        'max_books_per_student' => 5,
        'issue_duration_days' => 14,
        'per_day_fine' => 10,
        'grace_period_days' => 2,
        'max_fine_amount' => 500,
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.students.privileges.save', $student), [
            'max_books' => 7,
            'issue_duration_days' => 21,
            'per_day_fine' => 15,
            'borrowing_allowed' => false,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Library privileges saved successfully')
        ->assertJsonPath('effective.max_books', 7)
        ->assertJsonPath('effective.issue_duration_days', 21)
        ->assertJsonPath('effective.per_day_fine', '15.00')
        ->assertJsonPath('effective.borrowing_allowed', false)
        ->assertJsonPath('privileges.max_books', 7)
        ->assertJsonPath('privileges.issue_duration_days', 21)
        ->assertJsonPath('privileges.per_day_fine', '15.00')
        ->assertJsonPath('privileges.borrowing_allowed', false)
        ->assertJsonPath('has_custom_overrides', true);

    $storedPrivilege = StudentPrivilege::query()->where('student_id', $student->id)->first();

    expect($storedPrivilege)->not->toBeNull();
    expect($storedPrivilege->max_books)->toBe(7);
    expect($storedPrivilege->issue_duration_days)->toBe(21);
    expect((float) $storedPrivilege->per_day_fine)->toBe(15.0);
    expect($storedPrivilege->borrowing_allowed)->toBeFalse();
});
