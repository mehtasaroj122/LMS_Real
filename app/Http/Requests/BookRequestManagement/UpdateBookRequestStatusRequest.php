<?php

namespace App\Http\Requests\BookRequestManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower(trim((string) $this->input('status'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please choose an action for this request.',
            'status.in' => 'Only approve or reject actions are allowed from this page.',
        ];
    }

    public function status(): string
    {
        return (string) $this->validated('status');
    }
}
