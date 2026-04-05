<?php

namespace App\Services\Auth;

use App\Mail\RegistrationInvitationEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvitationEmailService
{
    public function sendRegistrationInvite(User $user): bool
    {
        $user->loadMissing('staff.department', 'student.department');

        if (! $user->requiresSelfRegistration()) {
            return false;
        }

        if (! in_array($user->role, ['staff', 'student'], true)) {
            return false;
        }

        $email = strtolower(trim((string) ($user->email ?? '')));
        if ($email === '') {
            return false;
        }

        [$identifierLabel, $identifierValue] = $this->resolveIdentityDetails($user);

        try {
            Mail::to($email)->queue(new RegistrationInvitationEmail(
                userName: trim((string) ($user->name ?: ucfirst($user->role))),
                roleLabel: ucfirst($user->role),
                userEmail: $email,
                registerUrl: route('register', [
                    'role' => $user->role,
                    'email' => $email,
                ]),
                identifierLabel: $identifierLabel,
                identifierValue: $identifierValue,
                phone: filled($user->phone) ? trim((string) $user->phone) : null,
            ));

            Log::info('Queued registration invitation email', [
                'user_id' => $user->id,
                'email' => $email,
                'role' => $user->role,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Unable to queue registration invitation email: ' . $exception->getMessage(), [
                'user_id' => $user->id,
                'email' => $email,
                'role' => $user->role,
            ]);

            return false;
        }
    }

    protected function resolveIdentityDetails(User $user): array
    {
        if ($user->role === 'staff') {
            return [
                'Staff ID',
                trim((string) ($user->staff?->staff_id ?? 'Not assigned yet')),
            ];
        }

        return [
            'Student ID',
            trim((string) ($user->student?->student_id ?: $user->student?->roll_no ?: 'Not assigned yet')),
        ];
    }
}
