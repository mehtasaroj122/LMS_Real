<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\IncludesProfilePhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    use IncludesProfilePhoto;

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
            'status' => $user?->status,
            'profile_photo' => $user?->profile_photo,
            'profile_photo_url' => $this->profilePhotoUrl($user?->profile_photo),
        ];
    }
}
