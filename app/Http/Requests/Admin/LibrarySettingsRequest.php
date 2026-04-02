<?php

namespace App\Http\Requests\Admin;

use App\Models\FineSetting;
use Illuminate\Foundation\Http\FormRequest;

class LibrarySettingsRequest extends FormRequest
{
    protected const SETTING_FIELDS = [
        'per_day_fine',
        'grace_period_days',
        'max_fine_amount',
        'lost_book_penalty',
        'damaged_book_penalty',
        'fair_condition_penalty',
        'issue_duration_days',
        'max_books_per_student',
        'renewal_limit',
        'renewal_duration_days',
        'logo_fallback_text',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $defaults = FineSetting::defaults(self::SETTING_FIELDS);
        $normalized = [];

        foreach (self::SETTING_FIELDS as $field) {
            if ($this->input($field) === null || $this->input($field) === '') {
                $normalized[$field] = $defaults[$field];
            }
        }

        $fallbackText = trim((string) $this->input('logo_fallback_text', ''));
        if ($fallbackText !== '') {
            $normalized['logo_fallback_text'] = preg_replace('/\s+/', ' ', $fallbackText);
        }

        $normalized['remove_logo'] = $this->boolean('remove_logo');

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'per_day_fine' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:365'],
            'max_fine_amount' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'lost_book_penalty' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'damaged_book_penalty' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'fair_condition_penalty' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'issue_duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'max_books_per_student' => ['required', 'integer', 'min:1', 'max:100'],
            'renewal_limit' => ['required', 'integer', 'min:0', 'max:10'],
            'renewal_duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'logo_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
            'logo_fallback_text' => ['required', 'string', 'max:10'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
