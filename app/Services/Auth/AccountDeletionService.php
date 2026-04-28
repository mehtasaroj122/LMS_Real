<?php

namespace App\Services\Auth;

use App\Helpers\ActivityLogger;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountDeletionService
{
    public function eligibilityFor(User $user): array
    {
        $role = strtolower((string) $user->role);

        if ($role === 'admin') {
            return [
                'allowed' => false,
                'status' => 'blocked_admin',
                'title' => 'Administrator Account',
                'message' => 'You cannot delete your account because you are an administrator.',
                'detail' => 'Please contact another administrator for this action.',
                'button_enabled' => true,
                'button_label' => 'Delete Account',
                'button_tooltip' => 'This action cannot be undone',
                'issued_books_count' => 0,
                'pending_fines_count' => 0,
            ];
        }

        $student = $role === 'student'
            ? $user->loadMissing('student')->student
            : null;

        $issuedBooksCount = $student
            ? $student->issuedBooks()->whereNull('return_date')->count()
            : 0;

        $pendingFinesCount = $student
            ? Fine::query()
                ->where('student_id', $student->id)
                ->where('status', 'pending')
                ->count()
            : 0;

        $hasRestrictions = $role === 'student' && ($issuedBooksCount > 0 || $pendingFinesCount > 0);

        return [
            'allowed' => ! $hasRestrictions,
            'status' => $hasRestrictions ? 'blocked_student_constraints' : 'allowed',
            'title' => 'Danger Zone',
            'message' => $hasRestrictions
                ? 'Please return all issued books and clear pending fines before deleting your account.'
                : 'Deleting your account is permanent.',
            'detail' => $hasRestrictions
                ? 'Your account becomes eligible again after all issued books are returned and pending fines are cleared.'
                : 'All your data will be removed and cannot be recovered.',
            'button_enabled' => ! $hasRestrictions,
            'button_label' => 'Delete Account',
            'button_tooltip' => 'This action cannot be undone',
            'issued_books_count' => $issuedBooksCount,
            'pending_fines_count' => $pendingFinesCount,
        ];
    }

    public function delete(User $user): void
    {
        $user->loadMissing('student', 'staff');

        DB::transaction(function () use ($user): void {
            $student = $user->student;
            $staff = $user->staff;

            ActivityLogger::logActivity(
                'account_deleted',
                'Account deleted by account owner',
                'user',
                'user',
                $user->id,
                [
                    'self_service' => true,
                    'deleted_user_id' => $user->id,
                    'deleted_user_role' => $user->role,
                    'deleted_user_email' => $user->email,
                    'student_profile_id' => $student?->id,
                    'staff_profile_id' => $staff?->id,
                ]
            );

            DB::table('sessions')->where('user_id', $user->id)->delete();

            IssuedBook::query()
                ->where('issued_by', $user->id)
                ->update(['issued_by' => null]);

            $this->deleteStoredProfilePhoto($user->profile_photo);

            $student?->delete();
            $staff?->delete();
            $user->delete();
        });
    }

    private function deleteStoredProfilePhoto(?string $photoPath): void
    {
        if (! $photoPath) {
            return;
        }

        $normalizedPath = str_starts_with($photoPath, 'storage/')
            ? substr($photoPath, 8)
            : ltrim($photoPath, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }
}
