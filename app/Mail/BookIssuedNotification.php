<?php

namespace App\Mail;

use App\Models\IssuedBook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookIssuedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public IssuedBook $issuedBook,
        public string $recipientEmail = '',
    ) {}

    public function envelope(): Envelope
    {
        $email = $this->recipientEmail ?: $this->issuedBook->student->user->email;
        return new Envelope(
            to: [$email],
            subject: 'Book Issued - ' . $this->issuedBook->book->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book-issued',
            with: [
                'issuedBook' => $this->issuedBook,
                'student' => $this->issuedBook->student,
                'book' => $this->issuedBook->book,
            ],
        );
    }
}
