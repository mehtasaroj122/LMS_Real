<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountUnlockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected ?string $ip = null)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Account unlocked - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account lock has been cleared and you can sign in again.')
            ->line('Related IP: ' . ($this->ip ?? 'Multiple IP addresses'))
            ->line('If you did not request this action, please change your password immediately and contact support.')
            ->salutation('Library Security Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'security.account_unlock',
            'title' => 'Account Unlocked',
            'message' => 'Your account has been unlocked. If you did not request this, please change your password immediately.',
            'ip' => $this->ip,
            'timestamp' => now(),
        ];
    }
}
