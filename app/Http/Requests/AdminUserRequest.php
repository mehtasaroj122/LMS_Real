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

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');
        $studentId = $user?->student?->id;

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in(['admin', 'staff', 'student'])],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'department_id' => ['nullable', 'required_if:role,student,staff', 'exists:departments,id'],
            'roll_no' => [
                'nullable',
                'required_if:role,student',
                'string',
                'max:100',
                Rule::unique('students', 'roll_no')->ignore($studentId),
            ],
            'batch' => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'required_if:role,staff', 'string', 'max:100'],
            'join_date' => ['nullable', 'required_if:role,staff', 'date'],
            'semester' => ['nullable', 'required_if:role,student', Rule::in(['1', '2', '3', '4', '5', '6', '7', '8'])],
        ];

        if ($this->isMethod('post') && !$user) {
            $rules['password'] = ['required', 'string', 'min:6'];
            $rules['status'] = ['required', Rule::in(['active', 'inactive'])];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the user\'s full name',
            'name.min' => 'Name must be at least 2 characters',
            'email.required' => 'Please enter an email address',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered. Please use a different email.',
            'password.required' => 'Please enter a password',
            'password.min' => 'Password must be at least 6 characters',
            'role.required' => 'Please select a user role',
            'role.in' => 'Please select a valid user role',
            'status.required' => 'Please select user status',
            'status.in' => 'Please select a valid status',
            'roll_no.required_if' => 'Please enter the roll number for the student',
            'roll_no.unique' => 'This roll number is already assigned to another student',
            'batch.max' => 'Batch must not exceed 20 characters',
            'department_id.required_if' => 'Please select a department',
            'department_id.exists' => 'The selected department is invalid',
            'designation.required_if' => 'Please enter the staff designation',
            'designation.max' => 'Staff designation must not exceed 100 characters',
            'join_date.required_if' => 'Please select the join date for the staff member',
            'join_date.date' => 'Please enter a valid join date',
            'semester.required_if' => 'Please select the current semester',
            'semester.in' => 'Please select a valid semester',
        ];
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
