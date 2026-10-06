<?php

use App\Mail\RegistrationInvitationEmail;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function mirroredStudentPayload(): array
{
    $department = Department::create(['name' => 'Computing', 'code' => 'CMP', 'status' => 'active']);

    return [
        'name' => 'New Student',
        'email' => 'new.student@example.com',
        'role' => 'student',
        'phone' => '',
        'gender' => 'female',
        'date_of_birth' => today()->subYears(20)->format('Y-m-d'),
        'department_id' => $department->id,
        'roll_no' => 'CMP-2026-001',
        'batch' => '2026',
        'semester' => '8',
        'address' => '',
        'status' => 'inactive',
    ];
}

test('both creation pages use the same student fields and the students page only offers student', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->get(route('admin.users.index'))->assertOk()->assertSee('id="addDateOfBirth"', false);
    $response = $this->get(route('admin.students.index'))->assertOk();
    $html = $response->getContent();
    preg_match('/<select[^>]*id="addRoleSelect"[^>]*>(.*?)<\/select>/s', $html, $role);
    expect($role[1])->toContain('value="student"')->not->toContain('value="admin"', 'value="staff"');
    foreach (['addName', 'addEmail', 'addPhone', 'addGender', 'addDateOfBirth', 'addDepartmentSelect', 'addRollNo', 'addBatch', 'addSemester', 'addAddress'] as $id) {
        $response->assertSee('id="'.$id.'"', false);
    }
});

test('both student creation routes save date of birth and create inactive invitations with optional contact fields', function (string $route) {
    Mail::fake();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $payload = mirroredStudentPayload();
    $payload['name'] = '  New   Student  ';
    $payload['email'] = ' NEW.STUDENT@EXAMPLE.COM ';
    $payload['roll_no'] = ' cmp-2026-001 ';
    $payload['status'] = 'active';

    $this->postJson(route($route), $payload)->assertOk()->assertJsonPath('success', true);

    $user = User::where('email', 'new.student@example.com')->firstOrFail();
    expect($user->role)->toBe('student')
        ->and($user->status)->toBe('inactive')
        ->and($user->password)->toBeNull()
        ->and($user->phone)->toBeNull()
        ->and($user->address)->toBeNull()
        ->and($user->name)->toBe('New Student')
        ->and($user->date_of_birth->format('Y-m-d'))->toBe($payload['date_of_birth'])
        ->and($user->student->roll_no)->toBe('CMP-2026-001');
    Mail::assertQueued(RegistrationInvitationEmail::class);
})->with(['admin.users.store', 'admin.students.store']);

test('student creation validation messages match across both routes', function (string $field, mixed $value) {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $payload = mirroredStudentPayload();
    $payload[$field] = $value;

    $userResponse = $this->postJson(route('admin.users.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
    $studentResponse = $this->postJson(route('admin.students.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors($field);

    expect($studentResponse->json('errors'))->toBe($userResponse->json('errors'))
        ->and($studentResponse->json('message'))->toBe($userResponse->json('message'));
})->with([
    'name required' => ['name', ''],
    'name length' => ['name', str_repeat('A', 256)],
    'email format' => ['email', 'bad-email'],
    'phone format' => ['phone', '9812345678'],
    'gender option' => ['gender', 'invalid'],
    'birth date required' => ['date_of_birth', ''],
    'birth date invalid' => ['date_of_birth', '2004-02-30'],
    'birth date future' => ['date_of_birth', '2999-01-01'],
    'student age' => ['date_of_birth', '1900-01-01'],
    'student identifier' => ['roll_no', 'bad_id'],
    'student identifier length' => ['roll_no', str_repeat('A', 101)],
    'department' => ['department_id', '999999'],
    'batch' => ['batch', '26'],
    'semester limit' => ['semester', '9'],
    'address length' => ['address', 'short'],
]);

test('both student creation routes accept the user management identifier length limit', function (string $route) {
    Mail::fake();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $payload = mirroredStudentPayload();
    $payload['roll_no'] = str_repeat('A', 100);

    $this->postJson(route($route), $payload)->assertOk();
    $this->assertDatabaseHas('students', ['student_id' => $payload['roll_no'], 'roll_no' => $payload['roll_no']]);
})->with(['admin.users.store', 'admin.students.store']);

test('both student creation routes report the same duplicate identity messages', function (string $field) {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $payload = mirroredStudentPayload();
    $existing = User::factory()->create(['email' => 'existing@example.com', 'phone' => '+9779812345678']);
    $student = Student::create([
        'user_id' => $existing->id,
        'department_id' => $payload['department_id'],
        'student_id' => 'CMP-EXISTING',
        'roll_no' => 'CMP-EXISTING',
        'batch' => '2026',
        'semester' => 1,
    ]);
    $payload[$field] = match ($field) {
        'email' => $existing->email,
        'phone' => $existing->phone,
        'roll_no' => $student->roll_no,
    };

    $first = $this->postJson(route('admin.users.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
    $second = $this->postJson(route('admin.students.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
    expect($second->json('errors'))->toBe($first->json('errors'));
})->with(['email', 'phone', 'roll_no']);

test('students creation rejects a supplied non-student role', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $payload = mirroredStudentPayload();
    $payload['role'] = $role;

    $this->postJson(route('admin.students.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('role');
    $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
})->with(['admin', 'staff']);

test('live date of birth validation uses the creation age rule', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->postJson(route('admin.users.validate-field'), [
        'field' => 'date_of_birth',
        'role' => 'student',
        'date_of_birth' => '1900-01-01',
    ])->assertUnprocessable()->assertJsonPath('message', 'Student age must be between 14 and 100 years.');
});
