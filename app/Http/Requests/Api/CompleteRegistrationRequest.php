<?php

namespace App\Http\Requests\Api;

use App\Services\Auth\InvitedUserRegistrationService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CompleteRegistrationRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $service = app(InvitedUserRegistrationService::class);
        $role = strtolower(trim((string) $this->input('role', '')));

        $this->merge([
            'role' => $role,
            'email' => $service->normalizeEmail($this->input('email')),
            'identifier' => $service->normalizeIdentifier($this->input('identifier')),
            'phone' => $service->normalizePhone($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'role' => ['bail', 'required', Rule::in(['student', 'staff'])],
            'email' => ['bail', 'required', 'email:rfc', 'max:255'],
            'identifier' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9-]+$/',
            ],
            'phone' => ['bail', 'required', 'string', 'min:8', 'max:20', 'regex:/^\+?[0-9]{8,20}$/'],
            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'password_confirmation' => ['bail', 'required', 'string', 'min:8', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Select the role linked to your invitation.',
            'role.in' => 'Select a valid registration role.',
            'email.required' => 'Enter the email address linked to your account.',
            'email.email' => 'Enter a valid email address.',
            'identifier.required' => 'Enter your student or staff ID.',
            'identifier.min' => 'ID must be at least 3 characters long.',
            'identifier.max' => 'ID must be 50 characters or fewer.',
            'identifier.regex' => 'ID can use letters, numbers, and hyphens only.',
            'phone.required' => 'Enter the phone number linked to your invitation.',
            'phone.regex' => 'Enter a valid phone number.',
            'password.required' => 'Create a password for your account.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password_confirmation.required' => 'Confirm your password.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->has('role') || $validator->errors()->has('email')) {
                return;
            }

            $service = app(InvitedUserRegistrationService::class);
            $role = (string) $this->input('role');
            $email = (string) $this->input('email');
            $user = $service->findUserByRoleAndEmail($role, $email);

            if (! $user) {
                $validator->errors()->add(
                    'email',
                    "We could not find an invited {$role} account with that email address."
                );

                return;
            }

            if ($user->hasCompletedRegistration()) {
                $validator->errors()->add(
                    'email',
                    'This account is already registered. Please sign in.'
                );

                return;
            }

            if ($validator->errors()->has('identifier') || $validator->errors()->has('phone')) {
                return;
            }

            $identityValidation = $service->validateIdentity(
                $role,
                $email,
                $this->input('phone'),
                $this->input('identifier'),
            );

            if (($identityValidation['valid'] ?? false) === true) {
                return;
            }

            $field = (string) ($identityValidation['field'] ?? 'identifier');
            $message = (string) ($identityValidation['message'] ?? 'The invitation details do not match our records.');

            if (in_array($field, ['staff_id', 'student_id'], true)) {
                $field = 'identifier';
            }

            if ($message === 'This account is already active. Please sign in with your email and password instead.') {
                $message = 'This account is already registered. Please sign in.';
            }

            $validator->errors()->add($field, $message);
        });
    }
}
