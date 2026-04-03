<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Facades\Log;
use Throwable;

trait QueuesLibraryNotification
{
    public int $tries = 3;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    protected function configureLibraryNotificationQueue(): void
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
        Log::error('Queued notification email failed', array_merge([
            'notification' => static::class,
            'error' => $exception->getMessage(),
        ], $this->libraryNotificationFailureContext()));
    }

    protected function libraryNotificationFailureContext(): array
    {
        return [];
    }
}
