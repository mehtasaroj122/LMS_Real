<?php

namespace App\Http\Requests\Api;

class StaffFinePaymentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['nullable', 'in:cash,card,online'],
        ];
    }
}
