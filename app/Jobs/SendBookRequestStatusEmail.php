<?php

namespace App\Jobs;

use App\Mail\BookRequestStatusSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookRequestStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $status, // 'approved' or 'rejected'
    ) {
        $this->delay(now()->addSeconds(3));
    }

    public function handle(): void
    {
        Mail::to($this->studentEmail)->send(new BookRequestStatusSimple(
            $this->studentEmail,
            $this->studentName,
            $this->bookTitle,
            $this->status
        ));
    }
}
