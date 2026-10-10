<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('book and borrower images are consistent across catalogue and circulation APIs', function (?string $cover, ?string $portrait, ?string $expectedCover, ?string $expectedPortrait) {
    $coverUrl = $expectedCover !== null && ! str_starts_with($expectedCover, 'https://') ? asset($expectedCover) : $expectedCover;
    $portraitUrl = $expectedPortrait !== null && ! str_starts_with($expectedPortrait, 'https://') ? asset($expectedPortrait) : $expectedPortrait;
    $staff = User::factory()->create(['role' => 'staff', 'status' => 'active', 'is_verified' => true]);
    $borrower = User::factory()->create(['role' => 'student', 'status' => 'active', 'is_verified' => true, 'profile_photo' => $portrait]);
    $department = Department::create(['name' => 'Media Department', 'code' => 'MEDIA', 'status' => 'active']);
    $student = Student::create([
        'user_id' => $borrower->id,
        'department_id' => $department->id,
        'student_id' => 'MEDIA-001',
        'roll_no' => 'MEDIA-001',
        'batch' => '2026',
        'semester' => '1',
    ]);
    $category = Category::create(['name' => 'Media Category']);
    $book = Book::create([
        'category_id' => $category->id,
        'title' => 'Media Book',
        'author' => 'Media Author',
        'isbn' => 'MEDIA-ISBN',
        'total_copies' => 2,
        'available_copies' => 1,
        'condition' => 'good',
        'status' => 'available',
        'cover_image' => $cover,
    ]);
    $copy = BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000101',
        'book_type' => 'borrowing',
        'status' => 'issued',
        'condition' => 'good',
    ]);
    BookCopy::create([
        'book_id' => $book->id,
        'accession_number' => 'ACC-000102',
        'book_type' => 'borrowing',
        'status' => 'available',
        'condition' => 'good',
    ]);
    $issue = IssuedBook::create([
        'book_id' => $book->id,
        'book_copy_id' => $copy->id,
        'student_id' => $student->id,
        'issued_by' => $staff->id,
        'issue_date' => today(),
        'due_date' => today()->addDays(14),
        'status' => 'issued',
        'condition' => 'good',
    ]);
    $bookRequest = BookRequest::create(['student_id' => $student->id, 'book_id' => $book->id, 'status' => 'pending', 'request_date' => now()]);
    $fine = Fine::create(['student_id' => $student->id, 'issued_book_id' => $issue->id, 'amount' => 12, 'days_late' => 0, 'status' => 'pending']);

    Sanctum::actingAs($staff);
    $responses = [
        '/api/books' => ['data.0.cover_image_url' => $coverUrl],
        '/api/books/search?q=Media' => ['data.0.cover_image_url' => $coverUrl],
        '/api/books/available' => ['data.0.cover_image_url' => $coverUrl],
        '/api/books/category/'.$category->id => ['data.0.cover_image_url' => $coverUrl],
        '/api/books/'.$book->id => ['data.cover_image' => $cover, 'data.cover_image_url' => $coverUrl],
        '/api/staff/books/search?query=Media' => ['data.0.cover_image_url' => $coverUrl],
        '/api/staff/issue-books' => ['data.0.cover_image_url' => $coverUrl],
        '/api/book-copies' => ['data.0.book.cover_image_url' => $coverUrl],
        '/api/book-copies/'.$copy->accession_number => [
            'data.copy.book.cover_image_url' => $coverUrl,
            'data.active_issue.book.cover_image_url' => $coverUrl,
            'data.active_issue.student.profile_photo_url' => $portraitUrl,
        ],
        '/api/staff/return-books/accession/'.$copy->accession_number => [
            'data.book.cover_image_url' => $coverUrl,
            'data.issue.cover_image_url' => $coverUrl,
            'data.borrower.profile_photo_url' => $portraitUrl,
            'data.issue.student.profile_photo_url' => $portraitUrl,
        ],
        '/api/staff/return-books/student/'.$student->id => [
            'data.active_issues.0.cover_image_url' => $coverUrl,
            'data.student.profile_photo_url' => $portraitUrl,
        ],
        '/api/staff/students/'.$student->id => [
            'data.active_issued_books.0.cover_image_url' => $coverUrl,
            'data.pending_requests.0.cover_image_url' => $coverUrl,
            'data.pending_fines.0.cover_image_url' => $coverUrl,
            'data.profile_photo_url' => $portraitUrl,
        ],
        '/api/staff/book-requests/'.$bookRequest->id => [
            'data.cover_image_url' => $coverUrl,
            'data.book.cover_image_url' => $coverUrl,
            'data.student.photo_url' => $portraitUrl,
            'data.student.profile_photo_url' => $portraitUrl,
        ],
        '/api/staff/fines/'.$fine->id => ['data.cover_image_url' => $coverUrl],
        '/api/staff/fines/students/'.$student->id => [
            'data.fines.0.cover_image_url' => $coverUrl,
            'data.student.profile_photo_url' => $portraitUrl,
        ],
        '/api/issues/'.$issue->id => ['data.book.cover_image_url' => $coverUrl, 'data.student.profile_photo_url' => $portraitUrl],
        '/api/book-requests/'.$bookRequest->id => ['data.book.cover_image_url' => $coverUrl, 'data.student.profile_photo_url' => $portraitUrl],
    ];
    foreach ($responses as $endpoint => $images) {
        $response = $this->getJson($endpoint)->assertOk();
        foreach ($images as $path => $value) {
            $response->assertJsonPath($path, $value);
        }
    }

    $this->actingAs($staff)->getJson('/staff/book-copies/search?query='.$copy->accession_number.'&mode=return')
        ->assertOk()
        ->assertJsonPath('data.0.copy.book.cover_image_url', $coverUrl)
        ->assertJsonPath('data.0.issue.book.cover_image_url', $coverUrl)
        ->assertJsonPath('data.0.issue.student.profile_photo_url', $portraitUrl);

    Sanctum::actingAs($borrower);
    $this->getJson('/api/profile')->assertOk()->assertJsonPath('data.profile_photo_url', $portraitUrl);
    $this->getJson('/api/auth/check')->assertOk()->assertJsonPath('user.profile_photo_url', $portraitUrl);
    $this->getJson('/api/student/my-books/current')->assertOk()->assertJsonPath('data.0.book.cover_image_url', $coverUrl);

    expect($book->fresh()->cover_image)->toBe($cover)
        ->and($borrower->fresh()->profile_photo)->toBe($portrait);
})->with([
    'external images' => [
        'https://covers.openlibrary.org/b/id/8065615-L.jpg',
        'https://randomuser.me/api/portraits/men/0.jpg',
        'https://covers.openlibrary.org/b/id/8065615-L.jpg',
        'https://randomuser.me/api/portraits/men/0.jpg',
    ],
    'uploaded images with storage prefixes' => [
        '/storage/books/covers/sample.jpg',
        '/storage/profile_photos/sample.jpg',
        'storage/books/covers/sample.jpg',
        'storage/profile_photos/sample.jpg',
    ],
    'relative upload paths' => [
        'books/covers/sample.jpg',
        'profile_photos/sample.jpg',
        'storage/books/covers/sample.jpg',
        'storage/profile_photos/sample.jpg',
    ],
    'missing images' => [null, null, null, null],
    'blank images' => ['  ', '  ', null, null],
]);
