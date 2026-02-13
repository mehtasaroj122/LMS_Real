<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class AccountUnlockController extends \App\Http\Controllers\Controller
{
    /**
     * Unlock account via email link
     */
    public function unlockFromEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'ip' => 'nullable|ip',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', '❌ User not found');
        }

        if ($request->ip) {
            $throttleKey = strtolower($user->email) . '|' . $request->ip;
            RateLimiter::clear($throttleKey);
        } else {
            // Clear all IPs
            \DB::table('cache')
                ->where('key', 'like', '%throttle|' . strtolower($user->email) . '%')
                ->delete();
        }

        Log::info('Account unlocked via email link', [
            'email' => $user->email,
            'ip' => $request->ip,
            'timestamp' => now(),
        ]);

        // Send notification to user
        $user->notify(new \App\Notifications\AccountUnlockNotification($user, $request->ip));

        return redirect()->route('login')
            ->with('success', '✅ Account unlocked successfully. You can now login with your password.');
    }
}
