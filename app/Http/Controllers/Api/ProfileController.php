<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Resources\Concerns\IncludesProfilePhoto;
use App\Http\Resources\UserResource;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use IncludesProfilePhoto;

    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $original = $user->only(ActivityLogger::userProfileAuditFields());

        $user->fill($request->safe()->only(['name', 'phone', 'address']));
        $user->save();

        $changeSet = ActivityLogger::buildUserProfileChangeSet($original, $user->fresh()->only(ActivityLogger::userProfileAuditFields()));
        ActivityLogger::logUserProfileChanges($user, $changeSet, ['source' => 'mobile_api']);
        $this->notifications->notifyProfileUpdated($user->fresh(), $changeSet, $request);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => (new UserResource($user->fresh(['student.user', 'student.department', 'staff.department'])))->resolve($request),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->password || ! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->string('password')->toString()),
            'password_reset_at' => now(),
            'force_password_change' => false,
        ])->save();

        $this->notifications->notifyPasswordChanged($user->fresh(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => [],
        ]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = $request->user();
        $original = $user->only(ActivityLogger::userProfileAuditFields());

        $this->deleteStoredProfilePhoto($user->profile_photo);

        $path = $validated['photo']->store('profile_photos', 'public');

        $user->forceFill([
            'profile_photo' => $path,
        ])->save();

        ActivityLogger::logUserProfileChanges(
            $user,
            ActivityLogger::buildUserProfileChangeSet(
                $original,
                $user->fresh()->only(ActivityLogger::userProfileAuditFields())
            ),
            ['source' => 'mobile_api']
        );

        $this->notifications->notifyPhotoUpdated($user->fresh(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo uploaded successfully.',
            'data' => [
                'profile_photo' => $path,
                'profile_photo_url' => $this->profilePhotoUrl($path),
            ],
            'profile_photo' => $path,
            'profile_photo_url' => $this->profilePhotoUrl($path),
        ], 200);
    }

    public function removePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->profile_photo) {
            return response()->json([
                'success' => true,
                'message' => 'No profile photo found.',
                'data' => [
                    'profile_photo' => null,
                    'profile_photo_url' => null,
                ],
                'profile_photo' => null,
                'profile_photo_url' => null,
            ], 200);
        }

        $original = $user->only(ActivityLogger::userProfileAuditFields());

        $this->deleteStoredProfilePhoto($user->profile_photo);

        $user->forceFill([
            'profile_photo' => null,
        ])->save();

        ActivityLogger::logUserProfileChanges(
            $user,
            ActivityLogger::buildUserProfileChangeSet(
                $original,
                $user->fresh()->only(ActivityLogger::userProfileAuditFields())
            ),
            ['source' => 'mobile_api']
        );

        $this->notifications->notifyPhotoRemoved($user->fresh(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo removed successfully.',
            'data' => [
                'profile_photo' => null,
                'profile_photo_url' => null,
            ],
            'profile_photo' => null,
            'profile_photo_url' => null,
        ], 200);
    }

    public function deleteEligibility(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('student');

        if ($user->role !== 'student') {
            return response()->json([
                'can_delete' => false,
                'message' => 'Admin and staff accounts cannot be deleted from mobile.',
                'reasons' => ['Please contact system administrator.'],
            ], 200);
        }

        if (! $user->student) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        return response()->json($this->deleteEligibilityPayload($user->student->id), 200);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('student');

        if ($request->input('confirmation') !== 'DELETE') {
            return response()->json([
                'message' => 'Please type DELETE to confirm account deletion.',
            ], 422);
        }

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Admin and staff accounts cannot be deleted from mobile.',
            ], 403);
        }

        if (! $user->student) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        $eligibility = $this->deleteEligibilityPayload($user->student->id);

        if (! $eligibility['can_delete']) {
            return response()->json([
                'message' => 'Account deletion is currently unavailable.',
                'errors' => [
                    'issued_books' => $eligibility['issued_books'],
                    'pending_fines' => $eligibility['pending_fines'],
                    'active_requests' => $eligibility['active_requests'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($user, $request): void {
            $user->forceFill([
                'status' => 'inactive',
            ])->save();

            $this->notifications->notifyAccountDeactivated($user->fresh(['student', 'staff']), $request);

            $user->tokens()->delete();
        });

        return response()->json([
            'message' => 'Your account has been deactivated successfully.',
        ], 200);
    }

    public function destroyAccount(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['student', 'staff']);
        $payload = $request->all();

        if (! array_key_exists('confirmation_text', $payload) && array_key_exists('confirmation', $payload)) {
            $payload['confirmation_text'] = $payload['confirmation'];
        }

        $validator = Validator::make($payload, [
            'current_password' => ['required', 'string'],
            'confirmation_text' => ['required', 'string', Rule::in(['DELETE'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $user->password || ! Hash::check((string) $payload['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts cannot be deleted from mobile.',
            ], 403);
        }

        if ($user->role === 'student') {
            if (! $user->student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found.',
                ], 404);
            }

            $eligibility = $this->deleteEligibilityPayload($user->student->id);

            if (! $eligibility['can_delete']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account deletion is currently unavailable.',
                    'errors' => [
                        'issued_books' => $eligibility['issued_books'],
                        'pending_fines' => $eligibility['pending_fines'],
                        'active_requests' => $eligibility['active_requests'],
                    ],
                ], 422);
            }
        }

        DB::transaction(function () use ($user, $request): void {
            $user->forceFill([
                'status' => 'inactive',
            ])->save();

            $this->notifications->notifyAccountDeactivated($user->fresh(['student', 'staff']), $request);

            $user->tokens()->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deactivated successfully.',
            'data' => [
                'status' => 'inactive',
            ],
        ], 200);
    }

    private function deleteEligibilityPayload(int $studentId): array
    {
        $issuedBooks = IssuedBook::query()
            ->where('student_id', $studentId)
            ->whereNull('return_date')
            ->count();

        $pendingFines = (float) Fine::query()
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->sum('amount');

        $activeRequests = BookRequest::query()
            ->where('student_id', $studentId)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        $reasons = [];

        if ($issuedBooks > 0) {
            $reasons[] = 'You have ' . $issuedBooks . ' issued ' . str('book')->plural($issuedBooks) . '.';
        }

        if ($pendingFines > 0) {
            $reasons[] = 'You have Rs. ' . $this->formatAmount($pendingFines) . ' pending fines.';
        }

        if ($activeRequests > 0) {
            $reasons[] = 'You have ' . $activeRequests . ' active book ' . str('request')->plural($activeRequests) . '.';
        }

        $canDelete = $issuedBooks === 0 && $pendingFines <= 0 && $activeRequests === 0;

        return [
            'can_delete' => $canDelete,
            'message' => $canDelete
                ? 'Your account is eligible for deletion.'
                : 'Account deletion is currently unavailable.',
            'issued_books' => $issuedBooks,
            'pending_fines' => $pendingFines,
            'active_requests' => $activeRequests,
            'reasons' => $reasons,
        ];
    }

    private function deleteStoredProfilePhoto(?string $photoPath): void
    {
        $normalizedPath = $this->normalizePublicStoragePath($photoPath);

        if ($normalizedPath && Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }

    private function normalizePublicStoragePath(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || preg_match('/^https?:\/\//i', $path)) {
            return null;
        }

        return str_starts_with($path, 'storage/')
            ? substr($path, 8)
            : ltrim($path, '/');
    }

    private function formatAmount(float $amount): string
    {
        return fmod($amount, 1.0) === 0.0
            ? (string) (int) $amount
            : number_format($amount, 2, '.', '');
    }
}
