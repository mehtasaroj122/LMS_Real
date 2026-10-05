<?php

use App\Exceptions\PhysicalCopyException;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\PhysicalBookCopyService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
