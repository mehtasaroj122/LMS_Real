<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->relationLoaded('category') ? $this->category?->name : null;

        return [
            'id' => $this->id,
            'accession_no' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'category' => $category,
            'quantity' => (int) $this->total_copies,
            'available_quantity' => (int) $this->available_copies,
            'status' => $this->status,
        ];
    }
}
