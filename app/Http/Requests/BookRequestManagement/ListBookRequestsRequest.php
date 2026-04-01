<?php

namespace App\Http\Requests\BookRequestManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBookRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['search', 'status', 'sort', 'page', 'per_page'] as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $value = trim((string) $this->input($field));

            if (in_array($field, ['status', 'sort'], true)) {
                $value = strtolower($value);
            }

            $normalized[$field] = $value === '' ? null : $value;
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['all', 'pending', 'approved', 'rejected'])],
            'sort' => ['nullable', Rule::in(['date-desc', 'date-asc', 'student-asc', 'book-asc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Select a valid request status filter.',
            'sort.in' => 'Select a valid request sorting option.',
        ];
    }
}
