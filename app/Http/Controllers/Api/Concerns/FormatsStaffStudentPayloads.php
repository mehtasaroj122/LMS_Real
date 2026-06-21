<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FormatsStaffStudentPayloads
{
    protected function staffStudentPhotoPayload(Student $student): array
    {
        $photo = $this->resolveStaffStudentProfilePhoto($student);

        return [
            'profile_photo' => $photo,
            'profile_photo_url' => $this->staffStudentProfilePhotoUrl($photo),
        ];
    }

    protected function resolveStaffStudentProfilePhoto(Student $student): ?string
    {
        $userPhoto = trim((string) ($student->user?->profile_photo ?? ''));
        $studentPhoto = trim((string) ($student->getAttribute('profile_photo') ?? ''));

        return $userPhoto !== ''
            ? $userPhoto
            : ($studentPhoto !== '' ? $studentPhoto : null);
    }

    protected function staffStudentProfilePhotoUrl(?string $photo): ?string
    {
        $photo = trim((string) $photo);

        if ($photo === '') {
            return null;
        }

        if (Str::startsWith($photo, ['http://', 'https://'])) {
            return $photo;
        }

        $path = ltrim($photo, '/');

        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return url(Storage::url($path));
    }
}
