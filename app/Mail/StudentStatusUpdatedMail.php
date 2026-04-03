<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StudentStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, QueuesLibraryMail;

    public function __construct(
        private string $studentEmail,
        private string $studentName,
        private string $status,
        private string $changedByName,
        private string $changedByRole,
        private string $updatedAt,
        private string $accountRole = 'student',
    ) {
        $this->configureLibraryMailQueue();
    }

    public function envelope(): Envelope
    {
        $subject = strtolower($this->status) === 'active'
            ? 'Library Account Activated'
            : 'Library Account Deactivated';

        return new Envelope(
            to: [$this->studentEmail],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-status-updated',
            with: [
                'studentName' => $this->studentName,
                'status' => $this->status,
                'changedByName' => $this->changedByName,
                'changedByRole' => $this->changedByRole,
                'updatedAt' => $this->updatedAt,
                'accountRole' => $this->accountRole,
            ],
        );
    }

    protected function libraryMailFailureContext(): array
    {
        return [
            'student_email' => $this->studentEmail,
            'status' => $this->status,
            'account_role' => $this->accountRole,
        ];
    }
}
