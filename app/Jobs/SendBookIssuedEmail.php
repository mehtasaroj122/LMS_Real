<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesQueuedEmail;
use App\Mail\BookIssuedSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookIssuedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesQueuedEmail;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $author,
        public string $issueDate,
        public string $dueDate,
    ) {
        $this->configureEmailQueue();
    }

    public function handle(): void
    {
        $this->sendQueuedMail(
            $this->studentEmail,
            new BookIssuedSimple(
                $this->studentEmail,
                $this->studentName,
                $this->bookTitle,
                $this->author,
                $this->issueDate,
                $this->dueDate
            ),
            'book_issued',
            [
                'book_title' => $this->bookTitle,
                'due_date' => $this->dueDate,
            ]
        );
    }
}
