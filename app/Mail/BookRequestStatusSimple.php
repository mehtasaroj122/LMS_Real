<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookRequestStatusSimple extends Mailable
{
    public function __construct(
        private string $studentEmail,
        private string $studentName,
        private string $bookTitle,
        private string $status,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->studentEmail],
            subject: 'Book Request ' . ucfirst($this->status) . ' - Library Management System',
        );
    }

    public function content(): Content
    {
        $viewName = $this->status === 'approved' ? 'emails.request-approved' : 'emails.request-rejected';
        return new Content(
            view: $viewName,
            with: [
                'studentName' => $this->studentName,
                'bookTitle' => $this->bookTitle,
            ],
        );
    }
}
