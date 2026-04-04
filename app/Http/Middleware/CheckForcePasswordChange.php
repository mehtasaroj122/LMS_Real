<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authenticated user
        $user = Auth::user();

        // Check if user is authenticated and needs to change password
        if ($user && $user->force_password_change) {
            // Allow access to password change routes
            if ($request->routeIs('force-password.change') || $request->routeIs('force-password.update')) {
                return $next($request);
            }

            // Redirect to password change page for all other routes
            return redirect()->route('force-password.change');
        }

        return $next($request);
    }
}
