<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PasswordChangeController extends Controller
{
    /**
     * Show the forced password change page
     */
    public function showChangePassword()
    {
        $user = Auth::user();

        // Check if user is logged in and needs to change password
        if (!$user || !$user->force_password_change) {
            return redirect('/');
        }

        return view('auth.change-password', [
            'user' => $user,
            'tempPassword' => session('temp_password', false)
        ]);
    }

    /**
     * Update the password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Verify user is logged in
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Validate input
        $validated = $request->validate([
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // At least one lowercase, uppercase, and digit
                'different:email' // Different from email
            ]
        ], [
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Passwords do not match',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
            'password.different' => 'Password cannot be the same as your email'
        ]);

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
            'force_password_change' => false // Mark as no longer forced
        ]);

        // Log the activity
        \App\Helpers\ActivityLogger::logActivity(
            'password_changed',
            "User changed their password",
            'auth',
            'user',
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'redirect' => auth()->user()->role === 'admin' ? '/admin/dashboard' : '/'
        ]);
    }
}
