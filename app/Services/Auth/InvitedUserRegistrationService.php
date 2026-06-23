<?php

namespace App\Services\Auth;

use App\Helpers\ActivityLogger;
use App\Mail\WelcomeEmail;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvitedUserRegistrationService
{
    protected function normalizeRole(string $role): string
    {
        return strtolower(trim($role));
    }

    protected function identifierFieldForRole(string $role): string
    {
        return $this->normalizeRole($role) === 'staff' ? 'staff_id' : 'student_id';
    }

    protected function identifierLabelForRole(string $role): string
    {
        return $this->normalizeRole($role) === 'staff' ? 'staff ID' : 'student ID';
    }

    protected function invitedUserMatchesIdentifier(User $user, string $role, ?string $identifier): bool
    {
        $normalizedRole = $this->normalizeRole($role);
        $normalizedIdentifier = $this->normalizeIdentifier($identifier);

        if ($normalizedIdentifier === null) {
            return false;
        }

        if ($normalizedRole === 'staff') {
            return $this->normalizeIdentifier($user->staff?->staff_id) === $normalizedIdentifier;
        }

        if ($normalizedRole === 'student') {
            return in_array($normalizedIdentifier, array_filter([
                $this->normalizeIdentifier($user->student?->student_id),
                $this->normalizeIdentifier($user->student?->roll_no),
            ]), true);
        }

        return false;
    }

    public function normalizeEmail(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    public function normalizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw);

        return str_starts_with($raw, '+') ? '+' . $digits : $digits;
    }

    public function normalizeIdentifier(?string $value): ?string
    {
        $identifier = trim((string) $value);

        return $identifier === '' ? null : strtoupper($identifier);
    }

    public function findUserByRoleAndEmail(string $role, string $email): ?User
    {
        $normalizedRole = $this->normalizeRole($role);
        $normalizedEmail = $this->normalizeEmail($email);

        if ($normalizedEmail === '' || !in_array($normalizedRole, ['staff', 'student'], true)) {
            return null;
        }

        return User::query()
            ->with(['staff', 'student'])
            ->where('role', $normalizedRole)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();
    }

    public function validateEmailForRegistration(string $role, string $email): array
    {
        $user = $this->findUserByRoleAndEmail($role, $email);

        if ($user && $user->hasCompletedRegistration()) {
            return [
                'valid' => false,
                'user' => $user,
                'field' => 'email',
                'message' => 'This account is already active. Please sign in with your email and password instead.',
            ];
        }

        return [
            'valid' => true,
            'user' => $user,
            'field' => null,
            'message' => null,
        ];
    }

    public function findPendingUser(string $role, string $email, ?string $identifier): ?User
    {
        $normalizedRole = $this->normalizeRole($role);
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedIdentifier = $this->normalizeIdentifier($identifier);

        if ($normalizedEmail === '' || $normalizedIdentifier === null || !in_array($normalizedRole, ['staff', 'student'], true)) {
            return null;
        }

        $query = User::query()
            ->with(['staff', 'student'])
            ->where('role', $normalizedRole)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail]);

        if ($normalizedRole === 'staff') {
            $query->whereHas('staff', function ($builder) use ($normalizedIdentifier) {
                $builder->where('staff_id', $normalizedIdentifier);
            });
        }

        if ($normalizedRole === 'student') {
            $query->whereHas('student', function ($builder) use ($normalizedIdentifier) {
                $builder->where(function ($studentQuery) use ($normalizedIdentifier) {
                    $studentQuery
                        ->where('student_id', $normalizedIdentifier)
                        ->orWhere('roll_no', $normalizedIdentifier);
                });
            });
        }

        return $query->first();
    }

    public function validateIdentity(string $role, string $email, ?string $phone, ?string $identifier): array
    {
        $normalizedRole = $this->normalizeRole($role);
        $normalizedPhone = $this->normalizePhone($phone);
        $identifierField = $this->identifierFieldForRole($normalizedRole);
        $identifierLabel = $this->identifierLabelForRole($normalizedRole);
        $user = $this->findUserByRoleAndEmail($normalizedRole, $email);

        if (! $user) {
            return [
                'valid' => false,
                'user' => null,
                'field' => 'email',
                'message' => "We could not find an invited {$normalizedRole} account with that email address.",
            ];
        }

        if ($user->hasCompletedRegistration()) {
            return [
                'valid' => false,
                'user' => $user,
                'field' => 'email',
                'message' => 'This account is already active. Please sign in with your email and password instead.',
            ];
        }

        if (! $this->invitedUserMatchesIdentifier($user, $normalizedRole, $identifier)) {
            return [
                'valid' => false,
                'user' => $user,
                'field' => $identifierField,
                'message' => "We could not find an invited {$normalizedRole} account with that {$identifierLabel}.",
            ];
        }

        $storedPhone = $this->normalizePhone($user->phone);

        if ($storedPhone !== null && $storedPhone !== $normalizedPhone) {
            return [
                'valid' => false,
                'user' => $user,
                'field' => 'phone',
                'message' => 'The phone number does not match the invited account details.',
            ];
        }

        if ($storedPhone === null && $normalizedPhone !== null) {
            $phoneInUse = User::query()
                ->where('phone', $normalizedPhone)
                ->whereKeyNot($user->id)
                ->exists();

            if ($phoneInUse) {
                return [
                    'valid' => false,
                    'user' => $user,
                    'field' => 'phone',
                    'message' => 'This phone number is already assigned to another user.',
                ];
            }
        }

        return [
            'valid' => true,
            'user' => $user,
            'field' => null,
            'message' => null,
        ];
    }

    public function completeRegistration(array $data): User
    {
        $role = strtolower(trim((string) ($data['role'] ?? '')));
        $identifier = $role === 'staff'
            ? ($data['staff_id'] ?? null)
            : ($data['student_id'] ?? null);

        $validation = $this->validateIdentity(
            $role,
            (string) ($data['email'] ?? ''),
            $data['phone'] ?? null,
            $identifier,
        );

        /** @var User|null $user */
        $user = $validation['user'] ?? null;

        if (($validation['valid'] ?? false) !== true || !$user) {
            throw new \RuntimeException((string) ($validation['message'] ?? 'Unable to complete registration.'));
        }

        $normalizedPhone = $this->normalizePhone($data['phone'] ?? null);
        $normalizedIdentifier = $this->normalizeIdentifier($identifier);

        DB::transaction(function () use ($user, $data, $role, $normalizedPhone, $normalizedIdentifier): void {
            $user->fill([
                'phone' => $normalizedPhone,
                'password' => $data['password'],
                'status' => 'active',
                'is_verified' => true,
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null,
                'force_password_change' => false,
                'password_reset_at' => null,
            ]);
            $user->save();

            if ($role === 'staff') {
                if ($user->staff) {
                    $user->staff->update([
                        'staff_id' => $normalizedIdentifier,
                    ]);
                } else {
                    staff::create([
                        'user_id' => $user->id,
                        'staff_id' => $normalizedIdentifier,
                    ]);
                }
            }

            if ($role === 'student' && $user->student) {
                $user->student->update([
                    'student_id' => $normalizedIdentifier,
                    'roll_no' => $normalizedIdentifier,
                ]);
            }
        });

        ActivityLogger::logActivity(
            'registration_completed',
            "Completed self-registration for {$user->name} (" . ucfirst($user->role) . ')',
            'auth',
            'user',
            $user->id,
            [
                'role' => $user->role,
                'email' => $user->email,
            ]
        );

        $freshUser = $user->fresh(['student.department', 'staff.department']);

        try {
            Mail::to($freshUser->email)->queue(new WelcomeEmail(
                $freshUser->name,
                $this->dashboardRouteFor($freshUser)
            ));
        } catch (\Throwable $exception) {
            \Log::warning('Unable to queue welcome email after invited registration', [
                'user_id' => $freshUser->id,
                'email' => $freshUser->email,
                'message' => $exception->getMessage(),
            ]);
        }

        return $freshUser;
    }

    public function dashboardRouteFor(User $user): string
    {
        return match ($user->role) {
            'staff' => route('staff.dashboard'),
            'student' => route('student.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('dashboard'),
        };
    }
}
