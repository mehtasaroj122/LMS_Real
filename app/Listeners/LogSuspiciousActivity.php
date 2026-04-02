<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Models\User;
use App\Support\AccountLockoutManager;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LogSuspiciousActivity
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
    public function handle(Failed $event): void
    {
        try {
            $email = $event->credentials['email'] ?? null;
            
            if (!$email) {
                return;
            }

            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return;
            }

            // Get the number of failed attempts
            $throttleKey = AccountLockoutManager::throttleKey($email, (string) request()->ip());
            $attempts = RateLimiter::attempts($throttleKey) + 1;

            // Send a single early warning when the user reaches 3 failed attempts.
            if ($attempts === 3) {
                Notification::notify(
                    user: $user,
                    type: 'security.suspicious_activity',
                    title: 'Suspicious Activity Detected',
                    message: 'Multiple failed login attempts detected on your account from IP: ' . request()->ip(),
                    data: [
                        'activity_type' => 'failed_login_attempts',
                        'attempt_count' => $attempts,
                        'ip' => request()->ip(),
                        'timestamp' => now()
                    ],
                    relatedModel: 'User',
                    relatedId: $user->id
                );

                // Log the suspicious activity
                Log::warning('Suspicious activity: ' . $attempts . ' failed login attempts for user ' . $email . ' from IP: ' . request()->ip());
            }
        } catch (\Exception $e) {
            Log::error('Error logging suspicious activity: ' . $e->getMessage());
        }
    }
}
