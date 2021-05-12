<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
class ClearDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the deleted records and re-indexing the database all tables.';

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
        DB::table('calls')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_activity')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_client_selection')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_events')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_event_comment')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_event_linked_staff')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_event_location')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_event_reminder')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_intake_form')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_intake_form_fields_data')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_master')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_notes')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_practice_area')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_sol_reminder')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_staff')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_stage')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_stage_history')->where("deleted_at","!=",NULL)->delete();
        DB::table('case_update')->where("deleted_at","!=",NULL)->delete();
        DB::table('client_activity')->where("deleted_at","!=",NULL)->delete();
        DB::table('client_group')->where("deleted_at","!=",NULL)->delete();
        DB::table('client_notes')->where("deleted_at","!=",NULL)->delete();
        DB::table('contract_access_permission')->where("deleted_at","!=",NULL)->delete();
        DB::table('contract_user_case')->where("deleted_at","!=",NULL)->delete();
        DB::table('contract_user_permission')->where("deleted_at","!=",NULL)->delete();
        // DB::table('countries')->where("deleted_at","!=",NULL)->delete();
        DB::table('deactivated_user')->where("deleted_at","!=",NULL)->delete();
        DB::table('email_template')->where("deleted_at","!=",NULL)->delete();
        DB::table('event_type')->where("deleted_at","!=",NULL)->delete();
        DB::table('expense_entry')->where("deleted_at","!=",NULL)->delete();
        DB::table('expense_for_invoice')->where("deleted_at","!=",NULL)->delete();
        DB::table('firm')->where("deleted_at","!=",NULL)->delete();
        DB::table('firm_address')->where("deleted_at","!=",NULL)->delete();
        DB::table('firm_event_reminder')->where("deleted_at","!=",NULL)->delete();
        DB::table('firm_sol_reminder')->where("deleted_at","!=",NULL)->delete();
        DB::table('intake_form')->where("deleted_at","!=",NULL)->delete();
        DB::table('intake_form_fields')->where("deleted_at","!=",NULL)->delete();
        DB::table('invoices')->where("deleted_at","!=",NULL)->delete();
        DB::table('invoice_adjustment')->where("deleted_at","!=",NULL)->delete();
        DB::table('invoice_installment')->where("deleted_at","!=",NULL)->delete();
        DB::table('invoice_payment')->where("deleted_at","!=",NULL)->delete();
        DB::table('invoice_payment_plan')->where("deleted_at","!=",NULL)->delete();
        DB::table('lead_additional_info')->where("deleted_at","!=",NULL)->delete();
        DB::table('lead_case_activity_history')->where("deleted_at","!=",NULL)->delete();
        DB::table('lead_notes')->where("deleted_at","!=",NULL)->delete();
        DB::table('lead_notes_activity')->where("deleted_at","!=",NULL)->delete();
        DB::table('lead_notes_activity_history')->where("deleted_at","!=",NULL)->delete();
        DB::table('lead_status')->where("deleted_at","!=",NULL)->delete();
        DB::table('messages')->where("deleted_at","!=",NULL)->delete();
        // DB::table('migrations')->where("deleted_at","!=",NULL)->delete();
        DB::table('not_hire_reasons')->where("deleted_at","!=",NULL)->delete();
        // DB::table('password_resets')->where("deleted_at","!=",NULL)->delete();
        DB::table('plan_history')->where("deleted_at","!=",NULL)->delete();
        DB::table('potential_case_invoice')->where("deleted_at","!=",NULL)->delete();
        DB::table('potential_case_invoice_payment')->where("deleted_at","!=",NULL)->delete();
        DB::table('potential_case_payment')->where("deleted_at","!=",NULL)->delete();
        DB::table('referal_resource')->where("deleted_at","!=",NULL)->delete();
        DB::table('requested_fund')->where("deleted_at","!=",NULL)->delete();
        DB::table('shared_invoice')->where("deleted_at","!=",NULL)->delete();
        DB::table('task')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_activity')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_checklist')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_comment')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_history')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_linked_staff')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_reminder')->where("deleted_at","!=",NULL)->delete();
        DB::table('task_time_entry')->where("deleted_at","!=",NULL)->delete();
        DB::table('temp_user_selection')->where("deleted_at","!=",NULL)->delete();
        DB::table('time_entry_for_invoice')->where("deleted_at","!=",NULL)->delete();
        DB::table('trust_history')->where("deleted_at","!=",NULL)->delete();
        DB::table('users')->where("deleted_at","!=",NULL)->delete();
        DB::table('usersold')->where("deleted_at","!=",NULL)->delete();
        DB::table('users_additional_info')->where("deleted_at","!=",NULL)->delete();
        DB::table('users_detail')->where("deleted_at","!=",NULL)->delete();

        $sqlQuery="OPTIMIZE TABLE `calls`, `case_activity`, `case_client_selection`, `case_events`, `case_event_comment`, `case_event_linked_staff`, `case_event_location`, `case_event_reminder`, `case_intake_form`, `case_intake_form_fields_data`, `case_master`, `case_notes`, `case_practice_area`, `case_sol_reminder`, `case_staff`, `case_stage`, `case_stage_history`, `case_update`, `client_activity`, `client_group`, `client_notes`, `contract_access_permission`, `contract_user_case`, `contract_user_permission`, `countries`, `deactivated_user`, `email_template`, `event_type`, `expense_entry`, `expense_for_invoice`, `firm`, `firm_address`, `firm_event_reminder`, `firm_sol_reminder`, `intake_form`, `intake_form_fields`, `invoices`, `invoice_adjustment`, `invoice_installment`, `invoice_payment`, `invoice_payment_plan`, `lead_additional_info`, `lead_case_activity_history`, `lead_notes`, `lead_notes_activity`, `lead_notes_activity_history`, `lead_status`, `messages`, `migrations`, `not_hire_reasons`, `password_resets`, `plan_history`, `potential_case_invoice`, `potential_case_invoice_payment`, `potential_case_payment`, `referal_resource`, `requested_fund`, `shared_invoice`, `task`, `task_activity`, `task_checklist`, `task_comment`, `task_history`, `task_linked_staff`, `task_reminder`, `task_time_entry`, `temp_user_selection`, `time_entry_for_invoice`, `trust_history`, `users`, `usersold`, `users_additional_info`, `users_detail`";

        $result = DB::select(DB::raw($sqlQuery));

        $this->info('Success!');
    }
}
