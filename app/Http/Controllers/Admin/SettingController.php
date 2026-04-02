<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LibrarySettingsRequest;
use App\Http\Requests\Admin\PasswordUpdateRequest;
use App\Http\Requests\Admin\ProfileUpdateRequest;
use App\Models\FineSetting;
use App\Support\LibraryBranding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        Gate::authorize('access-admin');

        $user = Auth::user();
        $fineSetting = FineSetting::resolveActive();

        return view('Admin.Settings', compact('user', 'fineSetting'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        Gate::authorize('access-admin');

        $user = $request->user();
        $validated = $request->validated();
        $removeProfilePhoto = (bool) Arr::pull($validated, 'remove_profile_photo', false);

        try {
            if ($request->hasFile('profile_photo')) {
                $validated['profile_photo'] = $this->storeProfilePhoto($user, $request->file('profile_photo'));
            } elseif ($removeProfilePhoto) {
                $this->deleteStoredProfilePhoto($user->profile_photo);
                $validated['profile_photo'] = null;
            }
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload photo. Please try again.',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['profile_photo' => 'Failed to upload photo. Please try again.']);
        }

        $user->update($validated);

        ActivityLogger::logActivity(
            'profile_updated',
            'Admin profile updated',
            'user',
            'user',
            $user->id,
            ['changed_fields' => array_keys($validated)]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Profile updated successfully');
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        Gate::authorize('access-admin');

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('new_password')),
        ]);

        ActivityLogger::logActivity(
            'password_changed',
            'Admin password changed',
            'auth',
            'user',
            $user->id
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Password updated successfully');
    }

    public function updateLibrarySettings(LibrarySettingsRequest $request)
    {
        Gate::authorize('access-admin');

        $validated = $request->validated();
        $fineSetting = FineSetting::resolveActive();
        $removeLogo = (bool) Arr::pull($validated, 'remove_logo', false);

        try {
            if ($request->hasFile('logo_image')) {
                $validated['logo_path'] = $this->storeLibraryLogo($fineSetting->logo_path, $request->file('logo_image'));
            } elseif ($removeLogo) {
                $this->deleteStoredLibraryLogo($fineSetting->logo_path);
                $validated['logo_path'] = null;
            }
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload the library logo. Please try again.',
                    'errors' => [
                        'logo_image' => ['Failed to upload the library logo. Please try again.'],
                    ],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['logo_image' => 'Failed to upload the library logo. Please try again.']);
        }

        $validated['logo_fallback_text'] = trim((string) ($validated['logo_fallback_text'] ?? FineSetting::DEFAULTS['logo_fallback_text']));
        $validated['logo_fallback_text'] = $validated['logo_fallback_text'] !== ''
            ? $validated['logo_fallback_text']
            : FineSetting::DEFAULTS['logo_fallback_text'];

        $fineSetting->fill($validated);
        $fineSetting->is_active = true;
        $fineSetting->save();

        FineSetting::where('id', '!=', $fineSetting->id)->update(['is_active' => false]);
        $branding = LibraryBranding::refresh();

        ActivityLogger::logActivity(
            'library_settings_updated',
            'Library settings updated',
            'system',
            'fine_setting',
            $fineSetting->id,
            ['changed_settings' => array_keys($validated)]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Library settings updated successfully',
                'data' => $fineSetting->fresh(),
                'branding' => $branding,
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Library settings updated successfully');
    }

    public function removePhoto(Request $request)
    {
        Gate::authorize('access-admin');

        try {
            $user = $request->user();

            if (!$user->profile_photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile photo to remove',
                ], 400);
            }

            $this->deleteStoredProfilePhoto($user->profile_photo);

            $user->update(['profile_photo' => null]);

            ActivityLogger::logActivity(
                'profile_photo_removed',
                'Admin removed profile photo',
                'user',
                'user',
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove photo. Please try again.',
            ], 500);
        }
    }

    private function storeProfilePhoto(User $user, UploadedFile $file): string
    {
        $this->deleteStoredProfilePhoto($user->profile_photo);

        $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('profile_pics', $filename, 'public');

        return 'storage/profile_pics/' . $filename;
    }

    private function deleteStoredProfilePhoto(?string $photoPath): void
    {
        if (!$photoPath) {
            return;
        }

        $normalizedPath = str_starts_with($photoPath, 'storage/')
            ? substr($photoPath, 8)
            : ltrim($photoPath, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }

    private function storeLibraryLogo(?string $currentLogoPath, UploadedFile $file): string
    {
        $this->deleteStoredLibraryLogo($currentLogoPath);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $filename = 'library_logo_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $file->storeAs('branding/logos', $filename, 'public');

        return 'storage/branding/logos/' . $filename;
    }

    private function deleteStoredLibraryLogo(?string $logoPath): void
    {
        if (!$logoPath) {
            return;
        }

        $normalizedPath = str_starts_with($logoPath, 'storage/')
            ? substr($logoPath, 8)
            : ltrim($logoPath, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }
}
