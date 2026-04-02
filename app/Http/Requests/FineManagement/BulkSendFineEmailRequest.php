<?php

namespace App\Http\Requests\FineManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkSendFineEmailRequest extends FormRequest
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

        $this->merge([
            'fine_ids' => is_array($fineIds) ? $fineIds : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'fine_ids' => ['required', 'array', 'min:1'],
            'fine_ids.*' => ['integer', 'distinct', Rule::exists('fines', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'fine_ids.required' => 'Please select at least one fine to email.',
            'fine_ids.array' => 'The selected fines could not be processed.',
            'fine_ids.min' => 'Please select at least one fine to email.',
            'fine_ids.*.integer' => 'One of the selected fine IDs is invalid.',
            'fine_ids.*.distinct' => 'Duplicate fine selections are not allowed.',
            'fine_ids.*.exists' => 'One of the selected fines no longer exists.',
        ];
    }

    public function fineIds(): array
    {
        return collect($this->validated('fine_ids', []))
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->values()
            ->all();
    }
}
