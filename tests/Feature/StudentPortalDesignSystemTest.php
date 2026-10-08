<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\Category;
use App\Models\department as Department;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use App\Support\LibraryBranding;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\View;

test('student pages retain their selected presentation with existing data and form contracts intact', function () {
    app(Vite::class)->useHotFile(storage_path('framework/testing/student-portal/unused.hot'));
    View::share('libraryBranding', LibraryBranding::resolve());
    $user = User::factory()->create(['role' => 'student', 'status' => 'active', 'is_verified' => true, 'name' => 'Student UI Reviewer']);
    $department = Department::create(['name' => 'Computing', 'code' => 'CS', 'status' => 'active']);
    $student = Student::create(['user_id' => $user->id, 'department_id' => $department->id, 'student_id' => 'UI-001', 'roll_no' => 'UI-001', 'batch' => '2026', 'semester' => '2']);
    $category = Category::create(['name' => 'Computing']);
    FineSetting::create(['max_books_per_student' => 20, 'issue_duration_days' => 14, 'per_day_fine' => 2, 'grace_period_days' => 0, 'max_fine_amount' => 1000, 'is_active' => true]);

    for ($index = 1; $index <= 12; $index++) {
        $book = Book::create(['category_id' => $category->id, 'title' => sprintf('Library Book %02d', $index), 'author' => 'Library Author', 'isbn' => 'UI-'.$index, 'total_copies' => 2, 'available_copies' => 1, 'condition' => 'good', 'status' => 'available']);
        $copy = BookCopy::create(['book_id' => $book->id, 'accession_number' => 'UI-ACC-'.$index, 'book_type' => 'borrowing', 'condition' => 'good', 'status' => 'issued']);
        $issue = IssuedBook::create(['book_id' => $book->id, 'book_copy_id' => $copy->id, 'student_id' => $student->id, 'issue_date' => today()->subDays(14), 'due_date' => today()->subDays($index), 'fine_amount' => 0, 'condition' => 'good']);
        Fine::create(['student_id' => $student->id, 'issued_book_id' => $issue->id, 'amount' => 20, 'days_late' => $index, 'status' => $index % 2 ? 'pending' : 'paid']);
        BookRequest::create(['student_id' => $student->id, 'book_id' => $book->id, 'status' => $index % 2 ? 'pending' : 'approved', 'request_date' => now()->subHours($index)]);
    }

    $before = [IssuedBook::all()->toArray(), Fine::all()->toArray(), BookRequest::all()->toArray()];
    $directory = getenv('STUDENT_PORTAL_RENDER_DIR');
    if ($directory && ! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    foreach (['dashboard', 'search', 'my-books', 'requests', 'fines', 'profile'] as $page) {
        $response = $this->actingAs($user)->get(route('student.'.$page));
        $response->assertOk();
        if (in_array($page, ['search', 'profile'], true)) {
            $response->assertSee('class="light-theme"', false)
                ->assertDontSee('admin/CSS/admin-design-system.css', false)
                ->assertDontSee('student/CSS/student-design-system.css', false)
                ->assertDontSee('admin/JS/admin-ui.js', false);
        } else {
            $response->assertSee('class="light-theme admin-portal student-portal"', false)
                ->assertSee('admin/CSS/admin-design-system.css', false)
                ->assertSee('student/CSS/student-design-system.css', false)
                ->assertSee('admin/JS/admin-ui.js', false);
        }
        if ($page === 'profile') {
            $response->assertSee(route('student.profile.update-personal-info'), false)
                ->assertSee('id="error_name"', false)->assertSee('name="_token"', false);
        }
        if ($directory) {
            file_put_contents($directory.'/'.$page.'.html', $response->getContent());
        }
    }
    $catalog = $this->actingAs($user)->getJson(route('student.search'), ['X-Requested-With' => 'XMLHttpRequest']);
    $catalog->assertOk()->assertJsonCount(12, 'books');
    if ($directory) {
        file_put_contents($directory.'/catalog.json', $catalog->getContent());
    }
    expect([IssuedBook::all()->toArray(), Fine::all()->toArray(), BookRequest::all()->toArray()])->toBe($before);
});
