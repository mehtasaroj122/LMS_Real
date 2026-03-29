<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function normalizeInput(array $input): array
    {
        $cleanText = static function ($value): ?string {
            if ($value === null) {
                return null;
            }

            $value = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $value)));

            return $value === '' ? null : $value;
        };

        $normalizePhone = static function ($value): ?string {
            if ($value === null) {
                return null;
            }

            $raw = trim((string) $value);
            if ($raw === '') {
                return null;
            }

            $digits = preg_replace('/\D/', '', $raw);

            return str_starts_with($raw, '+') ? '+' . $digits : $digits;
        };

        return array_merge($input, [
            'name' => $cleanText($input['name'] ?? null),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'password' => (string) ($input['password'] ?? ''),
            'role' => $cleanText($input['role'] ?? null),
            'phone' => $normalizePhone($input['phone'] ?? null),
            'address' => $cleanText($input['address'] ?? null),
            'department_id' => $cleanText($input['department_id'] ?? null),
            'designation' => $cleanText($input['designation'] ?? null),
            'join_date' => $cleanText($input['join_date'] ?? null),
            'roll_no' => ($rollNo = $cleanText($input['roll_no'] ?? null)) !== null ? strtoupper($rollNo) : null,
            'batch' => $cleanText($input['batch'] ?? null),
            'semester' => $cleanText($input['semester'] ?? null),
            'status' => $cleanText($input['status'] ?? null),
        ]);
    }

    public static function rulesFor(?User $user = null, bool $isCreate = true): array
    {
        $studentId = $user?->student?->id;

        $rules = [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z ]+$/'],
            'email' => [
                'bail',
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['bail', 'required', Rule::in(['admin', 'staff', 'student'])],
            'phone' => [
                'bail',
                'nullable',
                'string',
                'min:8',
                'max:20',
                'regex:/^\+[1-9]\d{7,14}$/',
                Rule::unique('users', 'phone')->ignore($user?->id),
            ],
            'address' => ['bail', 'nullable', 'string', 'min:10', 'max:255', 'not_regex:/<[^>]*>/'],
            'department_id' => ['bail', 'nullable', 'required_if:role,student,staff', 'integer', Rule::exists('departments', 'id')],
            'roll_no' => [
                'bail',
                'nullable',
                'required_if:role,student',
                'string',
                'min:3',
                'max:100',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('students', 'roll_no')->ignore($studentId),
            ],
            'batch' => ['bail', 'nullable', 'required_if:role,student', 'regex:/^(19|20)\d{2}$/'],
            'designation' => ['bail', 'nullable', 'required_if:role,staff', 'string', 'min:2', 'max:100'],
            'join_date' => ['bail', 'nullable', 'required_if:role,staff', 'date', 'before_or_equal:today'],
            'semester' => ['bail', 'nullable', 'required_if:role,student', 'integer', 'between:1,8'],
        ];

        if ($isCreate) {
            $rules['password'] = ['bail', 'required', 'string', 'min:6', 'max:255'];
            $rules['status'] = ['bail', 'required', Rule::in(['active', 'inactive'])];
        }

        return $rules;
    }

    public static function validationMessages(): array
    {
        return [
            'name.required' => 'Enter the user\'s full name.',
            'name.min' => 'Full name must be at least 2 characters long.',
            'name.max' => 'Full name must be 255 characters or fewer.',
            'name.regex' => 'Full name can use letters and spaces only.',

            'email.required' => 'Enter the user\'s email address.',
            'email.email' => 'Enter a valid email address, like user@example.com.',
            'email.max' => 'Email address must be 255 characters or fewer.',
            'email.unique' => 'This email is already assigned to another user.',

            'password.required' => 'Enter a password for the user.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.max' => 'Password must be 255 characters or fewer.',

            'role.required' => 'Select a user role.',
            'role.in' => 'Select a valid user role.',

            'phone.min' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.max' => 'Phone number is too long. Use international format like +9779812345678.',
            'phone.regex' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.unique' => 'This phone number is already assigned to another user.',

            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address must be 255 characters or fewer.',
            'address.not_regex' => 'Address contains unsupported characters. Remove any HTML or script-like content.',

            'department_id.required_if' => 'Select a department.',
            'department_id.integer' => 'Select a valid department.',
            'department_id.exists' => 'Select a valid department.',

            'designation.required_if' => 'Enter the staff designation.',
            'designation.min' => 'Staff designation must be at least 2 characters long.',
            'designation.max' => 'Staff designation must be 100 characters or fewer.',

            'join_date.required_if' => 'Select the join date for the staff member.',
            'join_date.date' => 'Enter a valid join date.',
            'join_date.before_or_equal' => 'Join date cannot be in the future.',

            'roll_no.required_if' => 'Enter the student ID.',
            'roll_no.min' => 'Student ID must be at least 3 characters long.',
            'roll_no.max' => 'Student ID must be 100 characters or fewer.',
            'roll_no.regex' => 'Student ID can use letters, numbers, and hyphens only.',
            'roll_no.unique' => 'This student ID is already in use.',

            'batch.required_if' => 'Enter the batch year.',
            'batch.regex' => 'Batch year must be a 4-digit year.',

            'semester.required_if' => 'Select the current semester.',
            'semester.integer' => 'Semester must be a number between 1 and 8.',
            'semester.between' => 'Semester must be a number between 1 and 8.',

            'status.required' => 'Select the user status.',
            'status.in' => 'Select a valid user status.',
        ];
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return self::rulesFor($user, $this->isMethod('post') && !$user);
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::normalizeInput($this->all()));
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $firstField = array_key_first($errors);
        $firstMessage = $errors[$firstField][0] ?? 'Validation error occurred';

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstMessage,
            'errors' => $errors,
            'first_error_field' => $firstField,
        ], 422));
    }
}
