<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ProfilePhoto
{
    public static function resolveUrl(?string $photoPath): ?string
    {
        $normalizedPhotoPath = trim((string) $photoPath);

        if ($normalizedPhotoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $normalizedPhotoPath)) {
            return $normalizedPhotoPath;
        }

        $normalizedStoragePath = self::normalizeStoragePath($normalizedPhotoPath);

        if (!$normalizedStoragePath || !self::isProfilePhotoPath($normalizedStoragePath)) {
            return null;
        }

        if (!Storage::disk('public')->exists($normalizedStoragePath)) {
            return null;
        }

        $segments = array_map('rawurlencode', explode('/', ltrim($normalizedStoragePath, '/')));

        return url('/profile-photos/' . implode('/', $segments));
    }

    public static function normalizeStoragePath(?string $photoPath): ?string
    {
        $normalizedPhotoPath = trim((string) $photoPath);

        if ($normalizedPhotoPath === '') {
            return null;
        }

        if (str_starts_with($normalizedPhotoPath, 'storage/')) {
            return substr($normalizedPhotoPath, 8);
        }

        return ltrim($normalizedPhotoPath, '/');
    }

    public static function isProfilePhotoPath(?string $photoPath): bool
    {
        $normalizedPhotoPath = ltrim((string) $photoPath, '/');

        if ($normalizedPhotoPath === '') {
            return false;
        }

        return str_starts_with(strtolower($normalizedPhotoPath), 'profile_pics/');
    }
}
