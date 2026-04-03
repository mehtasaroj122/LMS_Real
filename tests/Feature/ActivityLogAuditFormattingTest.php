<?php

use App\Models\ActivityLog;
use App\Models\BookRequest;
use App\Models\book as Book;
use App\Models\category as Category;
use App\Models\department as Department;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Models\Fine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function makeAuditRoleUser(string $role, array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => $role,
        'name' => ucfirst($role) . ' User ' . $unique,
        'email' => "{$role}-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeAuditStudentRecord(string $studentName = 'Student User'): Student
{
    $unique = Str::lower(Str::random(8));
    $studentUser = makeAuditRoleUser('student', [
        'name' => $studentName,
        'email' => "student-{$unique}@example.com",
    ]);

    $department = Department::create([
        'name' => 'Department ' . $unique,
        'code' => Str::upper(substr($unique, 0, 4)),
        'status' => 'active',
    ]);

    return Student::create([
        'user_id' => $studentUser->id,
        'department_id' => $department->id,
        'roll_no' => 'ROLL-' . Str::upper(substr($unique, 0, 6)),
        'batch' => '2026',
        'semester' => '6',
        'address' => 'Pokhara, Nepal',
    ]);
}

function makeAuditBookRecord(string $title = 'Sample Book'): Book
{
    $unique = Str::lower(Str::random(8));

    $category = Category::create([
        'name' => 'Category ' . $unique,
        'description' => 'Audit activity test category',
    ]);

    return Book::create([
        'category_id' => $category->id,
        'title' => $title,
        'author' => 'Author ' . $unique,
        'publisher' => 'Publisher ' . $unique,
        'isbn' => 'ISBN-' . Str::upper($unique),
        'total_copies' => 4,
        'available_copies' => 2,
        'condition' => 'good',
        'description' => 'Audit activity test book',
        'shelf_no' => 'S-' . Str::upper(substr($unique, 0, 4)),
        'status' => 'available',
    ]);
}

test('staff issue flow writes one issue log and one request-issued log with names', function () {
    Queue::fake();

    $staff = makeAuditRoleUser('staff', [
        'name' => 'Anil Kapoor',
        'email' => 'anil.kapoor@example.com',
    ]);

    $student = makeAuditStudentRecord('Riya Sharma');
    $book = makeAuditBookRecord('The Odyssey');

    BookRequest::create([
        'student_id' => $student->id,
        'book_id' => $book->id,
        'request_date' => now()->subDay(),
        'status' => 'approved',
        'processed_by' => 'Anil Kapoor',
        'processed_date' => now()->subHour(),
    ]);

    $this
        ->actingAs($staff)
        ->postJson(route('staff.transactions.issue'), [
            'student_id' => $student->id,
            'book_ids' => [$book->id],
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'book_issued')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'book_issued')->value('description'))
        ->toBe("Book 'The Odyssey' issued to Riya Sharma by Anil Kapoor");

    expect(ActivityLog::query()->where('action', 'book_request_issued')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'book_request_issued')->value('description'))
        ->toBe("Book 'The Odyssey' request marked as issued for Riya Sharma by Anil Kapoor");
});

test('staff return flow writes one return log and one request-returned log with names', function () {
    Queue::fake();

    $staff = makeAuditRoleUser('staff', [
        'name' => 'Anil Kapoor',
        'email' => 'anil.return@example.com',
    ]);

    $student = makeAuditStudentRecord('Riya Sharma');
    $book = makeAuditBookRecord('The Odyssey');

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $staff->id,
        'issue_date' => Carbon::now()->subDay(),
        'due_date' => Carbon::now()->addDays(7),
        'status' => 'issued',
    ]);

    BookRequest::create([
        'student_id' => $student->id,
        'book_id' => $book->id,
        'request_date' => now()->subDays(2),
        'status' => 'issued',
        'processed_by' => 'Anil Kapoor',
        'processed_date' => now()->subDay(),
    ]);

    ActivityLog::query()->delete();

    $this
        ->actingAs($staff)
        ->postJson(route('staff.transactions.return'), [
            'student_id' => $student->id,
            'issued_book_ids' => [$issuedBook->id],
            'condition' => 'good',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'book_returned')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'book_returned')->value('description'))
        ->toBe("Book 'The Odyssey' returned by Riya Sharma to Anil Kapoor");

    expect(ActivityLog::query()->where('action', 'book_request_returned')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'book_request_returned')->value('description'))
        ->toBe("Book 'The Odyssey' request marked as returned for Riya Sharma by Anil Kapoor");
});

test('request approval and rejection logs include the book, student, and actor names', function () {
    Queue::fake();

    $admin = makeAuditRoleUser('admin', [
        'name' => 'Saroj Mehta Arya',
        'email' => 'saroj.audit@example.com',
    ]);

    $approvedStudent = makeAuditStudentRecord('Aarav Gautam');
    $rejectedStudent = makeAuditStudentRecord('Puja Bista');
    $approvedBook = makeAuditBookRecord('Clean Code');
    $rejectedBook = makeAuditBookRecord('The Pragmatic Programmer');

    $approvedRequest = BookRequest::create([
        'student_id' => $approvedStudent->id,
        'book_id' => $approvedBook->id,
        'request_date' => now()->subDay(),
        'status' => 'pending',
    ]);

    $rejectedRequest = BookRequest::create([
        'student_id' => $rejectedStudent->id,
        'book_id' => $rejectedBook->id,
        'request_date' => now()->subDay(),
        'status' => 'pending',
    ]);

    $this
        ->actingAs($admin)
        ->putJson(route('admin.book-requests.update', $approvedRequest->id), [
            'status' => 'approved',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this
        ->actingAs($admin)
        ->putJson(route('admin.book-requests.update', $rejectedRequest->id), [
            'status' => 'rejected',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'book_request_approved')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'book_request_approved')->value('description'))
        ->toBe("Book 'Clean Code' request accepted for Aarav Gautam by Saroj Mehta Arya");

    expect(ActivityLog::query()->where('action', 'book_request_rejected')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'book_request_rejected')->value('description'))
        ->toBe("Book 'The Pragmatic Programmer' request rejected for Puja Bista by Saroj Mehta Arya");
});

test('fine applied log includes student name with roll number', function () {
    $staff = makeAuditRoleUser('staff', [
        'name' => 'Anil Kapoor',
        'email' => 'anil.fine@example.com',
    ]);

    $student = makeAuditStudentRecord('Riya Sharma');
    $book = makeAuditBookRecord('Strength of Materials');

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $staff->id,
        'issue_date' => Carbon::now()->subDays(10),
        'due_date' => Carbon::now()->subDays(2),
        'status' => 'issued',
    ]);

    ActivityLog::query()->delete();

    $this->actingAs($staff);

    Fine::create([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => 50,
        'days_late' => 2,
        'status' => 'pending',
        'remarks' => 'Overdue fine',
    ]);

    expect(ActivityLog::query()->where('action', 'fine_applied')->count())->toBe(1);
    expect(ActivityLog::query()->where('action', 'fine_applied')->value('description'))
        ->toBe("Fine of ₹50 applied for 'Strength of Materials' (ISBN: {$book->isbn}) to Riya Sharma ({$student->roll_no})");
});

test('fine paid and fine payment logs include book isbn and student label', function () {
    $staff = makeAuditRoleUser('staff', [
        'name' => 'Anil Kapoor',
        'email' => 'anil.finepaid@example.com',
    ]);

    $student = makeAuditStudentRecord('Saroj Mehta');
    $book = makeAuditBookRecord('Natural Language Processing with Python');

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $staff->id,
        'issue_date' => Carbon::now()->subDays(10),
        'due_date' => Carbon::now()->subDays(2),
        'status' => 'issued',
    ]);

    $fine = Fine::create([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => 30,
        'days_late' => 2,
        'status' => 'pending',
        'payment_method' => 'cash',
    ]);

    $this
        ->actingAs($staff)
        ->postJson(route('staff.fines.mark-as-paid', $fine))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'fine_payment')->value('description'))
        ->toBe("Fine payment of ₹30 for 'Natural Language Processing with Python' (ISBN: {$book->isbn}) processed for Saroj Mehta ({$student->roll_no})");

    expect(ActivityLog::query()->where('action', 'fine_paid')->value('description'))
        ->toBe("Fine of ₹30 for 'Natural Language Processing with Python' (ISBN: {$book->isbn}) marked as paid for Saroj Mehta ({$student->roll_no}) via cash");
});

test('fine waived log includes book isbn, student label, and reason', function () {
    $admin = makeAuditRoleUser('admin', [
        'name' => 'Saroj Mehta Arya',
        'email' => 'saroj.finewaive@example.com',
    ]);

    $student = makeAuditStudentRecord('Riya Sharma');
    $book = makeAuditBookRecord('Strength of Materials');

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $admin->id,
        'issue_date' => Carbon::now()->subDays(8),
        'due_date' => Carbon::now()->subDays(3),
        'status' => 'issued',
    ]);

    $fine = Fine::create([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => 15,
        'days_late' => 3,
        'status' => 'pending',
    ]);

    $this
        ->actingAs($admin)
        ->postJson(route('admin.fines.waive', $fine), [
            'reason' => 'Approved by admin after review',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'fine_waived')->value('description'))
        ->toBe("Fine of ₹15 for 'Strength of Materials' (ISBN: {$book->isbn}) waived for Riya Sharma ({$student->roll_no}). Reason: Approved by admin after review");
});

test('fine adjusted log includes book isbn and student label', function () {
    $admin = makeAuditRoleUser('admin', [
        'name' => 'Saroj Mehta Arya',
        'email' => 'saroj.fineadjust@example.com',
    ]);

    $student = makeAuditStudentRecord('Aarav Gautam');
    $book = makeAuditBookRecord('HTTP-2 in Action');

    $issuedBook = IssuedBook::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_by' => $admin->id,
        'issue_date' => Carbon::now()->subDays(5),
        'due_date' => Carbon::now()->subDays(1),
        'status' => 'issued',
    ]);

    $fine = Fine::create([
        'issued_book_id' => $issuedBook->id,
        'student_id' => $student->id,
        'amount' => 20,
        'days_late' => 1,
        'status' => 'pending',
    ]);

    $this
        ->actingAs($admin)
        ->postJson(route('admin.fines.adjust', $fine), [
            'action' => 'adjust',
            'amount' => 35,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(ActivityLog::query()->where('action', 'fine_adjusted')->value('description'))
        ->toBe("Fine for 'HTTP-2 in Action' (ISBN: {$book->isbn}) adjusted for Aarav Gautam ({$student->roll_no}) from ₹20 to ₹35");
});
