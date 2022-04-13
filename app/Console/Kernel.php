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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('eventhour:reminderemail')->everyMinute();
        $schedule->command('eventminute:reminderemail')->everyMinute();
        // $schedule->command('eventday:reminderemail')->everyThirtyMinutes();
        $schedule->command('eventday:reminderemail')->dailyAt('02:00');
        $schedule->command('taskday:reminderemail')->everyThirtyMinutes();
        // $schedule->command('invoice:reminderemail')->everyThirtyMinutes();
        $schedule->command('invoice:reminderemail')->dailyAt('01:00');
        $schedule->command('deletefile:fullbackup')->dailyAt('05:00');
        // $schedule->command('sol:reminderemail')->everyThirtyMinutes();
        $schedule->command('sol:reminderemail')->dailyAt('00:00');
        $schedule->command('notification:email')->everyThirtyMinutes();    
        $schedule->command('invoiceonline:paymentreminderemail')->dailyAt('02:00');
        $schedule->command('update:status')->dailyAt('02:00');
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
