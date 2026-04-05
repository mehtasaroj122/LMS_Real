<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationInvitationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, QueuesLibraryMail;

    public function __construct(
        public string $userName,
        public string $roleLabel,
        public string $userEmail,
        public string $registerUrl,
        public string $identifierLabel,
        public string $identifierValue,
        public ?string $phone = null,
    ) {
        $this->configureLibraryMailQueue();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - Complete Your Registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-invitation',
            with: [
                'userName' => $this->userName,
                'roleLabel' => $this->roleLabel,
                'userEmail' => $this->userEmail,
                'registerUrl' => $this->registerUrl,
                'identifierLabel' => $this->identifierLabel,
                'identifierValue' => $this->identifierValue,
                'phone' => $this->phone,
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
            'user_email' => $this->userEmail,
            'role' => $this->roleLabel,
        ];
    }
}
