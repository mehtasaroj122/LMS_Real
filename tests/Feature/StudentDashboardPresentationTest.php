<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentPrivilege;
use App\Models\User;
use App\Support\LibraryBranding;
use Carbon\Carbon;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\View;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-07 09:00:00', 'Asia/Kathmandu'));
    app(Vite::class)->useHotFile(storage_path('framework/testing/student-dashboard/unused.hot'));
    View::share('libraryBranding', LibraryBranding::resolve());
    FineSetting::create([
        'max_books_per_student' => 13, 'issue_duration_days' => 14,
        'per_day_fine' => 2, 'grace_period_days' => 0, 'max_fine_amount' => 1000, 'is_active' => true,
    ]);
});

function dashboardPresentationStudent(): Student
{
    $user = User::factory()->create([
        'role' => 'student', 'status' => 'active', 'is_verified' => true,
        'name' => 'Saroj Mehta', 'email' => 'dashboard-student@example.com',
        'profile_photo' => null, 'created_at' => '2023-10-05 09:00:00',
    ]);
    $department = Department::create(['name' => 'Computer Science', 'code' => 'CS', 'status' => 'active']);

    return Student::create([
        'user_id' => $user->id, 'department_id' => $department->id,
        'student_id' => 'CS-2023-001', 'roll_no' => 'ROLL-001', 'semester' => '2', 'batch' => '2023',
    ]);
}

// Optional full Laravel render artifacts for the companion browser checks.
function saveDashboardPresentationFixture(string $name, string $html): void
{
    if ($directory = getenv('STUDENT_DASHBOARD_RENDER_DIR')) {
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory.DIRECTORY_SEPARATOR.$name.'.html', $html);
    }
}

test('student dashboard shows actual identity, circulation, fines and request data without changing records', function () {
    $student = dashboardPresentationStudent();
    $category = Category::create(['name' => 'Computing']);
    $titles = ['HTTP/2 in Action', 'Clean Code', 'Designing Data-Intensive Applications', 'The Pragmatic Programmer', 'Refactoring', 'Computer Networks', 'Database Systems', 'Returned Book'];
    $issues = collect();
    foreach ($titles as $index => $title) {
        $book = Book::create([
            'category_id' => $category->id, 'title' => $title, 'author' => 'Library Author',
            'isbn' => 'DASH-'.$index, 'total_copies' => 1, 'available_copies' => 0,
            'condition' => 'good', 'status' => 'issued',
        ]);
        $copy = BookCopy::create([
            'book_id' => $book->id, 'accession_number' => 'ACC-00014'.$index,
            'book_type' => 'borrowing', 'status' => 'issued', 'condition' => 'good',
        ]);
        $issues->push(IssuedBook::create([
            'book_id' => $book->id, 'book_copy_id' => $copy->id, 'student_id' => $student->id,
            'issue_date' => today()->subDays(14),
            'due_date' => today()->addDays([-5, 0, 3, 7, 8, 9, 10, -1][$index]),
            'return_date' => $index === 7 ? today()->subDay() : null,
            'status' => $index === 7 ? 'returned' : ($index === 0 ? 'overdue' : 'issued'),
            'condition' => 'good', 'fine_amount' => 0,
        ]));
    }
    $fine = Fine::create([
        'issued_book_id' => $issues->first()->id, 'student_id' => $student->id,
        'amount' => 35, 'days_late' => 5, 'status' => 'pending',
    ]);
    $privilege = StudentPrivilege::create([
        'student_id' => $student->id, 'max_books' => 13, 'issue_duration_days' => 14,
        'per_day_fine' => 2, 'borrowing_allowed' => true,
    ]);
    foreach (['pending', 'pending', 'approved', 'rejected', 'returned'] as $index => $status) {
        BookRequest::create([
            'student_id' => $student->id, 'book_id' => $issues[$index]->book_id,
            'status' => $status, 'request_date' => now()->subHours($index + 1),
        ]);
    }
    foreach (['book.overdue', 'request.approved', 'fine.created', 'book.due_soon', 'request.rejected', 'book.new'] as $index => $type) {
        Notification::create([
            'user_id' => $student->user_id, 'type' => $type, 'title' => 'Library update '.($index + 1),
            'message' => $index === 0 ? 'Please return your overdue book. Fine: रु 35.00' : 'Your library account has been updated.',
            'read_at' => $index < 3 ? null : now()->subHours(2), 'created_at' => now()->subMinutes($index + 1),
        ]);
    }
    $recordsBefore = [$fine->fresh()->getAttributes(), $privilege->fresh()->getAttributes(), $issues->map(fn ($issue) => $issue->fresh()->getAttributes())->all()];

    $response = $this->actingAs($student->user)->get(route('student.dashboard'));
    $response->assertOk()->assertSee('Student Library ID')->assertSee('CS-2023-001')
        ->assertSee('Semester 2')->assertSee('Batch 2023')->assertSee('Oct 05, 2023')
        ->assertSee('रु 35.00')->assertSee('Account Snapshot')
        ->assertSee('Monthly Activity')->assertSee('Request Status Overview')
        ->assertSee('class="tables-row"', false)->assertSee('class="dashboard-bottom-row"', false)
        ->assertDontSee('Year 4')->assertDontSee('₹');
    expect($response->viewData('booksIssuedCount'))->toBe(7)
        ->and($response->viewData('booksReturnedCount'))->toBe(1)
        ->and((float) $response->viewData('pendingFines'))->toBe(35.0)
        ->and($response->viewData('activeRequestsCount'))->toBe(2)
        ->and($response->viewData('dueSoon')->count())->toBe(2)
        ->and($response->viewData('requestOverview')['total'])->toBe(5)
        ->and($response->viewData('privilegeSettings')['remaining_slots'])->toBe(6)
        ->and([$fine->fresh()->getAttributes(), $privilege->fresh()->getAttributes(), $issues->map(fn ($issue) => $issue->fresh()->getAttributes())->all()])->toBe($recordsBefore)
        ->and(Notification::where('user_id', $student->user_id)->count())->toBe(6);
    saveDashboardPresentationFixture('populated', $response->getContent());
});

test('student identity omits unavailable academic fields while the original dashboard empty states remain', function () {
    $student = dashboardPresentationStudent();
    $student->update(['semester' => '', 'batch' => null]);

    $response = $this->actingAs($student->user)->get(route('student.dashboard'));
    $response->assertOk()->assertDontSee('id="important-alerts-heading"', false)
        ->assertDontSee('Semester 2')->assertDontSee('Batch 2023')->assertDontSee('Year 4')
        ->assertSee('No books due soon.')->assertSee('No books currently issued.')
        ->assertSee('You have not made any requests yet.')
        ->assertSee('No issue or return activity recorded in the last 30 days.');
    expect($response->viewData('booksIssuedCount'))->toBe(0)
        ->and($response->viewData('booksReturnedCount'))->toBe(0)
        ->and((float) $response->viewData('pendingFines'))->toBe(0.0);
    saveDashboardPresentationFixture('empty', $response->getContent());
});

test('student dashboard displays restricted borrowing and safely renders a missing student profile', function () {
    $student = dashboardPresentationStudent();
    StudentPrivilege::create([
        'student_id' => $student->id, 'max_books' => 0, 'issue_duration_days' => 14,
        'per_day_fine' => 2, 'borrowing_allowed' => false,
    ]);
    $response = $this->actingAs($student->user)->get(route('student.dashboard'));
    $response->assertOk()->assertSee('Borrowing Permission')->assertSee('Restricted')
        ->assertSee('Borrowing is currently restricted for your account.');
    saveDashboardPresentationFixture('restricted', $response->getContent());

    $student->delete();
    $this->actingAs($student->user->fresh())->get(route('student.dashboard'))
        ->assertOk()->assertSee('Not provided')->assertDontSee('Semester 2')->assertDontSee('Batch 2023');
});

test('student identity does not duplicate the stored semester prefix', function () {
    $student = dashboardPresentationStudent();
    $student->update(['semester' => 'Semester 2']);
    $this->actingAs($student->user)->get(route('student.dashboard'))
        ->assertOk()->assertSee('Semester 2')->assertDontSee('Semester Semester 2');
});
