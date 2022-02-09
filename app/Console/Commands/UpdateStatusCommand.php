<?php

namespace App\Console\Commands;

use App\EmailTemplate;
use App\Invoices;
use App\Jobs\TaskReminderEmailJob;
use App\RequestedFund;
use App\TaskReminder;
use App\Traits\TaskReminderTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStatusCommand extends Command
{
    use TaskReminderTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of request fund and invoices';

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
        // Update request fund status
        $fundResult = RequestedFund::where("status", "!=", "paid")->get();
        if($fundResult) {
            foreach($fundResult as $key => $item) {
                if($item->due_date < date('Y-m-d')) {
                    $status = "overdue";
                } else if($item->amount_due > 0 && $item->amount_paid > 0) {
                    $status = "partial";
                } else {
                    $status = $item->status;
                }
                $item->fill(["status" => $status])->save();
            }
        }

        // Update invoice status
        /* $invoiceResult = Invoices::whereNotIn("status", ["Paid","Forwarded"])->whereDate("due_date", "<", date('Y-m-d'))->get();
        if($invoiceResult) {
            foreach($invoiceResult as $key => $item) {
                $item->fill(["status" => "Overdue"])->save();
            }
        } */
    }
}
