<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, QueuesLibraryMail;

    public $userName;
    public $userEmail;
    public $tempPassword;
    public $appName;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $userEmail, $tempPassword)
    {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->tempPassword = $tempPassword;
        $this->appName = config('app.name');
        $this->loginUrl = route('login');
        $this->configureLibraryMailQueue();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->appName . ' - Password Reset by Administrator',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
                'tempPassword' => $this->tempPassword,
                'appName' => $this->appName,
                'loginUrl' => $this->loginUrl,
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
            'user_email' => $this->userEmail,
        ];
    }
}
