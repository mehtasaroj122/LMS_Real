<?php

namespace App\Http\Controllers\Staff;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdatePasswordRequest;
use App\Http\Requests\Staff\UpdateProfileRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\Auth\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function __construct(
        private readonly AccountDeletionService $accountDeletionService
    ) {
    }

    public function index()
    {
        Gate::authorize('access-staff');
        $user = Auth::user()->loadMissing('staff.department');
        $accountDeletionState = $this->accountDeletionService->eligibilityFor($user);

        return view('Staff.Setting', compact('user', 'accountDeletionState'));
    }

    public function update(UpdateProfileRequest $request)
    {
        Gate::authorize('access-staff');
        $user = $request->user()->loadMissing('staff.department');
        $validated = $request->validated();
        $originalProfileState = $user->only(ActivityLogger::userProfileAuditFields());

        try {
            if ($request->hasFile('profile_photo')) {
                $validated['profile_photo'] = $this->handlePhotoUpload($user, $request->file('profile_photo'));
            }
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload profile photo. Please try again.',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['profile_photo' => 'Failed to upload profile photo. Please try again.']);
        }

        $user->update($validated);
        $user->refresh()->loadMissing('staff.department');
        $profileChanges = ActivityLogger::buildUserProfileChangeSet(
            $originalProfileState,
            $user->only(ActivityLogger::userProfileAuditFields())
        );
        $changedFields = $profileChanges['changed_fields'];
        $emailChanged = in_array('email', $changedFields, true);

        if (!empty($profileChanges['messages'])) {
            Notification::notify(
                user: $user,
                type: 'account.profile_updated',
                title: 'Profile Information Updated',
                message: 'Your profile information was updated: ' . $profileChanges['summary'],
                data: [
                    'ip' => request()->ip(),
                    'timestamp' => now(),
                    'changes' => $profileChanges['changes'],
                    'changed_fields' => $changedFields,
                ],
                relatedModel: 'User',
                relatedId: $user->id
            );
        }

        // Notify if email was changed
        if ($emailChanged) {
            Notification::notify(
                user: $user,
                type: 'account.email_changed',
                title: 'Email Address Changed',
                message: 'Your email address was changed to ' . $user->email,
                data: [
                    'old_email' => $profileChanges['changes']['email']['old'] ?? null,
                    'new_email' => $profileChanges['changes']['email']['new'] ?? null,
                ],
                relatedModel: 'User',
                relatedId: $user->id
            );
        }

        $message = $emailChanged
            ? 'Profile updated successfully. Your login email has been changed.'
            : 'Profile updated successfully.';

        ActivityLogger::logUserProfileChanges($user, $profileChanges, [
            'role_context' => 'staff',
            'email_changed' => $emailChanged,
        ]);

        $freshUser = $user->fresh()->loadMissing('staff.department');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'email_changed' => $emailChanged,
                'user' => [
                    'name' => $freshUser->name,
                    'email' => $freshUser->email,
                    'phone' => $freshUser->phone,
                    'address' => $freshUser->address,
                    'department' => $freshUser->staff?->department?->name,
                    'profile_photo' => $freshUser->profile_photo,
                    'profile_photo_url' => $this->resolveProfilePhotoUrl($freshUser),
                    'username' => $freshUser->username ?? Str::before($freshUser->email ?? '', '@'),
                ],
            ]);
        }

        return redirect()
            ->route('staff.settings.index')
            ->with('success', $message);
    }

    public function password(UpdatePasswordRequest $request)
    {
        Gate::authorize('access-staff');
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
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

        ActivityLogger::logActivity('password_changed', 'Staff changed password', 'auth', 'user', $user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
            ]);
        }

        return redirect()
            ->route('staff.settings.index')
            ->with('success', 'Password updated successfully.');
    }

    public function photo(Request $request)
    {
        Gate::authorize('access-staff');
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        try {
            $user = $request->user();
            $originalProfileState = $user->only(ActivityLogger::userProfileAuditFields());
            $path = $this->handlePhotoUpload($user, $request->file('profile_photo'));
            $user->update(['profile_photo' => $path]);
            $user->refresh();

            ActivityLogger::logUserProfileChanges(
                $user,
                ActivityLogger::buildUserProfileChangeSet(
                    $originalProfileState,
                    $user->only(ActivityLogger::userProfileAuditFields())
                ),
                ['role_context' => 'staff']
            );

            $message = 'Photo uploaded successfully.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => [
                        'profile_photo' => $path,
                        'profile_photo_url' => $this->resolveProfilePhotoUrl($user->fresh()),
                    ],
                ]);
            }

            return redirect()
                ->route('staff.settings.index')
                ->with('success', $message);
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
    }

    public function removePhoto(Request $request)
    {
        Gate::authorize('access-staff');
        $user = $request->user();
        $originalProfileState = $user->only(ActivityLogger::userProfileAuditFields());

        if (!$user->profile_photo) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No photo to remove.',
                ], 400);
            }

            return redirect()
                ->route('staff.settings.index')
                ->with('error', 'No photo to remove.');
        }

        $this->deleteStoredPhoto($user->profile_photo);
        $user->update(['profile_photo' => null]);
        $user->refresh();

        ActivityLogger::logUserProfileChanges(
            $user,
            ActivityLogger::buildUserProfileChangeSet(
                $originalProfileState,
                $user->only(ActivityLogger::userProfileAuditFields())
            ),
            ['role_context' => 'staff']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully.',
            ]);
        }

        return redirect()
            ->route('staff.settings.index')
            ->with('success', 'Profile photo removed successfully.');
    }

    public function checkEmail(Request $request)
    {
        Gate::authorize('access-staff');

        $validated = $request->validate([
            'email' => ['bail', 'required', 'email', 'max:255'],
        ]);

        $available = !User::query()
            ->where('email', $validated['email'])
            ->where('id', '!=', $request->user()->id)
            ->exists();

        return response()->json([
            'available' => $available,
        ]);
    }

    private function handlePhotoUpload(User $user, UploadedFile $file): string
    {
        $this->deleteStoredPhoto($user->profile_photo);

        $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('profile_pics', $filename, 'public');

        return 'storage/profile_pics/' . $filename;
    }

    private function deleteStoredPhoto(?string $photoPath): void
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

    private function resolveProfilePhotoUrl(User $user): ?string
    {
        if (!$user->profile_photo) {
            return null;
        }

        return str_starts_with($user->profile_photo, 'http')
            ? $user->profile_photo
            : asset($user->profile_photo);
    }
}
