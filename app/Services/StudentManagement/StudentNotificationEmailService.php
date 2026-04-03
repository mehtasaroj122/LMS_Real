<?php

namespace App\Services\StudentManagement;

use App\Mail\StudentPrivilegeUpdatedMail;
use App\Mail\StudentStatusUpdatedMail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StudentNotificationEmailService
{
    public function sendStatusChangedEmail(
        Student $student,
        string $status,
        ?string $changedByName = null,
        ?string $changedByRole = null,
    ): void {
        $student->loadMissing('user');

        if (!$student->user) {
            return;
        }

        $this->sendUserStatusChangedEmail(
            $student->user,
            $status,
            $changedByName,
            $changedByRole,
        );
    }

    public function sendUserStatusChangedEmail(
        User $user,
        string $status,
        ?string $changedByName = null,
        ?string $changedByRole = null,
    ): void {
        $userEmail = trim((string) ($user->email ?? ''));

        if ($userEmail === '') {
            return;
        }

        $accountRole = strtolower((string) ($user->role ?? 'student'));

        try {
            Mail::to($userEmail)->queue(new StudentStatusUpdatedMail(
                $userEmail,
                $user->name ?? ucfirst($accountRole),
                strtolower($status),
                $this->resolveActorName($changedByName),
                $this->resolveActorRole($changedByRole),
                now()->format('Y-m-d H:i'),
                $accountRole,
            ));

            Log::info('Queued account status update email', [
                'user_id' => $user->id,
                'email' => $userEmail,
                'status' => strtolower($status),
                'role' => $accountRole,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Unable to queue account status update email: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'status' => strtolower($status),
                'role' => $accountRole,
            ]);
        }
    }

    public function sendPrivilegeSettingsUpdatedEmail(
        Student $student,
        array $effectiveSettings,
        ?string $changeSummary = null,
        bool $resetToDefaults = false,
        ?string $changedByName = null,
        ?string $changedByRole = null,
    ): void {
        $student->loadMissing('user');

        $studentEmail = trim((string) ($student->user?->email ?? ''));

        if ($studentEmail === '') {
            return;
        }

        try {
            Mail::to($studentEmail)->queue(new StudentPrivilegeUpdatedMail(
                $studentEmail,
                $student->user?->name ?? 'Student',
                $this->buildPrivilegeSettingsRows($effectiveSettings),
                $this->resolveActorName($changedByName),
                $this->resolveActorRole($changedByRole),
                now()->format('Y-m-d H:i'),
                $resetToDefaults,
                $this->normalizeChangeSummary($changeSummary, $resetToDefaults),
            ));

            Log::info('Queued student privilege update email', [
                'student_id' => $student->id,
                'email' => $studentEmail,
                'reset_to_defaults' => $resetToDefaults,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Unable to queue student privilege update email: ' . $exception->getMessage(), [
                'student_id' => $student->id,
                'reset_to_defaults' => $resetToDefaults,
            ]);
        }
    }

    protected function buildPrivilegeSettingsRows(array $effectiveSettings): array
    {
        return [
            ['label' => 'Maximum books', 'value' => (string) ($effectiveSettings['max_books'] ?? 'N/A')],
            ['label' => 'Issue duration', 'value' => $this->formatDays($effectiveSettings['issue_duration_days'] ?? null)],
            ['label' => 'Per-day fine', 'value' => $this->formatCurrency($effectiveSettings['per_day_fine'] ?? null)],
            ['label' => 'Grace period', 'value' => $this->formatDays($effectiveSettings['grace_period_days'] ?? null)],
            ['label' => 'Maximum fine', 'value' => $this->formatCurrency($effectiveSettings['max_fine_amount'] ?? null)],
            ['label' => 'Borrowing access', 'value' => (bool) ($effectiveSettings['borrowing_allowed'] ?? true) ? 'Allowed' : 'Restricted'],
        ];
    }

    protected function formatDays($value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return (string) $value . ' days';
    }

    protected function formatCurrency($value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return 'Rs. ' . number_format((float) $value, 2);
    }

    protected function resolveActorName(?string $changedByName): string
    {
        $name = trim((string) $changedByName);

        return $name !== '' ? $name : 'Library team';
    }

    protected function resolveActorRole(?string $changedByRole): string
    {
        return match (strtolower(trim((string) $changedByRole))) {
            'admin' => 'Administrator',
            'staff' => 'Library staff',
            default => 'Library team',
        };
    }

    protected function normalizeChangeSummary(?string $changeSummary, bool $resetToDefaults): ?string
    {
        $summary = trim((string) $changeSummary);

        if ($summary !== '') {
            return $summary;
        }

        if ($resetToDefaults) {
            return 'Your custom borrowing overrides were removed and your account now follows the default library policy.';
        }

        return null;
    }
}
