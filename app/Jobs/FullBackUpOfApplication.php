<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\CaseMaster,App\User,App\CasePracticeArea,App\CaseClientSelection,App\CaseStaff,App\Event,App\AccountActivity,App\ClientNotes,App\CaseStage,App\ClientFullBackup,App\ExpenseEntry,App\TaskTimeEntry,App\Countries,App\InvoicePayment,App\FlatFeeEntry,App\InvoiceAdjustment,App\CaseEventComment;
use App\Task,App\CaseTaskLinkedStaff,App\CaseEventLocation;
use ZipArchive,File,DB,Validator,Session,Storage,Image;
use Illuminate\Support\Facades\Input;
use App\DeactivatedUser,App\Invoices;

class FullBackUpOfApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $ClientFullBackup, $zipPath, $zipFileName, $request, $authUser;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ClientFullBackup, $zipPath, $zipFileName, $request, $authUser)
    {        
        $this->ClientFullBackup = $ClientFullBackup;
        $this->zipPath = $zipPath;
        $this->zipFileName = $zipFileName;
        $this->request = $request;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Full Back Up Job started :". date('Y-m-d H:i:s'));
        try {
        $authUser = $this->authUser;
        Log::info("Full BackUp > authUser :". $this->authUser);
        $clientFullBackup = ClientFullBackup::find($this->ClientFullBackup['id']);
        $clientFullBackup->status = 2;
        $clientFullBackup->save();

        $folderPath = public_path('backup/'.convertUTCToUserDate(date("Y-m-d"), $this->authUser->user_timezone)->format('Y-m-d').'/'.$authUser->firm_name);

        File::deleteDirectory($folderPath);
        if(!is_dir($folderPath)) {
            File::makeDirectory($folderPath, $mode = 0777, true, true);
        }    
        
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        
        $this->generateBackupCasesCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/cases.csv";
        $CSV[] = $folderPath."/notes.csv";
        $CSV[] = $folderPath."/expenses.csv";
        $CSV[] = $folderPath."/time_entries.csv";
        $this->generateClientsCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/clients.csv";
        $CSV[] = $folderPath."/companies.csv";
        $this->generateAccountActivitiesCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/account_activities.csv";
        $this->generateDocumentsCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/documents.csv";
        $this->generateEmailsCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/emails.csv";
        $this->generateEventsCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/events.csv";
        $this->generateFlatFeesCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/flat_fees.csv";
        $this->generateInvoiceDiscountsCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/invoice_discounts.csv";
        $this->generateInvoicesCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/invoices.csv";
        $this->generateLawyersCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/lawyers.csv";
        $this->generateLocationsCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/locations.csv";
        $this->generateMessagesCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/messages.csv";
        $this->generateTasksCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/tasks.csv";
        $this->generateTrustActivitiesCSV($this->request, $folderPath, $this->authUser);
        $CSV[] = $folderPath."/trust_activities.csv";

        $zip = new ZipArchive;
        if ($zip->open((public_path($this->zipFileName)), ZipArchive::CREATE) === true) {
            foreach ($CSV as $relativName) {
                $zip->addFile($relativName,basename($relativName));
            }
            $zip->close();
            if ($zip->open(public_path($this->zipFileName)) === true) {
                $Path= $this->zipPath;
            } else {
                $Path="";
            }
        }

        $clientFullBackup->status = 3;
        $clientFullBackup->save();
        
        if(isset($this->request['include_mail'])){
            $data = ['to' => $this->authUser->email];
            $mail = Mail::send('emails.full_backup_email', [], function($message) use ($data){
                $message->to($data['to'])->subject('Your LegalCase backup is finished');
            });
        }

        } catch (\Throwable $e) {
            $clientFullBackup->status = 4;
            $clientFullBackup->save();
            Log::info("Full Back Up Job handle error :".$e->getMessage()." on line number ".$e->getLine());
        }
        Log::info("Full Back Up Job ended :". date('Y-m-d H:i:s'));
    }

    public function generateBackupCasesCSV($request, $folderPath, $authUser){  
        $casesCsvData = $caseNotesCsvData = $casesExpensesCsvData = $casesTimeEntriesCsvData=[];
        $casesHeader="Case/Matter Name|Number|Open Date|Practice Area|Case Description|Case Closed|Closed Date|Lead Attorney|Originating Attorney|SOL Date|Outstanding Balance|LegalCase ID|Contacts|Billing Type|Billing Contact|Flat fee|Case Stage|Case Balance|Conflict Check?|Conflict Check Notes|Archived";
        $casesCsvData[]=$casesHeader;

        $caseNotesHeader="Case Name|Created By|Date|Created at|Updated at|Subject|Note";
        $caseNotesCsvData[]=$caseNotesHeader;
        
        $casesExpensesHeader="Date|Activity|Quantity|Cost|Total|Description|User|Case Name|Invoice|Nonbillable|LegalCase ID";
        $casesExpensesCsvData[]=$casesExpensesHeader;

        $casesTimeEntriesHeader="Date|Activity|Time|Rate|Flat rate|Total|Description|User|Case Name|Invoice|Nonbillable|LegalCase ID";
        $casesTimeEntriesCsvData[]=$casesTimeEntriesHeader;

        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        $case = $case->where("case_master.is_entry_done","1");
        if($request['export_cases'] == 0) {
            $case = $case->whereHas("caseStaffAll", function($query) {
                $query->where('user_id', auth()->id());
            });
        } else {
            $case = $case->where("firm_id", $authUser->firm_name);
        }
        /* if($request['export_cases'] == 1 && $authUser->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $case = $case->whereIn("case_master.created_by",$getChildUsers);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',$authUser->id)->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$childUSersCase);
        } */
        // $case = $case->where("case_master.case_close_date", NULL);
        if(isset($request['include_archived'])){
            $case = $case->withTrashed();
        }
        $case = $case->get();
        
        if(count($case) > 0) {
            foreach($case as $k=>$v){
                $practiceArea = '';
                if($v->practice_area > 0){
                    $practiceAreaList = CasePracticeArea::where("status","1")->where("id",$v->practice_area)->first();  
                    $practiceArea = $practiceAreaList->title;
                }

                $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
                ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
                ->leftJoin('user_role','user_role.id','case_client_selection.user_role')
                ->leftJoin('client_group','client_group.id','users_additional_info.contact_group_id')
                ->select("users.first_name","users.last_name","case_client_selection.is_billing_contact")
                ->where("case_client_selection.case_id",$v->id);
                if(isset($request['include_archived'])){
                    $caseCllientSelection = $caseCllientSelection->withTrashed();
                }
                $caseCllientSelection =$caseCllientSelection->get();
                
                $contactList = '';
                $is_billing_contact = '';
                if(count($caseCllientSelection) > 0){
                    foreach($caseCllientSelection as $key=>$val){
                        if($val->is_billing_contact == 'yes'){
                            $is_billing_contact = $val->first_name.' '.$val->last_name;
                        }
                        if($val->user_level==4){
                            $contactList .= $val->first_name.' '.$val->last_name.'(Attorney)'.PHP_EOL;
                        }else{
                            $contactList .= $val->first_name.' '.$val->last_name.'(Client)'.PHP_EOL;
                        }
                    }
                }
                
                $caseStage = 'Not Specified';
                if($v->case_status > 0){
                    $caseStageList = CaseStage::select("*")->where("status","1")->where("id",$v->case_status)->first();
                    $caseStage = $caseStageList->title ?? 'Not Specified';
                }

                $flatFee = 0;
                if($v->billing_method =='flat' || $v->billing_method =='mixed'){
                    $flatFee = $v->billing_amount;
                }

                $leadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.first_name","users.last_name")->where("case_id",$v->id)->where("lead_attorney","!=",null)->first();
                $originatingAttorney = CaseStaff::join('users','users.id','=','case_staff.originating_attorney')->select("users.first_name","users.last_name")->where("case_id",$v->id)->where("originating_attorney","!=",null)->first();
            
                $timezone = $authUser->user_timezone ?? 'UTC';
                $caseOpenDate = ($v->case_open_date) ? convertUTCToUserDate($v->case_open_date, $timezone)->format('m/d/Y') : '';
                $caseCloseDate = ($v->case_close_date) ? convertUTCToUserDate($v->case_close_date, $timezone)->format('m/d/Y') : '';
                $case_arc=($v->deleted_at==null)?"FALSE":"TRUE";
                $solDate = ($v->case_statute_date) ? convertUTCToUserDate($v->case_statute_date, $timezone)->format('m/d/Y') : '';
                $caseBalance = Invoices::where('case_id', $v->id)->sum('due_amount');
                $casesCsvData[]=$v->case_title."|".$v->case_number."|".$caseOpenDate."|".$practiceArea."|".$v->case_description."|".(($v->case_close_date != NUll) ? 'true' : 'false')."|".$caseCloseDate."|".( (!empty($leadAttorney)) ?  $leadAttorney->first_name.' '.$leadAttorney->last_name : '')."|".( (!empty($originatingAttorney)) ?  $originatingAttorney->first_name.' '.$originatingAttorney->last_name : '')."|".$solDate."|".number_format($v->total_allocated_trust_balance ?? 0, 2)."|".$v->id."|".$contactList."|".$v->billing_method."|".$is_billing_contact."|".$flatFee."|".$caseStage."|".$caseBalance."|".(($v->conflict_check == '0') ? 'false' : 'true')."|".(($v->conflict_check_description == NULL) ? 'No Conflict Check Notes' : $v->conflict_check_description)."|".$case_arc;
                
                // Notes Entry
                $ClientNotesData = ClientNotes::where("case_id",$v->id)->get();
                if(count($ClientNotesData) > 0){
                    foreach($ClientNotesData as $key=>$notes){
                        $createdAtDate = date('m/d/Y',strtotime(convertUTCToUserTime($notes->created_at, $authUser->user_timezone ?? 'UTC')));
                        $updatedAtDate = date('m/d/Y',strtotime(convertUTCToUserTime($notes->updated_at, $authUser->user_timezone ?? 'UTC')));
                        $caseNotesCsvData[]=$v->case_title."|".$v->created_by_name."|".$caseOpenDate."|".$createdAtDate."|".$updatedAtDate."|".$notes->note_subject."|". strip_tags($notes->notes);
                    }                    
                }
                
                // Expenses Entry
                $ExpenseEntryCase = ExpenseEntry::leftJoin("users","expense_entry.user_id","=","users.id")
                ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
                ->select('expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'))
                ->where("expense_entry.case_id",$v->id);
                if(isset($request['include_archived'])){
                    $ExpenseEntryCase = $ExpenseEntryCase->withTrashed();
                }
                $ExpenseEntryCase =$ExpenseEntryCase->with('invoice')->get();

                foreach($ExpenseEntryCase as $kk =>$vv){
                    $entryDate = convertUTCToUserDate($vv->entry_date, $authUser->user_timezone ?? 'UTC')->format('m/d/Y');
                    $casesExpensesCsvData[]=$entryDate."|".$vv->activity_title."|".(int)$vv->duration."|".(int)$vv->cost."|".((int)$vv->cost * (int)$vv->duration)."|".$vv->description."|".$vv->user_name."|".$v->case_title."|".@$vv->invoice->unique_invoice_number."|".(($vv->time_entry_billable == 'yes') ? 'false' : 'true')."|".$vv->id;
                }

                // TaskTimeEntry
                $TaskTimeEntryCase = TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
                ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
                ->select('task_time_entry.*','task_time_entry.duration as tasktimeEntryDuration',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'))
                ->where("task_time_entry.case_id",$v->id);
                if(isset($request['include_archived'])){
                    $TaskTimeEntryCase = $TaskTimeEntryCase->withTrashed();
                }
                $TaskTimeEntryCase =$TaskTimeEntryCase->with('invoice')->get();

                foreach($TaskTimeEntryCase as $kk =>$vv){
                    $entryDate = convertUTCToUserDate($vv->entry_date, $authUser->user_timezone ?? 'UTC')->format('m/d/Y');
                    $total = ($vv->rate_type == 'flat') ? (int)$vv->entry_rate : ( str_replace(",","",(int)$vv->entry_rate)  * (int)$vv->tasktimeEntryDuration);
                    $casesTimeEntriesCsvData[]=$entryDate."|".$vv->activity_title."|".(int)$vv->tasktimeEntryDuration."|".str_replace(",","",(int)$vv->entry_rate)."|".$vv->rate_type."|".$total."|".$vv->description."|".$vv->user_name."|".$v->case_title."|".@$vv->invoice->unique_invoice_number."|".(($vv->time_entry_billable == 'yes') ? 'false' : 'true')."|".$vv->id;
                }
            }
            // echo json_encode($casesCsvData);
            // exit;
            
            $file_path =  $folderPath.'/cases.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 

            $file_path_notes =  $folderPath.'/notes.csv';  
            $file_notes = fopen($file_path_notes,"w+");
            foreach ($caseNotesCsvData as $exp_data_notes){
            fputcsv($file_notes,explode('|',$exp_data_notes));
            }   
            fclose($file_notes); 

            $file_path_expenses =  $folderPath.'/expenses.csv';  
            $file_expenses = fopen($file_path_expenses,"w+");
            foreach ($casesExpensesCsvData as $exp_data){
            fputcsv($file_expenses,explode('|',$exp_data));
            }   
            fclose($file_expenses);          

            $file_path_time_entries =  $folderPath.'/time_entries.csv';  
            $file_time_entries = fopen($file_path_time_entries,"w+");
            foreach ($casesTimeEntriesCsvData as $exp_data){
            fputcsv($file_time_entries,explode('|',$exp_data));
            }   
            fclose($file_time_entries); 
        }
        return true; 
    }
    
    
    public function generateAccountActivitiesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")        
        ->leftJoin("case_master","case_master.id","=","account_activity.case_id")
        ->leftJoin("invoices","invoices.case_id","=","case_master.id")
        ->leftJoin("users as invoiceUser","invoiceUser.id","=","account_activity.user_id");
        $FetchQuery = $FetchQuery->select('account_activity.*','users.id as uid',
            DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),
            DB::raw('CONCAT_WS(" ",invoiceUser.first_name,invoiceUser.last_name) as contact_by_name'),
            'case_master.case_title','invoices.unique_invoice_number');
        if($request['export_cases'] == 1){
            $FetchQuery = $FetchQuery->where("account_activity.firm_id",$authUser->firm_name);
        }else{
            $FetchQuery = $FetchQuery->where("account_activity.created_by",$authUser->id);
        }
        // $FetchQuery = $FetchQuery->where("entry_type","1");
        $FetchQuery = $FetchQuery->where("pay_type","client");
        $FetchQuery = $FetchQuery->groupBy("id")
            // ->with('invoice')
            ->orderBy("id", 'desc');
        if(isset($request['include_archived'])){
            $FetchQuery = $FetchQuery->withTrashed();
        }
        $FetchQuery = $FetchQuery->with('leadAdditionalInfo')->get();

        if(count($FetchQuery) > 0){
            $casesCsvData[]="Date|Related To|Contact|Case Name|Entered By|Notes|Payment Method|Refund|Refunded|Rejection|Rejected|Amount|Trust|Trust payment|Credit|Operating Credit|Total|LegalCase ID";
            foreach($FetchQuery as $k=>$v){
                // $entryDate = convertUTCToUserDate("$added_date", $authUser->user_timezone ?? 'UTC')->format('m/d/Y');
                $amount=0.00;
                if($v->d_amt=="0.00" && $v->c_amt > 0){
                    $amount=$v->c_amt;
                } else if($v->c_amt=="0.00" && $v->d_amt > 0) {
                    $amount="-".$v->d_amt;
                }
                $casesCsvData[] = date('m/d/Y',strtotime($v->added_date))."|".$v->related."|".$v->contact_by_name."|".$v->case_title."|".$v->entered_by."|".$v->payment_note."|".$v->payment_method."|".(($v->payment_method == 'Refund') ? 'true' : 'false')."|".(($v->payment_method == 'Refund') ? 'true' : 'false')."|".(($v->payment_method == 'Rejection') ? 'true' : 'false')."|".(($v->payment_method == 'Rejected') ? 'true' : 'false')."|".$amount."|".(($v->payment_from == 'trust') ? 'true' : 'false')."|".(($v->deposit_into == 'Trust Account') ? 'true' : 'false')."|".(($v->payment_from == 'client') ? 'true' : 'false')."|".(($v->deposit_into == 'Operating Account') ? 'true' : 'false')."|".$v->t_amt."|".$v->id;    
            }

            $file_path =  $folderPath.'/account_activities.csv';
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }
            fclose($file); 
        }
        return true; 
    }

    public function generateClientsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="LegalCase ID|First Name|Middle Name|Last Name|Company|Job Title|Home Street|Home Street 2|Home City|Home State|Home Postal Code|Home Country/Region|Home Fax|Work Phone|Home Phone|Mobile Phone|Contact Group|E-mail Address|Web Page|Outstanding Trust Balance|Login Enabled|Archived|Birthday|Private Notes|License Number|License State|Welcome Message|Non-Trust Credit Balance|:Notes|Cases|Case Link IDs|Created Date";
        $casesCsvData[]=$casesHeader;

        $companyCsvData=[];
        $companyHeader="LegalCase ID|Company|Business Street|Business Street 2|Business City|Business State|Business Postal Code|Business Country/Region|Business Fax|Company Main Phone|E-mail Address|Web Page|Outstanding Trust Balance|Archived|Private Notes|Non-Trust Credit Balance|Contacts|Cases|Case Link IDs|:Notes|Created Date";
        $companyCsvData[]=$companyHeader;

        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
            ->leftJoin('client_group','client_group.id','=','users_additional_info.contact_group_id')
            ->select('users.*','users_additional_info.*','client_group.group_name',"users.id as id");
        $user = $user->whereIn("user_level",["2","4"]);//4=Company
        if($request['export_cases'] == 1 && $authUser->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $user = $user->whereIn("parent_user",$getChildUsers);
        }else{            
            $user = $user->where("users.created_by",$authUser->id); //Logged in user not visible in grid
        }
        if(isset($request['include_archived'])){
            $user = $user->whereIn("users.user_status",[1,2,4]);
            $user = $user->withTrashed();
        }else{
            $user = $user->whereIn("users.user_status",[1,2]);
        }
        $user = $user->orderBy("users.id",'asc');
        $user = $user->with("clientCases");
        $userData = $user->get();
        
        if(count($userData) > 0){
            foreach ($userData as $k=>$v){
                /*$countries = Countries::select('id','name')->get();
                $countryName = ($v->country !=NULL) ? $countries[$v->country]['name'] : '';*/
                $countryName=Countries::where("id","=",$v->country)->first();
                $countryName=($countryName!="")?$countryName->name:"";

                $contacts = $company = $cases = $casesID = [];            
                if(!empty($v->clientCases)){
                    foreach($v->clientCases as $kk=>$vv){
                        $cases[] = $vv->case_title;
                        $casesID[] = $vv->id;
                    }
                }
                $companyList = User::select("users.first_name","users.id")->whereIn("users.id",explode(",",$v['multiple_compnay_id']))->get();

                foreach($companyList as $kk=>$vv){
                    $company[] = $vv->first_name;
                }
                if($v->user_level == '2'){
                    $is_archived="FALSE";
                    if(isset($request['include_archived'])){
                        if(($v->deleted_at!="") || ($v->user_status!="1" && $v->user_status!="2")){
                            $is_archived="TRUE";
                        }
                    }
                    $casesCsvData[]=$v->id."|".$v->first_name."|".$v->middle_name."|".$v->last_name."|".implode(PHP_EOL, $company)."|".$v->job_title."|".$v->street."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$countryName."|".$v->fax_number."|".$v->work_phone."|".$v->home_phone."|".$v->mobile_number."|".$v->group_name."|".$v->email."|".$v->website."|".$v->trust_account_balance."|".(($v->last_login != NULL) ? 'true' : 'false')."|".$is_archived."|".(($v->dob==null || $v->dob=="" || $v->dob=="1970-01-01")?"":date("m/d/Y", strtotime($v->dob)))."|".$v->notes."|".$v->driver_license."|".$v->license_state."||".$v->credit_account_balance."||".implode(PHP_EOL, $cases)."|".implode(PHP_EOL, $casesID)."|".date("m/d/Y", strtotime($v->created_at));
                }
                if($v->user_level == '4'){
                    $companyID = $v['id'];
                    $contactlist = DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                        ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                        ->whereRaw("find_in_set($companyID,users_additional_info.multiple_compnay_id)")
                        ->get();
                    foreach ($contactlist as $kk => $vv){
                        $contacts[] = $vv->fullname;
                    }
                    $companyCsvData[]=$v->id."|".$v->first_name."|".$v->street."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$countryName."|".$v->fax_number."|".$v->mobile_number."|".$v->email."|".$v->website."|".$v->trust_account_balance."|".(($v->user_status == 4 || $v->user_status==3) ? 'true' : 'false')."|".$v->notes."|".$v->trust_account_balance."|".implode(PHP_EOL, $contacts)."|".implode(PHP_EOL, $cases)."|".implode(PHP_EOL, $casesID)."|".$v->notes."|".(($v->created_at!="")?date("m/d/Y", strtotime($v->created_at)):"");
                }
            }

            $file_path =  $folderPath.'/clients.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 

            $company_file_path =  $folderPath.'/companies.csv';  
            $company_file = fopen($company_file_path,"w+");
            foreach ($companyCsvData as $exp_data){
            fputcsv($company_file,explode('|',$exp_data));
            }   
            fclose($company_file);
        }
        return true; 
    }
    
    public function generateDocumentsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Name|Description|Case Name|Archived|Template|LegalCase ID|Tags|Versions|Comments|Shared With";
        $casesCsvData[]="";

        // $file_path =  $folderPath.'/documents.csv';  
        // $file = fopen($file_path,"w+");
        // foreach ($casesCsvData as $exp_data){
        //   fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
        // }   
        // fclose($file); 
        return true; 
    }
    
    public function generateEmailsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="";
        $casesCsvData[]="";

        // $file_path =  $folderPath.'/emails.csv';  
        // $file = fopen($file_path,"w+");
        // foreach ($casesCsvData as $exp_data){
        //   fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
        // }   
        // fclose($file); 
        return true; 
    }
    
    public function generateEventsCSV($request, $folderPath, $authUser){   
        $eventCsvData=[];
        $eventHeader="Name|Description|Start Time|End Time|All day|Case Name|Location|Private?|Archived|LegalCase ID|Comments|Shared With|Event Type";
        $eventCsvData[]=$eventHeader;
        $events=Event::leftJoin("event_recurrings","event_recurrings.event_id","=","events.id")
        ->leftJoin("case_master","case_master.id","=","events.case_id")
        ->leftJoin("case_events","case_events.id","=","events.case_id")
        ->leftJoin("case_event_location","case_event_location.id","=","events.event_location_id")
        ->leftJoin("event_type","event_type.id","=","events.event_type_id")
        ->select("events.id as event_p_id","events.case_id","events.lead_id","events.parent_event_id","events.event_title","events.event_type_id","events.is_full_day",
            "events.recurring_event_end_date","events.event_location_id","events.is_event_private","events.is_recurring","events.event_recurring_type","events.firm_id",
            "events.created_by","events.created_at","events.deleted_at","events.event_description as desc","events.start_date as event_start_date",
            "events.start_time as event_start_time","events.end_date as event_end_date","events.end_time as event_end_time",
            
            "event_recurrings.id as r_id","event_recurrings.event_id","event_recurrings.event_comments","event_recurrings.event_linked_staff","event_recurrings.event_linked_contact_lead",
            DB::raw('CONCAT_WS(", ",case_event_location.location_name,case_event_location.address1,case_event_location.address2,case_event_location.city,case_event_location.state,case_event_location.postal_code) as location'),
            "case_event_location.location_name","case_event_location.address1","case_event_location.address2","case_event_location.city","case_event_location.state","case_event_location.postal_code",

            "event_type.title as event_type",
            "case_master.case_title as case_title"    
        )->with("eventType");
        
        if($request['export_cases'] == 1 && $authUser->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $events = $events->whereIn('events.created_by',$getChildUsers);
        }else{
            $events = $events->where('events.created_by',$authUser->id);
        }

        if(isset($request['include_archived'])){  
            $events = $events->withTrashed();
        }
        $events = $events->groupBy("event_id")->get();
        foreach($events as $event){
            $comments="";
            if($event->event_comments!=null){
                $comment_arr=array();
                foreach(json_decode($event->event_comments) as $comment){
                    if($comment->comment!=""){
                        $comment_arr[]=$comment->comment;
                    }
                }
                $comments=implode(PHP_EOL,$comment_arr);
            }
            $sharedWith="";
            if($event->event_linked_staff!=null){
                $shared_arr=array();
                foreach(json_decode($event->event_linked_staff) as $shared){
                    $user_data=User::where("id","=",$shared->user_id)->first();
                    $shared_arr[]=$user_data->first_name." ".$user_data->last_name;
                }
                $sharedWith=implode(PHP_EOL,$shared_arr);
            }
            $isArchived="FALSE";
            if($event->deleted_at!=null){
                $isArchived="TRUE";
            }
            $eventCsvData[]=$event->event_title."|".$event->desc."|".$event->event_start_date.(($event->event_start_time!=null && $event->event_start_time!="")?" ".$event->event_start_time:"")."|".$event->event_end_date.(($event->event_end_time!=null && $event->event_end_time!="")?" ".$event->event_time:"")."|".(($event->event_recurring_type!=null)?"TRUE":"FALSE")."|".$event->case_title."|".$event->location. "|".(($event->is_event_private=="yes")?"TRUE":"FALSE")."|".$isArchived."|".$event->event_p_id."|".$comments."|".$sharedWith."|".$event->event_type;
        }        
        $file_path =  $folderPath.'/events.csv';  
        $file = fopen($file_path,"w+");
        foreach ($eventCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateFlatFeesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
       
        $FlatFeeEntry = FlatFeeEntry::leftJoin("users","flat_fee_entry.created_by","=","users.id")->leftJoin("case_master","case_master.id","=","flat_fee_entry.case_id");   
        $FlatFeeEntry = $FlatFeeEntry->select('flat_fee_entry.*', DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),'case_master.case_title');
        $FlatFeeEntry = $FlatFeeEntry->where('status','paid');
        
        //If Parent user logged in then show all child case to parent
        if($request['export_cases'] == 1 && $authUser->parent_user=="0"){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $FlatFeeEntry = $FlatFeeEntry->whereIn("flat_fee_entry.created_by",$getChildUsers);
        }else{
            $FlatFeeEntry = $FlatFeeEntry->where("flat_fee_entry.created_by",$authUser->id);
        }

        if(isset($request['include_archived'])){  
            $FlatFeeEntry = $FlatFeeEntry->withTrashed();
        }
        $FlatFeeEntry = $FlatFeeEntry->get();

        if(count($FlatFeeEntry) > 0){
            $casesCsvData[]="Date|Amount|Description|Entered By|Case Name|Invoice|Nonbillable|LegalCase ID";
            
            foreach ($FlatFeeEntry as $k=>$v){
                $entryDate = convertUTCToUserDate($v->entry_date, $authUser->user_timezone ?? 'UTC')->format('m/d/Y');
                $casesCsvData[]= $entryDate."|".$v->cost."|".$v->description."|".$v->entered_by."|".$v->case_title."|".$v->invoice_link."|".(($v->time_entry_billable == 'no') ? 'true' : 'false')."|".$v->id;
            }

            $file_path =  $folderPath.'/flat_fees.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 
        }
        return true; 
    }
    
    public function generateInvoiceDiscountsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];

        $InvoiceAdjustment = InvoiceAdjustment::leftJoin("users","invoice_adjustment.created_by","=","users.id")->leftJoin("case_master","case_master.id","=","invoice_adjustment.case_id");   
        $InvoiceAdjustment = $InvoiceAdjustment->select('invoice_adjustment.*', DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),'case_master.case_title');
        //If Parent user logged in then show all child case to parent
        if($request['export_cases'] == 1 && $authUser->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $InvoiceAdjustment = $InvoiceAdjustment->whereIn('invoice_adjustment.created_by',$getChildUsers);
        }else{
            $InvoiceAdjustment = $InvoiceAdjustment->where('invoice_adjustment.created_by',$authUser->id);
        }
        if(isset($request['include_archived'])){  
            $InvoiceAdjustment = $InvoiceAdjustment->withTrashed();
        }
        $InvoiceAdjustment = $InvoiceAdjustment->get();
        $AppliedTo=array("flat_fees"=>"Flat Fees","time_entries"=>"Time Entries","expenses"=>"Expenses","balance_forward_total"=>"Balance Forward Total","sub_total"=>"Sub Total");
        $adType=array("percentage"=>"% - Percentage","amount"=>"$ - Amount");  
        
        if(count($InvoiceAdjustment) > 0){ 
            $casesCsvData[]="Item|Applied To|Type|Description|Basis|Percent|Amount|Case Name|Invoice|LegalCase ID";
            
            foreach ($InvoiceAdjustment as $k=>$v){
                $appliedTo =  ($v->applied_to != '') ? $AppliedTo[$v->applied_to] : "";
                $appliedType = ($v->ad_type != '') ? $adType[$v->ad_type] : "";
                $invoice_no="";
                if($v->invoice_id!=""){
                    $invoice_no=Invoices::where("id",$v->invoice_id)->first();
                    if($invoice_no!=""){
                        $invoice_no=$invoice_no->unique_invoice_number; 
                    }
                }
                $casesCsvData[]= ucwords($v->item)."|".$appliedTo."|".$appliedType."|".$v->notes."|".$v->basis."|".$v->percentages."|".(($v->item == 'discount') ? '-':'').$v->amount."|".$v->case_title."|".$invoice_no."|".$v->id;
            }

            $file_path =  $folderPath.'/invoice_discounts.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 
        }
        return true; 
    }
    
    public function generateInvoicesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Invoice Number|Case Name|Invoice date|Due date|Billing User|Address|From date|To date|Payment terms|Terms and conditions|Notes|Status|Archived|Created By|Allow online payments|Deposit account|Sent|Draft|Time entry total|Expense total|Flat fee total|Subtotal|Discount total|Write off total|Addition total|Balance forward total|Total amount|Paid|Paid amount|Paid date|Balance due|Forwarded|Forwarded To|Shared With|Has payment plan|Payment Plan|LegalCase ID";
        $casesCsvData[]=$casesHeader;

        // $file_path =  $folderPath.'/invoices.csv';  
        // $file = fopen($file_path,"w+");
        // foreach ($casesCsvData as $exp_data){
        //   fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
        // }   
        // fclose($file); 
        return true; 
    }
    
    public function generateLawyersCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->select('users.*','users_additional_info.*',"users.id as id");
        $user = $user->where("users.firm_name",$authUser->firm_name); //Logged in user not visible in grid
        $user = $user->whereIn("users.user_level",['1','3']); //Show firm staff only
        if(!isset($request['include_archived'])){
            $user = $user->doesntHave("deactivateUserDetail"); // Check user is deactivated or not
        }
        
        $user = $user->get();

        if(count($user) > 0){
            $casesCsvData[]="First name|Middle Name|Last name|Email|User Type|Street|Street 2|City|State|Postal Code|Country/Region|Home phone|Cell phone|Work phone|Fax phone|LegalCase ID|Archived|Default rate|Cases";
            
            foreach ($user as $k => $v){
                // $countries = Countries::select('id','name')->get();            
                // $countryName = ($v->country !=NULL) ? $countries[$v->country]['name'] : '';
                $countryName=Countries::where("id","=",$v->country)->first();
                $countryName=($countryName!="")?$countryName->name:"";
                $cases = $casesID = [];            
                
                $case = CaseStaff::leftJoin('case_master','case_master.id',"=","case_staff.case_id")
                ->select('case_master.case_title');
                $case = $case->where("case_staff.user_id",$v->id);
                $case = $case->where("case_master.is_entry_done","1");
                $case = $case->get();
                
                foreach($case as $kk=>$vv){
                    $cases[] = $vv->case_title;
                }
                $is_archived="FALSE";
                if(isset($request['include_archived'])){
                    $check_deactive = DeactivatedUser::where("user_id",$v->id)->whereNull("deleted_at")->first(); // Check user is deactivated or not
                    if($check_deactive!="" && $v->user_status!="1"){
                        $is_archived="TRUE";
                    }                    
                }
                $casesCsvData[]=$v->first_name."|".$v->middle_name."|".$v->last_name."|".$v->email."|".$v->user_title."|".$v->street."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$countryName."|".$v->home_phone."|".$v->mobile_number."|".$v->work_phone."|".$v->fax_number."|".$v->id."|".$is_archived."|".$v->default_rate."|".implode(PHP_EOL, $cases);
            }

            $file_path =  $folderPath.'/lawyers.csv';
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }
            fclose($file); 
        }
        return true; 
    }
    
    public function generateLocationsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];

        $CaseEventLocation = CaseEventLocation::leftJoin('countries','case_event_location.country',"=","countries.id")
        ->select('case_event_location.*','countries.name');
        //If Parent user logged in then show all child case to parent
        if($request['export_cases'] == 1 && $authUser->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $CaseEventLocation = $CaseEventLocation->whereIn('case_event_location.created_by',$getChildUsers);
        }else{
            $CaseEventLocation = $CaseEventLocation->where('case_event_location.created_by',$authUser->id);
        }

        if(isset($request['include_archived'])){  
            $CaseEventLocation = $CaseEventLocation->withTrashed();
        }
        $CaseEventLocation = $CaseEventLocation->get();

        if(count($CaseEventLocation) > 0){
            $casesCsvData[]="Name|Street|Street2|City|State|Postal Code|Country name|LegalCase ID";
            
            foreach ($CaseEventLocation as $k => $v) {     
                $casesCsvData[]=$v->location_name."|".$v->address1."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$v->name."|".$v->id;
            }
            $file_path =  $folderPath.'/locations.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 
        }
        return true; 
    }
    
    public function generateMessagesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="";
        $casesCsvData[]=$casesHeader;

        // $file_path =  $folderPath.'/messages.csv';  
        // $file = fopen($file_path,"w+");
        // foreach ($casesCsvData as $exp_data){
        //   fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
        // }   
        // fclose($file); 
        return true; 
    }
    
    public function generateTasksCSV($request, $folderPath, $authUser){
        $casesCsvData=[];

        $task = Task::join("users","task.created_by","=","users.id")
        ->leftjoin("case_master","task.case_id","=","case_master.id")
        ->select('task.*','case_master.case_title as case_title',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        
        //If Parent user logged in then show all child case to parent
        if($request['export_cases'] == 1 && $authUser->parent_user=="0"){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $task = $task->whereIn("task.created_by",$getChildUsers);
        }else{
            $task = $task->where("task.created_by",$authUser->id);
        }
        $task = $task->orderBy('task_due_on', 'desc');       
        if(isset($request['include_archived'])){
            $task = $task->withTrashed();
        }
        $task = $task->get();

        if(count($task) > 0){
            $casesCsvData[]="Name|Notes|Due date|Complete|Priority|Case Name|Assigned By|Completed By|Completed at|Archived|LegalCase ID|Assigned To";
            foreach ($task as $k => $v) {
                $tasklinkedstaff =  CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')
                    ->select(DB::raw('group_concat(CONCAT_WS(" ",users.first_name,users.last_name)) as assigned_to_name'))
                    ->where('task_id',$v->id)
                    ->get();
                $assigned_by = User::where("id","=",$v->created_by)->first();
                $completed_by="";
                if($v->task_completed_by!=null){
                    $completed_by_usr=User::where("id","=",$v->task_completed_by)->first();
                    $completed_by=$completed_by_usr->first_name." ".$completed_by_usr->last_name;
                }
                $casesCsvData[]=$v->task_title."|".$v->description."|".(($v->task_due_on=="9999-12-30" || $v->task_due_on=="" || $v->task_due_on=="1970-01-01")?"":date("m/d/Y",strtotime($v->task_due_on)))."|".(($v->status == '0') ? 'false' : 'true')."|".$v->getPriorityTextAttribute()."|".$v->case_title."|".$assigned_by->first_name." ".$assigned_by->last_name."|".$completed_by."|".(($v->task_completed_date == NULL) ? '' : date("m/d/Y",strtotime($v->task_completed_date)))."|".(($v->deleted_at != NULL) ? 'true' : 'false')."|".$v->id."|".($tasklinkedstaff[0]['assigned_to_name'] ?? '');
            }
            $file_path =  $folderPath.'/tasks.csv';
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }
            fclose($file);
        }
        return true;
    }
    
    public function generateTrustActivitiesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")        
        ->leftJoin("case_master","case_master.id","=","account_activity.case_id")
        ->leftJoin("invoices","invoices.case_id","=","case_master.id")
        ->leftJoin("users as invoiceUser","invoiceUser.id","=","account_activity.user_id");
        $FetchQuery = $FetchQuery->select('account_activity.*','users.id as uid',
            DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),
            DB::raw('CONCAT_WS(" ",invoiceUser.first_name,invoiceUser.last_name) as contact_by_name'),
            'case_master.case_title');
        if($request['export_cases'] == 1){
            $FetchQuery = $FetchQuery->where("account_activity.firm_id",$authUser->firm_name);
        }else{
            $FetchQuery = $FetchQuery->where("account_activity.created_by",$authUser->id);
        }
        $FetchQuery = $FetchQuery->where("pay_type","trust");
        $FetchQuery = $FetchQuery->groupBy("id")
            // ->with('invoice')
            ->orderBy("id", 'desc');
        if(isset($request['include_archived'])){
            $FetchQuery = $FetchQuery->withTrashed();
        }
        $FetchQuery = $FetchQuery->with('leadAdditionalInfo')->get();

        if(count($FetchQuery) > 0){
            $casesCsvData[]="Date|Related To|Contact|Case Name|Entered By|Notes|Payment Method|Refund|Refunded|Rejection|Rejected|Amount|Trust|Trust payment|Credit|Operating Credit|Total|LegalCase ID";
            foreach($FetchQuery as $k=>$v){
                // $entryDate = convertUTCToUserDate("$added_date", $authUser->user_timezone ?? 'UTC')->format('m/d/Y');
                $amount=0.00;
                if($v->d_amt=="0.00" && $v->c_amt > 0){
                    $amount=$v->c_amt;
                } else if($v->c_amt=="0.00" && $v->d_amt > 0) {
                    $amount="-".$v->d_amt;
                }
                $casesCsvData[] = date('m/d/Y',strtotime($v->added_date))."|".$v->related."|".$v->contact_by_name."|".$v->case_title."|".$v->entered_by."|".$v->payment_note."|".$v->payment_method."|".(($v->is_refunded=="yes")?"TRUE":"FALSE")."|".(($v->is_refunded=="yes")?"TRUE":"FALSE")."|false|false|".$amount."|".(($v->payment_from == 'trust') ? 'true' : 'false')."|false|false|false|".$v->t_amt."|".$v->id;    
            }
            $file_path =  $folderPath.'/trust_activities.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }
            fclose($file); 
        }
        return true; 
    }
}
