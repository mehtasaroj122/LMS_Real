<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\IncludesProfilePhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use IncludesProfilePhoto;

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'address' => $this->address,
            'role' => $this->role,
            'status' => $this->status,
            'profile_photo' => $this->profile_photo,
            'profile_photo_url' => $this->profilePhotoUrl($this->profile_photo),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_login_at' => $this->last_login_at,
        ];

        // Only include student data if user is a student
        if ($this->role === 'student') {
            $data['student'] = $this->whenLoaded('student', fn () => $this->student ? new StudentResource($this->student) : null);
        }

        // Only include staff data if user is staff or admin
        if (in_array($this->role, ['staff', 'admin'])) {
            $data['staff'] = $this->whenLoaded('staff', function () {
                if (! $this->staff) {
                    return null;
                }

                return [
                    'id' => $this->staff->id,
                    'staff_id' => $this->staff->staff_id,
                    'department_id' => $this->staff->department_id,
                    'department' => $this->staff->department?->name,
                    'designation' => $this->staff->designation,
                    'join_date' => $this->staff->join_date,
                ];
            });
        }

        return $data;
    }
}
