<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizedAddress = is_string($this->address) ? trim($this->address) : $this->address;

        $this->merge([
            'name' => is_string($this->name) ? preg_replace('/\s+/', ' ', trim($this->name)) : $this->name,
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
            'phone' => is_string($this->phone) ? preg_replace('/\D+/', '', $this->phone) : $this->phone,
            'address' => $normalizedAddress === '' ? null : $normalizedAddress,
            'remove_profile_photo' => $this->boolean('remove_profile_photo'),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['bail', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['bail', 'required', 'string', 'regex:/^[0-9]{10,15}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'address' => ['bail', 'nullable', 'string', 'min:10', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:' . now()->subYears(100)->toDateString()],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'remove_profile_photo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name can only contain letters and spaces.',
            'phone.regex' => 'Phone must contain only numbers (10-15 digits).',
            'address.min' => 'Address must be at least 10 characters.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Date of birth must be within the last 100 years.',
        ];
    }
}
