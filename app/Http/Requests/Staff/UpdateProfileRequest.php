<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cleanString = static function (mixed $value): mixed {
            if (!is_string($value)) {
                return $value;
            }

            $value = preg_replace('/\s+/', ' ', trim($value));

            return $value === '' ? null : $value;
        };

        $phone = $this->input('phone');
        $normalizedPhone = null;

        if (is_string($phone)) {
            $normalizedPhone = preg_replace('/[^\d+]+/', '', trim($phone));
            $normalizedPhone = preg_replace('/(?!^)\+/', '', (string) $normalizedPhone);
            $normalizedPhone = $normalizedPhone === '' ? null : $normalizedPhone;
        }

        $this->merge([
            'name' => $cleanString($this->input('name')),
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'phone' => $normalizedPhone,
            'address' => $cleanString($this->input('address')),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:255', "regex:/^[A-Za-z\s'\-]+$/"],
            'email' => ['bail', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['bail', 'nullable', 'string', 'regex:/^\+?\d{10,20}$/'],
            'address' => ['bail', 'nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'name.min' => 'Full name must be at least 2 characters long.',
            'name.max' => 'Full name must be 255 characters or fewer.',
            'name.regex' => 'Full name can only contain letters, spaces, hyphens, and apostrophes.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Enter a valid email address.',
            'email.max' => 'Email address must be 255 characters or fewer.',
            'email.unique' => 'This email address is already in use.',

            'phone.regex' => 'Phone number must contain 10 to 20 digits and may start with a plus sign.',
            'phone.max' => 'Phone number must be 20 digits or fewer.',

            'address.max' => 'Address must be 500 characters or fewer.',

            'profile_photo.image' => 'Profile photo must be an image file.',
            'profile_photo.mimes' => 'Profile photo must be a JPEG, PNG, JPG, or GIF file.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ];
    }
}
