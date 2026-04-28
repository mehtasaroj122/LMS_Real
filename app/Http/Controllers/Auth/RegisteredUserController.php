<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteRegistrationRequest;
use App\Services\Auth\InvitedUserRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

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
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(
        CompleteRegistrationRequest $request,
        InvitedUserRegistrationService $registrationService
    ): RedirectResponse|JsonResponse
    {
        try {
            $user = $registrationService->completeRegistration($request->validated());
        } catch (\RuntimeException $exception) {
            $message = (string) $exception->getMessage();
            $role = (string) $request->input('role');
            $identifierField = $role === 'staff' ? 'staff_id' : 'student_id';
            $identityValidation = $registrationService->validateIdentity(
                $role,
                (string) $request->input('email'),
                $request->input('phone'),
                $request->input($identifierField),
            );
            $errorField = (string) ($identityValidation['field'] ?? $identifierField);
            $resolvedMessage = (string) ($identityValidation['message'] ?? $message);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $resolvedMessage,
                    'errors' => [
                        $errorField => [$resolvedMessage],
                    ],
                ], 422);
            }

            return redirect()
                ->route('register')
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    $errorField => $resolvedMessage,
                ]);
        }

        event(new Registered($user));
        Auth::login($user);

        $redirectTo = $registrationService->dashboardRouteFor($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully. Your account is now active.',
                'redirect' => $redirectTo,
            ]);
        }

        return redirect()
            ->to($redirectTo)
            ->with('status', 'Registration completed successfully. Your account is now active.');
    }
}
