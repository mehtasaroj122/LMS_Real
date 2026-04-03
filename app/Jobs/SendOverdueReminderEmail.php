<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesQueuedEmail;
use App\Mail\OverdueBookReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOverdueReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesQueuedEmail;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $dueDate,
        public int $daysOverdue,
        public float $fineAmount = 0,
    ) {
        $this->configureEmailQueue();
    }

    public function handle(): void
    {
        $this->sendQueuedMail(
            $this->studentEmail,
            new OverdueBookReminderMail(
                $this->studentName,
                $this->bookTitle,
                $this->dueDate,
                $this->daysOverdue,
                $this->fineAmount
            ),
            'book_overdue',
            [
                'book_title' => $this->bookTitle,
                'days_overdue' => $this->daysOverdue,
                'fine_amount' => $this->fineAmount,
            ]
        );
    }
}
