<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'confirmation_text' => ['required', 'string', Rule::in(['DELETE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation_text.required' => 'Please type DELETE to confirm',
            'confirmation_text.in' => 'Please type DELETE to confirm',
        ];
    }
}
