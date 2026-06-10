<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
        // Pick up new pending orders and assign to nearest rider
        $schedule->command('assign:rider')->everyMinute()->withoutOverlapping();

        // Expire assignment requests older than 4 minutes so the order re-queues
        $schedule->command('assign:order_expire')->everyMinute()->withoutOverlapping();

        // Seed drop-off waypoints for orders that don't have them yet
        $schedule->command('run:order_drop_off_status')->everyFiveMinutes()->withoutOverlapping();

        // Notify admin when a rider accumulates more than 2 negative reviews in a week
        $schedule->command('rider:reviews')->dailyAt('08:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
