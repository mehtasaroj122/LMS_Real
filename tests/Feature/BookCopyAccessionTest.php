<?php

use App\Exceptions\PhysicalCopyException;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PhysicalBookCopyService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery\MockInterface;

function accessionTestBook(array $overrides = []): Book
{
    $key = Str::lower(Str::random(10));
    $category = Category::create([
        'name' => 'Accession Category ' . $key,
        'description' => 'Accession feature test category',
    ]);

    return Book::create(array_merge([
        'category_id' => $category->id,
        'title' => 'Accession Test Book ' . $key,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => '9' . random_int(100000000000, 999999999999),
        'total_copies' => 1,
        'available_copies' => 1,
        'condition' => 'good',
        'description' => 'Physical copy test book',
        'shelf_no' => 'A-01',
        'status' => 'available',
    ], $overrides));
}

function accessionTestStudent(): Student
{
    $key = Str::lower(Str::random(10));
    $user = User::create([
        'role' => 'student',
        'name' => 'Accession Student ' . $key,
        'email' => "accession-{$key}@example.com",
        'phone' => '98' . random_int(10000000, 99999999),
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);
    $department = Department::create([
        'name' => 'Accession Department ' . $key,
        'code' => Str::upper(substr($key, 0, 5)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'ACC-STU-' . Str::upper(substr($key, 0, 5)),
        'roll_no' => 'ACC-ROLL-' . Str::upper(substr($key, 0, 5)),
        'batch' => '2026',
        'semester' => '1',
    ]);
}

test('accession numbers start at one and remain unique across multiple copies', function () {
    $book = accessionTestBook(['total_copies' => 3]);
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 3);

    expect($copies->pluck('accession_number')->all())->toBe([
        'ACC-000001',
        'ACC-000002',
        'ACC-000003',
    ]);
    expect(BookCopy::query()->where('book_id', $book->id)->count())->toBe(3);
    expect($book->fresh()->available_copies)->toBe(3);
});

test('issuing and returning an accession number updates the physical copy and issue history', function () {
    $book = accessionTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    $student = accessionTestStudent();
    $staff = User::create([
        'role' => 'staff',
        'name' => 'Accession Staff',
        'email' => 'accession-staff@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $service = app(PhysicalBookCopyService::class);
    $issue = $service->issue($student, $copy->accession_number, $staff);

    expect($issue->book_copy_id)->toBe($copy->id);
    expect($copy->fresh()->status)->toBe('issued');
    expect($book->fresh()->available_copies)->toBe(0);

    expect(fn () => $service->issue($student, $copy->accession_number, $staff))
        ->toThrow(PhysicalCopyException::class, 'already issued');

    $returned = $service->return($copy->accession_number);
    expect($returned['issue']->return_date)->not->toBeNull();
    expect($copy->fresh()->status)->toBe('available');
    expect($book->fresh()->available_copies)->toBe(1);
});

test('reference copies cannot be issued', function () {
    $book = accessionTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1, ['book_type' => 'reference'])->first();
    $student = accessionTestStudent();

    expect(fn () => app(PhysicalBookCopyService::class)->issue($student, $copy->accession_number))
        ->toThrow(PhysicalCopyException::class, 'reference-only');
});

test('staff API can issue and return a copy by accession number', function () {
    $book = accessionTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    $student = accessionTestStudent();
    $staff = User::create([
        'role' => 'staff',
        'name' => 'API Accession Staff',
        'email' => 'api-accession-staff@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $issueResponse = $this->actingAs($staff, 'sanctum')->postJson('/api/staff/issues/by-accession', [
        'student_id' => $student->id,
        'accession_number' => $copy->accession_number,
    ]);

    $issueResponse
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.issue.accession_number', $copy->accession_number);

    $this->actingAs($staff, 'sanctum')
        ->getJson('/api/staff/issues/by-accession?accession_number=' . $copy->accession_number)
        ->assertOk()
        ->assertJsonPath('data.issue.accession_number', $copy->accession_number);

    $this->actingAs($staff, 'sanctum')->postJson('/api/staff/returns/by-accession', [
        'accession_number' => $copy->accession_number,
        'condition' => 'good',
    ])->assertOk()->assertJsonPath('success', true);
});

test('staff accession search accepts a partial suffix and returns the borrower', function () {
    $book = accessionTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    $student = accessionTestStudent();
    $staff = User::create([
        'role' => 'staff',
        'name' => 'Search Accession Staff',
        'email' => 'search-accession-staff@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $suffix = substr($copy->accession_number, -3);

    $this->actingAs($staff)
        ->getJson('/staff/book-copies/search?query=' . $suffix . '&mode=issue')
        ->assertOk()
        ->assertJsonPath('data.0.copy.accession_number', $copy->accession_number);

    app(PhysicalBookCopyService::class)->issue($student, $copy->accession_number, $staff);

    $this->actingAs($staff)
        ->getJson('/staff/book-copies/search?query=' . $suffix . '&mode=return')
        ->assertOk()
        ->assertJsonPath('data.0.issue.student.name', $student->user->name)
        ->assertJsonPath('data.0.issue.accession_number', $copy->accession_number);
});

test('staff and admin issue searches show unavailable copies by accession and title', function () {
    $book = accessionTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    $borrower = accessionTestStudent();
    $targetStudent = accessionTestStudent();
    $staff = User::create([
        'role' => 'staff',
        'name' => 'Unavailable Search Staff ' . Str::random(6),
        'email' => 'unavailable-search-staff-' . Str::lower(Str::random(8)) . '@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);
    $admin = User::create([
        'role' => 'admin',
        'name' => 'Unavailable Search Admin ' . Str::random(6),
        'email' => 'unavailable-search-admin-' . Str::lower(Str::random(8)) . '@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    app(PhysicalBookCopyService::class)->issue($borrower, $copy->accession_number, $staff);

    foreach ([
        [$staff, '/staff/book-copies/search', '/staff/transactions/books'],
        [$admin, '/admin/book-copies/search', '/admin/transactions/books/available'],
    ] as [$user, $accessionSearchUrl, $bookSearchUrl]) {
        $this->actingAs($user)
            ->getJson($accessionSearchUrl . '?query=' . $copy->accession_number . '&mode=issue')
            ->assertOk()
            ->assertJsonPath('data.0.copy.accession_number', $copy->accession_number)
            ->assertJsonPath('data.0.copy.status', 'issued');

        $this->actingAs($user)
            ->getJson($bookSearchUrl . '?query=' . urlencode($book->title) . '&studentId=' . $targetStudent->id)
            ->assertOk()
            ->assertJsonPath('0.available_copies', 0)
            ->assertJsonPath('0.copies.0.accession_number', $copy->accession_number)
            ->assertJsonPath('0.copies.0.status', 'issued');
    }
});

test('staff issue workflow issues the selected physical copy rather than an arbitrary title copy', function () {
    $book = accessionTestBook(['total_copies' => 2]);
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 2);
    $otherStudent = accessionTestStudent();
    $student = accessionTestStudent();
    $staff = User::create([
        'role' => 'staff',
        'name' => 'Physical Selection Staff',
        'email' => 'physical-selection-staff@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $this->actingAs($staff)
        ->postJson('/staff/transactions/issue', [
            'student_id' => $student->id,
            'book_copy_ids' => [$copies->last()->id],
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('issued_count', 1)
        ->assertJsonPath('issued_books.0.book_copy_id', $copies->last()->id)
        ->assertJsonPath('issued_books.0.accession_number', $copies->last()->accession_number);

    expect($copies->first()->fresh()->status)->toBe('available')
        ->and($copies->last()->fresh()->status)->toBe('issued')
        ->and(IssuedBook::query()->where('book_copy_id', $copies->last()->id)->whereNull('return_date')->exists())->toBeTrue();
    $this->assertDatabaseHas('issued_books', ['book_copy_id' => $copies->last()->id, 'student_id' => $student->id]);
    $this->assertDatabaseMissing('issued_books', ['student_id' => $otherStudent->id]);
});

test('admin transaction issue workflow also issues the selected physical copy', function () {
    $book = accessionTestBook(['total_copies' => 2]);
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 2);
    $otherStudent = accessionTestStudent();
    $student = accessionTestStudent();
    $admin = User::create([
        'role' => 'admin',
        'name' => 'Physical Selection Admin',
        'email' => 'physical-selection-admin@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $this->actingAs($admin)
        ->postJson('/admin/transactions/issue', [
            'student_id' => $student->id,
            'book_copy_ids' => [$copies->first()->id],
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('issued_books.0.book_copy_id', $copies->first()->id)
        ->assertJsonPath('issued_books.0.accession_number', $copies->first()->accession_number);
    $this->assertDatabaseHas('issued_books', ['book_copy_id' => $copies->first()->id, 'student_id' => $student->id]);
    $this->assertDatabaseMissing('issued_books', ['student_id' => $otherStudent->id]);
});

test('web issue rejects multiple copies of the same book before any issue or notification', function (string $role) {
    Queue::fake();
    $this->mock(NotificationService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('notifyBookIssued'));
    $service = app(PhysicalBookCopyService::class);
    $book = accessionTestBook();
    $copies = $service->createCopies($book, 2);
    $otherCopy = $service->createCopies(accessionTestBook(), 1)->first();
    $student = accessionTestStudent();
    $issuer = User::factory()->create(['role' => $role, 'status' => 'active', 'is_verified' => true]);

    $this->actingAs($issuer)->postJson("/{$role}/transactions/issue", [
        'student_id' => $student->id,
        'book_copy_ids' => [$otherCopy->id, $copies[0]->id, $copies[1]->id],
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Only one copy of each book can be selected. Remove the extra copies and try again.');

    $this->assertDatabaseCount('issued_books', 0);
    expect($book->fresh()->available_copies)->toBe(2)
        ->and($otherCopy->fresh()->status)->toBe('available');
    foreach ($copies as $copy) {
        expect($copy->fresh()->status)->toBe('available');
    }
    Queue::assertNothingPushed();
})->with(['admin', 'staff']);

test('web issue uses loan records when a copy has a stale issued flag', function (string $role, bool $hasHistory) {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $book = accessionTestBook();
    $copy = $service->createCopies($book, 1)->first();
    $student = accessionTestStudent();
    $issuer = User::factory()->create(['role' => $role, 'status' => 'active', 'is_verified' => true]);

    if ($hasHistory) {
        $service->issue($student, $copy->accession_number, $issuer);
        $service->return($copy->accession_number);
    }
    $copy->update(['status' => 'issued']);
    $service->refreshBookCounters($book);
    $this->actingAs($issuer);

    $bookSearchUrl = $role === 'admin' ? '/admin/transactions/books/available' : '/staff/transactions/books';
    $this->getJson($bookSearchUrl.'?query='.urlencode($book->title).'&studentId='.$student->id)
        ->assertOk()
        ->assertJsonPath('0.available_copies', 1)
        ->assertJsonPath('0.already_issued_to_student', false)
        ->assertJsonPath('0.copies.0.status', 'available')
        ->assertJsonPath('0.copies.0.active_issue', null);
    $this->getJson("/{$role}/book-copies/search?query=".$copy->accession_number.'&mode=issue&student_id='.$student->id)
        ->assertOk()
        ->assertJsonPath('data.0.copy.status', 'available')
        ->assertJsonPath('data.0.copy.active_issue', null)
        ->assertJsonPath('data.0.copy.already_issued_to_student', false);

    $this->postJson("/{$role}/transactions/issue", [
        'student_id' => $student->id,
        'book_copy_ids' => [$copy->id],
    ])->assertOk()->assertJsonPath('success', true);
    expect($copy->fresh()->status)->toBe('issued')
        ->and($book->fresh()->available_copies)->toBe(0);
    expect(IssuedBook::where('book_copy_id', $copy->id)->whereNull('return_date')->count())->toBe(1);
})->with(['admin', 'staff'])->with([false, true]);

test('web issue blocks a copy loaned to another student even when its available flag is stale', function (string $role) {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $book = accessionTestBook();
    $copy = $service->createCopies($book, 1)->first();
    $student = accessionTestStudent();
    $borrower = accessionTestStudent();
    $issuer = User::factory()->create(['role' => $role, 'status' => 'active', 'is_verified' => true]);
    $issue = $service->issue($borrower, $copy->accession_number, $issuer);
    $copy->update(['status' => 'available']);
    $this->actingAs($issuer);

    $bookSearchUrl = $role === 'admin' ? '/admin/transactions/books/available' : '/staff/transactions/books';
    $this->getJson($bookSearchUrl.'?query='.urlencode($book->title).'&studentId='.$student->id)
        ->assertOk()
        ->assertJsonPath('0.available_copies', 0)
        ->assertJsonPath('0.already_issued_to_student', false)
        ->assertJsonPath('0.copies.0.status', 'issued')
        ->assertJsonPath('0.copies.0.active_issue.id', $issue->id)
        ->assertJsonPath('0.copies.0.active_issue.student.id', $borrower->id)
        ->assertJsonPath('0.copies.0.active_issue.student.name', $borrower->user->name)
        ->assertJsonPath('0.copies.0.active_issue.student.roll_no', $borrower->roll_no);
    $this->getJson("/{$role}/book-copies/search?query=".$copy->accession_number.'&mode=issue&student_id='.$student->id)
        ->assertOk()->assertJsonPath('data.0.copy.status', 'issued')
        ->assertJsonPath('data.0.copy.active_issue.id', $issue->id)
        ->assertJsonPath('data.0.copy.active_issue.student.id', $borrower->id);
    $this->postJson("/{$role}/transactions/issue", [
        'student_id' => $student->id,
        'book_copy_ids' => [$copy->id],
    ])->assertUnprocessable();
    expect(IssuedBook::where('book_copy_id', $copy->id)->whereNull('return_date')->count())->toBe(1);
    expect($issue->fresh()->student_id)->toBe($borrower->id);
})->with(['admin', 'staff']);

test('web issue permits one copy of each distinct book even when titles match', function (string $role) {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $firstCopy = $service->createCopies(accessionTestBook(['title' => 'Same title']), 1)->first();
    $secondCopy = $service->createCopies(accessionTestBook(['title' => 'Same title']), 1)->first();
    $student = accessionTestStudent();
    $issuer = User::factory()->create(['role' => $role, 'status' => 'active', 'is_verified' => true]);

    $this->actingAs($issuer)->postJson("/{$role}/transactions/issue", [
        'student_id' => $student->id,
        'book_copy_ids' => [$firstCopy->id, $secondCopy->id],
    ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('issued_count', 2);

    $this->assertDatabaseCount('issued_books', 2);
    expect($firstCopy->fresh()->status)->toBe('issued')
        ->and($secondCopy->fresh()->status)->toBe('issued');
})->with(['admin', 'staff']);

test('web issue searches show books already issued to the selected student until returned', function (string $role) {
    Queue::fake();
    $service = app(PhysicalBookCopyService::class);
    $book = accessionTestBook();
    $copies = $service->createCopies($book, 2);
    $student = accessionTestStudent();
    $otherStudent = accessionTestStudent();
    $issuer = User::factory()->create(['role' => $role, 'status' => 'active', 'is_verified' => true]);
    $service->issue($student, $copies[0]->accession_number, $issuer);
    $this->actingAs($issuer);

    $bookSearchUrl = $role === 'admin' ? '/admin/transactions/books/available' : '/staff/transactions/books';
    $bookQuery = $bookSearchUrl.'?query='.urlencode($book->title).'&studentId=';
    $copyQuery = "/{$role}/book-copies/search?query=".$copies[1]->accession_number.'&mode=issue&student_id=';

    $this->getJson($bookQuery.$student->id)->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $book->id)
        ->assertJsonPath('0.already_issued_to_student', true)
        ->assertJsonPath('0.copies.0.already_issued_to_student', true)
        ->assertJsonPath('0.copies.1.already_issued_to_student', true);
    $this->getJson($copyQuery.$student->id)->assertOk()
        ->assertJsonPath('data.0.copy.status', 'available')
        ->assertJsonPath('data.0.copy.already_issued_to_student', true);

    // Another borrower may still take the available physical copy.
    $this->getJson($bookQuery.$otherStudent->id)->assertOk()
        ->assertJsonPath('0.already_issued_to_student', false)
        ->assertJsonPath('0.copies.1.already_issued_to_student', false);
    $this->getJson($copyQuery.$otherStudent->id)->assertOk()
        ->assertJsonPath('data.0.copy.already_issued_to_student', false);

    $service->return($copies[0]->accession_number);
    $this->getJson($bookQuery.$student->id)->assertOk()
        ->assertJsonPath('0.already_issued_to_student', false)
        ->assertJsonPath('0.copies.0.active_issue', null)
        ->assertJsonPath('0.copies.1.already_issued_to_student', false);
    $this->getJson($copyQuery.$student->id)->assertOk()
        ->assertJsonPath('data.0.copy.already_issued_to_student', false);
})->with(['admin', 'staff']);

test('circulation repair fixes stale flags and counters without changing loans or restricted copies', function () {
    $service = app(PhysicalBookCopyService::class);
    $book = accessionTestBook();
    $copies = $service->createCopies($book, 9);
    $student = accessionTestStudent();
    $service->issue($student, $copies[0]->accession_number);
    $service->return($copies[0]->accession_number);
    $copies[0]->update(['status' => 'issued']);
    $copies[1]->update(['status' => 'issued']);
    $copies[2]->update(['status' => 'issued', 'condition' => 'damaged']);
    $copies[3]->update(['status' => 'issued', 'condition' => 'lost']);
    $service->issue($student, $copies[4]->accession_number);
    $copies[4]->update(['status' => 'available']);
    $copies[5]->update(['status' => 'maintenance']);
    $copies[6]->update(['status' => 'withdrawn']);
    $copies[7]->update(['status' => 'damaged']);
    $copies[8]->update(['status' => 'lost']);
    $book->forceFill(['total_copies' => 99, 'available_copies' => 99, 'status' => 'inactive'])->saveQuietly();
    $loansBefore = IssuedBook::orderBy('id')->get()->toArray();

    $migration = require database_path('migrations/2026_10_10_000001_reconcile_copy_circulation_statuses.php');
    $migration->up();
    $migration->up(); // A repeated repair is safe.

    expect($copies->map(fn (BookCopy $copy) => $copy->fresh()->status)->all())->toBe([
        'available', 'available', 'damaged', 'lost', 'issued', 'maintenance', 'withdrawn', 'damaged', 'lost',
    ])->and($book->fresh()->total_copies)->toBe(9)
        ->and($book->fresh()->available_copies)->toBe(2)
        ->and($book->fresh()->getRawOriginal('status'))->toBe('inactive')
        ->and(IssuedBook::orderBy('id')->get()->toArray())->toBe($loansBefore);
});
