<?php

namespace App\Mail;

use App\Models\IssuedBook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookReturnedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public IssuedBook $issuedBook,
        public float $fineAmount = 0,
        public string $recipientEmail = '',
    ) {}

    public function envelope(): Envelope
    {
        $email = $this->recipientEmail ?: $this->issuedBook->student->user->email;
        return new Envelope(
            to: [$email],
            subject: 'Book Return Processed - ' . $this->issuedBook->book->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book-returned',
            with: [
                'issuedBook' => $this->issuedBook,
                'student' => $this->issuedBook->student,
                'book' => $this->issuedBook->book,
                'fineAmount' => $this->fineAmount,
            ],
        );
    }
}
