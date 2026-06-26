<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function makeMobileCatalogUser(): User
{
    return User::factory()->create([
        'role' => 'student',
        'status' => 'active',
        'is_verified' => true,
    ]);
}

function makeMobileCatalogBook(array $overrides = []): Book
{
    $unique = Str::upper(Str::random(8));

    $category = $overrides['category'] ?? Category::create([
        'name' => 'Catalog Category ' . $unique,
        'description' => 'Mobile catalog test category',
    ]);

    unset($overrides['category']);

    return Book::create(array_merge([
        'category_id' => $category->id,
        'title' => 'Catalog Book ' . $unique,
        'author' => 'Catalog Author',
        'publisher' => 'Catalog Publisher',
        'isbn' => 'CAT-' . $unique,
        'total_copies' => 10,
        'available_copies' => 5,
        'condition' => 'good',
        'description' => 'Mobile catalog test book',
        'cover_image' => 'books/covers/' . Str::lower($unique) . '.jpg',
        'shelf_no' => 'Shelf A1',
        'status' => 'available',
    ], $overrides));
}

test('authenticated mobile users can list paginated books', function () {
    Sanctum::actingAs(makeMobileCatalogUser());
    $book = makeMobileCatalogBook([
        'title' => 'Example Book',
        'author' => 'Example Author',
        'available_copies' => 2,
    ]);

    $this->getJson('/api/books?page=1&per_page=20')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [[
                'id',
                'accession_no',
                'title',
                'author',
                'publisher',
                'category',
                'condition',
                'location',
                'quantity',
                'available_quantity',
                'status',
                'cover_image',
                'cover_image_url',
                'created_at',
            ]],
            'links',
            'meta',
        ])
        ->assertJsonPath('data.0.id', $book->id)
        ->assertJsonPath('data.0.title', 'Example Book')
        ->assertJsonPath('data.0.author', 'Example Author')
        ->assertJsonPath('data.0.available_quantity', 2);
});

test('authenticated mobile users can browse available, search, category, and show book endpoints', function () {
    Sanctum::actingAs(makeMobileCatalogUser());
    $category = Category::create([
        'name' => 'Mobile Science',
        'description' => 'Science catalog',
    ]);

    $availableBook = makeMobileCatalogBook([
        'category' => $category,
        'title' => 'Applied Algebra',
        'available_copies' => 3,
    ]);

    makeMobileCatalogBook([
        'category' => $category,
        'title' => 'Archived Algebra',
        'available_copies' => 0,
    ]);

    $this->getJson('/api/books/available?page=1&per_page=20')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $availableBook->id,
            'available_quantity' => 3,
        ]);

    $this->getJson('/api/books/search?q=Algebra&page=1&per_page=20')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $availableBook->id,
            'title' => 'Applied Algebra',
        ]);

    $this->getJson('/api/books/category/' . $category->id . '?page=1&per_page=20')
        ->assertOk()
        ->assertJsonFragment([
            'category' => 'Mobile Science',
        ]);

    $this->getJson('/api/books/' . $availableBook->id)
        ->assertOk()
        ->assertJsonPath('data.id', $availableBook->id)
        ->assertJsonPath('data.category', 'Mobile Science');
});
