<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Fine;

class FineWaivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Fine $fine) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->fine->student;

        return (new MailMessage)
            ->subject('Fine Waived - ' . config('app.name'))
            ->view('emails.fine-waived', [
                'student' => $student,
                'fine' => $this->fine,
            ]);
    }
}
