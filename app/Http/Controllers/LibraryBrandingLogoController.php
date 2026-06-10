<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LibraryBrandingLogoController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $normalizedPath = ltrim($path, '/');

        abort_unless(
            $normalizedPath !== '' && str_starts_with($normalizedPath, 'branding/logos/'),
            404
        );

        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        return response()->file(Storage::disk('public')->path($normalizedPath), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
