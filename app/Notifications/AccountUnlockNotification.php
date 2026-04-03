<?php

namespace App\Notifications;

use App\Notifications\Concerns\QueuesLibraryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountUnlockNotification extends Notification implements ShouldQueue
{
    use Queueable, QueuesLibraryNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected ?string $ip = null)
    {
        $this->configureLibraryNotificationQueue();
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
            ->view('emails.account-unlocked', [
                'userName' => $notifiable->name,
                'sourceIp' => $this->ip ?? 'Multiple IP addresses',
                'loginUrl' => route('login'),
            ]);
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

    protected function libraryNotificationFailureContext(): array
    {
        return [
            'notification_type' => 'account_unlocked',
            'ip' => $this->ip,
        ];
    }
}
