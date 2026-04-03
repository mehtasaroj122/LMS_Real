<?php

namespace App\Services\StudentManagement;

use App\Helpers\ActivityLogger;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentManagementActionService
{
    public function __construct(
        private StudentNotificationEmailService $studentNotificationEmailService,
    ) {}

    public function create(array $validated): Student
    {
        $student = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'role' => 'student',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'roll_no' => $validated['roll_no'],
                'department_id' => $validated['department_id'],
                'batch' => $validated['batch'],
                'semester' => $validated['semester'],
                'address' => $validated['address'],
            ]);

            ActivityLogger::logProfileUpdate($student, ['created_by' => auth()->id()]);

            return $student;
        });

        return $student->load(['user', 'department']);
    }

    public function updateStatus(Student $student, string $status): Student
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new RuntimeException('Unsupported student status update.');
        }

        $student->loadMissing('user');

        if (!$student->user) {
            throw new RuntimeException('User not found for this student.');
        }

        $oldStatus = strtolower((string) $student->user->status);

        if ($oldStatus === $status) {
            return $student->load(['user', 'department']);
        }

        $student->user->update(['status' => $status]);

        // Notify student about status change
        $statusMessage = $status === 'active' ? 'activated' : 'deactivated';
        Notification::notify(
            user: $student->user,
            type: 'account.status_changed',
            title: 'Account Status Changed',
            message: "Your account has been {$statusMessage} by staff",
            data: ['status' => $status, 'changed_by' => auth()->user()?->name],
            relatedModel: 'Student',
            relatedId: $student->id
        );

        $this->studentNotificationEmailService->sendStatusChangedEmail(
            $student,
            $status,
            auth()->user()?->name,
            auth()->user()?->role,
        );
        
        // Notify admin about student status change by staff
        $staffName = auth()->user()?->name ?? 'Staff Member';
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Notification::notify(
                user: $admin,
                type: 'staff.student_status_changed',
                title: 'Student Status Changed by Staff',
                message: "{$staffName} {$statusMessage} student {$student->user->name} (Roll: {$student->roll_no})",
                data: [
                    'student_id' => $student->id,
                    'user_id' => $student->user_id,
                    'student_name' => $student->user->name,
                    'roll_no' => $student->roll_no,
                    'status' => $status,
                    'staff_name' => $staffName,
                ],
                relatedModel: 'Student',
                relatedId: $student->id
            );
        }
        
        ActivityLogger::logStatusChange($student, $oldStatus, $status);

        return $student->load(['user', 'department']);
    }

    public function duplicateStudentErrors(QueryException $exception, array $validated): array
    {
        $errorMessage = $exception->getMessage();
        $errors = [];

        if (stripos($errorMessage, 'users_email_unique') !== false || (stripos($errorMessage, 'Duplicate entry') !== false && stripos($errorMessage, (string) ($validated['email'] ?? '')) !== false)) {
            $errors['email'] = ['This email is already assigned to another user.'];
        }

        if (stripos($errorMessage, 'users_phone_unique') !== false || (stripos($errorMessage, 'Duplicate entry') !== false && stripos($errorMessage, (string) ($validated['phone'] ?? '')) !== false)) {
            $errors['phone'] = ['This phone number is already assigned to another user.'];
        }

        if (stripos($errorMessage, 'students_roll_no_unique') !== false || (stripos($errorMessage, 'Duplicate entry') !== false && stripos($errorMessage, (string) ($validated['roll_no'] ?? '')) !== false)) {
            $errors['roll_no'] = ['This student ID is already in use.'];
        }

        if (empty($errors)) {
            $errors['email'] = ['Unable to save the student with the provided details.'];
        }

        return $errors;
    }
}
