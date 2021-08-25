<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\CaseMaster,App\User,App\CasePracticeArea,App\CaseClientSelection,App\CaseStaff,App\AccountActivity,App\ClientNotes,App\CaseStage,App\ClientFullBackup,App\ExpenseEntry,App\TaskTimeEntry,App\Countries,App\InvoicePayment,App\FlatFeeEntry,App\InvoiceAdjustment;
use ZipArchive,File,DB,Validator,Session,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FullBackUpOfApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ClientFullBackup, $zipPath, $zipFileName, $request, $authUser;
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
        Log::info("FullBackUp job handle");
        $authUser = $this->authUser;

        $clientFullBackup = ClientFullBackup::find($this->ClientFullBackup['id']);
        $clientFullBackup->status = 2;
        $clientFullBackup->save();

        $folderPath = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name);

        File::deleteDirectory($folderPath);
        if(!is_dir($folderPath)) {
            File::makeDirectory($folderPath, $mode = 0777, true, true);
        }    
        
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        
        $this->generateAccountActivitiesCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/account_activities.csv");
        $this->generateBackupCasesCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/cases.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/notes.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/expenses.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/time_entries.csv");
        $this->generateClientsCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/clients.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/companies.csv");
        $this->generateDocumentsCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/documents.csv");
        $this->generateEmailsCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/emails.csv");
        $this->generateEventsCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/events.csv");
        $this->generateFlatFeesCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/flat_fees.csv");
        $this->generateInvoiceDiscountsCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/invoice_discounts.csv");
        $this->generateInvoicesCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/invoices.csv");
        $this->generateLawyersCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/lawyers.csv");
        $this->generateLocationsCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/locations.csv");
        $this->generateMessagesCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/messages.csv");
        $this->generateTasksCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/tasks.csv");
        $this->generateTrustActivitiesCSV($this->request, $folderPath, $authUser);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.$authUser->firm_name."/trust_activities.csv");

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
        
    }

    public function generateBackupCasesCSV($request, $folderPath, $authUser){        
        $casesCsvData = $caseNotesCsvData = $casesExpensesCsvData = $casesTimeEntriesCsvData=[];
        $casesHeader="Case/Matter Name|Number|Open Date|Practice Area|Case Description|Case Closed|Closed Date|Lead Attorney|Originating Attorney|SOL Date|Outstanding Balance|LegalCase ID|Contacts|Billing Type|Billing Contact|Flat fee|Case Stage|Case Balance|Conflict Check?|Conflict Check Notes";
        $casesCsvData[]=$casesHeader;

        $caseNotesHeader="Case Name|Created By|Date|Created at|Updated at|Subject|Note";
        $caseNotesCsvData[]=$caseNotesHeader;
        
        $casesExpensesHeader="Date|Activity|Quantity|Cost|Total|Description|User|Case Name|Invoice|Nonbillable|LegalCase ID";
        $casesExpensesCsvData[]=$casesExpensesHeader;

        $casesTimeEntriesHeader="Date|Activity|Time|Rate|Flat rate|Total|Description|User|Case Name|Invoice|Nonbillable|LegalCase ID";
        $casesTimeEntriesCsvData[]=$casesTimeEntriesHeader;

        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");

        if($request['export_cases'] == 0){
            $case = $case->where("case_master.is_entry_done","1");
            if($authUser->parent_user==0){
                $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
                $getChildUsers[]=$authUser->id;
                $case = $case->whereIn("case_master.created_by",$getChildUsers);
            }else{
                $childUSersCase = CaseStaff::select("case_id")->where('user_id',$authUser->id)->get()->pluck('case_id');
                $case = $case->whereIn("case_master.id",$childUSersCase);
            }
            if(!isset($request['include_archived'])){   
                $case = $case->where("case_master.case_close_date", NULL);
            }
        }else{
            if(!isset($request['include_archived'])){
                $case = $case->where("case_master.case_close_date", NULL);
            }
        }
        $case = $case->get();        
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
            ->where("case_client_selection.case_id",$v->id)
            ->get();
            
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
          
            $casesCsvData[]=$v->case_title."|".$v->case_number."|".date("m/d/Y",strtotime($v->case_open_date))."|".$practiceArea."|".$v->case_description."|".(($v->case_close_date != NUll) ? 'true' : 'false')."|".(($v->case_close_date != NUll) ? date("m/d/Y",strtotime($v->case_close_date)) : '')."|".( (!empty($leadAttorney)) ?  $leadAttorney->first_name.' '.$leadAttorney->last_name : '')."|".( (!empty($originatingAttorney)) ?  $originatingAttorney->first_name.' '.$originatingAttorney->last_name : '')."||0|".$v->id."|".$contactList."|".$v->billing_method."|".$is_billing_contact."|".$flatFee."|".$caseStage."|0|".(($v->conflict_check == '0') ? 'false' : 'true')."|".(($v->conflict_check_description == NULL) ? 'No Conflict Check Notes' : $v->conflict_check_description);
            
            // Notes Entry
            $ClientNotesData = ClientNotes::where("case_id",$v->id)->get();
            if(count($ClientNotesData) > 0){
                foreach($ClientNotesData as $key=>$notes)
                $caseNotesCsvData[]=$v->case_title."|".$v->created_by_name."|".date("m/d/Y",strtotime($v->case_open_date))."|".$notes->created_at."|".$notes->updated_at."|".$notes->note_subject."|". strip_tags($notes->notes);
            }
            
            // Expenses Entry
            $ExpenseEntryCase = ExpenseEntry::leftJoin("users","expense_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
            ->select('expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'))
            ->where("expense_entry.case_id",$v->id)->get();

            foreach($ExpenseEntryCase as $kk =>$vv){
                $casesExpensesCsvData[]=date("m/d/Y",strtotime($vv->entry_date))."|".$vv->activity_title."|".(int)$vv->duration."|".(int)$vv->cost."|".((int)$vv->cost * (int)$vv->duration)."|".$vv->description."|".$vv->user_name."|".$v->case_title."|".$vv->invoice_link."|".(($vv->time_entry_billable == 'yes') ? 'false' : 'true')."|".$vv->id;
            }

            // TaskTimeEntry
            $TaskTimeEntryCase = TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
            ->select('task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'))
            ->where("task_time_entry.case_id",$v->id)->get();

            foreach($TaskTimeEntryCase as $kk =>$vv){
                $total = ($vv->rate_type == 'flat') ? (int)$vv->entry_rate : ( str_replace(",","",(int)$vv->entry_rate)  * (int)$vv->duration);

                $casesTimeEntriesCsvData[]=date("m/d/Y",strtotime($vv->entry_date))."|".$vv->activity_title."|".(int)$vv->duration."|".str_replace(",","",(int)$vv->entry_rate)."|".$vv->rate_type."|".$total."|".$vv->description."|".$vv->user_name."|".$v->case_title."|".$vv->invoice_link."|".(($vv->time_entry_billable == 'yes') ? 'false' : 'true')."|".$vv->id;
            }
        }
        // echo json_encode($casesCsvData);
        // exit;
        
        $file_path =  $folderPath.'/cases.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
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
        return true; 
    }
    
    public function generateAccountActivitiesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Date|Related To|Contact|Case Name|Entered By|Notes|Payment Method|Refund|Refunded|Rejection|Rejected|Amount|Trust|Trust payment|Credit|Operating Credit|Total|LegalCase ID";
        $casesCsvData[]=$casesHeader; 

        $FetchQuery = InvoicePayment::leftJoin("users","invoice_payment.created_by","=","users.id")
        ->leftJoin("invoices","invoices.id","=","invoice_payment.invoice_id")
        ->leftJoin("users as invoiceUser","invoiceUser.id","=","invoices.user_id")
        ->leftJoin("case_master","case_master.id","=","invoices.case_id");    
        $FetchQuery = $FetchQuery->select('invoice_payment.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),DB::raw('CONCAT_WS(" ",invoiceUser.first_name,invoiceUser.last_name) as contact_by'),'case_master.case_title');
        $FetchQuery = $FetchQuery->where("invoice_payment.firm_id",$authUser->firm_name);
        $FetchQuery = $FetchQuery->where("entry_type","1");
        $FetchQuery = $FetchQuery->orderBy("id", 'desc');
        $FetchQuery = $FetchQuery->get();

        foreach($FetchQuery as $k=>$v){
            $casesCsvData[] = date('m/d/Y', strtotime($v->payment_date))."|".$v->invoice_id."|".$v->contact_by."|".$v->case_title."|".$v->entered_by."|".$v->notes."|".$v->payment_method."|".(($v->payment_method == 'Refund') ? 'true' : 'false')."|".(($v->payment_method == 'Refunded') ? 'true' : 'false')."|".(($v->payment_method == 'Rejection') ? 'true' : 'false')."|".(($v->payment_method == 'Rejected') ? 'true' : 'false')."|".(($v->amount_refund > 0) ? "-".$v->amount_refund : $v->amount_paid)."|".(($v->payment_from == 'trust') ? 'true' : 'false')."|".(($v->deposit_into == 'Trust Account') ? 'true' : 'false')."|".(($v->payment_from == 'client') ? 'true' : 'false')."|".(($v->deposit_into == 'Operating Account') ? 'true' : 'false')."|".$v->total."|".$v->id;
        }

        $file_path =  $folderPath.'/account_activities.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateClientsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="LegalCase ID|First Name|Middle Name|Last Name|Company|Job Title|Home Street|Home Street 2|Home City|Home State|Home Postal Code|Home Country/Region|Home Fax|Work Phone|Home Phone|Mobile Phone|Contact Group|E-mail Address|Web Page|Outstanding Trust Balance|Login Enabled|Archived|Birthday|Private Notes|License Number|License State|Welcome Message|Non-Trust Credit Balance|:Notes|Cases|Case Link IDs|Created Date";
        $casesCsvData[]=$casesHeader;

        $companyCsvData=[];
        $companyHeader="LegalCase ID|Company|Business Street|Business Street 2|Business City|Business State|Business Postal Code|Business Country/Region|Business Fax|Company Main Phone|E-mail Address|Web Page|Outstanding Trust Balance|Archived|Private Notes|Non-Trust Credit Balance|Contacts|Cases|Case Link IDs|:Notes|Created Date";
        $companyCsvData[]=$companyHeader;

        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->leftJoin('client_group','client_group.id','=','users_additional_info.contact_group_id')->select('users.*','users_additional_info.*','client_group.group_name',"users.id as id");
        $user = $user->whereIn("user_level",["2","4"]);
        if($authUser->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',$authUser->id)->get()->pluck('id');
            $getChildUsers[]=$authUser->id;
            $user = $user->whereIn("parent_user",$getChildUsers);
        }else{
            $user = $user->where("parent_user",$authUser->id); //Logged in user not visible in grid
        }
        if(isset($request['include_archived'])){  
            $user = $user->whereIn("users.user_status",[1,2,4]);
        }else{
            $user = $user->whereIn("users.user_status",[1,2]);
        }
        $user = $user->orderBy("users.id",'asc');
        $user = $user->with("clientCases");
        $userData = $user->get();
        foreach ($userData as $k=>$v){
            $countries = Countries::select('id','name')->get();            
            $countryName = ($v->country !=NULL) ? $countries[$v->country]['name'] : '';

            $contacts = $company = $cases = $casesID = [];            
            foreach($v['clientCases'] as $kk=>$vv){
                $cases[] = $vv->case_title;
                $casesID[] = $vv->id;
            }
            $companyList = User::select("users.first_name","users.id")->whereIn("users.id",explode(",",$v['multiple_compnay_id']))->get();

            foreach($companyList as $kk=>$vv){
                $company[] = $vv->first_name;
            }

            if($v->user_level == '2'){
                $casesCsvData[]=$v->id."|".$v->first_name."|".$v->middle_name."|".$v->last_name."|".implode(PHP_EOL, $company)."|".$v->job_title."|".$v->street."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$countryName."|".$v->fax_number."|".$v->work_phone."|".$v->home_phone."|".$v->mobile_number."|".$v->group_name."|".$v->email."|".$v->website."|".$v->trust_account_balance."|".(($v->last_login != NULL) ? 'true' : 'false')."|".(($v->user_status == 4) ? 'true' : 'false')."|".date("m/d/Y", strtotime($v->dob))."|".$v->notes."|".$v->driver_license."|".$v->license_state."||".$v->trust_account_balance."||".implode(PHP_EOL, $cases)."|".implode(PHP_EOL, $casesID)."|".date("m/d/Y", strtotime($v->created_at));
            }
            if($v->user_level == '3'){
                $companyID = $v['id'];
                $contactlist = DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                    ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                    ->whereRaw("find_in_set($companyID,users_additional_info.multiple_compnay_id)")
                    ->get();

                foreach ($contactlist as $kk => $vv){
                    $contacts[] = $vv->fullname;
                }

                $companyCsvData[]=$v->id."|".$v->first_name."|".$v->street."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$countryName."|".$v->fax_number."|".$v->mobile_number."|".$v->email."|".$v->website."|".$v->trust_account_balance."|".(($v->user_status == 4) ? 'true' : 'false')."|".$v->notes."|".$v->trust_account_balance."|".implode(PHP_EOL, $contacts)."|".implode(PHP_EOL, $cases)."|".implode(PHP_EOL, $casesID)."|".date("m/d/Y", strtotime($v->created_at));
            }
            if($v->user_level == '1' || $v->user_level == '4'){
                
            }

        }

        $file_path =  $folderPath.'/clients.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 

        $company_file_path =  $folderPath.'/companies.csv';  
        $company_file = fopen($company_file_path,"w+");
        foreach ($companyCsvData as $exp_data){
          fputcsv($company_file,explode('|',$exp_data));
        }   
        fclose($company_file);
        return true; 
    }
    
    public function generateDocumentsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Name|Description|Case Name|Archived|Template|LegalCase ID|Tags|Versions|Comments|Shared With";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/documents.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateEmailsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/emails.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateEventsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Name|Description|Start Time|End Time|All day|Case Name|Location|Private?|Archived|LegalCase ID|Comments|Shared With|Event Type";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/events.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateFlatFeesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Date|Amount|Description|Entered By|Case Name|Invoice|Nonbillable|LegalCase ID";
        $casesCsvData[]=$casesHeader;

        $FlatFeeEntry = FlatFeeEntry::leftJoin("users","flat_fee_entry.created_by","=","users.id")->leftJoin("case_master","case_master.id","=","flat_fee_entry.case_id");   
        $FlatFeeEntry = $FlatFeeEntry->select('flat_fee_entry.*', DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),'case_master.case_title');
        $FlatFeeEntry = $FlatFeeEntry->where('flat_fee_entry.created_by',$authUser->id)->where('status','paid')->get();

        foreach ($FlatFeeEntry as $k=>$v){
            $casesCsvData[]= date('m/d/Y', strtotime($v->entry_date))."|".$v->cost."|".$v->description."|".$v->entered_by."|".$v->case_title."|".$v->invoice_link."|".(($v->time_entry_billable == 'no') ? 'true' : 'false')."|".$v->id;
        }

        $file_path =  $folderPath.'/flat_fees.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateInvoiceDiscountsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Item|Applied To|Type|Description|Basis|Percent|Amount|Case Name|Invoice|LegalCase ID";
        $casesCsvData[]=$casesHeader;

        $InvoiceAdjustment=InvoiceAdjustment::leftJoin("users","invoice_adjustment.created_by","=","users.id")->leftJoin("case_master","case_master.id","=","invoice_adjustment.case_id");   
        $InvoiceAdjustment = $InvoiceAdjustment->select('invoice_adjustment.*', DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),'case_master.case_title');
        $InvoiceAdjustment = $InvoiceAdjustment->where('invoice_adjustment.created_by',$authUser->id)->get();

        $AppliedTo=array("flat_fees"=>"Flat Fees","time_entries"=>"Time Entries","expenses"=>"Expenses","balance_forward_total"=>"Balance Forward Total","sub_total"=>"Sub Total");
        $adType=array("percentage"=>"% - Percentage","amount"=>"$ - Amount");      
        foreach ($InvoiceAdjustment as $k=>$v){
            $appliedTo =  ($v->applied_to != '') ? $AppliedTo[$v->applied_to] : "";
            $appliedType = ($v->ad_type != '') ? $adType[$v->ad_type] : "";
            $casesCsvData[]= ucwords($v->item)."|".$appliedTo."|".$appliedType."|".$v->notes."|".$v->basis."|".$v->percentages."|".(($v->item == 'discount') ? '-':'').$v->amount."|".$v->case_title."|".$v->invoice_id."|".$v->id;
        }

        $file_path =  $folderPath.'/invoice_discounts.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateInvoicesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Invoice Number|Case Name|Invoice date|Due date|Billing User|Address|From date|To date|Payment terms|Terms and conditions|Notes|Status|Archived|Created By|Allow online payments|Deposit account|Sent|Draft|Time entry total|Expense total|Flat fee total|Subtotal|Discount total|Write off total|Addition total|Balance forward total|Total amount|Paid|Paid amount|Paid date|Balance due|Forwarded|Forwarded To|Shared With|Has payment plan|Payment Plan|LegalCase ID";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/invoices.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateLawyersCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="First name|Middle Name|Last name|Email|User Type|Street|Street 2|City|State|Postal Code|Country/Region|Home phone|Cell phone|Work phone|Fax phone|LegalCase ID|Archived|Default rate|Cases";
        $casesCsvData[]=$casesHeader;

        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->select('users.*','users_additional_info.*',"users.id as id");
        $user = $user->where("firm_name",$authUser->firm_name); //Logged in user not visible in grid
        $user = $user->whereIn("user_level",['1','3']); //Show firm staff only
        $user = $user->doesntHave("deactivateUserDetail"); // Check user is deactivated or not
        $user = $user->get();

        foreach ($user as $k => $v){
            $countries = Countries::select('id','name')->get();            
            $countryName = ($v->country !=NULL) ? $countries[$v->country]['name'] : '';

            $cases = $casesID = [];            
            
            $case = CaseStaff::leftJoin('case_master','case_master.id',"=","case_staff.case_id")
            ->select('case_master.case_title');
            $case = $case->where("case_staff.user_id",$v->id);
            $case = $case->where("case_master.is_entry_done","1");
            $case = $case->get();

            foreach($case as $kk=>$vv){
                $cases[] = $vv->case_title;
            }

            $casesCsvData[]=$v->first_name."|".$v->middle_name."|".$v->last_name."|".$v->email."|".$v->user_title."|".$v->street."|".$v->address2."|".$v->city."|".$v->state."|".$v->postal_code."|".$countryName."|".$v->home_phone."|".$v->mobile_number."|".$v->work_phone."|".$v->fax_number."|".$v->id."|".(($v->user_status == '4') ? 'true' : 'false')."|".$v->default_rate."|".implode(PHP_EOL, $cases);
        }

        $file_path =  $folderPath.'/lawyers.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateLocationsCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/locations.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateMessagesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/messages.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateTasksCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Name|Notes|Due date|Complete|Priority|Case Name|Assigned By|Completed By|Completed at|Archived|LegalCase ID|Assigned To";
        $casesCsvData[]=$casesHeader;

        $file_path =  $folderPath.'/tasks.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
    
    public function generateTrustActivitiesCSV($request, $folderPath, $authUser){
        $casesCsvData=[];
        $casesHeader="Date|Related To|Contact|Case Name|Entered By|Notes|Payment Method|Refund|Refunded|Rejection|Rejected|Amount|Trust|Trust payment|Credit|Operating Credit|Total|LegalCase ID";
        $casesCsvData[]=$casesHeader;

        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->leftJoin("users as invoiceUser","invoiceUser.id","=","account_activity.user_id")
        ->leftJoin("case_master","case_master.id","=","account_activity.case_id");    
        $FetchQuery = $FetchQuery->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),DB::raw('CONCAT_WS(" ",invoiceUser.first_name,invoiceUser.last_name) as contact_by'),'case_master.case_title', "users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",$authUser->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","trust");
        $FetchQuery = $FetchQuery->get();

        foreach ($FetchQuery as $k => $v){

            $casesCsvData[] = date('m/d/Y', strtotime($v->entry_date))."|".$v->related_to."|".$v->contact_by."|".$v->case_title."|".$v->entered_by."|".$v->notes."|false|false|false|false|false|".(($v->debit_amount > 0) ? "-".$v->debit_amount : $v->credit_amount)."|".(($v->pay_type == 'trust') ? 'true' : 'false')."|false|false|false|".$v->total_amount."|".$v->id;
        }

        $file_path =  $folderPath.'/trust_activities.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
}
