<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;

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
            // Extract email from the request
            $email = $event->request->email;
            
            // Find the user by email
            $user = User::where('email', $email)->first();
            
            if ($user) {
                // Send account locked notification
                Notification::notify(
                    user: $user,
                    type: 'security.account_locked',
                    title: 'Account Temporarily Locked',
                    message: 'Your account has been temporarily locked due to too many failed login attempts. Please try again after 15 minutes.',
                    data: [
                        'reason' => 'Too many failed login attempts',
                        'retry_after' => 900, // 15 minutes in seconds
                        'ip' => $event->request->ip(),
                        'timestamp' => now()
                    ],
                    relatedModel: 'User',
                    relatedId: $user->id
                );

                // Log the security event
                Log::warning('Account locked for user: ' . $email . ' from IP: ' . $event->request->ip());
            }
        } catch (\Exception $e) {
            Log::error('Error sending account locked notification: ' . $e->getMessage());
        }
    }
}
