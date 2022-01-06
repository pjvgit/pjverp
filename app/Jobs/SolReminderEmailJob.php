<?php

namespace App\Jobs;

use App\Mail\SolReminderMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class SolReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $item, $staff, $caseDetails, $firmDetail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($item, $staff, $caseDetails, $firmDetail)
    {
        $this->item = $item;
        $this->staff = $staff;
        $this->caseDetails = $caseDetails;
        $this->firmDetail = $firmDetail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo "Mail send";echo PHP_EOL;
        $mailSend = \Mail::to($this->staff->email)->send(new SolReminderMail($this->caseDetails, $this->firmDetail, $this->staff));
        Log::info("SOL reminder Email sent to : ". $this->staff->email. ' for time zone : '.$this->staff->user_timezone );
    }
}
