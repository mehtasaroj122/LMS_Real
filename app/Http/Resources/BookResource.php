<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\IncludesBookCover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    use IncludesBookCover;

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
            ...$this->bookCoverPayload($this->resource),
            'created_at' => $this->created_at?->toDateTimeString(),
            'copies' => BookCopyResource::collection($this->whenLoaded('copies')),
        ];
    }
}
