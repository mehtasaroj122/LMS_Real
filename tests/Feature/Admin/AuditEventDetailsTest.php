<?php

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;

test('student audit details preserve records and only offer existing related resources', function () {
    Illuminate\Support\Facades\View::share('libraryBranding', App\Support\LibraryBranding::resolve());
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Saroj Mehta']);
    $borrower = User::factory()->create(['role' => 'student', 'name' => 'Rajkumar Roy']);
    $department = Department::create(['name' => 'Chemistry', 'code' => 'CHEM']);
    $student = Student::create(['user_id' => $borrower->id, 'department_id' => $department->id, 'roll_no' => 'CHEM-2023-070', 'semester' => '6']);
    $category = Category::create(['name' => 'Computing']);
    $book = Book::create(['category_id' => $category->id, 'title' => 'HTTP/2 in Action', 'isbn' => '0001000033', 'author' => 'Test Author', 'total_copies' => 1, 'available_copies' => 0]);
    $copy = BookCopy::create(['book_id' => $book->id, 'accession_number' => 'ACC-000034', 'condition' => 'good', 'status' => 'issued', 'book_type' => 'borrowing']);
    $issue = IssuedBook::withoutEvents(fn () => IssuedBook::create(['book_id' => $book->id, 'book_copy_id' => $copy->id, 'student_id' => $student->id, 'issued_by' => $admin->id, 'issue_date' => '2026-09-01', 'due_date' => '2026-09-15', 'status' => 'issued']));
    $fine = Fine::withoutEvents(fn () => Fine::create(['issued_book_id' => $issue->id, 'student_id' => $student->id, 'amount' => 35, 'days_late' => 17, 'status' => 'pending']));
    $metadata = ['old_amount' => 29, 'new_amount' => 35, 'amount_change' => 6, 'fine_id' => $fine->id, 'book_name' => $book->title, 'isbn' => $book->isbn, 'student_label' => 'Rajkumar Roy (CHEM-2023-070)', 'remarks' => 'Reviewed by admin'];
    $log = ActivityLog::create(['user_id' => $admin->id, 'action' => 'fine_adjusted', 'action_category' => 'fine', 'status' => 'completed', 'model_type' => Student::class, 'model_id' => $student->id, 'resource_type' => 'fine', 'resource_id' => $fine->id, 'description' => 'Fine adjusted after review.', 'metadata' => $metadata, 'created_at' => now()]);
    $missing = ActivityLog::create(['user_id' => $admin->id, 'action' => 'fine_adjusted', 'action_category' => 'fine', 'model_type' => Student::class, 'model_id' => $student->id, 'resource_type' => 'fine', 'resource_id' => 99999, 'metadata' => ['fine_id' => 99999], 'description' => 'Historical fine adjustment.']);
    $before = $log->fresh()->getAttributes();

    $response = $this->actingAs($admin)->getJson(route('admin.students.activity-logs', $student))->assertOk()->assertJsonPath('success', true);
    $logs = collect($response->json('activityLogs'))->keyBy('id');
    expect($logs[$log->id]['title'])->toBe('Fine Adjusted')
        ->and($logs[$log->id]['status'])->toBe('warning')
        ->and($logs[$log->id]['userName'])->toBe('Saroj Mehta')
        ->and($logs[$log->id]['userRole'])->toBe('admin')
        ->and($logs[$log->id]['fullTimestamp'])->toBe($log->fresh()->created_at->format('M d, Y h:i:s A'))
        ->and($logs[$log->id]['description'])->toBe('Fine adjusted after review.')
        ->and($logs[$log->id]['metadata'])->toBe($metadata)
        ->and($logs[$log->id]['resourceAvailable'])->toBeTrue()
        ->and($logs[$log->id]['accessionNumber'])->toBe('ACC-000034')
        ->and($logs[$log->id]['resourceUrl'])->toBe(route('admin.fines.index').'?student='.$student->id)
        ->and($logs[$missing->id]['resourceAvailable'])->toBeFalse()
        ->and($logs[$missing->id]['accessionNumber'])->toBeNull()
        ->and($log->fresh()->getAttributes())->toBe($before)
        ->and($fine->fresh()->amount)->toEqual(35);

    $this->get(route('admin.students.show', $student))->assertOk()
        ->assertSee('Audit Event Details')->assertSee('Show Technical Details')->assertSee('audit-event-details.js');
});
