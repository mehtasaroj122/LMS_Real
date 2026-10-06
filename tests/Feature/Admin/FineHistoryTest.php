<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;

test('fine history exposes existing identity and settlement data without changing amounts or audit records', function (string $status) {
    $admin = User::factory()->create(['role' => 'admin']);
    $staff = User::factory()->create(['role' => 'staff', 'name' => 'Reviewing Staff']);
    $borrower = User::factory()->create(['role' => 'student']);
    $department = Department::create(['name' => 'History', 'code' => 'HIST']);
    $student = Student::create([
        'user_id' => $borrower->id, 'department_id' => $department->id,
        'roll_no' => 'HIST-001', 'semester' => '6',
    ]);
    FineSetting::create(['per_day_fine' => 3.5, 'is_active' => true]);
    $category = Category::create(['name' => 'Computing']);
    $book = Book::create([
        'category_id' => $category->id, 'title' => 'HTTP/2 in Action', 'isbn' => '0001000033',
        'author' => 'Test Author', 'total_copies' => 2, 'available_copies' => 1,
    ]);
    $copy = BookCopy::create([
        'book_id' => $book->id, 'accession_number' => 'ACC-HISTORY-001',
        'condition' => 'good', 'status' => 'issued', 'book_type' => 'borrowing',
    ]);
    $issue = IssuedBook::withoutEvents(fn () => IssuedBook::create([
        'book_id' => $book->id, 'book_copy_id' => $copy->id, 'student_id' => $student->id,
        'issue_date' => '2026-09-01', 'due_date' => '2026-09-19', 'status' => 'overdue',
    ]));
    $fine = Fine::withoutEvents(fn () => Fine::create([
        'issued_book_id' => $issue->id, 'student_id' => $student->id,
        'amount' => 35, 'days_late' => 17, 'status' => $status,
        'paid_at' => $status === 'paid' ? '2026-10-06 11:00:00' : null,
        'paid_on' => $status === 'paid' ? '2026-10-06' : null,
        'paid_by' => $status === 'paid' ? $admin->id : null,
        'payment_method' => $status === 'paid' ? 'card' : null,
        'waived_at' => $status === 'waived' ? '2026-10-06 11:00:00' : null,
        'waived_by' => $status === 'waived' ? $admin->id : null,
        'waive_reason' => $status === 'waived' ? 'Approved administrative waiver.' : null,
    ]));
    foreach ([
        ['fine_applied', '2026-10-04 09:00:00', ['new_amount' => 29]],
        ['fine_adjusted', '2026-10-06 10:02:00', ['old_amount' => 29, 'new_amount' => 35, 'amount_change' => 6, 'remarks' => 'Adjusted after review.']],
        // Another fine belonging to the same student must never enter this history.
        ['fine_adjusted', '2026-10-06 10:03:00', ['fine_id' => $fine->id + 100, 'issued_book_id' => $issue->id + 100, 'new_amount' => 999]],
    ] as [$action, $date, $metadata]) {
        ActivityLog::forceCreate([
            'user_id' => $staff->id, 'user_name' => $staff->name, 'user_role' => 'staff',
            'resource_type' => 'student', 'resource_id' => $student->id,
            'action_category' => 'fine', 'action' => $action, 'description' => 'Recorded fine action',
            'metadata' => array_merge(['fine_id' => $fine->id, 'issued_book_id' => $issue->id], $metadata),
            'created_at' => $date,
        ]);
    }
    $before = $fine->fresh()->getAttributes();
    $logsBefore = ActivityLog::all()->toArray();
    $response = $this->actingAs($admin)->getJson(route('admin.fines.history', $fine))->assertOk();
    $response->assertJsonPath('fineDetails.id', $fine->id)
        ->assertJsonPath('fineDetails.accessionNumber', 'ACC-HISTORY-001')
        ->assertJsonPath('fineDetails.transactionId', 'TXN-' . str_pad((string) $issue->id, 6, '0', STR_PAD_LEFT))
        ->assertJsonPath('fineDetails.bookTitle', 'HTTP/2 in Action')
        ->assertJsonPath('fineDetails.isbn', '0001000033')
        ->assertJsonPath('fineDetails.status', $status)
        ->assertJsonPath('fineDetails.daysLate', 17)
        ->assertJsonPath('calculation.baseRate', 3.5)
        ->assertJsonCount(2, 'history')
        ->assertJsonPath('history.0.actionType', 'adjusted')
        ->assertJsonPath('history.0.user', 'Reviewing Staff')
        ->assertJsonPath('history.0.userRole', 'staff')
        ->assertJsonPath('history.0.remarks', 'Adjusted after review.')
        ->assertJsonPath('history.0.date', 'Oct 06, 2026 10:02 AM')
        ->assertJsonPath('history.1.actionType', 'created');
    expect((float) $response->json('fineDetails.originalAmount'))->toBe(29.0)
        ->and((float) $response->json('fineDetails.currentAmount'))->toBe(35.0)
        ->and((float) $response->json('calculation.subtotal'))->toBe(29.0)
        ->and((float) $response->json('calculation.adjustments'))->toBe(6.0)
        ->and((float) $response->json('calculation.finalAmount'))->toBe(35.0)
        ->and((float) $response->json('history.0.amountChange'))->toBe(6.0);
    if ($status === 'paid') {
        $response->assertJsonPath('paymentDetails.recordedBy', $admin->name)
            ->assertJsonPath('paymentDetails.method', 'card')
            ->assertJsonPath('paymentDetails.date', 'Oct 06, 2026 11:00 AM')
            ->assertJsonPath('paymentDetails.outstanding', 0)
            ->assertJsonPath('waiverDetails', null);
    } elseif ($status === 'waived') {
        $response->assertJsonPath('waiverDetails.waivedBy', $admin->name)
            ->assertJsonPath('waiverDetails.date', 'Oct 06, 2026 11:00 AM')
            ->assertJsonPath('waiverDetails.reason', 'Approved administrative waiver.')
            ->assertJsonPath('paymentDetails', null);
    } else {
        $response->assertJsonPath('paymentDetails', null)->assertJsonPath('waiverDetails', null);
    }
    expect($fine->fresh()->getAttributes())->toBe($before)
        ->and(ActivityLog::all()->toArray())->toBe($logsBefore);

    $issue->update(['book_copy_id' => null]);
    $this->getJson(route('admin.fines.history', $fine))->assertOk()->assertJsonPath('fineDetails.accessionNumber', null);
    if ($status === 'waived') {
        // Older records may keep the waiver reason in remarks.
        $fine->update(['waive_reason' => null, 'remarks' => 'Legacy waiver reason.']);
        $this->getJson(route('admin.fines.history', $fine))->assertOk()
            ->assertJsonPath('waiverDetails.reason', 'Legacy waiver reason.');
    }
})->with(['pending', 'paid', 'waived']);
