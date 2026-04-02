<?php

namespace App\Services\FineManagement;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Concerns\InteractsWithFineRecords;
use App\Models\Fine;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Throwable;

class FineManagementActionService
{
    use InteractsWithFineRecords;

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
        $reason = $status === 'waived' ? trim((string) ($fine->remarks ?? '')) : null;

        // MAIL SYSTEM DISABLED - To re-enable uncomment the queue dispatch and configure MAIL_* in .env
        // if ($status === 'paid') {
        //     SendFineEmail::dispatch($studentEmail, $studentName, (float) $fine->amount, 'paid', null);
        // } elseif ($status === 'waived') {
        //     SendFineEmail::dispatch($studentEmail, $studentName, (float) $fine->amount, 'waived', $reason ?: 'Fine waived');
        // } else {
        //     SendFineEmail::dispatch($studentEmail, $studentName, (float) $fine->amount, 'pending', null);
        // }
        \Log::info('Fine email would have been sent to: ' . $studentEmail . ' (Mail disabled)', [
            'fine_id' => $fine->id,
            'student_id' => $fine->student?->id,
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
        ]);

        try {
            if ($fine->student) {
                ActivityLogger::logStudentActivity(
                    $fine->student,
                    'fine_paid',
                    "Fine of ₹{$fine->amount} marked as paid",
                    'fine',
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
            $student = $fine->student;

            if ($student && $student->user) {
                Notification::notify(
                    user: $student->user,
                    type: 'payment.confirmed',
                    title: 'Fine Payment Received',
                    message: "Your fine payment of ₹{$fine->amount} has been received and marked as paid.",
                    data: [
                        'fine_id' => $fine->id,
                        'amount' => $fine->amount,
                        'student_id' => $student->id,
                    ],
                    relatedModel: 'Fine',
                    relatedId: $fine->id
                );
            }
        }

        if ($options['log_email'] && $fine->student?->user?->email) {
            \Log::info('Fine email would have been sent to: ' . $fine->student->user->email);
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
            'remarks' => $normalizedReason,
        ]);

        try {
            if ($fine->student) {
                ActivityLogger::logStudentActivity(
                    $fine->student,
                    'fine_waived',
                    "Fine of ₹{$fine->amount} waived. Reason: {$normalizedReason}",
                    'fine',
                    $this->buildFineHistoryMetadata($fine, [
                        'action_type' => 'waived',
                        'amount' => (float) $fine->amount,
                        'new_amount' => (float) $fine->amount,
                        'remarks' => $normalizedReason,
                    ])
                );
            }
        } catch (Throwable $logError) {
            \Log::warning('Failed to log activity: ' . $logError->getMessage());
        }

        if ($options['notify_student']) {
            $student = $fine->student;

            if ($student && $student->user) {
                Notification::notify(
                    user: $student->user,
                    type: 'fine.reminder',
                    title: 'Fine Waived',
                    message: "Your fine of ₹{$fine->amount} has been waived. Reason: {$normalizedReason}",
                    data: [
                        'fine_id' => $fine->id,
                        'amount' => $fine->amount,
                        'reason' => $normalizedReason,
                    ],
                    relatedModel: 'Fine',
                    relatedId: $fine->id
                );
            }
        }

        if ($options['log_email'] && $fine->student?->user?->email) {
            \Log::info('Fine email would have been sent to: ' . $fine->student->user->email);
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
