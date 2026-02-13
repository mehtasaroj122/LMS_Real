<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\IssuedBook;

class BookIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public IssuedBook $issuedBook) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $book = $this->issuedBook->book;
        $student = $this->issuedBook->student;

        return (new MailMessage)
            ->subject('Book Issued - ' . config('app.name'))
            ->view('emails.book-issued', [
                'student' => $student,
                'book' => $book,
                'issuedBook' => $this->issuedBook,
            ]);
    }
}
