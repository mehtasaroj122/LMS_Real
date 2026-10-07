<?php

namespace App\Http\Requests\Admin;

use App\Services\AccessionNumberGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateAccessionLabelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'from' => strtoupper(trim((string) $this->input('from'))),
            'to' => strtoupper(trim((string) $this->input('to'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'from' => ['bail', 'required', 'regex:/^ACC-\d{6}$/'],
            'to' => ['bail', 'required', 'regex:/^ACC-\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.required' => 'The starting accession number is required.',
            'to.required' => 'The ending accession number is required.',
            'from.regex' => 'Use the current accession format, for example ACC-001800.',
            'to.regex' => 'Use the current accession format, for example ACC-001850.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $generator = app(AccessionNumberGenerator::class);
            $start = $generator->parse((string) $this->input('from'));
            $end = $generator->parse((string) $this->input('to'));

            if ($start === null) {
                $validator->errors()->add('from', 'The starting accession number must be between ACC-000001 and ACC-999999.');

                return;
            }

            if ($end === null) {
                $validator->errors()->add('to', 'The ending accession number must be between ACC-000001 and ACC-999999.');

                return;
            }

            if ($end < $start) {
                $validator->errors()->add('to', 'End accession number must be greater than or equal to start accession number.');

                return;
            }

            $count = $end - $start + 1;
            $maximum = (int) config('accession-labels.max_per_batch', 500);
            if ($count > $maximum) {
                $validator->errors()->add('to', "This range contains {$count} labels. Maximum allowed per batch is {$maximum}.");
            }
        }];
    }
}
