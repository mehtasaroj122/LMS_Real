<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, QueuesLibraryMail;

    public $url;
    public $email;
    public $name;

    /**
     * Create a new message instance.
     */
    public function __construct($url, $email, $name)
    {
        $this->url = $url;
        $this->email = $email;
        $this->name = $name;
        $this->configureLibraryMailQueue();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - Password Reset Link',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-email',
            with: [
                'url' => $this->url,
                'email' => $this->email,
                'name' => $this->name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    protected function libraryMailFailureContext(): array
    {
        return [
            'user_email' => $this->email,
        ];
    }
}
