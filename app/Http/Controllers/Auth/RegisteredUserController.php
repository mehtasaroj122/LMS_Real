<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteRegistrationRequest;
use App\Services\Auth\InvitedUserRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request, InvitedUserRegistrationService $registrationService): View
    {
        $prefillRole = strtolower(trim((string) $request->query('role', 'staff')));
        $prefillEmail = strtolower(trim((string) $request->query('email', '')));
        $existingAccountMessage = null;

        if (in_array($prefillRole, ['staff', 'student'], true) && $prefillEmail !== '') {
            $emailValidation = $registrationService->validateEmailForRegistration($prefillRole, $prefillEmail);

            if (($emailValidation['valid'] ?? false) !== true) {
                $existingAccountMessage = $emailValidation['message'];
            }
        }

        return view('auth.register', [
            'prefillRole' => $prefillRole,
            'prefillEmail' => $prefillEmail,
            'existingAccountMessage' => $existingAccountMessage,
        ]);
    }

    /**
     * Validate live registration fields.
     */
    public function validateField(Request $request, InvitedUserRegistrationService $registrationService): JsonResponse
    {
        $field = (string) $request->input('field');

        if (!in_array($field, ['email', 'staff_id', 'student_id', 'phone'], true)) {
            return response()->json([
                'valid' => false,
                'message' => 'Unsupported validation field.',
            ], 422);
        }

        $role = strtolower(trim((string) $request->input('role', '')));
        $email = $registrationService->normalizeEmail($request->input('email'));
        $phone = $registrationService->normalizePhone($request->input('phone'));
        $identifier = $role === 'staff'
            ? $request->input('staff_id')
            : $request->input('student_id');

        $validator = Validator::make(
            [
                'role' => $role,
                'email' => $email,
                'phone' => $phone,
                'staff_id' => $registrationService->normalizeIdentifier($request->input('staff_id')),
                'student_id' => $registrationService->normalizeIdentifier($request->input('student_id')),
            ],
            [
                'role' => ['bail', 'required', 'in:staff,student'],
                'email' => ['bail', 'required', 'email:rfc', 'max:255'],
                'phone' => ['bail', 'nullable', 'regex:/^\+[1-9]\d{7,14}$/'],
                'staff_id' => ['bail', 'nullable', 'regex:/^[A-Za-z0-9-]+$/'],
                'student_id' => ['bail', 'nullable', 'regex:/^[A-Za-z0-9-]+$/'],
            ],
            [
                'role.required' => 'Please select the invitation role.',
                'role.in' => 'Please select a valid registration role.',
                'email.required' => 'Please enter the invited email address.',
                'email.email' => 'Please enter a valid email address.',
                'phone.regex' => 'Please use an international phone format like +9779812345678.',
                'staff_id.regex' => 'Staff ID can use letters, numbers, and hyphens only.',
                'student_id.regex' => 'Student ID can use letters, numbers, and hyphens only.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($field === 'email') {
            $emailValidation = $registrationService->validateEmailForRegistration($role, $email);

            if (($emailValidation['valid'] ?? false) !== true) {
                return response()->json([
                    'valid' => false,
                    'message' => $emailValidation['message'] ?? 'This account is already active. Please sign in instead.',
                ], 422);
            }
        }

        if (in_array($field, ['staff_id', 'student_id', 'phone'], true) && $identifier && $email !== '') {
            $validation = $registrationService->validateIdentity($role, $email, $phone, $identifier);

            if (($validation['valid'] ?? false) !== true) {
                return response()->json([
                    'valid' => false,
                    'message' => $validation['message'] ?? 'The invitation details do not match our records.',
                ], 422);
            }
        }

        return response()->json([
            'valid' => true,
            'message' => 'Registration details look valid.',
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(
        CompleteRegistrationRequest $request,
        InvitedUserRegistrationService $registrationService
    ): RedirectResponse
    {
        try {
            $user = $registrationService->completeRegistration($request->validated());
        } catch (\RuntimeException $exception) {
            $message = (string) $exception->getMessage();
            $normalizedMessage = strtolower($message);
            $errorField = $request->input('role') === 'staff' ? 'staff_id' : 'student_id';

            if (str_contains($normalizedMessage, 'already active') || str_contains($normalizedMessage, 'sign in')) {
                $errorField = 'email';
            } elseif (str_contains($normalizedMessage, 'phone number')) {
                $errorField = 'phone';
            }

            return redirect()
                ->route('register')
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    $errorField => $message,
                ]);
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()
            ->to($registrationService->dashboardRouteFor($user))
            ->with('status', 'Registration completed successfully. Your account is now active.');
    }
}
