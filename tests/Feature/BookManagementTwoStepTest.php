<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function twoStepStaff(): User
{
    return User::create([
        'role' => 'staff',
        'name' => 'Two Step Staff',
        'email' => 'two-step-staff-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);
}

function twoStepBookPayload(Category $category): array
{
    return [
        'title' => 'Database Management System',
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'isbn' => (string) random_int(100000000, 999999999),
        'category_id' => $category->id,
        'description' => 'General title information only.',
    ];
}

function twoStepAdmin(): User
{
    return User::create([
        'role' => 'admin',
        'name' => 'Two Step Admin',
        'email' => 'two-step-admin-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
        'status' => 'active',
        'is_verified' => true,
    ]);
}

test('admin and staff book pages expose separate title and physical-copy actions', function () {
    $this->actingAs(twoStepStaff())
        ->get(route('staff.book-management.index'))
        ->assertOk()
        ->assertSee('Add New Book')
        ->assertSee('Add Physical Book')
        ->assertSee('addPhysicalBookModal');

    $this->actingAs(twoStepAdmin())
        ->get(route('admin.books.index'))
        ->assertOk()
        ->assertSee('Add New Book')
        ->assertSee('Add Physical Book')
        ->assertSee('addPhysicalBookModal');
});

test('manage book copies pages expose the batch popup and copy filters', function () {
    $category = Category::create(['name' => 'Copy Page Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));

    $this->actingAs(twoStepStaff())
        ->get(route('staff.books.copies.index', $book))
        ->assertOk()
        ->assertSee('Add Copies')
        ->assertSee('addPhysicalBookModal')
        ->assertSee('copyStatusFilter')
        ->assertSee('copyTypeFilter')
        ->assertSee('copyEntriesSelect')
        ->assertSee('data-copy-summary="damaged"', false)
        ->assertSee('fa-triangle-exclamation')
        ->assertSee('copyTableSkeleton')
        ->assertSee('table-skeleton-row')
        ->assertSee('Loading copies...')
        ->assertSee('actionFeedbackToastContainer')
        ->assertSee('Borrowed By')
        ->assertDontSee('singleModeBtn');

    $this->actingAs(twoStepAdmin())
        ->get(route('admin.books.copies.index', $book))
        ->assertOk()
        ->assertSee('Add Copies')
        ->assertSee('copyStatusFilter')
        ->assertSee('copyTypeFilter')
        ->assertDontSee('singleModeBtn');
});

test('manage book copies filters before paginating and preserves entries selection', function () {
    $category = Category::create(['name' => 'Copy Pagination Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));
    app(\App\Services\PhysicalBookCopyService::class)->createCopyBatch($book, 11, 2);

    $staff = twoStepStaff();
    $url = route('staff.books.copies.index', $book);
    $this->actingAs($staff)->get($url)
        ->assertOk()
        ->assertSee('Showing 1 to 10 of 13 results')
        ->assertSee('page=2', false)
        ->assertSee('ACC-000010')
        ->assertDontSee('ACC-000011');

    $this->actingAs($staff)->get($url . '?page=2')
        ->assertOk()
        ->assertSee('Showing 11 to 13 of 13 results')
        ->assertSee('ACC-000013');

    $this->actingAs($staff)->get($url . '?per_page=20')
        ->assertOk()
        ->assertSee('Showing 1 to 13 of 13 results');

    $this->actingAs($staff)->get($url . '?book_type=reference&per_page=20')
        ->assertOk()
        ->assertSee('Showing 1 to 2 of 2 results')
        ->assertSee('ACC-000012')
        ->assertSee('ACC-000013')
        ->assertDontSee('ACC-000011');

    $this->actingAs($staff)->get($url . '?search=ACC-000013&per_page=20')
        ->assertOk()
        ->assertSee('Showing 1 to 1 of 1 results')
        ->assertSee('ACC-000013')
        ->assertDontSee('ACC-000012');
});

test('manage book copies returns filtered rows and shared pagination asynchronously', function () {
    $category = Category::create(['name' => 'Async Copy Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));
    app(\App\Services\PhysicalBookCopyService::class)->createCopyBatch($book, 11, 2);
    BookCopy::query()->where('book_id', $book->id)->firstOrFail()->update(['status' => 'damaged']);

    $url = route('staff.books.copies.index', $book);
    $response = $this->actingAs(twoStepStaff())->getJson($url . '?ajax_copies=1&book_type=borrowing&per_page=10&page=2');
    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('current_page', 2)
        ->assertJsonPath('per_page', 10)
        ->assertJsonPath('total', 11)
        ->assertJsonPath('summary.damaged', 1);
    expect($response->json('tableRows'))->toContain('ACC-000011')->not->toContain('ACC-000012');
    expect($response->json('pagination'))->toContain('Showing 11 to 11 of 11 results')->not->toContain('ajax_copies');

    $this->actingAs(twoStepStaff())->getJson($url . '?ajax_copies=1&search=ACC-000013&page=9')
        ->assertOk()
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('total', 1);
});

test('creating a book does not create a physical copy', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Two Step Category']);

    $response = $this->actingAs($staff)->postJson(route('staff.books.store'), twoStepBookPayload($category));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Book created successfully.');

    $book = Book::query()->where('title', 'Database Management System')->firstOrFail();

    expect($book->total_copies)->toBe(0)
        ->and($book->available_copies)->toBe(0)
        ->and($book->copies()->count())->toBe(0);
});

test('single and multiple physical copies use one global generated accession sequence', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Copy Workflow Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));

    $this->actingAs($staff)
        ->postJson(route('staff.books.copies.preview', $book), ['quantity' => 5])
        ->assertOk()
        ->assertJsonPath('data.accessions.0', 'ACC-000001')
        ->assertJsonPath('data.accessions.4', 'ACC-000005');

    $this->actingAs($staff)
        ->postJson(route('staff.books.copies.store', $book), [
            'quantity' => 5,
            'entry_date' => '2026-10-05',
            'book_type' => 'borrowing',
            'shelf_location' => 'A-12',
            'condition' => 'good',
            'remarks' => 'Batch test',
        ])
        ->assertCreated()
        ->assertJsonPath('data.summary.total', 5)
        ->assertJsonPath('data.summary.available', 5);

    expect(BookCopy::query()->where('book_id', $book->id)->pluck('accession_number')->all())
        ->toBe(['ACC-000001', 'ACC-000002', 'ACC-000003', 'ACC-000004', 'ACC-000005'])
        ->and(BookCopy::query()->where('book_id', $book->id)->where('status', 'available')->count())->toBe(5)
        ->and($book->fresh()->total_copies)->toBe(5)
        ->and($book->fresh()->available_copies)->toBe(5);
});

test('manage copies multiple mode derives book types from borrowing and reference counts', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Per Book Batch Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));

    $this->actingAs($staff)
        ->postJson(route('staff.books.copies.store', $book), [
            'mode' => 'multiple',
            'total_copies' => 5,
            'borrowing_copies' => 3,
            'reference_copies' => 2,
            'price' => '125.50',
            'entry_date' => '2026-10-05',
            'condition' => 'good',
        ])
        ->assertCreated()
        ->assertJsonPath('data.summary.total', 5);

    expect(BookCopy::query()->where('book_id', $book->id)->where('book_type', 'borrowing')->count())->toBe(3)
        ->and(BookCopy::query()->where('book_id', $book->id)->where('book_type', 'reference')->count())->toBe(2)
        ->and(BookCopy::query()->where('book_id', $book->id)->where('price', '125.50')->count())->toBe(5);
});

test('physical copy edit keeps the generated accession stable', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Edit Copy Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));
    $copy = app(\App\Services\PhysicalBookCopyService::class)->createCopies($book, 1)->first();

    $this->actingAs($staff)
        ->putJson(route('staff.book-copies.update', $copy), [
            'entry_date' => '2026-10-05',
            'book_type' => 'reference',
            'shelf_location' => 'R-05',
            'condition' => 'fair',
            'remarks' => 'Updated copy metadata',
        ])
        ->assertOk();

    expect($copy->fresh()->accession_number)->toBe('ACC-000001')
        ->and($copy->fresh()->book_type)->toBe('reference')
        ->and($copy->fresh()->shelf_location)->toBe('R-05');
});

test('the physical-book selector searches existing books and stores the selected book id', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Search Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));

    $this->actingAs($staff)
        ->getJson(route('staff.book-copies.search-books', ['q' => 'Database']))
        ->assertOk()
        ->assertJsonPath('has_books', true)
        ->assertJsonPath('data.0.book_id', $book->id)
        ->assertJsonPath('data.0.title', $book->title);

    $this->actingAs($staff)
        ->getJson(route('staff.book-copies.next-accession', [
            'book_id' => $book->id,
            'total_copies' => 5,
            'borrowing_copies' => 3,
            'reference_copies' => 2,
        ]))
        ->assertOk()
        ->assertJsonPath('data.accession_from', 'ACC-000001')
        ->assertJsonPath('data.accession_to', 'ACC-000005');

    $this->actingAs($staff)
        ->postJson(route('staff.book-copies.store-selected'), [
            'book_id' => $book->id,
            'total_copies' => 5,
            'borrowing_copies' => 3,
            'reference_copies' => 2,
            'price' => '125.50',
            'entry_date' => '2026-10-05',
            'shelf_location' => 'A-12',
            'condition' => 'good',
            'remarks' => 'Newly purchased batch',
        ])
        ->assertCreated()
        ->assertJsonPath('message', '5 physical copies added successfully.')
        ->assertJsonPath('data.accession_range.from', 'ACC-000001')
        ->assertJsonPath('data.accession_range.to', 'ACC-000005')
        ->assertJsonPath('data.copies.0.book_id', $book->id)
        ->assertJsonPath('data.copies.0.book_type', 'borrowing')
        ->assertJsonPath('data.copies.2.book_type', 'borrowing')
        ->assertJsonPath('data.copies.3.book_type', 'reference')
        ->assertJsonPath('data.copies.4.status', 'available');

    expect(BookCopy::query()->where('book_id', $book->id)->where('price', '125.50')->count())->toBe(5);
});

test('physical-book batch validation requires borrowing and reference counts to equal total', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Count Validation Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));

    $this->actingAs($staff)
        ->postJson(route('staff.book-copies.store-selected'), [
            'book_id' => $book->id,
            'total_copies' => 10,
            'borrowing_copies' => 7,
            'reference_copies' => 2,
            'entry_date' => '2026-10-05',
            'condition' => 'good',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.total_copies.0', 'Borrowing and Reference copies must equal the total number of copies.');

    expect(BookCopy::query()->where('book_id', $book->id)->count())->toBe(0);
});

test('physical-book entry date cannot be later than today', function () {
    $staff = twoStepStaff();
    $category = Category::create(['name' => 'Date Validation Category']);
    $book = Book::create(array_merge(twoStepBookPayload($category), [
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'status' => 'unavailable',
    ]));

    $this->actingAs($staff)
        ->postJson(route('staff.book-copies.store-selected'), [
            'book_id' => $book->id,
            'total_copies' => 1,
            'borrowing_copies' => 1,
            'reference_copies' => 0,
            'entry_date' => now()->addDay()->toDateString(),
            'condition' => 'good',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['entry_date']);
});

test('physical-book creation rejects invalid book ids and client accession numbers', function () {
    $staff = twoStepStaff();

    $this->actingAs($staff)
        ->postJson(route('staff.book-copies.store-selected'), [
            'book_id' => 999999,
            'accession_number' => 'ACC-999999',
            'entry_date' => '2026-10-05',
            'book_type' => 'borrowing',
            'condition' => 'good',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['book_id', 'accession_number'])
        ->assertJsonPath('errors.book_id.0', 'Selected book does not exist.');
});
