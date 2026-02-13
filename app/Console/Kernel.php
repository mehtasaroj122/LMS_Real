<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Prune expired password reset tokens every minute
        $schedule->command('auth:clear-resets')
            ->everyMinute()
            ->name('clear-expired-password-resets')
            ->withoutOverlapping();

        // Run fine calculation every day at 2 AM
        $schedule->command('fines:calculate-overdue')
            ->dailyAt('02:00')
            ->name('calculate-overdue-fines')
            ->withoutOverlapping()
            ->onOneServer();

        // Send overdue book reminders every day at 8 AM
        $schedule->command('notifications:overdue-reminders')
            ->dailyAt('08:00')
            ->name('send-overdue-reminders')
            ->withoutOverlapping()
            ->onOneServer();

        // Send fine payment reminders every week on Monday at 9 AM
        $schedule->command('notifications:fine-reminders')
            ->weeklyOn(1, '09:00')
            ->name('send-fine-reminders')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
