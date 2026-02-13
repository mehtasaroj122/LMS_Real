<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Notify user of password change
        Notification::notify(
            user: $user,
            type: 'account.password_changed',
            title: 'Password Changed Successfully',
            message: 'Your password was changed on ' . now()->format('M d, Y h:i A'),
            data: ['ip' => request()->ip(), 'timestamp' => now()],
            relatedModel: 'User',
            relatedId: $user->id
        );

        return back()->with('status', 'password-updated');
    }
}
