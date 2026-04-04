<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OTPVerificationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\User;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Validate live registration fields.
     */
    public function validateField(Request $request): JsonResponse
    {
        if ((string) $request->input('field') !== 'email') {
            return response()->json([
                'valid' => false,
                'message' => 'Unsupported validation field.',
            ], 422);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'email' => ['bail', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            ],
            [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email has already been taken.',
                'email.max' => 'Email address must not exceed 255 characters.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first('email'),
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Email address is available.',
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedPassword = Hash::make($request->password);

        // Store registration data in session (NOT in database yet)
        session([
            'registration_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10)->timestamp,
            ]
        ]);

        // Send OTP via email
        try {
            Mail::to($request->email)->send(new OTPVerificationMail($otp, $request->name));
            \Log::info('Sent registration OTP email', [
                'email' => $request->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            return redirect()->route('register')
                ->withErrors(['email' => 'Failed to send OTP. Please try again.']);
        }

        // Redirect to OTP verification page
        return redirect()->route('verify.otp.page', ['email' => $request->email])
            ->with('status', 'OTP has been sent to your email. Please verify to complete registration.');
    }
}
