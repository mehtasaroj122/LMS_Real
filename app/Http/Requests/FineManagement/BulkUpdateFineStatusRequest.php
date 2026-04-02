<?php

namespace App\Http\Requests\FineManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateFineStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $fineIds = $this->input('fine_ids', []);

        if (is_string($fineIds)) {
            $fineIds = array_filter(
                array_map('trim', explode(',', $fineIds)),
                static fn (string $value) => $value !== ''
            );
        }

        $normalized = [
            'status' => strtolower(trim((string) $this->input('status', ''))),
            'fine_ids' => is_array($fineIds) ? $fineIds : [],
        ];

        foreach (['reason', 'remarks'] as $field) {
            if ($this->has($field)) {
                $normalized[$field] = trim((string) $this->input($field));
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $waiveReasonRequired = function (): bool {
            return $this->input('status') === 'waived' && !$this->filled('remarks');
        };

        $waiveRemarksRequired = function (): bool {
            return $this->input('status') === 'waived' && !$this->filled('reason');
        };

        return [
            'status' => ['required', Rule::in(['paid', 'waived'])],
            'fine_ids' => ['required', 'array', 'min:1'],
            'fine_ids.*' => ['integer', 'distinct', Rule::exists('fines', 'id')],
            'reason' => ['nullable', 'string', 'max:500', Rule::requiredIf($waiveReasonRequired)],
            'remarks' => ['nullable', 'string', 'max:500', Rule::requiredIf($waiveRemarksRequired)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please choose an action for the selected fines.',
            'status.in' => 'Only mark as paid or waive actions are allowed from this page.',
            'fine_ids.required' => 'Please select at least one fine to continue.',
            'fine_ids.array' => 'The selected fines could not be processed.',
            'fine_ids.min' => 'Please select at least one fine to continue.',
            'fine_ids.*.integer' => 'One of the selected fine IDs is invalid.',
            'fine_ids.*.distinct' => 'Duplicate fine selections are not allowed.',
            'fine_ids.*.exists' => 'One of the selected fines no longer exists.',
            'reason.required_if' => 'Please enter a reason for waiving the selected fines.',
            'remarks.required_if' => 'Please enter a reason for waiving the selected fines.',
        ];
    }

    public function status(): string
    {
        return (string) $this->validated('status');
    }

    public function fineIds(): array
    {
        return collect($this->validated('fine_ids', []))
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->values()
            ->all();
    }

    public function waiverReason(): ?string
    {
        $reason = trim((string) ($this->validated('reason') ?? $this->validated('remarks') ?? ''));

        return $reason !== '' ? $reason : null;
    }
}
