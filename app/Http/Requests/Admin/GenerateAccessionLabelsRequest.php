<?php

namespace App\Http\Requests\Admin;

use App\Services\AccessionNumberGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'method' => $this->input('method', 'quantity'),
            'start' => strtoupper(trim((string) $this->input('start'))),
            'from' => strtoupper(trim((string) $this->input('from'))),
            'to' => strtoupper(trim((string) $this->input('to'))),
            'skip_existing' => $this->boolean('skip_existing'),
        ]);
    }

    public function rules(): array
    {
        $maximum = (int) config('accession-labels.max_per_batch', 500);

        return [
            'method' => ['required', Rule::in(['quantity', 'range'])],
            'start' => ['exclude_unless:method,quantity', 'required_if:method,quantity', 'regex:/^ACC-\d{6}$/'],
            'quantity' => ['exclude_unless:method,quantity', 'required_if:method,quantity', 'integer', 'min:1', "max:{$maximum}"],
            'skip_existing' => ['exclude_unless:method,quantity', 'boolean'],
            'from' => ['exclude_unless:method,range', 'required_if:method,range', 'regex:/^ACC-\d{6}$/'],
            'to' => ['exclude_unless:method,range', 'required_if:method,range', 'regex:/^ACC-\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        $maximum = (int) config('accession-labels.max_per_batch', 500);

        return [
            'start.required_if' => 'The starting accession number is required.',
            'start.regex' => 'Use the current accession format, for example ACC-001800.',
            'quantity.required_if' => 'Enter the total number of labels.',
            'quantity.integer' => 'Total labels must be a whole number.',
            'quantity.min' => 'Generate at least one label.',
            'quantity.max' => "You can generate a maximum of {$maximum} labels at one time.",
            'from.required_if' => 'The starting accession number is required.',
            'to.required_if' => 'The ending accession number is required.',
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
            if ($this->input('method') === 'quantity') {
                $start = $generator->parse((string) $this->input('start'));
                $quantity = (int) $this->input('quantity');

                if ($start === null) {
                    $validator->errors()->add('start', 'The starting accession number must be between ACC-000001 and ACC-999999.');
                } elseif ($start + $quantity - 1 > AccessionNumberGenerator::MAX_NUMBER) {
                    $validator->errors()->add('quantity', 'This quantity exceeds the supported accession-number range.');
                }

                return;
            }

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
