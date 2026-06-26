<?php

use App\Jobs\SendFineEmail;
use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function makeStaffFineApiUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' User ' . $unique,
        'email' => "{$role}-api-{$unique}@example.com",
        'phone' => '97' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makeStaffFineApiStudent(array $overrides = []): Student
{
    $unique = Str::lower(Str::random(8));
    $user = makeStaffFineApiUser('student', [
        'name' => $overrides['name'] ?? 'Fine Student ' . $unique,
        'email' => $overrides['email'] ?? "fine-student-{$unique}@example.com",
    ]);

    $department = Department::create([
        'name' => $overrides['department'] ?? 'Computer Science',
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => $overrides['student_id'] ?? 'CS-' . Str::upper(substr($unique, 0, 6)),
        'roll_no' => $overrides['roll_no'] ?? 'ROLL-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Pokhara, Nepal',
    ]);
}

function makeStaffFineApiFine(Student $student, string $status = 'pending', array $overrides = []): Fine
{
    $unique = Str::lower(Str::random(8));
    $operator = makeStaffFineApiUser('staff', [
        'email' => "issuer-api-{$unique}@example.com",
        'name' => 'Issuer ' . $unique,
    ]);

    $category = Category::create([
        'name' => 'Fine API Category ' . $unique,
        'description' => 'Fine API test category',
    ]);

    $book = Book::create([
        'category_id' => $category->id,
        'title' => $overrides['book_title'] ?? 'Clean Code ' . $unique,
        'author' => $overrides['author'] ?? 'Robert C. Martin',
        'publisher' => 'Publisher ' . $unique,
        'isbn' => 'ISBN-STAFF-FINE-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 3,
        'condition' => 'good',
        'description' => 'Fine API test book',
        'shelf_no' => 'S-' . Str::upper(substr($unique, 0, 4)),
        'status' => 'available',
    ]);

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $operator->id,
        'issue_date' => now()->subDays(15),
        'due_date' => now()->subDays(5),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => (float) ($overrides['amount'] ?? 100),
        'remarks' => 'Issued for fine API test',
    ]);

    return Fine::create([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => $overrides['amount'] ?? 100,
        'days_late' => $overrides['days_late'] ?? 5,
        'status' => $status,
        'paid_on' => $status === 'paid' ? now()->subDay() : null,
        'payment_method' => 'cash',
        'remarks' => $overrides['remarks'] ?? 'Overdue fine',
        'waive_reason' => $status === 'waived' ? ($overrides['waive_reason'] ?? 'Previously waived') : null,
        'waived_at' => $status === 'waived' ? now()->subDay() : null,
    ]);
}

test('staff fines APIs require staff or admin role', function () {
    $student = makeStaffFineApiUser('student');

    $this->getJson('/api/staff/fines/summary')->assertUnauthorized();

    Sanctum::actingAs($student);

    $this->getJson('/api/staff/fines/summary')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden.');
});

test('staff can fetch fine summary, grouped students, search, and student detail', function () {
    $staff = makeStaffFineApiUser('staff');
    $student = makeStaffFineApiStudent([
        'name' => 'Saroj Mehta',
        'email' => 'saroj-fines@example.com',
        'roll_no' => 'CS-2026-001',
        'student_id' => 'SID-2026-001',
        'department' => 'Computer Science',
    ]);

    makeStaffFineApiFine($student, 'pending', ['amount' => 250, 'book_title' => 'Clean Code', 'remarks' => 'Damaged book']);
    makeStaffFineApiFine($student, 'paid', ['amount' => 100, 'book_title' => 'Refactoring', 'remarks' => 'Late return']);
    makeStaffFineApiFine($student, 'waived', ['amount' => 50, 'book_title' => 'Patterns', 'waive_reason' => 'Approved reason']);

    Sanctum::actingAs($staff);

    $this->getJson('/api/staff/fines/summary')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total_fines', 400)
        ->assertJsonPath('data.pending', 250)
        ->assertJsonPath('data.collected', 100)
        ->assertJsonPath('data.waived', 50)
        ->assertJsonPath('data.total_records', 3);

    $this->getJson('/api/staff/fines/students?search=Clean')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.name', 'Saroj Mehta')
        ->assertJsonPath('data.0.pending_amount', 250)
        ->assertJsonPath('data.0.status', 'pending');

    $this->getJson("/api/staff/fines/students/{$student->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.student.name', 'Saroj Mehta')
        ->assertJsonPath('data.summary.records_count', 3)
        ->assertJsonCount(3, 'data.fines');
});

test('staff can pay and waive pending fines with persisted action metadata', function () {
    Queue::fake();

    $staff = makeStaffFineApiUser('staff');
    $student = makeStaffFineApiStudent();
    $payFine = makeStaffFineApiFine($student, 'pending', ['amount' => 125]);
    $waiveFine = makeStaffFineApiFine($student, 'pending', ['amount' => 75]);

    Sanctum::actingAs($staff);

    $this->postJson("/api/staff/fines/{$waiveFine->id}/waive")
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['reason']);

    $this->postJson("/api/staff/fines/{$payFine->id}/pay")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.paid_by', $staff->id);

    expect($payFine->fresh()->paid_at)->not->toBeNull();
    expect($payFine->fresh()->paid_by)->toBe($staff->id);

    $this->postJson("/api/staff/fines/{$payFine->id}/pay")
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'This fine is already paid.');

    $this->postJson("/api/staff/fines/{$payFine->id}/waive", ['reason' => 'After payment'])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Paid fine cannot be waived.');

    $this->postJson("/api/staff/fines/{$waiveFine->id}/waive", [
        'reason' => 'Approved by librarian due to valid reason.',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'waived')
        ->assertJsonPath('data.waive_reason', 'Approved by librarian due to valid reason.')
        ->assertJsonPath('data.waived_by', $staff->id);

    expect($waiveFine->fresh()->waive_reason)->toBe('Approved by librarian due to valid reason.');
    expect($waiveFine->fresh()->waived_at)->not->toBeNull();
    expect($waiveFine->fresh()->waived_by)->toBe($staff->id);

    Queue::assertPushed(SendFineEmail::class, 2);
});
