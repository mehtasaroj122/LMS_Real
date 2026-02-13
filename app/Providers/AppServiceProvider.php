<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\IssuedBook;
use App\Models\Fine;
use App\Models\BookRequest;
use App\Observers\IssuedBookObserver;
use App\Observers\FineObserver;
use App\Observers\BookRequestObserver;

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
    }
}
