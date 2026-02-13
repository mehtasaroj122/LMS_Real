<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\BookRequest;

class RequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BookRequest $bookRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->bookRequest->student;
        $book = $this->bookRequest->book;

        return (new MailMessage)
            ->subject('Book Request Update - ' . config('app.name'))
            ->view('emails.request-rejected', [
                'student' => $student,
                'book' => $book,
                'bookRequest' => $this->bookRequest,
            ]);
    }
}
