<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\PhysicalBookCopyService;
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
