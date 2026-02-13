<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AccountUnlockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $ip;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $ip = null)
    {
        $this->user = $user;
        $this->ip = $ip;
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
        $unlockUrl = URL::temporarySignedRoute(
            'auth.unlock-from-email',
            now()->addHours(24),
            [
                'email' => $notifiable->email,
                'ip' => $this->ip,
            ]
        );

        return (new MailMessage)
            ->subject('🔓 Account Unlock Request - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been locked due to multiple failed login attempts for security reasons.')
            ->line('IP Address: ' . ($this->ip ?? 'Multiple IPs'))
            ->line('Lock Duration: ' . config('security.rate_limiting.lockout_duration', 60) . ' minutes')
            ->line('')
            ->line('You can unlock your account immediately by clicking the button below, or wait for the automatic unlock.')
            ->action('🔓 Unlock Account Now', $unlockUrl)
            ->line('')
            ->line('**Security Tips:**')
            ->line('• This unlock link expires in 24 hours')
            ->line('• Make sure you use the correct password')
            ->line('• Enable two-factor authentication for extra security')
            ->line('• If you did not attempt to login, please contact support')
            ->line('')
            ->line('If you did not request this unlock, please ignore this email and contact support immediately.')
            ->footer('This is a security notification. Do not share this email or the unlock link with anyone.')
            ->salutation('Best regards, ' . config('app.name') . ' Security Team');
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
            'title' => 'Account Unlock Notification',
            'message' => 'Your account has been unlocked. If you did not request this, please change your password immediately.',
            'ip' => $this->ip,
            'timestamp' => now(),
        ];
    }
}
