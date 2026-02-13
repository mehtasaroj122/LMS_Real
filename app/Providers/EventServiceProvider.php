<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Failed;
use App\Listeners\UpdateLastLogin;
use App\Listeners\SendAccountLockedNotification;
use App\Listeners\LogSuspiciousActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            UpdateLastLogin::class,
        ],
        Lockout::class => [
            SendAccountLockedNotification::class,
        ],
        Failed::class => [
            LogSuspiciousActivity::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
