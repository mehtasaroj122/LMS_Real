<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CompleteRegistrationRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Concerns\IncludesProfilePhoto;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\InvitedUserRegistrationService;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use IncludesProfilePhoto;

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->with(['student.user', 'student.department', 'staff'])
            ->where('email', $request->string('email')->lower()->toString())
            ->first();

        if (! $user || ! $user->password || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact the library administrator.',
            ], 403);
        }

        $tokenName = $request->input('device_name', 'android-mobile');
        $token = $user->createToken($tokenName)->plainTextToken;
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ], 200);
    }

    public function completeRegistration(
        CompleteRegistrationRequest $request,
        InvitedUserRegistrationService $registrationService,
        NotificationService $notificationService
    ): JsonResponse {
        $validated = $request->validated();
        $role = (string) $validated['role'];
        $identifierField = $role === 'staff' ? 'staff_id' : 'student_id';

        try {
            $user = $registrationService->completeRegistration([
                'role' => $role,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                $identifierField => $validated['identifier'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
            ]);
        } catch (\RuntimeException $exception) {
            $message = $exception->getMessage() === 'This account is already active. Please sign in with your email and password instead.'
                ? 'This account is already registered. Please sign in.'
                : $exception->getMessage();

            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'identifier' => [$message],
                ],
            ], 422);
        }

        event(new Registered($user));

        $notificationService->create(
            user: $user,
            type: 'account.registration_completed',
            title: 'Registration Completed',
            message: 'Your account registration was completed successfully.',
            data: [
                'role' => $user->role,
                'email' => $user->email,
                'source' => 'mobile_api',
            ],
            relatedModel: 'User',
            relatedId: $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully. You can now sign in.',
            'data' => [
                'role' => $user->role,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
            'data' => [],
        ], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['student.user', 'student.department', 'staff.department']);

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully.',
            'data' => (new UserResource($user))->resolve($request),
        ]);
    }

    public function check(Request $request): JsonResponse
    {
        $user = $request->user()->load(['student', 'staff']);

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'profile_photo' => $user->profile_photo,
                'profile_photo_url' => $this->profilePhotoUrl($user->profile_photo),
            ],
            'student_id' => $user->student?->id,
            'staff_id' => $user->staff?->id,
        ]);
    }

    public function testUnauthorized(): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }
}
