<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $original = $user->only(ActivityLogger::userProfileAuditFields());

        $user->fill($request->safe()->only(['name', 'phone']));
        $user->save();

        $changeSet = ActivityLogger::buildUserProfileChangeSet($original, $user->fresh()->only(ActivityLogger::userProfileAuditFields()));
        ActivityLogger::logUserProfileChanges($user, $changeSet, ['source' => 'mobile_api']);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user->fresh(['student.user', 'student.department', 'staff'])),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->password || ! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return response()->json([
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

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
