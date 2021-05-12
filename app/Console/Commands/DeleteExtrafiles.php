<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteExtrafiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:extafile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all pdfs and csv files from public folder.';

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
     * @return mixed
     */
    public function handle()
    {
        $a = glob("public/download/pdf/*"); 
        foreach($a as $file){
            unlink($file);
        }

        $b = glob("public/download_intakeform/pdf/*"); 
        foreach($b as $file){
            unlink($file);
        }

        $c = glob("public/download/*"); 
        foreach($c as $file){
            if(!is_dir($file)){
                unlink($file);
            }
        }
        $this->info('Deleted!');

    }
}
