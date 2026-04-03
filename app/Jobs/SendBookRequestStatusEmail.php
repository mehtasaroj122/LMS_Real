<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesQueuedEmail;
use App\Mail\BookRequestStatusSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookRequestStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesQueuedEmail;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $status, // 'approved' or 'rejected'
    ) {
        $this->configureEmailQueue();
    }

    public function handle(): void
    {
        $this->sendQueuedMail(
            $this->studentEmail,
            new BookRequestStatusSimple(
                $this->studentEmail,
                $this->studentName,
                $this->bookTitle,
                $this->status
            ),
            'book_request_status',
            [
                'book_title' => $this->bookTitle,
                'status' => $this->status,
            ]
        );
    }
}
