<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\IssuedBook;

class BookReturnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public IssuedBook $issuedBook,
        public float $fineAmount = 0
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $book = $this->issuedBook->book;
        $student = $this->issuedBook->student;

        return (new MailMessage)
            ->subject('Book Return Processed - ' . config('app.name'))
            ->view('emails.book-returned', [
                'student' => $student,
                'book' => $book,
                'issuedBook' => $this->issuedBook,
                'fineAmount' => $this->fineAmount,
            ]);
    }
}
