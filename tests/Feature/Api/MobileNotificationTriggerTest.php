<?php

use App\Models\BookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\IssuedBook;
use App\Models\Notification;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function makeMobileNotificationUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' Notification ' . $unique,
        'email' => "{$role}-notification-{$unique}@example.com",
        'phone' => '96' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeMobileNotificationDepartment(): Department
{
    $unique = Str::upper(Str::random(6));

    return Department::create([
        'name' => 'Notification Department ' . $unique,
        'code' => substr($unique, 0, 4),
        'status' => 'active',
    ]);
}

function makeMobileNotificationStudent(?User $user = null): Student
{
    $unique = Str::upper(Str::random(6));
    $user ??= makeMobileNotificationUser('student');

    return Student::create([
        'user_id' => $user->id,
        'department_id' => makeMobileNotificationDepartment()->id,
        'student_id' => 'MN-' . $unique,
        'roll_no' => 'MR-' . $unique,
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);
}

function makeMobileNotificationStaff(?User $user = null): Staff
{
    $unique = Str::upper(Str::random(6));
    $user ??= makeMobileNotificationUser('staff');

    return Staff::create([
        'user_id' => $user->id,
        'department_id' => makeMobileNotificationDepartment()->id,
        'staff_id' => 'MS-' . $unique,
        'designation' => 'Librarian',
        'join_date' => now()->toDateString(),
    ]);
}

function makeMobileNotificationBook(array $overrides = []): Book
{
    $unique = Str::upper(Str::random(8));
    $category = Category::create([
        'name' => 'Notification Category ' . $unique,
        'description' => 'Mobile notification trigger tests',
    ]);

    return Book::create(array_merge([
        'category_id' => $category->id,
        'title' => 'Notification Book ' . $unique,
        'author' => 'Notification Author',
        'publisher' => 'Notification Publisher',
        'isbn' => 'MN-' . $unique,
        'total_copies' => 3,
        'available_copies' => 2,
        'condition' => 'good',
        'description' => 'Mobile notification trigger test book',
        'shelf_no' => 'N-' . substr($unique, 0, 4),
        'status' => 'available',
    ], $overrides));
}

test('student mobile request creates staff and admin notifications', function () {
    $studentUser = makeMobileNotificationUser('student');
    makeMobileNotificationStudent($studentUser);
    $staffUser = makeMobileNotificationUser('staff');
    makeMobileNotificationStaff($staffUser);
    $adminUser = makeMobileNotificationUser('admin');
    $book = makeMobileNotificationBook();

    Sanctum::actingAs($studentUser);

    $this->postJson('/api/student/requests', [
        'book_id' => $book->id,
        'remarks' => 'Need this for coursework',
    ])->assertCreated();

    expect(Notification::query()->where('type', 'student.book_request')->count())->toBe(2);
    expect(Notification::query()->where('user_id', $staffUser->id)->where('type', 'student.book_request')->exists())->toBeTrue();
    expect(Notification::query()->where('user_id', $adminUser->id)->where('type', 'student.book_request')->exists())->toBeTrue();
});

test('staff mobile approval creates one student request notification', function () {
    Queue::fake();

    $staffUser = makeMobileNotificationUser('staff');
    makeMobileNotificationStaff($staffUser);
    makeMobileNotificationUser('admin');
    $studentUser = makeMobileNotificationUser('student');
    $student = makeMobileNotificationStudent($studentUser);
    $book = makeMobileNotificationBook();

    $bookRequest = BookRequest::create([
        'student_id' => $student->id,
        'book_id' => $book->id,
        'request_date' => now(),
        'status' => 'pending',
    ]);

    Sanctum::actingAs($staffUser);

    $this->postJson("/api/staff/book-requests/{$bookRequest->id}/approve")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Notification::query()
        ->where('user_id', $studentUser->id)
        ->where('related_model', 'BookRequest')
        ->where('related_id', $bookRequest->id)
        ->where('type', 'request.approved')
        ->count())->toBe(1);
});

test('shared mobile issue API creates one issued-book notification', function () {
    Queue::fake();

    $staffUser = makeMobileNotificationUser('staff');
    makeMobileNotificationStaff($staffUser);
    $studentUser = makeMobileNotificationUser('student');
    $student = makeMobileNotificationStudent($studentUser);
    $book = makeMobileNotificationBook();

    Sanctum::actingAs($staffUser);

    $this->postJson('/api/issues', [
        'student_id' => $student->id,
        'book_ids' => [$book->id],
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
    ])->assertCreated();

    $issue = IssuedBook::query()->where('student_id', $student->id)->where('book_id', $book->id)->firstOrFail();

    expect(Notification::query()
        ->where('user_id', $studentUser->id)
        ->where('related_model', 'IssuedBook')
        ->where('related_id', $issue->id)
        ->where('type', 'book.issued')
        ->count())->toBe(1);
});

test('staff mobile return API creates one return notification with fine payload', function () {
    Queue::fake();

    $staffUser = makeMobileNotificationUser('staff');
    makeMobileNotificationStaff($staffUser);
    $studentUser = makeMobileNotificationUser('student');
    $student = makeMobileNotificationStudent($studentUser);
    $book = makeMobileNotificationBook(['available_copies' => 1]);

    $issue = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $staffUser->id,
        'issue_date' => now()->subDays(15)->toDateString(),
        'due_date' => now()->subDays(5)->toDateString(),
        'status' => 'issued',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);

    Sanctum::actingAs($staffUser);

    $this->postJson("/api/staff/issues/{$issue->id}/return", [
        'condition' => 'good',
        'return_date' => now()->toDateString(),
    ])->assertOk();

    $notification = Notification::query()
        ->where('user_id', $studentUser->id)
        ->where('related_model', 'IssuedBook')
        ->where('related_id', $issue->id)
        ->where('type', 'fine.created')
        ->first();

    expect($notification)->not->toBeNull();
    expect(Notification::query()->where('related_model', 'IssuedBook')->where('related_id', $issue->id)->count())->toBe(1);
    expect((float) ($notification->data['fine_amount'] ?? 0))->toBeGreaterThan(0);
});

test('mobile profile and password updates create account notifications', function () {
    $staffUser = makeMobileNotificationUser('staff');
    makeMobileNotificationStaff($staffUser);

    Sanctum::actingAs($staffUser);

    $this->putJson('/api/profile', [
        'name' => 'Updated Notification Staff',
        'phone' => '9811111111',
        'address' => 'Updated Address',
    ])->assertOk();

    $this->postJson('/api/profile/password', [
        'current_password' => 'Password!123',
        'password' => 'ChangedPassword!123',
        'password_confirmation' => 'ChangedPassword!123',
    ])->assertOk();

    expect(Notification::query()->where('user_id', $staffUser->id)->where('type', 'account.profile_updated')->count())->toBe(1);
    expect(Notification::query()->where('user_id', $staffUser->id)->where('type', 'account.password_changed')->count())->toBe(1);
});
