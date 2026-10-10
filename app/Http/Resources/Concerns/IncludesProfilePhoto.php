<?php

namespace App\Http\Resources\Concerns;

use App\Support\ImageUrl;

trait IncludesProfilePhoto
{
    protected function profilePhotoPayload($user): array
    {
        $profilePhoto = $user?->profile_photo;

        return [
            'profile_photo' => $profilePhoto,
            'profile_photo_url' => $this->profilePhotoUrl($profilePhoto),
        ];
    }

    protected function profilePhotoUrl(?string $profilePhoto): ?string
    {
        return ImageUrl::resolve($profilePhoto);
    }
}
