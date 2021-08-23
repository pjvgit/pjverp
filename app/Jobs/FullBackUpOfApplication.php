<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\CaseMaster,App\User,App\CaseClientSelection;
use ZipArchive,File,DB,Validator,Session,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FullBackUpOfApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ClientFullBackup, $zipPath, $zipFileName, $request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ClientFullBackup, $zipPath, $zipFileName, $request)
    {
        $this->clientFullBackup = $ClientFullBackup;
        $this->zipPath = $zipPath;
        $this->zipFileName = $zipFileName;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("FullBackUp job handle");

        File::deleteDirectory(public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name));
        if(!is_dir(public_path("backup/".date('Y-m-d').'/'.Auth::User()->firm_name))) {
            File::makeDirectory(public_path("backup/".date('Y-m-d').'/'.Auth::User()->firm_name), $mode = 0777, true, true);
        }        
        
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/account_activities.csv");
        $this->generateBackupCasesCSV($this->request);
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/cases.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/clients.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/companies.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/documents.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/emails.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/events.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/expenses.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/flat_fees.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/invoice_discounts.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/invoices.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/lawyers.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/locations.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/messages.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/notes.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/tasks.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/time_entries.csv");
        $CSV[] = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name."/trust_activities.csv");

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
    }

    public function generateBackupCasesCSV($request){
        $casesCsvData=[];
        $casesHeader="Case/Matter Name|Number|Open Date|Practice Area|Case Description|Case Closed|Closed Date|Lead Attorney|Originating Attorney|SOL Date|Outstanding Balance|LegalCase ID|Contacts|Billing Type|Billing Contact|Flat fee|Case Stage|Case Balance|Conflict Check?|Conflict Check Notes";
        $casesCsvData[]=$casesHeader;

        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");

        if($request['export_cases'] == 0){
            $case = $case->where("case_master.is_entry_done","1");
            if(Auth::user()->parent_user==0){
                $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
                $getChildUsers[]=Auth::user()->id;
                $case = $case->whereIn("case_master.created_by",$getChildUsers);
            }else{
                $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
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
          

            $casesCsvData[]=$v->case_title."|".$v->case_number."|".date("m/d/Y",strtotime($v->case_open_date))."|".$practiceArea."|".$v->case_description."|".(($v->case_close_date != NUll) ? 'true' : 'false')."|".(($v->case_close_date != NUll) ? date("m/d/Y",strtotime($v->case_close_date)) : '')."|".( (!empty($leadAttorney)) ?  $leadAttorney->first_name.' '.$leadAttorney->last_name : '')."|".( (!empty($originatingAttorney)) ?  $originatingAttorney->first_name.' '.$originatingAttorney->last_name : '')."||0|".$v->id."|".$contactList."|".$v->billing_method."|".$is_billing_contact."|".$flatFee."|".$caseStage."|0|".(($v->conflict_check == 0) ? 'false' : 'true')."|".(($v->conflict_check_description == NULL) ? 'No Conflict Check Notes' : $v->conflict_check_description);
            
            $ClientNotesData = ClientNotes::where("case_id",$v->id)->get();
            if(count($ClientNotesData) > 0){
                foreach($ClientNotesData as $key=>$notes)
                $caseNotesCsvData[]=$v->case_title."|".$v->created_by_name."|".date("m/d/Y",strtotime($v->case_open_date))."|".$notes->created_at."|".$notes->updated_at."|".$notes->note_subject."|". strip_tags($notes->notes);
            }
        }
        // echo json_encode($casesCsvData);
        // exit;
        
        $folderPath = public_path('backup/'.date('Y-m-d').'/'.Auth::User()->firm_name);
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        $file_path =  $folderPath.'/cases.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }
}
