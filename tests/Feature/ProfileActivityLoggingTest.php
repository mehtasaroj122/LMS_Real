<?php

use App\Models\ActivityLog;
use App\Models\department as Department;
use App\Models\staff as Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function makeProfileLoggingUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' User',
        'email' => "{$role}-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu Metropolitan City, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeProfileLoggingDepartment(): Department
{
    $unique = Str::lower(Str::random(8));

    return Department::create([
        'name' => 'Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);
}

function makeProfileLoggingStudentUser(array $overrides = []): User
{
    $user = makeProfileLoggingUser('student', $overrides);

    Student::create([
        'user_id' => $user->id,
        'department_id' => makeProfileLoggingDepartment()->id,
        'roll_no' => 'ROLL-' . Str::upper(substr(Str::random(6), 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Pokhara, Nepal',
    ]);

    return $user->fresh();
}

function makeProfileLoggingStaffUser(array $overrides = []): User
{
    $user = makeProfileLoggingUser('staff', $overrides);

    Staff::create([
        'user_id' => $user->id,
        'department_id' => makeProfileLoggingDepartment()->id,
        'designation' => 'Librarian',
        'join_date' => now()->subYear()->toDateString(),
    ]);

    return $user->fresh();
}

function makeProfileLoggingTinyPng(): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'profile_log_');

    file_put_contents(
        $path,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+XxqQAAAAASUVORK5CYII=')
    );

    return new UploadedFile($path, 'profile-log.png', 'image/png', null, true);
}

test('admin profile update logs only fields that actually changed', function () {
    Storage::fake('public');

    $admin = makeProfileLoggingUser('admin', [
        'name' => 'Admin User',
        'email' => 'admin-log@example.com',
        'phone' => '9800000001',
        'address' => 'Ward 10, Kathmandu, Nepal',
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update'), [
            '_method' => 'PUT',
            'name' => 'Admin Prime',
            'email' => 'admin-log@example.com',
            'phone' => '9800000001',
            'address' => 'Ward 10, Kathmandu, Nepal',
            'profile_photo' => makeProfileLoggingTinyPng(),
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'profile_updated')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'profile_updated')->value('description'))
        ->toBe('Profile updated: name: Admin User -> Admin Prime, profile picture added');
});

test('staff unchanged profile submission does not create a profile activity log', function () {
    $staff = makeProfileLoggingStaffUser([
        'name' => 'Desk Staff',
        'email' => 'staff-log@example.com',
        'phone' => '9800000002',
        'address' => 'Library Block, Kathmandu',
    ]);

    $response = $this
        ->actingAs($staff)
        ->withHeader('Accept', 'application/json')
        ->put(route('staff.settings.update'), [
            'name' => 'Desk Staff',
            'email' => 'staff-log@example.com',
            'phone' => '9800000002',
            'address' => 'Library Block, Kathmandu',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'profile_updated')->count())->toBe(0);
});

test('staff photo upload is logged as a profile change', function () {
    Storage::fake('public');

    $staff = makeProfileLoggingStaffUser([
        'name' => 'Photo Staff',
        'email' => 'staff-photo@example.com',
    ]);

    $response = $this
        ->actingAs($staff)
        ->withHeader('Accept', 'application/json')
        ->post(route('staff.settings.photo'), [
            'profile_photo' => makeProfileLoggingTinyPng(),
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'profile_updated')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'profile_updated')->value('description'))
        ->toBe('Profile updated: profile picture added');
});

test('student personal info update logs only the changed fields', function () {
    $studentUser = makeProfileLoggingStudentUser([
        'name' => 'Student User',
        'email' => 'student-old@example.com',
        'phone' => '9800000003',
        'address' => 'Hostel Block A',
    ]);

    $response = $this
        ->actingAs($studentUser)
        ->postJson(route('student.profile.update-personal-info'), [
            'name' => 'Student User',
            'email' => 'student-new@example.com',
            'phone' => '9800000003',
            'address' => 'Hostel Block A',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('email_changed', true);

    expect(ActivityLog::query()->where('action', 'profile_updated')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'profile_updated')->value('description'))
        ->toBe('Profile updated: email: student-old@example.com -> student-new@example.com');
});
