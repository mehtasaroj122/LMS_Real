<?php

use App\Models\BookRequest;
use App\Models\book as Book;
use App\Models\category as Category;
use App\Models\department as Department;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeRoleUser(string $role, array $overrides = []): User
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

function makeBookRequestRecord(string $status = 'pending'): BookRequest
{
    $unique = Str::lower(Str::random(8));
    $studentUser = makeRoleUser('student', [
        'name' => 'Student ' . $unique,
        'email' => "student-{$unique}@example.com",
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
        'description' => 'Bulk action test category',
    ]);

    $book = Book::create([
        'category_id' => $category->id,
        'title' => 'Book ' . $unique,
        'author' => 'Author ' . $unique,
        'publisher' => 'Publisher ' . $unique,
        'isbn' => 'ISBN-' . Str::upper($unique),
        'total_copies' => 4,
        'available_copies' => 2,
        'condition' => 'good',
        'description' => 'Bulk action test book',
        'shelf_no' => 'S-' . Str::upper(substr($unique, 0, 4)),
        'status' => 'available',
    ]);

    return BookRequest::create([
        'student_id' => $student->id,
        'book_id' => $book->id,
        'request_date' => now()->subDay(),
        'status' => $status,
        'processed_by' => $status === 'pending' ? null : 'Previous Handler',
        'processed_date' => $status === 'pending' ? null : now()->subHours(4),
    ]);
}

test('admin can bulk approve pending requests while skipping already processed ones', function () {
    $admin = makeRoleUser('admin', [
        'name' => 'Primary Admin',
        'email' => 'admin-bulk@example.com',
    ]);

    $pendingOne = makeBookRequestRecord('pending');
    $pendingTwo = makeBookRequestRecord('pending');
    $alreadyApproved = makeBookRequestRecord('approved');

    $response = $this
        ->actingAs($admin)
        ->postJson(route('admin.book-requests.bulk-status'), [
            'status' => 'approved',
            'request_ids' => [$pendingOne->id, $pendingTwo->id, $alreadyApproved->id],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processedCount', 2)
        ->assertJsonPath('skippedCount', 1);

    expect($pendingOne->fresh()->status)->toBe('approved');
    expect($pendingTwo->fresh()->status)->toBe('approved');
    expect($pendingOne->fresh()->processed_by)->toBe('Primary Admin');
    expect($pendingTwo->fresh()->processed_by)->toBe('Primary Admin');
    expect($alreadyApproved->fresh()->processed_by)->toBe('Previous Handler');

    expect(Notification::query()->count())->toBe(4);
});

test('staff can bulk reject pending requests without notifying admins', function () {
    $staff = makeRoleUser('staff', [
        'name' => 'Desk Staff',
        'email' => 'staff-bulk@example.com',
    ]);

    $pendingRequest = makeBookRequestRecord('pending');
    $alreadyRejected = makeBookRequestRecord('rejected');

    $response = $this
        ->actingAs($staff)
        ->postJson(route('staff.book-requests.bulk-status'), [
            'status' => 'rejected',
            'request_ids' => [$pendingRequest->id, $alreadyRejected->id],
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processedCount', 1)
        ->assertJsonPath('skippedCount', 1);

    expect($pendingRequest->fresh()->status)->toBe('rejected');
    expect($pendingRequest->fresh()->processed_by)->toBe('Desk Staff');
    expect($alreadyRejected->fresh()->processed_by)->toBe('Previous Handler');

    expect(Notification::query()->count())->toBe(1);
    expect(Notification::query()->first()?->type)->toBe('request.rejected');
});
