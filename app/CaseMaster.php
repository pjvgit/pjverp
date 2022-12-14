<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;

class CaseMaster extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_master";
    public $primaryKey = 'id';

    protected $fillable = [
        'case_title','case_status','created_at'
    ];
    protected $appends = ['payment_plan_active_for_case','last_invoice','token','caseuser','caseupdate','created_new_date','createdby','case_stage_text','upcoming_event','upcoming_tasks','lead_attorney',"fee_structure","practice_area_filter",'practice_area_text',"unpaid_amount","unpaid_balance","role_name"];
    public function getCaseuserAttribute(){
        $ContractUserCase =  CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","case_staff.lead_attorney")
        ->where('case_id',$this->id)  
        ->get();
        if(!$ContractUserCase->isEmpty()){
            foreach($ContractUserCase as $k=>$v){
                $ContractUserCase[$k]->decode_user_id=base64_encode($ContractUserCase[$k]->id);
            }
        }
        return json_encode($ContractUserCase); 
    }

    public function getCreatedNewDateAttribute(){
        return date('M j, Y',strtotime($this->created_at));
    }   
    public function getCaseUpdateAttribute(){
        $ContractCaseUpdate =  CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","case_update.update_status","case_update.created_at")
        ->where('case_id',$this->id)  
        ->orderBy("case_update.id","DESC")
        ->limit(1)
        ->get();

        if(!$ContractCaseUpdate->isEmpty()){
            $ContractCaseUpdate[0]->newFormateCreatedAt=date('M j, Y h:i A',strtotime($ContractCaseUpdate[0]->created_at));
            // $ContractCaseUpdate[0]->update_status_small=substr($ContractCaseUpdate[0]->update_status,0,40);

        }
        return json_encode($ContractCaseUpdate); 
    }

    public function getCreatedbyAttribute(){
        return base64_encode($this->uid);
    }
    public function getCaseStageTextAttribute(){
        $caseStageText =  CaseStage::select('title')
        ->where('id',$this->case_status)  
        ->limit(1)
        ->first();
        if(!empty($caseStageText)){
           return $caseStageText->title;
        }else{
            return "Not Specified";
        } 
    }

    public function getUpcomingEventAttribute(){
        
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        // $currentConvertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d H:i:s'),$timezone);
        $case_events =  CaseEvent::select('*')
        ->where('case_id',$this->id)  
        ->where('start_date',">=",date('Y-m-d'))  
        ->where('start_time',">=",date('h:i:s'))  
        ->orderBy('start_time','ASC')
        ->limit(1)
        ->get();
        
        if(!$case_events->isEmpty()){
            if($case_events[0]->start_time!=NULL){
                $eventDateandTime=$case_events[0]->start_date.' '.$case_events[0]->start_time;
                $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($eventDateandTime)),$timezone);
                $case_events[0]->convertedDate=date('M j, Y',strtotime($convertedDate));
                $case_events[0]->convertedTime=date('h:i A',strtotime($convertedDate));
            }
        }
        return json_encode($case_events); 
    }
    public function getUpcomingTasksAttribute(){
      
        $TaskDatata =DB::table('task')->select('*')
        ->where('case_id',$this->id)  
        ->where('task_due_on',">=",date('Y-m-d'))  
        ->where('task_due_on',"!=",'9999-12-30')  
        ->where('status','0')  
        ->whereNull('deleted_at')  
        ->orderBy('task_due_on','ASC')
        ->limit(1)
        ->get();
        if(!$TaskDatata->isEmpty()){
            $TaskIds=DB::table('task')->select('id')->where("case_id",$this->id)->where('status','0')->where('task_due_on',"<=",date('Y-m-d'))->count();
            $TaskDatata[0]->overdueTaskCounter=$TaskIds;
            $TaskDatata[0]->convertedDate=date('M j, Y',strtotime($TaskDatata[0]->task_due_on));
            return json_encode($TaskDatata); 
        }else{
            $TaskDatata=[];
            return json_encode($TaskDatata); 
        }
    }
    public function getLeadAttorneyAttribute(){
        if(isset($this->case_id)){
            $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as lead_name'),"users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id")->where("case_client_selection.case_id",$this->case_id)->first();
            return $caseCllientSelection->lead_name;
        }else{
            return "";
        }
     } 
    //  public function getFeeStructureAttribute(){
    //     $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.case_id",$this->case_id)->where("case_client_selection.billing_method","!=",NULL)->first();
    //     if(empty($caseCllientSelection)){
    //         return "Not Specified";
    //     }else{
    //         if($caseCllientSelection->billing_method=="flat"){
    //             return "Flat Fee";
    //         }else{
    //             return ucfirst($caseCllientSelection->billing_method);
    //         }
            
    //     }
    // }

    public function getFeeStructureAttribute(){
      
        if($this->billing_method==NULL){
            return "Not Specified";
        }else{
            if($this->billing_method=="flat"){
                return "Flat Fee";
            }else{
                return ucfirst($this->billing_method);
            }
        }
    }
    public function getPracticeAreaFilterAttribute(){
        if(isset($this->case_id) && isset($this->pa) && $this->pa!='-1'){
            $CasePracticeArea = CasePracticeArea::where("id",$this->pa)->first();
            return $CasePracticeArea['title'];
        }else{
            return "Not Specified";
        }
     } 

     public function getPracticeAreaTextAttribute(){
        if(isset($this->practice_area) && $this->practice_area!='-1'){
            $CasePracticeArea = CasePracticeArea::where("id",$this->practice_area)->first();
            return $CasePracticeArea['title'];
        }else{
            return "";
        }
     }
     public function getUnpaidAmountAttribute(){
        if(isset($this->case_id)){
             $ExpenseEntry=ExpenseEntry::select(DB::raw('sum(cost*duration) AS totalExpenseEntry'))->where("case_id",$this->case_id)->where("status","unpaid")->where("time_entry_billable","yes")->first();
            
             $TaskTimeEntryFlat=TaskTimeEntry::select(DB::raw("sum(`entry_rate`) AS totalTimeEntry"))
             ->where("case_id",$this->case_id)
             ->where("status","unpaid")
             ->where("rate_type","flat")
             ->where("time_entry_billable","yes")
             ->first();

             $TaskTimeEntryhr=TaskTimeEntry::select(DB::raw("sum(entry_rate * duration)  AS totalTimeEntry"))
             ->where("case_id",$this->case_id)
             ->where("status","unpaid")
             ->where("rate_type","hr")
             ->where("time_entry_billable","yes")
             ->first();


            //  $TaskTimeEntry=TaskTimeEntry::select(DB::raw('sum(entry_rate*duration) AS totalTimeEntry'))->where("case_id",$this->case_id)->where("status","unpaid")->where("time_entry_billable","yes")->first();

            return "$".number_format(($ExpenseEntry['totalExpenseEntry']+$TaskTimeEntryFlat['totalTimeEntry']+$TaskTimeEntryhr['totalTimeEntry']+$this->billing_amount),2);

            
        }else{
            return "Not Specified";
        }
     } 
     
    //  public function getUnpaidAmountAttribute(){
    //     if(isset($this->case_id)){
    //          $ExpenseEntry=ExpenseEntry::select(DB::raw('sum(cost*duration) AS totalExpenseEntry'))->where("case_id",$this->case_id)->where("status","unpaid")->where("time_entry_billable","yes")->first();
            
    //          $TaskTimeEntry=TaskTimeEntry::select(DB::raw("(CASE WHEN rate_type='flat' THEN entry_rate WHEN rate_type = 'hr' THEN sum(entry_rate * duration) END) AS totalTimeEntry"))->where("case_id",$this->case_id)->where("status","unpaid")->where("time_entry_billable","yes")->first();

    //         //  $TaskTimeEntry=TaskTimeEntry::select(DB::raw('sum(entry_rate*duration) AS totalTimeEntry'))->where("case_id",$this->case_id)->where("status","unpaid")->where("time_entry_billable","yes")->first();

    //         return "$".number_format(($ExpenseEntry['totalExpenseEntry']+$TaskTimeEntry['totalTimeEntry']),2);

            
    //     }else{
    //         return "Not Specified";
    //     }
    //  } 

     public function getUnpaidBalanceAttribute(){
        if(isset($this->ccid)){
            $lastInvoice =  Invoices::select("*")
            ->where('case_id',$this->ccid)    
            ->sum('due_amount');
            return "$".number_format($lastInvoice,2);
            
        }else{
            return "Not Specified";
        }
     } 

     
    public function getTokenAttribute(){
        return substr(sha1(rand()), 0, 15);
    }

    public function getLastInvoiceAttribute(){
        $lastInvoice =  Invoices::select("invoice_date")
        ->where('case_id',$this->ccid)  
        ->orderBy("invoice_date","DESC")
        ->first();

        if(!empty($lastInvoice)){
            return date('M j, Y',strtotime($lastInvoice['invoice_date']));
        }else{
            return "--  ";
        }
    }

    public function getPaymentPlanActiveForCaseAttribute(){
        $lastInvoice =  Invoices::select("*")
        ->where('case_id',$this->ccid)  
        ->where('payment_plan_enabled',"yes")  
        ->first();

        if(!empty($lastInvoice)){
            return "yes";
        }else{
            return "--";
        }
    }

    public function getRoleNameAttribute(){
        if(isset(request()->all()['company_id'])){
            $getData=CaseClientSelection::where("selected_user",request()->all()['company_id'])->where("case_id",$this->id)->first();
            if(!empty($getData)){
                $user_role=$getData['user_role'];
                $client_group=UserRole::find($user_role);
            }
            if(!empty($client_group)){
             return $client_group['role_name'];
            }else{
                return null;
            }
        }else if(isset(request()->all()['user_id'])){
            $getData=CaseClientSelection::where("selected_user",request()->all()['user_id'])->where("case_id",$this->id)->first();
            if(!empty($getData)){
                $user_role=$getData['user_role'];
                $client_group=UserRole::find($user_role);
            }
            if(!empty($client_group)){
            return $client_group['role_name'];
            }else{
                return null;
            }
        }else{
            return null;
        }
        
    } 

    
}
