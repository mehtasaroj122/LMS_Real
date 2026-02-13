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
        /**
         * MAIL SYSTEM DISABLED
         * To re-enable: Uncomment the code below
         * Make sure MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD are set in .env
         */
        // Mail::to($this->studentEmail)->send(new FineSimple(
        //     $this->studentEmail,
        //     $this->studentName,
        //     $this->fineAmount,
        //     $this->type,
        //     $this->reason
        // ));
        
        // Log notification instead of sending email
        \Log::info('Email would have been sent to: ' . $this->studentEmail . ' (Mail disabled)');
    }
}
