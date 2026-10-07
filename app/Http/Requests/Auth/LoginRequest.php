<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const FORM_ERROR_KEY = 'auth';

    public const ACCOUNT_INACTIVE_ERROR = 'account_inactive';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        // Check if user exists and get their status before authenticating
        $user = User::where('email', $this->email)->first();

        if ($user && $user->status === 'inactive') {
            throw ValidationException::withMessages([
                self::FORM_ERROR_KEY => self::ACCOUNT_INACTIVE_ERROR,
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                self::FORM_ERROR_KEY => trans('auth.failed'),
            ]);
        }
    }
}
