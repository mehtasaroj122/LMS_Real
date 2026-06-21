<?php

namespace App\Http\Requests\Api;

class StaffReturnRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition' => ['nullable', 'in:good,fair,damaged,lost'],
            'return_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
