<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fine = null;
        if ($this->relationLoaded('fine') && $this->fine) {
            $fine = [
                'amount' => (float) $this->fine->amount,
                'status' => $this->fine->status,
            ];
        }

        return [
            'id' => $this->id,
            'issue_id' => $this->id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'book' => new BookResource($this->whenLoaded('book')),
            'issue_date' => optional($this->issue_date)->toDateString(),
            'due_date' => optional($this->due_date)->toDateString(),
            'return_date' => optional($this->return_date)->toDateString(),
            'fine_amount' => (float) $this->fine_amount,
            'fine' => $fine,
            'status' => $this->status,
            'condition' => $this->condition,
            'remarks' => $this->remarks,
        ];
    }
}
