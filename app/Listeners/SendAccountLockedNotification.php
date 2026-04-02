<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\AccountLockedNotification;
use App\Support\AccountLockoutManager;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SendAccountLockedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Lockout $event): void
    {
        try {
            $email = (string) $event->request->input('email');
            $ip = (string) $event->request->ip();

            if ($email === '' || $ip === '') {
                return;
            }

            $throttleKey = AccountLockoutManager::throttleKey($email, $ip);
            $retryAfter = max(0, RateLimiter::availableIn($throttleKey));
            $minutes = max(1, (int) ceil($retryAfter / 60));

            if (! Cache::add(AccountLockoutManager::notificationMarkerKey($throttleKey), true, $retryAfter ?: AccountLockoutManager::decaySeconds())) {
                return;
            }
            
            // Find the user by email
            $user = User::where('email', $email)->first();
            
            if ($user) {
                Notification::notify(
                    user: $user,
                    type: 'security.account_locked',
                    title: 'Account Temporarily Locked',
                    message: 'Your account has been temporarily locked after repeated failed sign-in attempts.',
                    data: [
                        'reason' => 'Too many failed login attempts',
                        'retry_after' => $retryAfter,
                        'ip' => $ip,
                        'minutes_remaining' => $minutes,
                        'throttle_key' => $throttleKey,
                        'timestamp' => now(),
                    ],
                    relatedModel: 'User',
                    relatedId: $user->id
                );

                if (AccountLockoutManager::emailUnlockEnabled()) {
                    $user->notify(new AccountLockedNotification($ip, $retryAfter));
                }

                Log::warning('Account locked for user: ' . $email . ' from IP: ' . $ip . ' for ' . $minutes . ' ' . Str::plural('minute', $minutes));
            }
        } catch (\Exception $e) {
            Log::error('Error sending account locked notification: ' . $e->getMessage());
        }
    }
}
