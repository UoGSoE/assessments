<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\NotifyNewFeedbacks::class,
        Commands\NotifyOffice::class,
        Commands\AutoSignoffAssessments::class,
        Commands\WlmImport::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('assessments:autosignoff')->dailyAt('03:00');
        $schedule->command('assessments:notifyoffice')->dailyAt('04:00');
        $schedule->command('assessments:notifystaff')->dailyAt('05:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
