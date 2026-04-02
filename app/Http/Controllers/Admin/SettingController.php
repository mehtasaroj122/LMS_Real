<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LibrarySettingsRequest;
use App\Http\Requests\Admin\PasswordUpdateRequest;
use App\Http\Requests\Admin\ProfileUpdateRequest;
use App\Models\FineSetting;
use App\Models\Notification;
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

        // Capture old values before update
        $oldEmail = $user->email;
        $oldName = $user->name;
        $hadProfilePhoto = $user->profile_photo !== null;
        
        $user->update($validated);

        // Build list of changes
        $changes = [];
        if (isset($validated['name']) && $oldName !== $validated['name']) {
            $changes[] = "name: {$oldName} → {$validated['name']}";
        }
        if (isset($validated['email']) && $oldEmail !== $validated['email']) {
            $changes[] = "email: {$oldEmail} → {$validated['email']}";
        }
        if (isset($validated['profile_photo']) || $removeProfilePhoto) {
            if ($removeProfilePhoto) {
                $changes[] = "profile picture: removed";
            } elseif ($hadProfilePhoto) {
                $changes[] = "profile picture: updated";
            } else {
                $changes[] = "profile picture: added";
            }
        }

        // Notify about profile update with specific changes
        if (!empty($changes)) {
            $changesSummary = implode(", ", $changes);
            Notification::notify(
                user: $user,
                type: 'account.profile_updated',
                title: 'Profile Information Updated',
                message: "Your profile information was updated: {$changesSummary}",
                data: [
                    'ip' => request()->ip(),
                    'timestamp' => now(),
                    'changes' => $changes,
                    'changed_fields' => array_keys($validated)
                ],
                relatedModel: 'User',
                relatedId: $user->id
            );
        }

        // Notify if email was changed
        if ($oldEmail !== ($validated['email'] ?? $oldEmail)) {
            Notification::notify(
                user: $user,
                type: 'account.email_changed',
                title: 'Email Address Changed',
                message: 'Your email address was changed to ' . $validated['email'],
                data: ['old_email' => $oldEmail, 'new_email' => $validated['email']],
                relatedModel: 'User',
                relatedId: $user->id
            );
        }

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

        // Notify about password change with details
        $ipAddress = request()->ip();
        $timestamp = now();
        $changeMessage = "Your password was changed successfully on {$timestamp->format('M d, Y')} at {$timestamp->format('h:i A')} from IP {$ipAddress}";
        
        Notification::notify(
            user: $user,
            type: 'account.password_changed',
            title: 'Password Changed Successfully',
            message: $changeMessage,
            data: [
                'ip' => $ipAddress,
                'timestamp' => $timestamp,
                'date_formatted' => $timestamp->format('M d, Y h:i A'),
                'user_agent' => request()->header('User-Agent')
            ],
            relatedModel: 'User',
            relatedId: $user->id
        );

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

        // Capture old values before update
        $oldValues = $fineSetting->toArray();
        
        $fineSetting->fill($validated);
        $fineSetting->is_active = true;
        $fineSetting->save();

        FineSetting::where('id', '!=', $fineSetting->id)->update(['is_active' => false]);
        $branding = LibraryBranding::refresh();

        // Build list of changes for notification (only show actual changes)
        $changes = [];
        foreach ($validated as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            
            // Skip system fields and unchanged values (use loose comparison to handle type differences)
            if (in_array($field, ['created_at', 'updated_at', 'id'])) {
                continue;
            }
            
            // Convert to string for comparison to handle type differences between form and database
            if ((string)$oldValue === (string)$newValue) {
                continue;
            }
            
            // Format the change message
            $oldValueStr = $oldValue ?? 'not set';
            $newValueStr = $newValue ?? 'not set';
            
            // For boolean fields, convert to yes/no
            if (is_bool($oldValue) || is_bool($newValue)) {
                $oldValueStr = $oldValue ? 'yes' : 'no';
                $newValueStr = $newValue ? 'yes' : 'no';
            }
            
            // Truncate long values for readability
            if (strlen((string) $oldValueStr) > 50) {
                $oldValueStr = substr((string) $oldValueStr, 0, 47) . '...';
            }
            if (strlen((string) $newValueStr) > 50) {
                $newValueStr = substr((string) $newValueStr, 0, 47) . '...';
            }
            
            $changes[] = "{$field}: {$oldValueStr} → {$newValueStr}";
        }

        // Notify about library settings update
        $user = Auth::user();
        if ($user) {
            if (!empty($changes)) {
                $changesSummary = implode(", ", $changes);
                $message = "Library settings have been updated: {$changesSummary}";
            } else {
                $message = 'Library settings have been updated successfully';
            }
            
            Notification::notify(
                user: $user,
                type: 'system.settings_updated',
                title: 'Library Settings Updated',
                message: $message,
                data: [
                    'changed_settings' => array_keys($validated),
                    'changes' => $changes,
                    'timestamp' => now()
                ],
                relatedModel: 'FineSetting',
                relatedId: $fineSetting->id
            );
        }

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
