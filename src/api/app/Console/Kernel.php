<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // CACHE CATALOG CATEGORIES PAGE 1
        $schedule->command('cache:catalog')->everyFiveMinutes();

        // Get product updates FROM Xml-links
        $schedule->command('xml:source')->everyFiveMinutes();

        // Remove product duplications, merge products 
        $schedule->command('db:join-and-remove-duplications')->everyTenMinutes();

        // Clear old backups
        $schedule->command('backup:clean')->daily()->at('04:00');
        
        // Make new backup
        $schedule->command('backup:run')->daily()->at('05:00');

        // Translate attributes
        $schedule->command('translate:attributes')->daily()->at('06:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
