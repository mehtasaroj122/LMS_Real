<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Services\PhysicalBookCopyService;
use App\Exceptions\PhysicalCopyException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function bulkCopyTestBook(string $label = 'Primary'): Book
{
    $key = Str::lower(Str::random(10));
    $category = Category::create([
        'name' => "Bulk {$label} {$key}",
        'description' => 'Bulk copy deletion test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => "Bulk {$label} Book {$key}",
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => '8' . random_int(100000000000, 999999999999),
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'description' => 'Bulk copy deletion feature test',
        'shelf_no' => 'B-01',
        'status' => 'unavailable',
    ]);
}

function bulkCopyTestUser(string $role): User
{
    $key = Str::lower(Str::random(10));

    return User::create([
        'role' => $role,
        'name' => ucfirst($role) . " Bulk {$key}",
        'email' => "{$role}-bulk-{$key}@example.com",
        'phone' => '98' . random_int(10000000, 99999999),
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);
}

function bulkCopyTestStudent(): Student
{
    $key = Str::lower(Str::random(10));
    $user = bulkCopyTestUser('student');
    $department = Department::create([
        'name' => "Bulk Department {$key}",
        'code' => Str::upper(substr($key, 0, 5)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'BULK-' . Str::upper(substr($key, 0, 6)),
        'roll_no' => 'ROLL-' . Str::upper(substr($key, 0, 6)),
        'batch' => '2026',
        'semester' => '1',
    ]);
}

test('admin bulk deletion rechecks eligibility and preserves active and historical loans', function () {
    $admin = bulkCopyTestUser('admin');
    $book = bulkCopyTestBook();
    $otherBook = bulkCopyTestBook('Other');
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 5);
    $otherCopy = app(PhysicalBookCopyService::class)->createCopies($otherBook, 1)->first();

    $copies[1]->update(['status' => 'available', 'condition' => 'damaged']);
    $copies[4]->update(['status' => 'lost', 'condition' => 'lost']);
    $student = bulkCopyTestStudent();
    app(PhysicalBookCopyService::class)->issue($student, $copies[3]->accession_number, $admin);
    app(PhysicalBookCopyService::class)->return($copies[3]->accession_number);
    app(PhysicalBookCopyService::class)->issue($student, $copies[2]->accession_number, $admin);

    $ids = [...$copies->pluck('id')->all(), $otherCopy->id];

    $this->actingAs($admin)
        ->postJson(route('admin.books.copies.bulk-preview', $book), [
            'action' => 'delete_selected',
            'copy_ids' => $ids,
        ])
        ->assertOk()
        ->assertJsonPath('data.requested_count', 6)
        ->assertJsonPath('data.matched_count', 5)
        ->assertJsonPath('data.eligible_count', 3)
        ->assertJsonPath('data.borrowed_count', 1)
        ->assertJsonPath('data.history_protected_count', 1)
        ->assertJsonPath('data.invalid_count', 1);

    $this->actingAs($admin)
        ->deleteJson(route('admin.books.copies.bulk-delete', $book), [
            'action' => 'delete_selected',
            'copy_ids' => $ids,
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.deleted_count', 3)
        ->assertJsonPath('data.skipped_count', 3)
        ->assertJsonPath('data.summary.total', 2)
        ->assertJsonPath('data.summary.available', 1)
        ->assertJsonPath('data.summary.issued', 1);

    expect(BookCopy::query()->whereKey($copies[0]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[1]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[4]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[2]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($copies[3]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($otherCopy->id)->exists())->toBeTrue()
        ->and($book->fresh()->total_copies)->toBe(2)
        ->and($book->fresh()->available_copies)->toBe(1);

    $log = ActivityLog::query()->where('action', 'bulk_copies_deleted')->latest('id')->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->user_role)->toBe('admin')
        ->and($log->metadata['deleted'])->toBe(3)
        ->and($log->metadata['borrowed_protected'])->toBe(1);
});

test('staff category action affects only the current book and skips a copy issued after preview', function () {
    $staff = bulkCopyTestUser('staff');
    $book = bulkCopyTestBook();
    $otherBook = bulkCopyTestBook('Other');
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 2);
    $otherCopy = app(PhysicalBookCopyService::class)->createCopies($otherBook, 1)->first();

    $this->actingAs($staff)
        ->postJson(route('staff.books.copies.bulk-preview', $book), ['action' => 'delete_available'])
        ->assertOk()
        ->assertJsonPath('data.eligible_count', 2);

    app(PhysicalBookCopyService::class)->issue(bulkCopyTestStudent(), $copies[1]->accession_number, $staff);

    $this->actingAs($staff)
        ->deleteJson(route('staff.books.copies.bulk-delete', $book), ['action' => 'delete_available'])
        ->assertOk()
        ->assertJsonPath('data.deleted_count', 1)
        ->assertJsonPath('data.summary.issued', 1);

    expect(BookCopy::query()->whereKey($copies[0]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[1]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($otherCopy->id)->exists())->toBeTrue();

    $log = ActivityLog::query()->where('action', 'bulk_copies_deleted')->latest('id')->first();
    expect($log->user_id)->toBe($staff->id)
        ->and($log->user_role)->toBe('staff');
});

test('individual deletion uses the same borrowed and history protection', function () {
    $admin = bulkCopyTestUser('admin');
    $book = bulkCopyTestBook();
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 2);
    $student = bulkCopyTestStudent();

    app(PhysicalBookCopyService::class)->issue($student, $copies[0]->accession_number, $admin);
    $this->actingAs($admin)
        ->deleteJson(route('admin.book-copies.destroy', $copies[0]))
        ->assertStatus(409)
        ->assertJsonPath('success', false);

    app(PhysicalBookCopyService::class)->issue(bulkCopyTestStudent(), $copies[1]->accession_number, $admin);
    app(PhysicalBookCopyService::class)->return($copies[1]->accession_number);
    $this->actingAs($admin)
        ->deleteJson(route('admin.book-copies.destroy', $copies[1]))
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    expect(BookCopy::query()->whereKey($copies[0]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($copies[1]->id)->exists())->toBeTrue();
});

test('damaged lost and all eligible actions use real condition rules and retain protected copies and the book', function () {
    $admin = bulkCopyTestUser('admin');
    $book = bulkCopyTestBook();
    $copies = app(PhysicalBookCopyService::class)->createCopies($book, 5);
    $copies[1]->update(['status' => 'available', 'condition' => 'damaged']);
    $copies[2]->update(['status' => 'lost', 'condition' => 'lost']);

    $student = bulkCopyTestStudent();
    app(PhysicalBookCopyService::class)->issue($student, $copies[3]->accession_number, $admin);
    app(PhysicalBookCopyService::class)->return($copies[3]->accession_number);
    app(PhysicalBookCopyService::class)->issue($student, $copies[4]->accession_number, $admin);

    $this->actingAs($admin)
        ->deleteJson(route('admin.books.copies.bulk-delete', $book), ['action' => 'delete_damaged'])
        ->assertOk()
        ->assertJsonPath('data.deleted_count', 1);

    $this->actingAs($admin)
        ->deleteJson(route('admin.books.copies.bulk-delete', $book), ['action' => 'delete_lost'])
        ->assertOk()
        ->assertJsonPath('data.deleted_count', 1);

    $this->actingAs($admin)
        ->deleteJson(route('admin.books.copies.bulk-delete', $book), ['action' => 'delete_all_eligible'])
        ->assertOk()
        ->assertJsonPath('data.deleted_count', 1)
        ->assertJsonPath('data.borrowed_count', 1)
        ->assertJsonPath('data.history_protected_count', 1)
        ->assertJsonPath('data.summary.total', 2);

    $replacement = app(PhysicalBookCopyService::class)->createCopies($book->fresh(), 1)->first();

    expect(Book::query()->whereKey($book->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($copies[0]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[1]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[2]->id)->exists())->toBeFalse()
        ->and(BookCopy::query()->whereKey($copies[3]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($copies[4]->id)->exists())->toBeTrue()
        ->and($copies->pluck('accession_number'))->not->toContain($replacement->accession_number);
});

test('copy management renders accessible disabled selection for protected copies', function () {
    $admin = bulkCopyTestUser('admin');
    $book = bulkCopyTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    app(PhysicalBookCopyService::class)->issue(bulkCopyTestStudent(), $copy->accession_number, $admin);

    $this->actingAs($admin)
        ->get(route('admin.books.copies.index', $book))
        ->assertOk()
        ->assertSee('Select all eligible copies on this page')
        ->assertSee("Select copy {$copy->accession_number}")
        ->assertSee('Currently borrowed by')
        ->assertSee('disabled', false);
});

test('students cannot access admin or staff bulk deletion endpoints', function () {
    $studentUser = bulkCopyTestUser('student');
    $book = bulkCopyTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();

    $this->actingAs($studentUser)
        ->deleteJson(route('admin.books.copies.bulk-delete', $book), [
            'action' => 'delete_selected',
            'copy_ids' => [$copy->id],
        ])->assertForbidden();

    $this->actingAs($studentUser)
        ->deleteJson(route('staff.books.copies.bulk-delete', $book), [
            'action' => 'delete_selected',
            'copy_ids' => [$copy->id],
        ])->assertForbidden();

    expect($copy->fresh())->not->toBeNull();
});

test('both portals archive returned copies and restore the same accession without losing history', function (string $role) {
    $actor = bulkCopyTestUser($role);
    $book = bulkCopyTestBook();
    $service = app(PhysicalBookCopyService::class);
    $copies = $service->createCopies($book, 2);
    $copy = $copies[0];
    $student = bulkCopyTestStudent();
    $issue = $service->issue($student, $copy->accession_number, $actor);
    $service->return($copy->accession_number);

    $this->actingAs($actor)->get(route("{$role}.books.copies.index", $book))
        ->assertOk()->assertSee('Archive Selected')->assertSee('archive-copy-btn', false);

    $this->postJson(route("{$role}.book-copies.archive", $copy), ['reason' => 'Removed from library'])
        ->assertOk()->assertJsonPath('data.archived_count', 1)
        ->assertJsonPath('data.summary.total', 1)
        ->assertJsonPath('data.summary.available', 1)
        ->assertJsonPath('data.summary.archived', 1);

    $archived = BookCopy::onlyTrashed()->findOrFail($copy->id);
    expect($archived->archived_by)->toBe($actor->id)
        ->and($archived->archive_reason)->toBe('Removed from library')
        ->and($book->fresh()->total_copies)->toBe(1)
        ->and($book->fresh()->available_copies)->toBe(1)
        ->and($issue->fresh()->book_copy_id)->toBe($copy->id)
        ->and($issue->fresh()->bookCopy->accession_number)->toBe($copy->accession_number)
        ->and($issue->fresh()->return_date)->not->toBeNull()
        ->and($service->findByAccession($copy->accession_number))->toBeNull()
        ->and($service->issueEligibility($archived)?->getMessage())->toContain('archived');

    expect(fn () => $service->issue($student, $copy->accession_number, $actor))
        ->toThrow(PhysicalCopyException::class, 'Accession number not found.');
    expect(fn () => $service->issueById($student, $copy->id, $actor))
        ->toThrow(PhysicalCopyException::class, 'Book copy not found.');

    $this->get(route("{$role}.books.copies.index", $book))
        ->assertOk()->assertDontSee($copy->accession_number)->assertSee($copies[1]->accession_number);
    $this->get(route("{$role}.books.copies.index", [$book, 'inventory' => 'archived']))
        ->assertOk()->assertSee($copy->accession_number)->assertSee('Removed from library')
        ->assertSee('Restore')->assertDontSee('class="book-copies-action edit-copy-btn"', false);
    $this->getJson(route("{$role}.books.copies.index", [$book, 'inventory' => 'archived', 'ajax_copies' => 1]))
        ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('summary.archived', 1);
    $this->getJson(route("{$role}.books.copies.index", $book))
        ->assertOk()->assertJsonCount(1, 'data.copies');
    $this->putJson(route("{$role}.book-copies.update", $copy), ['remarks' => 'Should not change'])
        ->assertNotFound();
    $this->deleteJson(route("{$role}.book-copies.destroy", $copy))->assertNotFound();

    $this->postJson(route("{$role}.book-copies.restore", $copy))
        ->assertOk()->assertJsonPath('data.copy.id', $copy->id)
        ->assertJsonPath('data.copy.accession_number', $copy->accession_number)
        ->assertJsonPath('data.summary.total', 2)->assertJsonPath('data.summary.archived', 0);
    expect($copy->fresh()->archived_at)->toBeNull()
        ->and($copy->fresh()->archive_reason)->toBeNull()
        ->and($copy->fresh()->issuedBooks()->count())->toBe(1)
        ->and(IssuedBook::findOrFail($issue->id)->book_copy_id)->toBe($copy->id);
    $this->postJson(route("{$role}.book-copies.restore", $copy))->assertStatus(409);
    expect($book->fresh()->total_copies)->toBe(2);

    $archiveLog = ActivityLog::query()->where('action', 'copies_archived')->latest('id')->firstOrFail();
    $restoreLog = ActivityLog::query()->where('action', 'copy_restored')->latest('id')->firstOrFail();
    expect($archiveLog->user_id)->toBe($actor->id)
        ->and($archiveLog->metadata['reason'])->toBe('Removed from library')
        ->and($restoreLog->user_id)->toBe($actor->id);
})->with(['admin', 'staff']);

test('both portals recheck borrowed copies after archive preview and isolate bulk changes to this book', function (string $role) {
    $actor = bulkCopyTestUser($role);
    $book = bulkCopyTestBook();
    $otherBook = bulkCopyTestBook('Other');
    $service = app(PhysicalBookCopyService::class);
    $copies = $service->createCopies($book, 3);
    $otherCopy = $service->createCopies($otherBook, 1)->first();
    $student = bulkCopyTestStudent();
    $service->issue($student, $copies[0]->accession_number, $actor);
    $service->return($copies[0]->accession_number);
    $ids = [...$copies->pluck('id')->all(), $otherCopy->id];

    $this->actingAs($actor)->postJson(route("{$role}.books.copies.archive-preview", $book), ['copy_ids' => $ids])
        ->assertOk()->assertJsonPath('data.eligible_count', 3)->assertJsonPath('data.invalid_count', 1);
    $service->issue($student, $copies[1]->accession_number, $actor);
    // A stale inventory flag must never override an actual active loan.
    $copies[1]->update(['status' => 'available']);
    $this->postJson(route("{$role}.book-copies.archive", $copies[1]))->assertStatus(409);

    $this->postJson(route("{$role}.books.copies.bulk-archive", $book), ['copy_ids' => $ids])
        ->assertOk()->assertJsonPath('data.archived_count', 2)
        ->assertJsonPath('data.borrowed_count', 1)->assertJsonPath('data.invalid_count', 1)
        ->assertJsonPath('data.skipped_count', 2)->assertJsonPath('data.summary.total', 1)
        ->assertJsonPath('data.summary.archived', 2);

    expect(BookCopy::onlyTrashed()->whereKey($copies[0]->id)->exists())->toBeTrue()
        ->and(BookCopy::onlyTrashed()->whereKey($copies[2]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($copies[1]->id)->exists())->toBeTrue()
        ->and(BookCopy::query()->whereKey($otherCopy->id)->exists())->toBeTrue()
        ->and(IssuedBook::query()->where('book_copy_id', $copies[0]->id)->count())->toBe(1);
})->with(['admin', 'staff']);

test('restoring archived copies preserves lost damaged and reference restrictions', function (string $condition, string $bookType, string $status, bool $borrowable) {
    $staff = bulkCopyTestUser('staff');
    $book = bulkCopyTestBook();
    $service = app(PhysicalBookCopyService::class);
    $copy = $service->createCopies($book, 1, ['condition' => $condition, 'book_type' => $bookType])->first();
    $copy->update(['status' => $status]);

    $this->actingAs($staff)->postJson(route('staff.book-copies.archive', $copy))->assertOk();
    expect($book->fresh()->total_copies)->toBe(0)->and($book->fresh()->available_copies)->toBe(0);
    expect(Book::availableForBorrowing()->whereKey($book->id)->exists())->toBeFalse();
    // Archived physical inventory must not become a legacy title when counters drift.
    $book->forceFill(['total_copies' => 99, 'available_copies' => 99])->saveQuietly();
    expect(Book::availableForBorrowing()->whereKey($book->id)->exists())->toBeFalse()
        ->and($book->fresh()->availableForBorrowingCount())->toBe(0)
        ->and(Book::withCirculationAvailability()->findOrFail($book->id)->availableForBorrowingCount())->toBe(0);
    $this->postJson(route('staff.book-copies.restore', $copy))
        ->assertOk()->assertJsonPath('data.copy.condition', $condition)->assertJsonPath('data.copy.status', $status);
    expect(BookCopy::availableForIssue()->whereKey($copy->id)->exists())->toBe($borrowable);
})->with([
    ['good', 'borrowing', 'available', true],
    ['damaged', 'borrowing', 'damaged', false],
    ['lost', 'borrowing', 'lost', false],
    ['good', 'reference', 'available', false],
]);

test('archive endpoints validate input and deny students in both portals', function (string $role) {
    $actor = bulkCopyTestUser($role);
    $book = bulkCopyTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    $this->actingAs($actor)
        ->postJson(route("{$role}.books.copies.bulk-archive", $book), [])->assertUnprocessable();
    $this->postJson(route("{$role}.books.copies.bulk-archive", $book), ['copy_ids' => [$copy->id, $copy->id]])
        ->assertUnprocessable();
    $this->postJson(route("{$role}.book-copies.archive", $copy), ['reason' => str_repeat('x', 501)])
        ->assertUnprocessable();

    $this->actingAs(bulkCopyTestUser('student'));
    $this->postJson(route("{$role}.books.copies.archive-preview", $book), ['copy_ids' => [$copy->id]])->assertForbidden();
    $this->postJson(route("{$role}.books.copies.bulk-archive", $book), ['copy_ids' => [$copy->id]])->assertForbidden();
    $this->postJson(route("{$role}.book-copies.archive", $copy))->assertForbidden();
    $this->postJson(route("{$role}.book-copies.restore", $copy))->assertForbidden();
    expect(BookCopy::query()->whereKey($copy->id)->exists())->toBeTrue();
})->with(['admin', 'staff']);

test('deleting an unused copy remains permanent rather than archiving it', function () {
    $admin = bulkCopyTestUser('admin');
    $book = bulkCopyTestBook();
    $copy = app(PhysicalBookCopyService::class)->createCopies($book, 1)->first();
    $this->actingAs($admin)->deleteJson(route('admin.book-copies.destroy', $copy))->assertOk();
    expect(BookCopy::withTrashed()->whereKey($copy->id)->exists())->toBeFalse();
});
