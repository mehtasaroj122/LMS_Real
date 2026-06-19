<?php

namespace App\Http\Requests\Api;

class StudentBookRequestStoreRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
