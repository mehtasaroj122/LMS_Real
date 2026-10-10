<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Services\MissingImageBackfill;

function imageBackfillBook(array $attributes = []): Book
{
    $category = Category::create(['name' => 'Image Category '.str()->uuid()]);

    return Book::create(array_merge([
        'category_id' => $category->id,
        'title' => 'Clean Code',
        'author' => 'Robert C. Martin',
        'isbn' => 'media-'.str()->uuid(),
        'total_copies' => 7,
        'available_copies' => 3,
        'condition' => 'good',
        'status' => 'available',
    ], $attributes));
}

test('image backfill adds matched covers and stock portraits while preserving existing images and other data', function () {
    $matched = imageBackfillBook();
    $unknown = imageBackfillBook(['title' => 'Unpublished library manuscript']);
    $uploaded = imageBackfillBook(['cover_image' => 'books/covers/uploaded.jpg']);
    $external = imageBackfillBook(['cover_image' => 'https://example.com/existing-cover.jpg']);
    $blank = User::factory()->create(['profile_photo' => null]);
    $whitespace = User::factory()->create(['profile_photo' => '  ']);
    $userUpload = User::factory()->create(['profile_photo' => 'profile_photos/uploaded.jpg']);
    $userExternal = User::factory()->create(['profile_photo' => 'https://example.com/portrait.jpg']);
    $bookBefore = $matched->fresh()->getAttributes();
    $userBefore = $blank->fresh()->getAttributes();

    $counts = app(MissingImageBackfill::class)->run();
    expect($counts)->toBe(['book_covers' => 1, 'generic_book_photos' => 1, 'profile_photos' => 2])
        ->and($matched->fresh()->cover_image)->toStartWith('https://covers.openlibrary.org/b/id/')
        ->and($unknown->fresh()->cover_image)->toBe(MissingImageBackfill::BOOK_PHOTO_URL)
        ->and($blank->fresh()->profile_photo)->toStartWith('https://randomuser.me/api/portraits/')
        ->and($whitespace->fresh()->profile_photo)->toStartWith('https://randomuser.me/api/portraits/')
        ->and($uploaded->fresh()->cover_image)->toBe('books/covers/uploaded.jpg')
        ->and($external->fresh()->cover_image)->toBe('https://example.com/existing-cover.jpg')
        ->and($userUpload->fresh()->profile_photo)->toBe('profile_photos/uploaded.jpg')
        ->and($userExternal->fresh()->profile_photo)->toBe('https://example.com/portrait.jpg');

    unset($bookBefore['cover_image'], $userBefore['profile_photo']);
    $bookAfter = $matched->fresh()->getAttributes();
    $userAfter = $blank->fresh()->getAttributes();
    unset($bookAfter['cover_image'], $userAfter['profile_photo']);
    expect($bookAfter)->toBe($bookBefore)->and($userAfter)->toBe($userBefore)
        ->and(app(MissingImageBackfill::class)->run())
        ->toBe(['book_covers' => 0, 'generic_book_photos' => 0, 'profile_photos' => 0]);
});

test('image backfill command can preview without writing and can be repeated safely', function () {
    $book = imageBackfillBook();
    $user = User::factory()->create(['profile_photo' => null]);
    $this->artisan('media:fill-missing-images', ['--dry-run' => true])->assertSuccessful();
    expect($book->fresh()->cover_image)->toBeNull()->and($user->fresh()->profile_photo)->toBeNull();

    $this->artisan('media:fill-missing-images')->assertSuccessful();
    $cover = $book->fresh()->cover_image;
    $portrait = $user->fresh()->profile_photo;
    $this->artisan('media:fill-missing-images')->assertSuccessful();
    expect($book->fresh()->cover_image)->toBe($cover)->and($user->fresh()->profile_photo)->toBe($portrait);
});

test('seed catalogue and user images contain usable external image urls', function () {
    foreach (['books.json' => 'cover_image', 'users.json' => 'profile_photo'] as $file => $field) {
        $rows = json_decode(file_get_contents(database_path('JSON/'.$file)), true, 512, JSON_THROW_ON_ERROR);
        foreach ($rows as $row) {
            expect(filter_var($row[$field], FILTER_VALIDATE_URL))->not->toBeFalse()
                ->and($row[$field])->toStartWith('https://');
        }
    }
});
