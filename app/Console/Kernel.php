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
        $schedule->command('app:update-intern-status')->daily()->withoutOverlapping();
        $schedule->command('app:update-guest-item-status')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('app:update-leave-status')->daily()->withoutOverlapping();

        // Schedule the command to run daily at 5 AM
        $schedule
            ->command('app:pointing')
            ->dailyAt('05:00')
            ->emailOutputOnFailure(
                config('mail.to.address'),
            );

        // Schedule the command to run daily at 5 AM
        $schedule
            ->command('app:salary')
            ->dailyAt('05:00')
            ->emailOutputOnFailure(
                config('mail.to.address'),
            );
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
