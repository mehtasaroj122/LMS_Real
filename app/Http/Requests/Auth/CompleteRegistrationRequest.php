<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\MatchesInvitedUserIdentity;
use App\Services\Auth\InvitedUserRegistrationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CompleteRegistrationRequest extends FormRequest
{
    protected ?User $matchedUser = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $service = app(InvitedUserRegistrationService::class);

        $this->merge([
            'role' => strtolower($this->cleanText($this->input('role')) ?? ''),
            'email' => $service->normalizeEmail($this->input('email')),
            'phone' => $service->normalizePhone($this->input('phone')),
            'staff_id' => $service->normalizeIdentifier($this->input('staff_id')),
            'student_id' => $service->normalizeIdentifier($this->input('student_id')),
        ]);
    }

    public function rules(): array
    {
        return [
            'role' => ['bail', 'required', Rule::in(['staff', 'student'])],
            'email' => ['bail', 'required', 'email:rfc', 'max:255'],
            'phone' => ['bail', 'required', 'string', 'min:8', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'staff_id' => [
                'bail',
                'nullable',
                'required_if:role,staff',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9-]+$/',
                new MatchesInvitedUserIdentity('staff'),
            ],
            'student_id' => [
                'bail',
                'nullable',
                'required_if:role,student',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9-]+$/',
                new MatchesInvitedUserIdentity('student'),
            ],
            'password' => [
                'bail',
                'required',
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
            'email.max' => 'Email address must be 255 characters or fewer.',

            'phone.required' => 'Enter the phone number linked to your invitation.',
            'phone.min' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.max' => 'Phone number is too long. Use international format like +9779812345678.',
            'phone.regex' => 'Enter a valid phone number with country code, like +9779812345678.',

            'staff_id.required_if' => 'Enter your staff ID.',
            'staff_id.min' => 'Staff ID must be at least 3 characters long.',
            'staff_id.max' => 'Staff ID must be 50 characters or fewer.',
            'staff_id.regex' => 'Staff ID can use letters, numbers, and hyphens only.',

            'student_id.required_if' => 'Enter your student ID.',
            'student_id.min' => 'Student ID must be at least 3 characters long.',
            'student_id.max' => 'Student ID must be 50 characters or fewer.',
            'student_id.regex' => 'Student ID can use letters, numbers, and hyphens only.',

            'password.required' => 'Create a password for your account.',
            'password.confirmed' => 'Password confirmation does not match.',
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
            $emailValidation = $service->validateEmailForRegistration(
                (string) $this->input('role'),
                (string) $this->input('email'),
            );

            if (($emailValidation['valid'] ?? false) !== true) {
                $validator->errors()->add('email', (string) ($emailValidation['message'] ?? 'This account is already active. Please sign in instead.'));
            }
        });
    }

    public function matchedUser(): ?User
    {
        if ($this->matchedUser) {
            return $this->matchedUser;
        }

        if (!$this->validated()) {
            return null;
        }

        $service = app(InvitedUserRegistrationService::class);
        $role = (string) $this->input('role');
        $identifier = $role === 'staff'
            ? $this->input('staff_id')
            : $this->input('student_id');

        $this->matchedUser = $service->findPendingUser(
            $role,
            (string) $this->input('email'),
            $identifier ? (string) $identifier : null,
        );

        return $this->matchedUser;
    }

    protected function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $value)));

        return $value === '' ? null : $value;
    }
}
