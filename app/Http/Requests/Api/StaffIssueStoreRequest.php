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
            'book_id' => ['required_without_all:book_ids,accession_number,accession_numbers', 'integer', 'exists:books,id'],
            'book_ids' => ['required_without_all:book_id,accession_number,accession_numbers', 'array', 'min:1', 'max:5'],
            'book_ids.*' => ['integer', 'distinct', 'exists:books,id'],
            'accession_number' => ['required_without_all:book_id,book_ids,accession_numbers', 'string', 'max:32'],
            'accession_numbers' => ['required_without_all:book_id,book_ids,accession_number', 'array', 'min:1', 'max:5'],
            'accession_numbers.*' => ['string', 'distinct', 'max:32'],
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

    public function accessionNumbers(): array
    {
        if ($this->filled('accession_numbers')) {
            return array_values(array_unique(array_map('strtoupper', $this->input('accession_numbers', []))));
        }

        return $this->filled('accession_number') ? [strtoupper(trim((string) $this->input('accession_number')))] : [];
    }
}
