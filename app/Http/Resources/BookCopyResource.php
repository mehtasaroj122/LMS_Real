<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookCopyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_copy_id' => $this->id,
            'book_id' => $this->book_id,
            'accession_number' => $this->accession_number,
            'entry_date' => optional($this->entry_date)->toDateString(),
            'book_type' => $this->book_type,
            'status' => $this->resource->circulationStatus(),
            'shelf_location' => $this->shelf_location,
            'condition' => $this->condition,
            'price' => $this->price,
            'remarks' => $this->remarks,
            'book' => $this->whenLoaded('book', fn () => [
                'id' => $this->book->id,
                'title' => $this->book->title,
                'author' => $this->book->author,
                'isbn' => $this->book->isbn,
                'category' => $this->book->category?->name,
            ]),
        ];
    }
}
