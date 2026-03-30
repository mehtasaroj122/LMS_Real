<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['bail', 'required', 'string'],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/\d/',
                'confirmed',
                'different:current_password',
            ],
            'password_confirmation' => ['bail', 'required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Enter your current password.',

            'password.required' => 'Enter a new password.',
            'password.min' => 'New password must be at least 8 characters long.',
            'password.regex' => 'New password must include at least one uppercase letter, one lowercase letter, and one number.',
            'password.confirmed' => 'New password and confirmation do not match.',
            'password.different' => 'New password must be different from your current password.',

            'password_confirmation.required' => 'Confirm your new password.',
            'password_confirmation.min' => 'Password confirmation must be at least 8 characters long.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if (!$user) {
                return;
            }

            $currentPassword = (string) $this->input('current_password');
            $newPassword = (string) $this->input('password');

            if ($currentPassword !== '' && !Hash::check($currentPassword, $user->password)) {
                $validator->errors()->add('current_password', 'The current password you entered is incorrect.');
            }

            if ($newPassword !== '' && Hash::check($newPassword, $user->password)) {
                $validator->errors()->add('password', 'New password must be different from your current password.');
            }
        });
    }
}
