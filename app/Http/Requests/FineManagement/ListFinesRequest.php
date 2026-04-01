<?php

namespace App\Http\Requests\FineManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListFinesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['search', 'date_from', 'date_to'] as $field) {
            if ($this->has($field)) {
                $value = trim((string) $this->input($field));
                $normalized[$field] = $value === '' ? null : $value;
            }
        }

        foreach (['status', 'sort'] as $field) {
            if ($this->has($field)) {
                $value = strtolower(trim((string) $this->input($field)));
                $normalized[$field] = $value === '' ? null : $value;
            }
        }

        foreach (['min_amount', 'max_amount', 'page', 'per_page'] as $field) {
            if ($this->has($field)) {
                $value = trim((string) $this->input($field));
                $normalized[$field] = $value === '' ? null : $value;
            }
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'waived', 'overdue'])],
            'sort' => ['nullable', Rule::in(['date-desc', 'date-asc', 'amount-desc', 'amount-asc'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'min_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99', 'gte:min_amount'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'The end date must be on or after the start date.',
            'max_amount.gte' => 'The maximum amount must be greater than or equal to the minimum amount.',
            'status.in' => 'Select a valid fine status filter.',
            'sort.in' => 'Select a valid fine sorting option.',
        ];
    }
}
