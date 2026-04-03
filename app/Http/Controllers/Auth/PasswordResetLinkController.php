<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            Log::error('Failed to queue password reset link email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not queue your password reset email right now. Please try again shortly.']);
        }

        // Notify user of password reset request if user exists
        if ($user && $status == Password::RESET_LINK_SENT) {
            Notification::notify(
                user: $user,
                type: 'account.password_reset',
                title: 'Password Reset Requested',
                message: 'A password reset link was sent to your email. If you did not request this, please ignore.',
                data: ['ip' => request()->ip(), 'timestamp' => now()],
                relatedModel: 'User',
                relatedId: $user->id
            );

            Log::info('Queued password reset link email', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
