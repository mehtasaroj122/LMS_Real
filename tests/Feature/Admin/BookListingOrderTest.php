<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\User;

function listingOrderBook(Category $category, string $title, $createdAt): Book
{
    return Book::forceCreate([
        'category_id' => $category->id,
        'title' => $title,
        'author' => 'Listing Author',
        'isbn' => 'listing-' . str()->uuid(),
        'total_copies' => 0,
        'available_copies' => 0,
        'condition' => 'good',
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}

function listingOrderRowIds(string $rows): array
{
    preg_match_all('/data-book-id="(\d+)"/', $rows, $matches);

    return array_map('intval', $matches[1]);
}

test('admin books show newest entries first consistently across initial and ajax pages', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $category = Category::create(['name' => 'Listing Order']);
    $createdAt = now()->startOfSecond();
    $ids = [];
    for ($number = 1; $number <= 12; $number++) {
        $ids[] = listingOrderBook($category, sprintf('Listing Book %02d', $number), $createdAt)->id;
    }
    // A higher ID must not move an older entry ahead of newer creation dates.
    $older = listingOrderBook($category, 'Older Book', $createdAt->copy()->subDay());
    $expected = [...array_reverse($ids), $older->id];

    foreach ([1, 2] as $page) {
        $pageIds = array_slice($expected, ($page - 1) * 10, 10);
        $this->get(route('admin.books.index', ['page' => $page]))
            ->assertOk()
            ->assertViewHas('initialBooks', fn ($books) => $books->pluck('id')->all() === $pageIds);

        foreach ([null, 'recently-added', '', 'unknown'] as $sort) {
            $response = $this->getJson(route('admin.books.data', ['page' => $page, 'sort' => $sort]))
                ->assertOk()
                ->assertJsonPath('current_page', $page)
                ->assertJsonPath('total', 13);
            expect(listingOrderRowIds($response->json('tableRows')))->toBe($pageIds);
        }
    }

    $response = $this->getJson(route('admin.books.data', ['sort' => 'title-asc', 'per_page' => 20]))->assertOk();
    expect(listingOrderRowIds($response->json('tableRows')))->toBe([...$ids, $older->id]);
});

test('admin copies show newest entries first before filtering and pagination while staff keeps accession order', function () {
    $category = Category::create(['name' => 'Copy Listing Order']);
    $createdAt = now()->startOfSecond();
    $book = listingOrderBook($category, 'Copy Order Book', $createdAt);
    $copies = [];
    for ($number = 1; $number <= 13; $number++) {
        $copies[] = BookCopy::forceCreate([
            'book_id' => $book->id,
            'accession_number' => sprintf('ACC-%06d', $number),
            'entry_date' => $createdAt->toDateString(),
            'book_type' => $number === 13 ? 'reference' : 'borrowing',
            'status' => 'available',
            'condition' => 'good',
            'created_at' => $number === 13 ? $createdAt->copy()->subDay() : $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
    $recent = array_reverse(array_slice($copies, 0, 12));
    $ordered = [...$recent, $copies[12]];
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    foreach ([1, 2] as $page) {
        $pageCopies = array_slice($ordered, ($page - 1) * 10, 10);
        $this->get(route('admin.books.copies.index', ['book' => $book, 'page' => $page]))
            ->assertOk()
            ->assertViewHas('copies', fn ($rows) => $rows->pluck('id')->all() === array_map(fn ($copy) => $copy->id, $pageCopies));
        $response = $this->getJson(route('admin.books.copies.index', ['book' => $book, 'ajax_copies' => 1, 'page' => $page]))
            ->assertOk()
            ->assertJsonPath('current_page', $page)
            ->assertJsonPath('total', 13);
        preg_match_all('/<td class="accession">([^<]+)<\/td>/', $response->json('tableRows'), $matches);
        expect($matches[1])->toBe(array_map(fn ($copy) => $copy->accession_number, $pageCopies));
    }

    $response = $this->getJson(route('admin.books.copies.index', ['book' => $book]))->assertOk();
    expect(array_column($response->json('data.copies'), 'id'))->toBe(array_map(fn ($copy) => $copy->id, $ordered));

    $response = $this->getJson(route('admin.books.copies.index', [
        'book' => $book, 'ajax_copies' => 1, 'book_type' => 'borrowing', 'page' => 99,
    ]))->assertOk()->assertJsonPath('current_page', 2)->assertJsonPath('total', 12);
    preg_match_all('/<td class="accession">([^<]+)<\/td>/', $response->json('tableRows'), $matches);
    expect($matches[1])->toBe(['ACC-000002', 'ACC-000001']);

    $this->actingAs(User::factory()->create(['role' => 'staff']))
        ->get(route('staff.books.copies.index', $book))
        ->assertOk()
        ->assertViewHas('copies', fn ($rows) => $rows->pluck('id')->all() === array_map(fn ($copy) => $copy->id, array_slice($copies, 0, 10)));
});
