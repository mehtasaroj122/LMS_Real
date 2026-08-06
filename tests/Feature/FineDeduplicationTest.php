<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeduplicatesFineRecordsHarness
{
    use App\Services\Concerns\DeduplicatesFineRecords;

    public function collapse(Collection $fines): Collection
    {
        return $this->collapseDuplicateFineRecords($fines);
    }

    public function distinctCount(Builder $query): int
    {
        return $this->distinctIssuedBookCount($query);
    }

    public function collapseQuery(Builder $query): Collection
    {
        return $this->collapseFineRecordsQuery($query);
    }
}

function makeInMemoryFine(array $attributes, int $id, ?Carbon $updatedAt = null): Fine
{
    $fine = new Fine($attributes);
    $fine->id = $id;

    if ($updatedAt !== null) {
        $fine->updated_at = $updatedAt;
    }

    return $fine;
}

function makeFineDedupStudent(): Student
{
    $unique = Str::lower(Str::random(8));

    $user = User::forceCreate([
        'role' => 'student',
        'name' => 'Fine Dedup Student ' . $unique,
        'email' => "fine-dedup-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $department = Department::create([
        'name' => 'Fine Dedup Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'student_id' => 'FDD-' . Str::upper(substr($unique, 0, 6)),
        'roll_no' => 'ROLL-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Kathmandu',
    ]);
}

function makeFineDedupIssuedBook(Student $student): IssuedBook
{
    $unique = Str::lower(Str::random(8));
    $category = Category::create([
        'name' => 'Fine Dedup Category ' . $unique,
        'description' => 'Test category',
    ]);

    $book = Book::create([
        'category_id' => $category->id,
        'title' => 'Fine Dedup Book ' . $unique,
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => 'ISBN-FDD-' . Str::upper($unique),
        'total_copies' => 5,
        'available_copies' => 4,
        'condition' => 'good',
        'status' => 'available',
    ]);

    return IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issue_date' => now()->subDays(20)->toDateString(),
        'due_date' => now()->subDays(5)->toDateString(),
        'return_date' => null,
        'status' => 'overdue',
        'condition' => 'good',
        'fine_amount' => 0,
    ]);
}

test('collapse keeps a paid record over a pending duplicate for the same issued book', function () {
    $harness = new DeduplicatesFineRecordsHarness();

    $fines = collect([
        makeInMemoryFine([
            'issued_book_id' => 10,
            'student_id' => 1,
            'amount' => 150,
            'days_late' => 10,
            'status' => 'pending',
        ], 1, Carbon::parse('2026-08-05 10:00:00')),
        makeInMemoryFine([
            'issued_book_id' => 10,
            'student_id' => 1,
            'amount' => 80,
            'days_late' => 4,
            'status' => 'paid',
        ], 2, Carbon::parse('2026-08-01 10:00:00')),
    ]);

    $collapsed = $harness->collapse($fines);

    expect($collapsed)->toHaveCount(1)
        ->and($collapsed->first()->id)->toBe(2)
        ->and($collapsed->first()->status)->toBe('paid')
        ->and((float) $collapsed->first()->amount)->toBe(80.0);
});

test('collapse keeps a waived record over a pending duplicate for the same issued book', function () {
    $harness = new DeduplicatesFineRecordsHarness();

    $fines = collect([
        makeInMemoryFine([
            'issued_book_id' => 11,
            'student_id' => 1,
            'amount' => 60,
            'days_late' => 2,
            'status' => 'waived',
        ], 1, Carbon::parse('2026-08-05 10:00:00')),
        makeInMemoryFine([
            'issued_book_id' => 11,
            'student_id' => 1,
            'amount' => 200,
            'days_late' => 12,
            'status' => 'pending',
        ], 2, Carbon::parse('2026-08-06 10:00:00')),
    ]);

    $collapsed = $harness->collapse($fines);

    expect($collapsed)->toHaveCount(1)
        ->and($collapsed->first()->id)->toBe(1)
        ->and($collapsed->first()->status)->toBe('waived');
});

test('collapse keeps the higher amount among pending duplicates for the same issued book', function () {
    $harness = new DeduplicatesFineRecordsHarness();

    $fines = collect([
        makeInMemoryFine([
            'issued_book_id' => 12,
            'student_id' => 1,
            'amount' => 50,
            'days_late' => 5,
            'status' => 'pending',
        ], 1, Carbon::parse('2026-08-01 10:00:00')),
        makeInMemoryFine([
            'issued_book_id' => 12,
            'student_id' => 1,
            'amount' => 120,
            'days_late' => 5,
            'status' => 'pending',
        ], 2, Carbon::parse('2026-08-02 10:00:00')),
    ]);

    $collapsed = $harness->collapse($fines);

    expect($collapsed)->toHaveCount(1)
        ->and($collapsed->first()->id)->toBe(2)
        ->and((float) $collapsed->first()->amount)->toBe(120.0);
});

test('collapse keeps the most recently updated record when amount and status tie', function () {
    $harness = new DeduplicatesFineRecordsHarness();

    $fines = collect([
        makeInMemoryFine([
            'issued_book_id' => 13,
            'student_id' => 1,
            'amount' => 90,
            'days_late' => 6,
            'status' => 'pending',
        ], 1, Carbon::parse('2026-08-01 10:00:00')),
        makeInMemoryFine([
            'issued_book_id' => 13,
            'student_id' => 1,
            'amount' => 90,
            'days_late' => 6,
            'status' => 'pending',
        ], 2, Carbon::parse('2026-08-09 10:00:00')),
    ]);

    $collapsed = $harness->collapse($fines);

    expect($collapsed)->toHaveCount(1)
        ->and($collapsed->first()->id)->toBe(2);
});

test('collapse never merges fines belonging to different issued books', function () {
    $harness = new DeduplicatesFineRecordsHarness();

    $fines = collect([
        makeInMemoryFine([
            'issued_book_id' => 20,
            'student_id' => 1,
            'amount' => 100,
            'days_late' => 5,
            'status' => 'pending',
        ], 1),
        makeInMemoryFine([
            'issued_book_id' => 21,
            'student_id' => 1,
            'amount' => 100,
            'days_late' => 5,
            'status' => 'pending',
        ], 2),
        makeInMemoryFine([
            'issued_book_id' => 22,
            'student_id' => 1,
            'amount' => 100,
            'days_late' => 5,
            'status' => 'paid',
        ], 3),
    ]);

    $collapsed = $harness->collapse($fines);

    expect($collapsed)->toHaveCount(3);
});

test('distinct issued book count reflects one record per issued book', function () {
    $student = makeFineDedupStudent();
    $firstIssue = makeFineDedupIssuedBook($student);
    $secondIssue = makeFineDedupIssuedBook($student);

    Fine::create([
        'issued_book_id' => $firstIssue->id,
        'student_id' => $student->id,
        'amount' => 50,
        'days_late' => 3,
        'status' => 'pending',
    ]);
    Fine::create([
        'issued_book_id' => $secondIssue->id,
        'student_id' => $student->id,
        'amount' => 75,
        'days_late' => 5,
        'status' => 'pending',
    ]);

    $harness = new DeduplicatesFineRecordsHarness();

    expect($harness->distinctCount(Fine::query()))->toBe(2);
});

test('collapse fine query collapses persisted rows one per issued book', function () {
    $student = makeFineDedupStudent();
    $firstIssue = makeFineDedupIssuedBook($student);
    $secondIssue = makeFineDedupIssuedBook($student);

    Fine::create([
        'issued_book_id' => $firstIssue->id,
        'student_id' => $student->id,
        'amount' => 50,
        'days_late' => 3,
        'status' => 'pending',
    ]);
    Fine::create([
        'issued_book_id' => $secondIssue->id,
        'student_id' => $student->id,
        'amount' => 75,
        'days_late' => 5,
        'status' => 'paid',
    ]);

    $harness = new DeduplicatesFineRecordsHarness();
    $collapsed = $harness->collapseQuery(Fine::query());

    expect($collapsed)->toHaveCount(2)
        ->and((float) $collapsed->sum('amount'))->toBe(125.0)
        ->and($collapsed->pluck('status')->sort()->values()->all())->toBe(['paid', 'pending']);
});
