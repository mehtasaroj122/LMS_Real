<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookIssuedSimple extends Mailable implements ShouldQueue
{
    use Queueable, QueuesLibraryMail;

    public function __construct(
        private string $studentEmail,
        private string $studentName,
        private string $bookTitle,
        private string $author,
        private string $issueDate,
        private string $dueDate,
    ) {
        $this->configureLibraryMailQueue();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->studentEmail],
            subject: 'Book Issued - Library Management System',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book-issued',
            with: [
                'studentName' => $this->studentName,
                'bookTitle' => $this->bookTitle,
                'author' => $this->author,
                'issueDate' => $this->issueDate,
                'dueDate' => $this->dueDate,
            ],
        );
    }

    protected function libraryMailFailureContext(): array
    {
        return [
            'student_email' => $this->studentEmail,
            'book_title' => $this->bookTitle,
        ];
    }
}
