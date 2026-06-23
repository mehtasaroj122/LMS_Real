<?php

namespace App\Http\Controllers\Student;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Fine;
use App\Models\User;
use App\Models\Notification;
use App\Services\Auth\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AccountDeletionService $accountDeletionService
    ) {
    }

    public function index()
    {
        Gate::authorize('access-student');
        
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)
            ->with('department')
            ->first();

        if (!$student) {
            return view('Student.Profile', [
                'user' => $user,
                'student' => null,
                'booksIssuedCount' => 0,
                'unpaidFinesCount' => 0,
                'pendingRequestsCount' => 0,
                'totalFinesAmount' => 0,
                'finesPaidAmount' => 0,
                'approvedRequestsCount' => 0,
                'accountDeletionState' => $this->accountDeletionService->eligibilityFor($user),
            ]);
        }

        // Count books issued (not yet returned)
        $booksIssuedCount = $student->issuedBooks()->whereNull('return_date')->count();

        // Count pending fines
        $unpaidFinesCount = Fine::where('student_id', $student->id)
            ->where('status', 'pending')
            ->count();

        // Count pending requests
        $pendingRequestsCount = $student->bookRequests()->where('status', 'pending')->count();

        // Total fines amount
        $totalFinesAmount = Fine::where('student_id', $student->id)->sum('amount');

        // Fines paid amount
        $finesPaidAmount = Fine::where('student_id', $student->id)
            ->where('status', 'paid')
            ->sum('amount');

        // Approved requests count
        $approvedRequestsCount = $student->bookRequests()->where('status', 'approved')->count();

        return view('Student.Profile', [
            'user' => $user,
            'student' => $student,
            'booksIssuedCount' => $booksIssuedCount,
            'unpaidFinesCount' => $unpaidFinesCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'totalFinesAmount' => $totalFinesAmount,
            'finesPaidAmount' => $finesPaidAmount,
            'approvedRequestsCount' => $approvedRequestsCount,
            'accountDeletionState' => $this->accountDeletionService->eligibilityFor($user),
        ]);
    }

    public function updatePersonalInfo(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();
        $originalProfileState = $user->only(ActivityLogger::userProfileAuditFields());

        if (!$request->has('name') && $request->has('fullName')) {
            $request->merge(['name' => $request->input('fullName')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            $user->refresh();
            $profileChanges = ActivityLogger::buildUserProfileChangeSet(
                $originalProfileState,
                $user->only(ActivityLogger::userProfileAuditFields())
            );
            $emailChanged = in_array('email', $profileChanges['changed_fields'], true);

            if (!empty($profileChanges['messages'])) {
                Notification::notify(
                    user: $user,
                    type: 'account.profile_updated',
                    title: 'Profile Information Updated',
                    message: 'Your profile information was updated: ' . $profileChanges['summary'],
                    data: [
                        'ip' => request()->ip(),
                        'changes' => $profileChanges['changes'],
                        'changed_fields' => $profileChanges['changed_fields'],
                    ],
                    relatedModel: 'User',
                    relatedId: $user->id
                );
            }

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

            ActivityLogger::logUserProfileChanges($user, $profileChanges, [
                'role_context' => 'student',
                'email_changed' => $emailChanged,
            ]);

            $freshUser = $user->fresh()->loadMissing('student.department');

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'email_changed' => $emailChanged,
                'user' => [
                    'name' => $freshUser->name,
                    'email' => $freshUser->email,
                    'phone' => $freshUser->phone,
                    'gender' => $freshUser->gender,
                    'address' => $freshUser->address,
                    'department' => $freshUser->student?->department?->name,
                    'profile_photo' => $freshUser->profile_photo,
                    'profile_photo_url' => $this->resolveProfilePhotoUrl($freshUser),
                    'username' => $freshUser->email ? explode('@', $freshUser->email)[0] : 'student',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update your profile right now.',
            ], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();

        if (!$request->hasFile('profile_photo') && $request->hasFile('photo')) {
            $request->files->set('profile_photo', $request->file('photo'));
        }

        $validated = $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $originalProfileState = $user->only(ActivityLogger::userProfileAuditFields());

            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            if (!Storage::disk('public')->exists('profile_pics')) {
                Storage::disk('public')->makeDirectory('profile_pics');
            }

            $fileName = 'user_' . $user->id . '_' . time() . '.' . $validated['profile_photo']->getClientOriginalExtension();
            $path = $validated['profile_photo']->storeAs('profile_pics', $fileName, 'public');

            $user->update([
                'profile_photo' => $path,
            ]);
            $user->refresh();

            ActivityLogger::logUserProfileChanges(
                $user,
                ActivityLogger::buildUserProfileChangeSet(
                    $originalProfileState,
                    $user->only(ActivityLogger::userProfileAuditFields())
                ),
                ['role_context' => 'student']
            );

            $freshUser = $user->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully.',
                'photoUrl' => asset('storage/' . $path),
                'user' => [
                    'profile_photo' => $freshUser->profile_photo,
                    'profile_photo_url' => $this->resolveProfilePhotoUrl($freshUser),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to upload your photo right now.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();

        if (!$request->has('current_password') && $request->has('currentPassword')) {
            $request->merge(['current_password' => $request->input('currentPassword')]);
        }

        if (!$request->has('password') && $request->has('newPassword')) {
            $request->merge([
                'password' => $request->input('newPassword'),
                'password_confirmation' => $request->input('newPassword_confirmation'),
            ]);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/\d/'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain uppercase, lowercase, and a number.',
        ]);

        try {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                    'errors' => [
                        'current_password' => ['Current password is incorrect.'],
                    ],
                ], 422);
            }

            if (Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password.',
                    'errors' => [
                        'password' => ['New password must be different from your current password.'],
                    ],
                ], 422);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
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

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update your password right now.',
            ], 500);
        }
    }

    public function removePhoto(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();

        try {
            $originalProfileState = $user->only(ActivityLogger::userProfileAuditFields());

            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->update([
                'profile_photo' => null,
            ]);
            $user->refresh();

            ActivityLogger::logUserProfileChanges(
                $user,
                ActivityLogger::buildUserProfileChangeSet(
                    $originalProfileState,
                    $user->only(ActivityLogger::userProfileAuditFields())
                ),
                ['role_context' => 'student']
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing photo. Please try again.',
            ], 500);
        }
    }

    public function checkEmail(Request $request)
    {
        Gate::authorize('access-student');

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

    public function edit()
    {
        Gate::authorize('access-student');
        return view('Student.Profile');
    }

    private function resolveProfilePhotoUrl(User $user): ?string
    {
        if (!$user->profile_photo) {
            return null;
        }

        $path = ltrim($user->profile_photo, '/');

        return str_starts_with($user->profile_photo, 'http')
            ? $user->profile_photo
            : asset(str_starts_with($path, 'storage/') ? $path : 'storage/' . $path);
    }
}
