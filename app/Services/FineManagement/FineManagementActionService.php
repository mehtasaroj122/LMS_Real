<?php

namespace App\Services\FineManagement;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Concerns\InteractsWithFineRecords;
use App\Jobs\SendFineEmail;
use App\Models\Fine;
use App\Services\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Throwable;

class FineManagementActionService
{
    use InteractsWithFineRecords;

    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function sendEmailNotification(Fine $fine): array
    {
        $fine->loadMissing(['student.user', 'student.privileges', 'issuedBook.book']);

        $studentEmail = trim((string) ($fine->student?->user?->email ?? ''));

        if ($studentEmail === '') {
            throw ValidationException::withMessages([
                'fine' => 'Student email not found',
            ]);
        }

        $studentName = trim((string) ($fine->student?->user?->name ?? 'Student'));
        $status = strtolower(trim((string) ($fine->status ?? 'pending'))) ?: 'pending';
        $reason = $status === 'waived'
            ? trim((string) ($fine->waive_reason ?? $fine->remarks ?? ''))
            : null;

        $mailType = match ($status) {
            'paid' => 'paid',
            'waived' => 'waived',
            default => 'pending',
        };

        SendFineEmail::dispatch(
            $studentEmail,
            $studentName,
            (float) $fine->amount,
            $mailType,
            $mailType === 'waived' ? ($reason ?: 'Fine waived') : null
        );

        \Log::info('Queued fine email', [
            'fine_id' => $fine->id,
            'student_id' => $fine->student?->id,
            'recipient' => $studentEmail,
            'status' => $status,
            'amount' => (float) $fine->amount,
        ]);

        return [
            'fine_id' => (int) $fine->id,
            'recipient' => $studentEmail,
            'student_name' => $studentName,
            'status' => $status,
            'amount' => (float) $fine->amount,
            'reason' => $reason !== '' ? $reason : null,
        ];
    }

    public function markAsPaid(Fine $fine, array $options = []): Fine
    {
        $options = $this->normalizeOptions($options);

        $fine->loadMissing(['student.user', 'student.privileges', 'issuedBook.book']);
        $this->ensureFineIsActionable($fine);

        $fine->update([
            'status' => 'paid',
            'paid_on' => now(),
            'paid_at' => now(),
            'paid_by' => auth()->id(),
        ]);

        try {
            if ($fine->student) {
                ActivityLogger::logFinePaid(
                    $fine->student,
                    (float) $fine->amount,
                    $fine->issuedBook?->book?->title ?? '',
                    $this->buildFineHistoryMetadata($fine, [
                        'action_type' => 'paid',
                        'amount' => (float) $fine->amount,
                        'new_amount' => (float) $fine->amount,
                        'payment_method' => $fine->payment_method ?? 'cash',
                    ])
                );
            }
        } catch (Throwable $logError) {
            \Log::warning('Failed to log activity: ' . $logError->getMessage());
        }

        if ($options['notify_student']) {
            $this->notifications->notifyFinePaid($fine);
        }

        if ($options['log_email'] && $fine->student?->user?->email) {
            try {
                $this->sendEmailNotification($fine);
            } catch (Throwable $exception) {
                \Log::warning('Unable to queue paid fine email', [
                    'fine_id' => $fine->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $fine->fresh(['student.user', 'student.privileges', 'issuedBook.book']);
    }

    public function waive(Fine $fine, string $reason, array $options = []): Fine
    {
        $options = $this->normalizeOptions($options);
        $normalizedReason = trim($reason);

        if ($normalizedReason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Please enter a reason for waiving the fine.',
            ]);
        }

        $fine->loadMissing(['student.user', 'student.privileges', 'issuedBook.book']);
        $this->ensureFineIsActionable($fine);

        $fine->update([
            'status' => 'waived',
            'waive_reason' => $normalizedReason,
            'waived_at' => now(),
            'waived_by' => auth()->id(),
            'remarks' => $normalizedReason,
        ]);

        try {
            if ($fine->student) {
                ActivityLogger::logFineWaived(
                    $fine->student,
                    (float) $fine->amount,
                    $fine->issuedBook?->book?->title ?? '',
                    $this->buildFineHistoryMetadata($fine, [
                        'action_type' => 'waived',
                        'amount' => (float) $fine->amount,
                        'new_amount' => (float) $fine->amount,
                        'remarks' => $normalizedReason,
                        'reason' => $normalizedReason,
                    ])
                );
            }
        } catch (Throwable $logError) {
            \Log::warning('Failed to log activity: ' . $logError->getMessage());
        }

        if ($options['notify_student']) {
            $this->notifications->notifyFineWaived($fine, $normalizedReason);
        }

        if ($options['log_email'] && $fine->student?->user?->email) {
            try {
                $this->sendEmailNotification($fine);
            } catch (Throwable $exception) {
                \Log::warning('Unable to queue waived fine email', [
                    'fine_id' => $fine->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $fine->fresh(['student.user', 'student.privileges', 'issuedBook.book']);
    }

    public function bulkUpdateStatus(
        array $fineIds,
        string $status,
        array $options = [],
        ?string $reason = null
    ): array {
        $normalizedIds = collect($fineIds)
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->unique()
            ->values();

        /** @var Collection<int, Fine> $fines */
        $fines = Fine::query()
            ->with(['student.user', 'student.privileges', 'issuedBook.book'])
            ->whereIn('id', $normalizedIds->all())
            ->get()
            ->keyBy('id');

        $updated = [];
        $skipped = [];
        $totalAmount = 0;

        foreach ($normalizedIds as $fineId) {
            $fine = $fines->get($fineId);

            if (!$fine) {
                $skipped[] = [
                    'id' => $fineId,
                    'reason' => 'missing',
                ];
                continue;
            }

            if (strtolower((string) $fine->status) !== 'pending') {
                $skipped[] = [
                    'id' => $fineId,
                    'reason' => 'not_pending',
                ];
                continue;
            }

            $updatedFine = $status === 'paid'
                ? $this->markAsPaid($fine, $options)
                : $this->waive($fine, (string) $reason, $options);

            $updated[] = $updatedFine;
            $totalAmount += (float) $updatedFine->amount;
        }

        return [
            'updated' => $updated,
            'updated_count' => count($updated),
            'skipped' => $skipped,
            'skipped_count' => count($skipped),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    public function bulkSendEmailNotifications(array $fineIds): array
    {
        $normalizedIds = collect($fineIds)
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->unique()
            ->values();

        /** @var Collection<int, Fine> $fines */
        $fines = Fine::query()
            ->with(['student.user', 'student.privileges', 'issuedBook.book'])
            ->whereIn('id', $normalizedIds->all())
            ->get()
            ->keyBy('id');

        $processed = [];
        $skipped = [];
        $recipients = collect();

        foreach ($normalizedIds as $fineId) {
            $fine = $fines->get($fineId);

            if (!$fine) {
                $skipped[] = [
                    'id' => $fineId,
                    'reason' => 'missing',
                ];
                continue;
            }

            try {
                $result = $this->sendEmailNotification($fine);
                $processed[] = $result;
                $recipients->push(strtolower((string) $result['recipient']));
            } catch (ValidationException $e) {
                $skipped[] = [
                    'id' => $fineId,
                    'reason' => 'missing_email',
                    'message' => collect($e->errors())->flatten()->first(),
                ];
            }
        }

        return [
            'processed' => $processed,
            'processed_count' => count($processed),
            'skipped' => $skipped,
            'skipped_count' => count($skipped),
            'recipient_count' => $recipients->filter()->unique()->count(),
        ];
    }

    protected function normalizeOptions(array $options): array
    {
        return [
            'notify_student' => (bool) ($options['notify_student'] ?? false),
            'log_email' => (bool) ($options['log_email'] ?? false),
        ];
    }
}
