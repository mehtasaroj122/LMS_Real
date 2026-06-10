<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->relationLoaded('user') ? $this->user : null;
        $department = $this->relationLoaded('department') ? $this->department : null;

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'roll_no' => $this->roll_no,
            'email' => $user?->email,
            'phone' => $user?->phone,
            'faculty' => $department?->name,
            'semester' => $this->semester,
        ];
    }
}
