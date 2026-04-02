<?php

namespace App\Http\Requests\BookRequestManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateBookRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $requestIds = $this->input('request_ids', []);

        if (is_string($requestIds)) {
            $requestIds = array_filter(
                array_map('trim', explode(',', $requestIds)),
                static fn (string $value) => $value !== ''
            );
        }

        $normalizedIds = is_array($requestIds)
            ? $requestIds
            : [];

        $this->merge([
            'status' => strtolower(trim((string) $this->input('status', ''))),
            'request_ids' => $normalizedIds,
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer', 'distinct', Rule::exists('book_requests', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please choose an action for the selected requests.',
            'status.in' => 'Only approve or reject actions are allowed from this page.',
            'request_ids.required' => 'Please select at least one request to continue.',
            'request_ids.array' => 'The selected requests could not be processed.',
            'request_ids.min' => 'Please select at least one request to continue.',
            'request_ids.*.integer' => 'One of the selected request IDs is invalid.',
            'request_ids.*.distinct' => 'Duplicate request selections are not allowed.',
            'request_ids.*.exists' => 'One of the selected requests no longer exists.',
        ];
    }

    public function status(): string
    {
        return (string) $this->validated('status');
    }

    public function requestIds(): array
    {
        return collect($this->validated('request_ids', []))
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->values()
            ->all();
    }
}
