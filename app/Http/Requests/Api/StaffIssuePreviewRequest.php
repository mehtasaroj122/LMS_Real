<?php

namespace App\Http\Requests\Api;

class StaffIssuePreviewRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('accession_numbers'))) {
            $this->merge(['accession_numbers' => array_map(
                fn ($accession) => is_string($accession) ? strtoupper(trim($accession)) : $accession,
                $this->input('accession_numbers')
            )]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'book_ids' => ['required_without:accession_numbers', 'array', 'min:1', 'max:5'],
            'book_ids.*' => ['integer', 'distinct', 'exists:books,id'],
            'accession_numbers' => ['required_without:book_ids', 'array', 'min:1', 'max:5'],
            'accession_numbers.*' => ['required', 'string', 'distinct:strict', 'max:32'],
        ];
    }

    public function bookIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->input('book_ids', []))));
    }
}
