<?php

namespace App\Jobs;

use App\Mail\FineSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFineEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public float $fineAmount,
        public string $type, // 'paid' or 'waived' or 'pending'
        public ?string $reason = null,
    ) {
        $this->delay(now()->addSeconds(3));
    }

    public function handle(): void
    {
        Mail::to($this->studentEmail)->send(new FineSimple(
            $this->studentEmail,
            $this->studentName,
            $this->fineAmount,
            $this->type,
            $this->reason
        ));
    }
}
