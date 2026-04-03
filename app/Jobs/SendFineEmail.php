<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesQueuedEmail;
use App\Mail\FineSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFineEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesQueuedEmail;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public float $fineAmount,
        public string $type, // 'paid' or 'waived' or 'pending'
        public ?string $reason = null,
    ) {
        $this->configureEmailQueue();
    }

    public function handle(): void
    {
        $this->sendQueuedMail(
            $this->studentEmail,
            new FineSimple(
                $this->studentEmail,
                $this->studentName,
                $this->fineAmount,
                $this->type,
                $this->reason
            ),
            'fine_status',
            [
                'amount' => $this->fineAmount,
                'status' => $this->type,
            ]
        );
    }
}
