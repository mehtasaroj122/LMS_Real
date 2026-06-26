<?php

use App\Mail\WelcomeEmail;
use App\Models\department as Department;
use App\Models\Notification;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function makeMobileRegistrationDepartment(): Department
{
    return Department::create([
        'name' => 'Mobile Registration ' . Str::random(8),
        'code' => Str::upper(Str::random(6)),
        'status' => 'active',
    ]);
}

function makeMobileInvitedStudent(array $overrides = []): User
{
    $department = makeMobileRegistrationDepartment();
    $identifier = $overrides['identifier'] ?? 'STU-' . Str::upper(Str::random(8));

    $user = User::create([
        'name' => $overrides['name'] ?? 'Mobile Invited Student',
        'email' => $overrides['email'] ?? 'student-' . Str::random(8) . '@example.com',
        'phone' => $overrides['phone'] ?? '+9779807044875',
        'role' => 'student',
        'status' => $overrides['status'] ?? 'inactive',
        'password' => $overrides['password'] ?? null,
        'email_verified_at' => $overrides['email_verified_at'] ?? null,
    ]);

    Student::create([
        'user_id' => $user->id,
        'student_id' => $identifier,
        'roll_no' => $identifier,
        'department_id' => $department->id,
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);

    return $user->fresh()->load('student');
}

function makeMobileInvitedStaff(array $overrides = []): User
{
    $department = makeMobileRegistrationDepartment();
    $identifier = $overrides['identifier'] ?? 'STAFF-' . Str::upper(Str::random(8));

    $user = User::create([
        'name' => $overrides['name'] ?? 'Mobile Invited Staff',
        'email' => $overrides['email'] ?? 'staff-' . Str::random(8) . '@example.com',
        'phone' => $overrides['phone'] ?? '+9779807044876',
        'role' => 'staff',
        'status' => $overrides['status'] ?? 'inactive',
        'password' => $overrides['password'] ?? null,
        'email_verified_at' => $overrides['email_verified_at'] ?? null,
    ]);

    Staff::create([
        'user_id' => $user->id,
        'staff_id' => $identifier,
        'department_id' => $department->id,
        'designation' => 'Librarian',
        'join_date' => now()->toDateString(),
    ]);

    return $user->fresh()->load('staff');
}

test('mobile invited student can complete registration with matching details', function () {
    Mail::fake();
    $user = makeMobileInvitedStudent([
        'email' => 'mobile-student@example.com',
        'identifier' => 'STU-2023-001',
        'phone' => '+9779807044875',
    ]);

    $response = $this->postJson('/api/auth/complete-registration', [
        'role' => 'student',
        'email' => 'mobile-student@example.com',
        'identifier' => 'STU-2023-001',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Registration completed successfully. You can now sign in.')
        ->assertJsonPath('data.role', 'student')
        ->assertJsonPath('data.email', 'mobile-student@example.com');

    $user->refresh();

    expect($user->status)->toBe('active');
    expect($user->is_verified)->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
    expect(Hash::check('Password123', $user->password))->toBeTrue();

    expect(Notification::query()
        ->where('user_id', $user->id)
        ->where('type', 'account.registration_completed')
        ->exists())->toBeTrue();

    Mail::assertQueued(WelcomeEmail::class);
});

test('mobile invited staff can complete registration with matching details', function () {
    Mail::fake();
    $user = makeMobileInvitedStaff([
        'email' => 'mobile-staff@example.com',
        'identifier' => 'STAFF-001',
        'phone' => '+9779807044875',
    ]);

    $response = $this->postJson('/api/auth/complete-registration', [
        'role' => 'staff',
        'email' => 'mobile-staff@example.com',
        'identifier' => 'STAFF-001',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.role', 'staff')
        ->assertJsonPath('data.email', 'mobile-staff@example.com');

    expect($user->fresh()->status)->toBe('active');
    expect(Hash::check('Password123', $user->fresh()->password))->toBeTrue();
});

test('mobile complete registration returns role specific error for wrong email', function () {
    Mail::fake();
    makeMobileInvitedStudent([
        'email' => 'right-student@example.com',
        'identifier' => 'STU-EMAIL-001',
        'phone' => '+9779807044875',
    ]);

    $this->postJson('/api/auth/complete-registration', [
        'role' => 'student',
        'email' => 'wrong-student@example.com',
        'identifier' => 'STU-EMAIL-001',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('email')
        ->assertJsonPath('errors.email.0', 'We could not find an invited student account with that email address.');
});

test('mobile complete registration rejects wrong identifier', function () {
    Mail::fake();
    makeMobileInvitedStaff([
        'email' => 'identifier-staff@example.com',
        'identifier' => 'STAFF-RIGHT',
        'phone' => '+9779807044875',
    ]);

    $this->postJson('/api/auth/complete-registration', [
        'role' => 'staff',
        'email' => 'identifier-staff@example.com',
        'identifier' => 'STAFF-WRONG',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('identifier')
        ->assertJsonPath('errors.identifier.0', 'We could not find an invited staff account with that staff ID.');
});

test('mobile complete registration rejects wrong phone', function () {
    Mail::fake();
    makeMobileInvitedStudent([
        'email' => 'phone-student@example.com',
        'identifier' => 'STU-PHONE-001',
        'phone' => '+9779807044875',
    ]);

    $this->postJson('/api/auth/complete-registration', [
        'role' => 'student',
        'email' => 'phone-student@example.com',
        'identifier' => 'STU-PHONE-001',
        'phone' => '9811111111',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('phone')
        ->assertJsonPath('errors.phone.0', 'The phone number does not match the invited account details.');
});

test('mobile complete registration rejects already registered account with sign in message', function () {
    $user = makeMobileInvitedStudent([
        'email' => 'already-mobile@example.com',
        'identifier' => 'STU-ALREADY',
        'phone' => '+9779807044875',
        'status' => 'active',
        'password' => Hash::make('Password123'),
        'email_verified_at' => now(),
    ]);

    $this->postJson('/api/auth/complete-registration', [
        'role' => 'student',
        'email' => 'already-mobile@example.com',
        'identifier' => 'STU-ALREADY',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('email')
        ->assertJsonPath('errors.email.0', 'This account is already registered. Please sign in.');

    expect($user->fresh()->status)->toBe('active');
});

test('mobile complete registration rejects password mismatch', function () {
    Mail::fake();
    makeMobileInvitedStaff([
        'email' => 'mismatch-staff@example.com',
        'identifier' => 'STAFF-MISMATCH',
        'phone' => '+9779807044875',
    ]);

    $this->postJson('/api/auth/complete-registration', [
        'role' => 'staff',
        'email' => 'mismatch-staff@example.com',
        'identifier' => 'STAFF-MISMATCH',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Different123',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('password');
});

test('mobile user can login after completing registration', function () {
    Mail::fake();
    makeMobileInvitedStudent([
        'email' => 'login-after-registration@example.com',
        'identifier' => 'STU-LOGIN-001',
        'phone' => '+9779807044875',
    ]);

    $this->postJson('/api/auth/complete-registration', [
        'role' => 'student',
        'email' => 'login-after-registration@example.com',
        'identifier' => 'STU-LOGIN-001',
        'phone' => '9807044875',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertOk();

    $this->postJson('/api/login', [
        'email' => 'login-after-registration@example.com',
        'password' => 'Password123',
        'device_name' => 'android-test',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonStructure(['access_token', 'user']);
});
