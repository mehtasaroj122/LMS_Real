<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookReturnedSimple extends Mailable
{
    public function __construct(
        private string $studentEmail,
        private string $studentName,
        private string $bookTitle,
        private string $condition,
        private float $fineAmount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->studentEmail],
            subject: 'Book Return Processed - Library Management System',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book-returned',
            with: [
                'studentName' => $this->studentName,
                'bookTitle' => $this->bookTitle,
                'condition' => $this->condition,
                'fineAmount' => $this->fineAmount,
            ],
        );
    }
}
