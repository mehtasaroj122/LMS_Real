<?php

namespace App\Mail;

use App\Models\Fine;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FineNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientEmail,
        public Fine $fine,
        public string $type = 'created', // created, paid, waived
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'paid' => 'Fine Payment Confirmed',
            'waived' => 'Fine Waived',
            default => 'Fine Notification - ' . $this->fine->amount,
        };

        return new Envelope(
            to: [$this->recipientEmail],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $view = match($this->type) {
            'paid' => 'emails.fine-paid',
            'waived' => 'emails.fine-waived',
            default => 'emails.fine-created',
        };

        return new Content(
            view: $view,
            with: [
                'fine' => $this->fine,
                'student' => $this->fine->student,
                'type' => $this->type,
            ],
        );
    }
}
