<?php

namespace App\Http\Requests\Api;

class StaffIssueStoreRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'book_id' => ['required_without:book_ids', 'integer', 'exists:books,id'],
            'book_ids' => ['required_without:book_id', 'array', 'min:1', 'max:5'],
            'book_ids.*' => ['integer', 'distinct', 'exists:books,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function bookIds(): array
    {
        return $this->filled('book_ids')
            ? array_values(array_unique(array_map('intval', $this->input('book_ids', []))))
            : [(int) $this->integer('book_id')];
    }
}
