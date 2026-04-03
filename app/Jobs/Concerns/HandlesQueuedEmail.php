<?php

namespace App\Jobs\Concerns;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

trait HandlesQueuedEmail
{
    public int $tries = 3;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    protected function configureEmailQueue(): void
    {
        $this->onQueue(config('mail.queue', 'emails'));
        $this->delay(now()->addSeconds((int) config('mail.send_delay_seconds', 3)));
        $this->afterCommit();
    }

    protected function sendQueuedMail(
        string $recipient,
        Mailable $mailable,
        string $type,
        array $context = []
    ): void {
        try {
            Mail::to($recipient)->send($mailable);

            Log::info('Email sent successfully', array_merge([
                'type' => $type,
                'recipient' => $recipient,
            ], $context));
        } catch (Throwable $exception) {
            Log::error('Email send attempt failed', array_merge([
                'type' => $type,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ], $context));

            throw $exception;
        }
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }
}
