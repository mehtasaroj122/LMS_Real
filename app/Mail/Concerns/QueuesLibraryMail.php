<?php

namespace App\Mail\Concerns;

use Illuminate\Support\Facades\Log;
use Throwable;

trait QueuesLibraryMail
{
    public int $tries = 3;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    protected function configureLibraryMailQueue(): void
    {
        $this->onQueue(config('mail.queue', 'emails'));
        $this->delay(now()->addSeconds((int) config('mail.send_delay_seconds', 3)));
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Queued email failed', array_merge([
            'mailable' => static::class,
            'error' => $exception->getMessage(),
        ], $this->libraryMailFailureContext()));
    }

    protected function libraryMailFailureContext(): array
    {
        return [];
    }
}
