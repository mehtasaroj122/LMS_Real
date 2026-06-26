<?php

use App\Jobs\SendFineEmail;
use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function makeFineBulkTestUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' User ' . $unique,
        'email' => "{$role}-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makeFineBulkTestRecord(string $status = 'pending', array $overrides = []): Fine
{
    $unique = Str::lower(Str::random(8));
    $operator = makeFineBulkTestUser('admin', [
        'email' => "issuer-{$unique}@example.com",
        'name' => 'Issuer ' . $unique,
    ]);

    $studentUser = makeFineBulkTestUser('student', [
        'email' => "student-fine-{$unique}@example.com",
        'name' => 'Student ' . $unique,
    ]);

    $department = Department::create([
        'name' => 'Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'department_id' => $department->id,
        'roll_no' => 'ROLL-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Pokhara, Nepal',
    ]);

    $category = Category::create([
        'name' => 'Category ' . $unique,
        'description' => 'Fine bulk action test category',
    ]);

    $book = Book::create([
        'category_id' => $category->id,
        'title' => 'Book ' . $unique,
        'author' => 'Author ' . $unique,
        'publisher' => 'Publisher ' . $unique,
        'isbn' => 'ISBN-FINE-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 3,
        'condition' => 'good',
        'description' => 'Fine bulk action test book',
        'shelf_no' => 'S-' . Str::upper(substr($unique, 0, 4)),
        'status' => 'available',
    ]);

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $operator->id,
        'issue_date' => now()->subDays(10),
        'due_date' => now()->subDays(4),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => (float) ($overrides['amount'] ?? 100),
        'remarks' => 'Issued for testing',
    ]);

    return Fine::create(array_merge([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => 100,
        'days_late' => 4,
        'status' => $status,
        'paid_on' => $status === 'paid' ? now()->subDay() : null,
        'payment_method' => 'cash',
        'remarks' => $status === 'waived' ? 'Previously waived' : null,
    ], $overrides));
}

test('admin can bulk mark pending fines as paid while skipping already processed fines', function () {
    $admin = makeFineBulkTestUser('admin', [
        'name' => 'Fine Admin',
        'email' => 'fine-admin-bulk@example.com',
    ]);

    $pendingOne = makeFineBulkTestRecord('pending', ['amount' => 125.50]);
    $pendingTwo = makeFineBulkTestRecord('pending', ['amount' => 80.00]);
    $alreadyPaid = makeFineBulkTestRecord('paid', ['amount' => 60.00]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('admin.fines.bulk-status'), [
            'status' => 'paid',
            'fine_ids' => [$pendingOne->id, $pendingTwo->id, $alreadyPaid->id],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processedCount', 2)
        ->assertJsonPath('skippedCount', 1)
        ->assertJsonPath('totalAmount', 205.5);

    expect($pendingOne->fresh()->status)->toBe('paid');
    expect($pendingTwo->fresh()->status)->toBe('paid');
    expect($pendingOne->fresh()->paid_on)->not->toBeNull();
    expect($pendingTwo->fresh()->paid_on)->not->toBeNull();
    expect($alreadyPaid->fresh()->paid_on)->not->toBeNull();

    expect(Notification::query()->count())->toBe(0);
});

test('staff can bulk waive pending fines with a shared reason', function () {
    $staff = makeFineBulkTestUser('staff', [
        'name' => 'Fine Staff',
        'email' => 'fine-staff-bulk@example.com',
    ]);

    $pendingOne = makeFineBulkTestRecord('pending', ['amount' => 90.00]);
    $pendingTwo = makeFineBulkTestRecord('pending', ['amount' => 45.00]);
    $alreadyWaived = makeFineBulkTestRecord('waived', ['amount' => 30.00, 'remarks' => 'Old waiver']);

    $response = $this
        ->actingAs($staff)
        ->postJson(route('staff.fines.bulk-status'), [
            'status' => 'waived',
            'fine_ids' => [$pendingOne->id, $pendingTwo->id, $alreadyWaived->id],
            'remarks' => 'Approved as a staff courtesy',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processedCount', 2)
        ->assertJsonPath('skippedCount', 1)
        ->assertJsonPath('totalAmount', 135);

    expect($pendingOne->fresh()->status)->toBe('waived');
    expect($pendingTwo->fresh()->status)->toBe('waived');
    expect($pendingOne->fresh()->remarks)->toBe('Approved as a staff courtesy');
    expect($pendingTwo->fresh()->remarks)->toBe('Approved as a staff courtesy');
    expect($alreadyWaived->fresh()->remarks)->toBe('Old waiver');

    expect(Notification::query()->count())->toBe(2);
    expect(Notification::query()->pluck('type')->all())->toBe(['fine.reminder', 'fine.reminder']);
});

test('staff can mark a pending fine as paid and queue the confirmation email', function () {
    Queue::fake();

    $staff = makeFineBulkTestUser('staff', [
        'name' => 'Fine Desk Staff',
        'email' => 'fine-desk-staff@example.com',
    ]);

    $fine = makeFineBulkTestRecord('pending', ['amount' => 125.00]);

    $response = $this
        ->actingAs($staff)
        ->postJson(route('staff.fines.mark-as-paid', $fine));

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($fine->fresh()->status)->toBe('paid');
    expect($fine->fresh()->paid_on)->not->toBeNull();

    Queue::assertPushed(SendFineEmail::class, 1);
});

test('staff can bulk mark pending fines as paid and queue confirmation emails', function () {
    Queue::fake();

    $staff = makeFineBulkTestUser('staff', [
        'name' => 'Fine Bulk Staff',
        'email' => 'fine-bulk-staff@example.com',
    ]);

    $pendingOne = makeFineBulkTestRecord('pending', ['amount' => 110.00]);
    $pendingTwo = makeFineBulkTestRecord('pending', ['amount' => 95.00]);
    $alreadyPaid = makeFineBulkTestRecord('paid', ['amount' => 60.00]);

    $response = $this
        ->actingAs($staff)
        ->postJson(route('staff.fines.bulk-status'), [
            'status' => 'paid',
            'fine_ids' => [$pendingOne->id, $pendingTwo->id, $alreadyPaid->id],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processedCount', 2)
        ->assertJsonPath('skippedCount', 1);

    expect((float) $response->json('totalAmount'))->toBe(205.0);

    expect($pendingOne->fresh()->status)->toBe('paid');
    expect($pendingTwo->fresh()->status)->toBe('paid');
    expect($pendingOne->fresh()->paid_on)->not->toBeNull();
    expect($pendingTwo->fresh()->paid_on)->not->toBeNull();

    Queue::assertPushed(SendFineEmail::class, 2);
});

test('admin can bulk queue fine emails without changing fine statuses', function () {
    Queue::fake();

    $admin = makeFineBulkTestUser('admin', [
        'name' => 'Fine Mail Admin',
        'email' => 'fine-mail-admin@example.com',
    ]);

    $pendingFine = makeFineBulkTestRecord('pending', ['amount' => 150.00]);
    $waivedFine = makeFineBulkTestRecord('waived', ['amount' => 75.00, 'remarks' => 'Already waived']);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('admin.fines.bulk-email'), [
            'fine_ids' => [$pendingFine->id, $waivedFine->id],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processedCount', 2)
        ->assertJsonPath('skippedCount', 0)
        ->assertJsonPath('recipientCount', 2);

    expect($pendingFine->fresh()->status)->toBe('pending');
    expect($waivedFine->fresh()->status)->toBe('waived');

    Queue::assertPushed(SendFineEmail::class, 2);
});

test('staff bulk fine email validates that at least one fine is selected', function () {
    $staff = makeFineBulkTestUser('staff', [
        'name' => 'Fine Mail Staff',
        'email' => 'fine-mail-staff@example.com',
    ]);

    $response = $this
        ->actingAs($staff)
        ->postJson(route('staff.fines.bulk-email'), [
            'fine_ids' => [],
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fine_ids']);
});
