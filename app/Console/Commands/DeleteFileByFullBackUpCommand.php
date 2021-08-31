<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File;
use App\ClientFullBackup;

class DeleteFileByFullBackUpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deletefile:fullbackup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'After seven days your old backups will be deleted to save space.';

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
        // we'll retain your backups for seven days. After seven days your old backups will be deleted to save space. 
        // 0 5 * * * cd /var/www/html && php artisan deletefile:fullbackup >> /var/www/html/storage/logs/cron-job.log 2>&1
        // * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1

        Log::info("FullBackUp Delete File Command Fired : ". date('Y-m-d H:i:s'));
        $backUpData =  ClientFullBackup::whereDate('created_at', '<', \Carbon\Carbon::now()->subDays(7))->get();
        Log::info("Count of Record :". count($backUpData));
        if(count($backUpData) > 0){
            foreach ($backUpData as $k=>$v){
                $folderPath = public_path('backup/'.date("Y-m-d", strtotime($v->created_at)));
                File::deleteDirectory($folderPath);
                ClientFullBackup::where('id', $v->id)->delete();
            }
        }
    }
}
//sudo php artisan deletefile:fullbackup