<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IssueReturnRequest extends FormRequest
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
        ];
    }
}
