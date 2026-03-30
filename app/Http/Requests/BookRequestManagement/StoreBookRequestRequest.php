<?php

namespace App\Http\Requests\BookRequestManagement;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['student_id', 'book_id'] as $field) {
            if ($this->has($field)) {
                $normalized[$field] = trim((string) $this->input($field));
            }
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'Select a valid student record.',
            'book_id.required' => 'Please select a book.',
            'book_id.exists' => 'Select a valid book record.',
        ];
    }
}
