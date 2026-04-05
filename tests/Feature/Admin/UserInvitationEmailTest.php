<?php

use App\Mail\RegistrationInvitationEmail;
use App\Models\User;
use App\Models\department as Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function makeInvitationAdmin(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'role' => 'admin',
        'name' => 'Invitation Admin',
        'email' => 'invitation-admin-' . Str::lower(Str::random(6)) . '@example.com',
        'phone' => '+9779800000001',
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Admin!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ], $overrides));
}

test('admin user management queues a registration invitation email for invited staff users', function () {
    Mail::fake();

    $admin = makeInvitationAdmin();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.users.store'), [
            'name' => 'Invited Staff',
            'email' => 'invited.staff@example.com',
            'role' => 'staff',
            'phone' => '+9779812345678',
            'gender' => 'male',
            'address' => 'Kathmandu, Nepal Ward 10',
            'staff_id' => 'STAFF-9001',
            'designation' => 'Assistant Librarian',
            'join_date' => now()->subYear()->toDateString(),
            'status' => 'inactive',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    Mail::assertQueued(RegistrationInvitationEmail::class, function (RegistrationInvitationEmail $mail) {
        return $mail->hasTo('invited.staff@example.com')
            && $mail->identifierValue === 'STAFF-9001'
            && $mail->phone === '+9779812345678'
            && $mail->registerUrl === route('register', ['role' => 'staff', 'email' => 'invited.staff@example.com']);
    });
});

test('admin student management queues a registration invitation email for invited students', function () {
    Mail::fake();

    $admin = makeInvitationAdmin([
        'email' => 'invitation-admin-students@example.com',
        'phone' => '+9779800000002',
    ]);

    $department = Department::create([
        'name' => 'Computer Science',
        'code' => 'CS',
        'status' => 'active',
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('admin.students.store'), [
            'name' => 'Invited Student',
            'email' => 'invited.student@example.com',
            'phone' => '+9779811111111',
            'gender' => 'female',
            'date_of_birth' => '2004-05-10',
            'roll_no' => 'STU-9001',
            'department_id' => $department->id,
            'batch' => '2026',
            'semester' => '4',
            'address' => 'Pokhara, Nepal Ward 8',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    Mail::assertQueued(RegistrationInvitationEmail::class, function (RegistrationInvitationEmail $mail) {
        return $mail->hasTo('invited.student@example.com')
            && $mail->identifierValue === 'STU-9001'
            && $mail->phone === '+9779811111111'
            && $mail->registerUrl === route('register', ['role' => 'student', 'email' => 'invited.student@example.com']);
    });
});
