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
        'case_title','case_status','created_at','case_statute_date','case_open_date', 'total_allocated_trust_balance','conflict_check_at'
    ];
    protected $appends = ['token', 'created_new_date', 'createdby', "fee_structure"/* ,"uninvoiced_balance" */,"role_name"/* ,"setup_billing" */];

    /* public function getCaseuserAttribute(){
        $ContractUserCase =  CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","case_staff.lead_attorney")
        ->where('case_id',$this->id)  
        ->get();
        if(!$ContractUserCase->isEmpty()){
            foreach($ContractUserCase as $k=>$v){
                $ContractUserCase[$k]->decode_user_id=base64_encode($ContractUserCase[$k]->id);
            }
        }
        return json_encode($ContractUserCase); 
    } */

    public function getCreatedNewDateAttribute(){
        if($this->created_at!=NULL){
            $userTime = convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y',strtotime($userTime));
        }else{
            return '--';
        }
    }   

    /* public function getCaseUpdateAttribute(){
        $ContractCaseUpdate =  CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","case_update.update_status","case_update.created_at")
        ->where('case_id',$this->id)  
        ->orderBy("case_update.id","DESC")
        ->limit(1)
        ->get();

        if(!$ContractCaseUpdate->isEmpty()){
            $ContractCaseUpdate[0]->newFormateCreatedAt=date('M j, Y h:i A',strtotime(convertUTCToUserTime($ContractCaseUpdate[0]->created_at, auth()->user()->user_timezone ?? 'UTC')));
            // $ContractCaseUpdate[0]->update_status_small=substr($ContractCaseUpdate[0]->update_status,0,40);

        }
        return json_encode($ContractCaseUpdate); 
    } */

    public function getCaseOpenDateAttribute(){
        if(isset($this->attributes['case_open_date'])){
            $userTime = convertUTCToUserDate(date("Y-m-d", strtotime($this->attributes['case_open_date'])), auth()->user()->user_timezone ?? 'UTC');
            return date('Y-m-d', strtotime($userTime));  
        }
    } 

    public function getCaseStatuteDateAttribute(){
        if(isset($this->attributes['case_statute_date'])){
            $userTime = convertUTCToUserDate(date("Y-m-d", strtotime($this->attributes['case_statute_date'])), auth()->user()->user_timezone ?? 'UTC');
            return date('Y-m-d', strtotime($userTime));  
        }
    }

    public function getCreatedbyAttribute(){
        return base64_encode($this->uid);
    }

    /**
     * Do not add this attribute to append array, If required, set append dynamically
     */
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

    /* public function getUpcomingEventAttribute(){
        
        $CommonController= new CommonController();
        $timezone = Auth::User()->user_timezone ?? 'UTC';
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
    } */
    /* public function getUpcomingTasksAttribute(){
      
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
    } */

    /**
     * Do not add this attribute to append array, If required, set append dynamically
     */
    public function getLeadAttorneyAttribute(){
        if(isset($this->case_id)){
            $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as lead_name'),"users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id")->where("case_client_selection.case_id",$this->case_id)->first();
            return $caseCllientSelection->lead_name ?? "";
        }else{
            return "";
        }
     } 

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

    /**
     * Do not add this attribute to append array, If required, set append dynamically
     */
    public function getPracticeAreaFilterAttribute(){
        if(isset($this->case_id) && isset($this->pa) && $this->pa!='-1'){
            $CasePracticeArea = CasePracticeArea::where("id",$this->pa)->where("firm_id",Auth::User()->firm_name)->first();
            if(!empty($CasePracticeArea)){
                return $CasePracticeArea['title'];
            }else{
                return "Not Specified";
            }
        }else{
            return "Not Specified";
        }
     } 

    /**
     * Do not add this attribute to append array, If required, set append dynamically
     */
     public function getPracticeAreaTextAttribute(){
        if(isset($this->practice_area) && $this->practice_area!='-1'){
            $CasePracticeArea = CasePracticeArea::where("id",$this->practice_area)->first();
            return $CasePracticeArea['title'];
        }else{
            return "";
        }
     }

    /**
     * Do not add this attribute to append array, If required, set append dynamically
     */
     public function getUninvoicedBalanceAttribute(){
        if(isset($this->case_id) || isset($this->id)){
            $flatTotalBillable=$flatTotalNonBillable=0;
            $flatFeeData = FlatFeeEntry::select("*")->where('case_id', $this->case_id ?? $this->id)->where("time_entry_billable","yes")->get();
            foreach($flatFeeData as $TK=>$TE){
                if($TE->status == 'paid'){
                    $flatTotalBillable+=str_replace(",","",number_format($TE['cost'], 2));
                }
            }
            $flatFeeTotal = 0;
            if(in_array($this->billing_method,["flat","mixed"])){
                $flatFeeTotal = ($this->billing_amount - $flatTotalBillable);
                $flatFeeTotal = ($flatFeeTotal > 0 ) ?  $flatFeeTotal : 0;
            }
            $timeTotalBillable=$timeTotalNonBillable=0;
            $TimeEntry=TaskTimeEntry::select("*")->where("case_id",$this->case_id ?? $this->id)->where('status','unpaid')->get();
            foreach($TimeEntry as $TK=>$TE){
                if($TE['rate_type']=="flat"){
                    if($TE['time_entry_billable']=="yes"){
                            $timeTotalBillable+=str_replace(",","",number_format($TE['entry_rate'], 2));
                    }else{
                            $timeTotalNonBillable+=str_replace(",","",number_format($TE['entry_rate'], 2));
                    }
                }else{
                    if($TE['time_entry_billable']=="yes"){
                        $timeTotalBillable+=(str_replace(",","",number_format($TE['entry_rate'], 2)) * str_replace(",","",number_format($TE['duration'], 2)));
                    }else{
                        $timeTotalNonBillable+=(str_replace(",","",number_format($TE['entry_rate'], 2)) * str_replace(",","",number_format($TE['duration'], 2)));
                    }
                }
            }
            $expenseTotalBillable=$expenseTotalNonBillable=0;
            $ExpenseEntry=ExpenseEntry::select("*")->where("case_id",$this->case_id ?? $this->id)->where('status','unpaid')->get();
            foreach($ExpenseEntry as $kE=>$vE){
                if($vE['time_entry_billable']=="yes"){
                    $expenseTotalBillable+=(str_replace(",","",number_format($vE->cost, 2)) * str_replace(",","",number_format($vE->duration, 2)));
                }else{
                    $expenseTotalNonBillable+=(str_replace(",","",number_format($vE->cost, 2)) * str_replace(",","",number_format($vE->duration, 2)));
                }
            }

            return "$".number_format(($expenseTotalBillable + $timeTotalBillable + $flatFeeTotal),2);
        }else{
            return "Not Specified";
        }
     } 

    /**
     * Do not add this attribute to append array, if required, set append dynamically
     */
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

    /**
     * Do not add this attribute to append array, if required, set append dynamically
     */
    public function getLastInvoiceAttribute(){
        $lastInvoice =  Invoices::select("invoice_date")
        ->where('case_id',$this->ccid)  
        ->orderBy("invoice_date","DESC")
        ->first();

        if(!empty($lastInvoice)){
            $userTime = convertUTCToUserTime($lastInvoice['invoice_date']. '00:00:00', auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y',strtotime($userTime));
            // return date('M j, Y',strtotime($lastInvoice['invoice_date']));
        }else{
            return "--  ";
        }
    }

    /**
     * Do not add this attribute to append array, if required, set append dynamically
     */
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

    /**
     * Do not add this attribute to append array, if required, set append dynamically
     */
    public function getSetupBillingAttribute(){
        $caseBiller = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
        ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
        ->select("case_client_selection.billing_amount")
        ->where("case_client_selection.case_id",$this->ccid)
        ->where("is_billing_contact","yes")->first();
        if(!empty($caseBiller)){
            return "yes";
        }else{
            return "";
        }
                                
    }

    /**
     * Get the caseOffice that owns the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caseOffice()
    {
        return $this->belongsTo(FirmAddress::class, 'case_office');
    }

    /**
     * Get the caseStaff associated with the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caseStaff()
    {
        return $this->hasOne("App\CaseStaff", 'case_id', 'id');
    }

    /**
     * Get all of the invoices for the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoices::class, 'case_id');
    }

    /**
     * Get the all Staff associated with the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caseStaffAll()
    {
        return $this->hasMany("App\CaseStaff", 'case_id', 'id');
    }

    /**
     * The caseClient that belong to the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function caseBillingClient()
    {
        return $this->belongsToMany(User::class, 'case_client_selection', 'case_id', 'selected_user')->wherePivot("is_billing_contact", "yes");
    }

    /**
     * The caseAllClient that belong to the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function caseAllClient()
    {
        return $this->belongsToMany(User::class, 'case_client_selection', 'case_id', 'selected_user')->orderBy("users.id", "asc")
                ->withPivot('allocated_trust_balance', 'minimum_trust_balance')->whereNull('case_client_selection.deleted_at');
    }

    /**
     * Get the User detials associated with the Case staff 
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    
    public function caseStaffDetails(){
        return $this->belongsToMany(User::class, 'case_staff', 'case_id', 'user_id')->whereNull('case_staff.deleted_at');
    }

    /**
     * Get the firm detials associated with the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caseFirm()
    {
        return $this->hasOne(Firm::class, 'id', 'firm_id');
    }

    /**
     * Get the caseCreatedByUser that owns the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caseCreatedByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The caseAllClient that belong to the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function caseAllClientWithTrashed()
    {
        return $this->belongsToMany(User::class, 'case_client_selection', 'case_id', 'selected_user')->orderBy("users.id", "asc")
                ->withPivot('allocated_trust_balance', 'minimum_trust_balance')->withTrashed();
    }

    /**
     * Get all of the upcomingEvent for the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    /* public function upcomingEvent()
    {
        return $this->hasOne(CaseEvent::class, 'case_id')->where('start_date',">=",date('Y-m-d'))->where('start_time',">=",date('h:i:s'))->orderBy('start_time','ASC');
    } */

    /**
     * Do not add this attribute to append array, if required, set append dynamically
     */
    public function getUpcomingEventAttribute()
    {
        $userTimezone = auth()->user()->user_timezone;
        $eventRecurring = EventRecurring::where('start_date', ">=", date('Y-m-d'))->orderBy('start_date','ASC')
                ->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id()])
                ->whereHas('event', function($query) {
                    $query->where('case_id', $this->id)->where('start_time', ">=", date('h:i:s'))->orderBy('start_time','ASC');
                })->with(['event' => function($query) {
                    $query->where('start_time', ">=", date('h:i:s'))->orderBy('start_time','ASC');
                }])->first();
        $eventArr = [];
        if($eventRecurring) {
            $event = $eventRecurring->event;
            $eventArr = [
                'event_title' => $event->event_title,
                'start_date_time' => ($event->is_full_day == 'no') ? convertUTCToUserTime($eventRecurring->start_date.' '.$event->start_time, $userTimezone) : convertUTCToUserTime($eventRecurring->start_date.' 00:00:00', $userTimezone),
                'is_all_day' => $event->is_full_day,
            ];
        }
        return (object)$eventArr;
    }

    /**
     * Get the upcomingTask associated with the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function upcomingTask()
    {
        return $this->hasOne(Task::class, 'case_id')->where('task_due_on',">=",date('Y-m-d'))->where('task_due_on',"!=",'9999-12-30')
            ->where('status','0')->whereNull('deleted_at')->orderBy('task_due_on','ASC');
    }

    /**
     * Get all of the overdueTasks for the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function overdueTasks()
    {
        return $this->hasMany(Task::class, 'case_id')->where('status','0')->where('task_due_on',"<=",date('Y-m-d'));
    }
    
    /**
     * Get the case status Update associated with the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caseUpdate()
    {
        return $this->hasOne(CaseUpdate::class, 'case_id')->orderBy("case_update.id","DESC");
    }
}
