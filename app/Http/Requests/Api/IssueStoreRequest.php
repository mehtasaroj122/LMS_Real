<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IssueStoreRequest extends FormRequest
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
            'book_ids' => ['required_without:book_id', 'array', 'min:1', 'max:50'],
            'book_ids.*' => ['integer', 'distinct', 'exists:books,id'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function bookIds(): array
    {
        return $this->filled('book_ids')
            ? array_values(array_unique($this->array('book_ids')))
            : [(int) $this->integer('book_id')];
    }
}
