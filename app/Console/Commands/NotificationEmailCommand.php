<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File;
use App\AllHistory;
use App\Mail\NotificationActivityMail;

class NotificationEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send activity notification on mid-night.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Send activity notification on mid-night.
        // 0 5 * * * cd /var/www/html && php artisan notification:email >> /var/www/html/storage/logs/cron-job.log 2>&1
        // * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1

        Log::info("Activity notification reminder Command Fired : ". date('Y-m-d H:i:s'));

       
        Log::info("Activity notification reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan notification:email