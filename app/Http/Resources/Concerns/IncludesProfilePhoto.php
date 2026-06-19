<?php

namespace App\Http\Resources\Concerns;

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
        $profilePhoto = trim((string) $profilePhoto);

        if ($profilePhoto === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $profilePhoto)) {
            return $profilePhoto;
        }

        $storagePath = str_starts_with($profilePhoto, 'storage/')
            ? substr($profilePhoto, 8)
            : ltrim($profilePhoto, '/');

        return asset('storage/' . $storagePath);
    }
}
