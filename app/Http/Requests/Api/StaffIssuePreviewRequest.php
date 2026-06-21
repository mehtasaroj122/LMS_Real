<?php

namespace App\Http\Requests\Api;

class StaffIssuePreviewRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'book_ids' => ['required', 'array', 'min:1', 'max:5'],
            'book_ids.*' => ['integer', 'distinct', 'exists:books,id'],
        ];
    }

    public function bookIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->input('book_ids', []))));
    }
}
