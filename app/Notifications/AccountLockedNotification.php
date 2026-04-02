<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AccountLockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ?string $ip = null,
        protected ?int $retryAfter = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = max(1, (int) ceil(($this->retryAfter ?? config('security.rate_limiting.lockout_duration', 60) * 60) / 60));

        $unlockUrl = URL::temporarySignedRoute(
            'auth.unlock-from-email',
            now()->addHours(24),
            [
                'email' => $notifiable->email,
                'ip' => $this->ip,
            ]
        );

        return (new MailMessage)
            ->subject('Account temporarily locked - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We temporarily locked your account after repeated failed sign-in attempts.')
            ->line('Source IP: ' . ($this->ip ?: 'Multiple IP addresses'))
            ->line('Estimated unlock window: ' . $minutes . ' ' . Str::plural('minute', $minutes) . '.')
            ->line('If this activity was not yours, please review your password immediately.')
            ->action('Unlock account now', $unlockUrl)
            ->line('This secure unlock link expires in 24 hours.')
            ->salutation('Library Security Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'security.account_locked',
            'title' => 'Account Temporarily Locked',
            'message' => 'Your account has been temporarily locked after repeated failed sign-in attempts.',
            'ip' => $this->ip,
            'retry_after' => $this->retryAfter,
            'timestamp' => now(),
        ];
    }
}
