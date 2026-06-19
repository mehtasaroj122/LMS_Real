<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $book = $this->relationLoaded('issuedBook') && $this->issuedBook?->relationLoaded('book')
            ? $this->issuedBook->book
            : null;

        return [
            'id' => $this->id,
            'issue_id' => $this->issued_book_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'book' => $book ? new BookResource($book) : null,
            'issue' => new IssueResource($this->whenLoaded('issuedBook')),
            'amount' => (float) $this->amount,
            'days_late' => (int) $this->days_late,
            'status' => $this->status,
            'paid_on' => optional($this->paid_on)->toDateString(),
            'remarks' => $this->remarks,
        ];
    }
}
