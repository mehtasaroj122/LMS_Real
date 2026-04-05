<?php

use App\Mail\WelcomeEmail;
use App\Models\staff;
use App\Models\Student;
use App\Models\User;
use App\Models\department as Department;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('register page shows sign-in guidance for an already active account', function () {
    $department = Department::create([
        'name' => 'Archives',
        'code' => 'ARC',
        'status' => 'active',
    ]);

    $user = User::create([
        'name' => 'Existing Staff',
        'email' => 'existing-staff@example.com',
        'phone' => '+9779812000000',
        'role' => 'staff',
        'status' => 'active',
        'password' => 'Password!123',
        'email_verified_at' => now(),
    ]);

    staff::create([
        'user_id' => $user->id,
        'staff_id' => 'STAFF-EXISTING',
        'department_id' => $department->id,
    ]);

    $response = $this->get('/register?role=staff&email=existing-staff@example.com');

    $response
        ->assertOk()
        ->assertSee('This account is already active. Please sign in with your email and password instead.');
});

test('invited staff can complete registration with matching identity details', function () {
    Mail::fake();

    $department = Department::create([
        'name' => 'Library Services',
        'code' => 'LS',
        'status' => 'active',
    ]);

    $user = User::create([
        'name' => 'Staff Invite',
        'email' => 'staff-invite@example.com',
        'phone' => '+9779812345678',
        'role' => 'staff',
        'status' => 'inactive',
        'password' => null,
    ]);

    staff::create([
        'user_id' => $user->id,
        'staff_id' => 'STAFF-000101',
        'department_id' => $department->id,
    ]);

    $response = $this->post('/register', [
        'role' => 'staff',
        'email' => 'staff-invite@example.com',
        'staff_id' => 'STAFF-000101',
        'phone' => '+9779812345678',
        'password' => 'Password!123',
        'password_confirmation' => 'Password!123',
    ]);

    $response->assertRedirect(route('staff.dashboard', absolute: false));
    $this->assertAuthenticatedAs($user->fresh());

    $user->refresh();

    expect($user->status)->toBe('active');
    expect($user->password)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();

    Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail) {
        return $mail->hasTo('staff-invite@example.com');
    });
});

test('invited student registration fails when phone does not match stored identity details', function () {
    Mail::fake();

    $department = Department::create([
        'name' => 'Computer Science',
        'code' => 'CSE',
        'status' => 'active',
    ]);

    $user = User::create([
        'name' => 'Student Invite',
        'email' => 'student-invite@example.com',
        'phone' => '+9779800000000',
        'role' => 'student',
        'status' => 'inactive',
        'password' => null,
    ]);

    Student::create([
        'user_id' => $user->id,
        'student_id' => 'STU-000201',
        'roll_no' => 'STU-000201',
        'department_id' => $department->id,
        'batch' => '2025',
        'semester' => '2',
        'address' => 'Kathmandu, Nepal',
    ]);

    $response = $this->from(route('register'))->post('/register', [
        'role' => 'student',
        'email' => 'student-invite@example.com',
        'student_id' => 'STU-000201',
        'phone' => '+9779811111111',
        'password' => 'Password!123',
        'password_confirmation' => 'Password!123',
    ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('student_id');

    $this->assertGuest();

    $user->refresh();

    expect($user->status)->toBe('inactive');
    expect($user->password)->toBeNull();

    Mail::assertNothingQueued();
});

test('already active invited account is told to sign in instead of registering again', function () {
    $department = Department::create([
        'name' => 'Reference',
        'code' => 'REF',
        'status' => 'active',
    ]);

    $user = User::create([
        'name' => 'Existing Student',
        'email' => 'existing-student@example.com',
        'phone' => '+9779813333333',
        'role' => 'student',
        'status' => 'active',
        'password' => 'Password!123',
        'email_verified_at' => now(),
    ]);

    Student::create([
        'user_id' => $user->id,
        'student_id' => 'STU-EXISTING',
        'roll_no' => 'STU-EXISTING',
        'department_id' => $department->id,
        'batch' => '2025',
        'semester' => '2',
        'address' => 'Kathmandu, Nepal',
    ]);

    $response = $this->from(route('register'))->post('/register', [
        'role' => 'student',
        'email' => 'existing-student@example.com',
        'student_id' => 'STU-EXISTING',
        'phone' => '+9779813333333',
        'password' => 'Password!123',
        'password_confirmation' => 'Password!123',
    ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('email');
});
