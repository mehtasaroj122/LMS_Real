<?php

namespace App\Services\StudentManagement;

use App\Helpers\ActivityLogger;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentManagementActionService
{
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
