<?php

namespace App\Http\Resources\Concerns;

use App\Models\Book;
use App\Support\ImageUrl;

trait IncludesBookCover
{
    protected function bookCoverPayload(?Book $book): array
    {
        return [
            'cover_image' => $book?->cover_image,
            'cover_image_url' => ImageUrl::resolve($book?->cover_image),
        ];
    }
}
