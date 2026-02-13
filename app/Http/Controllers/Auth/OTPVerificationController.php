<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OTPVerificationMail;
use App\Models\User;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OTPVerificationController extends Controller
{
    /**
     * Show the OTP verification page.
     */
    public function showVerificationPage(Request $request)
    {
        $email = $request->query('email');
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('register')->withErrors(['email' => 'Invalid email format']);
        }

        // Check if registration data exists in session
        $regData = session('registration_data');
        
        if (!$regData || $regData['email'] !== $email) {
            return redirect()->route('register')->withErrors(['email' => 'Registration session expired. Please register again.']);
        }

        // Check if OTP has expired
        if ($regData['otp_expires_at'] < now()->timestamp) {
            session()->forget('registration_data');
            return redirect()->route('register')->withErrors(['email' => 'OTP has expired. Please register again.']);
        }

        return view('auth.verify-otp', ['email' => $email]);
    }

    /**
     * Verify the OTP and complete registration by saving user to database.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp1' => 'required|numeric|max:9',
            'otp2' => 'required|numeric|max:9',
            'otp3' => 'required|numeric|max:9',
            'otp4' => 'required|numeric|max:9',
            'otp5' => 'required|numeric|max:9',
            'otp6' => 'required|numeric|max:9',
        ]);

        // Get registration data from session
        $regData = session('registration_data');

        if (!$regData || $regData['email'] !== $request->email) {
            return back()->withErrors(['email' => 'Registration session expired. Please register again.']);
        }

        // Check if OTP has expired
        if ($regData['otp_expires_at'] < now()->timestamp) {
            session()->forget('registration_data');
            return back()->withErrors(['otp' => 'OTP has expired. Please register again.']);
        }

        // Combine OTP digits
        $enteredOTP = $request->otp1 . $request->otp2 . $request->otp3 . 
                      $request->otp4 . $request->otp5 . $request->otp6;

        // Verify OTP
        if ($regData['otp'] !== $enteredOTP) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again']);
        }

        // OTP is valid - NOW create user in database
        $user = User::create([
            'name' => $regData['name'],
            'email' => $regData['email'],
            'password' => $regData['password'],
            'role' => 'student',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Get or create General department
        $generalDepartment = DB::table('departments')
            ->where('name', 'General')
            ->first();
        
        if (!$generalDepartment) {
            DB::table('departments')->insert([
                'name' => 'General',
                'code' => 'GEN',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $generalDepartment = DB::table('departments')
                ->where('name', 'General')
                ->first();
        }
        
        // Create student record
        Student::create([
            'user_id' => $user->id,
            'department_id' => $generalDepartment->id,
            'roll_no' => 'STU-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'semester' => '1',
        ]);

        // Clear registration session data
        session()->forget('registration_data');

        // Trigger registered event
        event(new Registered($user));

        // Log the user in
        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('status', 'Email verified successfully! Your account has been created. Welcome to Library Management System');
    }

    /**
     * Resend OTP via AJAX.
     */
    public function resendOTP(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Get registration data from session
        $regData = session('registration_data');

        if (!$regData || $regData['email'] !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'Registration session expired. Please register again.'
            ], 404);
        }

        // Check if user is trying to resend too frequently (allow after 1 minute)
        $expiresAt = \Carbon\Carbon::createFromTimestamp($regData['otp_expires_at']);
        $allowResendAt = $expiresAt->copy()->subMinutes(9);
        
        if ($allowResendAt->isFuture()) {
            $waitTime = $allowResendAt->diffInSeconds(now());
            return response()->json([
                'success' => false,
                'message' => "Please wait {$waitTime} seconds before requesting a new OTP"
            ], 429);
        }

        // Generate new OTP
        $newOTP = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Update session with new OTP
        $regData['otp'] = $newOTP;
        $regData['otp_expires_at'] = now()->addMinutes(10)->timestamp;
        session(['registration_data' => $regData]);

        // Send OTP via email
        try {
            Mail::send('emails.otp-email', ['otp' => $newOTP, 'name' => $regData['name']], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('New OTP - Library Management System');
            });

            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent to your email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again'
            ], 500);
        }
    }
}
