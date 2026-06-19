<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->string('email')->lower()->toString();
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            try {
                Password::sendResetLink(['email' => $email]);

                Notification::notify(
                    user: $user,
                    type: 'account.password_reset',
                    title: 'Password Reset Requested',
                    message: 'A password reset link was sent to your email. If you did not request this, please ignore.',
                    data: ['ip' => request()->ip(), 'timestamp' => now()],
                    relatedModel: 'User',
                    relatedId: $user->id
                );
            } catch (\Throwable $exception) {
                Log::warning('Unable to send mobile password reset link', [
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Password reset instructions have been sent if the email exists.',
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')->toString()),
                    'remember_token' => Str::random(60),
                    'password_reset_at' => now(),
                    'force_password_change' => false,
                ])->save();

                Notification::notify(
                    user: $user,
                    type: 'account.password_reset',
                    title: 'Password Reset Successfully',
                    message: 'Your password was reset on ' . now()->format('M d, Y h:i A'),
                    data: ['ip' => request()->ip(), 'timestamp' => now()],
                    relatedModel: 'User',
                    relatedId: $user->id
                );

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => [__($status)],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }
}
