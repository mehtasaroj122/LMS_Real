<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MissingImageBackfill
{
    public const BOOK_PHOTO_URL = 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=600&q=80';

    public function run(bool $dryRun = false): array
    {
        $catalogue = json_decode(file_get_contents(database_path('JSON/books.json')), true, 512, JSON_THROW_ON_ERROR);
        $covers = [];
        foreach ($catalogue as $book) {
            if (filter_var($book['cover_image'] ?? null, FILTER_VALIDATE_URL)) {
                $covers[$this->bookKey($book['title'], $book['author'])] = $book['cover_image'];
            }
        }

        $counts = ['book_covers' => 0, 'generic_book_photos' => 0, 'profile_photos' => 0];
        $this->missingImages('books', 'cover_image')
            ->select(['id', 'title', 'author'])
            ->chunkById(100, function ($books) use ($covers, $dryRun, &$counts): void {
                foreach ($books as $book) {
                    $cover = $covers[$this->bookKey($book->title, $book->author)] ?? self::BOOK_PHOTO_URL;
                    $updated = $dryRun ? 1 : $this->missingImages('books', 'cover_image')
                        ->where('id', $book->id)->update(['cover_image' => $cover]);
                    $counts[$cover === self::BOOK_PHOTO_URL ? 'generic_book_photos' : 'book_covers'] += $updated;
                }
            });

        $this->missingImages('users', 'profile_photo')
            ->select(['id', 'gender'])
            ->chunkById(100, function ($users) use ($dryRun, &$counts): void {
                foreach ($users as $user) {
                    $updated = $dryRun ? 1 : $this->missingImages('users', 'profile_photo')
                        ->where('id', $user->id)
                        ->update(['profile_photo' => self::stockPortraitUrl($user->id, $user->gender)]);
                    $counts['profile_photos'] += $updated;
                }
            });

        return $counts;
    }

    /** These are sample portraits, never verified identities of library users. */
    public static function stockPortraitUrl(int $id, ?string $gender = null): string
    {
        $gender = strtolower(trim((string) $gender));
        $collection = match ($gender) {
            'female' => 'women',
            'male' => 'men',
            default => $id % 2 === 0 ? 'women' : 'men',
        };
        $number = intdiv(max(0, $id - 1), 2) % 100;

        return "https://randomuser.me/api/portraits/{$collection}/{$number}.jpg";
    }

    private function missingImages(string $table, string $column): Builder
    {
        return DB::table($table)->where(fn (Builder $query) => $query
            ->whereNull($column)->orWhereRaw("TRIM({$column}) = ''"));
    }

    private function bookKey(string $title, string $author): string
    {
        return Str::lower(Str::squish($title)).'|'.Str::lower(Str::squish($author));
    }
}
