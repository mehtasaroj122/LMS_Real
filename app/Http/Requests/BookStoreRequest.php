<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book');

        return [
            'isbn' => [
                'required',
                'string',
                'regex:/^[0-9]{10,13}$/',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'shelf_no' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9]+[-]?[A-Za-z0-9]*$/',
                'max:20',
            ],
            'title' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z0-9\s\-:\'.&()]+$/',
            ],
            'author' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z\s.]+$/',
            ],
            'publisher' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\s&.,\'-]+$/',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'new_category' => [
                'nullable',
                'string',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z\s&]+$/',
                'required_without:category_id',
            ],
            'total_copies' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
            'available_copies' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
                'lte:total_copies',
            ],
            'condition' => [
                'required',
                Rule::in(['new', 'good', 'damaged']),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.regex' => 'ISBN must contain only 10-13 digits',
            'shelf_no.regex' => 'Rack number must contain only letters, numbers, and optional dash (e.g., A-12, B5)',
            'title.regex' => 'Title can only contain letters, numbers, spaces, and special characters: - : \' . & ( )',
            'author.regex' => 'Author name can only contain letters, spaces, and periods',
            'publisher.regex' => 'Publisher name can only contain letters, numbers, spaces, and special characters: & . , \' -',
            'new_category.regex' => 'Category name can only contain letters, spaces, and &',
            'available_copies.lte' => 'Available copies cannot exceed total copies',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('isbn')) {
            $this->merge([
                'isbn' => preg_replace('/[-\s]/', '', $this->isbn),
            ]);
        }
    }
}
