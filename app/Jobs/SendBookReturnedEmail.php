<?php

namespace App\Jobs;

use App\Mail\BookReturnedSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookReturnedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $condition,
        public float $fineAmount,
    ) {
        $this->delay(now()->addSeconds(3));
    }

    public function handle(): void
    {
        Mail::to($this->studentEmail)->send(new BookReturnedSimple(
            $this->studentEmail,
            $this->studentName,
            $this->bookTitle,
            $this->condition,
            $this->fineAmount
        ));
    }
}
