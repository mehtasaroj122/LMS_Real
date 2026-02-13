<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
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
                'metadata' => !empty($metadata) ? json_encode($metadata) : null,
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
        return self::logStudentActivity(
            $student,
            'book_issued',
            "Book '{$bookName}' issued to student by " . (Auth::user()->name ?? 'System'),
            'book',
            ['book_name' => $bookName, ...$bookDetails]
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
        return self::logStudentActivity(
            $student,
            'book_returned',
            "Book '{$bookName}' returned by student",
            'book',
            ['book_name' => $bookName, ...$bookDetails]
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
        $bookInfo = $bookName ? " for '{$bookName}'" : '';
        return self::logStudentActivity(
            $student,
            'fine_payment',
            "Fine payment of ₹{$amount}{$bookInfo} processed",
            'fine',
            ['amount' => $amount, 'book_name' => $bookName, ...$metadata]
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
        $actionText = ucfirst($action);
        return self::logStudentActivity(
            $student,
            "book_request_{$action}",
            "Book request for '{$bookName}' {$action}ed by " . (Auth::user()->name ?? 'System'),
            'book_request',
            ['book_name' => $bookName, 'action' => $action, ...$metadata]
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
                'metadata' => !empty($metadata) ? json_encode($metadata) : null,
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
