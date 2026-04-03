<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StudentPrivilegeUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, QueuesLibraryMail;

    public function __construct(
        private string $studentEmail,
        private string $studentName,
        private array $settings,
        private string $changedByName,
        private string $changedByRole,
        private string $updatedAt,
        private bool $resetToDefaults = false,
        private ?string $changeSummary = null,
    ) {
        $this->configureLibraryMailQueue();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->studentEmail],
            subject: $this->resetToDefaults
                ? 'Library Privileges Reset'
                : 'Library Privileges Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-privileges-updated',
            with: [
                'studentName' => $this->studentName,
                'settings' => $this->settings,
                'changedByName' => $this->changedByName,
                'changedByRole' => $this->changedByRole,
                'updatedAt' => $this->updatedAt,
                'resetToDefaults' => $this->resetToDefaults,
                'changeSummary' => $this->changeSummary,
            ],
        );
    }

    protected function libraryMailFailureContext(): array
    {
        return [
            'student_email' => $this->studentEmail,
            'reset_to_defaults' => $this->resetToDefaults,
        ];
    }
}
