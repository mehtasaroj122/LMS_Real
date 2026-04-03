<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesQueuedEmail;
use App\Mail\BookReturnedSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookReturnedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesQueuedEmail;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $condition,
        public float $fineAmount,
    ) {
        $this->configureEmailQueue();
    }

    public function handle(): void
    {
        $this->sendQueuedMail(
            $this->studentEmail,
            new BookReturnedSimple(
                $this->studentEmail,
                $this->studentName,
                $this->bookTitle,
                $this->condition,
                $this->fineAmount
            ),
            'book_returned',
            [
                'book_title' => $this->bookTitle,
                'condition' => $this->condition,
                'fine_amount' => $this->fineAmount,
            ]
        );
    }
}
