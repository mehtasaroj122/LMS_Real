<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookStoreRequest extends FormRequest
{
    public static function rulesFor(mixed $bookId = null): array
    {
        return [
            'isbn' => [
                'bail',
                'required',
                'string',
                'regex:/^[0-9]{5,13}$/',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'title' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z0-9\s,\-:\'.&()]+$/',
            ],
            'author' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z\s.,&\'\-]+$/',
            ],
            'publisher' => [
                'nullable',
                'string',
                'max:150',
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
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'remove_cover_image' => [
                'nullable',
                'boolean',
            ],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'isbn.required' => 'Enter the book ISBN.',
            'isbn.regex' => 'ISBN must contain 5 to 13 digits. You may use / or - as separators.',
            'isbn.unique' => 'This ISBN is already assigned to another book.',

            'title.required' => 'Enter the book title.',
            'title.min' => 'Book title must be at least 2 characters long.',
            'title.max' => 'Book title must be 255 characters or fewer.',
            'title.regex' => 'Title can only contain letters, numbers, spaces, commas, and - : \' . & ( ).',

            'author.required' => 'Enter the author name.',
            'author.min' => 'Author name must be at least 2 characters long.',
            'author.max' => 'Author name must be 255 characters or fewer.',
            'author.regex' => 'Author name can only contain letters, spaces, periods, commas, apostrophes, hyphens, and ampersands.',

            'publisher.max' => 'Publisher name must be 150 characters or fewer.',
            'publisher.regex' => 'Publisher name can only contain letters, numbers, spaces, and & . , \' -.',

            'category_id.exists' => 'Select a valid category.',
            'new_category.required_without' => 'Select an existing category or create a new one.',
            'new_category.min' => 'New category name must be at least 2 characters long.',
            'new_category.max' => 'New category name must be 50 characters or fewer.',
            'new_category.regex' => 'Category name can only contain letters, spaces, and &.',

            'description.max' => 'Description must be 2000 characters or fewer.',

            'cover_image.image' => 'Cover image must be an image file.',
            'cover_image.mimes' => 'Cover image must be a JPG, PNG, or GIF file.',
            'cover_image.max' => 'Cover image must not exceed 2MB.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book');

        return self::rulesFor($bookId);
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('isbn')) {
            $normalized['isbn'] = preg_replace('/[\/\-\s]/', '', (string) $this->isbn);
        }

        foreach (['title', 'author', 'publisher', 'new_category', 'description'] as $field) {
            if ($this->has($field)) {
                $normalized[$field] = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $this->input($field))));
            }
        }

        if ($this->has('remove_cover_image')) {
            $normalized['remove_cover_image'] = $this->boolean('remove_cover_image');
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }
}
