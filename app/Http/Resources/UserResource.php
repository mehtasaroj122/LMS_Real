<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
        ];

        // Only include student data if user is a student
        if ($this->role === 'student') {
            $data['student'] = $this->whenLoaded('student', fn () => $this->student ? new StudentResource($this->student) : null);
        }

        // Only include staff data if user is staff or admin
        if (in_array($this->role, ['staff', 'admin'])) {
            $data['staff'] = $this->whenLoaded('staff', fn () => $this->staff);
        }

        return $data;
    }
}
