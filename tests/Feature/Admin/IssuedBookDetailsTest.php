<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('student transaction details preserve backend values and expose the assigned physical copy', function (string $status, ?string $fineStatus) {
    // Console tests do not run the HTTP-only branding view share.
    Illuminate\Support\Facades\View::share('libraryBranding', App\Support\LibraryBranding::resolve());
    $this->travelTo(now()->setDate(2026, 10, 6)->startOfDay());
    $admin = User::factory()->create(['role' => 'admin']);
    $borrower = User::factory()->create(['role' => 'student']);
    $department = Department::create(['name' => 'Chemistry', 'code' => 'CHEM']);
    $student = Student::create([
        'user_id' => $borrower->id, 'department_id' => $department->id,
        'roll_no' => 'CHEM-2023-070', 'semester' => '6',
    ]);
    $category = Category::create(['name' => 'Software Engineering']);
    $book = Book::create([
        'category_id' => $category->id, 'title' => 'High Performance Browser Networking',
        'author' => 'Ilya Grigorik', 'publisher' => "O'Reilly", 'isbn' => '0001000032',
        'description' => "First line\n<script>untrusted description</script>",
        'total_copies' => 2, 'available_copies' => 1, 'condition' => 'good',
    ]);
    $copy = BookCopy::create([
        'book_id' => $book->id, 'accession_number' => 'ACC-000033',
        'condition' => 'fair', 'status' => $status === 'returned' ? 'available' : 'issued',
        'shelf_location' => 'RACK-A2', 'book_type' => 'borrowing',
    ]);
    $issue = IssuedBook::withoutEvents(fn () => IssuedBook::create([
        'book_id' => $book->id, 'book_copy_id' => $copy->id, 'student_id' => $student->id,
        'issued_by' => $admin->id, 'condition' => 'good', 'status' => $status,
        'issue_date' => '2026-09-01', 'due_date' => $status === 'issued' ? '2026-10-16' : '2026-09-15',
        'return_date' => $status === 'returned' ? '2026-09-18' : null,
    ]));
    $fine = $fineStatus ? Fine::withoutEvents(fn () => Fine::create([
        'issued_book_id' => $issue->id, 'student_id' => $student->id,
        'amount' => 500, 'days_late' => 3, 'status' => $fineStatus,
        'payment_method' => $fineStatus === 'paid' ? 'cash' : null,
        'paid_on' => $fineStatus === 'paid' ? '2026-09-19' : null,
        'waived_at' => $fineStatus === 'waived' ? '2026-09-20 10:00:00' : null,
    ])) : null;

    // A second transaction without a copy must not inherit the first copy's data.
    $withoutCopy = IssuedBook::withoutEvents(fn () => IssuedBook::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'status' => 'issued',
        'issue_date' => '2026-10-01', 'due_date' => '2026-10-16',
    ]));
    $issueBefore = $issue->fresh()->getAttributes();
    $fineBefore = $fine?->fresh()->getAttributes();
    DB::enableQueryLog();
    $response = $this->actingAs($admin)->get(route('admin.students.show', $student))->assertOk();
    $copyQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'from "book_copies"'));
    DB::disableQueryLog();
    expect($copyQueries)->toHaveCount(1); // Copy details are eager loaded once, independent of row count.
    $data = collect($response->viewData('booksData'))->keyBy('id');
    $details = $data[$issue->id];
    expect($details['accessionNumber'])->toBe('ACC-000033')
        ->and($details['copyCondition'])->toBe('fair')
        ->and($details['condition'])->toBe('good')
        ->and($details['copyStatus'])->toBe($copy->status)
        ->and($details['copyShelf'])->toBe('RACK-A2')
        ->and($details['transactionId'])->toBe('TXN-' . str_pad((string) $issue->id, 6, '0', STR_PAD_LEFT))
        ->and($details['issueDate'])->toBe('Sep 01, 2026')
        ->and($details['dueDate'])->toBe($status === 'issued' ? 'Oct 16, 2026' : 'Sep 15, 2026')
        ->and($details['returnDate'])->toBe($status === 'returned' ? 'Sep 18, 2026' : '-')
        ->and($details['status'])->toBe($status)
        ->and($details['daysOverdue'])->toBe($status === 'overdue' ? 21 : 0)
        ->and($details['issuedBy'])->toBe($admin->name)
        ->and($details['renewalCount'])->toBe(0)
        ->and($details['fine'])->toBe($fine ? 500.0 : 0.0)
        ->and($details['hasFine'])->toBe((bool) $fine)
        ->and($details['fineStatus'])->toBe($fineStatus === 'pending' ? 'unpaid' : ($fineStatus ?? 'none'))
        ->and($details['finePaymentMethod'])->toBe($fineStatus === 'paid' ? 'cash' : null)
        ->and($details['finePaidDate'])->toBe($fineStatus === 'paid' ? 'Sep 19, 2026' : null)
        ->and($details['fineWaivedDate'])->toBe($fineStatus === 'waived' ? 'Sep 20, 2026' : null)
        ->and($data[$withoutCopy->id]['accessionNumber'])->toBeNull()
        ->and($data[$withoutCopy->id]['copyCondition'])->toBeNull()
        ->and($data[$withoutCopy->id]['copyStatus'])->toBeNull()
        ->and($data[$withoutCopy->id]['copyShelf'])->toBeNull();
    expect($issue->fresh()->getAttributes())->toBe($issueBefore);
    if ($fine) {
        expect($fine->fresh()->getAttributes())->toBe($fineBefore);
    }
})->with([
    'overdue with pending fine' => ['overdue', 'pending'],
    'returned with paid fine' => ['returned', 'paid'],
    'returned with waived fine' => ['returned', 'waived'],
    'issued without fine' => ['issued', null],
]);
