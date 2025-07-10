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

        //cache:catalog-update
        $schedule->command('cache:catalog-update')->everyFiveMinutes();

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

        // Translate products
        $schedule->command('translate:products')->daily()->at('03:00');

        // Transform attributes (merge, split)
        $schedule->command('ak_store:attributes-transform')->everyTenMinutes();

        // 
        $schedule->command('xml:correct-in-stock')->hourly();

        // OpenAI Product Content generation
        $schedule->command('openai:fill-products')->daily()->at('01:00');

        // Create merchants-feed.xml file in uploads dir
        $schedule->command('fetch:merchants-feed')->everyTenMinutes();
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
