<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->relationLoaded('category') ? $this->category?->name : null;
        $available = $this->resource->availableForBorrowingCount();

        return [
            'id' => $this->id,
            'accession_no' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'category' => $category,
            'condition' => $this->display_condition,
            'location' => $this->shelf_no,
            'quantity' => (int) $this->total_copies,
            'available_quantity' => $available,
            'status' => $available > 0 ? 'available' : 'unavailable',
            'cover_image' => $this->cover_image,
            'cover_image_url' => $this->cover_image
                ? (str_starts_with($this->cover_image, 'http')
                    ? $this->cover_image
                    : asset('storage/' . $this->cover_image))
                : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'copies' => BookCopyResource::collection($this->whenLoaded('copies')),
        ];
    }
}
