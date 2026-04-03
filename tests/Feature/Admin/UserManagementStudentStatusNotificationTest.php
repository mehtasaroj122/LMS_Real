<?php

use App\Mail\StudentStatusUpdatedMail;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Models\staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function makeUserManagementAdmin(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'role' => 'admin',
        'name' => 'User Management Admin',
        'email' => 'user-management-admin@example.com',
        'phone' => '9810000001',
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Current!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ], $overrides));
}

function makeUserManagementStudent(array $overrides = []): User
{
    $studentUser = User::forceCreate([
        'role' => 'student',
        'name' => 'Managed Student',
        'email' => 'managed-student-' . Str::random(6) . '@example.com',
        'phone' => '9810000002',
        'address' => 'Pokhara, Nepal',
        'password' => Hash::make('Student!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ]);

    $department = Department::query()->create([
        'name' => 'Management',
        'code' => 'MG' . Str::upper(Str::random(2)),
    ]);

    Student::query()->create(array_merge([
        'user_id' => $studentUser->id,
        'department_id' => $department->id,
        'roll_no' => 'UM-' . random_int(1000, 9999),
        'batch' => '2026',
        'semester' => '4',
        'address' => 'Pokhara, Nepal',
    ], $overrides));

    return $studentUser->fresh('student');
}

function makeUserManagementStaff(array $overrides = []): User
{
    $staffUser = User::forceCreate([
        'role' => 'staff',
        'name' => 'Managed Staff',
        'email' => 'managed-staff-' . Str::random(6) . '@example.com',
        'phone' => '9810000003',
        'address' => 'Lalitpur, Nepal',
        'password' => Hash::make('Staff!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ]);

    $department = Department::query()->create([
        'name' => 'Library Operations',
        'code' => 'LO' . Str::upper(Str::random(2)),
    ]);

    staff::query()->create(array_merge([
        'user_id' => $staffUser->id,
        'department_id' => $department->id,
        'designation' => 'Assistant Librarian',
        'join_date' => now()->subYear()->toDateString(),
    ], $overrides));

    return $staffUser->fresh('staff');
}

test('admin user management status toggle queues a student status email for student users', function () {
    Mail::fake();

    $admin = makeUserManagementAdmin();
    $studentUser = makeUserManagementStudent();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->patch(route('admin.users.status', $studentUser));

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'inactive');

    expect($studentUser->fresh()->status)->toBe('inactive');

    Mail::assertQueued(StudentStatusUpdatedMail::class, function (StudentStatusUpdatedMail $mail) use ($studentUser) {
        return $mail->hasTo($studentUser->email)
            && str_contains($mail->render(), 'Your account has been deactivated');
    });
});

test('admin user management status toggle queues a staff status email for staff users', function () {
    Mail::fake();

    $admin = makeUserManagementAdmin([
        'email' => 'user-management-admin-2@example.com',
        'phone' => '9810000011',
    ]);

    $staffUser = makeUserManagementStaff();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->patch(route('admin.users.status', $staffUser));

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'inactive');

    expect($staffUser->fresh()->status)->toBe('inactive');

    Mail::assertQueued(StudentStatusUpdatedMail::class, function (StudentStatusUpdatedMail $mail) use ($staffUser) {
        return $mail->hasTo($staffUser->email)
            && str_contains($mail->render(), 'staff dashboard, circulation tools')
            && !str_contains($mail->render(), 'Borrowing, renewals, and book requests may be restricted');
    });
});

test('admin user management status toggle does not queue account status email for admin users', function () {
    Mail::fake();

    $admin = makeUserManagementAdmin([
        'email' => 'user-management-admin-3@example.com',
        'phone' => '9810000021',
    ]);

    $anotherAdmin = makeUserManagementAdmin([
        'email' => 'managed-admin@example.com',
        'phone' => '9810000022',
        'name' => 'Managed Admin',
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->patch(route('admin.users.status', $anotherAdmin));

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'inactive');

    Mail::assertNotQueued(StudentStatusUpdatedMail::class);
});
