<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($this->user()?->id),
            ],
        ];
    }
}
