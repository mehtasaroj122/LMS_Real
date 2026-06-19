<?php

namespace App\Http\Requests\Api;

class BookRequestRejectRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
