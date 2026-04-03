<?php

use App\Mail\StudentStatusUpdatedMail;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function makeStatusNotificationUser(string $role, array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' User',
        'email' => strtolower($role) . '-' . Str::random(6) . '@example.com',
        'phone' => '98' . random_int(10000000, 99999999),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Current!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ], $overrides));
}

function makeStatusNotificationStudent(array $overrides = []): Student
{
    $studentUser = makeStatusNotificationUser('student', [
        'name' => 'Status Student',
        'email' => 'status-student-' . Str::random(6) . '@example.com',
        'phone' => '97' . random_int(10000000, 99999999),
    ]);

    $department = Department::query()->create([
        'name' => 'Electronics',
        'code' => 'EC' . Str::upper(Str::random(2)),
    ]);

    return Student::query()->create(array_merge([
        'user_id' => $studentUser->id,
        'department_id' => $department->id,
        'roll_no' => 'STAT-' . random_int(1000, 9999),
        'batch' => '2026',
        'semester' => '5',
        'address' => 'Bhaktapur, Nepal',
    ], $overrides));
}

test('admin toggle status queues a student status email', function () {
    Mail::fake();

    $admin = makeStatusNotificationUser('admin');
    $student = makeStatusNotificationStudent();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->put(route('admin.students.toggle-status', $student));

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'inactive');

    expect($student->fresh()->user->status)->toBe('inactive');

    Mail::assertQueued(StudentStatusUpdatedMail::class, function (StudentStatusUpdatedMail $mail) use ($student) {
        return $mail->hasTo($student->user->email)
            && str_contains($mail->render(), 'Your account has been deactivated')
            && str_contains($mail->render(), 'Warning:');
    });
});

test('admin update form queues a student status email when the status changes', function () {
    Mail::fake();

    $admin = makeStatusNotificationUser('admin', [
        'email' => 'admin-edit@example.com',
        'phone' => '9811111111',
    ]);
    $student = makeStatusNotificationStudent([
        'roll_no' => 'STAT-2201',
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->put(route('admin.students.update', $student), [
            'name' => $student->user->name,
            'email' => $student->user->email,
            'phone' => '+9779812345678',
            'date_of_birth' => now()->subYears(20)->format('Y-m-d'),
            'roll_no' => $student->roll_no,
            'department_id' => $student->department_id,
            'batch' => $student->batch,
            'semester' => $student->semester,
            'address' => $student->address,
            'status' => 'inactive',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($student->fresh()->user->status)->toBe('inactive');

    Mail::assertQueued(StudentStatusUpdatedMail::class);
});

test('staff deactivate queues a student status email', function () {
    Mail::fake();

    makeStatusNotificationUser('admin', [
        'email' => 'fallback-admin@example.com',
        'phone' => '9822222222',
    ]);

    $staff = makeStatusNotificationUser('staff', [
        'email' => 'staff-user@example.com',
        'phone' => '9833333333',
    ]);
    $student = makeStatusNotificationStudent([
        'roll_no' => 'STAT-3301',
    ]);

    $response = $this
        ->actingAs($staff)
        ->withHeader('Accept', 'application/json')
        ->post(route('staff.students.deactivate', $student));

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($student->fresh()->user->status)->toBe('inactive');

    Mail::assertQueued(StudentStatusUpdatedMail::class, function (StudentStatusUpdatedMail $mail) use ($student) {
        return $mail->hasTo($student->user->email)
            && str_contains($mail->render(), 'Library staff')
            && str_contains($mail->render(), 'Borrowing, renewals, and book requests may be restricted');
    });
});
