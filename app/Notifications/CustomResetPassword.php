<?php

namespace App\Notifications;

use App\Mail\PasswordResetLinkMail;
use App\Notifications\Concerns\QueuesLibraryNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;

class CustomResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable, QueuesLibraryNotification;

    public function __construct($token)
    {
        parent::__construct($token);
        $this->configureLibraryNotificationQueue();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $email = $notifiable->getEmailForPasswordReset();

        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ], false));

        return (new PasswordResetLinkMail(
            $resetUrl,
            $email,
            $notifiable->name
        ))->to($email, $notifiable->name);
    }

    protected function libraryNotificationFailureContext(): array
    {
        return [
            'notification_type' => 'password_reset_link',
        ];
    }
}
