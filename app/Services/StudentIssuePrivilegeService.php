<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use Carbon\Carbon;

class StudentIssuePrivilegeService
{
    public function getPrivileges(Student $student, ?Carbon $issueDate = null): array
    {
        $student->loadMissing(['user', 'department', 'privileges']);

        $issueDate = $issueDate?->copy()->startOfDay() ?? today();
        $fineSetting = FineSetting::resolveActive();
        $privileges = $student->privileges;

        $maxBooks = (int) ($privileges?->max_books ?: $fineSetting->max_books_per_student);
        $durationDays = (int) ($privileges?->issue_duration_days ?: $fineSetting->issue_duration_days);
        $fineRate = (float) ($privileges?->per_day_fine ?: $fineSetting->per_day_fine);
        $alreadyIssued = $this->activeIssuedCount($student);
        $canIssue = max($maxBooks - $alreadyIssued, 0);
        $pendingFineAmount = (float) Fine::query()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->sum('amount');

        $reason = null;

        if ($student->user?->status !== 'active') {
            $reason = 'Student account is inactive.';
        } elseif ($privileges && ! $privileges->borrowing_allowed) {
            $reason = 'Student borrowing privileges are suspended.';
        } elseif ($canIssue <= 0) {
            $reason = 'Book issue limit reached.';
        }

        return [
            'allowed' => $reason === null,
            'reason' => $reason,
            'max_books' => $maxBooks,
            'already_issued' => $alreadyIssued,
            'can_issue' => $canIssue,
            'duration_days' => $durationDays,
            'fine_rate' => $fineRate,
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $issueDate->copy()->addDays($durationDays)->toDateString(),
            'has_pending_fines' => $pendingFineAmount > 0,
            'pending_fine_amount' => $pendingFineAmount,
            'account_status' => $student->user?->status,
            'policy_source' => $privileges ? 'student_privileges' : 'fine_settings',
            'policy_name' => $privileges ? 'Custom student privilege' : 'Default library policy',
        ];
    }

    public function canIssue(Student $student, int $requestedCount = 0): array
    {
        $privileges = $this->getPrivileges($student);

        if (! $privileges['allowed']) {
            return [
                'allowed' => false,
                'message' => $privileges['reason'],
                'privileges' => $privileges,
            ];
        }

        if ($requestedCount > $privileges['can_issue']) {
            return [
                'allowed' => false,
                'message' => 'Student can only issue ' . $privileges['can_issue'] . ' more book' . ($privileges['can_issue'] === 1 ? '.' : 's.'),
                'privileges' => $privileges,
            ];
        }

        return [
            'allowed' => true,
            'message' => null,
            'privileges' => $privileges,
        ];
    }

    public function activeIssuedCount(Student $student): int
    {
        return (int) IssuedBook::query()
            ->where('student_id', $student->id)
            ->whereNull('return_date')
            ->count();
    }
}
