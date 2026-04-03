<?php

namespace App\Mail;

use App\Mail\Concerns\QueuesLibraryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, QueuesLibraryMail;

    public function __construct(
        public string $userName,
        public string $loginUrl,
    ) {
        $this->configureLibraryMailQueue();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - Welcome to Your Library Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'userName' => $this->userName,
                'loginUrl' => $this->loginUrl,
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
            'user_name' => $this->userName,
        ];
    }
}
