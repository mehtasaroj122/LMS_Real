<?php

namespace App\Notifications;

use App\Notifications\Concerns\QueuesLibraryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AccountLockedNotification extends Notification implements ShouldQueue
{
    use Queueable, QueuesLibraryNotification;

    public function __construct(
        protected ?string $ip = null,
        protected ?int $retryAfter = null,
    ) {
        $this->configureLibraryNotificationQueue();
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = max(1, (int) ceil(($this->retryAfter ?? config('security.rate_limiting.lockout_duration', 60) * 60) / 60));

        $unlockUrl = URL::temporarySignedRoute(
            'unlock-from-email',
            now()->addHours(24),
            [
                'email' => $notifiable->email,
                'ip' => $this->ip,
            ]
        );

        return (new MailMessage)
            ->subject('Account temporarily locked - ' . config('app.name'))
            ->view('emails.account-locked', [
                'userName' => $notifiable->name,
                'sourceIp' => $this->ip ?: 'Multiple IP addresses',
                'minutes' => $minutes,
                'unlockUrl' => $unlockUrl,
                'unlockExpiresAt' => now()->addHours(24)->format('M d, Y h:i A'),
                'minuteLabel' => Str::plural('minute', $minutes),
            ]);
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

    protected function libraryNotificationFailureContext(): array
    {
        return [
            'notification_type' => 'account_locked',
            'ip' => $this->ip,
        ];
    }
}
