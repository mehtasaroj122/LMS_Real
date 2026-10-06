<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function detailsTestBook(array $overrides = []): Book
{
    $category = Category::create(['name' => 'Details Category ' . str()->uuid()]);

    return Book::forceCreate(array_merge([
        'category_id' => $category->id,
        'title' => 'Book Details Test',
        'author' => 'Details Author',
        'isbn' => 'details-' . str()->uuid(),
        'total_copies' => 99,
        'available_copies' => 99,
        'condition' => 'good',
        'shelf_no' => 'CATALOGUE-01',
    ], $overrides));
}

test('admin book details aggregate actual copy statuses without inferring issued from availability', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $book = detailsTestBook([
        'publisher' => 'Publisher & Co.',
        'description' => "First line\n<script>untrusted content</script>",
        'cover_image' => 'books/covers/example.jpg',
    ]);
    $statuses = ['available', 'available', 'issued', 'issued', 'lost', 'damaged'];
    foreach ($statuses as $index => $status) {
        BookCopy::create([
            'book_id' => $book->id,
            'accession_number' => sprintf('ACC-%06d', $index + 1),
            'status' => $status,
            'book_type' => $index < 4 ? 'borrowing' : 'reference',
            'shelf_location' => $index % 2 ? 'B-02' : 'A-01',
        ]);
    }

    DB::enableQueryLog();
    $response = $this->getJson(route('admin.books.show', $book))->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.book.id', $book->id)
        ->assertJsonPath('data.book.category', $book->category->name)
        ->assertJsonPath('data.book.publisher', 'Publisher & Co.')
        ->assertJsonPath('data.book.description', $book->description)
        ->assertJsonPath('data.book.shelf_no', 'CATALOGUE-01')
        ->assertJsonPath('data.book.cover_url', asset('storage/books/covers/example.jpg'))
        ->assertJsonPath('data.inventory.total', 6)
        ->assertJsonPath('data.inventory.available', 2)
        ->assertJsonPath('data.inventory.issued', 2)
        ->assertJsonPath('data.inventory.unavailable', 2)
        ->assertJsonPath('data.inventory.statuses.lost', 1)
        ->assertJsonPath('data.inventory.statuses.damaged', 1)
        ->assertJsonPath('data.inventory.types.borrowing', 4)
        ->assertJsonPath('data.inventory.types.reference', 2)
        ->assertJsonPath('data.inventory.shelf_locations', ['A-01', 'B-02'])
        ->assertJsonPath('data.inventory.has_more_shelf_locations', false)
        ->assertJsonPath('data.manage_copies_url', route('admin.books.copies.index', $book));

    $copyQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'from "book_copies"'));
    DB::disableQueryLog();
    expect($copyQueries)->toHaveCount(2);
    expect($response->json('data'))->not->toHaveKey('copies');
    expect($book->fresh()->total_copies)->toBe(99); // The read-only modal does not repair or alter inventory.
});

test('admin book details support zero physical copies and missing optional metadata', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $book = detailsTestBook(['publisher' => null, 'description' => null, 'cover_image' => null, 'shelf_no' => null]);

    $this->getJson(route('admin.books.show', $book))->assertOk()
        ->assertJsonPath('data.book.publisher', null)
        ->assertJsonPath('data.book.description', null)
        ->assertJsonPath('data.book.cover_url', null)
        ->assertJsonPath('data.inventory.total', 0)
        ->assertJsonPath('data.inventory.available', 0)
        ->assertJsonPath('data.inventory.issued', 0)
        ->assertJsonPath('data.inventory.unavailable', 0)
        ->assertJsonPath('data.inventory.shelf_locations', [])
        ->assertJsonPath('data.inventory.has_more_shelf_locations', false);
});

test('admin book details bound shelf previews and preserve remote cover urls', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $book = detailsTestBook(['cover_image' => 'https://example.com/cover.jpg']);
    foreach (range(1, 8) as $number) {
        BookCopy::create([
            'book_id' => $book->id,
            'accession_number' => sprintf('ACC-%06d', $number),
            'status' => 'available',
            'shelf_location' => 'RACK-' . $number,
        ]);
    }

    $this->getJson(route('admin.books.show', $book))->assertOk()
        ->assertJsonPath('data.book.cover_url', 'https://example.com/cover.jpg')
        ->assertJsonPath('data.inventory.has_more_shelf_locations', true)
        ->assertJsonPath('data.inventory.shelf_locations', ['RACK-1', 'RACK-2', 'RACK-3', 'RACK-4', 'RACK-5']);
});

test('book detail data remains restricted to admins and missing books return not found', function () {
    $book = detailsTestBook();
    $url = route('admin.books.show', $book);
    $this->getJson($url)->assertUnauthorized();
    foreach (['staff', 'student'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))->getJson($url)->assertForbidden();
    }
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->getJson(route('admin.books.show', 999999))->assertNotFound();
});
