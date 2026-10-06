<?php

namespace App\Http\Requests\StudentManagement;

use App\Http\Requests\AdminUserRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends AdminUserRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => $this->input('role', 'student'),
            'status' => $this->input('status', 'inactive'),
        ]);
        parent::prepareForValidation();
    }

    public function rules(): array
    {
        $rules = array_intersect_key(parent::rulesFor(), array_flip([
            'name', 'email', 'role', 'phone', 'gender', 'date_of_birth',
            'address', 'department_id', 'roll_no', 'batch', 'semester', 'status',
        ]));
        $rules['role'] = ['bail', 'required', Rule::in(['student'])];

        return $rules;
    }
}
