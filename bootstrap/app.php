<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register force password change middleware
        $middleware->web(append: [
            \App\Http\Middleware\CheckForcePasswordChange::class,
        ]);

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            
            if ($user) {
                if ($user->role === 'admin') {
                    return route('admin.dashboard');
                } elseif ($user->role === 'staff') {
                    return route('staff.dashboard');
                } elseif ($user->role === 'student') {
                    return route('student.dashboard');
                }
            }
            
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
