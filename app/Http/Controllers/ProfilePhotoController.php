<?php

namespace App\Http\Controllers;

use App\Support\ProfilePhoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfilePhotoController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $normalizedPath = ProfilePhoto::normalizeStoragePath($path);

        abort_unless(
            $normalizedPath !== null && ProfilePhoto::isProfilePhotoPath($normalizedPath),
            404
        );

        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        return response()->file(Storage::disk('public')->path($normalizedPath), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
