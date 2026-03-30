<?php

namespace App\Http\Requests\FineManagement;

use Illuminate\Foundation\Http\FormRequest;

class WaiveFineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['reason', 'remarks'] as $field) {
            if ($this->has($field)) {
                $normalized[$field] = trim((string) $this->input($field));
            }
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500', 'required_without:remarks'],
            'remarks' => ['nullable', 'string', 'max:500', 'required_without:reason'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_without' => 'Please enter a reason for waiving the fine.',
            'remarks.required_without' => 'Please enter a reason for waiving the fine.',
        ];
    }

    public function waiverReason(): string
    {
        return trim((string) ($this->validated()['reason'] ?? $this->validated()['remarks'] ?? ''));
    }
}
