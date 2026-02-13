<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use App\Helpers\ActivityLogger;
use App\Models\Notification;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Check if the error is due to inactive account
            if (isset($e->errors()['email']) && $e->errors()['email'][0] === 'account_inactive') {
                return redirect()->route('account.inactive');
            }
            throw $e;
        }

        $request->session()->regenerate();

        // Log login activity
        $user = Auth::user();
        
        ActivityLogger::logActivity(
            'login',
            $user->name . ' logged in',
            'auth',
            'user',
            $user->id
        );

        // Role-based redirect
        if (Gate::allows('access-admin') || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'staff') {
            return redirect()->route('staff.dashboard');
        } elseif ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        // Default fallback
        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log logout activity before destroying session
        $user = Auth::user();
        if ($user) {
            ActivityLogger::logActivity(
                'logout',
                $user->name . ' logged out',
                'auth',
                'user',
                $user->id
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
