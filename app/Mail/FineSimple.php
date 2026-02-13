<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class FineSimple extends Mailable
{
    public function __construct(
        private string $studentEmail,
        private string $studentName,
        private float $fineAmount,
        private string $type,
        private ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'paid' => 'Fine Payment Received',
            'waived' => 'Fine Waived',
            'pending' => 'Fine Payment Reminder',
            default => 'Fine Notification'
        };
        return new Envelope(
            to: [$this->studentEmail],
            subject: $subject . ' - Library Management System',
        );
    }

    public function content(): Content
    {
        $viewName = match($this->type) {
            'paid' => 'emails.fine-paid',
            'waived' => 'emails.fine-waived',
            'pending' => 'emails.fine-pending',
            default => 'emails.fine-paid'
        };
        return new Content(
            view: $viewName,
            with: [
                'studentName' => $this->studentName,
                'fineAmount' => $this->fineAmount,
                'reason' => $this->type === 'waived' ? ($this->reason ?? 'Fine waived by admin') : $this->reason,
            ],
        );
    }
}
