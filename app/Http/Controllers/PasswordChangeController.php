<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

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
            'tempPassword' => session('temp_password', false),
        ]);
    }

    /**
     * Update the password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
                function ($attribute, $value, $fail) use ($user) {
                    if (is_string($value) && mb_strtolower($value) === mb_strtolower((string) $user->email)) {
                        $fail('Password must be different from your email address.');
                    }
                },
            ],
            'password_confirmation' => [
                'required',
                'same:password',
            ],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password_confirmation.required' => 'Please confirm your new password.',
            'password_confirmation.same' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the highlighted fields.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $validated = $validator->validated();

        $user->update([
            'password' => Hash::make($validated['password']),
            'force_password_change' => false,
        ]);

        \App\Helpers\ActivityLogger::logActivity(
            'password_changed',
            'User changed their password',
            'auth',
            'user',
            $user->id
        );

        $redirect = $user->role === 'admin' ? '/admin/dashboard' : '/';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect)->with('status', 'Password changed successfully.');
    }
}
