<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Services\PhysicalBookCopyService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function makePhysicalReturnApiUser(string $role = 'staff', array $overrides = []): User
{
    $key = Str::lower(Str::random(10));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role).' Return API '.$key,
        'email' => "{$role}-return-api-{$key}@example.com",
        'phone' => '98'.random_int(10000000, 99999999),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makePhysicalReturnApiStudent(array $overrides = []): Student
{
    $key = Str::lower(Str::random(10));
    $user = makePhysicalReturnApiUser('student', [
        'name' => $overrides['name'] ?? 'Return Student '.$key,
        'email' => $overrides['email'] ?? "return-student-{$key}@example.com",
    ]);
    $department = Department::create([
        'name' => 'Return Department '.$key,
        'code' => Str::upper(substr($key, 0, 5)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => $overrides['student_id'] ?? 'RET-'.Str::upper(substr($key, 0, 6)),
        'roll_no' => $overrides['roll_no'] ?? 'ROLL-'.Str::upper(substr($key, 0, 6)),
        'batch' => '2026',
        'semester' => '1',
    ]);
}

function makePhysicalReturnApiBook(array $overrides = []): Book
{
    $key = Str::lower(Str::random(10));
    $category = Category::create([
        'name' => 'Return Category '.$key,
        'description' => 'Return API test category',
    ]);

    return Book::create(array_merge([
        'category_id' => $category->id,
        'title' => 'Return Workflow '.$key,
        'author' => 'Return Author '.$key,
        'publisher' => 'Return Publisher',
        'isbn' => 'RET-ISBN-'.Str::upper($key),
        'total_copies' => 1,
        'available_copies' => 1,
        'condition' => 'good',
        'description' => 'Physical return API book',
        'shelf_no' => 'RET-01',
        'status' => 'available',
    ], $overrides));
}

function makePhysicalReturnApiIssue(
    Student $student,
    User $staff,
    array $copyOverrides = [],
    array $issueOverrides = []
): array {
    $book = makePhysicalReturnApiBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1, $copyOverrides)->first();
    $issue = app(PhysicalBookCopyService::class)->issue($student, $copy->accession_number, $staff, array_merge([
        'issue_date' => today()->subDays(20),
        'due_date' => today()->subDays(5),
    ], $issueOverrides));

    return [$book->fresh(), $copy->fresh(), $issue->fresh(['student.user', 'student.department', 'book', 'bookCopy'])];
}

beforeEach(function () {
    FineSetting::query()->delete();
    FineSetting::create(array_merge(FineSetting::defaults(), [
        'per_day_fine' => 5,
        'grace_period_days' => 2,
        'max_fine_amount' => 500,
        'fair_condition_penalty' => 50,
        'damaged_book_penalty' => 250,
        'lost_book_penalty' => 1000,
        'is_active' => true,
    ]));
});

test('staff can search borrowers and retrieve only their active physical issues', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $activeCopy, $activeIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $returnedCopy, $returnedIssue] = makePhysicalReturnApiIssue($student, $staff);
    app(PhysicalBookCopyService::class)->return($returnedCopy->accession_number, 'good', now(), null, $staff);

    Sanctum::actingAs($staff);

    foreach ([$student->user->name, $student->student_id, $student->user->email] as $search) {
        $this->getJson('/api/staff/returns/students/search?query='.urlencode($search))
            ->assertOk()
            ->assertJsonPath('data.0.id', $student->id);
    }

    $this->getJson("/api/staff/return-books/student/{$student->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data.active_issues')
        ->assertJsonPath('data.active_issues.0.issue_id', $activeIssue->id)
        ->assertJsonPath('data.active_issues.0.book_copy_id', $activeCopy->id)
        ->assertJsonPath('data.active_issues.0.accession_number', $activeCopy->accession_number)
        ->assertJsonPath('data.active_issues.0.book_type', 'borrowing')
        ->assertJsonPath('data.active_issues.0.student.id', $student->id)
        ->assertJsonMissing(['issue_id' => $returnedIssue->id]);
});

test('staff accession lookup returns the physical copy borrower and active issue', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [$book, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);

    Fine::create([
        'issued_book_id' => $issue->id,
        'student_id' => $student->id,
        'amount' => 15,
        'days_late' => 5,
        'status' => 'pending',
        'remarks' => 'Current overdue fine',
    ]);

    Sanctum::actingAs($staff);

    $this->getJson("/api/staff/return-books/accession/{$copy->accession_number}")
        ->assertOk()
        ->assertJsonPath('data.book.title', $book->title)
        ->assertJsonPath('data.book.isbn', $book->isbn)
        ->assertJsonPath('data.physical_copy.book_copy_id', $copy->id)
        ->assertJsonPath('data.physical_copy.accession_number', $copy->accession_number)
        ->assertJsonPath('data.borrower.id', $student->id)
        ->assertJsonPath('data.issue.issue_id', $issue->id)
        ->assertJsonPath('data.issue.current_fine.amount', 15);

    $this->getJson('/api/staff/return-books/accession/DOES-NOT-EXIST')
        ->assertNotFound()
        ->assertJsonPath('message', 'Accession number not found.');

    $available = app(PhysicalBookCopyService::class)->createCopies(makePhysicalReturnApiBook(), 1)->first();
    $this->getJson("/api/staff/return-books/accession/{$available->accession_number}")
        ->assertUnprocessable()
        ->assertJsonPath('message', 'This book copy is not currently issued.');
});

test('fine preview uses server rules and never trusts a client fine amount', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/return-books/calculate-fine', [
        'issue_id' => $issue->id,
        'book_copy_id' => $copy->id,
        'return_condition' => 'fair',
        'fine_amount' => 1,
    ])->assertOk()
        ->assertJsonPath('data.overdue_days', 5)
        ->assertJsonPath('data.grace_days', 2)
        ->assertJsonPath('data.chargeable_overdue_days', 3)
        ->assertJsonPath('data.overdue_fine', 15)
        ->assertJsonPath('data.condition_fine', 50)
        ->assertJsonPath('data.total_fine', 65)
        ->assertJsonPath('data.currency', 'NPR');
});

test('staff returns one physical copy and prevents a second return', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);
    $accession = $copy->accession_number;

    Sanctum::actingAs($staff);

    $payload = [
        'issue_id' => $issue->id,
        'book_copy_id' => $copy->id,
        'return_condition' => 'fair',
        'fine_amount' => 1,
    ];

    $this->postJson('/api/staff/return-books', $payload)
        ->assertOk()
        ->assertJsonPath('data.issue.book_copy_id', $copy->id)
        ->assertJsonPath('data.issue.accession_number', $accession)
        ->assertJsonPath('data.issue.status', 'returned')
        ->assertJsonPath('data.fine.total_fine', 65);

    expect($issue->fresh()->return_date)->not->toBeNull()
        ->and($issue->fresh()->condition)->toBe('fair')
        ->and((float) $issue->fresh()->fine_amount)->toBe(65.0)
        ->and($copy->fresh()->status)->toBe('available')
        ->and($copy->fresh()->condition)->toBe('fair')
        ->and($copy->fresh()->accession_number)->toBe($accession);

    $this->assertDatabaseHas('fines', [
        'issued_book_id' => $issue->id,
        'amount' => 65,
        'status' => 'pending',
    ]);

    $this->postJson('/api/staff/return-books', $payload)
        ->assertConflict()
        ->assertJsonPath('message', 'This book has already been returned.');
});

test('single return rejects an issue and physical copy mismatch', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $secondCopy] = makePhysicalReturnApiIssue($student, $staff);

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/return-books', [
        'issue_id' => $firstIssue->id,
        'book_copy_id' => $secondCopy->id,
        'return_condition' => 'good',
    ])->assertUnprocessable()
        ->assertJsonPath('code', 'issue_copy_mismatch');

    expect($firstCopy->fresh()->status)->toBe('issued')
        ->and($firstIssue->fresh()->return_date)->toBeNull();
});

test('bulk physical return is atomic and supports per-copy conditions', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $secondCopy, $secondIssue] = makePhysicalReturnApiIssue($student, $staff);

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/return-books/bulk', [
        'items' => [
            ['issue_id' => $firstIssue->id, 'book_copy_id' => $firstCopy->id, 'return_condition' => 'good'],
            ['issue_id' => $secondIssue->id, 'book_copy_id' => $secondCopy->id, 'return_condition' => 'damaged'],
        ],
    ])->assertOk()
        ->assertJsonPath('data.returned_count', 2);

    expect($firstCopy->fresh()->status)->toBe('available')
        ->and($secondCopy->fresh()->status)->toBe('damaged')
        ->and($firstIssue->fresh()->status)->toBe('returned')
        ->and($secondIssue->fresh()->status)->toBe('returned');
});

test('bulk physical return rolls back every item when one issue-copy pair is invalid', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $secondCopy, $secondIssue] = makePhysicalReturnApiIssue($student, $staff);
    $unrelatedCopy = app(PhysicalBookCopyService::class)->createCopies(makePhysicalReturnApiBook(), 1)->first();

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/return-books/bulk', [
        'items' => [
            ['issue_id' => $firstIssue->id, 'book_copy_id' => $firstCopy->id, 'return_condition' => 'good'],
            ['issue_id' => $secondIssue->id, 'book_copy_id' => $unrelatedCopy->id, 'return_condition' => 'good'],
        ],
    ])->assertUnprocessable();

    expect($firstCopy->fresh()->status)->toBe('issued')
        ->and($secondCopy->fresh()->status)->toBe('issued')
        ->and($firstIssue->fresh()->return_date)->toBeNull()
        ->and($secondIssue->fresh()->return_date)->toBeNull();
});

test('return preserves a reference copy accession and type', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    $book = makePhysicalReturnApiBook();
    $copy = BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'REF-RETURN-001',
        'book_type' => 'reference',
        'status' => 'issued',
        'condition' => 'good',
        'shelf_location' => 'REF-01',
    ]);
    $issue = IssuedBook::create([
        'book_id' => $book->id,
        'book_copy_id' => $copy->id,
        'student_id' => $student->id,
        'issued_by' => $staff->id,
        'issue_date' => today()->subDays(2),
        'due_date' => today()->addDays(5),
        'status' => 'issued',
    ]);

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/return-books', [
        'issue_id' => $issue->id,
        'book_copy_id' => $copy->id,
        'return_condition' => 'good',
    ])->assertOk();

    expect($copy->fresh()->accession_number)->toBe('REF-RETURN-001')
        ->and($copy->fresh()->book_type)->toBe('reference')
        ->and($copy->fresh()->status)->toBe('available');
});

test('physical return APIs require a staff or admin Sanctum user', function () {
    $studentUser = makePhysicalReturnApiUser('student');

    $this->getJson('/api/staff/return-books/accession/ACC-000001')->assertUnauthorized();

    Sanctum::actingAs($studentUser);

    $this->getJson('/api/staff/return-books/student/1')->assertForbidden();
    $this->postJson('/api/staff/return-books', [])->assertForbidden();
    $this->postJson('/api/staff/return-books/calculate-fine', [])->assertForbidden();
});
