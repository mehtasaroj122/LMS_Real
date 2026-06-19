<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'book' => new BookResource($this->whenLoaded('book')),
            'request_date' => optional($this->request_date)->toDateTimeString(),
            'status' => $this->status,
            'processed_by' => $this->processed_by,
            'processed_date' => optional($this->processed_date)->toDateTimeString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
