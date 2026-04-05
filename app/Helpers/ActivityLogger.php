<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function userProfileAuditFields(): array
    {
        return ['name', 'email', 'phone', 'gender', 'address', 'date_of_birth', 'profile_photo'];
    }

    private static function resolveStudentName(Student $student): string
    {
        $student->loadMissing('user');

        return trim((string) ($student->user?->name ?: $student->roll_no ?: 'Unknown Student'));
    }

    private static function resolveActorName(): string
    {
        return trim((string) (Auth::user()?->name ?: 'System'));
    }

    private static function resolveStudentReference(Student $student): string
    {
        $reference = trim((string) ($student->roll_no ?: $student->student_id ?: ''));

        if ($reference !== '') {
            return $reference;
        }

        return 'Student ID: ' . $student->id;
    }

    private static function resolveFineStudentLabel(Student $student): string
    {
        return self::resolveStudentName($student) . ' (' . self::resolveStudentReference($student) . ')';
    }

    private static function formatBookActionDescription(
        string $action,
        string $bookName,
        string $studentName,
        string $actorName
    ): string {
        return match (strtolower($action)) {
            'issued' => "Book '{$bookName}' issued to {$studentName} by {$actorName}",
            'returned' => "Book '{$bookName}' returned by {$studentName} to {$actorName}",
            default => "Book '{$bookName}' updated for {$studentName} by {$actorName}",
        };
    }

    private static function formatBookRequestDescription(
        string $action,
        string $bookName,
        string $studentName,
        string $actorName
    ): string {
        return match (strtolower($action)) {
            'approved', 'accepted' => "Book '{$bookName}' request accepted for {$studentName} by {$actorName}",
            'rejected' => "Book '{$bookName}' request rejected for {$studentName} by {$actorName}",
            'issued' => "Book '{$bookName}' request marked as issued for {$studentName} by {$actorName}",
            'returned' => "Book '{$bookName}' request marked as returned for {$studentName} by {$actorName}",
            'cancelled' => "Book '{$bookName}' request cancelled for {$studentName} by {$actorName}",
            'pending', 'requested' => "Book '{$bookName}' requested for {$studentName} by {$actorName}",
            default => "Book '{$bookName}' request updated for {$studentName} by {$actorName}",
        };
    }

    private static function formatFineActionDescription(
        string $action,
        float $amount,
        string $bookName,
        string $studentLabel,
        ?string $isbn = null,
        array $details = []
    ): string {
        $amountLabel = self::formatCurrencyAmount($amount);
        $bookInfo = self::formatFineBookContext($bookName, $isbn);
        $bookTarget = self::formatFineBookTarget($bookName, $isbn);
        $reason = trim((string) ($details['reason'] ?? $details['remarks'] ?? ''));
        $paymentMethod = trim((string) ($details['payment_method'] ?? ''));
        $oldAmount = array_key_exists('old_amount', $details) ? (float) $details['old_amount'] : null;
        $newAmount = array_key_exists('new_amount', $details) ? (float) $details['new_amount'] : null;

        return match (strtolower($action)) {
            'applied' => "Fine of ₹{$amountLabel} applied{$bookTarget} to {$studentLabel}",
            'payment' => "Fine payment of ₹{$amountLabel}{$bookInfo} processed for {$studentLabel}",
            'paid' => 'Fine of ₹' . $amountLabel . $bookInfo . ' marked as paid for ' . $studentLabel
                . ($paymentMethod !== '' ? " via {$paymentMethod}" : ''),
            'waived' => 'Fine of ₹' . $amountLabel . $bookInfo . ' waived for ' . $studentLabel
                . ($reason !== '' ? ". Reason: {$reason}" : ''),
            'adjusted' => 'Fine' . $bookInfo . ' adjusted for ' . $studentLabel
                . ($oldAmount !== null && $newAmount !== null
                    ? ' from ₹' . self::formatCurrencyAmount($oldAmount) . ' to ₹' . self::formatCurrencyAmount($newAmount)
                    : ''),
            default => "Fine of ₹{$amountLabel}{$bookInfo} updated for {$studentLabel}",
        };
    }

    private static function formatCurrencyAmount(float $amount): string
    {
        return abs($amount - round($amount)) < 0.00001
            ? number_format($amount, 0, '.', '')
            : number_format($amount, 2, '.', '');
    }

    private static function formatFineBookContext(string $bookName = '', ?string $isbn = null): string
    {
        $bookName = trim($bookName);
        $isbn = trim((string) ($isbn ?? ''));

        if ($bookName !== '' && $isbn !== '') {
            return " for '{$bookName}' (ISBN: {$isbn})";
        }

        if ($bookName !== '') {
            return " for '{$bookName}'";
        }

        if ($isbn !== '') {
            return " for book (ISBN: {$isbn})";
        }

        return '';
    }

    private static function formatFineBookTarget(string $bookName = '', ?string $isbn = null): string
    {
        $context = self::formatFineBookContext($bookName, $isbn);

        return $context !== '' ? str_replace(' for ', ' for ', $context) : '';
    }

    private static function buildFineAuditMetadata(
        Student $student,
        string $bookName,
        ?string $isbn,
        array $metadata = []
    ): array {
        return [
            'amount' => $metadata['amount'] ?? null,
            'book_name' => $bookName,
            'isbn' => $isbn,
            'student_label' => self::resolveFineStudentLabel($student),
            ...$metadata,
        ];
    }

    private static function formatUserProfileFieldLabel(string $field): string
    {
        return match ($field) {
            'date_of_birth' => 'date of birth',
            'gender' => 'gender',
            'profile_photo' => 'profile picture',
            default => str_replace('_', ' ', $field),
        };
    }

    private static function normalizeUserProfileAuditValue(string $field, mixed $value): ?string
    {
        if ($field === 'profile_photo') {
            return filled($value) ? 'present' : null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        if ($field === 'date_of_birth' && filled($value)) {
            try {
                return Carbon::parse((string) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return trim((string) $value);
            }
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private static function formatUserProfileChangeMessage(string $field, array $change): string
    {
        $label = self::formatUserProfileFieldLabel($field);

        if ($field === 'profile_photo') {
            return match ($change['change_type'] ?? 'updated') {
                'added' => 'profile picture added',
                'removed' => 'profile picture removed',
                default => 'profile picture updated',
            };
        }

        $oldValue = $change['old'] ?? 'not set';
        $newValue = $change['new'] ?? 'not set';

        return "{$label}: {$oldValue} -> {$newValue}";
    }

    public static function buildUserProfileChangeSet(array $original, array $updated): array
    {
        $changes = [];
        $messages = [];

        foreach (self::userProfileAuditFields() as $field) {
            $oldValue = self::normalizeUserProfileAuditValue($field, $original[$field] ?? null);
            $newValue = self::normalizeUserProfileAuditValue($field, $updated[$field] ?? null);

            if ($oldValue === $newValue) {
                continue;
            }

            if ($field === 'profile_photo') {
                $changeType = $oldValue === null
                    ? 'added'
                    : ($newValue === null ? 'removed' : 'updated');

                $changes[$field] = [
                    'change_type' => $changeType,
                ];
            } else {
                $changes[$field] = [
                    'old' => $oldValue ?? 'not set',
                    'new' => $newValue ?? 'not set',
                ];
            }

            $messages[] = self::formatUserProfileChangeMessage($field, $changes[$field]);
        }

        return [
            'changes' => $changes,
            'changed_fields' => array_keys($changes),
            'messages' => $messages,
            'summary' => implode(', ', $messages),
        ];
    }

    public static function logUserProfileChanges(User $user, array $changeSet, array $metadata = [])
    {
        if (empty($changeSet['changes'])) {
            return null;
        }

        return self::logActivity(
            'profile_updated',
            'Profile updated: ' . ($changeSet['summary'] ?: 'changes recorded'),
            'user',
            'user',
            $user->id,
            [
                'changes' => $changeSet['changes'],
                'changed_fields' => $changeSet['changed_fields'],
                ...$metadata,
            ]
        );
    }

    /**
     * Get device type from user agent
     */
    private static function getDeviceType(): string
    {
        $ua = Request::header('User-Agent');
        
        if (preg_match('/mobile|android|iphone|ipod/i', $ua)) {
            return 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $ua)) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Get browser name from user agent
     */
    private static function getBrowserName(): string
    {
        $ua = Request::header('User-Agent');
        
        if (preg_match('/firefox/i', $ua)) {
            return 'Firefox';
        } elseif (preg_match('/chrome/i', $ua)) {
            return 'Chrome';
        } elseif (preg_match('/safari/i', $ua)) {
            return 'Safari';
        } elseif (preg_match('/edge/i', $ua)) {
            return 'Edge';
        }
        return 'Unknown';
    }

    /**
     * Get user details for logging
     */
    private static function getUserDetails(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [
                'user_id' => null,
                'user_name' => 'System',
                'user_role' => 'system',
                'user_email' => null,
            ];
        }

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role ?? 'user',
            'user_email' => $user->email,
        ];
    }

    /**
     * Enrich log metadata with request-bound audit details when available.
     */
    private static function enrichMetadata(array $metadata = []): array
    {
        try {
            if (!array_key_exists('session_id', $metadata) && app()->bound('session')) {
                $sessionId = session()->getId();

                if (!empty($sessionId)) {
                    $metadata['session_id'] = $sessionId;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Unable to capture session id for activity log: ' . $e->getMessage());
        }

        return $metadata;
    }

    /**
     * Enforce activity log retention limit (max 500 records)
     * Delete oldest records if limit exceeded
     */
    private static function enforceRetentionLimit(): void
    {
        try {
            $maxRecords = 500;
            $currentCount = ActivityLog::count();
            
            if ($currentCount > $maxRecords) {
                $recordsToDelete = $currentCount - $maxRecords;
                
                // Delete the oldest records
                ActivityLog::orderBy('created_at', 'asc')
                    ->limit($recordsToDelete)
                    ->delete();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to enforce retention limit: ' . $e->getMessage());
        }
    }

    /**
     * Log an activity for a student (Enhanced)
     * 
     * @param Student $student
     * @param string $action
     * @param string $description
     * @param string $category
     * @param array $metadata
     * @return ActivityLog
     */
    public static function logStudentActivity(Student $student, string $action, string $description, string $category = 'general', array $metadata = [])
    {
        try {
            $userDetails = self::getUserDetails();
            $metadata = self::enrichMetadata($metadata);
            
            $activityLog = ActivityLog::create([
                'user_id' => $userDetails['user_id'],
                'user_name' => $userDetails['user_name'],
                'user_role' => $userDetails['user_role'],
                'user_email' => $userDetails['user_email'],
                'action' => $action,
                'action_category' => $category,
                'status' => 'completed',
                'model_type' => Student::class,
                'model_id' => $student->id,
                'description' => $description,
                'ip_address' => Request::ip(),
                'browser' => self::getBrowserName(),
                'device_type' => self::getDeviceType(),
                'metadata' => !empty($metadata) ? $metadata : null,
                'resource_type' => 'student',
                'resource_id' => $student->id,
                'affected_user_id' => $student->user_id ?? null,
            ]);

            // Enforce retention limit (max 500 records)
            self::enforceRetentionLimit();
            
            return $activityLog;
        } catch (\Exception $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a book issue activity
     * 
     * @param Student $student
     * @param string $bookName
     * @param array $bookDetails
     * @return ActivityLog
     */
    public static function logBookIssued(Student $student, string $bookName, array $bookDetails = [])
    {
        $studentName = self::resolveStudentName($student);
        $actorName = self::resolveActorName();

        return self::logStudentActivity(
            $student,
            'book_issued',
            self::formatBookActionDescription('issued', $bookName, $studentName, $actorName),
            'book',
            [
                'book_name' => $bookName,
                'student_name' => $studentName,
                'actor_name' => $actorName,
                ...$bookDetails,
            ]
        );
    }

    /**
     * Log a book return activity
     * 
     * @param Student $student
     * @param string $bookName
     * @param array $bookDetails
     * @return ActivityLog
     */
    public static function logBookReturned(Student $student, string $bookName, array $bookDetails = [])
    {
        $studentName = self::resolveStudentName($student);
        $actorName = self::resolveActorName();

        return self::logStudentActivity(
            $student,
            'book_returned',
            self::formatBookActionDescription('returned', $bookName, $studentName, $actorName),
            'book',
            [
                'book_name' => $bookName,
                'student_name' => $studentName,
                'actor_name' => $actorName,
                ...$bookDetails,
            ]
        );
    }

    /**
     * Log a fine payment activity
     * 
     * @param Student $student
     * @param float $amount
     * @param string $bookName
     * @param array $metadata
     * @return ActivityLog
     */
    public static function logFinePayment(Student $student, float $amount, string $bookName = '', array $metadata = [])
    {
        $studentLabel = self::resolveFineStudentLabel($student);
        $isbn = isset($metadata['isbn']) ? (string) $metadata['isbn'] : null;

        return self::logStudentActivity(
            $student,
            'fine_payment',
            self::formatFineActionDescription('payment', $amount, $bookName, $studentLabel, $isbn, $metadata),
            'fine',
            self::buildFineAuditMetadata($student, $bookName, $isbn, [
                'amount' => $amount,
                ...$metadata,
            ])
        );
    }

    /**
     * Log a fine applied activity
     *
     * @param Student $student
     * @param float $amount
     * @param string $bookName
     * @param array $metadata
     * @return ActivityLog
     */
    public static function logFineApplied(Student $student, float $amount, string $bookName = '', array $metadata = [])
    {
        $studentLabel = self::resolveFineStudentLabel($student);
        $isbn = isset($metadata['isbn']) ? (string) $metadata['isbn'] : null;

        return self::logStudentActivity(
            $student,
            'fine_applied',
            self::formatFineActionDescription('applied', $amount, $bookName, $studentLabel, $isbn, $metadata),
            'fine',
            self::buildFineAuditMetadata($student, $bookName, $isbn, [
                'amount' => $amount,
                ...$metadata,
            ])
        );
    }

    public static function logFinePaid(Student $student, float $amount, string $bookName = '', array $metadata = [])
    {
        $studentLabel = self::resolveFineStudentLabel($student);
        $isbn = isset($metadata['isbn']) ? (string) $metadata['isbn'] : null;

        return self::logStudentActivity(
            $student,
            'fine_paid',
            self::formatFineActionDescription('paid', $amount, $bookName, $studentLabel, $isbn, $metadata),
            'fine',
            self::buildFineAuditMetadata($student, $bookName, $isbn, [
                'amount' => $amount,
                ...$metadata,
            ])
        );
    }

    public static function logFineWaived(Student $student, float $amount, string $bookName = '', array $metadata = [])
    {
        $studentLabel = self::resolveFineStudentLabel($student);
        $isbn = isset($metadata['isbn']) ? (string) $metadata['isbn'] : null;

        return self::logStudentActivity(
            $student,
            'fine_waived',
            self::formatFineActionDescription('waived', $amount, $bookName, $studentLabel, $isbn, $metadata),
            'fine',
            self::buildFineAuditMetadata($student, $bookName, $isbn, [
                'amount' => $amount,
                ...$metadata,
            ])
        );
    }

    public static function logFineAdjusted(
        Student $student,
        float $oldAmount,
        float $newAmount,
        string $bookName = '',
        array $metadata = []
    ) {
        $studentLabel = self::resolveFineStudentLabel($student);
        $isbn = isset($metadata['isbn']) ? (string) $metadata['isbn'] : null;

        return self::logStudentActivity(
            $student,
            'fine_adjusted',
            self::formatFineActionDescription('adjusted', $newAmount, $bookName, $studentLabel, $isbn, [
                ...$metadata,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
            ]),
            'fine',
            self::buildFineAuditMetadata($student, $bookName, $isbn, [
                'amount' => $newAmount,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                ...$metadata,
            ])
        );
    }

    /**
     * Log a book request action
     * 
     * @param Student $student
     * @param string $action
     * @param string $bookName
     * @param array $metadata
     * @return ActivityLog
     */
    public static function logBookRequest(Student $student, string $action, string $bookName, array $metadata = [])
    {
        $studentName = self::resolveStudentName($student);
        $actorName = self::resolveActorName();

        return self::logStudentActivity(
            $student,
            "book_request_{$action}",
            self::formatBookRequestDescription($action, $bookName, $studentName, $actorName),
            'book_request',
            [
                'book_name' => $bookName,
                'action' => $action,
                'student_name' => $studentName,
                'actor_name' => $actorName,
                ...$metadata,
            ]
        );
    }

    /**
     * Log status change activity
     * 
     * @param Student $student
     * @param string $oldStatus
     * @param string $newStatus
     * @return ActivityLog
     */
    public static function logStatusChange(Student $student, string $oldStatus, string $newStatus)
    {
        return self::logStudentActivity(
            $student,
            'status_changed',
            "Account status changed from {$oldStatus} to {$newStatus} by " . (Auth::user()->name ?? 'System'),
            'user',
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    /**
     * Log role change activity
     * 
     * @param Student $student
     * @param string $oldRole
     * @param string $newRole
     * @return ActivityLog
     */
    public static function logRoleChange(Student $student, string $oldRole, string $newRole)
    {
        return self::logStudentActivity(
            $student,
            'role_changed',
            "User role changed from {$oldRole} to {$newRole} by " . (Auth::user()->name ?? 'System'),
            'user',
            ['old_role' => $oldRole, 'new_role' => $newRole]
        );
    }

    /**
     * Log profile update activity
     * 
     * @param Student $student
     * @param array $changes
     * @return ActivityLog
     */
    public static function logProfileUpdate(Student $student, array $changes)
    {
        $changedFields = array_keys($changes);
        $fieldsList = implode(', ', $changedFields);
        return self::logStudentActivity(
            $student,
            'profile_updated',
            "Student profile updated: {$fieldsList}",
            'user',
            ['changed_fields' => $changedFields, 'changes' => $changes]
        );
    }

    /**
     * Log account deletion activity
     * 
     * @param Student $student
     * @return ActivityLog
     */
    public static function logAccountDeleted(Student $student)
    {
        return self::logStudentActivity(
            $student,
            'account_deleted',
            "Student account deleted by " . (Auth::user()->name ?? 'System'),
            'user'
        );
    }

    /**
     * Log password reset activity
     * 
     * @param Student $student
     * @return ActivityLog
     */
    public static function logPasswordReset(Student $student)
    {
        return self::logStudentActivity(
            $student,
            'password_reset',
            "Password reset by " . (Auth::user()->name ?? 'System'),
            'auth'
        );
    }

    /**
     * Log a general activity (for non-student actions like book management, user management)
     * 
     * @param string $action
     * @param string $description
     * @param string $category
     * @param string $resourceType
     * @param string|int $resourceId
     * @param array $metadata
     * @return ActivityLog
     */
    public static function logActivity(
        string $action,
        string $description,
        string $category = 'general',
        string $resourceType = null,
        $resourceId = null,
        array $metadata = []
    ) {
        try {
            $userDetails = self::getUserDetails();
            $metadata = self::enrichMetadata($metadata);
            
            $activityLog = ActivityLog::create([
                'user_id' => $userDetails['user_id'],
                'user_name' => $userDetails['user_name'],
                'user_role' => $userDetails['user_role'],
                'user_email' => $userDetails['user_email'],
                'action' => $action,
                'action_category' => $category,
                'status' => 'completed',
                'description' => $description,
                'ip_address' => Request::ip(),
                'browser' => self::getBrowserName(),
                'device_type' => self::getDeviceType(),
                'metadata' => !empty($metadata) ? $metadata : null,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
            ]);

            // Enforce retention limit (max 500 records)
            self::enforceRetentionLimit();
            
            return $activityLog;
        } catch (\Exception $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }
}
