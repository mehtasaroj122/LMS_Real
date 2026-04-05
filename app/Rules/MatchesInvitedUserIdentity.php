<?php

namespace App\Rules;

use App\Services\Auth\InvitedUserRegistrationService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class MatchesInvitedUserIdentity implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function __construct(
        protected string $role,
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(InvitedUserRegistrationService::class);
        $role = strtolower(trim((string) ($this->data['role'] ?? $this->role)));

        if (!in_array($role, ['staff', 'student'], true)) {
            return;
        }

        $identifier = $role === 'staff'
            ? ($this->data['staff_id'] ?? $value)
            : ($this->data['student_id'] ?? $value);

        $email = (string) ($this->data['email'] ?? '');
        $phone = $this->data['phone'] ?? null;

        if ($service->normalizeEmail($email) === '' || $service->normalizeIdentifier($identifier) === null) {
            return;
        }

        $validation = $service->validateIdentity($role, $email, $phone, $identifier);

        if (($validation['valid'] ?? false) !== true) {
            $fail((string) ($validation['message'] ?? 'The invitation details do not match our records.'));
        }
    }
}
