<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Concerns\IncludesProfilePhoto;
use App\Http\Resources\UserResource;
use App\Models\User;
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

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ], 200);
    }

    public function profile(Request $request): UserResource
    {
        return new UserResource(
            $request->user()->load(['student.user', 'student.department', 'staff'])
        );
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
