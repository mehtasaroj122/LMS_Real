<?php

use App\Exceptions\PhysicalCopyException;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\StudentPrivilege;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PhysicalBookCopyService;
use App\Services\StudentIssuePrivilegeService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

function makePhysicalIssueApiUser(string $role = 'staff', array $overrides = []): User
{
    $key = Str::lower(Str::random(10));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role).' Physical API '.$key,
        'email' => "{$role}-physical-api-{$key}@example.com",
        'phone' => '98'.random_int(10000000, 99999999),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makePhysicalIssueApiStudent(array $userOverrides = []): Student
{
    $key = Str::lower(Str::random(10));
    $user = makePhysicalIssueApiUser('student', array_merge([
        'name' => 'Physical Student '.$key,
        'email' => "physical-student-{$key}@example.com",
    ], $userOverrides));
    $department = Department::create([
        'name' => 'Physical Department '.$key,
        'code' => Str::upper(substr($key, 0, 5)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'PHY-'.Str::upper(substr($key, 0, 6)),
        'roll_no' => 'ROLL-'.Str::upper(substr($key, 0, 6)),
        'batch' => '2026',
        'semester' => '1',
        'address' => 'Private student address',
    ]);
}

function makePhysicalIssueApiBook(array $overrides = []): Book
{
    $key = Str::lower(Str::random(10));
    $category = Category::create([
        'name' => 'Physical Category '.$key,
        'description' => 'Physical API test category',
    ]);

    return Book::create(array_merge([
        'category_id' => $category->id,
        'title' => 'Clean Architecture '.$key,
        'author' => 'Robert Martin '.$key,
        'publisher' => 'Physical Publisher',
        'isbn' => '978-'.random_int(100000000, 999999999),
        'total_copies' => 1,
        'available_copies' => 1,
        'condition' => 'good',
        'description' => 'Physical API book',
        'shelf_no' => 'PHY-01',
        'status' => 'available',
    ], $overrides));
}

test('staff issue-book search returns only eligible physical copies and supports all required fields', function () {
    $staff = makePhysicalIssueApiUser();
    $book = makePhysicalIssueApiBook();
    $availableCopy = BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000101',
        'book_type' => 'borrowing',
        'status' => 'available',
        'shelf_location' => 'A-01',
        'condition' => 'good',
    ]);
    BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000102',
        'book_type' => 'reference',
        'status' => 'available',
        'shelf_location' => 'REF-01',
        'condition' => 'good',
    ]);
    $issuedCopy = BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000103',
        'book_type' => 'borrowing',
        'status' => 'available',
        'shelf_location' => 'A-01',
        'condition' => 'good',
    ]);
    app(PhysicalBookCopyService::class)->issue(makePhysicalIssueApiStudent(), $issuedCopy->accession_number, $staff);
    $incidentalBook = makePhysicalIssueApiBook([
        'title' => 'Unrelated title',
        'author' => 'Unrelated author',
        'isbn' => 'UNRELATED-ISBN',
    ]);
    BookCopy::create([
        'book_id' => $incidentalBook->id,
        'accession_number' => 'OLD-ACC-000101',
        'book_type' => 'borrowing',
        'status' => 'available',
        'shelf_location' => 'ARCHIVE-01',
        'condition' => 'good',
    ]);

    Sanctum::actingAs($staff);

    $this->getJson('/api/staff/issue-books?search=ACC-000101')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.book_id', $book->id)
        ->assertJsonPath('data.0.book_copy_id', $availableCopy->id)
        ->assertJsonPath('data.0.title', $book->title)
        ->assertJsonPath('data.0.isbn', $book->isbn)
        ->assertJsonPath('data.0.author', $book->author)
        ->assertJsonPath('data.0.accession_number', 'ACC-000101')
        ->assertJsonPath('data.0.copy_type', 'borrowing')
        ->assertJsonPath('data.0.status', 'available')
        ->assertJsonPath('data.0.shelf_location', 'A-01')
        ->assertJsonPath('data.0.condition', 'good');

    foreach (['clean', $book->isbn, $book->author] as $search) {
        $this->getJson('/api/staff/issue-books?search='.urlencode($search))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.book_copy_id', $availableCopy->id);
    }
});

test('staff student search returns issue eligibility without private profile fields', function () {
    $staff = makePhysicalIssueApiUser();
    $student = makePhysicalIssueApiStudent();

    Sanctum::actingAs($staff);

    $this->getJson('/api/staff/students/search?query='.urlencode($student->user->name))
        ->assertOk()
        ->assertJsonPath('data.0.id', $student->id)
        ->assertJsonPath('data.0.student_id', $student->student_id)
        ->assertJsonPath('data.0.can_issue', true)
        ->assertJsonMissingPath('data.0.address')
        ->assertJsonMissingPath('data.0.phone');
});

test('staff can issue the selected physical copy by id inside the existing workflow', function () {
    $staff = makePhysicalIssueApiUser();
    $student = makePhysicalIssueApiStudent();
    $book = makePhysicalIssueApiBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_id' => $book->id,
        'book_copy_id' => $copy->id,
        'issue_date' => '2026-10-07',
        'due_date' => '2026-10-21',
        'remarks' => 'Issued from Android staff app.',
    ])->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.issue.book_id', $book->id)
        ->assertJsonPath('data.issue.book_copy_id', $copy->id)
        ->assertJsonPath('data.issue.accession_number', $copy->accession_number)
        ->assertJsonPath('data.issue.issue_date', '2026-10-07')
        ->assertJsonPath('data.issue.due_date', '2026-10-21');

    expect($copy->fresh()->status)->toBe('issued')
        ->and($book->fresh()->available_copies)->toBe(0);

    $this->assertDatabaseHas('issued_books', [
        'student_id' => $student->id,
        'book_id' => $book->id,
        'book_copy_id' => $copy->id,
        'issued_by' => $staff->id,
        'issue_date' => '2026-10-07 00:00:00',
        'due_date' => '2026-10-21 00:00:00',
        'status' => 'issued',
    ]);

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_copy_id' => $copy->id,
    ])->assertStatus(409)
        ->assertJsonPath('message', 'This book copy is already issued.');
});

test('staff issue endpoint prevents reference copies and mismatched book selections', function () {
    $staff = makePhysicalIssueApiUser();
    $student = makePhysicalIssueApiStudent();
    $book = makePhysicalIssueApiBook();
    $otherBook = makePhysicalIssueApiBook(['title' => 'Different title']);
    $referenceCopy = app(PhysicalBookCopyService::class)
        ->createCopies($book, 1, ['book_type' => 'reference'])
        ->first();

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_copy_id' => $referenceCopy->id,
    ])->assertUnprocessable()
        ->assertJsonPath('code', 'reference_only');

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_id' => $otherBook->id,
        'book_copy_id' => $referenceCopy->id,
    ])->assertUnprocessable()
        ->assertJsonPath('message', 'The selected book copy does not belong to this book.')
        ->assertJsonPath('code', 'book_copy_mismatch');
});

test('staff issue endpoint rejects students who are not eligible to borrow', function () {
    $staff = makePhysicalIssueApiUser();
    $student = makePhysicalIssueApiStudent(['status' => 'inactive']);
    $copy = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_copy_id' => $copy->id,
    ])->assertUnprocessable()
        ->assertJsonPath('code', 'student_not_eligible');

    expect($copy->fresh()->status)->toBe('available');
    $this->assertDatabaseMissing('issued_books', ['book_copy_id' => $copy->id]);
});

test('staff issue transaction rolls back the issue and copy status when a downstream action fails', function () {
    $staff = makePhysicalIssueApiUser();
    $student = makePhysicalIssueApiStudent();
    $book = makePhysicalIssueApiBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();

    $this->mock(NotificationService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('notifyBookIssued')
            ->once()
            ->andThrow(new RuntimeException('Simulated notification failure.'));
    });

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_copy_id' => $copy->id,
    ])->assertInternalServerError();

    expect($copy->fresh()->status)->toBe('available')
        ->and($book->fresh()->available_copies)->toBe(1);
    $this->assertDatabaseMissing('issued_books', ['book_copy_id' => $copy->id]);
});

test('staff physical issue APIs require authentication and a staff or admin role', function () {
    $studentUser = makePhysicalIssueApiUser('student');

    $this->getJson('/api/staff/issue-books')->assertUnauthorized();

    Sanctum::actingAs($studentUser);

    $this->getJson('/api/staff/issue-books')->assertForbidden();
    $this->postJson('/api/staff/issues', [])->assertForbidden();
});

test('staff issue endpoint does not accept a title id without a physical copy identifier', function () {
    $staff = makePhysicalIssueApiUser();
    $student = makePhysicalIssueApiStudent();
    $book = makePhysicalIssueApiBook();

    Sanctum::actingAs($staff);

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'book_id' => $book->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('book_copy_id');

    $this->assertDatabaseMissing('issued_books', [
        'student_id' => $student->id,
        'book_id' => $book->id,
    ]);
});

test('batch issue rejects different accessions for the same book before any write or notification', function () {
    $student = makePhysicalIssueApiStudent();
    $book = makePhysicalIssueApiBook();
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 2);
    $this->mock(NotificationService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('notifyBookIssued'));
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'accession_numbers' => $copies->pluck('accession_number')->all(),
    ])->assertStatus(409)->assertJsonPath('invalid_copies.0.code', 'duplicate_book_id');
    $this->assertDatabaseCount('issued_books', 0);
    expect($book->fresh()->available_copies)->toBe(2);
});

test('batch accessions are normalized before distinct validation instead of silently deduplicated', function () {
    $student = makePhysicalIssueApiStudent();
    $copy = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->postJson('/api/staff/issues', [
        'student_id' => $student->id,
        'accession_numbers' => [$copy->accession_number, ' '.strtolower($copy->accession_number).' '],
    ])->assertUnprocessable()->assertJsonValidationErrors('accession_numbers.0');
    $this->assertDatabaseCount('issued_books', 0);
});

test('different book ids with the same title are issued together and physical preview is compatible', function () {
    $student = makePhysicalIssueApiStudent();
    $first = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(['title' => 'Same title']), 1)->first();
    $second = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(['title' => 'Same title']), 1)->first();
    $body = ['student_id' => $student->id, 'accession_numbers' => [$first->accession_number, $second->accession_number]];
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->postJson('/api/staff/issues/preview', $body)->assertOk()
        ->assertJsonPath('success', true)->assertJsonCount(2, 'data.selected_copies')
        ->assertJsonPath('data.selected_copies.0.can_select', true);
    $this->postJson('/api/staff/issues', $body)->assertCreated()->assertJsonPath('data.issued_count', 2);
    $this->assertDatabaseCount('issued_books', 2);
    expect($first->fresh()->status)->toBe('issued')->and($second->fresh()->status)->toBe('issued');
    $this->postJson('/api/staff/issues/preview', ['student_id' => $student->id, 'book_ids' => [$first->book_id]])
        ->assertUnprocessable(); // Legacy title preview remains supported.
});

test('another active copy of the same book is blocked in context search preview and final issue', function () {
    $student = makePhysicalIssueApiStudent();
    $copies = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 2);
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->postJson('/api/staff/issues', ['student_id' => $student->id, 'book_copy_id' => $copies[0]->id])->assertCreated();
    $this->getJson('/api/staff/students/'.$student->id.'/issue-privileges')
        ->assertOk()->assertJsonPath('data.privileges.active_book_ids.0', $copies[0]->book_id)
        ->assertJsonPath('data.privileges.one_active_copy_per_book', true);
    $this->getJson('/api/staff/issue-books?include_unavailable=1&student_id='.$student->id.'&search='.$copies[1]->accession_number)
        ->assertOk()->assertJsonPath('data.0.can_select', false)
        ->assertJsonPath('data.0.eligibility_code', 'duplicate_student_issue');
    $body = ['student_id' => $student->id, 'accession_numbers' => [$copies[1]->accession_number]];
    $this->postJson('/api/staff/issues/preview', $body)->assertUnprocessable()
        ->assertJsonPath('data.invalid_copies.0.code', 'duplicate_student_issue');
    $this->postJson('/api/staff/issues', $body)->assertStatus(409)
        ->assertJsonPath('invalid_copies.0.code', 'duplicate_student_issue');
    $this->assertDatabaseCount('issued_books', 1);
});

test('ineligible physical copies remain visible with an authoritative reason and cannot be issued', function (array $attributes, string $code) {
    $student = makePhysicalIssueApiStudent();
    $copy = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    $copy->update($attributes);
    if ($code === 'copy_issued') {
        IssuedBook::create([
            'book_id' => $copy->book_id,
            'book_copy_id' => $copy->id,
            'student_id' => makePhysicalIssueApiStudent()->id,
            'issue_date' => today(),
            'due_date' => today()->addDays(14),
            'status' => 'issued',
        ]);
    }
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->getJson('/api/staff/issue-books?include_unavailable=1&search='.$copy->accession_number)
        ->assertOk()->assertJsonPath('data.0.can_select', false)->assertJsonPath('data.0.eligibility_code', $code);
    $body = ['student_id' => $student->id, 'accession_numbers' => [$copy->accession_number]];
    $this->postJson('/api/staff/issues/preview', $body)->assertUnprocessable()->assertJsonPath('data.invalid_copies.0.code', $code);
    $this->postJson('/api/staff/issues', $body)->assertStatus(409)->assertJsonPath('invalid_copies.0.code', $code);
    $this->assertDatabaseCount('issued_books', $code === 'copy_issued' ? 1 : 0);
})->with([
    'issued' => [['status' => 'issued'], 'copy_issued'],
    'lost' => [['status' => 'lost'], 'copy_lost'],
    'damaged status' => [['status' => 'damaged'], 'copy_damaged'],
    'damaged condition' => [['condition' => 'damaged'], 'copy_damaged'],
    'lost condition' => [['condition' => 'lost'], 'copy_lost'],
    'stale issued damaged copy' => [['status' => 'issued', 'condition' => 'damaged'], 'copy_damaged'],
    'stale issued lost copy' => [['status' => 'issued', 'condition' => 'lost'], 'copy_lost'],
    'reference' => [['book_type' => 'reference'], 'reference_only'],
    'maintenance' => [['status' => 'maintenance'], 'copy_maintenance'],
    'withdrawn' => [['status' => 'withdrawn'], 'copy_withdrawn'],
    'inactive' => [['status' => 'inactive'], 'copy_unavailable'],
]);

test('a copy becoming issued after preview rejects the entire final batch with accession errors', function () {
    $student = makePhysicalIssueApiStudent();
    $otherStudent = makePhysicalIssueApiStudent();
    $first = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    $second = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $body = ['student_id' => $student->id, 'accession_numbers' => [$first->accession_number, $second->accession_number]];
    $this->postJson('/api/staff/issues/preview', $body)->assertOk();
    $this->postJson('/api/staff/issues', ['student_id' => $otherStudent->id, 'book_copy_id' => $second->id])->assertCreated();
    $this->postJson('/api/staff/issues', $body)->assertStatus(409)
        ->assertJsonPath('invalid_copies.0.accession_number', $second->accession_number)
        ->assertJsonValidationErrors('accession_numbers.1');
    $this->assertDatabaseMissing('issued_books', ['student_id' => $student->id]);
    expect($first->fresh()->status)->toBe('available');
});

test('deleted and inconsistent available copies are rejected by current database state', function () {
    $student = makePhysicalIssueApiStudent();
    $copy = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->postJson('/api/staff/issues', ['student_id' => $student->id, 'book_copy_id' => $copy->id])->assertCreated();
    $copy->refresh()->update(['status' => 'available']);
    $this->getJson('/api/staff/issue-books?include_unavailable=1&search='.$copy->accession_number)
        ->assertOk()->assertJsonPath('data.0.can_select', false)->assertJsonPath('data.0.eligibility_code', 'copy_unavailable');
    $deleted = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    $accession = $deleted->accession_number;
    $deleted->delete();
    $this->postJson('/api/staff/issues', ['student_id' => $student->id, 'accession_numbers' => [$accession]])
        ->assertStatus(409)->assertJsonPath('invalid_copies.0.code', 'accession_not_found');
});

test('final batch respects remaining capacity account status and suspended borrowing', function () {
    $student = makePhysicalIssueApiStudent();
    StudentPrivilege::create(['student_id' => $student->id, 'max_books' => 1, 'issue_duration_days' => 14, 'borrowing_allowed' => true]);
    $first = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    $second = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $body = ['student_id' => $student->id, 'accession_numbers' => [$first->accession_number, $second->accession_number]];
    $this->postJson('/api/staff/issues/preview', $body)->assertUnprocessable()->assertJsonPath('data.privileges.can_issue', 1);
    $this->postJson('/api/staff/issues', $body)->assertUnprocessable()->assertJsonPath('code', 'student_not_eligible');
    $student->privileges()->update(['borrowing_allowed' => false]);
    $body['accession_numbers'] = [$first->accession_number];
    $this->postJson('/api/staff/issues', $body)->assertUnprocessable()->assertJsonPath('code', 'student_not_eligible');
    $student->privileges()->update(['borrowing_allowed' => true]);
    $student->user->update(['status' => 'inactive']);
    $this->postJson('/api/staff/issues', $body)->assertUnprocessable()->assertJsonPath('code', 'student_not_eligible');
    $this->assertDatabaseCount('issued_books', 0);
});

test('mixed selectors cannot hide a manipulated accession batch', function () {
    $student = makePhysicalIssueApiStudent();
    $copy = app(PhysicalBookCopyService::class)->createCopies(makePhysicalIssueApiBook(), 1)->first();
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->postJson('/api/staff/issues', ['student_id' => $student->id, 'book_copy_id' => $copy->id,
        'accession_numbers' => [$copy->accession_number, $copy->accession_number]])
        ->assertUnprocessable()->assertJsonValidationErrors('book_copy_id');
    $this->assertDatabaseCount('issued_books', 0);
});

test('shared circulation rechecks privileges after an earlier preflight', function (string $change) {
    $student = makePhysicalIssueApiStudent();
    StudentPrivilege::create(['student_id' => $student->id, 'max_books' => 1, 'issue_duration_days' => 14, 'borrowing_allowed' => true]);
    $service = app(PhysicalBookCopyService::class);
    $copy = $service->createCopies(makePhysicalIssueApiBook(), 1)->first();
    expect(app(StudentIssuePrivilegeService::class)->canIssue($student, 1)['allowed'])->toBeTrue();

    if ($change === 'capacity') {
        $otherCopy = $service->createCopies(makePhysicalIssueApiBook(), 1)->first();
        $service->issue($student, $otherCopy->accession_number);
    } elseif ($change === 'borrowing') {
        $student->privileges()->update(['borrowing_allowed' => false]);
    } else {
        $student->user->update(['status' => 'inactive']);
    }

    expect(fn () => $service->issue($student, $copy->accession_number))
        ->toThrow(PhysicalCopyException::class);
    $this->assertDatabaseCount('issued_books', $change === 'capacity' ? 1 : 0);
    expect($copy->fresh()->status)->toBe('available');
})->with(['capacity', 'borrowing', 'account']);

test('physical API search preview and final issue agree on a stale issued flag', function () {
    $service = app(PhysicalBookCopyService::class);
    $student = makePhysicalIssueApiStudent();
    $copy = $service->createCopies(makePhysicalIssueApiBook(), 1)->first();
    $service->issue($student, $copy->accession_number);
    $service->return($copy->accession_number);
    $copy->update(['status' => 'issued']);
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->getJson('/api/staff/issue-books?student_id='.$student->id.'&search='.$copy->accession_number)
        ->assertOk()->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'available')
        ->assertJsonPath('data.0.can_select', true);
    $body = ['student_id' => $student->id, 'accession_numbers' => [$copy->accession_number]];
    $this->postJson('/api/staff/issues/preview', $body)
        ->assertOk()->assertJsonPath('data.selected_copies.0.can_select', true);
    $this->postJson('/api/staff/issues', $body)->assertCreated();
    expect(IssuedBook::where('book_copy_id', $copy->id)->whereNull('return_date')->count())->toBe(1);
});

test('catalog and title preview report physical availability despite stale flags and counters', function () {
    $service = app(PhysicalBookCopyService::class);
    $book = makePhysicalIssueApiBook();
    $copies = $service->createCopies($book, 2);
    $copies[0]->update(['status' => 'issued']);
    $copies[1]->update(['condition' => 'damaged']);
    $book->update(['available_copies' => 0]);
    $student = makePhysicalIssueApiStudent();
    Sanctum::actingAs(makePhysicalIssueApiUser());

    foreach ([
        '/api/books', '/api/books?availability=available', '/api/books/available',
        '/api/books/search?q='.urlencode($book->title).'&availability=available',
        '/api/books/category/'.$book->category_id,
    ] as $url) {
        $this->getJson($url)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book->id)
            ->assertJsonPath('data.0.available_quantity', 1)
            ->assertJsonPath('data.0.status', 'available');
    }
    $this->getJson('/api/books/'.$book->id)->assertOk()
        ->assertJsonPath('data.available_quantity', 1)
        ->assertJsonPath('data.copies.0.status', 'available');
    $this->getJson('/api/staff/books/search?student_id='.$student->id.'&query='.urlencode($book->title))
        ->assertOk()->assertJsonPath('data.0.available_copies', 1)->assertJsonPath('data.0.is_available', true);
    $this->postJson('/api/staff/issues/preview', ['student_id' => $student->id, 'book_ids' => [$book->id]])
        ->assertOk()->assertJsonPath('data.selected_books.0.is_available', true);
    $this->getJson('/api/staff/dashboard')->assertOk()->assertJsonPath('data.circulation.available_books', 1);
});

test('catalog availability filters and sorting combine physical inventory with legacy titles', function () {
    $service = app(PhysicalBookCopyService::class);
    $physical = makePhysicalIssueApiBook(['title' => 'A physical']);
    $service->createCopies($physical, 3);
    $physical->update(['available_copies' => 0]);
    $legacy = makePhysicalIssueApiBook(['title' => 'Z legacy', 'available_copies' => 5]);
    $unavailable = makePhysicalIssueApiBook(['title' => 'B unavailable']);
    $service->createCopies($unavailable, 1, ['book_type' => 'reference']);
    $unavailable->update(['available_copies' => 99]);
    $withdrawn = makePhysicalIssueApiBook(['title' => 'C withdrawn', 'status' => 'withdrawn', 'available_copies' => 99]);
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->getJson('/api/books?sort=available_desc')->assertOk()
        ->assertJsonPath('data.0.id', $legacy->id)->assertJsonPath('data.0.available_quantity', 5)
        ->assertJsonPath('data.1.id', $physical->id)->assertJsonPath('data.1.available_quantity', 3)
        ->assertJsonPath('data.2.id', $unavailable->id)->assertJsonPath('data.2.available_quantity', 0)
        ->assertJsonPath('data.3.id', $withdrawn->id)->assertJsonPath('data.3.available_quantity', 0);
    $this->getJson('/api/books?availability=unavailable')->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $unavailable->id)->assertJsonPath('data.1.id', $withdrawn->id);
    $this->getJson('/api/books/available')->assertOk()->assertJsonCount(2, 'data');
});

test('physical copy status filters agree with accession details when stored flags drift', function (string $prefix) {
    $service = app(PhysicalBookCopyService::class);
    $book = makePhysicalIssueApiBook();
    $copies = $service->createCopies($book, 2);
    $copies[0]->update(['status' => 'issued']);
    $issue = $service->issue(makePhysicalIssueApiStudent(), $copies[1]->accession_number);
    $copies[1]->update(['status' => 'available']);
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->getJson($prefix.'?book_id='.$book->id.'&status=available')->assertOk()
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $copies[0]->id)
        ->assertJsonPath('data.0.status', 'available');
    $this->getJson($prefix.'?book_id='.$book->id.'&status=issued')->assertOk()
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $copies[1]->id)
        ->assertJsonPath('data.0.status', 'issued');
    $this->getJson($prefix.'/'.$copies[0]->accession_number)->assertOk()
        ->assertJsonPath('data.copy.status', 'available')->assertJsonPath('data.active_issue', null);
    $this->getJson($prefix.'/'.$copies[1]->accession_number)->assertOk()
        ->assertJsonPath('data.copy.status', 'issued')->assertJsonPath('data.active_issue.id', $issue->id);
})->with(['/api/book-copies', '/api/staff/book-copies']);

test('general title issue uses an eligible physical copy with a stale issued flag', function () {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $book = makePhysicalIssueApiBook();
    $copy = $service->createCopies($book, 1)->first();
    $copy->update(['status' => 'issued']);
    $book->update(['available_copies' => 0]);
    $student = makePhysicalIssueApiStudent();
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->postJson('/api/issues', ['student_id' => $student->id, 'book_id' => $book->id])
        ->assertCreated()->assertJsonPath('data.0.book_copy.book_copy_id', $copy->id);
    $this->assertDatabaseHas('issued_books', ['student_id' => $student->id, 'book_copy_id' => $copy->id]);
    $this->postJson('/api/issues', ['student_id' => makePhysicalIssueApiStudent()->id, 'book_id' => $book->id])
        ->assertStatus(409);
    $this->assertDatabaseCount('issued_books', 1);
});

test('general title issue cannot bypass unavailable physical copies using stale book counters', function (array $attributes, bool $active) {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $book = makePhysicalIssueApiBook();
    $copy = $service->createCopies($book, 1)->first();
    if ($active) {
        $service->issue(makePhysicalIssueApiStudent(), $copy->accession_number);
    }
    $copy->update($attributes);
    $book->update(['available_copies' => 99]);
    $student = makePhysicalIssueApiStudent();
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->postJson('/api/issues', ['student_id' => $student->id, 'book_id' => $book->id])->assertStatus(409);
    $this->assertDatabaseMissing('issued_books', ['student_id' => $student->id]);
    $this->assertDatabaseCount('issued_books', $active ? 1 : 0);
    $this->getJson('/api/books/available')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/staff/books/search?student_id='.$student->id)->assertOk()
        ->assertJsonPath('data.0.available_copies', 0)->assertJsonPath('data.0.is_available', false);
    $this->postJson('/api/staff/issues/preview', ['student_id' => $student->id, 'book_ids' => [$book->id]])
        ->assertUnprocessable()->assertJsonPath('data.selected_books.0.is_available', false);
})->with([
    'loaned with stale available flag' => [['status' => 'available'], true],
    'damaged' => [['condition' => 'damaged'], false],
    'lost' => [['condition' => 'lost'], false],
    'reference' => [['book_type' => 'reference'], false],
    'maintenance' => [['status' => 'maintenance'], false],
]);

test('general title issue returns a JSON eligibility error for a restricted catalogue book', function (string $status) {
    $service = app(PhysicalBookCopyService::class);
    $book = makePhysicalIssueApiBook();
    $service->createCopies($book, 1);
    $book->update(['status' => $status]);
    $student = makePhysicalIssueApiStudent();
    Sanctum::actingAs(makePhysicalIssueApiUser());

    $this->postJson('/api/issues', ['student_id' => $student->id, 'book_id' => $book->id])
        ->assertUnprocessable()->assertJsonPath('code', 'book_not_borrowable');
    $this->assertDatabaseCount('issued_books', 0);
})->with(['inactive', 'withdrawn']);

test('request creation and approval use physical availability despite stale inventory', function (string $prefix, bool $active) {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $book = makePhysicalIssueApiBook();
    $copy = $service->createCopies($book, 1)->first();
    if ($active) {
        $service->issue(makePhysicalIssueApiStudent(), $copy->accession_number);
    }
    $copy->update(['status' => $active ? 'available' : 'issued']);
    $book->update(['available_copies' => $active ? 99 : 0]);
    $student = makePhysicalIssueApiStudent();
    Sanctum::actingAs($student->user);

    $response = $this->postJson('/api/student/requests', ['book_id' => $book->id]);
    if ($active) {
        $response->assertUnprocessable();
        $this->assertDatabaseCount('book_requests', 0);
        $bookRequest = BookRequest::create([
            'student_id' => $student->id, 'book_id' => $book->id, 'request_date' => now(), 'status' => 'pending',
        ]);
    } else {
        $response->assertCreated()->assertJsonPath('data.book.available_quantity', 1);
        $bookRequest = BookRequest::firstOrFail();
    }
    Sanctum::actingAs(makePhysicalIssueApiUser());
    $this->postJson($prefix.'/'.$bookRequest->id.'/approve')->assertStatus($active ? 409 : 200);
    expect($bookRequest->fresh()->status)->toBe($active ? 'pending' : 'approved');
})->with(['/api/book-requests', '/api/staff/book-requests'])->with([false, true]);
