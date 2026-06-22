<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($this->user()?->id),
            ],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
