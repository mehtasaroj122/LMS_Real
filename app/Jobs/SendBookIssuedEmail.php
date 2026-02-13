<?php

namespace App\Jobs;

use App\Mail\BookIssuedSimple;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookIssuedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $studentEmail,
        public string $studentName,
        public string $bookTitle,
        public string $author,
        public string $issueDate,
        public string $dueDate,
    ) {
        $this->delay(now()->addSeconds(3));
    }

    public function handle(): void
    {
        Mail::to($this->studentEmail)->send(new BookIssuedSimple(
            $this->studentEmail,
            $this->studentName,
            $this->bookTitle,
            $this->author,
            $this->issueDate,
            $this->dueDate
        ));
    }
}
