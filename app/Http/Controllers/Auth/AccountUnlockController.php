<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Notifications\AccountUnlockNotification;
use App\Support\AccountLockoutManager;
use Illuminate\Http\Request;
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
            AccountLockoutManager::clearLock($user->email, $request->ip);
        } else {
            AccountLockoutManager::clearLocksForEmail($user->email);
        }

        Log::info('Account unlocked via email link', [
            'email' => $user->email,
            'ip' => $request->ip,
            'timestamp' => now(),
        ]);

        // Send notification to user
        $user->notify(new AccountUnlockNotification($request->ip));

        return redirect()->route('login')
            ->with('success', 'Account unlocked successfully. You can now sign in again.');
    }
}
