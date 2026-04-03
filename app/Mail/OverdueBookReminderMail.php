<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueBookReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, QueuesLibraryMail;

    public function __construct(
        public string $studentName,
        public string $bookTitle,
        public string $dueDate,
        public int $daysOverdue,
        public float $fineAmount = 0,
    ) {
        $this->configureLibraryMailQueue();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - Overdue Book Reminder',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.book-overdue',
            with: [
                'studentName' => $this->studentName,
                'bookTitle' => $this->bookTitle,
                'dueDate' => $this->dueDate,
                'daysOverdue' => $this->daysOverdue,
                'fineAmount' => $this->fineAmount,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function libraryMailFailureContext(): array
    {
        return [
            'student_name' => $this->studentName,
            'book_title' => $this->bookTitle,
        ];
    }
}
