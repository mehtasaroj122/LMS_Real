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
            'book_copy_id' => ['required_without_all:accession_number,accession_numbers', 'integer', 'exists:book_copies,id'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'accession_number' => ['required_without_all:book_copy_id,accession_numbers', 'string', 'max:32'],
            'accession_numbers' => ['required_without_all:book_copy_id,accession_number', 'array', 'min:1', 'max:5'],
            'accession_numbers.*' => ['string', 'distinct', 'max:32'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function accessionNumbers(): array
    {
        if ($this->filled('accession_numbers')) {
            return array_values(array_unique(array_map('strtoupper', $this->input('accession_numbers', []))));
        }

        return $this->filled('accession_number') ? [strtoupper(trim((string) $this->input('accession_number')))] : [];
    }
}
