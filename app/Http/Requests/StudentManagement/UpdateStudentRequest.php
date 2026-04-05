<?php

namespace App\Http\Requests\StudentManagement;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->cleanText($this->input('name')),
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'phone' => $this->normalizePhone($this->input('phone')),
            'gender' => $this->cleanText($this->input('gender')),
            'date_of_birth' => $this->cleanText($this->input('date_of_birth')),
            'roll_no' => strtoupper($this->cleanText($this->input('roll_no')) ?? ''),
            'batch' => $this->cleanText($this->input('batch')),
            'semester' => $this->cleanText($this->input('semester')),
            'address' => $this->cleanText($this->input('address')),
            'status' => strtolower($this->cleanText($this->input('status')) ?? ''),
        ]);
    }

    public function rules(): array
    {
        $student = $this->resolveStudent();

        return [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^[A-Za-z ]+$/'],
            'email' => ['bail', 'required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($student?->user_id)],
            'phone' => ['bail', 'required', 'string', 'min:8', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/', Rule::unique('users', 'phone')->ignore($student?->user_id)],
            'gender' => ['bail', 'nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['bail', 'required', 'date', 'before:today'],
            'roll_no' => ['bail', 'required', 'string', 'min:3', 'max:30', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('students', 'roll_no')->ignore($student?->id)],
            'department_id' => ['bail', 'required', 'integer', Rule::exists('departments', 'id')],
            'batch' => ['bail', 'required', 'regex:/^(19|20)\d{2}$/'],
            'semester' => ['bail', 'required', 'integer', 'between:1,12'],
            'address' => ['bail', 'required', 'string', 'min:10', 'max:255', 'not_regex:/<[^>]*>/'],
            'status' => ['bail', 'required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the student\'s full name.',
            'name.min' => 'Full name must be at least 2 characters long.',
            'name.max' => 'Full name must be 100 characters or fewer.',
            'name.regex' => 'Full name can use letters and spaces only.',

            'email.required' => 'Enter the student\'s email address.',
            'email.email' => 'Enter a valid email address, like student@example.com.',
            'email.max' => 'Email address must be 255 characters or fewer.',
            'email.unique' => 'This email is already assigned to another user.',

            'phone.required' => 'Enter the student\'s phone number with country code.',
            'phone.min' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.max' => 'Phone number is too long. Use international format like +9779812345678.',
            'phone.regex' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.unique' => 'This phone number is already assigned to another user.',

            'gender.in' => 'Select a valid gender option.',

            'date_of_birth.required' => 'Select the student\'s date of birth.',
            'date_of_birth.date' => 'Enter a valid date of birth.',
            'date_of_birth.before' => 'Date of birth must be earlier than today.',

            'roll_no.required' => 'Enter the student ID.',
            'roll_no.min' => 'Student ID must be at least 3 characters long.',
            'roll_no.max' => 'Student ID must be 30 characters or fewer.',
            'roll_no.regex' => 'Student ID can use letters, numbers, and hyphens only.',
            'roll_no.unique' => 'This student ID is already in use.',

            'department_id.required' => 'Select a department.',
            'department_id.integer' => 'Select a valid department.',
            'department_id.exists' => 'Select a valid department.',

            'batch.required' => 'Enter the batch year.',
            'batch.regex' => 'Batch year must be a 4-digit year.',

            'semester.required' => 'Enter the semester number.',
            'semester.integer' => 'Semester must be a number between 1 and 12.',
            'semester.between' => 'Semester must be a number between 1 and 12.',

            'address.required' => 'Enter the student\'s address.',
            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address must be 255 characters or fewer.',
            'address.not_regex' => 'Address contains unsupported characters. Remove any HTML or script-like content.',

            'status.required' => 'Select the student status.',
            'status.in' => 'Select a valid student status.',
        ];
    }

    protected function resolveStudent(): ?Student
    {
        $routeStudent = $this->route('student');

        if ($routeStudent instanceof Student) {
            return $routeStudent;
        }

        if (is_scalar($routeStudent) && $routeStudent !== '') {
            return Student::find($routeStudent);
        }

        $routeId = $this->route('id');

        if (is_scalar($routeId) && $routeId !== '') {
            return Student::find($routeId);
        }

        return null;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $dateOfBirth = $this->input('date_of_birth');

            if (blank($dateOfBirth) || $validator->errors()->has('date_of_birth')) {
                return;
            }

            try {
                $age = Carbon::parse($dateOfBirth)->age;

                if ($age < 14 || $age > 100) {
                    $validator->errors()->add('date_of_birth', 'Student age must be between 14 and 100 years.');
                }
            } catch (\Throwable $exception) {
                $validator->errors()->add('date_of_birth', 'Enter a valid date of birth.');
            }
        });
    }

    protected function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $value)));
    }

    protected function normalizePhone($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D/', '', $raw);

        return str_starts_with($raw, '+') ? '+' . $digits : $digits;
    }
}
