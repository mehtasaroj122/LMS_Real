<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteRegistrationRequest;
use App\Services\Auth\InvitedUserRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
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
