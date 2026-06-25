<?php

namespace App\Providers;

use App\Support\LibraryBranding;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\IssuedBook;
use App\Models\Fine;
use App\Models\BookRequest;
use App\Observers\IssuedBookObserver;
use App\Observers\FineObserver;
use App\Observers\BookRequestObserver;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('registration', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email', 'guest')));

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        Gate::define('access-admin', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('access-staff', function ($user) {
            return $user->role === 'staff';
        });

        Gate::define('access-student', function ($user) {
            return $user->role === 'student';
        });

        // Register model observers for activity logging
        IssuedBook::observe(IssuedBookObserver::class);
        Fine::observe(FineObserver::class);
        BookRequest::observe(BookRequestObserver::class);

        // View::share('libraryBranding', LibraryBranding::resolve());
         // Only share branding for HTTP requests, not for console commands
        if (!$this->app->runningInConsole()) {
            View::share('libraryBranding', LibraryBranding::resolve());
        }
    }
}
