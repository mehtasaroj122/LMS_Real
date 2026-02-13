<?php

namespace App\Mail;

use App\Models\BookRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookRequestStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookRequest $bookRequest,
        public string $recipientEmail = '',
    ) {}

    public function envelope(): Envelope
    {
        $email = $this->recipientEmail ?: $this->bookRequest->student->user->email;
        $status = strtoupper($this->bookRequest->status);
        return new Envelope(
            to: [$email],
            subject: "Book Request {$status} - {$this->bookRequest->book->title}",
        );
    }

    public function content(): Content
    {
        $view = ($this->bookRequest->status === 'approved')
            ? 'emails.request-approved'
            : 'emails.request-rejected';

        return new Content(
            view: $view,
            with: [
                'bookRequest' => $this->bookRequest,
                'student' => $this->bookRequest->student,
                'book' => $this->bookRequest->book,
            ],
        );
    }
}
