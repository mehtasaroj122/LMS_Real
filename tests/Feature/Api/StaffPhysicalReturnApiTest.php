<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Notification;
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
    $portrait = 'https://randomuser.me/api/portraits/men/0.jpg';
    $student->user->update(['profile_photo' => $portrait]);
    [, $activeCopy, $activeIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $returnedCopy, $returnedIssue] = makePhysicalReturnApiIssue($student, $staff);
    app(PhysicalBookCopyService::class)->return($returnedCopy->accession_number, 'good', now(), null, $staff);

    Sanctum::actingAs($staff);

    foreach ([$student->user->name, $student->student_id, $student->user->email] as $search) {
        $this->getJson('/api/staff/returns/students/search?query='.urlencode($search))
            ->assertOk()
            ->assertJsonPath('data.0.id', $student->id)
            ->assertJsonPath('data.0.profile_photo_url', $portrait);
    }

    $this->getJson("/api/staff/return-books/student/{$student->id}")
        ->assertOk()
        ->assertJsonPath('data.student.profile_photo_url', $portrait)
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
    $this->postJson('/api/staff/return-books/bulk', [])->assertForbidden();
});

function borrowerReturnItem(BookCopy $copy, IssuedBook $issue, string $condition = 'good'): array
{
    return ['issue_id' => $issue->id, 'book_copy_id' => $copy->id,
        'accession_number' => $copy->accession_number, 'return_condition' => $condition];
}

test('accession identifies a borrower whose return list includes all eligible books only', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, , $secondIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $lostCopy, $lostIssue] = makePhysicalReturnApiIssue($student, $staff);
    $lostCopy->update(['status' => 'lost']);
    IssuedBook::whereKey($lostIssue->id)->update(['status' => 'lost']);
    [, , $otherIssue] = makePhysicalReturnApiIssue(makePhysicalReturnApiStudent(), $staff);
    Sanctum::actingAs($staff);

    $lookup = $this->getJson("/api/staff/return-books/accession/{$firstCopy->accession_number}")
        ->assertOk()->assertJsonPath('data.borrower.active_issues_count', 2)
        ->assertJsonPath('data.issue.can_return', true);
    $borrowerId = $lookup->json('data.borrower.id');
    $this->getJson("/api/staff/return-books/student/{$borrowerId}")
        ->assertOk()->assertJsonCount(2, 'data.active_issues')
        ->assertJsonFragment(['issue_id' => $firstIssue->id])
        ->assertJsonFragment(['issue_id' => $secondIssue->id])
        ->assertJsonMissing(['issue_id' => $lostIssue->id])
        ->assertJsonMissing(['issue_id' => $otherIssue->id]);
});

test('borrower bulk returns keep separate fines conditions notifications and refreshed pending amount', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $secondCopy, $secondIssue] = makePhysicalReturnApiIssue($student, $staff, [], ['due_date' => today()->addDays(7)]);
    Sanctum::actingAs($staff);
    $payload = ['student_id' => $student->id, 'items' => [
        borrowerReturnItem($firstCopy, $firstIssue, 'fair'), borrowerReturnItem($secondCopy, $secondIssue, 'damaged'),
    ]];
    $this->postJson('/api/staff/returns/preview', $payload)->assertOk()
        ->assertJsonPath('data.total_fine', 315)
        ->assertJsonPath('data.items.0.overdue_days', 5)
        ->assertJsonPath('data.items.0.book_total', 65)
        ->assertJsonPath('data.items.1.overdue_days', 0)
        ->assertJsonPath('data.items.1.book_total', 250);
    $this->postJson('/api/staff/return-books/bulk', $payload)->assertOk()
        ->assertJsonPath('success', true)->assertJsonPath('data.returned_count', 2)
        ->assertJsonPath('data.failed_count', 0)->assertJsonPath('data.total_fine', 315)
        ->assertJsonPath('data.results.0.fine.total_fine', 65)
        ->assertJsonPath('data.results.1.fine.total_fine', 250);
    expect($firstCopy->fresh()->condition)->toBe('fair')->and($secondCopy->fresh()->status)->toBe('damaged');
    $this->assertDatabaseHas('fines', ['issued_book_id' => $firstIssue->id, 'amount' => 65]);
    $this->assertDatabaseHas('fines', ['issued_book_id' => $secondIssue->id, 'amount' => 250]);
    expect(ActivityLog::where('action', 'book_returned')->count())->toBe(2);
    expect(Notification::where('related_model', 'IssuedBook')->where('related_id', $firstIssue->id)->count())->toBe(1);
    expect(Notification::where('related_model', 'IssuedBook')->where('related_id', $secondIssue->id)->count())->toBe(1);
    $this->getJson("/api/staff/return-books/student/{$student->id}")->assertOk()
        ->assertJsonCount(0, 'data.active_issues')->assertJsonPath('data.student.active_issues_count', 0)
        ->assertJsonPath('data.student.pending_fine', 315);
});

test('a return between preview and submit fails only that item and creates no duplicate fine', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $staleCopy, $staleIssue] = makePhysicalReturnApiIssue($student, $staff);
    Sanctum::actingAs($staff);
    $payload = ['student_id' => $student->id, 'items' => [borrowerReturnItem($firstCopy, $firstIssue), borrowerReturnItem($staleCopy, $staleIssue)]];
    $this->postJson('/api/staff/returns/preview', $payload)->assertOk()->assertJsonPath('data.selected_count', 2);
    app(PhysicalBookCopyService::class)->returnIssueById($staleIssue->id, $staleCopy->id, 'good', now(), null, makePhysicalReturnApiUser());
    $this->postJson('/api/staff/return-books/bulk', $payload)->assertOk()
        ->assertJsonPath('success', false)->assertJsonPath('data.returned_count', 1)
        ->assertJsonPath('data.failed_count', 1)->assertJsonPath('data.failed_items.0.issue_id', $staleIssue->id)
        ->assertJsonPath('data.failed_items.0.code', 'issue_already_returned')
        ->assertJsonPath('data.failed_items.0.can_return', false);
    expect($firstIssue->fresh()->status)->toBe('returned')
        ->and(Fine::where('issued_book_id', $staleIssue->id)->count())->toBe(1);
    expect(ActivityLog::where('action', 'book_returned')->count())->toBe(2);
});

test('borrower bulk validates ownership accession and physical issue pairing per item', function (string $change, string $code) {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $firstCopy, $firstIssue] = makePhysicalReturnApiIssue($student, $staff);
    [, $secondCopy, $secondIssue] = makePhysicalReturnApiIssue($student, $staff);
    $invalid = borrowerReturnItem($secondCopy, $secondIssue);
    if ($change === 'borrower') {
        $secondIssue->update(['student_id' => makePhysicalReturnApiStudent()->id]);
    } elseif ($change === 'accession') {
        $invalid['accession_number'] = 'WRONG-ACCESSION';
    } else {
        $invalid['book_copy_id'] = app(PhysicalBookCopyService::class)->createCopies(makePhysicalReturnApiBook(), 1)->first()->id;
    }
    Sanctum::actingAs($staff);
    $payload = ['student_id' => $student->id, 'items' => [borrowerReturnItem($firstCopy, $firstIssue), $invalid]];
    $this->postJson('/api/staff/returns/preview', $payload)->assertOk()
        ->assertJsonPath('data.selected_count', 1)->assertJsonPath('data.failed_items.0.code', $code);
    $this->postJson('/api/staff/return-books/bulk', $payload)->assertOk()
        ->assertJsonPath('data.returned_count', 1)->assertJsonPath('data.failed_items.0.code', $code);
    expect($firstIssue->fresh()->status)->toBe('returned')->and($secondIssue->fresh()->return_date)->toBeNull();
})->with([['borrower', 'borrower_mismatch'], ['accession', 'accession_mismatch'], ['copy', 'issue_copy_mismatch']]);

test('inactive lost cancelled and unavailable records cannot be selected previewed or returned', function (string $status, string $copyStatus) {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);
    IssuedBook::whereKey($issue->id)->update(['status' => $status]);
    $copy->update(['status' => $copyStatus]);
    Sanctum::actingAs($staff);
    $this->getJson("/api/staff/return-books/student/{$student->id}")->assertOk()->assertJsonCount(0, 'data.active_issues');
    $this->getJson("/api/staff/return-books/accession/{$copy->accession_number}")->assertUnprocessable();
    $payload = ['student_id' => $student->id, 'items' => [borrowerReturnItem($copy, $issue)]];
    $this->postJson('/api/staff/returns/preview', $payload)->assertOk()->assertJsonPath('data.selected_count', 0);
    $this->postJson('/api/staff/return-books/bulk', $payload)->assertOk()
        ->assertJsonPath('data.returned_count', 0)->assertJsonPath('data.failed_count', 1);
    expect($issue->fresh()->return_date)->toBeNull();
})->with([['lost', 'lost'], ['cancelled', 'issued'], ['inactive', 'issued'], ['issued', 'available']]);

test('missing item does not block a valid borrower return and repeated submit returns no duplicates', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);
    Sanctum::actingAs($staff);
    $payload = ['student_id' => $student->id, 'items' => [borrowerReturnItem($copy, $issue),
        ['issue_id' => 999999, 'book_copy_id' => 999999, 'accession_number' => 'MISSING', 'return_condition' => 'good']]];
    $this->postJson('/api/staff/return-books/bulk', $payload)->assertOk()
        ->assertJsonPath('data.returned_count', 1)->assertJsonPath('data.failed_count', 1);
    $this->postJson('/api/staff/return-books/bulk', $payload)->assertOk()
        ->assertJsonPath('data.returned_count', 0)->assertJsonPath('data.failed_count', 2);
    expect(Fine::where('issued_book_id', $issue->id)->count())->toBe(1);
});

test('ambiguous active issue and invalid dates are not returnable', function (string $invalid) {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);
    if ($invalid === 'duplicate') {
        $duplicate = $issue->replicate();
        $duplicate->save();
    } elseif ($invalid === 'future') {
        IssuedBook::whereKey($issue->id)->update(['issue_date' => today()->addDays(1)]);
    } else {
        IssuedBook::whereKey($issue->id)->update(['due_date' => today()->subDays(30)]);
    }
    Sanctum::actingAs($staff);
    $this->getJson("/api/staff/return-books/student/{$student->id}")->assertOk()->assertJsonCount(0, 'data.active_issues');
    $this->postJson('/api/staff/return-books/bulk', [
        'student_id' => $student->id, 'items' => [borrowerReturnItem($copy, $issue)],
    ])->assertOk()->assertJsonPath('data.returned_count', 0)->assertJsonPath('data.failed_count', 1);
    expect($copy->fresh()->status)->toBe('issued')->and($issue->fresh()->return_date)->toBeNull();
})->with(['duplicate', 'future', 'invalid_due']);

test('partial return rejects duplicate selections and requires borrower accession and authorized staff', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);
    $item = borrowerReturnItem($copy, $issue);
    $this->postJson('/api/staff/return-books/bulk', ['student_id' => $student->id, 'items' => [$item]])->assertUnauthorized();
    Sanctum::actingAs($staff);
    $this->postJson('/api/staff/return-books/bulk', ['student_id' => $student->id, 'items' => [$item, $item]])->assertUnprocessable();
    unset($item['accession_number']);
    $this->postJson('/api/staff/return-books/bulk', ['student_id' => $student->id, 'items' => [$item]])->assertUnprocessable();
    expect($issue->fresh()->return_date)->toBeNull();
});

test('cancelled historical issue never hides or replaces the current active issue of the physical copy', function () {
    $staff = makePhysicalReturnApiUser();
    $student = makePhysicalReturnApiStudent();
    [, $copy, $issue] = makePhysicalReturnApiIssue($student, $staff);
    $cancelled = $issue->replicate();
    $cancelled->status = 'cancelled';
    $cancelled->save();
    Sanctum::actingAs($staff);
    $this->getJson("/api/staff/return-books/accession/{$copy->accession_number}")->assertOk()->assertJsonPath('data.issue.issue_id', $issue->id);
    $this->postJson('/api/staff/return-books/bulk', [
        'student_id' => $student->id, 'items' => [borrowerReturnItem($copy, $issue)],
    ])->assertOk()->assertJsonPath('data.returned_count', 1);
    expect($cancelled->fresh()->return_date)->toBeNull();
});
