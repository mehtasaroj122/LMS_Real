<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OTPVerificationMail;
use App\Models\User;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

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
        // MAIL SYSTEM DISABLED - To re-enable:
        // 1. Uncomment the code below
        // 2. Set MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD in .env
        // 3. Uncomment the try-catch block
        
        // try {
        //     Mail::send('emails.otp-email', ['otp' => $otp, 'name' => $request->name], function ($message) use ($request) {
        //         $message->to($request->email)
        //             ->subject('Email Verification - Library Management System');
        //     });
        // } catch (\Exception $e) {
        //     \Log::error('Failed to send OTP email: ' . $e->getMessage());
        //     return redirect()->route('register')
        //         ->withErrors(['email' => 'Failed to send OTP. Please try again.']);
        // }
        
        \Log::info('OTP would have been sent to: ' . $request->email . ' (Mail disabled)');

        // Redirect to OTP verification page
        return redirect()->route('verify.otp.page', ['email' => $request->email])
            ->with('status', 'OTP has been sent to your email. Please verify to complete registration.');
    }
}


