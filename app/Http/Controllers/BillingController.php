<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Firm,App\FirmAddress,App\PotentialCaseInvoice;
use App\FirmEventReminder,App\FirmSolReminder,App\FlatFeeEntry;
use App\TaskTimeEntry,App\CaseMaster,App\TaskActivity,App\CaseTaskLinkedStaff;
use App\ExpenseEntry,App\RequestedFund,App\InvoiceAdjustment;
use App\Invoices,App\CaseClientSelection,App\UsersAdditionalInfo,App\CasePracticeArea,App\InvoicePayment;
use App\TimeEntryForInvoice,App\ExpenseForInvoice,App\SharedInvoice,App\InvoicePaymentPlan,App\InvoiceInstallment;
use App\InvoiceHistory,App\LeadAdditionalInfo,App\CaseStaff,App\InvoiceBatch,App\DepositIntoTrust,App\AllHistory,App\AccountActivity,App\DepositIntoCreditHistory,App\FlatFeeEntryForInvoice,App\TrustHistory;
use App\CaseStage,App\TempUserSelection,App\ClientNotes;
use App\InvoiceApplyTrustCreditFund;
use App\InvoiceCustomizationSetting;
use App\InvoiceOnlinePayment;
use App\InvoiceSetting;
use App\InvoiceTempInfo;
use App\Jobs\InvoiceReminderEmailJob;
use App\Jobs\OnlinePaymentEmailJob;
use App\RequestedFundOnlinePayment;
use App\Traits\CreditAccountTrait;
use App\Traits\InvoiceTrait;
use App\Traits\TrustAccountActivityTrait;
use App\Traits\TrustAccountTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
// use PDF;
use Illuminate\Support\Str;
class BillingController extends BaseController
{
    use CreditAccountTrait, InvoiceTrait, TrustAccountActivityTrait, TrustAccountTrait;
    public function __construct()
    {
    }
    public function dashboard()
    {
        $id=Auth::user()->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $user = User::find($id);
        $country = Countries::get();
        $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
        $FirmEventReminder=FirmEventReminder::where("firm_id",Auth::User()->firm_name)->get();
        $FirmSolReminder=FirmSolReminder::where("firm_id",Auth::User()->firm_name)->get();

        if(!empty($user)){
            return view('billing.dashboard', compact('user','country','firmData','firmAddress','FirmEventReminder','FirmSolReminder'));
        }else{
            return view('pages.404');
        }
    } public function time_entries()
    {
        $id=Auth::user()->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $user = User::find($id);
        if(!empty($user)){
            $country = Countries::get();
            $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
            $FirmEventReminder=FirmEventReminder::where("firm_id",Auth::User()->firm_name)->get();
            $FirmSolReminder=FirmSolReminder::where("firm_id",Auth::User()->firm_name)->get();
        
            $getChildUsers=$this->getParentAndChildUserIds();
          
            $case = TaskTimeEntry::leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
            ->select('task_time_entry.*',"case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number","case_master.id as cid")->whereIn('case_master.created_by',$getChildUsers)->groupBy("case_master.id")->get();


            $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
            $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
            $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
            $getChildUsers=$this->getParentAndChildUserIds();
            $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  
            $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
            $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $getChildUsers[]="0"; //This 0 mean default category need to load in each user
            $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_status","1")->where("user_level","3")->get();
            // return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
            $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
    

            return view('billing.time_entry.time_entries', compact('user','country','firmData','firmAddress','FirmEventReminder','FirmSolReminder','case','CaseMasterClient','CaseMasterCompany',/* 'user_id', */'practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','firmAddress'));
        }else{
            return view('pages.404');
        }
    }

    
    public function loadTimeEntry()
    {   
        // DB::enableQueryLog();

        $columns = array('id','entry_date', 'entry_date', 'activity_title', 'duration', 'case_status','case_unique_number','user_name','user_name','user_name');
        $requestData= $_REQUEST;
        
        $case = TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
        ->leftJoin("invoices","invoices.id","=","task_time_entry.invoice_link")
        ->select('task_time_entry.*',"task_activity.title as activity_title",
        "case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,
        "case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),
        "users.id as uid","invoices.is_lead_invoice");

        if(isset($requestData['c']) && $requestData['c']!=''){
            $case = $case->where("case_master.id",$requestData['c']);
        }
        if(isset($requestData['from']) && $requestData['from'] !='' && isset($requestData['to']) && $requestData['to']!=''){
            $case = $case->whereBetween('task_time_entry.entry_date', [ date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($requestData['from']))))), auth()->user()->user_timezone ?? 'UTC'))), date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($requestData['to']))))), auth()->user()->user_timezone ?? 'UTC')))]);
        }
        if(isset($requestData['from']) && $requestData['from'] !='' && isset($requestData['to']) && $requestData['to'] ==''){
            $case = $case->where('task_time_entry.entry_date', '>=', date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($requestData['from']))))), auth()->user()->user_timezone ?? 'UTC'))));
        }
        if(isset($requestData['from']) && $requestData['from'] =='' && isset($requestData['to']) && $requestData['to'] !=''){
            $case = $case->where('task_time_entry.entry_date', '<=', date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($requestData['to']))))), auth()->user()->user_timezone ?? 'UTC'))));
        }
        
        if(isset($requestData['type']) && $requestData['type']=='own'){
            $case = $case->where("task_time_entry.created_by",Auth::User()->id);
        }
        if(isset($requestData['st']) && $requestData['st']!=''){
            $case = $case->where("task_activity.title",'like', '%' . $requestData['st'] . '%');
            $case = $case->orWhere("task_time_entry.description",'like', '%' . $requestData['st'] . '%');
        }

        if(isset($requestData['i']) && $requestData['i']=='i'){
            $case = $case->where("task_time_entry.invoice_link","!=",NULL);
        }else if(isset($requestData['i']) && $requestData['i']=='o'){
            $case = $case->where("task_time_entry.invoice_link",NULL);
        }

        $totalData=$case->count();
        $totalFiltered = $totalData; 

        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
        $case = $case->get();
        // dd($case);
       
        // echo "<pre>";
        // print_r(\DB::getQueryLog());
        // exit;
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $case 
        );
        echo json_encode($json_data);  
    }
    public function loadTimeEntryPopup(Request $request)
    {  
        $case_id=$request->case_id;
        if(isset($request->note_id)){
            $dataNotes=ClientNotes::find($request->note_id);
            if($dataNotes['client_id']!=NULL){
                $client_id=$dataNotes['client_id'];
            }else if($dataNotes['case_id']!=NULL){
                $case_id=$dataNotes['case_id'];
            }else if($dataNotes['company_id']!=NULL){
                $company_id=$dataNotes['company_id'];
            }
        }
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        
        $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();

        $from=$curDate="";
        if(isset($request->from)){
            $from="timesheet";
            $curDate=$request->curDate;
        }

        // $rateUsers = CaseStaff::select("*")->where("case_id",$case_id)->whereRaw('case_staff.user_id = case_staff.lead_attorney')->first();
        // if(!empty($rateUsers) && $rateUsers['rate_type']=="0"){
        //     $defaultRate = User::select("*")->where("id",$rateUsers['user_id'])->first();
        //     $default_rate=($defaultRate['default_rate'])??0.00;
        // }else{
        //     $default_rate=($rateUsers['rate_amount'])??0.00;
        // }

        $caseStaffRates = [];
        $default_rate=0.00;
        $caseStaffData = CaseStaff::select("*")->where("case_id",$case_id)->get();
        if(count($caseStaffData) > 0){
            foreach($caseStaffData as $k => $v){
                if($v->rate_type == "0"){
                    $defaultRate = DB::table('users')->select("default_rate")->where("id",$v->user_id)->first();
                    $caseStaffRates[$v->user_id] = number_format($defaultRate->default_rate??0 ,2);            
                }else{
                    $caseStaffRates[$v->user_id] = $v->rate_amount;
                }
                if($v->user_id == Auth::User()->id){
                    $default_rate = $v->rate_amount;            
                }
            }
        }
        return view('billing.time_entry.loadTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity',"from","curDate","case_id","default_rate", "caseStaffRates", "request"));     
        exit;    
    } 
    public function loadTimeEntryPopupDontRefresh(Request $request)
    {  
        $case_id=$request->case_id;
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();

        $from=$curDate="";
        if(isset($request->from)){
            $from="timesheet";
            $curDate=$request->curDate;
        }

        $rateUsers = CaseStaff::select("*")->where("case_id",$case_id)->whereRaw('case_staff.user_id = case_staff.lead_attorney')->first();
        if(!empty($rateUsers) && $rateUsers['rate_type']=="0"){
            $defaultRate = User::select("*")->where("id",$rateUsers['user_id'])->first();
            $default_rate=($defaultRate['default_rate'])??0.00;
        }else{
            $default_rate=($rateUsers['rate_amount'])??0.00;
        }

        return view('billing.time_entry.loadTimeEntryPopupDontRefresh',compact('CaseMasterData','loadFirmStaff','TaskActivity',"from","curDate","case_id","default_rate"));     
        exit;    
    } 
    public function loadEditTimeEntryPopup(Request $request)
    {
        $entry_id=$request->entry_id;
        $TaskTimeEntry=TaskTimeEntry::find($entry_id);
        $createdBy=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))->where("id",$TaskTimeEntry['created_by'])->first();
        $updatedBy=[];
        if($TaskTimeEntry['updated_by']!=NULL)
        {  
            $updatedBy=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as updated_by_name'))->where("id",$TaskTimeEntry['updated_by'])->first();
        }
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();

        $from=$curDate="";
        if(isset($request->from)){
          $from="timesheet";
          $curDate=$request->curDate;
        }

        return view('billing.time_entry.loadEditTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','TaskTimeEntry','createdBy','updatedBy','from','curDate'));     
        exit;    
    } 
    public function getRate(Request $request)
    {
        $id = $request->id;
        $rate = TaskActivity::select("flat_fees")->where('id',$id)->where("firm_id",Auth::user()->firm_name)->get();
        return $rate;
    }
  
    public function updatedTimeEntryPopup(Request $request)
    {
      $validator = \Validator::make($request->all(), [
          'case_or_lead' => 'required',
          'staff_user' => 'required',
      ]);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
  
        $TaskTimeEntry = TaskTimeEntry::find($request->entry_id);
        $TaskTimeEntry->case_id =$request->case_or_lead;
        $TaskTimeEntry->user_id =$request->staff_user;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            
            $TaskAvtivity->firm_id=Auth::User()->firm_name;
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->save();
            $TaskTimeEntry->activity_id=$TaskAvtivity->id;
        }else{
            $TaskTimeEntry->activity_id=$request->activity;
        }
        if($request->time_tracking_enabled=="on"){
            $TaskTimeEntry->time_entry_billable="yes";
        }else{
            $TaskTimeEntry->time_entry_billable="no";
        }
        $TaskTimeEntry->description=$request->case_description;
        $TaskTimeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
        $TaskTimeEntry->entry_rate=str_replace(",","",$request->rate_field_id);
        $TaskTimeEntry->rate_type=$request->rate_type_field_id;
        $TaskTimeEntry->duration =$request->duration_field;
        $TaskTimeEntry->updated_at=date('Y-m-d h:i:s'); 
        $TaskTimeEntry->updated_by=Auth::User()->id; 
        $TaskTimeEntry->save();

         //Add time entory history
         $data=[];
         $data['case_id']=$TaskTimeEntry->case_id;
         $data['user_id']=$TaskTimeEntry->user_id;
         $data['activity']='updated a time entry';
         $data['activity_for']=$TaskTimeEntry->activity_id;
         $data['time_entry_id']=$TaskTimeEntry->id;

         $data['type']='time_entry';
         $data['action']='update';
         $CommonController= new CommonController();
         $CommonController->addMultipleHistory($data);

         //Case Activity
         $activity_text = TaskActivity::find($TaskTimeEntry->activity_id);
         $data=[];
         $data['activity_title']='updated a time entry';
         $data['case_id']=$TaskTimeEntry->case_id;
         $data['activity_type']='';
         $data['extra_notes']=$activity_text->title ?? NUll;
         $this->caseActivity($data);
             
         if(isset($request->from)){
            $from="timesheet";
        }else{
            $from="";
        }
        return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id,'from'=>$from]);
        exit;
      }
    } 
  
    public function deleteTimeEntryForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'entry_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=$request->entry_id;
            $TaskTimeEntry=TaskTimeEntry::find($id);
            if(!empty($TaskTimeEntry)){
                if($TaskTimeEntry->rate_type == 'flat'){
                    $totalTime = str_replace(",","",$TaskTimeEntry->duration);
                }else{
                    $totalTime = str_replace(",","",$TaskTimeEntry->entry_rate) * $TaskTimeEntry->duration;
                }              
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $TaskTimeEntry->case_id)->where('invoice_id',$TaskTimeEntry->invoice_link)->get();  
                if(count($InvoiceAdjustment) == 0){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($TaskTimeEntry->invoice_link))->get();
                }
                if(count($InvoiceAdjustment) >0){
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'time_entries'){
                        $invoiceAdjustTotal = $v->basis - $totalTime;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }   
                    }
                }}
            }
            $TaskTimeEntry->delete();
            
            
            //Add time entory history
            $data=[];
            $data['case_id']=$TaskTimeEntry['case_id'];
            $data['user_id']=$TaskTimeEntry['user_id'];
            $data['activity']='deleted a time entry';
            $data['activity_for']=$TaskTimeEntry['activity_id'];
            $data['time_entry_id']=$TaskTimeEntry->id;
            $data['type']='time_entry';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            //Case Activity
            $activity_text = TaskActivity::find($TaskTimeEntry->activity_id);
            $data=[];
            $data['activity_title']='deleted a time entry';
            $data['case_id']=$TaskTimeEntry->case_id;
            $data['activity_type']='';
            $data['extra_notes']=$activity_text->title ?? NUll;
            $this->caseActivity($data);
            
            if(isset($request->from)){
                $from="timesheet";
            }else{
                $from="";
            }

            session(['popup_success' => 'Time Entry has been deleted.']);
            return response()->json(['errors'=>'','id'=>$id,'from'=>$from]);
            exit;  
        }  
    }

    public function printTimeEntry(Request $request)
    {
       
        $columns = array('id','entry_date', 'entry_date', 'activity_title', 'duration', 'case_status','case_unique_number','user_name','user_name','user_name');
        $requestData= $_REQUEST;

        $case = TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
        ->select('task_time_entry.*',"task_activity.title as activity_title","case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,"case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid");

        if(isset($requestData['c']) && $requestData['c']!=''){
            $case = $case->where("case_master.id",$requestData['c']);
        }
        if(isset($requestData['from']) && $requestData['from']!='' && isset($requestData['to']) && $requestData['to']!=''){
            $case = $case->whereBetween("task_time_entry.entry_date",[date('Y-m-d',strtotime($requestData['from'])),date('Y-m-d',strtotime($requestData['to']))]);
        }
        
        if(isset($requestData['type']) && $requestData['type']=='own'){
            $case = $case->where("task_time_entry.user_id",Auth::User()->id);
        }
        if(isset($requestData['st']) && $requestData['st']!=''){
            $case = $case->where("task_activity.title",'like', '%' . $requestData['st'] . '%');
            $case = $case->orWhere("task_time_entry.description",'like', '%' . $requestData['st'] . '%');
        }

        if(isset($requestData['i']) && $requestData['i']=='i'){
            $case = $case->where("task_time_entry.invoice_link","!=",NULL);
        }else if(isset($requestData['i']) && $requestData['i']=='o'){
            $case = $case->where("task_time_entry.invoice_link",NULL);
        }

        // $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);

        $case = $case->get();
        return view('billing.time_entry.viewTimeEntryPdf',compact('case'));
        // $filename="time_entry_".time().'.pdf';
        //  $PDFData=view('billing.time_entry.viewTimeEntryPdf',compact('case'));
        // $pdf = new Pdf;
        // // $pdf->setOptions(['javascript-delay' => 5000]);
        // if($_SERVER['SERVER_NAME']=='localhost'){
        //     $pdf->binary = WKHTMLTOPDF_PATH;
        // }
        // $pdf->addPage($PDFData);
        // // $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->saveAs(public_path("download/pdf/".$filename));
        // $path = public_path("download/pdf/".$filename);
        // return response()->download($path);
        // exit;
        // $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        // return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
        // exit;
    }
    /******************************************** EXPENSES******************************/
    public function expenses()
    {
        $id=Auth::user()->id;
         $user = User::find($id);
        if(!empty($user)){
            $getChildUsers=$this->getParentAndChildUserIds();

            $case = ExpenseEntry::leftJoin("case_master","case_master.id","=","expense_entry.case_id")
            ->select('expense_entry.*',"case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number","case_master.id as cid")->whereIn('case_master.created_by',$getChildUsers)->groupBy("case_master.id")->get();


            $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
            $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
            $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
            $getChildUsers=$this->getParentAndChildUserIds();
            $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  
            $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
            $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $getChildUsers[]="0"; //This 0 mean default category need to load in each user
            $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_status","1")->where("user_level","3")->get();
            // return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
            $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();

            return view('billing.expenses.expenses_entries', compact('user','case','CaseMasterClient','CaseMasterCompany',/* 'user_id', */'practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','firmAddress'));
        }else{
            return view('pages.404');
        }
    }
    public function loadExpensesEntry()
    {   
        $columns = array('id', 'entry_date', 'activity_title', 'duration', 'cost','id','id','id','user_name','id');
        $requestData= $_REQUEST;
        
        $case = ExpenseEntry::leftJoin("users","expense_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","expense_entry.case_id")
        ->select('expense_entry.*',"task_activity.title as activity_title","case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,"case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid");

        if(isset($requestData['c']) && $requestData['c']!=''){
            $case = $case->where("case_master.id",$requestData['c']);
        }
        if(isset($requestData['from']) && $requestData['from']!='' && isset($requestData['to']) && $requestData['to']!=''){
            $case = $case->whereBetween("expense_entry.entry_date",[date('Y-m-d',strtotime($requestData['from'])),date('Y-m-d',strtotime($requestData['to']))]);
        }
        
        if(isset($requestData['type']) && $requestData['type']!=''){
            if($requestData['type']=="own"){
                $case = $case->where("expense_entry.user_id",Auth::User()->id);
            }
        }
        if(isset($requestData['i']) && $requestData['i']=='i'){
            $case = $case->where("expense_entry.invoice_link","!=",NULL);
        }else if(isset($requestData['i']) && $requestData['i']=='o'){
            $case = $case->where("expense_entry.invoice_link",NULL);
        }

        $totalData=$case->count();
        $totalFiltered = $totalData; 

        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
        $case = $case->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $case 
        );
        echo json_encode($json_data);  
    }
    public function loadExpenseEntryPopup(Request $request)
    {   
        $case_id=$request->case_id;
        $getChildUsers=$this->getParentAndChildUserIds();
        // $CaseMasterData = CaseMaster::whereIn('created_by',$getChildUsers)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();


        
        return view('billing.expenses.loadExpenseEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','case_id'));     
        exit;    
    }
    public function saveExpenseEntryPopup(Request $request)
    {
      $validator = \Validator::make($request->all(), [
          'case_or_lead' => 'required',
          'staff_user' => 'required',
      ],['case_or_lead.required'=>'Case can\'t be blank',
      'staff_user.required'=>'User can\'t be blank']);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
        $ExpenseEntry = new ExpenseEntry;
        $ExpenseEntry->case_id =$request->case_or_lead;
        $ExpenseEntry->user_id =$request->staff_user;
        $ExpenseEntry->firm_id = Auth::User()->firm_name;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            
            $TaskAvtivity->firm_id=Auth::User()->firm_name;
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->save();
            $ExpenseEntry->activity_id=$TaskAvtivity->id;
        }else{
            $ExpenseEntry->activity_id=$request->activity;
        }
        if($request->time_tracking_enabled=="on"){
            $ExpenseEntry->time_entry_billable="yes";
        }else{
            $ExpenseEntry->time_entry_billable="no";
        }
        $ExpenseEntry->description=$request->case_description;
        $ExpenseEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
        $ExpenseEntry->cost=str_replace(",","",$request->rate_field_id);
        $ExpenseEntry->duration =$request->duration_field;
        $ExpenseEntry->created_at=date('Y-m-d h:i:s'); 
        $ExpenseEntry->created_by=Auth::User()->id; 
        $ExpenseEntry->save();


        //Add expense history
        $data=[];
        $data['case_id']=$ExpenseEntry->case_id;
        $data['user_id']=$ExpenseEntry->user_id;
        $data['activity']='added an expense';
        $data['activity_for']=$ExpenseEntry->activity_id;
        $data['expense_id']=$ExpenseEntry->id;
        $data['type']='expenses';
        $data['action']='add';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        //Case Activity
        $activity_text = TaskActivity::find($ExpenseEntry->activity_id);
        $data=[];
        $data['activity_title']='added an expense';
        $data['case_id']=$ExpenseEntry->case_id;
        $data['activity_type']='';
        $data['extra_notes']=$activity_text->title ?? NUll;
        $this->caseActivity($data);

        return response()->json(['errors'=>'','id'=>$ExpenseEntry->id]);
        exit;
      }
    } 
    public function saveExpenseBulkEntryPopup(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'case_or_lead' => 'required',
            'staff_user' => 'required',
        ],['case_or_lead.required'=>'Case can\'t be blank',
        'staff_user.required'=>'User can\'t be blank']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $lastKey = key(array_slice($request->case_or_lead, -1, 1, true));
            for($i=1;$i<=$lastKey;$i++){
                if(isset($request->case_or_lead[$i])){
                    if($request->cost[$i] != ''){
                        $ExpenseEntry = new ExpenseEntry; 
                        $ExpenseEntry->case_id =$request->case_or_lead[$i];
                        $ExpenseEntry->user_id =$request->staff_user;
                        $ExpenseEntry->firm_id = Auth::User()->firm_name;
                        $ExpenseEntry->activity_id=$request->activity[$i];
                        if(isset($request->billable[$i]) && $request->billable[$i]=="on"){
                            $ExpenseEntry->time_entry_billable="yes";
                        }else{
                            $ExpenseEntry->time_entry_billable="no";
                        }
                        $ExpenseEntry->description=$request->description[$i];
                        $ExpenseEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
                        $ExpenseEntry->cost=$request->cost[$i];
                        $ExpenseEntry->duration =$request->duration[$i];
                        $ExpenseEntry->created_at=date('Y-m-d h:i:s'); 
                        $ExpenseEntry->created_by=Auth::User()->id; 
                        $ExpenseEntry->save();

                        //Add expense history
                        $data=[];
                        $data['case_id']=$ExpenseEntry->case_id;
                        $data['user_id']=$ExpenseEntry->user_id;
                        $data['activity']='added an expense';
                        $data['activity_for']=$ExpenseEntry->activity_id;
                        $data['expense_id']=$ExpenseEntry->id;
                        $data['type']='expenses';
                        $data['action']='add';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                        //Case Activity
                        $activity_text = TaskActivity::find($ExpenseEntry->activity_id);
                        $data=[];
                        $data['activity_title']='added an expense';
                        $data['case_id']=$ExpenseEntry->case_id;
                        $data['activity_type']='';
                        $data['extra_notes']=$activity_text->title ?? NUll;
                        $this->caseActivity($data);
                    }
                }
            }
            
            return response()->json(['errors'=>'','id'=>$ExpenseEntry->id]);
        exit;
        }
    } 
    public function loadEditExpenseEntryPopup(Request $request)
    {
        $entry_id=$request->entry_id;
        $TaskTimeEntry=ExpenseEntry::find($entry_id);
        $createdBy=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))->where("id",$TaskTimeEntry['created_by'])->first();
        $updatedBy=[];
        if($TaskTimeEntry['updated_by']!=NULL){
            $updatedBy=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as updated_by_name'))->where("id",$TaskTimeEntry['updated_by'])->first();
        }
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
        return view('billing.expenses.loadEditExpenseEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','TaskTimeEntry','createdBy','updatedBy'));     
        exit;    
    } 
    public function deleteExpenseEntryForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'entry_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=$request->entry_id;
            $ExpenseEntry=ExpenseEntry::find($id);
            if(!empty($ExpenseEntry)){                
                $totalTime = str_replace(",","",$ExpenseEntry->cost) * str_replace(",","",$ExpenseEntry->duration);
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $ExpenseEntry->case_id)->where('invoice_id',$ExpenseEntry->invoice_link)->get();  
                if(count($InvoiceAdjustment) == 0){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($ExpenseEntry->invoice_link))->get();
                }
                if(count($InvoiceAdjustment) >0){
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'expenses'){
                        $invoiceAdjustTotal = $v->basis - $totalTime;                        
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }}
            }
            
            //Add Expense history
            $data=[];
            $data['case_id']=$ExpenseEntry['case_id'];
            $data['user_id']=$ExpenseEntry['user_id'];
            $data['activity']='deleted an expense';
            $data['activity_for']=$ExpenseEntry['activity_id'];
            
            $data['type']='expenses';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            //Case Activity
            $activity_text = TaskActivity::find($ExpenseEntry->activity_id);
            $data=[];
            $data['activity_title']='deleted an expense';
            $data['case_id']=$ExpenseEntry->case_id;
            $data['activity_type']='';
            $data['extra_notes']=$activity_text->title ?? NUll;
            $this->caseActivity($data);


            $ExpenseEntry->delete();
            session(['popup_success' => 'Expense has been deleted.']);
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function deleteAdustmentEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'adjustment_entry_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=$request->adjustment_entry_id;
            $InvoiceAdjustment=InvoiceAdjustment::find($id);
            InvoiceAdjustment::where("id", $id)->delete();
            session(['popup_success' => 'Adjustment has been deleted.']);
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function deleteBulkExpenseEntryForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'entry_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->entry_id));
            foreach($data as $k=>$v){
                $ExpenseEntry=ExpenseEntry::find($v);

                //Add Expense history
                $data=[];
                $data['case_id']=$ExpenseEntry['case_id'];
                $data['user_id']=$ExpenseEntry['user_id'];
                $data['activity']='deleted an expense ';
                $data['activity_for']=$ExpenseEntry['activity_id'];
                $data['type']='expenses';
                $data['action']='delete';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                //Case Activity
                $activity_text = TaskActivity::find($ExpenseEntry->activity_id);
                $data=[];
                $data['activity_title']='deleted an expense';
                $data['case_id']=$ExpenseEntry->case_id;
                $data['activity_type']='';
                $data['extra_notes']=$activity_text->title ?? NUll;
                $this->caseActivity($data);
                

                ExpenseEntry::where('id',$v)->delete();
            }
            session(['popup_success' => 'Selected expenses has been deleted.']);
            return response()->json(['errors'=>'']);
            exit;  
        }  
    }
    public function updateEditExpenseEntry(Request $request)
    {
      $validator = \Validator::make($request->all(), [
          'case_or_lead' => 'required',
          'staff_user' => 'required',
      ],['case_or_lead.required'=>'Case can\'t be blank',
      'staff_user.required'=>'User can\'t be blank']);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
        $ExpenseEntry = ExpenseEntry::find($request->entry_id);
        $ExpenseEntry->case_id =$request->case_or_lead;
        $ExpenseEntry->user_id =$request->staff_user;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            
            $TaskAvtivity->firm_id=Auth::User()->firm_name;
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->save();
            $ExpenseEntry->activity_id=$TaskAvtivity->id;
        }else{
            $ExpenseEntry->activity_id=$request->activity;
        }
        if($request->time_tracking_enabled=="on"){
            $ExpenseEntry->time_entry_billable="yes";
        }else{
            $ExpenseEntry->time_entry_billable="no";
        }
        $ExpenseEntry->description=$request->case_description;
        $ExpenseEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
        $ExpenseEntry->cost=str_replace(",","",$request->rate_field_id);
        $ExpenseEntry->duration =$request->duration_field;
        $ExpenseEntry->updated_at=date('Y-m-d h:i:s'); 
        $ExpenseEntry->updated_by=Auth::User()->id; 
        $ExpenseEntry->save();

         //Add Expense history
         $data=[];
         $data['case_id']=$ExpenseEntry->case_id;
         $data['user_id']=$ExpenseEntry->user_id;
         $data['activity']='updated an expense ';
         $data['activity_for']=$ExpenseEntry->activity_id;
         $data['expense_id']=$ExpenseEntry->id;
         $data['type']='expenses';
         $data['action']='update';
         $CommonController= new CommonController();
         $CommonController->addMultipleHistory($data);

        //Case Activity
        $activity_text = TaskActivity::find($ExpenseEntry->activity_id);
        $data=[];
        $data['activity_title']='updated an expense';
        $data['case_id']=$ExpenseEntry->case_id;
        $data['activity_type']='';
        $data['extra_notes']=$activity_text->title ?? NUll;
        $this->caseActivity($data);

        return response()->json(['errors'=>'','id'=>$ExpenseEntry->id]);
        exit;
      }
    }
    public function bulkAssignCase(Request $request)
    {
        $getChildUsers=$this->getParentAndChildUserIds();
        $CaseMasterData = CaseMaster::whereIn('created_by',$getChildUsers)->where('is_entry_done',"1")->get();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        return view('billing.expenses.bulkAssignCase',compact('CaseMasterData'));     
        exit;    
    } 
    public function saveBulkAssignCase(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'entry_id' => 'required|json',
            'case_id' => 'required|numeric',
            
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->entry_id));
            foreach($data as $k=>$v){
                $ExpenseEntry=ExpenseEntry::find($v);
                $ExpenseEntry->case_id=$request->case_id;
                $ExpenseEntry->save();
            }
            session(['popup_success' => 'Expenses Reassigned Successfully']);
            return response()->json(['errors'=>'']);
            exit;  
        }  
    }
    public function bulkAssignUser(Request $request)
    {

       $loadFirmStaff = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','ASC')->get();
        return view('billing.expenses.bulkAssignUser',compact('loadFirmStaff'));     
        exit;    
    } 
    public function saveBulkAssignUser(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'entry_id' => 'required|json',
            'staff_id' => 'required|numeric',
            
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->entry_id));
            foreach($data as $k=>$v){
                $ExpenseEntry=ExpenseEntry::find($v);
                $ExpenseEntry->user_id=$request->staff_id;
                $ExpenseEntry->save();
            }
            session(['popup_success' => 'User Reassigned Successfully']);
            return response()->json(['errors'=>'']);
            exit;  
        }  
    }
    public function printExpenseEntry(Request $request)
    {
       
        $columns = array('id', 'entry_date', 'activity_title', 'duration', 'cost','id','id','id','user_name','id',);
        $requestData= $_REQUEST;
        
        $case = ExpenseEntry::leftJoin("users","expense_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","expense_entry.case_id")
        ->select('expense_entry.*',"task_activity.title as activity_title","case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,"case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid");

        if(isset($requestData['c']) && $requestData['c']!=''){
            $case = $case->where("case_master.id",$requestData['c']);
        }
        if(isset($requestData['from']) && $requestData['from']!='' && isset($requestData['to']) && $requestData['to']!=''){
            $case = $case->whereBetween("expense_entry.entry_date",[date('Y-m-d',strtotime($requestData['from'])),date('Y-m-d',strtotime($requestData['to']))]);
        }
        
        if(isset($requestData['type']) && $requestData['type']!=''){
            $case = $case->where("expense_entry.created_by",Auth::User()->id);
        }
        if(isset($requestData['i']) && $requestData['i']=='i'){
            $case = $case->where("expense_entry.invoice_link","!=",NULL);
        }else if(isset($requestData['i']) && $requestData['i']=='o'){
            $case = $case->where("expense_entry.invoice_link",NULL);
        }

        // $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);

        $case = $case->get();
        return view('billing.expenses.viewExpenseEntryPdf',compact('case'));

        // $filename="expenses_entry_".time().'.pdf';
        //  $PDFData=view('billing.expenses.viewExpenseEntryPdf',compact('case'));
        // $pdf = new Pdf;
        // // $pdf->setOptions(['javascript-delay' => 5000]);
        // if($_SERVER['SERVER_NAME']=='localhost'){
        //     $pdf->binary = WKHTMLTOPDF_PATH;
        // }
        // $pdf->addPage($PDFData);
        // // $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->saveAs(public_path("download/pdf/".$filename));
        // $path = public_path("download/pdf/".$filename);
        // return response()->download($path);
        // exit;
        // $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        // return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
        // exit;
    }


     /******************************************** Requested Fund******************************/
     public function retainer_requests()
     {
         $id=Auth::user()->id;
          $user = User::find($id);
         if(!empty($user)){
            
            $clientList = RequestedFund::leftJoin("users","requested_fund.client_id","=","users.id")
            ->select('requested_fund.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid")->groupBy("requested_fund.client_id")->get();
 
             return view('billing.requested_fund.retainer_requests', compact('user','clientList'));
         }else{
             return view('pages.404');
         }
     }
     public function loadRetainerRequestsEntry()
     {   
         $columns = array('requested_fund.id', 'requested_fund.id','contact_name', 'trust_account', 'amount_requested', 'amount_paid','amount_due','due_date','last_reminder_sent_on','user_name','id',);
         $requestData= $_REQUEST;
         
         $case = RequestedFund::leftJoin("users","requested_fund.client_id","=","users.id")
         ->leftJoin("users as u2","u2.id","=","requested_fund.deposit_into")
         ->select('requested_fund.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),DB::raw('CONCAT_WS(" ",u2.first_name,u2.last_name) as trust_account'),"users.id as uid");
        
        $getFirmsAllUserIds = User::where("firm_name", auth()->user()->firm_name)->pluck('id')->toArray();
        $case = $case->whereIn("requested_fund.created_by", $getFirmsAllUserIds);

         if(isset($requestData['c']) && $requestData['c']!=''){
             $case = $case->where("requested_fund.client_id",$requestData['c']);
         }
         if(isset($requestData['type']) && $requestData['type']!=''){
             if($requestData['type']=='sent'){
                $case = $case->where("requested_fund.status",'sent');
                $case = $case->where("requested_fund.due_date",">=",date('Y-m-d'));
             }
             if($requestData['type']=='overdue'){
                $case = $case->where("requested_fund.due_date","<",date('Y-m-d'));
                $case = $case->where("requested_fund.amount_paid",'0.00');

             }
             if($requestData['type']=='paid'){
                $case = $case->where("requested_fund.amount_due",'0.00');
             }
             if($requestData['type']=='partial'){
                $case = $case->where("requested_fund.amount_due","!=",'0.00');
                $case = $case->where("requested_fund.amount_paid","!=",'0.00');
             }

         }
         $totalData=$case->count();
         $totalFiltered = $totalData; 
 
         $case = $case->offset($requestData['start'])->limit($requestData['length']);
         if(isset($requestData['order']) && $requestData['order']!=''){
         $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
         }
         $case = $case->withCount('fundPaymentHistory');
         $case = $case->with('user', 'allocateToCase')->get();
         $json_data = array(
             "draw"            => intval( $requestData['draw'] ),   
             "recordsTotal"    => intval( $totalData ),  
             "recordsFiltered" => intval( $totalFiltered ), 
             "data"            => $case 
         );
         echo json_encode($json_data);  
     }
     public function printRequestFundEntry(Request $request)
     {
        
        $columns = array('requested_fund.id', 'requested_fund.id','contact_name', 'trust_account', 'amount_requested', 'amount_paid','amount_due','due_date','last_reminder_sent_on','user_name','id',);
        $requestData= $_REQUEST;
        
        $case = RequestedFund::leftJoin("users","requested_fund.client_id","=","users.id")
        ->leftJoin("users as u2","u2.id","=","requested_fund.deposit_into")
        ->select('requested_fund.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),DB::raw('CONCAT_WS(" ",u2.first_name,u2.last_name) as trust_account'),"users.id as uid");

        $getFirmsAllUserIds = User::where("firm_name", auth()->user()->firm_name)->pluck('id')->toArray();
        $case = $case->whereIn("requested_fund.created_by", $getFirmsAllUserIds);

        if(isset($requestData['c']) && $requestData['c']!=''){
            $case = $case->where("requested_fund.client_id",$requestData['c']);
        }
        if(isset($requestData['type']) && $requestData['type']!=''){
            if($requestData['type']=='sent'){
               $case = $case->where("requested_fund.status",'sent');
               $case = $case->where("requested_fund.due_date",">=",date('Y-m-d'));
            }
            if($requestData['type']=='overdue'){
               $case = $case->where("requested_fund.due_date","<",date('Y-m-d'));
               $case = $case->where("requested_fund.amount_paid",'0.00');

            }
            if($requestData['type']=='paid'){
               $case = $case->where("requested_fund.amount_due",'0.00');
            }
            if($requestData['type']=='partial'){
               $case = $case->where("requested_fund.amount_due","!=",'0.00');
               $case = $case->where("requested_fund.amount_paid","!=",'0.00');
            }

        }
        //  $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
         $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);
 
         $case = $case->get();
        return view('billing.requested_fund.viewRequestedFundsPdf',compact('case'));
        //  $filename="requested_funds_".time().'.pdf';
        //  $PDFData=view('billing.requested_fund.viewRequestedFundsPdf',compact('case'));
        //  $pdf = new Pdf;
        // //  $pdf->setOptions(['javascript-delay' => 5000]);
        //  if($_SERVER['SERVER_NAME']=='localhost'){
        //      $pdf->binary = WKHTMLTOPDF_PATH;
        //  }
        //  $pdf->addPage($PDFData);
        // //  $pdf->setOptions(['javascript-delay' => 5000]);
        //  $pdf->saveAs(public_path("download/pdf/".$filename));
        //  $path = public_path("download/pdf/".$filename);
        // $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        //  return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
        //  exit;
     }
     /******************************************** Saved Activity******************************/
     public function activities()
     {
         $id=Auth::user()->id;
          $user = User::find($id);
         if(!empty($user)){
             return view('billing.activities.activities');
         }else{
             return view('pages.404');
         }
     }

     public function loadActivity()
     {   
         $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
         $requestData= $_REQUEST;
         
         $case = TaskActivity::leftJoin("users","task_activity.created_by","=","users.id")
         ->select('task_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid");
         $case = $case->where("firm_id",Auth::User()->firm_name);
         $totalData=$case->count();
         $totalFiltered = $totalData; 
 
         $case = $case->offset($requestData['start'])->limit($requestData['length']);
         $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
         $case = $case->get();
         $json_data = array(
             "draw"            => intval( $requestData['draw'] ),   
             "recordsTotal"    => intval( $totalData ),  
             "recordsFiltered" => intval( $totalFiltered ), 
             "data"            => $case 
         );
         echo json_encode($json_data);  
     }
     public function newActivity(Request $request)
     {
         return view('billing.activities.newActivity');     
         exit;    
     } 

     public function saveActivity(Request $request)
     {
         $validator = \Validator::make($request->all(), [
             'activity_title' => 'required|min:1|max:255|unique:task_activity,title,NULL,id,firm_id,'.Auth::User()->firm_name,
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
                $TaskActivity=new TaskActivity;
                $TaskActivity->title=$request->activity_title;
                $TaskActivity->default_description=$request->description;
                if(isset($request->default_fees)){
                    $TaskActivity->flat_fees=$request->default_fees;
                }
                $TaskActivity->firm_id=Auth::User()->firm_name;
                $TaskActivity->created_at=date('Y-m-d h:i:s'); 
                $TaskActivity->created_by=Auth::User()->id; 
                $TaskActivity->save();
                session(['popup_success' => 'Activiy has been created successfully']);
                return response()->json(['errors'=>'']);
                exit;  
         }  
     }

     public function editActivity(Request $request)
     {
         
        $validator = \Validator::make($request->all(), [
            'id' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $activityData=TaskActivity::find($request->id);
            return view('billing.activities.editActivity',compact('activityData'));     
            exit;    
        }
     } 

      public function updateActivity(Request $request)
     {
         $validator = \Validator::make($request->all(), [
            'activity_title' => 'required|min:1|max:255|unique:task_activity,title,'.$request->id.',id,firm_id,'.Auth::User()->firm_name,
            'id' => 'required|numeric',
         ],['activity_title.unique'=>'Name already exists']);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
                $TaskActivity=TaskActivity::find($request->id);
                $TaskActivity->title=$request->activity_title;
                $TaskActivity->default_description=$request->description;
                if(isset($request->default_fees) && isset($request->flat_fees)){
                    $TaskActivity->flat_fees=$request->default_fees;
                }else{
                    $TaskActivity->flat_fees="0.00";
                }
                
                $TaskActivity->updated_at=date('Y-m-d h:i:s'); 
                $TaskActivity->updated_by=Auth::User()->id; 
                $TaskActivity->save();
                session(['popup_success' => 'Your activity has been updated']);
                return response()->json(['errors'=>'']);
                exit;  
         }  
     }
     public function deleteActivity(Request $request)
     {
         $validator = \Validator::make($request->all(), [
            'activity_id' => 'required|numeric',
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
                $id=$request->activity_id;
                TaskActivity::where("id", $id)->delete();
                session(['popup_success' => 'Activity was deleted']);
                return response()->json(['errors'=>'']);
                exit;  
         }  
     }
     public function printSavedActivity(Request $request)
     {
        
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $case = TaskActivity::leftJoin("users","task_activity.created_by","=","users.id")
        ->select('task_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid");
        $case = $case->where("firm_id",Auth::User()->firm_name);
 
        //  $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
         $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);
 
         $case = $case->get();
        return view('billing.activities.savedActivityPdf',compact('case')); 
        //  $filename="saved_activity".time().'.pdf';
        // $PDFData=view('billing.activities.savedActivityPdf',compact('case'));
        //  $pdf = new Pdf;
        //  // $pdf->setOptions(['javascript-delay' => 5000]);
        //  if($_SERVER['SERVER_NAME']=='localhost'){
        //      $pdf->binary = WKHTMLTOPDF_PATH;
        //  }
        //  $pdf->addPage($PDFData);
        //  // $pdf->setOptions(['javascript-delay' => 5000]);
        //  $pdf->saveAs(public_path("download/pdf/".$filename));
        //  $path = public_path("download/pdf/".$filename);
         // return response()->download($path);
         // exit;
        //  $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        //  return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
        //  exit;
     }
     /******************************************** Invoices******************************/
     public function invoices()
     {
        $id=Auth::user()->id;
        $user = User::find($id);
        if(!empty($user)){
             $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
            ->leftJoin("case_master","invoices.case_id","=","case_master.id")
            ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid")
            // ->where("invoices.created_by",$id);
            ->where("invoices.firm_id", Auth::User()->firm_name);
            $InvoiceCounter=$Invoices->count();
            
            $InvoicesPaidAmount = $InvoicesDraftAmount = $InvoicesSentAmount = $InvoicesPaidPartialAmount = $InvoicesUnsentAmount = $InvoicesPartialAmount = $InvoicesOverdueAmount = 0;
            if($Invoices->get()){
                foreach($Invoices->get() as $k=>$v){
                    if($v->due_date!=NULL && $v->due_date < date('Y-m-d') && $v->status != "Forwarded"){
                        $updateInvoice= Invoices::find($v->id);
                        if($v->status != "Paid" && $v->is_force_status == 0){
                            $updateInvoice->status="Overdue";
                            $updateInvoice->save();
                        }
                    }   
                    if($v->status == "Paid"){
                        $InvoicesPaidAmount += $v->paid_amount;
                    }
                    if($v->status == "Draft"){
                        $InvoicesDraftAmount += $v->total_amount;
                    }
                    if($v->status == "Sent"){
                        $InvoicesSentAmount += $v->total_amount;
                    }
                    if($v->status == "Partial"){
                        $InvoicesPaidPartialAmount += $v->paid_amount;
                    }
                    if($v->status == "Unsent"){
                        $InvoicesUnsentAmount += $v->total_amount;
                    }
                    if($v->status == "Partial"){
                        $InvoicesPartialAmount += $v->paid_amount;
                    }
                    if($v->status == "Overdue"){
                        $InvoicesOverdueAmount += $v->due_amount;
                    }
                }
            }


            // $InvoicesPaidAmount = Invoices::where("invoices.created_by",$id)->where("invoices.status","Paid")->where("invoices.created_by",$id)->sum("paid_amount");
           
            // $InvoicesPaidPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status","Partial")->where("invoices.created_by",$id)->sum("paid_amount");

            // $InvoicesSentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Sent')->where("invoices.created_by",$id)->sum("total_amount");

            // $InvoicesDraftAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Draft')->where("invoices.created_by",$id)->sum("total_amount");

            // $InvoicesUnsentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Unsent')->where("invoices.created_by",$id)->sum("total_amount");

            // $InvoicesPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Partial')->where("invoices.created_by",$id)->sum("paid_amount");
            
            // $InvoicesOverdueAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Overdue')->where("invoices.created_by",$id)->sum("due_amount");
            
            $getCaseIds = Invoices::select("case_id")->get()->pluck('case_id');
            $getClientIds = Invoices::select("user_id")->get()->pluck('user_id');

            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->whereIn("id",$getCaseIds)->where('is_entry_done',"1")->get();
           
            $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)/* ->where("parent_user",Auth::user()->id) */->where("firm_name",Auth::user()->firm_name)->whereIn("id",$getClientIds)->get();
           
            $CaseMasterCompanies = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->whereIn("id",$getClientIds)->get();
           
            $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();

            $potentialCaseList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","yes")->get();
            
            $InvoicesBatches = InvoiceBatch::leftJoin("users","invoice_batch.created_by","=","users.id")->select('*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_name'),"invoice_batch.id as invoice_batch_id")->where("firm_id",Auth::user()->firm_name)->get();

            if(isset($_GET['type']) && $_GET['type']=="batches"){
                
                return view('billing.invoices.batchInvoices',compact('InvoiceCounter','InvoicesPaidAmount','InvoicesPaidPartialAmount','InvoicesSentAmount','InvoicesDraftAmount','InvoicesUnsentAmount','InvoicesPartialAmount','InvoicesOverdueAmount','CaseMasterData','CaseMasterClient','caseLeadList','CaseMasterCompanies','potentialCaseList','InvoicesBatches'));
            }else{
                return view('billing.invoices.invoices',compact('InvoiceCounter','InvoicesPaidAmount','InvoicesPaidPartialAmount','InvoicesSentAmount','InvoicesDraftAmount','InvoicesUnsentAmount','InvoicesPartialAmount','InvoicesOverdueAmount','CaseMasterData','CaseMasterClient','caseLeadList','CaseMasterCompanies','potentialCaseList','InvoicesBatches'));
            }
        }else{
            return view('pages.404');
        }
     }

     public function loadInvoices()
     {  
        //For token generate Code
        // $Invoices=Invoices::get();
        // foreach($Invoices as $k){
        //    DB::table('invoices')->where("id",$k->id)->update([
        //         'invoice_token'=>Str::random(250)
        //     ]);
        // }

        $authUser = auth()->user();
         $columns = array('id', 'contact_name', 'id','id', 'contact_name', 'ctitle','total_amount','paid_amount','due_amount','invoices.due_date','invoices.created_at');
         $requestData= $_REQUEST;
         $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
         ->leftJoin("case_master","invoices.case_id","=","case_master.id")
         ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.user_level","users.id as uid","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid")
        //  ->where("invoices.created_by",Auth::user()->id);
         ->where("invoices.firm_id", $authUser->firm_name);
        if(auth()->user()->parent_user != 0) {
            $Invoices = $Invoices->whereHas('case.caseStaffAll', function($query) use($authUser){
                $query->where('user_id', $authUser->id);
            })
            ->orWhere("invoices.created_by", $authUser->id);
        }
      
         if(isset($requestData['type']) && in_array($requestData['type'],['unsent','sent','partial','forwarded','draft','paid','overdue'])){
            $Invoices = $Invoices->where("invoices.status",ucfirst($requestData['type']));
         }
      
         if(isset($requestData['global_search']) && $requestData['global_search']!=""){
            $MixVal=explode("-",$requestData['global_search']);
            $serachOn=base64_decode($MixVal[1]);
            $serachBy=base64_decode($MixVal[0]);
            if($serachOn=="case"){
                $Invoices = $Invoices->where("invoices.case_id",$serachBy);
            }
            if($serachOn=="contact" || $serachOn=="company"){
                $Invoices = $Invoices->where("invoices.user_id",$serachBy);
            }

            if($serachOn=="batches"){
                $InvoiceBatch=InvoiceBatch::find($serachBy);
                $AllIDs=explode(",",$InvoiceBatch['invoice_id']);
                $Invoices = $Invoices->whereIn("invoices.id",$AllIDs);
            }
         }
        //  $Invoices = $Invoices->where("invoices.is_lead_invoice",'no');
         $totalData=$Invoices->count();
         $totalFiltered = $totalData; 
        $Invoices = $Invoices->offset($requestData['start'])->limit($requestData['length']);
        $Invoices = $Invoices->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
        $Invoices = $Invoices->with(['invoiceForwardedToInvoice', 'invoiceShared' => function($query) {
                        $query->where('is_viewed', 'yes')->whereNotNull('last_viewed_at')->orderBy('last_viewed_at', 'asc');
                    }])->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
             "recordsTotal"    => intval( $totalData ),  
             "recordsFiltered" => intval( $totalFiltered ), 
             "data"            => $Invoices 
         );
         echo json_encode($json_data);  
     }
     public function loadBatchInvoices()
     {   
            $columns = array('invoice_batch_id','batch_code','created_name');
            $requestData= $_REQUEST;
            $Invoices = InvoiceBatch::leftJoin("users","invoice_batch.created_by","=","users.id")->select('*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_name'),"invoice_batch.id as invoice_batch_id")->where("firm_id",Auth::user()->firm_name);
            $totalData=$Invoices->count();
            $totalFiltered = $totalData; 

            $Invoices = $Invoices->offset($requestData['start'])->limit($requestData['length']);
            $Invoices = $Invoices->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
            $Invoices = $Invoices->get();
            $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
                "recordsTotal"    => intval( $totalData ),  
                "recordsFiltered" => intval( $totalFiltered ), 
                "data"            => $Invoices 
            );
            echo json_encode($json_data);  
     }

     public function sendInvoiceReminder(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'id' => 'required|min:1|max:255',
            'invoice_id' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseClientSelection=CaseClientSelection::select("selected_user")->where("is_billing_contact","yes")->where("case_id",$request->id)->get()->pluck("selected_user");
            
            $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->where("id",$CaseClientSelection)->first();

            $Invoices = Invoices::find($request->invoice_id);
            $invoice_id=$request->invoice_id;
            return view('billing.invoices.sendReminderPopup',compact('invoice_id','userData','Invoices'));     
            exit;    
        }
    } 
    public function saveInvoiceReminder(Request $request)
    {
     
        $validator = \Validator::make($request->all(), [
            'client' => 'required|array|min:1'
        ],
        ['min'=>'Please choose at least one contact',
        'required'=>'Please choose at least one contact']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $Invoices=Invoices::find($request->invoice_id);
            $Invoices->last_reminder_sent_on=date('Y-m-d h:i:s');
            $c=$Invoices->reminder_sent_counter;
            $Invoices->reminder_sent_counter=$c+1;
            $Invoices->save();

            $firmData=Firm::find(Auth::User()->firm_name);
            $getTemplateData = EmailTemplate::find(12);
            // $token=url('activate_account/bills=&web_token='.$Invoices->invoice_unique_token);
            $token = route("client/bills/detail", $Invoices->decode_id);

            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', $request->message, $mail_body);
            $mail_body = str_replace('{token}', $token, $mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
           $mail_body = str_replace('{year}', date('Y'), $mail_body);        

            $clientData=User::find($Invoices->user_id);
            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => "Reminder: Invoice #".$request->invoice_id." is available to view for ".$firmData->firm_name,
                "to" => $clientData->email,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            $sendEmail = $this->sendMail($user);
            session(['popup_success' => 'Reminders have been sent']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function payInvoicePopup(Request $request)
    {
       $validator = \Validator::make($request->all(), [
            'id' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $invoice_id=$request->id;
            $invoiceData=Invoices::where("id",$invoice_id)->first();
            if(!empty($invoiceData)){
                checkLeadInfoExists($invoiceData['user_id']);
                $firmData=Firm::find(Auth::User()->firm_name);
                $caseMaster=CaseMaster::select("case_title")->find($invoiceData['case_id']);
                $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid", "credit_account_balance", "users.user_level")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$invoiceData['user_id'])->first();
                $trustAccounts = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
                ->join('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')
                ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"users.id as uid","users.user_level","users_additional_info.trust_account_balance","users.user_level","users_additional_info.credit_account_balance", "users.user_title")
                ->where("case_client_selection.case_id",$invoiceData['case_id'])
                ->groupBy("case_client_selection.selected_user")->get(); 

                $invoiceUserNotInCase = ''; 
                if(!in_array($invoiceData->user_id, $trustAccounts->pluck('uid')->toArray()) && $userData->user_level != 5 && $invoiceData['case_id'] != 0) {
                    $invoiceUserNotInCase = UsersAdditionalInfo::where("user_id", $invoiceData->user_id)->with("user")->first();
                }
                // return $invoiceUserNotInCase;

                if($userData->user_level == 5) {
                    $trustAccounts = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid", "credit_account_balance", "users.user_level", "users.user_title")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$invoiceData['user_id'])->get();
                }
                // return $trustAccounts;
                return view('billing.invoices.payInvoice',compact('userData','firmData','invoice_id','invoiceData','caseMaster','trustAccounts','invoiceUserNotInCase'));
                exit;    
            }else{
                return response()->json(['errors'=>'error']);
            }
        }
    }

    public function saveTrustInvoicePayment(Request $request)
    {
        // return $request->all();
        // {"_token":"CMcon9N8F5rtezgagh3BcbT6WdK0Oo86a4XEtn9H","invoice_id":"260","contact_id":"11141","trust_account":"11141","amount":"2.75","payment_date":"09\/27\/2021","notes":"test","save":"yes"}
        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($request->invoice_id);
        $paid=$InvoiceData['paid_amount'];
        $invoice=$InvoiceData['total_amount'];
        $finalAmt=$invoice-$paid;

        $userAdditionalInfo=UsersAdditionalInfo::where("user_id",$request->contact_id)->first();
        if($request->is_case == "yes") {
            if($InvoiceData->is_lead_invoice == 'yes') {
                $leadAdditionalInfo = LeadAdditionalInfo::where('user_id', $request->contact_id)->select("allocated_trust_balance")->first();
                $account_balance = $leadAdditionalInfo->allocated_trust_balance ?? 0.00;
            } else {
                $CaseClientSelection = CaseClientSelection::select("allocated_trust_balance","selected_user")->where("selected_user",$request->contact_id)->where("case_id",$request->trust_account)->first();
                $account_balance = $CaseClientSelection['allocated_trust_balance'];
            }
        }else{
            $account_balance = $userAdditionalInfo['unallocate_trust_balance'];
        }
        // return $account_balance;
        $validator = \Validator::make($request->all(), [
            'trust_account' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt.'|lte:'.$account_balance,
            'invoice_id' => 'required|numeric'
        ],[
            'amount.min'=>"Amount must be greater than $0.00",
            'amount.lte'=>"Account does not have sufficient funds",
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
           
            DB::beginTransaction();
            try {
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","trust")->orderBy("created_at","DESC")->first();
                
                //Insert invoice payment record.
                $entryDone=  InvoicePayment::create([
                    'invoice_id'=>$request->invoice_id,
                    'payment_from'=>'trust',
                    'amount_paid'=>$request->amount,
                    'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"0",
                    'deposit_into'=>"Operating Account",
                    'payment_from_id'=>$request->trust_account,
                    'total'=> ($currentBalance) ? ($currentBalance['total']+$request->amount) : $request->amount,
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);
                $lastInvoicePaymentId= $entryDone->id;
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();
               
                //Deduct invoice amount when payment done
                $this->updateInvoiceAmount($request->invoice_id);

                // Deduct amount from trust account after payment.
                // $trustAccountAmount=($userData['trust_account_balance']-$request->amount);
                // UsersAdditionalInfo::where('user_id',$request->contact_id)
                // ->update(['trust_account_balance'=>$trustAccountAmount]);
                if(isset($request->trust_account)){
                    UsersAdditionalInfo::where("user_id",$request->contact_id)->decrement('trust_account_balance', $request->amount);
                    // $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->trust_account)->first();
                    // unallocate to selected user
                    if($request->is_case == "") {                        
                        $TrustInvoice=new TrustHistory;
                        $TrustInvoice->client_id=$request->trust_account;
                        $TrustInvoice->payment_method='Trust';
                        $TrustInvoice->amount_paid=$request->amount;
                        $TrustInvoice->current_trust_balance=$userAdditionalInfo->trust_account_balance;
                        $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                        $TrustInvoice->notes=$request->notes;
                        $TrustInvoice->fund_type='payment';
                        $TrustInvoice->related_to_invoice_id = $request->invoice_id;
                        $TrustInvoice->created_by=Auth::user()->id; 
                        $TrustInvoice->allocated_to_case_id = NULL;
                        $TrustInvoice->related_to_invoice_payment_id = $lastInvoicePaymentId;
                        $TrustInvoice->save();
                    }else{
                        // allocate to case/lead
                        if($InvoiceData->is_lead_invoice == 'yes') {
                            LeadAdditionalInfo::where('user_id', $request->contact_id)->decrement('allocated_trust_balance', $request->amount);
                        } else {
                            $CaseClientSelection = CaseClientSelection::select("allocated_trust_balance","selected_user")->where("selected_user",$request->contact_id)->where("case_id",$request->trust_account)->first();
                            if(!empty($CaseClientSelection)){
                                DB::table('case_client_selection')->where("selected_user",$request->contact_id)->where("case_id",$request->trust_account)
                                ->update([
                                    'allocated_trust_balance'=>($CaseClientSelection['allocated_trust_balance'] - $request->amount),
                                ]);
                                CaseMaster::where('id', $request->trust_account)->decrement('total_allocated_trust_balance', $request->amount);
                            }
                        }
                        $TrustInvoice=new TrustHistory;
                        $TrustInvoice->client_id=$request->contact_id;
                        $TrustInvoice->payment_method='Trust';
                        $TrustInvoice->amount_paid=$request->amount;
                        $TrustInvoice->current_trust_balance=$userAdditionalInfo->trust_account_balance;
                        $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                        $TrustInvoice->notes=$request->notes;
                        $TrustInvoice->fund_type='payment';
                        $TrustInvoice->related_to_invoice_id = $request->invoice_id;
                        $TrustInvoice->created_by=Auth::user()->id; 
                        $TrustInvoice->allocated_to_case_id = ($InvoiceData->is_lead_invoice == 'no') ? $request->trust_account : NULL;
                        $TrustInvoice->related_to_invoice_payment_id = $lastInvoicePaymentId;
                        $TrustInvoice->allocated_to_lead_case_id = ($InvoiceData->is_lead_invoice == 'yes') ? $request->contact_id : NULL;
                        $TrustInvoice->save();
                    } 
                }            

                //Response message
                $firmData=Firm::find(Auth::User()->firm_name);
                $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                // all good
                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$request->invoice_id;
                $invoiceHistory['acrtivity_title']='Payment Received';
                $invoiceHistory['pay_method']='Trust';
                $invoiceHistory['amount']=$request->amount;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['payment_from']='trust';
                $invoiceHistory['deposit_into']='Operating Account';
                $invoiceHistory['deposit_into_id']=($request->contact_id)??NULL;
                $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $newHistoryId = $this->invoiceHistory($invoiceHistory);

                $request->request->add(["invoice_history_id" => $newHistoryId]);
                $request->request->add(["trust_history_id" => @$TrustInvoice->id]);
                $request->request->add(["payment_type" => 'payment']);

                //Add Invoice history
                // $InvoiceData=Invoices::find($request->invoice_id);
                $data=[];
                $data['case_id']=$InvoiceData['case_id'];
                $data['user_id']=$request->contact_id;
                $data['activity']='accepted a payment of $'.number_format($request->amount,2).' (Trust)';
                $data['activity_for']=$InvoiceData['id'];
                $data['type']='invoices';
                $data['action']='pay';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            

                //Get previous amount
                $this->updateTrustAccountActivity($request, $amtAction = 'sub', $InvoiceData, $isDebit = "yes");

                
                //Get previous amount
                $this->updateClientPaymentActivity($request, $InvoiceData);
                
                DB::commit(); 
                return response()->json(['errors'=>'','msg'=>$msg]);
                exit;     
                            
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors'=>[$e->getMessage()], 'line_no' => [$e->getLine()]]); //$e->getMessage()
                 exit;   
            }
        }
    }
   
    /* public function saveInvoicePaymentOld(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($request->invoice_id);
        $paid=$InvoiceData['paid_amount'];
        $invoice=$InvoiceData['total_amount'];
        $finalAmt=$invoice-$paid;

        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt,
            'invoice_id' => 'required|numeric'
        ],[
            'amount.min'=>"Amount must be greater than $0.00",
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
           
            DB::beginTransaction();
            try {
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("deposit_into","Trust Account")->orderBy("created_at","DESC")->first();
                if(!empty($currentBalance['total'])){
                  $s=$currentBalance['total']+$request->amount;
                }else{
                    $s=$request->amount;
                }
                $entryDone=InvoicePayment::create([
                    'invoice_id'=>$request->invoice_id,
                    'payment_from'=>'client',
                    'amount_paid'=>$request->amount,
                    'payment_method'=>$request->payment_method,
                    'deposit_into'=>$request->deposit_into,
                    'notes'=>$request->notes,
                    'deposit_into_id'=>($request->trust_account)??NULL,
                    'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"1",
                    'total'=>$s,
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);

                $lastInvoicePaymentId=$entryDone->id;
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();

                //Deduct invoice amount when payment done
                $this->updateInvoiceAmount($request->invoice_id);

                //Deposit into trust account
                if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    // unallocate to selected user
                    $userDataForDeposit = UsersAdditionalInfo::select("trust_account_balance","user_id")->where("user_id",$request->trust_account)->first();
                    if(!empty($userDataForDeposit)){
                        DB::table('users_additional_info')->where("user_id",$request->trust_account)->update([
                            'trust_account_balance'=>($userDataForDeposit['trust_account_balance'] + $request->amount),
                        ]);

                        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->trust_account)->first();
                        
                        $TrustInvoice=new TrustHistory;
                        $TrustInvoice->client_id=$request->trust_account;
                        $TrustInvoice->payment_method=$request->payment_method;
                        $TrustInvoice->amount_paid=$request->amount;
                        $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
                        $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                        $TrustInvoice->notes=$request->notes;
                        $TrustInvoice->fund_type='payment';
                        $TrustInvoice->related_to_invoice_id = $request->invoice_id;
                        $TrustInvoice->created_by=Auth::user()->id; 
                        $TrustInvoice->allocated_to_case_id = NULL;
                        $TrustInvoice->related_to_invoice_payment_id = $InvoicePayment->id;
                        $TrustInvoice->save();
                    }
                    // allocate to case 
                    $CaseClientSelection = CaseClientSelection::select("allocated_trust_balance","selected_user")->where("selected_user",$request->contact_id)->where("case_id",$request->trust_account)->first();
                    if(!empty($CaseClientSelection)){
                        DB::table('case_client_selection')->where("selected_user",$request->contact_id)->where("case_id",$request->trust_account)
                        ->update([
                            'allocated_trust_balance'=>($CaseClientSelection['allocated_trust_balance'] + $request->amount),
                        ]);

                        CaseMaster::where('id', $request->trust_account)->increment('total_allocated_trust_balance', $request->amount);
                    
                        UsersAdditionalInfo::where("user_id",$request->contact_id)->increment('trust_account_balance', $request->amount);
                    
                        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->contact_id)->first();
                        
                        $TrustInvoice=new TrustHistory;
                        $TrustInvoice->client_id=$request->contact_id;
                        $TrustInvoice->payment_method=$request->payment_method;
                        $TrustInvoice->amount_paid=$request->amount;
                        $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
                        $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                        $TrustInvoice->notes=$request->notes;
                        $TrustInvoice->fund_type='payment';
                        $TrustInvoice->related_to_invoice_id = $request->invoice_id;
                        $TrustInvoice->created_by=Auth::user()->id; 
                        $TrustInvoice->allocated_to_case_id = $request->trust_account;
                        $TrustInvoice->related_to_invoice_payment_id = $InvoicePayment->id;
                        $TrustInvoice->save();
                        $request->trust_account = $request->contact_id;
                    }       
                }

                if(isset($request->credit_account) && $request->deposit_into=="Operating Account"){
                    // Deposit amount from credit account after payment.
                    UsersAdditionalInfo::where("user_id",$request->contact_id)->increment('credit_account_balance', $request->amount);
                        
                    // Add credit history
                    $userAddInfo = UsersAdditionalInfo::where("user_id", $request->contact_id)->first();
                    DepositIntoCreditHistory::create([
                        "user_id" => $request->contact_id,
                        "payment_method" => $request->payment_method,
                        "deposit_amount" => $request->amount ?? 0,
                        "payment_date" => date('Y-m-d'),
                        "payment_type" => "payment deposit",
                        "total_balance" => $userAddInfo->credit_account_balance,
                        "related_to_invoice_id" => $request->invoice_id,
                        "created_by" => auth()->id(),
                        "firm_id" => auth()->user()->firm_name,
                        "related_to_invoice_payment_id" => $InvoicePayment->id,
                    ]);
                }
                DB::commit();
                //Response message
                $firmData=Firm::find(Auth::User()->firm_name);
                $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                // all good

                 //Code For installment amount
                $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$request->invoice_id)->first();
                if(!empty($getInstallMentIfOn)){
                    $this->installmentManagement($request->amount,$request->invoice_id);
                }
                    
                
                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$request->invoice_id;
                $invoiceHistory['acrtivity_title']='Payment Received';
                $invoiceHistory['pay_method']=$request->payment_method;
                $invoiceHistory['amount']=$request->amount;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['payment_from']='offline';
                $invoiceHistory['deposit_into']=$request->deposit_into;
                $invoiceHistory['deposit_into_id']=$request->trust_account;
                $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $this->invoiceHistory($invoiceHistory);

                
                //Add Invoice history
                $data=[];
                $data['case_id']=$InvoiceData['case_id'];
                $data['user_id']=$InvoiceData['user_id'];
                $data['activity']='accepted a payment of $'.number_format($request->amount,2).' ('.ucfirst($request->payment_method).')';
                $data['activity_for']=$InvoiceData['id'];
                $data['type']='invoices';
                $data['action']='pay';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                //Case Activity
                if($InvoiceData['case_id'] > 0){
                    $caseActivityData=[];
                    $caseActivityData['activity_title']='accepted a payment of $'.number_format($request['amount'],2).' for invoice';
                    $caseActivityData['case_id']=$InvoiceData['case_id'];
                    $caseActivityData['activity_type']='accept_payment';
                    $caseActivityData['extra_notes']=$InvoiceData['id'];
                    $caseActivityData['staff_id']=$InvoiceData['user_id'];
                    $this->caseActivity($caseActivityData);
                }

                 //Get previous amount
                 if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
                 }else{
                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                 }
                 $activityHistory=[];
                 $activityHistory['user_id']=$InvoiceData['user_id'];
                 $activityHistory['related_to']=$InvoiceData['id'];
                 $activityHistory['case_id']=$InvoiceData['case_id'];
                 $activityHistory['credit_amount']=$request->amount;
                 $activityHistory['debit_amount']=0.00;
                 if(!empty($AccountActivityData)){
                    $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;
                }else{
                    $activityHistory['total_amount']=$request->amount;
                }
                 $activityHistory['entry_date']=date('Y-m-d');
                 $activityHistory['notes']=$request->notes;
                 $activityHistory['status']="unsent";
                 if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    $activityHistory['pay_type']="trust";
                    $activityHistory['from_pay']="trust";                    
                 }else{
                    $activityHistory['pay_type']="client"; 
                 }
                 $activityHistory['firm_id']=Auth::user()->firm_name;
                 $activityHistory['section']="invoice";
                 $activityHistory['created_by']=Auth::User()->id;
                 $activityHistory['created_at']=date('Y-m-d H:i:s');
                 $this->saveAccountActivity($activityHistory);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors'=>[$e->getMessage()], 'line_no' => [$e->getLine()] ]); //$e->getMessage()
                 exit;   
            }
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    } */

    public function saveInvoicePayment(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($request->invoice_id);
        $paid=$InvoiceData['paid_amount'];
        $invoice=$InvoiceData['total_amount'];
        $finalAmt=$invoice-$paid;

        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt,
            'invoice_id' => 'required|numeric'
        ],[
            'amount.min'=>"Amount must be greater than $0.00",
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
           
            DB::beginTransaction();
            try {
                $invoiceHistory=[];
                $invoiceHistory['deposit_into'] = $request->deposit_into;
                $request->request->add(['payment_type' => 'payment']);

                //Insert invoice payment record.
                $InvoicePayment=InvoicePayment::create([
                    'invoice_id'=>$request->invoice_id,
                    'payment_from'=>'client',
                    'amount_paid'=>$request->amount,
                    'payment_method'=>$request->payment_method,
                    'deposit_into'=>$request->deposit_into,
                    'notes'=>$request->notes,
                    'deposit_into_id'=>($request->trust_account) ?? $request->contact_id,
                    'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    'status'=>"0",
                    'entry_type'=>"1",
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);

                $lastInvoicePaymentId=$InvoicePayment->id;
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();
                $InvoicePayment->refresh();

                //Deduct invoice amount when payment done
                $this->updateInvoiceAmount($request->invoice_id);

                $userAddInfo = UsersAdditionalInfo::where("user_id", $request->contact_id)->first();

                // Offline payment and deposit into credit account
                if(isset($request->credit_account) && $request->deposit_into=="Operating Account" && $request->credit_payment == "on") {
                    // Deposit amount from credit account after payment.
                    if($userAddInfo) {
                        $userAddInfo->increment('credit_account_balance', $request->amount);
                    }  
                    // Add credit history
                    DepositIntoCreditHistory::create([
                        "user_id" => $request->contact_id,
                        "payment_method" => $request->payment_method,
                        "deposit_amount" => $request->amount ?? 0,
                        "payment_date" => date('Y-m-d'),
                        "payment_type" => "payment deposit",
                        "total_balance" => $userAddInfo->credit_account_balance,
                        "related_to_invoice_id" => $request->invoice_id,
                        "created_by" => auth()->id(),
                        "firm_id" => auth()->user()->firm_name,
                        "related_to_invoice_payment_id" => $InvoicePayment->id,
                        'notes'=>$request->notes,
                    ]);

                    $invoiceHistory['deposit_into'] = 'Credit';
                    $request->request->add(['payment_type' => 'payment deposit']);
                }

                // Offline payment and deposit into trust account
                if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    // unallocate to selected user
                    if($userAddInfo) {
                        $userAddInfo->increment("trust_account_balance", $request->amount);
                    }

                    if($request->is_case == '') {
                        $TrustInvoice=new TrustHistory;
                        $TrustInvoice->client_id=$request->trust_account;
                        $TrustInvoice->payment_method=$request->payment_method;
                        $TrustInvoice->amount_paid=$request->amount;
                        $TrustInvoice->current_trust_balance=@$userAddInfo->trust_account_balance;
                        $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                        $TrustInvoice->notes=$request->notes;
                        $TrustInvoice->fund_type='payment deposit';
                        $TrustInvoice->related_to_invoice_id = $request->invoice_id;
                        $TrustInvoice->created_by=Auth::user()->id; 
                        $TrustInvoice->allocated_to_case_id = NULL;
                        $TrustInvoice->related_to_invoice_payment_id = $InvoicePayment->id;
                        $TrustInvoice->save();
                    }
                    // allocate to case 
                    if($request->is_case == 'yes'){
                        CaseClientSelection::where("selected_user",$request->contact_id)->where("case_id",$request->trust_account)->increment('allocated_trust_balance', $request->amount);
                        CaseMaster::where('id', $request->trust_account)->increment('total_allocated_trust_balance', $request->amount);
                        
                        $TrustInvoice=new TrustHistory;
                        $TrustInvoice->client_id=$request->contact_id;
                        $TrustInvoice->payment_method=$request->payment_method;
                        $TrustInvoice->amount_paid=$request->amount;
                        $TrustInvoice->current_trust_balance=@$userAddInfo->trust_account_balance;
                        $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                        $TrustInvoice->notes=$request->notes;
                        $TrustInvoice->fund_type='payment deposit';
                        $TrustInvoice->related_to_invoice_id = $request->invoice_id;
                        $TrustInvoice->created_by=Auth::user()->id; 
                        $TrustInvoice->allocated_to_case_id = $request->trust_account;
                        $TrustInvoice->related_to_invoice_payment_id = $InvoicePayment->id;
                        $TrustInvoice->save();
                        $request->request->add(["allocated_case_id" => $request->trust_account]);
                    }       
                    $request->request->add(['payment_type' => 'payment deposit']);
                }

                //Response message
                $firmData=Firm::find(Auth::User()->firm_name);
                $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                // all good

                 //Code For installment amount
                $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$request->invoice_id)->first();
                if(!empty($getInstallMentIfOn)){
                    $this->installmentManagement($request->amount,$request->invoice_id);
                }
                
                $invoiceHistory['invoice_id']=$request->invoice_id;
                $invoiceHistory['acrtivity_title']='Payment Received';
                $invoiceHistory['pay_method']=$request->payment_method;
                $invoiceHistory['amount']=$request->amount;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['payment_from']='offline';
                $invoiceHistory['deposit_into_id']=$request->contact_id;
                $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $newHistoryId = $this->invoiceHistory($invoiceHistory);
                $request->request->add(["invoice_history_id" => $newHistoryId]);
                
                //Add Invoice history
                $data=[];
                $data['case_id']=$InvoiceData['case_id'];
                $data['user_id']=$InvoiceData['user_id'];
                $data['activity']='accepted a payment of $'.number_format($request->amount,2).' ('.ucfirst($request->payment_method).')';
                $data['activity_for']=$InvoiceData['id'];
                $data['type']='invoices';
                $data['action']='pay';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                //Case Activity
                if($InvoiceData['case_id'] > 0){
                    $caseActivityData=[];
                    $caseActivityData['activity_title']='accepted a payment of $'.number_format($request['amount'],2).' for invoice';
                    $caseActivityData['case_id']=$InvoiceData['case_id'];
                    $caseActivityData['activity_type']='accept_payment';
                    $caseActivityData['extra_notes']=$InvoiceData['id'];
                    $caseActivityData['staff_id']=$InvoiceData['user_id'];
                    $this->caseActivity($caseActivityData);
                }
                $request->request->add(["from_pay" => 'normal']);
                $request->request->add(["case_id" => $request->trust_account]);
                $request->trust_account = $request->contact_id;
                 //Get previous amount
                 if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    $request->request->add(["trust_history_id" => @$TrustInvoice->id]);
                    $this->updateTrustAccountActivity($request, null, $InvoiceData);
                 }else{
                    $this->updateClientPaymentActivity($request, $InvoiceData);
                 }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors'=>[$e->getMessage()], 'line_no' => [$e->getLine()] ]); //$e->getMessage()
                 exit;   
            }
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }

    public function deleteInvoiceForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
           'invoice_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                $id=$request->invoice_id;
                $Invoices=Invoices::find($id);
                    //Add Invoice history
                $data=[];
                $data['case_id']=$Invoices['case_id'];
                $data['user_id']=$Invoices['user_id'];
                $data['activity']='deleted an invoice';
                $data['activity_for']=$Invoices['id'];
                $data['type']='invoices';
                $data['action']='delete';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                // Invoices::where("id", $id)->delete();

                //Remove flat fee entry for the invoice and reactivated time entry
                $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::where("invoice_id",$id)->get();
                foreach($FlatFeeEntryForInvoice as $k=>$v){
                    DB::table('flat_fee_entry')->where("id",$v->flat_fee_entry_id)->update([
                    'status'=>'unpaid',
                    'invoice_link' => null,
                    'token_id'=>NULL,
                    ]);
                    FlatFeeEntryForInvoice::where("id", $v->id)->delete();
                }
                 //Removed time entry id
                 $TimeEntryForInvoice=TimeEntryForInvoice::where("invoice_id",$id)->get();
                 foreach($TimeEntryForInvoice as $k=>$v){
                     DB::table('task_time_entry')->where("id",$v->time_entry_id)->update([
                         'status'=>'unpaid',
                         'invoice_link'=>NULL,
                         'token_id'=>NULL,
                     ]); 
                     TimeEntryForInvoice::where("id",$v->time_entry_id)->delete();
                 }
                 //Removed expense entry
                 $ExpenseForInvoice=ExpenseForInvoice::where("invoice_id",$id)->get();
                 foreach($ExpenseForInvoice as $k=>$v){
                     DB::table('expense_entry')->where("id",$v->expense_entry_id)->update([
                         'status'=>'unpaid',
                         'invoice_link'=>NULL,
                         'token_id'=>NULL,
                     ]);
                     ExpenseForInvoice::where("id",$v->expense_entry_id)->delete();
                 }
                 //Removed shared invoice 
                 $SharedInvoice=SharedInvoice::where("invoice_id",$id)->delete();
 
                 //Removed invoice adjustment entry
                 $InvoiceAdjustment=InvoiceAdjustment::where("invoice_id",$id)->delete();

                // Update trust balance
                $invoicePaymentFromTrust = InvoicePayment::where("invoice_id", $Invoices->id)->where("payment_from", "trust")->get();
                $accessUser = UsersAdditionalInfo::where("user_id", $Invoices->user_id)->first();
                if($accessUser && $invoicePaymentFromTrust) {
                    $paidAmount = $invoicePaymentFromTrust->sum('amount_paid');
                    $refundAmount = $invoicePaymentFromTrust->sum('amount_refund');
                    $accessUser->fill(['trust_account_balance' => $accessUser->trust_account_balance + ($paidAmount - $refundAmount)])->save();
                }

                // Update forwarded invoices
                $forwardedInvoices = Invoices::whereIn("id", $Invoices->forwardedInvoices->pluck("id")->toArray())->get();
                if($forwardedInvoices) {
                    foreach($forwardedInvoices as $key => $item) {
                        $this->updateInvoiceAmount($item->id);
                    }
                }

                InvoicePaymentPlan::where("invoice_id", $Invoices->id)->delete();
                InvoiceInstallment::where("invoice_id", $Invoices->id)->delete();
                InvoicePayment::where("invoice_id",$Invoices->id)->delete();

                $Invoices->delete();
                session(['popup_success' => 'Invoice was deleted']);
                return response()->json(['errors'=>'']);
                exit;  
        }  
    }

    public function deleteLeadInvoiceForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
           'invoice_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                $id=$request->invoice_id;
                $Invoices=PotentialCaseInvoice::find($id);
                    //Add Invoice history
                $data=[];
                $data['case_id']=NULL;
                $data['user_id']=$Invoices['lead_id'];
                $data['activity']='deleted an invoice';
                $data['activity_for']=$Invoices['id'];
                $data['type']='invoices';
                $data['action']='delete';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
                PotentialCaseInvoice::where("id", $id)->delete();
                session(['popup_success' => 'Invoice was deleted']);
                return response()->json(['errors'=>'']);
                exit;  
        }  
    }
    public function open()
    {
        $id=Auth::user()->id;
         $user = User::find($id);
        if(!empty($user)){
            // $getChildUsers=$this->getParentAndChildUserIds();
            // $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
            // delete all time entry or expense_entry or flatfees which token id is 9999999
            TaskTimeEntry::where('status','unpaid')->where('token_id','9999999')->delete();
            ExpenseEntry::where('status','unpaid')->where('token_id','9999999')->delete();
            FlatFeeEntry::where('status','unpaid')->where('token_id','9999999')->delete();
            $upcomingInvoice=1;
            return view('billing.invoices.create_invoices',compact(/* 'practiceAreaList', */'upcomingInvoice'));
        }else{
            return view('pages.404');
        }
    }

    public function loadUpcomingInvoices(Request $request)
    {   
        $columns = array('contact_name', 'contact_name', 'id', 'contact_name', 'id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $Invoices = CaseMaster::
        leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")
        ->leftJoin("users","case_client_selection.selected_user","=","users.id")
        ->leftjoin("task_time_entry","task_time_entry.case_id","=","case_master.id")
        ->leftjoin("expense_entry","expense_entry.case_id","=","case_master.id")
        ->leftjoin("case_staff", "case_staff.case_id", "=", "case_master.id")
        ->leftjoin("users as lau", "lau.id", "=", "case_staff.lead_attorney")
        ->select('case_client_selection.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","case_master.case_title as ctitle",
                "case_master.case_unique_number","case_master.id as ccid","case_master.practice_area as pa","case_master.billing_method","case_master.billing_amount",
                DB::raw('CONCAT_WS(" ",lau.first_name,lau.last_name) as lead_attorney_name'));
      
        // $Invoices = $Invoices->whereIn("case_master.id",$this->getInvoicePendingCase());
        $Invoices = $Invoices->where("case_client_selection.is_billing_contact","yes");
        // $Invoices = $Invoices->where("task_time_entry.status","unpaid");
        // $Invoices = $Invoices->orWhere("expense_entry.status","unpaid");
        if(Auth::user()->parent_user==0)
        {
            $getChildUsers=$this->getParentAndChildUserIds();
            $Invoices = $Invoices->whereIn("case_master.created_by",$getChildUsers);
            $Invoices->where(function($Invoices){
                $Invoices = $Invoices->orwhere("task_time_entry.status","unpaid");
                $Invoices = $Invoices->orWhere("expense_entry.status","unpaid");
            });

        }else{
            // $Invoices = $Invoices->where("case_master.created_by",Auth::User);
            $Invoices = $Invoices->whereHas('caseStaffAll', function($query) {
                $query->where('user_id', auth()->id());
            });

        }
        //Filters
        if($request->practice_area_id != 'all') {
            $Invoices = $Invoices->where("case_master.practice_area", $request->practice_area_id);
        }
        if($request->lead_attorney_id != "all") {
            $Invoices = $Invoices->where("case_staff.lead_attorney", $request->lead_attorney_id);
        }
        if($request->firm_office_id != 'all') {
            $Invoices = $Invoices->where("case_master.case_office", $request->firm_office_id);
        }
        if($request->billing_method != 'all') {
            $Invoices = $Invoices->where("case_master.billing_method", $request->billing_method);
        }
        if($request->balance_filter != 'all') {
            if($request->balance_filter == "uninvoiced") {
                // $Invoices = $Invoices->whereDoesntHave("invoices");
                $Invoices = $Invoices->where("task_time_entry.status","unpaid")->orWhere("expense_entry.status","unpaid");
            } else {
                $Invoices = $Invoices->whereHas("invoices", function($query) {
                    $query->havingRaw('SUM(due_amount) > ?', array(0));
                });
            }
        }
        $totalData=$Invoices->count();
        $totalFiltered = $totalData; 
        
        $Invoices = $Invoices->offset($requestData['start'])->limit($requestData['length']);
        $Invoices = $Invoices->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
        $Invoices = $Invoices->groupBy("case_master.id");
        $Invoices = $Invoices->get()->each->setAppends(['payment_plan_active_for_case', 'last_invoice']);
        $json_data = array(
           "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $Invoices 
        );
        echo json_encode($json_data);  
    }

    public function loadUpcomingInvoicesWithLoader(Request $request)
    {   
        $columns = array('contact_name', 'contact_name', 'id', 'contact_name', 'id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $Invoices = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")
        ->leftJoin("users","case_client_selection.selected_user","=","users.id")
        // ->leftjoin("flat_fee_entry","flat_fee_entry.case_id","=","case_master.id")
        // ->leftjoin("task_time_entry","task_time_entry.case_id","=","case_master.id")
        // ->leftjoin("expense_entry","expense_entry.case_id","=","case_master.id")
        ->leftjoin("case_staff", "case_staff.case_id", "=", "case_master.id")
        ->leftjoin("users as lau", "lau.id", "=", "case_staff.lead_attorney")
        ->select('case_client_selection.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","case_master.case_title as ctitle",
                "case_master.case_unique_number","case_master.id as ccid","case_master.practice_area as pa","case_master.billing_method","case_master.billing_amount",
                DB::raw('CONCAT_WS(" ",lau.first_name,lau.last_name) as lead_attorney_name'));
      
        $Invoices = $Invoices->whereIn("case_master.id",$this->getInvoicePendingCase());
        $Invoices = $Invoices->where("case_client_selection.is_billing_contact","yes");
        // $Invoices = $Invoices->where("task_time_entry.status","unpaid");
        // $Invoices = $Invoices->orWhere("expense_entry.status","unpaid");
        if(Auth::user()->parent_user==0)
        {
            $getChildUsers=$this->getParentAndChildUserIds();
            $Invoices = $Invoices->whereIn("case_master.created_by",$getChildUsers);
            // $Invoices->where(function($Invoices){
            //     $Invoices = $Invoices->orwhere("flat_fee_entry.status","unpaid");
            //     $Invoices = $Invoices->orwhere("task_time_entry.status","unpaid");
            //     $Invoices = $Invoices->orWhere("expense_entry.status","unpaid");
            // });
        }else{
            // $Invoices = $Invoices->where("case_master.created_by",Auth::User()->id);
            $Invoices = $Invoices->whereHas('caseStaffAll', function($query) {
                $query->where('user_id', auth()->id());
            });
        }
        //Filters
        if($request->practice_area_id != 'all') {
            $Invoices = $Invoices->where("case_master.practice_area", $request->practice_area_id);
        }
        if($request->lead_attorney_id != "all") {
            $Invoices = $Invoices->where("case_staff.lead_attorney", $request->lead_attorney_id);
        }
        if($request->firm_office_id != 'all') {
            $Invoices = $Invoices->where("case_master.case_office", $request->firm_office_id);
        }
        if($request->billing_method != 'all') {
            $Invoices = $Invoices->where("case_master.billing_method", $request->billing_method);
        }
        if($request->balance_filter != 'all') {
            if($request->balance_filter == "uninvoiced") {
                // $Invoices = $Invoices->whereDoesntHave("invoices");
                // $Invoices = $Invoices->where("task_time_entry.status","unpaid")
                // ->orWhere("expense_entry.status","unpaid")->orWhere("flat_fee_entry.status","unpaid");
            } else {
                $Invoices = $Invoices->whereHas("invoices", function($query) {
                    $query->havingRaw('SUM(due_amount) > ?', array(0));
                });
            }
        }
        $totalData=$Invoices->count();
        
        $Invoices = $Invoices->offset($requestData['start'])->limit($requestData['pageLength']);
        $Invoices = $Invoices->orderBy("case_master.id", 'desc');
        $Invoices = $Invoices->groupBy("case_master.id");
        $Invoices = $Invoices->get()->each
            ->setAppends(["payment_plan_active_for_case", "last_invoice", "fee_structure", "practice_area_filter", "unpaid_balance", "setup_billing", "uninvoiced_balance"]);
        // dd($Invoices); 
        $arrData = [];
        $contactGroup = [];
        foreach ($Invoices as $k=>$v){
            if($v->setup_billing =="yes"){
                array_push($contactGroup,$v->contact_name);
                if(in_array($v->contact_name,$contactGroup))
                {
                    $arrData[$v->contact_name.'_'.$v->selected_user][$k] = $v;            
                }
            }else{
                array_push($contactGroup,"");            
                if(in_array("",$contactGroup))
                {
                    $arrData["No Billing Contact"][$k] = $v;            
                }
            }
                        
        }
        $arrData = array_map('array_values', $arrData);

        $json_data = array(           
            "recordsTotal"    => intval( $totalData ),             
            "data"            => $arrData 
        );
        echo json_encode($json_data);  
    }

    public function getInvoicePendingCase()
    {
        $getChildUsers=$this->getParentAndChildUserIds();

        $FlatFeeEntryIds = CaseMaster::join("flat_fee_entry","flat_fee_entry.case_id","=","case_master.id")
        ->select("case_master.id","flat_fee_entry.case_id")->where("flat_fee_entry.status","unpaid")->whereNull("flat_fee_entry.deleted_at")->whereIn("case_master.created_by",$getChildUsers)->get()->pluck('case_id')->toArray();
        
        $TaskTimeEntryIds = CaseMaster::join("task_time_entry","task_time_entry.case_id","=","case_master.id")
        ->select("case_master.id","task_time_entry.case_id")->where("task_time_entry.status","unpaid")->whereNull("task_time_entry.deleted_at")->whereIn("case_master.created_by",$getChildUsers)->get()->pluck('case_id')->toArray();
        
        $ExpenseEntryIds = CaseMaster::join("expense_entry","expense_entry.case_id","=","case_master.id")
        ->select("case_master.id","expense_entry.case_id")->where("expense_entry.status","unpaid")->whereNull("expense_entry.deleted_at")->whereIn("case_master.created_by",$getChildUsers)->get()->pluck('case_id')->toArray();
        
        $uniqueCase=array_unique(array_merge($FlatFeeEntryIds,$TaskTimeEntryIds,$ExpenseEntryIds));
        return $uniqueCase;
    }

    public function newInvoiceScratch(Request $request)
    {
        // return $request->all();
        $id=Auth::user()->id;
         $user = User::find($id);
         $from_date='';
         $bill_to_date='';
         $filterByDate='';
        if(!empty($user)){
            //Get all client related to firm
            // $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
            $ClientList = userClientList();
            //Get all company related to firm
            // $CompanyList = User::select("email","first_name","last_name","id","user_level")->where('user_level',4)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
            $CompanyList = userCompanyList();
            $case_id=$request->court_case_id;
            $caseClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$case_id)->select("*")->first();
          
            // $client_id= Session::get('clientId');
            $client_id = '';
            if($request->court_case_id) {
                $case = CaseClientSelection::where("case_id", $request->court_case_id)->where("is_billing_contact", "yes")->first();
                if($case) {
                    $client_id = $case->selected_user;
                }
            }
            if($request->contact){
                $client_id = $request->contact;
            }
            $userData=User::find($client_id);
            // $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$client_id)->first();

            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
            ->where("user_id",$client_id)
            ->first();

            TaskTimeEntry::where('status','unpaid')->where('token_id','9999999')->delete();
            ExpenseEntry::where('status','unpaid')->where('token_id','9999999')->delete();
            FlatFeeEntry::where('status','unpaid')->where('token_id','9999999')->delete();

            $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();

            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.selected_user",$client_id)->get()->pluck("case_id");

            //List all case by client 
            $caseListByClient = CaseMaster::select("*")->whereIn('case_master.id',$caseCllientSelection)->select("*")->get();
            
            //Get the case data
            $caseMaster = CaseMaster::find($case_id);

            //Get the Time Entry list
            $TimeEntry=TaskTimeEntry::leftJoin("users","users.id","=","task_time_entry.user_id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select("task_time_entry.*","task_activity.*","users.*","task_time_entry.id as itd")->where("task_time_entry.case_id",$case_id)
            // ->where("task_time_entry.remove_from_current_invoice","no")
            ->where("task_time_entry.status","unpaid");
            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                $TimeEntry=$TimeEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($request->from_date)),date('Y-m-d',strtotime($request->bill_to_date))]);
                $from_date=$request->from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }
            $TimeEntry=$TimeEntry->get();
        
            //Get the Expense Entry list
            $ExpenseEntry=ExpenseEntry::leftJoin("users","users.id","=","expense_entry.user_id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select("expense_entry.*","task_activity.*","users.*","expense_entry.id as eid")->where("expense_entry.case_id",$case_id)
            // ->where("expense_entry.remove_from_current_invoice","no")
            ->where("expense_entry.status","unpaid");
            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                $ExpenseEntry=$ExpenseEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($request->from_date)),date('Y-m-d',strtotime($request->bill_to_date))]);
                $from_date=$request->from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }
            $ExpenseEntry=$ExpenseEntry->get();

            //Get the Adjustment list
            $InvoiceAdjustment=InvoiceAdjustment::select("*")
            ->where("invoice_adjustment.case_id",$case_id)
            ->where("invoice_adjustment.token",$request->token)
            ->get();


            $maxInvoiceNumber = DB::table("invoices")->max("id") + 1;

            $adjustment_token=$request->token;

            $selectedClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$case_id)->select("*")->get();
            $selectedClient = User::where('user_level',2)->whereIn('id',$selectedClient->pluck('selected_user'))->first();

            $invoiceSetting = getInvoiceSetting();
            
            return view('billing.invoices.scratch_invoices',compact('ClientList','CompanyList','client_id','case_id','caseListByClient','caseMaster','TimeEntry','ExpenseEntry','InvoiceAdjustment','userData','UsersAdditionalInfo','getAllClientForSharing','maxInvoiceNumber','adjustment_token','from_date','bill_to_date','filterByDate','selectedClient', 'invoiceSetting'));
        }else{
            return view('pages.404');
        }
    }
    public function newInvoice(Request $request)
    {
        // return $request->all();
        $id=Auth::user()->id;
         $user = User::find($id);
         $from_date='';
         $bill_to_date='';
         $filterByDate='';
        $tempInvoiceToken = $request->temp_invoice_token;
        /* if(!$request->temp_invoice_token) {
            $tempInvoiceToken = round(microtime(true) * 1000);
        } */        
        if(!$request->contact){
            return redirect('bills/invoices/new');
        }
        if(!empty($user)){
            //Get all client related to firm
            // $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
            $ClientList = userClientList();
            //Get all company related to firm
            // $CompanyList = User::select("email","first_name","last_name","id","user_level")->whereIn("user_status",[1,2])->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
            $CompanyList = userCompanyList();
        
            $case_id=$request->court_case_id;
            $caseClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$case_id)->select("*")->first(); //->where('case_client_selection.is_billing_contact','yes')
          
            $client_id=$request->contact;
            if($client_id==""){
                $client_id=$caseClient['selected_user'] ?? "";
            }
            $userData=User::find($client_id);
            // $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$client_id)->first();

            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
            ->where("user_id",$client_id)
            ->first();

            // if case is none then insert record in case_client_selection for showing client detail
            if($case_id == "none"){
                CaseClientSelection::updateOrcreate([
                    'case_id' => 0,
                    'selected_user' => $client_id,
                ],[
                    'case_id' => 0,
                    'selected_user' => $client_id,
                    'created_by' => Auth::user()->id, 
                ]);
                $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users_additional_info.multiple_compnay_id","case_client_selection.is_billing_contact")->where("case_client_selection.case_id",0)->where("case_client_selection.selected_user",$client_id)->get();
            }else{
                $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users_additional_info.multiple_compnay_id","case_client_selection.is_billing_contact")->where("case_client_selection.case_id",$case_id)->get();
            }
            
            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.selected_user",$client_id)->get()->pluck("case_id");

            //List all case by client 
            $caseListByClient = CaseMaster::select("id", "case_title")->whereIn('case_master.id',$caseCllientSelection);
            if(auth()->user()->parent_user != 0) {
                $caseListByClient = $caseListByClient->whereHas('caseStaffAll', function($query) {
                    $query->where('user_id', auth()->id());
                });
            }
            $caseListByClient = $caseListByClient->get();
            
            //Get the case data
            $caseMaster = CaseMaster::whereId($case_id)->with('caseBillingClient', 'caseAllClient')->first();
            
            // if(!empty($caseClient) && $caseClient->case_id > 0 && $caseClient->uninvoiced_balance == '$0.00'){
            //     FlatFeeEntry::where('case_id', $case_id)->where("status","unpaid")->delete();
            //     ExpenseEntry::where('case_id', $case_id)->where("status","unpaid")->delete();
            //     TaskTimeEntry::where('case_id', $case_id)->where("status","unpaid")->delete();
            // }
            
            //Get the Time Entry list
            if(isset($request->adjustment_delete) && $request->adjustment_delete!=""){
                $TimeEntry=TaskTimeEntry::where("task_time_entry.case_id",$case_id)
                ->where("task_time_entry.status","unpaid")
                ->where(function($TimeEntry) use($request){
                    $TimeEntry->where("task_time_entry.token_id",$request->token);
                    $TimeEntry->orwhere("task_time_entry.token_id","9999999");
                });
                $TimeEntry=$TimeEntry->delete();
            }
            $TimeEntry=TaskTimeEntry::leftJoin("users","users.id","=","task_time_entry.user_id")
            ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
            ->select("task_time_entry.*","task_activity.*","users.*","task_time_entry.id as itd")
            ->where("task_time_entry.case_id",$case_id)
            ->where("task_time_entry.status","unpaid")
            ->where(function($TimeEntry) use($request){
                $TimeEntry->where("task_time_entry.token_id","!=",$request->token);
                $TimeEntry->orwhere("task_time_entry.token_id",NULL);
            });

            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                // $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->from_date))))), auth()->user()->user_timezone ?? 'UTC')));
                // $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_to_date))))), auth()->user()->user_timezone ?? 'UTC')));
                // $TimeEntry=$TimeEntry->whereBetween('entry_date', [$startDt,$endDt]);
                $from_date=$request->from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }
            $TimeEntry=$TimeEntry->get();
        
            //Get the Expense Entry list
            if(isset($request->adjustment_delete) && $request->adjustment_delete!=""){
                $ExpenseEntry=ExpenseEntry::where("expense_entry.case_id",$case_id)
                ->where("expense_entry.status","unpaid")
                ->where(function($ExpenseEntry) use($request){
                    $ExpenseEntry->where("expense_entry.token_id",$request->token);
                    $ExpenseEntry->orwhere("expense_entry.token_id","9999999");
                });
                $ExpenseEntry=$ExpenseEntry->delete();
            }
            $ExpenseEntry=ExpenseEntry::leftJoin("users","users.id","=","expense_entry.user_id")
            ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
            ->select("expense_entry.*","task_activity.*","users.*","expense_entry.id as eid")
            ->where("expense_entry.case_id",$case_id)
            ->where("expense_entry.status","unpaid")
            ->where(function($ExpenseEntry) use($request){
                $ExpenseEntry->where("expense_entry.token_id","!=",$request->token);
                $ExpenseEntry->orwhere("expense_entry.token_id",NULL);
            });

            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                // $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->from_date))))), auth()->user()->user_timezone ?? 'UTC')));
                // $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_to_date))))), auth()->user()->user_timezone ?? 'UTC')));
                // $ExpenseEntry=$ExpenseEntry->whereBetween('entry_date', [$startDt,$endDt]);
                $from_date=$request->from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }
            $ExpenseEntry=$ExpenseEntry->get();

            //Get Flat fees entry
            if($caseMaster) {
                if($caseMaster->billing_method == "flat" || $caseMaster->billing_method == "mixed") {
                $totalFlatFee = FlatFeeEntry::where('case_id', $case_id)->where("time_entry_billable","yes")->sum('cost');
                // $totalFlatFee = FlatFeeEntry::where('case_id', $case_id)->where("flat_fee_entry.status","unpaid")->where("flat_fee_entry.invoice_link",NULL)->sum('cost');
                    if($totalFlatFee == 0){
                        $totalFlatFee = FlatFeeEntry::where('case_id', $case_id)->where("time_entry_billable","yes")->withTrashed()->sum('cost');
                    }else{
                        $totalFlatFeeDeleted = FlatFeeEntry::where('case_id', $case_id)->where("time_entry_billable","yes")->where("flat_fee_entry.token_id",$request->token)->withTrashed()->sum('cost');
                        if($totalFlatFeeDeleted > 0){
                            $totalFlatFee = $totalFlatFee + $totalFlatFeeDeleted;
                        }
                    }
                    $FlatFeeUnpaidNonBillableSum = FlatFeeEntry::where('case_id', $case_id)->where("flat_fee_entry.status","unpaid")->where("time_entry_billable","no")->sum('cost');
                    if ($FlatFeeUnpaidNonBillableSum == 0) {
                        $remainFlatFee = $caseMaster->billing_amount - $totalFlatFee;
                        if($remainFlatFee > 0) {
                            FlatFeeEntry::create([
                                'case_id' => $caseMaster->id,
                                'user_id' => auth()->id(),
                                'entry_date' => Carbon::now(),
                                'cost' =>  $remainFlatFee,
                                'time_entry_billable' => 'yes',
                                'token_id' => $request->token,
                                'firm_id' => Auth::User()->firm_name,
                                'created_by' => auth()->id(), 
                            ]);
                        }
                    }  
                }       
            }
            if($case_id == "none"){
                // Fix for https://trello.com/c/3M7Dll9D/1032-invoice-from-scratch-matter-none-cant-delete-flat-fee
                $totalFlatFee = FlatFeeEntry::where('case_id', 0)->where('user_id', auth()->id())->where("status","unpaid")->first();
                // if(empty($totalFlatFee)) {
                //     FlatFeeEntry::updateOrcreate([
                //         'case_id' => 0,
                //         'user_id' => auth()->id(),
                //         "status" => "unpaid"
                //     ],[
                //         'case_id' => 0,
                //         'user_id' => auth()->id(),
                //         'entry_date' => Carbon::now(),
                //         'cost' =>  0,
                //         'time_entry_billable' => 'yes',
                //         'token_id' => $request->token,
                //         'created_by' => auth()->id(), 
                //     ]);
                // }
            }
            if(isset($request->adjustment_delete) && $request->adjustment_delete!=""){
                $FlatFeeEntry=FlatFeeEntry::where("flat_fee_entry.case_id",$case_id)
                ->where("flat_fee_entry.user_id",auth()->id())
                ->where("flat_fee_entry.invoice_link",NULL)
                ->where("flat_fee_entry.status","unpaid")
                ->where(function($FlatFeeEntry) use($request){
                    $FlatFeeEntry->where("flat_fee_entry.token_id","=",$request->token);
                    $FlatFeeEntry->orwhere("flat_fee_entry.token_id","=",'9999999');
                })->delete();
            }
            $FlatFeeEntry=FlatFeeEntry::leftJoin("users","users.id","=","flat_fee_entry.user_id")->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
            ->where("flat_fee_entry.case_id",$case_id)
            // ->where("flat_fee_entry.user_id",auth()->id())
            ->where("flat_fee_entry.invoice_link",NULL)
            ->where("flat_fee_entry.status","unpaid")
            ->where(function($FlatFeeEntry) use($request){
                $FlatFeeEntry->where("flat_fee_entry.token_id","=",$request->token);
                $FlatFeeEntry->orwhere("flat_fee_entry.token_id",NULL);
                $FlatFeeEntry->orwhere("flat_fee_entry.token_id","=",'9999999');
            });
            
            // if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
            //     $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->from_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_to_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $FlatFeeEntry=$FlatFeeEntry->whereBetween('entry_date', [$startDt,$endDt]);
            // }
            $FlatFeeEntry=$FlatFeeEntry->get();
            
            //Get the Adjustment list            
            if(isset($request->adjustment_delete) && $request->adjustment_delete!=""){
                $InvoiceAdjustment=InvoiceAdjustment::select("*")
                ->where("invoice_adjustment.case_id",$case_id)
                ->where("invoice_adjustment.token",$request->token)->delete();
            }
            $InvoiceAdjustment=InvoiceAdjustment::select("*")
            ->where("invoice_adjustment.case_id",$case_id)
            ->where("invoice_adjustment.token",$request->token)->get();

            $maxInvoiceNumber = DB::table("invoices")->max("id") + 1;

            $adjustment_token=$request->token;

            // Get unpaid balances invoices list
            $unpaidInvoices = [];
            if($caseMaster) {
                $unpaidInvoices = Invoices::where("case_id", $caseMaster->id)->where("due_amount", ">", 0)->where("status", "!=", "Forwarded")->get();
            }
            $invoiceSetting = getInvoiceSetting();
            $arrSetting = [
                'bill_payment_terms' => $request->bill_payment_terms,
                'invoice_number_padded' => $request->invoice_number_padded,
                'bill_sent_status' => $request->bill_sent_status,
                'bill_invoice_date' => $request->bill_invoice_date
            ];
            $customizSetting = getCustomizeSetting();
            $invoiceTempInfo = InvoiceTempInfo::where('invoice_unique_id', $request->token)->where('case_id', $request->court_case_id)->get();
            return view('billing.invoices.new_invoices',compact('ClientList','CompanyList','client_id','case_id','caseListByClient','caseMaster','TimeEntry','ExpenseEntry','InvoiceAdjustment','userData','UsersAdditionalInfo','getAllClientForSharing','maxInvoiceNumber','adjustment_token','from_date','bill_to_date','filterByDate','FlatFeeEntry', 'tempInvoiceToken', 'unpaidInvoices', 'invoiceSetting', 'arrSetting', 'customizSetting', 'invoiceTempInfo'));
        }else{
            return view('pages.404');
        }
    }

    public function saveInvoiceTempInfo(Request $request)
    {
        // return $request->all();
        InvoiceTempInfo::updateOrCreate([
            'invoice_unique_id' => $request->invoice_unique_id,
            'client_id' => $request->client_id,
            'case_id' => $request->case_id,
            'account_type' => $request->account_type,
            'trust_account_type' => $request->trust_account_type ?? 'unallocate',
        ], $request->all());
        return 'success';
    }

    public function getCaseList(Request $request)
    {
            $client_id=$request->id;
            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.selected_user",$client_id)->get()->pluck("case_id");
            $caseListByClient = CaseMaster::whereIn("case_master.id",$caseCllientSelection)->where("case_master.is_entry_done","1")->select("*","case_master.id as case_id")->orderBy("id","DESC")->get();
            return view('billing.invoices.caseListReaload',compact('caseListByClient'));
    }
    public function deleteTimeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'time_entry_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=$request->time_entry_id;
            $TaskTimeEntry = TaskTimeEntry::where("id", $id)->first();
            if(!empty($TaskTimeEntry)){
                if($TaskTimeEntry->rate_type == 'flat'){
                    $totalTime = str_replace(",","",$TaskTimeEntry->duration);
                }else{
                    $totalTime = str_replace(",","",$TaskTimeEntry->entry_rate) * $TaskTimeEntry->duration;
                }             
                if($TaskTimeEntry->invoice_link == null){ 
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $TaskTimeEntry->case_id)->where('token',$request->token_id)->get(); 
                }else{ 
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $TaskTimeEntry->case_id)->where('invoice_id',$TaskTimeEntry->invoice_link)->get(); 
                    if(count($InvoiceAdjustment) == 0){
                        $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($TaskTimeEntry->invoice_link))->get();
                    }
                }
                if(count($InvoiceAdjustment) >0){ 
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'time_entries'){
                        $invoiceAdjustTotal = $v->basis - $totalTime;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }}
            }
            TimeEntryForInvoice::where("time_entry_id", $id)->delete();
            if($request->action=="delete"){               
                $TaskTimeEntry->delete();
            }else{
                // TaskTimeEntry::where('id',$id)->update(['remove_from_current_invoice'=>'yes']);
                TaskTimeEntry::where('id',$id)->update([
                    'status'=>'unpaid',
                    'invoice_link' => null,
                    'token_id'=>$request->token_id
                ]);
            }
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function deleteFlatFeeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'flat_fee_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
           
            $id=$request->flat_fee_id;
            $FlatFeeEntry = FlatFeeEntry::where("id", $id)->first();
            if(!empty($FlatFeeEntry)){                
                $totalTime = str_replace(",","",$FlatFeeEntry->cost);
                if($FlatFeeEntry->invoice_link == null){ 
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $FlatFeeEntry->case_id)->where('token',$request->token_id)->get(); 
                }else{ 
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $FlatFeeEntry->case_id)->where('invoice_id',$FlatFeeEntry->invoice_link)->get(); 
                    if(count($InvoiceAdjustment) == 0){
                        $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($FlatFeeEntry->invoice_link))->get();
                    }
                }
                if(count($InvoiceAdjustment) >0){ 
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'flat_fees'){
                        $invoiceAdjustTotal = $v->basis - $totalTime;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100;
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        } 
                    }
                }}
            }
            FlatFeeEntryForInvoice::where("flat_fee_entry_id", $id)->delete();
            if($request->action=="delete"){                
                $FlatFeeEntry->token_id = $request->token_id;
                $FlatFeeEntry->deleted_at = date('Y-m-d h:i:s');
                $FlatFeeEntry->save();
            }else{
                FlatFeeEntry::where('id',$id)->update([
                    'status'=>'unpaid',
                    'invoice_link' => null,
                    'token_id'=>$request->token_id
                ]);
            }
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function deleteAllTimeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            // return $request->all();
            $id=base64_decode($request->case_id);
            TaskTimeEntry::where('case_id',$id)->where('status','unpaid')->update(['token_id'=>$request->token_id]);            
            if($request->invoice_id){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('applied_to','sub_total')->where("token",base64_encode($request->invoice_id))->get();
                if(count($InvoiceAdjustment) > 0){                
                    $totalTime = str_replace(",","",$request->total);
                    foreach($InvoiceAdjustment as $k=>$v){                        
                        $invoiceAdjustTotal = $totalTime - $v->basis;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100;
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }
                TaskTimeEntry::where('case_id',$id)->where('invoice_link',$request->invoice_id)->delete();
                TimeEntryForInvoice::where("invoice_id", $request->invoice_id)->delete();
                InvoiceAdjustment::where('ad_type','percentage')->where('applied_to','time_entries')->where("token",$request->token_id)->delete();
            }
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function deleteAllFlatFeeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
        
            $id=base64_decode($request->case_id);
            FlatFeeEntry::where('case_id',$id)->where('status','unpaid')->update(['token_id'=>$request->token_id]);            
            if($request->invoice_id){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('applied_to','sub_total')->where("token",base64_encode($request->invoice_id))->get();
                if(count($InvoiceAdjustment) > 0){                
                    $totalTime = str_replace(",","",$request->total);
                    foreach($InvoiceAdjustment as $k=>$v){                        
                        $invoiceAdjustTotal = $totalTime - $v->basis;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100;
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }                     
                    }
                }
                FlatFeeEntry::where('case_id',$id)->where('status','paid')->delete();
                FlatFeeEntryForInvoice::where("invoice_id", $request->invoice_id)->delete();
                InvoiceAdjustment::where('ad_type','percentage')->where('applied_to','flat_fees')->where("token",$request->token_id)->delete();
            }
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function deleteAllExpenseEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=base64_decode($request->case_id);
            // ExpenseEntry::where("case_id", $id)->delete();
            ExpenseEntry::where('case_id',$id)->where('status','unpaid')->update(['token_id'=>$request->token_id]);
            
            if($request->invoice_id){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('applied_to','sub_total')->where("token",base64_encode($request->invoice_id))->get();
                if(count($InvoiceAdjustment) > 0){                
                    $totalTime = str_replace(",","",$request->total);
                    foreach($InvoiceAdjustment as $k=>$v){                        
                        $invoiceAdjustTotal = $totalTime - $v->basis;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100;
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }                       
                    }
                }
                ExpenseForInvoice::where("invoice_id", $request->invoice_id)->delete();
                ExpenseEntry::where('case_id',$id)->where('status','paid')->delete();
                InvoiceAdjustment::where('ad_type','percentage')->where('applied_to','expenses')->where("token",$request->token_id)->delete();
            }
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function addSingleFlatFeeEntry(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){
                $invoice_id=$request->invoice_id;
                $adjustment_token = $request->adjustment_token;
                if($request->id=="0"){
                    $case_id=0;
                }else{
                    $case_id=base64_decode($request->id);
                }
                $CaseMasterData = CaseMaster::find($case_id);
                // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $loadFirmStaff = firmUserList();
                // $invoice_token = $request->invoice_token;
                return view('billing.invoices.addSingleFlatFeeEntryPopup',compact('CaseMasterData','loadFirmStaff','case_id','invoice_id','adjustment_token'/* , 'invoice_token' */));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }
    public function saveSingleFlatFeeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'staff_user' => 'required|numeric',
            'case_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $FlatFeeEntry = new FlatFeeEntry;
            $FlatFeeEntry->case_id =($request->case_id)??0;
            $FlatFeeEntry->user_id =$request->staff_user;
            $FlatFeeEntry->firm_id =auth()->user()->firm_name;
            if(isset($request->invoice_id)){
                $FlatFeeEntry->invoice_link =$request->invoice_id;
            }
            $FlatFeeEntry->description=$request->case_description;
            $FlatFeeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $FlatFeeEntry->time_entry_billable='yes';
            $FlatFeeEntry->cost=str_replace(",","",$request->rate_field_id);
            $FlatFeeEntry->created_by=Auth::User()->id; 
            $FlatFeeEntry->token_id=9999999; 
            $FlatFeeEntry->save();

            if(isset($request->invoice_id)){
                $FlatFeeEntryForInvoice=new FlatFeeEntryForInvoice;
                $FlatFeeEntryForInvoice->invoice_id=$FlatFeeEntry->invoice_link;                    
                $FlatFeeEntryForInvoice->flat_fee_entry_id=$FlatFeeEntry->id;
                $FlatFeeEntryForInvoice->created_by=Auth::User()->id; 
                $FlatFeeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                $FlatFeeEntryForInvoice->save();
            }
            // update adjustment entry
            if($request->invoice_id){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("invoice_id",$request->invoice_id)->get();
                if(count($InvoiceAdjustment) == 0){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($request->invoice_id))->get();
                }
            }else{
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",$request->adjustment_token)->get();
            }
            if(count($InvoiceAdjustment) > 0){                
                $totalTime = str_replace(",","",$request->rate_field_id);
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'flat_fees'){
                        $invoiceAdjustTotal = $totalTime + $v->basis;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100;
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }
            }
            return response()->json(['errors'=>'','id'=>$FlatFeeEntry->id]);
            exit;
        }
    } 

    public function editSingleFlatFeeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){
                $invoice_id=$request->invoice_id;
               
                // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $loadFirmStaff = firmUserList();

                $FlatFeeEntry = FlatFeeEntry::find($request->id);
                $case_id=$FlatFeeEntry['case_id'];
                $CaseMasterData = CaseMaster::find($case_id);                
                return view('billing.invoices.editSingleFlatFeeEntryPopup',compact('CaseMasterData','loadFirmStaff','case_id','invoice_id','FlatFeeEntry'));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }
    public function updateSingleFlatFeeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'staff_user' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $FlatFeeEntry = FlatFeeEntry::find($request->activity_id);
            if(!empty($FlatFeeEntry)){
                $totalTime = str_replace(",","",$FlatFeeEntry->cost);
                $totalNewTime = str_replace(",","",$request->rate_field_id);                
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $FlatFeeEntry->case_id)->where('invoice_id',$FlatFeeEntry->invoice_link)->get();  
                if(count($InvoiceAdjustment) == 0){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($FlatFeeEntry->invoice_link))->get();
                }
                if(count($InvoiceAdjustment) >0){
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'flat_fees'){
                        $invoiceAdjustTotal = $v->basis + $totalNewTime - $totalTime;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100;
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }}
            }
            $FlatFeeEntry->user_id =$request->staff_user;
            $FlatFeeEntry->description=$request->case_description;
            $FlatFeeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $FlatFeeEntry->time_entry_billable='yes';
            $FlatFeeEntry->cost=str_replace(",","",$request->rate_field_id);
            $FlatFeeEntry->updated_by=Auth::User()->id; 
            $FlatFeeEntry->save();
            return response()->json(['errors'=>'','id'=>$FlatFeeEntry->id]);
            exit;
        }
    } 

    public function addSingleTimeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){
                $invoice_id=$request->invoice_id;
                $case_id=base64_decode($request->id);
                $adjustment_token = $request->adjustment_token;

                $CaseMasterData = CaseMaster::find($case_id);
                
                $caseStaffRates = [];
                $defaultRate=0.00;
                $caseStaffData = CaseStaff::select("*")->where("case_id",$case_id)->get();
                if(count($caseStaffData) > 0){
                    foreach($caseStaffData as $k => $v){
                        if($v->rate_type == "0"){
                            $defaultRateUser = DB::table('users')->select("default_rate")->where("id",$v->user_id)->first();
                            $caseStaffRates[$v->user_id] = number_format($defaultRateUser->default_rate??0 ,2);            
                        }else{
                            $caseStaffRates[$v->user_id] = number_format($v->rate_amount,2);
                        }
                        if($v->user_id == Auth::User()->id){
                            $defaultRate = number_format($v->rate_amount,2);          
                        }
                    }
                }  
                // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $loadFirmStaff = firmUserList();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.addSingleTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','case_id','invoice_id','adjustment_token','caseStaffRates'));     
                exit; 
            
            }else{
                return view('pages.404');
            }
        }
    }
    public function saveSingleTimeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'staff_user' => 'required|numeric',
            'case_id' => 'nullable|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $TaskTimeEntry = new TaskTimeEntry;
            $TaskTimeEntry->case_id =($request->case_id)??0;
            $TaskTimeEntry->user_id =$request->staff_user;
            $TaskTimeEntry->firm_id =auth()->user()->firm_name;
            if(isset($request->activity_text)){
                $TaskAvtivity = new TaskActivity;
                $TaskAvtivity->title=$request->activity_text;
                $TaskAvtivity->status="1";
                $TaskAvtivity->firm_id=Auth::User()->firm_name; 
                $TaskAvtivity->created_by=Auth::User()->id; 
                $TaskAvtivity->save();
                $TaskTimeEntry->activity_id=$TaskAvtivity->id;
            }else{
                $TaskTimeEntry->activity_id=$request->activity;
            }
            if($request->time_tracking_enabled=="on"){
                $TaskTimeEntry->time_entry_billable="yes";
            }else{
                $TaskTimeEntry->time_entry_billable="no";
            }
            $TaskTimeEntry->description=$request->case_description;
            $TaskTimeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $TaskTimeEntry->entry_rate=str_replace(",","",$request->rate_field_id);
            $TaskTimeEntry->rate_type=$request->rate_type_field_id;
            $TaskTimeEntry->duration =$request->duration_field;
            $TaskTimeEntry->token_id=9999999; 
            $TaskTimeEntry->created_by=Auth::User()->id; 
            $TaskTimeEntry->save();

            if(isset($request->invoice_id) && $request->invoice_id!=""){
                $TimeEntryForInvoice=new TimeEntryForInvoice;
                $TimeEntryForInvoice->invoice_id=$request->invoice_id;                    
                $TimeEntryForInvoice->time_entry_id=$TaskTimeEntry->id;
                $TimeEntryForInvoice->created_by=Auth::User()->id; 
                $TimeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                $TimeEntryForInvoice->save();
            }
            
            // update adjustment entry
            if(isset($request->invoice_id) && $request->invoice_id!=""){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("invoice_id",$request->invoice_id)->get();
                if(count($InvoiceAdjustment) == 0){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($request->invoice_id))->get();
                }
            }else{
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",$request->adjustment_token)->get();
            }            
            if(count($InvoiceAdjustment) > 0){
                if($request->rate_type_field_id == 'flat'){
                    $totalTime = str_replace(",","",$request->duration_field);
                }else{
                    $totalTime = str_replace(",","",$request->rate_field_id) * $request->duration_field;
                }
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'time_entries'){
                        $invoiceAdjustTotal = $totalTime + $v->basis;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }
            }

            //Add time entry history
            $data=[];
            $data['case_id']=$TaskTimeEntry->case_id;
            $data['user_id']=$TaskTimeEntry->user_id;
            $data['activity']='added a time entry';
            $data['activity_for']=$TaskTimeEntry->id;
            $data['expense_id']=$TaskTimeEntry->id;
            $data['type']='time_entry';
            $data['action']='add';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
        exit;
        }
    } 

    public function editSingleTimeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){

                $TaskTimeEntry=TaskTimeEntry::find($request->id);
                $case_id=$TaskTimeEntry['case_id'];

                $CaseMasterData = CaseMaster::find($case_id);                
                $caseStaffRates = [];
                $defaultRate=0.00;
                $caseStaffData = CaseStaff::select("*")->where("case_id",$case_id)->get();
                if(count($caseStaffData) > 0){
                    foreach($caseStaffData as $k => $v){
                        if($v->rate_type == "0"){
                            $defaultRateUser = DB::table('users')->select("default_rate")->where("id",$v->user_id)->first();
                            $caseStaffRates[$v->user_id] = number_format($defaultRateUser->default_rate??0 ,2);            
                        }else{
                            $caseStaffRates[$v->user_id] = number_format($v->rate_amount,2);
                        }
                        if($v->user_id == Auth::User()->id){
                            $defaultRate = number_format($v->rate_amount,2);          
                        }
                    }
                }   
                // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $loadFirmStaff = firmUserList();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.editSingleTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','case_id','TaskTimeEntry','caseStaffRates'));     
                exit; 
            
            }else{
                return view('pages.404');
            }
        }
    }

    public function updateSingleTimeEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'staff_user' => 'required|numeric',
            // 'case_id' => 'required|numeric',
            'activity_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskTimeEntry = TaskTimeEntry::find($request->activity_id);           
            if(!empty($TaskTimeEntry)){
                if($TaskTimeEntry->rate_type == 'flat'){
                    $totalTime = str_replace(",","",$TaskTimeEntry->duration);                    
                }else{
                    $totalTime = str_replace(",","",$TaskTimeEntry->entry_rate) * $TaskTimeEntry->duration;
                }                
                if($request->rate_type_field_id == 'flat'){
                    $totalNewTime = str_replace(",","",$request->duration_field);
                }else{
                    $totalNewTime = str_replace(",","",$request->rate_field_id) * $request->duration_field;
                }
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $TaskTimeEntry->case_id)->where('invoice_id',$TaskTimeEntry->invoice_link)->get();
                if(count($InvoiceAdjustment) == 0){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($TaskTimeEntry->invoice_link))->get();
                }
                if(count($InvoiceAdjustment) > 0){  
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'time_entries'){
                        $invoiceAdjustTotal = $v->basis + $totalNewTime - $totalTime;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }}
            }
            $TaskTimeEntry->user_id =$request->staff_user;
            if(isset($request->activity_text)){
                $TaskAvtivity = new TaskActivity;
                $TaskAvtivity->title=$request->activity_text;
                $TaskAvtivity->status="1";
                $TaskAvtivity->firm_id=Auth::User()->firm_name; 
                $TaskAvtivity->created_by=Auth::User()->id; 
                $TaskAvtivity->save();
                $TaskTimeEntry->activity_id=$TaskAvtivity->id;
            }else{
                $TaskTimeEntry->activity_id=$request->activity;
            }
            if($request->time_tracking_enabled=="on"){
                $TaskTimeEntry->time_entry_billable="yes";
            }else{
                $TaskTimeEntry->time_entry_billable="no";
            }
            $TaskTimeEntry->description=$request->case_description;
            $TaskTimeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $TaskTimeEntry->entry_rate=str_replace(",","",$request->rate_field_id);
            $TaskTimeEntry->rate_type=$request->rate_type_field_id;
            $TaskTimeEntry->duration =$request->duration_field;
            $TaskTimeEntry->updated_by=Auth::User()->id; 
            $TaskTimeEntry->save();


            
            //Add time entory history
            $data=[];
            $data['case_id']=$TaskTimeEntry->case_id;
            $data['user_id']=$TaskTimeEntry->user_id;
            $data['activity']='updated a time entry';
            $data['activity_for']=$TaskTimeEntry->activity_id;
            $data['time_entry_id']=$TaskTimeEntry->id;
            $data['type']='time_entry';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            //Case Activity
            $activity_text = TaskActivity::find($TaskTimeEntry->activity_id);
            $data=[];
            $data['activity_title']='updated a time entry';
            $data['case_id']=$TaskTimeEntry->case_id;
            $data['activity_type']='';
            $data['extra_notes']=$activity_text->title ?? NUll;
            $this->caseActivity($data);
            
            return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
        exit;
        }
    } 

    //Expense entry
    
    public function deleteExpenseEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'expense_entry_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
           
            $id=$request->expense_entry_id;
            $ExpenseEntry = ExpenseEntry::where("id", $id)->first();
            if(!empty($ExpenseEntry)){                
                $totalTime = str_replace(",","",$ExpenseEntry->cost) * str_replace(",","",$ExpenseEntry->duration);
                if($ExpenseEntry->invoice_link == null){
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $ExpenseEntry->case_id)->where('token',$request->token_id)->get();  
                }else{
                    $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $ExpenseEntry->case_id)->where('invoice_id',$ExpenseEntry->invoice_link)->get();  
                    if(count($InvoiceAdjustment) == 0){
                        $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($ExpenseEntry->invoice_link))->get();
                    }
                }    
                if(count($InvoiceAdjustment) > 0){
                foreach($InvoiceAdjustment as $k=>$v){
                    if($v->applied_to == 'sub_total' || $v->applied_to == 'expenses'){
                        $invoiceAdjustTotal = $v->basis - $totalTime;
                        $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        if($invoiceAmount <= 0){
                            InvoiceAdjustment::where("id",$v->id)->delete();
                        }else{                             
                            InvoiceAdjustment::where("id",$v->id)->update([
                                'basis' => $invoiceAdjustTotal,
                                'amount'=> $invoiceAmount
                            ]);
                        }
                    }
                }}
            }

            ExpenseForInvoice::where("expense_entry_id", $id)->delete();
            if($request->action=="delete"){
                $ExpenseEntry->delete();
            }else{
                ExpenseEntry::where('id',$id)->update([
                    'status'=>'unpaid',
                    'invoice_link' => null,
                    'token_id'=>$request->token_id
                ]);
            }
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }
    public function addSingleExpenseEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){
                $invoice_id=$request->invoice_id;
                $case_id=base64_decode($request->id);
                $adjustment_token = $request->adjustment_token;

                $defaultRate='';
                $CaseMasterData = CaseMaster::find($case_id);

                // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $loadFirmStaff = firmUserList();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.addSingleExpenseEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','case_id','invoice_id','adjustment_token'));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }

    public function saveSingleExpenseEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
        //   'case_id' => 'required',
          'case_id' => 'nullable',
          'staff_user' => 'required',
      ],['case_or_lead.required'=>'Case can\'t be blank',
      'staff_user.required'=>'User can\'t be blank']);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
        $ExpenseEntry = new ExpenseEntry;
        $ExpenseEntry->case_id =($request->case_id)??0;
        $ExpenseEntry->user_id =$request->staff_user;
        $ExpenseEntry->firm_id = Auth::User()->firm_name;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            
            $TaskAvtivity->firm_id=Auth::User()->firm_name;
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->save();
            $ExpenseEntry->activity_id=$TaskAvtivity->id;
        }else{
            $ExpenseEntry->activity_id=$request->activity;
        }
        if($request->time_tracking_enabled=="on"){
            $ExpenseEntry->time_entry_billable="yes";
        }else{
            $ExpenseEntry->time_entry_billable="no";
        }
        $ExpenseEntry->description=$request->case_description;
        $ExpenseEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
        $ExpenseEntry->cost=str_replace(",","",$request->rate_field_id);
        $ExpenseEntry->duration =$request->duration_field;
        $ExpenseEntry->token_id=9999999; 
        $ExpenseEntry->created_at=date('Y-m-d h:i:s'); 
        $ExpenseEntry->created_by=Auth::User()->id; 
        $ExpenseEntry->save();

        if(isset($request->invoice_id) && $request->invoice_id!=""){
            $ExpenseEntryForInvoice=new ExpenseForInvoice;
            $ExpenseEntryForInvoice->invoice_id=$request->invoice_id;                    
            $ExpenseEntryForInvoice->expense_entry_id =$ExpenseEntry->id;
            $ExpenseEntryForInvoice->created_by=Auth::User()->id; 
            $ExpenseEntryForInvoice->created_at=date('Y-m-d h:i:s');
            $ExpenseEntryForInvoice->save();
        }
           
        // update adjustment entry
        if($request->invoice_id){
            $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("invoice_id",$request->invoice_id)->get();
            if(count($InvoiceAdjustment) == 0){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($request->invoice_id))->get();
            }
        }else{
            $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",$request->adjustment_token)->get();
        }
        if(count($InvoiceAdjustment) > 0){
            $totalExpense = str_replace(",","",$request->rate_field_id) * str_replace(",","",$request->duration_field);
            foreach($InvoiceAdjustment as $k=>$v){
                if($v->applied_to == 'sub_total' || $v->applied_to == 'expenses'){
                    $invoiceAdjustTotal = $totalExpense + $v->basis;
                    $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                    if($invoiceAmount <= 0){
                        InvoiceAdjustment::where("id",$v->id)->delete();
                    }else{                             
                        InvoiceAdjustment::where("id",$v->id)->update([
                            'basis' => $invoiceAdjustTotal,
                            'amount'=> $invoiceAmount
                        ]);
                    }
                }
            }
        }

        $data=[];
        $data['case_id']=$request->case_id;
        $data['user_id']=$request->staff_user;
        $data['activity']='added an expense';
        $data['activity_for']=$ExpenseEntry->id;
        $data['expense_id']=$ExpenseEntry->id;

        $data['type']='expenses';
        $data['action']='add';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        //Case Activity
        $activity_text = TaskActivity::find($ExpenseEntry->activity_id);
        $data=[];
        $data['activity_title']='added an expense';
        $data['case_id']=$ExpenseEntry->case_id;
        $data['activity_type']='';
        $data['extra_notes']=$activity_text->title ?? NUll;
        $this->caseActivity($data);

        return response()->json(['errors'=>'','id'=>$ExpenseEntry->id]);
        exit;
      }
    }
    
    public function editSingleExpenseEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){

                $ExpenseEntry=ExpenseEntry::find($request->id);
                $case_id=$ExpenseEntry['case_id'];

                $defaultRate='';
                $CaseMasterData = CaseMaster::find($case_id);

                // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $loadFirmStaff = firmUserList();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.editSingleExpenseEntryPopup',compact('CaseMasterData','loadFirmStaff','ExpenseEntry','defaultRate','case_id','TaskActivity'));     
                exit; 
            
            }else{
                return view('pages.404');
            }
        }
    }

    public function updateSingleExpenseEntry(Request $request)
    {
      $validator = \Validator::make($request->all(), [
        //   'case_id' => 'required',
          'case_id' => 'nullable',
          'staff_user' => 'required',
      ],['case_or_lead.required'=>'Case can\'t be blank',
      'staff_user.required'=>'User can\'t be blank']);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
        $ExpenseEntry =ExpenseEntry::find($request->activity_id);
        if(!empty($ExpenseEntry)){
            $totalTime = str_replace(",","",$ExpenseEntry->cost) * $ExpenseEntry->duration;
            $totalNewTime = str_replace(",","",$request->rate_field_id) * $request->duration_field;
            
            $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $ExpenseEntry->case_id)->where('invoice_id',$ExpenseEntry->invoice_link)->get();  
            if(count($InvoiceAdjustment) == 0){
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where("token",base64_encode($ExpenseEntry->invoice_link))->get();
            }
            if(count($InvoiceAdjustment) >0){
            foreach($InvoiceAdjustment as $k=>$v){
                if($v->applied_to == 'sub_total' || $v->applied_to == 'expenses'){
                    $invoiceAdjustTotal = $v->basis + $totalNewTime - $totalTime;
                    $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                    if($invoiceAmount <= 0){
                        InvoiceAdjustment::where("id",$v->id)->delete();
                    }else{                             
                        InvoiceAdjustment::where("id",$v->id)->update([
                            'basis' => $invoiceAdjustTotal,
                            'amount'=> $invoiceAmount
                        ]);
                    }
                }
            }}
        }
        $ExpenseEntry->user_id =$request->staff_user;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            
            $TaskAvtivity->firm_id=Auth::User()->firm_name;
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->save();
            $ExpenseEntry->activity_id=$TaskAvtivity->id;
        }else{
            $ExpenseEntry->activity_id=$request->activity;
        }
        if($request->time_tracking_enabled=="on"){
            $ExpenseEntry->time_entry_billable="yes";
        }else{
            $ExpenseEntry->time_entry_billable="no";
        }
        $ExpenseEntry->description=$request->case_description;
        $ExpenseEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
        $ExpenseEntry->cost=str_replace(",","",$request->rate_field_id);
        $ExpenseEntry->duration =$request->duration_field;
        $ExpenseEntry->updated_at=date('Y-m-d h:i:s'); 
        $ExpenseEntry->updated_by=Auth::User()->id; 
        $ExpenseEntry->save();
        return response()->json(['errors'=>'','id'=>$ExpenseEntry->id]);
        exit;
      }
    }

    public function addAdjustmentEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=Auth::user()->id;
            $user = User::find($id);
            if(!empty($user)){
                $case_id=base64_decode($request->id);
                $adjustment_token=$request->adjustment_token;
                $CaseMasterData = CaseMaster::find($case_id);
                return view('billing.invoices.addAdjustmentEntryPopup',compact('CaseMasterData','case_id','adjustment_token'));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }
    public function saveAdjustmentEntry(Request $request)
    {
      $validator = \Validator::make($request->all(), [
          'case_id' => 'nullable',
          'item' => 'required',
          'applied_to' => 'required',
          'ad_type' => 'required',
      ]);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
        //   print_r($request->all());exit;
        $InvoiceAdjustment = new InvoiceAdjustment;
        $InvoiceAdjustment->case_id =($request->case_id)??0;
        $InvoiceAdjustment->token =$request->adjustment_token;
        $InvoiceAdjustment->item=$request->item;
        $InvoiceAdjustment->applied_to=$request->applied_to;
        $InvoiceAdjustment->ad_type=$request->ad_type;
        $InvoiceAdjustment->basis =str_replace(",","",$request->basic);
        $InvoiceAdjustment->notes =$request->notes;
        $InvoiceAdjustment->percentages =$request->percentage;
        $InvoiceAdjustment->amount =str_replace(",","",$request->amount);
        $InvoiceAdjustment->created_at=date('Y-m-d h:i:s'); 
        $InvoiceAdjustment->created_by=Auth::User()->id; 
        $InvoiceAdjustment->save();
        return response()->json(['errors'=>'','id'=>$InvoiceAdjustment->id]);
        exit;
      }
    }
    public function editAdjustmentEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $InvoiceAdjustment=InvoiceAdjustment::find($request->id);
            if(!empty($InvoiceAdjustment)){
                return view('billing.invoices.editAdjustmentEntryPopup',compact('InvoiceAdjustment'));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }
    public function updateAdjustmentEntry(Request $request)
    {
      $validator = \Validator::make($request->all(), [
          'id' => 'required',
          'item' => 'required',
          'applied_to' => 'required',
          'ad_type' => 'required',
      ]);
      if ($validator->fails())
      {
          return response()->json(['errors'=>$validator->errors()->all()]);
      }else{
        $InvoiceAdjustment = InvoiceAdjustment::find($request->id);
        $InvoiceAdjustment->item=$request->item;
        $InvoiceAdjustment->applied_to=$request->applied_to;
        $InvoiceAdjustment->ad_type=$request->ad_type;
        $InvoiceAdjustment->basis =str_replace(",","",$request->basic);
        $InvoiceAdjustment->notes =$request->notes;
        $InvoiceAdjustment->percentages =$request->percentage;
        $InvoiceAdjustment->amount =str_replace(",","",$request->amount);
        $InvoiceAdjustment->updated_at=date('Y-m-d h:i:s'); 
        $InvoiceAdjustment->updated_by=Auth::User()->id; 
        $InvoiceAdjustment->save();
        return response()->json(['errors'=>'','id'=>$InvoiceAdjustment->id]);
        exit;
      }
    }
    public function removeAdjustmentEntry(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $InvoiceAdjustment=InvoiceAdjustment::find($request->id);
            if(!empty($InvoiceAdjustment)){
                $InvoiceAdjustment->delete();
                return response()->json(['errors'=>'','item'=>$InvoiceAdjustment->item]);
                exit;
            }else{
                return response()->json(['errors'=>'1','msg'=>"No record found"]);
                exit;
            }
        }
    }

    public function graantAccess(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|unique:users,email,NULL,id,firm_name,'.Auth::User()->firm_name,
            'client_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
       
            $user =User::find($request->client_id);
            $user->email=$request->email;
            $user->token  = Str::random(40);
            $user->save();

            UsersAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"1"]);


            $getTemplateData = EmailTemplate::find(6);
            $fullName=$user->first_name. ' ' .$user->last_name;
            $email=$request->email;
            $token=url('user/verify', $user->token);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{name}', $fullName, $mail_body);
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{token}', $token,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
            $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
            $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
            $mail_body = str_replace('{refuser}', Auth::User()->first_name, $mail_body);                          
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);       
            $refUser = Auth::User()->first_name . " ". Auth::User()->last_name;
            $userEmail = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $refUser." ".$getTemplateData->subject. " ". TITLE,
                "to" => $request->email,
                "full_name" => $fullName,
                "mail_body" => $mail_body
                ];
            $sendEmail = $this->sendMail($userEmail);
            if($sendEmail=="1"){
                $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
                $user->save();
            }
            return response()->json(['errors'=>'','user_id'=>$user->id]);
          exit;
        }
    }

    public function changeAccess(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'client_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            UsersAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"1"]);
            return response()->json(['errors'=>'','client_id'=>$request->client_id]);
          exit;
        }
    }
    public function getAddress(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
            ->where("user_id",$request->id)
            ->first();
            if(!empty($UsersAdditionalInfo)){
                return response()->json(['errors'=>'','address'=>$UsersAdditionalInfo['full_address']]);
                exit;   
            }else{
                return view('pages.404');
            }
        }
    }
    public function checkAccess(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
            ->where("user_id",$request->id)
            ->first();
            if(!empty($UsersAdditionalInfo)){
                if($UsersAdditionalInfo['email']==NULL){  
                    return view('billing.invoices.toGrantAccessPopup',compact('UsersAdditionalInfo'));     
                    exit; 
                }else if($UsersAdditionalInfo['client_portal_enable']==0){  
                    return view('billing.invoices.toConfirmAccessPopup',compact('UsersAdditionalInfo'));     
                    exit; 
                }else{
                    return "true";
                    exit; 
                }
            }else{
                return view('pages.404');
            }
        }
    }
    public function reloadRow(Request $request)
    {
        
        $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
        ->where("user_id",$request->id)
        ->first();
        return view('billing.invoices.reloadRow',compact('UsersAdditionalInfo'));     
        exit; 
    }

    public function addInvoiceEntry(Request $request)
    { 
        // return $request->all();
        $rules = [
            // 'invoice_number_padded' => 'required|numeric|unique:invoices,id,NULL,id,deleted_at,NULL',
            'invoice_number_padded' => 'required|numeric|unique:invoices,id',
            'court_case_id' => 'required'/* |numeric */,
            'contact' => 'required|numeric',
            // 'total_text' => 'required'
        ];
        if(!empty($request->flatFeeEntrySelectedArray) && count($request->flatFeeEntrySelectedArray)) {
            $rules['timeEntrySelectedArray'] = 'nullable|array';
            $rules['expenseEntrySelectedArray'] = 'nullable|array';            
        } else {
            if(empty($request->forwarded_invoices)){
                $rules['timeEntrySelectedArray'] = 'required_without:expenseEntrySelectedArray|array';
                $rules['expenseEntrySelectedArray'] = 'required_without:timeEntrySelectedArray|array';
            }
        }
        $paymentPlanAmount = 0;
        if(isset($request->new_payment_plans)){
            foreach($request->new_payment_plans as $k=>$v){
                $paymentPlanAmount += (float) str_replace(',', '', $v['amount']);
            }
        }
        $paymentPlanAmount = (float) str_replace(',', '', number_format($paymentPlanAmount,2));
        if($request->payment_plan == "on" && $request->final_total_text != $paymentPlanAmount){
            $rules['new_payment_plans'] = 'required|min:'.$request->final_total_text;
        }       
        $request->validate($rules,
        [
            "invoice_number_padded.unique"=>"Invoice number is already taken",
            "invoice_number_padded.required"=>"Invoice number must be greater than 0",
            "invoice_number_padded.numeric"=>"Invoice number must be greater than 0",
            "contact.required"=>"Billing user can't be blank",
            "timeEntrySelectedArray.required_without"=>"You are attempting to save a blank invoice, please add time entries activity.",
            "expenseEntrySelectedArray.required_without"=>"You are attempting to save a blank invoice, please add expenses activity",
            "new_payment_plans.min"=>"Payment plans must add up to the same total as the invoice."
        ]);      
        // dd($request->all());    
        dbStart();        
        try {
            DB::table('invoices')->where("deleted_at","!=",NULL)->where("id",$request->invoice_number_padded)->delete();
            $InvoiceSave=new Invoices;
            $InvoiceSave->id=$request->invoice_number_padded;
            $InvoiceSave->user_id=$request->contact;
            $InvoiceSave->case_id= ($request->court_case_id == "none") ? 0 : $request->court_case_id;
            $InvoiceSave->invoice_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->bill_invoice_date)))), auth()->user()->user_timezone ?? 'UTC');
            $InvoiceSave->bill_address_text=$request->bill_address_text;
            if($request->payment_terms==""){
                $InvoiceSave->payment_term="5";
            }else{
                $InvoiceSave->payment_term=$request->payment_terms;
            }
            $InvoiceSave->due_date=($request->bill_due_date) ? date('Y-m-d',strtotime($request->bill_due_date)) : NULL;   
            if(isset($request->automated_reminders)){
                $InvoiceSave->automated_reminder="yes";
            }else{
                $InvoiceSave->automated_reminder="no";
            }

            if(isset($request->payment_plan)){
                $InvoiceSave->payment_plan_enabled="yes";
            }else{
                $InvoiceSave->payment_plan_enabled="no";
            }

            if($request->court_case_id == "none"){
                if(count($request->flatFeeEntrySelectedArray) > 0 || $request->final_total_text == 0){
                    $InvoiceSave->status="Paid";
                }
            }
            $InvoiceSave->status=$request->bill_sent_status;
            $InvoiceSave->bill_sent_status=$request->bill_sent_status;
            $InvoiceSave->total_amount=$request->final_total_text;
            $InvoiceSave->due_amount=$request->final_total_text;
            $InvoiceSave->terms_condition=$request->bill['terms_and_conditions'];
            $InvoiceSave->notes=$request->bill['bill_notes'];
            $InvoiceSave->created_by=Auth::User()->id; 
            $InvoiceSave->created_at=date('Y-m-d h:i:s'); 
            $InvoiceSave->firm_id = auth()->user()->firm_name;
            $InvoiceSave->invoice_unique_token=Hash::make($InvoiceSave->id);
            $InvoiceSave->invoice_token=Str::random(250);
            $InvoiceSave->save();


            $invoiceHistory=[];
            $invoiceHistory['invoice_id']=$InvoiceSave->id;
            $invoiceHistory['acrtivity_title']='Invoice Created';
            $invoiceHistory['pay_method']=NULL;
            $invoiceHistory['amount']=NULL;
            $invoiceHistory['responsible_user']=Auth::User()->id;
            $invoiceHistory['deposit_into']=NULL;
            $invoiceHistory['notes']=NULL;
            $invoiceHistory['created_by']=Auth::User()->id;
            $invoiceHistory['created_at']=date('Y-m-d H:i:s');
            $this->invoiceHistory($invoiceHistory);


            InvoiceAdjustment::where('token',$request->adjustment_token)->update(['invoice_id'=>$InvoiceSave->id,'token'=> base64_encode($InvoiceSave->id)]);

            //Flat Fees entry referance
            if(!empty($request->flatFeeEntrySelectedArray)){
                FlatFeeEntryForInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                foreach($request->flatFeeEntrySelectedArray as $k=>$v){
                    $FlatFeeEntryForInvoice=new FlatFeeEntryForInvoice;
                    $FlatFeeEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                    $FlatFeeEntryForInvoice->flat_fee_entry_id=$v;
                    $FlatFeeEntryForInvoice->created_by=Auth::User()->id; 
                    $FlatFeeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                    $FlatFeeEntryForInvoice->save();                    

                    $FlatFeeEntry = FlatFeeEntry::where("id", $v)->first();
                    if(!empty($FlatFeeEntry)){
                        $FlatFeeEntry->status='paid';
                        $FlatFeeEntry->invoice_link = $InvoiceSave->id;
                        if($FlatFeeEntry->token_id == '9999999'){
                            $FlatFeeEntry->token_id = null;
                        }
                        $FlatFeeEntry->save();
                    }
                
                }
            }
            //Time entry referance
            if(!empty($request->timeEntrySelectedArray)){
                TimeEntryForInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                foreach($request->timeEntrySelectedArray as $k=>$v){
                    $TimeEntryForInvoice=new TimeEntryForInvoice;
                    $TimeEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                    $TimeEntryForInvoice->time_entry_id=$v;
                    $TimeEntryForInvoice->created_by=Auth::User()->id; 
                    $TimeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                    $TimeEntryForInvoice->save();
                    
                    $TaskTimeEntry = TaskTimeEntry::where("id", $v)->first();                    
                    if(!empty($TaskTimeEntry)){
                        $TaskTimeEntry->status='paid';
                        $TaskTimeEntry->invoice_link = $InvoiceSave->id;
                        if($TaskTimeEntry->token_id == '9999999'){
                            $TaskTimeEntry->token_id = null;
                        }
                        $TaskTimeEntry->save();
                    }                   
                }
            }
            //Expense entry referance
            if(!empty($request->expenseEntrySelectedArray)){
                ExpenseForInvoice::where("invoice_id",$InvoiceSave->id)->delete();

                foreach($request->expenseEntrySelectedArray as $k=>$v){
                    if($v > 0){
                    $ExpenseEntryForInvoice=new ExpenseForInvoice;
                    $ExpenseEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                    $ExpenseEntryForInvoice->expense_entry_id=$v;
                    $ExpenseEntryForInvoice->created_by=Auth::User()->id; 
                    $ExpenseEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                    $ExpenseEntryForInvoice->save();
                    
                    $ExpenseEntry = ExpenseEntry::where("id", $v)->first();                    
                    if(!empty($ExpenseEntry)){
                        $ExpenseEntry->status='paid';
                        $ExpenseEntry->invoice_link = $InvoiceSave->id;
                        if($ExpenseEntry->token_id == '9999999'){
                            $ExpenseEntry->token_id = null;
                        }
                        $ExpenseEntry->save();
                    }  }
                   
                }
            }
            
            //Invoice Shared With Client
            if(!empty($request->portalAccess)){
                SharedInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                $InvoiceSave->status="Sent";
                $InvoiceSave->bill_sent_status="Sent";
                $InvoiceSave->save();
                foreach($request->portalAccess as $k=>$v){
                    $SharedInvoice=new SharedInvoice;
                    $SharedInvoice->invoice_id=$InvoiceSave->id;                    
                    $SharedInvoice->user_id =$v;
                    $SharedInvoice->created_by=Auth::User()->id; 
                    $SharedInvoice->created_at=date('Y-m-d h:i:s'); 
                    $SharedInvoice->save();

                    $invoiceHistory=[];
                    $invoiceHistory['invoice_id']=$InvoiceSave->id;
                    $invoiceHistory['acrtivity_title']='Shared w/Contacts';
                    $invoiceHistory['pay_method']=NULL;
                    $invoiceHistory['amount']=NULL;
                    $invoiceHistory['responsible_user']=Auth::User()->id;
                    $invoiceHistory['deposit_into']=NULL;
                    $invoiceHistory['notes']=NULL;
                    $invoiceHistory['created_by']=Auth::User()->id;
                    $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                    $this->invoiceHistory($invoiceHistory);

                    $firmData=Firm::find(Auth::User()->firm_name);       
                    $findUSer=User::find($v);
                    $getTemplateData = EmailTemplate::find(20);
                    $email=$findUSer['email'];
                    $token=BASE_URL."bills/invoices/view/".base64_encode($InvoiceSave->id);

                    $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];
                    $mail_body = $getTemplateData->content;
                    $mail_body = str_replace('{name}', $fullName,$mail_body);
                    $mail_body = str_replace('{email}', $email,$mail_body);
                    $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                    $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
                    $mail_body = str_replace('{token}', $token,$mail_body);
                    $mail_body = str_replace('{regards}', $firmData['firm_name'], $mail_body);  
                    $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
                    $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                    $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                    
                    $userEmail = [
                        "from" => FROM_EMAIL,
                        "from_title" => $firmData['firm_name'],
                        "subject" => $firmData['firm_name']." has sent you an invoice",
                        "to" => $email,
                        "full_name" => $fullName,
                        "mail_body" => $mail_body
                        ];
                    $sendEmail = $this->sendMail($userEmail);
                }
            }

            if(isset($request->payment_plan)){
                InvoicePaymentPlan::where("invoice_id",$InvoiceSave->id)->delete();
                $InvoicePaymentPlan=new InvoicePaymentPlan;
                $InvoicePaymentPlan->invoice_id=$InvoiceSave->id;                    
                $InvoicePaymentPlan->start_date=date('Y-m-d',strtotime($request->start_date));
                $InvoicePaymentPlan->per_installment_amt=str_replace(",","",$request->amount_per_installment_field);                    
                $InvoicePaymentPlan->no_of_installment=$request->number_installment_field;                    
                $InvoicePaymentPlan->repeat_by=$request->installment_frequency_field;
                if(isset($request->with_first_payment)){                    
                    $InvoicePaymentPlan->is_set_first_installment=$request->with_first_payment;                    
                    $InvoicePaymentPlan->first_installment_amount=$request->first_payment_amount;                    
                }
                $InvoicePaymentPlan->created_by=Auth::User()->id; 
                $InvoicePaymentPlan->created_at=date('Y-m-d h:i:s'); 
                $InvoicePaymentPlan->save();


                // Invoice Installment entry
                InvoiceInstallment::where("invoice_id",$InvoiceSave->id)->delete();
                foreach($request->new_payment_plans as $kk=>$vv){
                    $InvoiceInstallment=new InvoiceInstallment;
                    $InvoiceInstallment->invoice_id=$InvoiceSave->id;                    
                    $InvoiceInstallment->installment_amount=str_replace(",","",$vv['amount']);                    
                    $InvoiceInstallment->due_date=date('Y-m-d',strtotime($vv['due_date']));
                    $InvoiceInstallment->created_by=Auth::User()->id; 
                    $InvoiceInstallment->firm_id=Auth::User()->firm_name; 
                    $InvoiceInstallment->created_at=date('Y-m-d h:i:s'); 
                    $InvoiceInstallment->save();
                }
            }

            if(!empty($request->forwarded_invoices)) {
                $InvoiceSave->forwardedInvoices()->sync($request->forwarded_invoices);
                $forwardedInvoices = Invoices::whereIn("id", $request->forwarded_invoices)->get();
                if($forwardedInvoices) {
                    foreach($forwardedInvoices as $key => $item) {
                        $this->updateInvoiceDraftStatus($InvoiceSave->invoice_id);
                        $item->fill(["status" => "Forwarded"])->save();
                        InvoiceHistory::create([
                            "invoice_id" => $item->id,
                            "acrtivity_title" => "balance forwarded",
                            "amount" => $item->due_amount,
                            "responsible_user" => auth()->id(),
                            "notes" => "Forwarded to ".$InvoiceSave->invoice_id,
                            "created_by" => auth()->id()
                        ]);
                    }
                }
            }

            //Add Invoice history
            $data=[];
            $data['case_id']=$InvoiceSave->case_id;
            $data['user_id']=$InvoiceSave->user_id;
            $data['activity']='added an invoice';
            $data['activity_for']=$InvoiceSave->id;
            $data['type']='invoices';
            $data['action']='add';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            // Store invoice view settings
            $jsonData = [];
            $preferenceSetting = InvoiceSetting::where("firm_id", auth()->user()->firm_name)->with("reminderSchedule")->first();
            $customizationSetting = InvoiceCustomizationSetting::where("firm_id", auth()->user()->firm_name)->with("flatFeeColumn", "timeEntryColumn", "expenseColumn")->first();
            if($preferenceSetting) {
                $jsonData = [
                    'hours_decimal_point' => $preferenceSetting->time_entry_hours_decimal_point,
                    'payment_terms' => $preferenceSetting->time_entry_default_invoice_payment_terms,
                    'trust_credit_activity_on_invoice' => $preferenceSetting->default_trust_and_credit_display_on_new_invoices,
                    'default_terms_conditions' => $preferenceSetting->time_entry_default_terms_conditions,
                    'is_non_trust_retainers_credit_account' => $preferenceSetting->is_non_trust_retainers_credit_account,
                    // 'is_ledes_billing' => $preferenceSetting->is_ledes_billing,
                    'request_funds_preferences_default_msg' => $preferenceSetting->request_funds_preferences_default_msg,
                ];
                if($preferenceSetting->reminderSchedule) {
                    foreach($preferenceSetting->reminderSchedule as $key => $item) {
                        $jsonData['reminder'][] = [
                            'remind_type' => $item->remind_type,
                            'days' => $item->days,
                            'is_reminded' => "no",
                        ];
                    }
                }
            }
            if($customizationSetting) {
                $jsonData['invoice_theme'] = $customizationSetting->invoice_theme;
                $jsonData['show_case_no_after_case_name'] = $customizationSetting->show_case_no_after_case_name;
                $jsonData['non_billable_time_entries_and_expenses'] = $customizationSetting->non_billable_time_entries_and_expenses;
                if($customizationSetting->flatFeeColumn) {
                    $jsonData['flat_fee'] = getColumnsIfYes($customizationSetting->flatFeeColumn->toArray());
                }
                if($customizationSetting->timeEntryColumn) {
                    $jsonData['time_entry'] = getColumnsIfYes($customizationSetting->timeEntryColumn->toArray());
                }
                if($customizationSetting->expenseColumn) {
                    $jsonData['expense'] = getColumnsIfYes($customizationSetting->expenseColumn->toArray());
                }
            }
            $InvoiceSave->invoice_setting = $jsonData;
            $InvoiceSave->save();

            // Apply trust and credit funds
            if(!empty($request->trust)) {
                foreach($request->trust as $key => $item) {
                    $trustHistoryLast = TrustHistory::where("client_id", @$item['client_id'])->orderBy('created_at', 'desc')->first();
                    $appliedTrustFund = InvoiceApplyTrustCreditFund::create([
                        'invoice_id' => $InvoiceSave->id,
                        'client_id' => @$item['client_id'] ?? NUll,
                        'case_id' => ($request->court_case_id == "none") ? 0 : $request->court_case_id,
                        'account_type' => 'trust',
                        'show_trust_account_history' => @$item['show_trust_account_history'] ?? "dont show",
                        'created_by' => auth()->id(),
                        'history_last_id' => @$trustHistoryLast->id,
                        'total_balance' => @$trustHistoryLast->current_trust_balance
                    ]);
                    if(!empty($item) && (array_key_exists("applied_amount", (array) $item) || array_key_exists("allocate_applied_amount", (array) $item))) {
                        $authUser = auth()->user();
                        if(array_key_exists("applied_amount", (array) $item) && $item["applied_amount"] != "") {

                            $appliedTrustFund->fill([
                                'applied_amount' => @$item['applied_amount'] ?? 0.00,
                                'deposite_into' => @$item['deposite_into'] ?? NULL,
                            ])->save();

                            $InvoicePayment = $this->invoiceApplyTrustFund($item, $request, $InvoiceSave);
                        
                            //Deduct invoice amount when payment done
                            $this->updateInvoiceAmount($InvoiceSave->id);
                        }

                        if(array_key_exists("allocate_applied_amount", (array) $item) && $item["allocate_applied_amount"] != "") {
                            $item["applied_amount"] = $item["allocate_applied_amount"];

                            $appliedTrustFund->fill([
                                'allocate_applied_amount' => @$item["allocate_applied_amount"] ?? 0.00,
                                'deposite_into' => @$item['deposite_into'] ?? NULL,
                            ])->save();

                            $InvoicePayment = $this->invoiceApplyTrustFund($item, $request, $InvoiceSave, 'allocate');
                        
                            //Deduct invoice amount when payment done
                            $this->updateInvoiceAmount($InvoiceSave->id);
                        }
                        // Update last trust history id/balance
                        $trustHistoryLast = TrustHistory::where("client_id", @$item['client_id'])->orderBy('created_at', 'desc')->first();
                        $appliedTrustFund->fill([
                            'history_last_id' => @$trustHistoryLast->id,
                            'total_balance' => @$trustHistoryLast->current_trust_balance
                        ])->save();
                    }
                }
            }
            if(!empty($request->credit)) {
                foreach($request->credit as $key => $item) {
                    $creditHistoryLast = DepositIntoCreditHistory::where("user_id", @$item['client_id'])->orderBy('created_at', 'desc')->first();
                    $appliedCreditFund = InvoiceApplyTrustCreditFund::create([
                        'invoice_id' => $InvoiceSave->id,
                        'client_id' => @$item['client_id'] ?? NUll,
                        'case_id' => ($request->court_case_id == "none") ? 0 : $request->court_case_id,
                        'account_type' => 'credit',
                        'applied_amount' => @$item['applied_amount'] ?? 0.00,
                        'deposite_into' => @$item['deposite_into'] ?? NULL,
                        'show_credit_account_history' => @$item['show_credit_account_history'] ?? "dont show",
                        'created_by' => auth()->id(),
                        'history_last_id' => @$creditHistoryLast->id,
                        'total_balance' => @$creditHistoryLast->total_balance
                    ]);

                    if(!empty($item) && array_key_exists("applied_amount", (array) $item) && @$item['applied_amount'] != "") {
                        $authUser = auth()->user();
                        //Insert invoice payment record.
                        $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","credit")->orderBy("created_at","DESC")->first();
                            
                        //Insert invoice payment record.
                        $InvoicePayment = InvoicePayment::create([
                            'invoice_id' => $InvoiceSave->id,
                            'payment_from' => 'credit',
                            'amount_paid' => @$item['applied_amount'] ?? 0,
                            'payment_date' => date('Y-m-d'),
                            'notes' => $request->notes,
                            'status' => "0",
                            'entry_type' => "0",
                            'payment_from_id' => @$item['client_id'],
                            'deposit_into' => "Operating Account",
                            'deposit_into_id' => @$item['client_id'],
                            'total' => (@$currentBalance['total'] ?? 0 + @$item['applied_amount'] ?? 0),
                            'firm_id' => $authUser->firm_name,
                            'created_by' => $authUser->id,
                        ]);
                        $InvoicePayment->fill(['ip_unique_id' => Hash::make($InvoicePayment->id)])->save();
                    
                        //Deduct invoice amount when payment done
                        $this->updateInvoiceAmount($InvoiceSave->id);

                        // Deduct amount from credit account after payment.
                        $userAddInfo = UsersAdditionalInfo::where("user_id", @$item['client_id'])->first();
                        if($userAddInfo) {
                            $userAddInfo->fill([
                                'credit_account_balance' => ($userAddInfo->credit_account_balance) ? $userAddInfo->credit_account_balance - $item['applied_amount'] ?? 00 : $userAddInfo->credit_account_balance
                            ])->save();
                        }
                            
                        // Add credit history
                        DepositIntoCreditHistory::create([
                            "user_id" => @$item['client_id'],
                            "payment_method" => "payment",
                            "deposit_amount" => @$item['applied_amount'] ?? 0,
                            "payment_date" => date('Y-m-d'),
                            "payment_type" => "payment",
                            "total_balance" => @$userAddInfo->credit_account_balance,
                            "related_to_invoice_id" => $InvoiceSave->id,
                            "created_by" => auth()->id(),
                            "firm_id" => auth()->user()->firm_name,
                            "related_to_invoice_payment_id" => $InvoicePayment->id,
                        ]);

                        // Update last credit history id/balance
                        $creditHistoryLast = DepositIntoCreditHistory::where("user_id", @$item['client_id'])->orderBy('created_at', 'desc')->first();
                        $appliedCreditFund->fill([
                            'history_last_id' => @$creditHistoryLast->id,
                            'total_balance' => @$creditHistoryLast->total_balance
                        ])->save();

                        $invoiceHistory=[];
                        $invoiceHistory['invoice_id'] = $InvoiceSave->id;
                        $invoiceHistory['acrtivity_title']='Payment Received';
                        $invoiceHistory['pay_method']='Credit';
                        $invoiceHistory['amount'] = @$item['applied_amount'] ?? 0;
                        $invoiceHistory['responsible_user'] = $authUser->id;
                        $invoiceHistory['payment_from'] = 'credit';
                        $invoiceHistory['deposit_into']='Operating Account';
                        $invoiceHistory['deposit_into_id'] = (@$item['client_id'])??NULL;
                        $invoiceHistory['invoice_payment_id'] = $InvoicePayment->id;
                        $invoiceHistory['notes']=$request->notes ?? NULL;
                        $invoiceHistory['status']="1";
                        $invoiceHistory['created_by'] = $authUser->id;
                        $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                        $this->invoiceHistory($invoiceHistory);

                        //Add Invoice history
                        $data=[];
                        $data['case_id'] = $InvoiceSave->case_id;
                        $data['user_id'] = $InvoiceSave->user_id;
                        $data['activity']='accepted a payment of $'.number_format(@$item['applied_amount'] ?? 0,2).' (Credit)';
                        $data['activity_for'] = $InvoiceSave->id;
                        $data['type']='invoices';
                        $data['action']='pay';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                    }
                }
            }

            InvoiceTempInfo::where('invoice_unique_id', $request->adjustment_token)->where("case_id", $request->court_case_id)->delete();

            dbCommit();
            $decodedId=base64_encode($InvoiceSave->id);
            return redirect('bills/invoices/view/'.$decodedId);
            // return response()->json(['errors'=>'','invoice_id'=>$InvoiceSave->id]);
            exit;
        } catch (Exception $e) {
            dbEnd();
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    //View Invoice 
    public function viewInvoice(Request $request)
    {
        $authUser = auth()->user();
        $invoiceID=base64_decode($request->id);
        $findInvoice=Invoices::whereId($invoiceID)->with("forwardedInvoices", "applyTrustFund", "applyCreditFund")->where('firm_id', $authUser->firm_name)->first();
        \Log::info("viewInvoice > ".$invoiceID." > InvoiceData >". json_encode($findInvoice));
        if(!empty($findInvoice)){
        $case = CaseMaster::whereId($findInvoice->case_id);
        if($authUser->parent_user != 0) {
            $case = $case->whereHas('caseStaffAll', function($query) use($authUser){
                $query->where('user_id', $authUser->id);
            });
        }
        $case = $case->first();
        if(empty($findInvoice) || ($findInvoice->case_id != 0 && empty($case)))
        {
            return view('errors.invoice_403');
        }elseif(empty($findInvoice) || $findInvoice->is_lead_invoice == 'yes')
        {
            return \Redirect::route('bills/invoices/potentialview', [$request->id]);
        }else{
            $firmData = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->first();

            $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoiceID)->orderBy("id","DESC")->get();

            $lastEntry= $InvoiceHistory->first();
          
            $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")
            ->leftJoin("users","task_time_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
            ->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")
            ->where("time_entry_for_invoice.invoice_id",$invoiceID)
            ->get();

            $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")
             ->leftJoin("users","expense_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
            ->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")
            ->where("expense_for_invoice.invoice_id",$invoiceID)
            ->get();

            
            //Get the flat fee Entry list
            $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
            ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
            ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
            ->where("flat_fee_entry_for_invoice.invoice_id",$invoiceID)
            ->get();
    
            //Get the Adjustment list
            $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoiceID)->where("invoice_adjustment.amount",">",0)->get();

            $caseMaster=CaseMaster::whereId($findInvoice->case_id)->with("caseAllClient", "caseAllClient.userAdditionalInfo", "caseAllClient.userTrustAccountHistory")->first();
            $userMaster=User::find($findInvoice->user_id);
            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
            ->where("user_id",$findInvoice->user_id)
            ->first();
            
            $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoiceID)->get();

            $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoiceID)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();


            $SharedInvoiceCount=SharedInvoice::Where("invoice_id",$invoiceID)->count();
            // if(!file_exists(public_path('download/pdf/'."Invoice_".$invoiceID.".pdf")))
            if(!file_exists(Storage::path('download/pdf/Invoice_'.$invoiceID.".pdf")))
            {
                $this->generateInvoicePdfAndSave($request);
            }

            //check case client company is list out on contacts
            $case_client_company = [];
            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.case_id",$findInvoice->case_id)->get()->pluck("selected_user");
            foreach($caseCllientSelection as $key=>$val){
                if(in_array($val,explode(",",$UsersAdditionalInfo->multiple_compnay_id))){ 
                    $case_client_company = User::find($val);
                }
            }
            //check case client company is list out on contacts


            $invoiceNo = sprintf('%06d', $findInvoice->id);
            $invoiceSetting = $findInvoice->invoice_setting;
            $invoiceDefaultSetting = getInvoiceSetting();
            if($request->ajax()) {
                return view('billing.invoices.partials.load_invoice_detail',compact('findInvoice','InvoiceHistory','lastEntry','firmData','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','caseMaster','userMaster','SharedInvoiceCount','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice', 'invoiceNo','UsersAdditionalInfo', 'invoiceSetting', 'invoiceDefaultSetting'))->render();
            }
            // return $findInvoice->invoice_setting['flat_fee'];
            return view('billing.invoices.viewInvoice',compact('findInvoice','InvoiceHistory','lastEntry','firmData','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','caseMaster','userMaster','SharedInvoiceCount','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice', 'invoiceNo','UsersAdditionalInfo', 'invoiceSetting', 'invoiceDefaultSetting','case_client_company'));     
            exit; 
        }
        }else{
            return view('errors.invoice_403');
        }
    }

    public function invoiceHistory($historyData)
    {
            $InvoiceHistory = new InvoiceHistory; 
            $InvoiceHistory->invoice_id=$historyData['invoice_id'];
            $InvoiceHistory->lead_invoice_id=($historyData['lead_invoice_id'])??NULL;
            $InvoiceHistory->acrtivity_title= $historyData['acrtivity_title'];
            $InvoiceHistory->pay_method= $historyData['pay_method'];
            $InvoiceHistory->amount= $historyData['amount'];
            $InvoiceHistory->responsible_user= $historyData['responsible_user'];
            $InvoiceHistory->payment_from= @$historyData['payment_from'];
            $InvoiceHistory->deposit_into= @$historyData['deposit_into'];
            $InvoiceHistory->deposit_into_id= ($historyData['deposit_into_id'])??NULL;
            $InvoiceHistory->invoice_payment_id= ($historyData['invoice_payment_id'])??NULL;
            $InvoiceHistory->notes= $historyData['notes'];
            $InvoiceHistory->status= ($historyData['status'])??0;
            $InvoiceHistory->online_payment_status = ($historyData['online_payment_status']) ?? Null;
            $InvoiceHistory->refund_ref_id= ($historyData['refund_ref_id'])??NULL;
            $InvoiceHistory->created_by=$historyData['created_by'];
            $InvoiceHistory->created_at=$historyData['created_at'];
            $InvoiceHistory->save();
            // return true;
            return $InvoiceHistory->id;
    }

    public function deleteInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'invoiceId' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $id=$request->invoiceId;

            //Remove flat fee entry for the invoice and reactivated time entry
            $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::where("invoice_id",$id)->get();
            foreach($FlatFeeEntryForInvoice as $k=>$v){
                DB::table('flat_fee_entry')->where("id",$v->flat_fee_entry_id)->update([
                'status'=>'unpaid',
                'invoice_link' => null,
                ]);
                FlatFeeEntryForInvoice::where("id", $v->id)->delete();
            }
             
            //Remove time entry for the invoice and reactivated time entry
            $timeEntryData=TimeEntryForInvoice::where("invoice_id",$id)->get();
            foreach($timeEntryData as $k=>$v){
                DB::table('task_time_entry')->where("id",$v->time_entry_id)->update([
                    'status'=>'unpaid',
                    'invoice_link' => null,
                ]);
                TimeEntryForInvoice::where("id", $v->id)->delete();
            }

            //Remove Expense for the invoice and reactivated expense entry
            $expenseEntryData=ExpenseForInvoice::where("invoice_id",$id)->get();
            foreach($expenseEntryData as $k=>$v){
                DB::table('expense_entry')->where("id",$v->expense_entry_id)->update([
                    'status'=>'unpaid',
                    'invoice_link' => null,
                ]);
                ExpenseForInvoice::where("id", $v->id)->delete();
            }
              
            InvoiceAdjustment::where("invoice_id", $id)->delete();
            SharedInvoice::where("invoice_id",$id)->delete();
            InvoicePaymentPlan::where("invoice_id",$id)->delete();
            InvoiceInstallment::where("invoice_id",$id)->delete();
            InvoiceHistory::where("invoice_id",$id)->delete();
            $Invoices = Invoices::where("id", $id)->first();
            // Update trust balance
            $invoicePaymentFromTrust = InvoicePayment::where("invoice_id", $Invoices->id)->where("payment_from", "trust")->get();
            $accessUser = UsersAdditionalInfo::where("user_id", $Invoices->user_id)->first();
            if($accessUser && $invoicePaymentFromTrust) {
                $paidAmount = $invoicePaymentFromTrust->sum('amount_paid');
                $refundAmount = $invoicePaymentFromTrust->sum('amount_refund');
                $accessUser->fill(['trust_account_balance' => $accessUser->trust_account_balance + ($paidAmount - $refundAmount)])->save();
            }

            // Update forwarded invoices
            $forwardedInvoices = Invoices::whereIn("id", $Invoices->forwardedInvoices->pluck("id")->toArray())->get();
            if($forwardedInvoices) {
                foreach($forwardedInvoices as $key => $item) {
                    $this->updateInvoiceAmount($item->id);
                }
            }

            InvoicePayment::where("invoice_id",$Invoices->id)->delete();

            $Invoices->delete();
            session(['popup_success' => 'Invoice has been deleted.']);
            return response()->json(['errors'=>'','id'=>$id]);
            exit;  
        }  
    }

    public function shareInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $Invoices=Invoices::find($request->id);
            if(!empty($Invoices)){
                $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
                    ->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')
                    ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users.last_login","users_additional_info.multiple_compnay_id")
                    ->where("case_client_selection.case_id",$Invoices['case_id']);
                    
                if($Invoices['case_id'] == 0){
                    $getAllClientForSharing=$getAllClientForSharing->where("case_client_selection.selected_user",$Invoices['user_id']);
                    $getAllClientForSharing=$getAllClientForSharing->orderBy('user_level', 'desc')->get();
                }else{
                    $getAllClientForSharing=$getAllClientForSharing->orderBy('user_level', 'desc')->get();
                }
                $companyClientIds = [];
                if(count($getAllClientForSharing) == 0){
                    $getAllClientForSharing[] = User::find($Invoices['user_id']); 
                }else{
                foreach($getAllClientForSharing as $k=>$v) {
                    if($v->user_level == 4) {
                        $companyContacts = $v->companyContactList($v->user_id, $v->case_client_selection_id);
                        $v["company_contacts"] = $companyContacts;
                        array_push($companyClientIds, implode(",", $companyContacts->pluck("cid")->toArray()));
                    } else {
                        $v["company_contacts"] = [];
                    }
                    $checkedUser=SharedInvoice::where("invoice_id",$Invoices['id'])->where("user_id",$v->user_id)->first();
                    if(!empty($checkedUser)){
                        $v['shared']=$checkedUser['is_shared'];
                        $v['sharedDate']=$checkedUser['created_at'];
                        $v['isViewd']=$checkedUser['is_viewed'];
                        $v['viewed_at']=$checkedUser['last_viewed_at'];

                    }else{
                        $v['shared']="no";
                        $v['sharedDate']=NULL;
                        $v['isViewd']=$checkedUser['is_viewed'] ?? "no";
                        $v['viewed_at']=$checkedUser['last_viewed_at'] ?? Null;
                    }
                    $v['is_company_contact']="no";
                    if(in_array($v->id, $companyClientIds)) {
                        $v['is_company_contact'] = "yes";
                    }
                }
                }
                // return $getAllClientForSharing;
                // $SharedInvoice=SharedInvoice::where("invoice_id",$Invoices['id'])->pluck("user_id");
                return view('billing.invoices.shareInvoice',compact('Invoices','getAllClientForSharing'/* ,'SharedInvoice' */));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }

    public function saveShareInvoice(Request $request)
    {
        // return array_values($request->invoice_shared);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $Invoices=Invoices::find($request->invoice_id);
            if(!empty($Invoices)  && $request->invoice_shared){
                foreach($request->invoice_shared as $k=>$v){
                    $SharedInvoice=SharedInvoice::where("user_id",$k)->Where("invoice_id",$request->invoice_id)->count();
                    if($SharedInvoice<=0){
                        $SharedInvoice=new SharedInvoice;
                        $SharedInvoice->invoice_id=$request->invoice_id;                    
                        $SharedInvoice->user_id =$k;
                        $SharedInvoice->is_shared = 'yes';
                        $SharedInvoice->created_by=Auth::User()->id; 
                        $SharedInvoice->created_at=date('Y-m-d h:i:s'); 
                        $SharedInvoice->save();
    
                        $invoiceHistory=[];
                        $invoiceHistory['invoice_id']=$request->invoice_id;
                        $invoiceHistory['acrtivity_title']='Shared w/Contacts';
                        $invoiceHistory['pay_method']=NULL;
                        $invoiceHistory['amount']=NULL;
                        $invoiceHistory['responsible_user']=Auth::User()->id;
                        $invoiceHistory['deposit_into']=NULL;
                        $invoiceHistory['notes']=NULL;
                        $invoiceHistory['created_by']=Auth::User()->id;
                        $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                        $this->invoiceHistory($invoiceHistory);

                        $firmData=Firm::find(Auth::User()->firm_name);       
                        $findUSer=User::find($k);
                        $getTemplateData = EmailTemplate::find(13);
                        $email=$findUSer['email'];
                        $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];
                        $mail_body = $getTemplateData->content;
                        $mail_body = str_replace('{email}', $email,$mail_body);
                        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
                        $mail_body = str_replace('{regards}', $firmData['firm_name'], $mail_body);  
                        $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
                        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                      
                        $userEmail = [
                            "from" => FROM_EMAIL,
                            "from_title" => $firmData['firm_name'],
                            "subject" => "New Invoice from ".$firmData['firm_name'],
                            "to" => $email,
                            "full_name" => $fullName,
                            "mail_body" => $mail_body
                            ];
                        $sendEmail = $this->sendMail($userEmail);

                        //Add history
                        $data=[];
                        $data['invoice_id']=$request->invoice_id;
                        $data['user_id']=$k;
                        $data['client_id']=$k;
                        $data['case_id']=$Invoices->case_id;
                        $data['activity']='shared invoice';
                        $data['activity_for']=$request->invoice_id;
                        $data['type']='invoices';
                        $data['action']='share';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                        // Add history for client
                        $data=[];
                        $data['invoice_id']=$request->invoice_id;
                        $data['user_id']=$k;
                        $data['client_id']=$k;
                        $data['activity']='shared invoice';
                        $data['activity_for']=$request->invoice_id;
                        $data['type']='invoices';
                        $data['action']='share';
                        $data['is_for_client']='yes';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);
                    } else {
                        SharedInvoice::where("user_id",$k)->Where("invoice_id",$request->invoice_id)->update(['is_shared' => "yes"]);
                    }
                }
                $notSharedUser = SharedInvoice::Where("invoice_id", $request->invoice_id)->whereNotIn("user_id", array_values($request->invoice_shared))
                                ->where("is_shared", "yes")->get();
                if($notSharedUser) {
                    foreach($notSharedUser as $key => $item) {
                        $item->update(['is_shared' => "no"]);
                        //Add history
                        $data=[];
                        $data['invoice_id']=$request->invoice_id;
                        $data['user_id']=$k;
                        $data['client_id']=$k;
                        $data['case_id']=$Invoices->case_id;
                        $data['activity']='unshared invoice ';
                        $data['activity_for']=$request->invoice_id;
                        $data['type']='invoices';
                        $data['action']='unshare';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                        // Add history for client
                        $data=[];
                        $data['invoice_id']=$request->invoice_id;
                        $data['user_id']=$k;
                        $data['client_id']=$k;
                        $data['activity']='unshared invoice';
                        $data['activity_for']=$request->invoice_id;
                        $data['type']='invoices';
                        $data['action']='share';
                        $data['is_for_client']='yes';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);
                    }
                }
                // $Invoices->fill([
                //     'status' => (in_array($Invoices->status, ['Unsent', 'Draft'])) ? 'Sent' : $Invoices->status,
                //     'bill_sent_status' => 'Sent',
                // ])->save();
                $this->updateInvoiceDraftStatus($request->invoice_id);
                $this->updateInvoiceAmount($request->invoice_id);
                session(['popup_success' => 'Sharing updated']);
                return response()->json(['errors'=>'']);
            }else{
                SharedInvoice::Where("invoice_id", $request->invoice_id)->update(['is_shared' => "no"]);
                session(['popup_success' => 'Sharing updated']);
                return response()->json(['errors'=>'']);
                return response()->json(['errors'=>['Internal server error!']]);
                exit;
            }
        }
    }

    public function checkAccessFromViewInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
            ->where("user_id",$request->id)
            ->first();
            if(!empty($UsersAdditionalInfo)){
                if($UsersAdditionalInfo['email']==NULL){  
                    return view('billing.invoices.toGrantAccessFromViewInvoicePopup',compact('UsersAdditionalInfo'));     
                    exit; 
                }else if($UsersAdditionalInfo['client_portal_enable']==0){  
                    return view('billing.invoices.toConfirmAccessFromViewInvoicePopup',compact('UsersAdditionalInfo'));     
                    exit; 
                }else{
                    return "true";
                    exit; 
                }
            }else{
                return view('pages.404');
            }
        }
    }
    public function reloadRowForViewInvoice(Request $request)
    {

        $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users.last_login")->where("users.id",$request->id)->first();

        $getAllClientForSharing['shared']="no";
        $getAllClientForSharing['sharedDate']=NULL;
        $getAllClientForSharing['isViewd']="no";
        
        return view('billing.invoices.reloadRowFromViewInvoice',compact('getAllClientForSharing'));     
        exit; 
    }

    public function sendReminder(Request $request)
    {
        $invoice_id=$request->id;
        $invoice = Invoices::whereId($request->id)->with("invoiceFirstInstallment")->first();
        // echo Hash::make($invoice_id);
        $getAllClientForSharing=  SharedInvoice::join('users','users.id','=','shared_invoice.user_id')->leftJoin('users_additional_info','users_additional_info.user_id','=','shared_invoice.user_id')
        ->select("shared_invoice.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","users.id as user_id","users_additional_info.client_portal_enable","users.last_login")->where("shared_invoice.invoice_id",$invoice_id)->get();
        return view('billing.invoices.sendInvoiceReminderPopup',compact('invoice_id','getAllClientForSharing', 'invoice'));     
        exit;    
    } 

    public function saveSendReminder(Request $request)
    {
     
        $validator = \Validator::make($request->all(), [
            'client' => 'required|array|min:1'
        ],
        ['min'=>'Please choose at least one contact',
        'required'=>'Please choose at least one contact']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $FindInvoice=Invoices::whereId($request->invoice_id)->with("invoiceFirstInstallment", "firmDetail")->first();
            $invoice_id=$request->invoice_id;
            foreach($request->client as $k=>$v){
                $findUSer=User::find($v);
                // $email=$findUSer['email'];
                // $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];
                        
                // $c=DB::table('shared_invoice')->where("user_id",$v)->where("invoice_id",$invoice_id)->first();
             
                // DB::table('shared_invoice')->where("user_id",$v)->where("invoice_id",$invoice_id)->update([
                //     'last_reminder_sent_on'=>date('Y-m-d h:i:s'),
                //     'reminder_sent_counter'=>$c->reminder_sent_counter+1,
                // ]);
                $sharedInv = SharedInvoice::where("user_id", $v)->where("invoice_id", $invoice_id)->first();
                $sharedInv->fill([
                    'last_reminder_sent_on' => date('Y-m-d h:i:s'),
                    'reminder_sent_counter' => $sharedInv->reminder_sent_counter + 1,
                ])->save();

                // $firmData=Firm::find(Auth::User()->firm_name);
                /* $getTemplateData = EmailTemplate::find(12);
                // $token=url('activate_account/bills='.base64_encode($email).'&web_token='.$FindInvoice['invoice_unique_token']);
                $token=url('bills/invoices/view/'.base64_encode($FindInvoice['id']));
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{token}', $token,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        
    
                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => $firmData->firm_name,
                    "subject" => "Reminder: Invoice #".$invoice_id." is available to view for ".$firmData->firm_name,
                    "to" => $email,
                    "full_name" => $fullName,
                    "mail_body" => $mail_body
                ];
                $sendEmail = $this->sendMail($user); */

                if($request->email_type == "past") {
                    $emailTemplateId = 22;
                } else if($request->email_type == "future") {
                    $emailTemplateId = 24;
                } else if($request->email_type == "present") {
                    $emailTemplateId = 23;
                }else{
                    $emailTemplateId = 29;
                }
                $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
                if($emailTemplate) {
                    dispatch(new InvoiceReminderEmailJob($FindInvoice, $findUSer, $emailTemplate));
                }
                // Invoice reminder sent
                InvoiceHistory::create([
                    "invoice_id" => $FindInvoice->id,
                    "acrtivity_title" => "Sent Reminder",
                    "responsible_user" => auth()->id(),
                    "notes" => "Sent to ".$findUSer->full_name." (".$findUSer->user_type_text.")",
                    "created_by" => auth()->id()
                ]);
            }
            
            session(['popup_success' => 'Reminders have been sent']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }
    public function ClientViewInvoice(Request $request)
    {
        $invoice_id=$request->id;
        $PotentialCaseInvoice=PotentialCaseInvoice::where("invoice_unique_id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$PotentialCaseInvoice['lead_id'])->first();
        $firmData=Firm::find($userData['firm_name']);

        if(empty($PotentialCaseInvoice)){
            return view('pages.404');
        }else{
            return view('lead.details.case_detail.invoices.viewInvoice',compact('userData','firmData','invoice_id','PotentialCaseInvoice'));
        }
    }

    public function downloaInvoivePdfView(Request $request)
    {
        $invoice_id=base64_decode($request->id);
        // $Invoice=Invoices::where("id",$invoice_id)->first();
        $Invoice=Invoices::whereId($invoice_id)->with("forwardedInvoices", "applyTrustFund", "applyCreditFund")->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
       
        $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
        ->where("user_id",$Invoice['user_id'])
        ->first();

        $caseMaster=CaseMaster::whereId($Invoice['case_id'])->with("caseAllClientWithTrashed")->first();
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        

        $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();

        $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();

        //Get the Adjustment list
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();
        $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();
        $firmData=Firm::find($userData['firm_name']);

        $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();

           //Get the flat fee Entry list
        $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
        ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
        ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
        ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
        ->get();
        
        if(empty($invoice_id)){
            return view('pages.404');
        }else{
            return view('billing.invoices.viewInvoicePdf',compact('userData','UsersAdditionalInfo','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceInstallment','InvoiceHistory','InvoiceHistoryTransaction','FlatFeeEntryForInvoice'));
        }
    }
    public function downloaInvoivePdf(Request $request)
    {
        
        $invoice_id=base64_decode($request->id);
        $Invoice=Invoices::where("id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
        
        $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
        ->where("user_id",$Invoice['user_id'])
        ->first();

        $caseMaster=CaseMaster::find($Invoice['case_id']);
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        

        $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();

        $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();
        $firmData=Firm::find($userData['firm_name']);

        //Get the Adjustment list
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();

        $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();

        $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();

        //Get the flat fee Entry list
        $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
        ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
        ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
        ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
        ->get();

        //check case client company is list out on contacts
        $case_client_company = [];
        $caseCllientSelection = CaseClientSelection::select("*")->where("case_id",$Invoice->case_id)->get()->pluck("selected_user");
        foreach($caseCllientSelection as $key=>$val){
            if(in_array($val,explode(",",$UsersAdditionalInfo->multiple_compnay_id))){ 
                $case_client_company = User::find($val);
            }
        }
        //check case client company is list out on contacts
        
        if(isset($request->print)){
            return view('billing.invoices.viewInvoicePdf',compact('userData','UsersAdditionalInfo','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistory','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice','case_client_company'));
        }else{

            $filename="Invoice_".$invoice_id.'.pdf';
            $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','UsersAdditionalInfo','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistory','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice','case_client_company'));
            /* $pdf = new Pdf;
            if($_SERVER['SERVER_NAME']=='localhost'){
                $pdf->binary = EXE_PATH;
            }
            $pdf->addPage($PDFData);
            $pdf->setOptions(['javascript-delay' => 5000]);
            $pdf->saveAs(public_path("download/pdf/".$filename));
            $path = public_path("download/pdf/".$filename); */
            $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
            // return response()->download($path);
            // exit;

            // return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
            return response()->json([ 'success' => true, "url" => $pdfUrl,"file_name"=>$filename], 200);
            exit;
        }
    }

    /**
     * Generate invoice pdf and store it
     */
    public function generateInvoicePdf($pdfData, $filename)
    {
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        }
        $pdf->addPage($pdfData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->saveAs(Storage::path('download/pdf/'.$filename));

		if (!File::isDirectory('download')) {
			File::makeDirectory('download', 0755, true, true);
		}		
		$subDirectory = Storage::path("download/pdf");
		if (!File::isDirectory($subDirectory)) {
			File::makeDirectory($subDirectory, 0755, true, true);
		}
        if (!$pdf->saveAs($subDirectory.'/'.$filename)) {
            Log::info("Generate pdf error: ". $pdf->getError());
        }
        return asset(Storage::url("download/pdf/".$filename));
    }

    public function generateInvoicePdfAndSave(Request $request)
    {
        
        $invoice_id=base64_decode($request->id);
        $Invoice=Invoices::where("id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();

        $caseMaster=CaseMaster::find($Invoice['case_id']);
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        

        $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();

        $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();
        $firmData=Firm::find($userData['firm_name']);

        //Get the Adjustment list
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();

        $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();

        $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();

        //Get the flat fee Entry list
        $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
        ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
        ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
        ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
        ->get();
        
        $filename="Invoice_".$invoice_id.'.pdf';
        $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData', 'firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceInstallment','InvoiceHistory','InvoiceHistoryTransaction','FlatFeeEntryForInvoice'));
        /* $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->saveAs(Storage::path('download/pdf/'.$filename));

		if (!File::isDirectory('download')) {
			File::makeDirectory('download', 0755, true, true);
		}		
		$subDirectory = Storage::path("download/pdf");
		if (!File::isDirectory($subDirectory)) {
			File::makeDirectory($subDirectory, 0755, true, true);
		}
        if (!$pdf->saveAs($subDirectory.'/'.$filename)) {
            Log::info("Generate pdf error: ". $pdf->getError());
        } */
        $this->generateInvoicePdf($PDFData, $filename);
        return true;
    }
    public function invoiceInlineView(Request $request)
    {
        
        $invoice_idEncoded=$request->id;
        $Invoice=Invoices::where("invoice_token",$invoice_idEncoded)->first();
        $invoice_id=$Invoice['id'];
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
       
        $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
        $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
        ->where("user_id",$Invoice['user_id'])
        ->first();

        $caseMaster=CaseMaster::find($Invoice['case_id']);
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        $firmData=Firm::find($userData['firm_name']);

        //Get the flat fee Entry list
        $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
        ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
        ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
        ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
        ->get();

        $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();

        $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();

        //Get the Adjustment list
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();

        $InvoiceInstallment=InvoiceInstallment::select("*")
        ->where("invoice_installment.invoice_id",$invoice_id)
        ->get();
    
        //check case client company is list out on contacts
        $case_client_company = [];
        $caseCllientSelection = CaseClientSelection::select("*")->where("case_id",$Invoice->case_id)->get()->pluck("selected_user");
        foreach($caseCllientSelection as $key=>$val){
            if(in_array($val,explode(",",$UsersAdditionalInfo->multiple_compnay_id))){ 
                $case_client_company = User::find($val);
            }
        }
        //check case client company is list out on contacts

        $filename="Invoice_".$invoice_id.'.pdf';
        $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','UsersAdditionalInfo','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistoryTransaction','InvoiceInstallment','FlatFeeEntryForInvoice','case_client_company'));
        $pdf = new Pdf;
        $pdf->setOptions(['javascript-delay' => 5000]);
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        }
        $pdf->addPage($PDFData);
        if (!$pdf->send()) {
            $error = $pdf->getError();
        }

        //inline pdf display
    }

    public function emailInvoice(Request $request)
    {
        // echo Hash::make($request->id);

        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $Invoices=Invoices::where("invoice_unique_token",$request->id)->first();
            if(!empty($Invoices)){
                $userData=User::find($Invoices['user_id']);
                if($Invoices['case_id'] == 0){
                    $getAllClientForSharing=CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users.last_login")->where("case_client_selection.case_id",$Invoices['case_id'])->where("case_client_selection.selected_user",$Invoices['user_id'])->get();
                }else{
                    $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users.last_login")->where("case_client_selection.case_id",$Invoices['case_id'])->get();
                }
                
                if(count($getAllClientForSharing) == 0){
                    $getAllClientForSharing[] = User::find($Invoices['user_id']); 
                }

                $firmAddress = Firm::find($userData['firm_name']);
                return view('billing.invoices.emailInvoicePopup',compact('Invoices','getAllClientForSharing','firmAddress'));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }

    public function saveSendReminderWithAttachment(Request $request)
    {
        // return asset(Storage::url("download/pdf/Invoice_86.pdf"));
        $validator = \Validator::make($request->all(), [
            'client' => 'required|array|min:1'
        ],
        ['min'=>'Please choose at least one contact.',
        'required'=>'Please choose at least one contact']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $error = [];
            foreach($request->client as $k=>$v) { 
                if($request['new_email-'.$v] == ''){
                    $error[$k] = 'Email Address is required';
                }
            }
            if(!empty($error)){
                return response()->json(['errors'=>$error]);
            }            
            // re-generate pdf file with updated info
            $invoice_id= $request->invoice_id;
            
            $Invoice=Invoices::find($invoice_id);

            $this->updateInvoiceDraftStatus($invoice_id);
            // if($Invoice->status=="Draft" || $Invoice->status=="Unsent"){
            //     $Invoice->status="Sent";
            //     $Invoice->is_sent="yes";
            //     $Invoice->bill_sent_status="Sent";
            //     $Invoice->save();
            // }
            // if($Invoice) {
            //     $Invoice->fill(['is_sent' => 'yes', "bill_sent_status" => "Sent"])->save();
            // }
            
            $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
            
            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
            ->where("user_id",$Invoice['user_id'])
            ->first();
    
            $caseMaster=CaseMaster::find($Invoice['case_id']);
            //Getting firm related data
            $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
            
    
            $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();
    
            $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();
            $firmData=Firm::find($userData['firm_name']);
    
            //Get the Adjustment list
            $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();
    
            $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
    
            $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
            $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();
    
            //Get the flat fee Entry list
            $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
            ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
            ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
            ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
            ->get();            
    
            //check case client company is list out on contacts
            $case_client_company = [];
            $caseCllientSelection = CaseClientSelection::select("*")->where("case_id",$Invoice->case_id)->get()->pluck("selected_user");
            foreach($caseCllientSelection as $key=>$val){
                if(in_array($val,explode(",",$UsersAdditionalInfo->multiple_compnay_id))){ 
                    $case_client_company = User::find($val);
                }
            }
            //check case client company is list out on contacts
            $this->updateInvoiceAmount($invoice_id);
            $filename="Invoice_".$invoice_id.'.pdf';
            $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','UsersAdditionalInfo','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistory','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice','case_client_company'));
            $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
            // end
            foreach($request->client as $k=>$v){
                $findUSer = User::whereId($v)->with(["userAdditionalInfo" => function($query) {
                    $query->select("user_id", "client_portal_enable");
                }])->first();
                if($findUSer['email'] == '' || $findUSer['email'] == NULL){
                    $findUSer->email = $request['new_email-'.$v];
                    $findUSer->save();
                    $email=$request['new_email-'.$v];
                }else{
                    $email=$findUSer['email'];
                }
                $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];
                        
                $firmData=Firm::find(Auth::User()->firm_name);
                $getTemplateData = EmailTemplate::find(15);
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{message}', $request->message, $mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        
    
                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => $firmData->firm_name,
                    "subject" => "New Invoice from ".$firmData->firm_name,
                    "to" => $email,
                    "full_name" => $fullName,
                    "mail_body" => $mail_body
                ];
                // $files=[BASE_URL."public/download/pdf/Invoice_".$invoice_id.".pdf"];
                // $files = [asset(Storage::url("download/pdf/Invoice_".$invoice_id.".pdf"))];
                $files = [Storage::path("download/pdf/Invoice_".$invoice_id.".pdf")];
                // $sendEmail = $this->sendMailWithAttachment($user,$files);
                
                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$invoice_id;
                $invoiceHistory['acrtivity_title']='Emailed Invoice';
                $invoiceHistory['pay_method']=NULL;
                $invoiceHistory['amount']=NULL;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['deposit_into']=NULL;
                $invoiceHistory['deposit_into_id']=NULL;
                $invoiceHistory['invoice_payment_id']=NULL;
                $invoiceHistory['notes']="To ". $fullName." (Client)";
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $this->invoiceHistory($invoiceHistory);

                // Add history
                $data=[];
                $data['invoice_id']=$invoice_id;
                $data['user_id']=$v;
                $data['client_id']=$v;
                $data['case_id']=$Invoice['case_id'];
                $data['activity']='emailed invoice';
                $data['activity_for']=$invoice_id;
                $data['type']='invoices';
                $data['action']='email';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                // Add history for client
                if($findUSer && $findUSer->userAdditionalInfo && $findUSer->userAdditionalInfo->client_portal_enable == 1) {
                    $data=[];
                    $data['invoice_id']=$invoice_id;
                    $data['user_id']=$v;
                    $data['client_id']=$v;
                    $data['activity']='shared invoice';
                    $data['activity_for']=$invoice_id;
                    $data['type']='invoices';
                    $data['action']='email';
                    $data['is_for_client']='yes';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                }
            }
            session(['popup_success' => 'Reminders have been sent']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }
    //Edit invoice
    public function editInvoice(Request $request)
    {
        $invoiceID=base64_decode($request->id);
        $findInvoice=Invoices::whereId($invoiceID)->with("forwardedInvoices", "applyTrustFund", "applyCreditFund")->first();
        if(empty($findInvoice) || $findInvoice->created_by!=Auth::User()->id)
        {
            return view('pages.404');
        }else{
            $bill_from_date='';
            $bill_to_date='';
            $filterByDate='';
            if(isset($request->bill_from_date) && isset($request->bill_to_date) && $request->bill_from_date!=NULL && $request->bill_to_date!=NULL){
                $bill_from_date=$request->bill_from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }        
            $case_id=$findInvoice->case_id;

            if((isset($request->adjustment_delete) && $request->adjustment_delete!="") || isset($request->removeAllCreatedEntry)){
                $TimeEntry=TaskTimeEntry::where("task_time_entry.case_id",$case_id)
                ->where("task_time_entry.status","unpaid")
                ->where("task_time_entry.token_id","9999999")
                ->get();
                foreach($TimeEntry as $k=>$v){
                    TimeEntryForInvoice::where("time_entry_id",$v->id)->delete();
                    DB::table('task_time_entry')->where("id",$v->id)->delete();
                }
                $ExpenseEntry=ExpenseEntry::where("expense_entry.case_id",$case_id)
                ->where("expense_entry.status","unpaid")->where("expense_entry.token_id","9999999")
                ->get();
                foreach($ExpenseEntry as $k=>$v){
                    ExpenseForInvoice::where("expense_entry_id",$v->id)->delete();
                    DB::table('expense_entry')->where("id",$v->id)->delete();
                }
                
                $FlatFeeEntry=FlatFeeEntry::where("flat_fee_entry.case_id",$case_id)
                ->where("flat_fee_entry.status","unpaid")->where("flat_fee_entry.token_id","=",'9999999')
                ->get();
                foreach($FlatFeeEntry as $k=>$v){
                    FlatFeeEntryForInvoice::where("flat_fee_entry_for_invoice.flat_fee_entry_id",$v->id)->delete();
                    DB::table('flat_fee_entry')->where("id",$v->id)->delete();
                }
                
                $InvoiceAdjustment=InvoiceAdjustment::where("invoice_adjustment.case_id",$case_id)
                ->where("invoice_adjustment.token",$request->token)->delete();

                if(isset($request->removeAllCreatedEntry)){
                    return redirect('bills/invoices/'.base64_encode($findInvoice->id).'/edit?token='.base64_encode($findInvoice->id));
                }
            }
            //Get all client related to firm
            // $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
            $ClientList = userClientList();

            //Get all company related to firm
            // $CompanyList = User::select("email","first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
            $CompanyList = userCompanyList();

            $caseClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$case_id)->where('case_client_selection.is_billing_contact','yes')->select("*")->first();
          
            $client_id=$findInvoice->user_id;

            $userData=User::find($client_id);
            // $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$client_id)->first();

            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*","users.state","countries.name as county_name")
            ->where("user_id",$client_id)
            ->first();


            $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users_additional_info.multiple_compnay_id","case_client_selection.is_billing_contact")->where("case_client_selection.case_id",$case_id)->get();

            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.selected_user",$client_id)->get()->pluck("case_id");

            //List all case by client 
            $caseListByClient = CaseMaster::select("*")->whereIn('case_master.id',$caseCllientSelection)->select("*")->get();
            
            //Get the case data
            $caseMaster = CaseMaster::whereId($case_id)->with("caseAllClient")->first();

            //Get the Time Entry list
            
            $TimeEntry=TimeEntryForInvoice::leftJoin("task_time_entry","time_entry_for_invoice.time_entry_id","=","task_time_entry.id");
            $TimeEntry=$TimeEntry->leftJoin("users","users.id","=","task_time_entry.user_id");
            $TimeEntry=$TimeEntry->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id");
            $TimeEntry=$TimeEntry->select("task_time_entry.*","task_activity.*","users.*","task_time_entry.id as itd");
            $TimeEntry=$TimeEntry->where("time_entry_for_invoice.invoice_id",$invoiceID);
            // ->where("task_time_entry.remove_from_current_invoice","no")
            // if(isset($request->bill_from_date) && isset($request->bill_to_date) && $request->bill_from_date!=NULL && $request->bill_to_date!=NULL){
            //     $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_from_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_to_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $TimeEntry=$TimeEntry->whereBetween('task_time_entry.entry_date', [$startDt,$endDt]);
            // }
            $TimeEntry=$TimeEntry->get();
        
            //Get the Expense Entry list
            $ExpenseEntry=ExpenseForInvoice::leftJoin("expense_entry","expense_for_invoice.expense_entry_id","=","expense_entry.id");
            $ExpenseEntry=$ExpenseEntry->leftJoin("users","users.id","=","expense_entry.user_id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select("expense_entry.*","task_activity.*","users.*","expense_entry.id as eid")->where("expense_entry.case_id",$case_id);
            // ->where("expense_entry.remove_from_current_invoice","no")
            $ExpenseEntry=$ExpenseEntry->where("expense_for_invoice.invoice_id",$invoiceID);
            // if(isset($request->bill_from_date) && isset($request->bill_to_date) && $request->bill_from_date!=NULL && $request->bill_to_date!=NULL){
            //     $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_from_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_to_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $ExpenseEntry=$ExpenseEntry->whereBetween('expense_entry.entry_date', [$startDt,$endDt]);
            // }
            $ExpenseEntry=$ExpenseEntry->get();
            
            //Get the Adjustment list
            $Adjustment_token = InvoiceAdjustment::where("invoice_adjustment.token",$request->token)->get();
            if($Adjustment_token->count() > 0 ){
                $InvoiceAdjustment=InvoiceAdjustment::select("*")
                ->where("invoice_adjustment.token",$request->token)
                ->where("invoice_adjustment.case_id",$case_id)->get();
            }else{
                $InvoiceAdjustment=InvoiceAdjustment::select("*")
                ->where("invoice_adjustment.invoice_id",$invoiceID)
                ->where("invoice_adjustment.amount",">",0)->get();
            }
            $InvoiceInstallment=InvoiceInstallment::select("*")
            ->where("invoice_installment.invoice_id",$invoiceID)
            ->get();
                        
            //Get the flat fee Entry list
            if((isset($request->adjustment_delete) && $request->adjustment_delete!="") || isset($request->removeAllCreatedEntry)){
                $FlatFeeEntry=FlatFeeEntry::where("flat_fee_entry.case_id",$case_id)
                ->where("flat_fee_entry.status","unpaid")->where("flat_fee_entry.token_id","=",'9999999')
                ->get();
                foreach($FlatFeeEntry as $k=>$v){
                    FlatFeeEntryForInvoice::where("flat_fee_entry_for_invoice.flat_fee_entry_id",$v->id)->delete();
                    DB::table('flat_fee_entry')->where("id",$v->id)->delete();
                }
            }
            $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id");
            $FlatFeeEntryForInvoice=$FlatFeeEntryForInvoice->leftJoin("users","users.id","=","flat_fee_entry.user_id");
            $FlatFeeEntryForInvoice=$FlatFeeEntryForInvoice->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd");
            $FlatFeeEntryForInvoice=$FlatFeeEntryForInvoice->where("flat_fee_entry_for_invoice.invoice_id",$invoiceID);
            // if(isset($request->bill_from_date) && isset($request->bill_to_date) && $request->bill_from_date!=NULL && $request->bill_to_date!=NULL){
            //     $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_from_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($request->bill_to_date))))), auth()->user()->user_timezone ?? 'UTC')));
            //     $FlatFeeEntryForInvoice=$FlatFeeEntryForInvoice->whereBetween('flat_fee_entry_for_invoice.created_at', [$startDt,$endDt]);
            // }
            $FlatFeeEntryForInvoice=$FlatFeeEntryForInvoice->get();

            $SharedInvoice=SharedInvoice::select("*")->where("invoice_id",$invoiceID)->get()->pluck('user_id')->toArray();

            $adjustment_token=$request->token;

            // Get unpaid balances invoices list
            $unpaidInvoices = [];
            if($caseMaster) {
                $selectedFwdInv = [];
                if(count($findInvoice->forwardedInvoices)) {
                    $selectedFwdInv = $findInvoice->forwardedInvoices->pluck("id")->toArray();
                }
                $unpaidInvoices = Invoices::where("case_id", $caseMaster->id)->where("due_amount", ">", 0)->where("status", "!=", "Forwarded")->where("id","!=", $findInvoice->id)->orWhereIn('id', $selectedFwdInv)->get();
            }
            $invoiceSetting = $findInvoice->invoice_setting;
            $invoiceDefaultSetting = getInvoiceSetting();
            $invoiceTempInfo = InvoiceTempInfo::where('invoice_unique_id', $request->token)->where('case_id', $case_id)->get();
            return view('billing.invoices.edit_invoices',compact('ClientList','CompanyList','client_id','case_id','caseListByClient','caseMaster','TimeEntry','ExpenseEntry','InvoiceAdjustment','userData','UsersAdditionalInfo','getAllClientForSharing','adjustment_token','findInvoice','InvoiceInstallment','SharedInvoice','FlatFeeEntryForInvoice', 'unpaidInvoices', 'invoiceSetting', 'invoiceDefaultSetting','bill_from_date','bill_to_date','filterByDate', 'invoiceTempInfo'));
        }
    }

    public function updateInvoiceBatchCount($invoice_id, $oldStatus, $newStatus){
        $InvoiceBatch=InvoiceBatch::where('invoice_id','like', "%".$invoice_id."%")->first();
        if(!empty($InvoiceBatch)){
            switch ($oldStatus) {
                case 'Unsent':
                    $InvoiceBatch->unsent_invoice = ($InvoiceBatch->unsent_invoice > 0 ) ? $InvoiceBatch->unsent_invoice - 1 : 0;
                    break;
                case 'Sent':
                    $InvoiceBatch->sent_invoice = ($InvoiceBatch->sent_invoice > 0 ) ? $InvoiceBatch->sent_invoice - 1 : 0;
                    break;
                default:
                    $InvoiceBatch->draft_invoice = ($InvoiceBatch->draft_invoice > 0 ) ?  $InvoiceBatch->draft_invoice - 1 : 0;
                    break;
            }
            $InvoiceBatch->save(); 
            switch ($newStatus) {
                case 'Unsent':
                    $InvoiceBatch->unsent_invoice = $InvoiceBatch->unsent_invoice + 1;
                    break;
                case 'Sent':
                    $InvoiceBatch->sent_invoice = $InvoiceBatch->sent_invoice + 1;
                    break;
                default:
                    $InvoiceBatch->draft_invoice = $InvoiceBatch->draft_invoice + 1;
                    break;
            }
            $InvoiceBatch->save(); 
        }    
           
    }

    public function updateInvoiceEntry(Request $request)
    {
        // return $request->all();
        $InvoiceSave=Invoices::find($request->invoice_id);
        $rules = [
            'invoice_number_padded' => 'required|numeric',
            'court_case_id' => 'required'/* |numeric */,
            'contact' => 'required|numeric',
            'total_text' => 'required',
        ];
        if(!empty($request->flatFeeEntrySelectedArray) && count($request->flatFeeEntrySelectedArray)) {
            $rules['timeEntrySelectedArray'] = 'nullable|array';
            $rules['expenseEntrySelectedArray'] = 'nullable|array';
        } else {
            if(empty($request->forwarded_invoices)){
                $rules['timeEntrySelectedArray'] = 'required_without:expenseEntrySelectedArray|array';
                $rules['expenseEntrySelectedArray'] = 'required_without:timeEntrySelectedArray|array';
            }
        }
        if($InvoiceSave->status == "Paid" && $request->final_total_text < $InvoiceSave->total_amount) {
            $rules['final_total_text'] = 'gte:'.$InvoiceSave->total_amount;
        }
        $paymentPlanAmount = 0;
        if(isset($request->new_payment_plans)){
            foreach($request->new_payment_plans as $k=>$v){
                $paymentPlanAmount += (float) str_replace(',', '', $v['amount']);
            }
        }
        $paymentPlanAmount = (float) str_replace(',', '', number_format($paymentPlanAmount,2));
        if($request->payment_plan == "on" && $request->final_total_text != $paymentPlanAmount){
            $rules['new_payment_plans'] = 'required|min:'.$request->final_total_text;
        }        
        $request->validate($rules, [
            "invoice_number_padded.required"=>"Invoice number must be greater than 0",
            "invoice_number_padded.numeric"=>"Invoice number must be greater than 0",
            "contact.required"=>"Billing user can't be blank",
            "timeEntrySelectedArray.required"=>"You are attempting to save a blank invoice, please add time entries activity.",
            "expenseEntrySelectedArray.required"=>"You are attempting to save a blank invoice, please add expenses activity",
            "final_total_text.gte" => "You cannot lower the amount of this invoice below $".$InvoiceSave->total_amount." because payments have already been received for that amount.",
            "new_payment_plans.min"=>"Payment plans must add up to the same total as the invoice."
        ]);
        
            // print_r($request->all());exit;
            $InvoiceSave->user_id=$request->contact;
            $InvoiceSave->case_id=($request->court_case_id == "none") ? 0 : $request->court_case_id;
            $InvoiceSave->invoice_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->bill_invoice_date)))), auth()->user()->user_timezone ?? 'UTC');
            $InvoiceSave->bill_address_text=$request->bill_address_text;            
            if($request->payment_terms==""){
                $InvoiceSave->payment_term="5";
            }else{
                $InvoiceSave->payment_term=$request->payment_terms;
            }
            $InvoiceSave->due_date=($request->bill_due_date) ? date('Y-m-d',strtotime($request->bill_due_date)) : NULL;   
            if(isset($request->automated_reminders)){
                $InvoiceSave->automated_reminder="yes";
            }else{
                $InvoiceSave->automated_reminder="no";
            }

            if(isset($request->payment_plan)){
                $InvoiceSave->payment_plan_enabled="yes";
            }else{
                $InvoiceSave->payment_plan_enabled="no";
            }
            $oldStatus = $InvoiceSave->bill_sent_status;
            $newStatus = $request->bill_sent_status;
            $this->updateInvoiceBatchCount($request->invoice_id, $oldStatus, $newStatus);
            $InvoiceSave->bill_sent_status = $request->bill_sent_status;
            // $InvoiceSave->status=$request->bill_sent_status;
            $InvoiceSave->total_amount=$request->final_total_text;
            $InvoiceSave->due_amount = $request->final_total_text - $InvoiceSave->paid_amount;
            $InvoiceSave->terms_condition=$request->bill['terms_and_conditions'];
            $InvoiceSave->notes=$request->bill['bill_notes'];
            if(!in_array($InvoiceSave->status, ['Partial','Paid','Forwarded','Overdue'])) {
                $InvoiceSave->status = $request->bill_sent_status;
            }
            if($request->bill_sent_status == "Draft") {
                $InvoiceSave->status = $request->bill_sent_status;
            }else{
                if($request->bill_due_date != ''){
                    if(strtotime($request->bill_invoice_date) <= strtotime($request->bill_due_date)){
                        $InvoiceSave->status = "Overdue";
                    }
                }else{
                    if($InvoiceSave->paid_amount > 0){
                        $InvoiceSave->status = "Partial";
                    }else{
                        $InvoiceSave->status = $request->bill_sent_status;
                    }
                }                       
            }

            // Check if invouce due date changed then update invoice settings
            if($request->bill_due_date && strtotime($request->bill_invoice_date) != strtotime($InvoiceSave->due_date)) {
                // Update invoice settings
                $InvoiceSave->invoice_setting = $this->updateInvoiceSetting($InvoiceSave) ?? $InvoiceSave->invoice_setting;
            }

            $InvoiceSave->is_sent = ($request->bill_sent_status == "Sent") ? "yes" : "no";
            $InvoiceSave->firm_id = auth()->user()->firm_name; 
            $InvoiceSave->updated_by=Auth::User()->id; 
            $InvoiceSave->updated_at=date('Y-m-d h:i:s'); 
            $InvoiceSave->save();

            session(['invoiceUpdate' => true]);

            $InvoiceSave->refresh();
            $this->updateInvoiceAmount($InvoiceSave->id);

            $invoiceHistory=[];
            $invoiceHistory['invoice_id']=$InvoiceSave->id;
            $invoiceHistory['acrtivity_title']='Invoice updated';
            $invoiceHistory['pay_method']=NULL;
            $invoiceHistory['amount']=NULL;
            $invoiceHistory['responsible_user']=Auth::User()->id;
            $invoiceHistory['deposit_into']=NULL;
            $invoiceHistory['notes']=NULL;
            $invoiceHistory['created_by']=Auth::User()->id;
            $invoiceHistory['created_at']=date('Y-m-d H:i:s');
            $this->invoiceHistory($invoiceHistory);

            InvoiceAdjustment::where('token',$request->adjustment_token)->update(['invoice_id'=>$InvoiceSave->id]);

            //Flat fees referance
            if(!empty($request->flatFeeEntrySelectedArray)){
                FlatFeeEntryForInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                foreach($request->flatFeeEntrySelectedArray as $k=>$v){
                    $FlatFeeEntryForInvoice=new FlatFeeEntryForInvoice;
                    $FlatFeeEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                    $FlatFeeEntryForInvoice->flat_fee_entry_id=$v;
                    $FlatFeeEntryForInvoice->created_by=Auth::User()->id; 
                    $FlatFeeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                    $FlatFeeEntryForInvoice->save();
                    DB::table('flat_fee_entry')->where("id",$v)->update([
                        'status'=>'paid',
                        'invoice_link'=>$InvoiceSave->id
                    ]);
                }
            }

            //Time entry referance
            if(!empty($request->timeEntrySelectedArray)){
                TimeEntryForInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                foreach($request->timeEntrySelectedArray as $k=>$v){
                    $TimeEntryForInvoice=new TimeEntryForInvoice;
                    $TimeEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                    $TimeEntryForInvoice->time_entry_id=$v;
                    $TimeEntryForInvoice->created_by=Auth::User()->id; 
                    $TimeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                    $TimeEntryForInvoice->save();
                    // if(empty($request->linked_staff_checked_share) || !in_array($v,$request->linked_staff_checked_share)){
                        DB::table('task_time_entry')->where("id",$v)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                    // }
                }
            }
            //Expense entry referance
            if(!empty($request->expenseEntrySelectedArray)){
                ExpenseForInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                foreach($request->expenseEntrySelectedArray as $k=>$v){
                    $ExpenseEntryForInvoice=new ExpenseForInvoice;
                    $ExpenseEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                    $ExpenseEntryForInvoice->expense_entry_id =$v;
                    $ExpenseEntryForInvoice->created_by=Auth::User()->id; 
                    $ExpenseEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                    $ExpenseEntryForInvoice->save();
                    // if(empty($request->invoice_expense_entry_nonbillable_time) || !in_array($v,$request->invoice_expense_entry_nonbillable_time)){
                        DB::table('expense_entry')->where("id",$v)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                    // }
                }
            }

            //Invoice Shared With Client
            if(!empty($request->portalAccess)){
                $sharedList=[];
                foreach($request->portalAccess as $k=>$v){
                    $alreadyShared=SharedInvoice::where("user_id",$v)->where("invoice_id",$InvoiceSave->id)->count();
                    if($alreadyShared<=0){
                        $SharedInvoice=new SharedInvoice;
                        $SharedInvoice->invoice_id=$InvoiceSave->id;                    
                        $SharedInvoice->user_id =$v;
                        $SharedInvoice->created_by=Auth::User()->id; 
                        $SharedInvoice->created_at=date('Y-m-d h:i:s'); 
                        $SharedInvoice->save();
                        
                        $invoiceHistory=[];
                        $invoiceHistory['invoice_id']=$InvoiceSave->id;
                        $invoiceHistory['acrtivity_title']='Shared w/Contacts';
                        $invoiceHistory['pay_method']=NULL;
                        $invoiceHistory['amount']=NULL;
                        $invoiceHistory['responsible_user']=Auth::User()->id;
                        $invoiceHistory['deposit_into']=NULL;
                        $invoiceHistory['notes']=NULL;
                        $invoiceHistory['created_by']=Auth::User()->id;
                        $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                        $this->invoiceHistory($invoiceHistory);
                    
                    }
                    $sharedList[]=$v;
                }
                $alreadyShared=SharedInvoice::where("invoice_id",$InvoiceSave->id)->whereNotIn("user_id",$sharedList)->get();
                foreach($alreadyShared as $kk=>$vv){
                    if(!in_array($vv->user_id,$sharedList)){
                        SharedInvoice::where("user_id",$vv->user_id)->where("invoice_id",$InvoiceSave->id)->delete();

                        $invoiceHistory=[];
                        $invoiceHistory['invoice_id']=$InvoiceSave->id;
                        $invoiceHistory['acrtivity_title']='Unshared w/Contacts';
                        $invoiceHistory['pay_method']=NULL;
                        $invoiceHistory['amount']=NULL;
                        $invoiceHistory['responsible_user']=Auth::User()->id;
                        $invoiceHistory['deposit_into']=NULL;
                        $invoiceHistory['notes']=NULL;
                        $invoiceHistory['created_by']=Auth::User()->id;
                        $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                        $this->invoiceHistory($invoiceHistory);
                    }
                }

            }

            if(isset($request->payment_plan)){
                if(isset($request->amount_per_installment_field) && isset($request->number_installment_field)){
                    InvoicePaymentPlan::where("invoice_id",$InvoiceSave->id)->delete();
                    $InvoicePaymentPlan=new InvoicePaymentPlan;
                    $InvoicePaymentPlan->invoice_id=$InvoiceSave->id;                    
                    $InvoicePaymentPlan->start_date=date('Y-m-d',strtotime($request->start_date));
                    $InvoicePaymentPlan->per_installment_amt=str_replace(",","",$request->amount_per_installment_field);                    
                    $InvoicePaymentPlan->no_of_installment=$request->number_installment_field;                    
                    $InvoicePaymentPlan->repeat_by=$request->installment_frequency_field;
                    if(isset($request->with_first_payment)){                    
                        $InvoicePaymentPlan->is_set_first_installment=$request->with_first_payment;                    
                        $InvoicePaymentPlan->first_installment_amount=$request->first_payment_amount;                    
                    }
                    $InvoicePaymentPlan->created_by=Auth::User()->id; 
                    $InvoicePaymentPlan->created_at=date('Y-m-d h:i:s'); 
                    $InvoicePaymentPlan->save();
                    // Invoice Installment entry
                    InvoiceInstallment::where("invoice_id",$InvoiceSave->id)->delete();
                    $paidAmt = $InvoiceSave->paid_amount;
                    foreach($request->new_payment_plans as $kk=>$vv){
                        $InvoiceInstallment=new InvoiceInstallment;
                        $InvoiceInstallment->invoice_id=$InvoiceSave->id;                    
                        $InvoiceInstallment->installment_amount=str_replace(",","",$vv['amount']);                                        
                        $InvoiceInstallment->due_date=date('Y-m-d',strtotime($vv['due_date']));
                        $InvoiceInstallment->created_by=Auth::User()->id; 
                        $InvoiceInstallment->firm_id=Auth::User()->firm_name;
                        if($paidAmt > 0){
                            if($paidAmt >= str_replace(",","",$vv['amount'])){
                                $InvoiceInstallment->status = 'paid';
                                $InvoiceInstallment->paid_date =  date('Y-m-d h:i:s');
                                $InvoiceInstallment->adjustment = str_replace(",","",$vv['amount']);
                                $paidAmt = $paidAmt - str_replace(",","",$vv['amount']);
                            }else{
                                $InvoiceInstallment->paid_date =  date('Y-m-d h:i:s');
                                $InvoiceInstallment->adjustment = $paidAmt;
                                $paidAmt = 0;
                            }
                        } 
                        $InvoiceInstallment->created_at=date('Y-m-d h:i:s'); 
                        $InvoiceInstallment->save();
                    }
                    // Update invoice settings
                    $InvoiceSave->invoice_setting = $this->updateInvoiceSetting($InvoiceSave) ?? $InvoiceSave->invoice_setting;
                }
            }else{
                InvoicePaymentPlan::where("invoice_id",$InvoiceSave->id)->delete();
                InvoiceInstallment::where("invoice_id",$InvoiceSave->id)->delete();
                // Update invoice settings
                $InvoiceSave->invoice_setting = $this->updateInvoiceSetting($InvoiceSave) ?? $InvoiceSave->invoice_setting;
            }

            if(!empty($request->forwarded_invoices)) {
                // Get old synced invoices id
                $syncedInvoices = $InvoiceSave->forwardedInvoices()->pluck('id')->toArray();
                $unsyncedInvoicesId = array_diff($syncedInvoices, $request->forwarded_invoices);
                if(count($unsyncedInvoicesId)) {
                    $unsyncedInvoices = Invoices::whereIn("id", $unsyncedInvoicesId)->where('status', 'Forwarded')->get();
                    if($unsyncedInvoices) {
                        foreach($unsyncedInvoices as $key => $item) {
                            $this->updateInvoiceAmount(@$item->id);
                        }
                    }
                }
                // Sync new/update invoices
                $InvoiceSave->forwardedInvoices()->sync($request->forwarded_invoices);
                $forwardedInvoices = Invoices::whereIn("id", $request->forwarded_invoices)->get();
                if($forwardedInvoices) {
                    foreach($forwardedInvoices as $key => $item) {
                        $this->updateInvoiceDraftStatus($InvoiceSave->invoice_id);
                        $item->fill(["status" => "Forwarded"])->save();
                        InvoiceHistory::create([
                            "invoice_id" => $item->id,
                            "acrtivity_title" => "balance forwarded",
                            "amount" => $item->due_amount,
                            "responsible_user" => auth()->id(),
                            "notes" => "Forwarded to ".$InvoiceSave->invoice_id,
                            "created_by" => auth()->id()
                        ]);
                    }
                }
            } else {
                $syncedInvoices = $InvoiceSave->forwardedInvoices()->pluck('id')->toArray();
                $forwardInv = Invoices::whereIn("id", $syncedInvoices)->get();
                if($forwardInv) {
                    foreach($forwardInv as $key => $item) {
                        $this->updateInvoiceAmount(@$item->id);
                        InvoiceHistory::create([
                            "invoice_id" => @$item->id,
                            "acrtivity_title" => "invoice reopened",
                            "amount" => @$item->due_amount,
                            "responsible_user" => auth()->id(),
                            "notes" => "Balance was unforwarded",
                            "created_by" => auth()->id()
                        ]);
                    }
                }
                $syncedInvoices = $InvoiceSave->forwardedInvoices()->sync([]);
            }

            //Add Invoice history
            $data=[];
            $data['case_id']=$InvoiceSave->case_id;
            $data['user_id']=$InvoiceSave->user_id;
            $data['activity']='updated an invoice';
            $data['activity_for']=$InvoiceSave->id;
            $data['type']='invoices';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            // Apply trust and credit funds
            if(!empty($request->trust)) {
                foreach($request->trust as $key => $item) {
                    // $appliedTrust = InvoiceApplyTrustCreditFund::whereId(@$item['id'])->first();
                    $trustHistoryLast = TrustHistory::where("client_id", @$item['client_id'])->orderBy('created_at', 'desc')->first();
                    /* $trustData = [
                        'invoice_id' => $InvoiceSave->id,
                        'client_id' => @$item['client_id'] ?? NUll,
                        'case_id' => ($request->court_case_id == "none") ? 0 : $request->court_case_id,
                        'account_type' => 'trust',
                        'show_trust_account_history' => @$item['show_trust_account_history'] ?? "dont show",
                    ]; */
                    /* if($appliedTrust) {
                        if(@$appliedTrust->show_trust_account_history != 'trust account history' && @$item['show_trust_account_history'] == 'trust account history') {
                            $trustData['history_last_id'] = @$trustHistoryLast->id;
                            $trustData['total_balance'] = @$trustHistoryLast->current_trust_balance;
                        } else if(@$appliedTrust->show_trust_account_history != 'trust account summary' && @$item['show_trust_account_history'] == 'trust account summary') {
                            $trustData['total_balance'] = @$trustHistoryLast->current_trust_balance;
                        }
                        $appliedTrust->fill($trustData + [
                            'updated_by' => auth()->id(),
                        ])->save();
                    } else { */
                        /* if($item['show_trust_account_history'] == 'trust account history') {
                            $trustData['history_last_id'] = @$trustHistoryLast->id;
                            $trustData['total_balance'] = @$trustHistoryLast->current_trust_balance;
                        } else if($item['show_trust_account_history'] == 'trust account summary') {
                            $trustData['total_balance'] = @$trustHistoryLast->current_trust_balance;
                        } else {
                            $trustData['show_trust_account_history'] = @$item['show_trust_account_history'] ?? "dont show";
                        } */
                        InvoiceApplyTrustCreditFund::updateOrCreate([
                            'invoice_id' => $InvoiceSave->id,
                            'client_id' => @$item['client_id'] ?? NUll,
                            'case_id' => ($request->court_case_id == "none") ? 0 : $request->court_case_id,
                            'account_type' => 'trust',
                            ], [
                                'show_trust_account_history' => @$item['show_trust_account_history'] ?? "dont show",
                                'history_last_id' => @$trustHistoryLast->id,
                                'total_balance' => @$trustHistoryLast->current_trust_balance,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                        ]);
                    // }
                }
            }
            if(!empty($request->credit)) {
                foreach($request->credit as $key => $item) {
                    // $appliedCredit = InvoiceApplyTrustCreditFund::whereId(@$item['id'])->first();
                    $creditHistoryLast = DepositIntoCreditHistory::where("user_id", @$item['client_id'])->orderBy('created_at', 'desc')->first();
                    /* $creditData = [
                        'invoice_id' => $InvoiceSave->id,
                        'client_id' => @$item['client_id'] ?? NUll,
                        'case_id' => ($request->court_case_id == "none") ? 0 : $request->court_case_id,
                        'account_type' => 'credit',
                        'show_credit_account_history' => @$item['show_credit_account_history'] ?? "dont show",
                    ]; */
                    /* if($appliedCredit) {
                        if(@$appliedCredit->show_credit_account_history != 'credit account history' && @$item['show_credit_account_history'] == 'credit account history') {
                            $creditData['history_last_id'] = @$creditHistoryLast->id;
                            $creditData['total_balance'] = @$creditHistoryLast->total_balance;
                        } else if(@$appliedCredit->show_credit_account_history != 'credit account summary' && @$item['show_credit_account_history'] == 'credit account summary') {
                            $creditData['total_balance'] = @$creditHistoryLast->total_balance;
                        }
                        $appliedCredit->fill($creditData + [
                            'updated_by' => auth()->id(),
                        ])->save();
                    } else { */
                        /* if($item['show_credit_account_history'] == 'credit account history') {
                            $creditData['history_last_id'] = @$creditHistoryLast->id;
                            $creditData['total_balance'] = @$creditHistoryLast->total_balance;
                        } else if($item['show_credit_account_history'] == 'credit account summary') {
                            $creditData['total_balance'] = @$creditHistoryLast->total_balance;
                        } else {
                            $creditData['show_credit_account_history'] = @$item['show_credit_account_history'] ?? "dont show";
                        } */
                        // return $creditData;
                        InvoiceApplyTrustCreditFund::updateOrCreate([
                            'invoice_id' => $InvoiceSave->id,
                            'client_id' => @$item['client_id'] ?? NUll,
                            'case_id' => ($request->court_case_id == "none") ? 0 : $request->court_case_id,
                            'account_type' => 'credit',
                            ], [
                                'show_credit_account_history' => @$item['show_credit_account_history'] ?? "dont show",
                                'history_last_id' => @$creditHistoryLast->id,
                                'total_balance' => @$creditHistoryLast->total_balance,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                        ]);
                    // }
                }
            }

            // Delete invoice temp info table detail
            InvoiceTempInfo::where('invoice_unique_id', $request->adjustment_token)->where("case_id", $InvoiceSave->case_id)->delete();

            $decodedId=base64_encode($InvoiceSave->id);
            return redirect('bills/invoices/view/'.$decodedId);
            // return response()->json(['errors'=>'','invoice_id'=>$InvoiceSave->id]);
            exit;

    }

    public function resendUpdatedInvoice(Request $request)
    {
     
        $validator = \Validator::make($request->all(), [
            'share_invoice_id' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $SharedInvoice=SharedInvoice::where("invoice_id",$request->share_invoice_id)->get();
            $FindInvoice=Invoices::find($request->share_invoice_id);
            // if($FindInvoice->status=="Draft" || $FindInvoice->status=="Unsent"){
            //     $FindInvoice->status="Sent";
            //     $FindInvoice->is_sent="yes";
            //     $FindInvoice->bill_sent_status="Sent";
            //     $FindInvoice->save();
            // }
            // if($FindInvoice) {
            //     $FindInvoice->fill(['is_sent' => 'yes',"bill_sent_status"=>"Sent"])->save();
            // }
            $this->updateInvoiceAmount($request->share_invoice_id);
            $invoice_id=$FindInvoice['id'];
            foreach($SharedInvoice as $k=>$v){
                $findUSer=User::find($v->user_id);
                $email=$findUSer['email'];
                $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];

                $firmData=Firm::find(Auth::User()->firm_name);
                $getTemplateData = EmailTemplate::find(16);
                // $token=url('activate_account/bills='.base64_encode($email).'&web_token='.$FindInvoice['invoice_unique_token']);
                $token = route("client/bills/detail", $FindInvoice->decode_id);
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{name}', $fullName,$mail_body);
                $mail_body = str_replace('{loginurl}', BASE_URL.'login',$mail_body);
                $mail_body = str_replace('{invoice}', $invoice_id,$mail_body);
                $mail_body = str_replace('{token}', $token,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        
    
                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => $firmData->firm_name,
                    "subject" => "Your invoice has been updated",
                    "to" => $email,
                    "full_name" => $fullName,
                    "mail_body" => $mail_body
                ];
                $sendEmail = $this->sendMail($user);
            }
            session(['invoiceUpdate' => '']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function payInvoice(Request $request)
    {
       $validator = \Validator::make($request->all(), [
            'id' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $invoice_id=$request->id;
            $invoiceData=Invoices::where("id",$invoice_id)->first();
            if(!empty($invoiceData)){
                $firmData=Firm::find(Auth::User()->firm_name);
                $caseMaster=CaseMaster::select("case_title")->find($invoiceData['case_id']);
                $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid", "credit_account_balance")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$invoiceData['user_id'])->first();


                $trustAccounts = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->join('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid","users.user_level","users_additional_info.trust_account_balance")->where("case_client_selection.case_id",$invoiceData['case_id'])->groupBy("case_client_selection.selected_user")->get();

                return view('billing.invoices.recordPaymentInvoice',compact('userData','firmData','invoice_id','invoiceData','caseMaster','trustAccounts'));
                exit;    
            }else{
                return response()->json(['errors'=>'error']);
            }
        }
    }
    public function saveTrustInvoicePaymentWithHistory(Request $request)
    {
        // return $request->all();
        $invoiceId=Invoices::where("invoice_unique_token",$request->invoice_id)->first();

        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($invoiceId['id']);
        $paid=$InvoiceData['paid_amount'];
        $invoice=$InvoiceData['total_amount'];
        $finalAmt=$invoice-$paid;

        $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$InvoiceData['user_id'])->first();

        $validator = \Validator::make($request->all(), [
            'trust_account' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt.'|lte:'.$userData['trust_account_balance'],
            'invoice_id' => 'required'
        ],[
            'amount.min'=>"Amount must be greater than $0.00",
            'amount.lte'=>"Account does not have sufficient funds",
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            DB::beginTransaction();
            try {
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","trust")->orderBy("created_at","DESC")->first();
                
                //Insert invoice payment record.
               $entryDone=  InvoicePayment::create([
                    'invoice_id'=>$invoiceId['id'],
                    'payment_from'=>'trust',
                    'amount_paid'=>$request->amount,
                    'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"0",
                    'payment_from_id'=>$request->trust_account,
                    'deposit_into'=>"Operating Account",
                    'total'=>($currentBalance['total']+$request->amount),
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);
                $lastInvoicePaymentId= $entryDone->id;
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();
               
                //Deduct invoice amount when payment done
                $this->updateInvoiceAmount($invoiceId['id']);

                // Deduct amount from trust account after payment.
                $trustAccountAmount=($userData['trust_account_balance']-$request->amount);
                UsersAdditionalInfo::where('user_id',$InvoiceData['user_id'])
                ->update(['trust_account_balance'=>$trustAccountAmount]);

                // Add trust history
                TrustHistory::create([
                    "client_id" => $InvoiceData['user_id'],
                    "withdraw_amount" => $request->amount,
                    "current_trust_balance" => $trustAccountAmount,
                    "payment_date" => date('Y-m-d'),
                    "payment_method" => "Trust",
                    "notes" => "Payment from Trust (Trust Account) to Operating (Operating Account)",
                    "fund_type" => 'withdraw',
                    "related_to_invoice_id" => $InvoiceData->id,
                    "created_by" => auth()->id(),
                ]);

                // For update installment amount and status
                $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$InvoiceData['id'])->first();
                if(!empty($getInstallMentIfOn)){
                    $this->installmentManagement($request->amount,$InvoiceData['id']);
                }

                DB::commit();
                //Response message
                $firmData=Firm::find(Auth::User()->firm_name);
                $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                // all good


                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$invoiceId['id'];
                $invoiceHistory['acrtivity_title']='Payment Received';
                $invoiceHistory['pay_method']='Trust';
                $invoiceHistory['amount']=$request->amount;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['deposit_into']='Operating Account';
                $invoiceHistory['deposit_into_id']=($request->trust_account)??NULL;
                $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $this->invoiceHistory($invoiceHistory);

                 //Add Invoice history
                 $data=[];
                 $data['case_id']=$InvoiceData['case_id'];
                 $data['user_id']=$InvoiceData['user_id'];
                 $data['activity']='accepted a payment of $'.number_format($request->amount,2).' (Trust)';
                 $data['activity_for']=$InvoiceData['id'];
                 $data['type']='invoices';
                 $data['action']='pay';
                 $CommonController= new CommonController();
                 $CommonController->addMultipleHistory($data);

                //Get previous amount
                $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
                $activityHistory=[];
                $activityHistory['user_id']=$InvoiceData['user_id'];
                $activityHistory['related_to']=$InvoiceData['id'];
                $activityHistory['case_id']=$InvoiceData['case_id'];
                $activityHistory['credit_amount']=0.00;
                $activityHistory['debit_amount']=$request->amount;
                if(!empty($AccountActivityData)){
                    $activityHistory['total_amount']=$AccountActivityData['total_amount']-$request->amount;

                }else{
                    $activityHistory['total_amount']=$request->amount;
                }
                // $activityHistory['total_amount']=$AccountActivityData['total_amount']-$request->amount;
                $activityHistory['entry_date']=date('Y-m-d');
                $activityHistory['notes']=$request->notes;
                $activityHistory['status']="unsent";
                $activityHistory['pay_type']="trust";
                $activityHistory['firm_id']=Auth::User()->firm_name;
                $activityHistory['section']="invoice";
                $activityHistory['created_by']=Auth::User()->id;
                $activityHistory['created_at']=date('Y-m-d H:i:s');
                $this->saveAccountActivity($activityHistory);
                
                //Get previous amount
                $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                $activityHistory=[];
                $activityHistory['user_id']=$InvoiceData['user_id'];
                $activityHistory['related_to']=$InvoiceData['id'];
                $activityHistory['case_id']=$InvoiceData['case_id'];
                $activityHistory['credit_amount']=0.00;
                $activityHistory['debit_amount']=$request->amount;
                if(!empty($AccountActivityData)){
                    $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;

                }else{
                    $activityHistory['total_amount']=$request->amount;
                }
                // $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;
                $activityHistory['entry_date']=date('Y-m-d');
                $activityHistory['notes']=$request->notes;
                $activityHistory['status']="unsent";
                $activityHistory['pay_type']="client";
                $activityHistory['from_pay']="trust";
                $activityHistory['firm_id']=Auth::User()->firm_name;
                $activityHistory['section']="invoice";
                $activityHistory['created_by']=Auth::User()->id;
                $activityHistory['created_at']=date('Y-m-d H:i:s');
                $this->saveAccountActivity($activityHistory);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors'=> [$e->getMessage()]]); //$e->getMessage()
                 exit;   
            }
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }

    public function saveInvoicePaymentWithHistory_old(Request $request)
    {
        $invoiceId=Invoices::where("invoice_unique_token",$request->invoice_id)->first();
        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($invoiceId['id']);
        $paid=$InvoiceData['paid_amount'];
        $invoice=$InvoiceData['total_amount'];
        $finalAmt=$invoice-$paid;

        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt,
            'invoice_id' => 'required'
        ],[
            'amount.min'=>"Amount must be greater than $0.00",
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            DB::beginTransaction();
            try {
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("deposit_into","Trust Account")->orderBy("created_at","DESC")->first();
                if(!empty($currentBalance['total'])){
                    $s=$currentBalance['total']+$request->amount;
                  }else{
                      $s=$request->amount;
                  }
               $entryDone=InvoicePayment::create([
                    'invoice_id'=>$invoiceId['id'],
                    'payment_from'=>'client',
                    'amount_paid'=>$request->amount,
                    'payment_method'=>$request->payment_method,
                    'deposit_into'=>$request->deposit_into,
                    'deposit_into_id'=>($request->trust_account)??NULL,
                    'notes'=>$request->notes,
                    'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"1",
                    'total'=>$s,
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);

                $lastInvoicePaymentId= $entryDone->id;
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();

                //Deduct invoice amount when payment done
                $this->updateInvoiceAmount($invoiceId['id']);
                // $totalPaid=InvoicePayment::where("invoice_id",$invoiceId['id'])->get()->sum("amount_paid");
                /* $allPayment = InvoicePayment::where("invoice_id", $invoiceId['id'])->get();
                $totalPaid = $allPayment->sum("amount_paid");
                $totalRefund = $allPayment->sum("amount_refund");
                $remainPaidAmt = ($totalPaid - $totalRefund);
                $dueAmount = ($InvoiceData['total_amount'] - $remainPaidAmt);
                if($remainPaidAmt == 0) {
                    $status="Unsent";
                } else if($dueAmount == 0) {
                    $status = "Paid";
                } else {
                    $status="Partial";
                }
                DB::table('invoices')->where("id",$invoiceId['id'])->update([
                    'paid_amount'=>$remainPaidAmt,
                    // 'due_amount'=>($InvoiceData['total_amount'] - $totalPaid),
                    'due_amount'=> $dueAmount,
                    'status'=>$status,
                ]); */

                //Deposit into trust account
                if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    $userDataForDeposit = UsersAdditionalInfo::select("trust_account_balance","user_id")->where("user_id",$request->trust_account)->first();
                    DB::table('users_additional_info')->where("user_id",$request->trust_account)->update([
                        'trust_account_balance'=>($userDataForDeposit['trust_account_balance'] + $request->amount),
                    ]);
                }
                DB::commit();
                //Response message
                $firmData=Firm::find(Auth::User()->firm_name);
                $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                // all good

                 //Code For installment amount
                 $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$InvoiceData['id'])->first();
                 if(!empty($getInstallMentIfOn)){
                     $this->installmentManagement($request->amount,$InvoiceData['id']);
                 }
                     
             
                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$invoiceId['id'];
                $invoiceHistory['acrtivity_title']='Payment Received';
                $invoiceHistory['pay_method']=$request->payment_method;
                $invoiceHistory['amount']=$request->amount;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['deposit_into']=$request->deposit_into;
                $invoiceHistory['deposit_into_id']=$request->trust_account;
                $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $this->invoiceHistory($invoiceHistory);

                 //Add Invoice history
                 $data=[];
                 $data['case_id']=$InvoiceData['case_id'];
                 $data['user_id']=$InvoiceData['user_id'];
                 $data['activity']='accepted a payment of $'.number_format($request->amount,2).' ('.ucfirst($request->payment_method).')';
                 $data['activity_for']=$InvoiceData['id'];
                 $data['type']='invoices';
                 $data['action']='pay';
                 $CommonController= new CommonController();
                 $CommonController->addMultipleHistory($data);

                  //Get previous amount
                  if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
                 }else{
                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                 }
                 $activityHistory=[];
                 $activityHistory['user_id']=$InvoiceData['user_id'];
                 $activityHistory['related_to']=$InvoiceData['id'];
                 $activityHistory['case_id']=$InvoiceData['case_id'];
                 $activityHistory['credit_amount']=$request->amount;
                 $activityHistory['debit_amount']=0.00;
                 if(!empty($AccountActivityData)){
                    $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;

                }else{
                    $activityHistory['total_amount']=$request->amount;
                }
                //  $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;
                 $activityHistory['entry_date']=date('Y-m-d');
                 $activityHistory['notes']=$request->notes;
                 $activityHistory['status']="unsent";
                 if(isset($request->trust_account) && $request->deposit_into=="Trust Account"){
                    $activityHistory['pay_type']="trust";
                 }else{
                    $activityHistory['pay_type']="client";
                 }
                 $activityHistory['firm_id']=Auth::user()->firm_name;
                 $activityHistory['section']="invoice";
                 $activityHistory['created_by']=Auth::User()->id;
                 $activityHistory['created_at']=date('Y-m-d H:i:s');
                 $this->saveAccountActivity($activityHistory);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors'=>[$e->getMessage()]]); //$e->getMessage()
                 exit;   
            }
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
        
    }

    /**
     * Get invoice payment refund popup
     */
    public function refundPopup(Request $request)
    {
        $findEntry=InvoiceHistory::find($request->transaction_id);
        if($findEntry) {
            $findInvoice=Invoices::find($findEntry['invoice_id']);
            $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($findInvoice['user_id']);
            $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$findInvoice['user_id'])->first();
            return view('billing.invoices.refundEntry',compact('userData','UsersAdditionalInfo','findEntry','findInvoice'));     
            exit;    
        } else {
            return "Record not found";
        }
    }

    public function saveRefundPopup(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $invoiceHistory = InvoiceHistory::find($request->transaction_id);
        $findInvoice = Invoices::find($invoiceHistory->invoice_id);

        $mt=$invoiceHistory->amount; 
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|max:'.$mt,
            'transaction_id' => [function ($attribute, $value, $fail) use($invoiceHistory) {
                if (empty($invoiceHistory) || in_array($invoiceHistory->status, ['2','3'])) {
                    $fail('This transaction cannot be refunded');
                }
            }]
        ],[
            'amount.max' => 'Refund cannot be more than $'.number_format($mt,2),
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        } else {
            try {
                dbStart();
                $authUser = auth()->user();
                $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$invoiceHistory['deposit_into_id'])->first();

                $invoiceHistoryNew = [];
                $invoiceHistoryNew['deposit_into'] = (in_array($invoiceHistory->pay_method, ["Trust", "Trust Refund"])) ? "Trust Account" : "Operating Account";
                $invoiceHistoryNew['deposit_into_id'] = @$UsersAdditionalInfo->user_id;

                if($invoiceHistory->payment_from == "offline" && !in_array($invoiceHistory->pay_method, ["Trust", "Non-Trust Credit Account"])) {
                    //Insert invoice payment record.
                    $entryDone = InvoicePayment::create([
                        'invoice_id'=>$findInvoice['id'],
                        'payment_from'=>'offline',
                        'amount_refund'=>$request->amount,
                        'amount_paid'=>0.00,
                        'payment_method'=>"Refund",
                        'notes'=>$request->notes,
                        'refund_ref_id'=>$invoiceHistory->invoice_payment_id,
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                        'status'=>"1",
                        'entry_type'=>"0",
                        'ip_unique_id'=>Hash::make(time().rand(1,20000)),
                        'firm_id'=>Auth::User()->firm_name,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'created_by'=>Auth::user()->id 
                    ]);
                    $invoiceHistoryNew['invoice_payment_id']=$entryDone->id;
                    $invoiceHistoryNew['pay_method']="Refund";
                    $invoiceHistoryNew['payment_from']="offline";

                    if($invoiceHistory->deposit_into == "Credit") {
                        // Deposit amount from credit account after payment.
                        UsersAdditionalInfo::where("user_id",$invoiceHistory->deposit_into_id)->decrement('credit_account_balance', $request->amount);
                            
                        // Add credit history
                        DepositIntoCreditHistory::create([
                            "user_id" => $invoiceHistory->deposit_into_id,
                            "payment_method" => "refund",
                            "deposit_amount" => $request->amount ?? 0,
                            "payment_date" => date('Y-m-d'),
                            "payment_type" => "refund payment deposit",
                            "deposit_into" => "Credit",
                            "deposit_into_id" => $invoiceHistory->deposit_into_id,
                            "total_balance" => $UsersAdditionalInfo->credit_account_balance,
                            "related_to_invoice_id" => $findInvoice->id,
                            "created_by" => auth()->id(),
                            "firm_id" => auth()->user()->firm_name,
                            "related_to_invoice_payment_id" => $entryDone->id,
                        ]);

                        $invoiceHistoryNew['deposit_into'] = "Credit";
                    }

                    if($invoiceHistory->deposit_into == "Trust Account"){
                        //Insert invoice payment record.
                        $entryDone->deposit_into = "Trust Account";
                        $entryDone->deposit_into_id = @$UsersAdditionalInfo->user_id;
                        $entryDone->save();

                        $invoiceHistoryNew['invoice_payment_id']=$entryDone->id;
                        $invoiceHistoryNew['pay_method']="Trust Refund";
                        $invoiceHistoryNew['deposit_into'] = "Trust Account";
    
                        if($UsersAdditionalInfo) {
                            $UsersAdditionalInfo->fill(['trust_account_balance' => ($UsersAdditionalInfo->trust_account_balance - $request->amount)])->save();
                            $UsersAdditionalInfo->refresh();
                        }
    
                        $trustHistory = TrustHistory::where("related_to_invoice_payment_id", $invoiceHistory->invoice_payment_id)->first();
                        if($trustHistory) {
                            $trustHistory->is_refunded="yes";
                            $trustHistory->save();
                
                            $newTrustHistory = TrustHistory::create([
                                "client_id" => $UsersAdditionalInfo->user_id,
                                "refund_amount" => $request->amount,
                                "payment_date" => date('Y-m-d',strtotime($request->payment_date)),
                                "payment_method" => "Trust Refund",
                                "fund_type" => 'refund payment deposit',
                                "current_trust_balance" => @$UsersAdditionalInfo->trust_account_balance,
                                "notes" => $request->notes,
                                "refund_ref_id" => $trustHistory->id,
                                "created_by" => auth()->id(),
                                "firm_id" => auth()->user()->firm_name,
                                "related_to_invoice_id" => $trustHistory->related_to_invoice_id,
                                "related_to_invoice_payment_id" => $entryDone->id,
                                "allocated_to_case_id" => $trustHistory->allocated_to_case_id,
                                "allocated_to_lead_case_id" => @$trustHistory->allocated_to_lead_case_id,
                            ]);

                            if($trustHistory->allocated_to_case_id) {
                                CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $request->amount);
                                CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $request->amount);
                            }
                        }
                    }
                }
                else if($invoiceHistory->payment_from == "trust" && $invoiceHistory->pay_method == "Trust"){
                    //Insert invoice payment record.
                    $entryDone=  InvoicePayment::create([
                        'invoice_id'=>$findInvoice['id'],
                        'payment_from'=>'trust',
                        'amount_refund'=>$request->amount,
                        'amount_paid'=>0.00,
                        'payment_method'=>"Trust Refund",
                        'deposit_into'=>"Trust",
                        'deposit_into_id' => @$UsersAdditionalInfo->user_id,
                        'notes'=>$request->notes,
                        // 'refund_ref_id'=>$request->transaction_id, // payment history table reference id
                        'refund_ref_id'=>$invoiceHistory->invoice_payment_id,
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                        'status'=>"1",
                        'entry_type'=>"0",
                        'ip_unique_id'=>Hash::make(time().rand(1,20000)),
                        'firm_id'=>Auth::User()->firm_name,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'created_by'=>Auth::user()->id 
                    ]);
                    $invoiceHistoryNew['invoice_payment_id']=$entryDone->id;
                    $invoiceHistoryNew['pay_method']="Trust Refund";
                    $invoiceHistoryNew['payment_from']="trust";

                    if($UsersAdditionalInfo) {
                        $UsersAdditionalInfo->fill(['trust_account_balance' => ($UsersAdditionalInfo->trust_account_balance + $request->amount)])->save();
                        $UsersAdditionalInfo->refresh();
                    }

                    $trustHistory = TrustHistory::where("related_to_invoice_payment_id", $invoiceHistory->invoice_payment_id)->first();
                    if($trustHistory) {
                        $trustHistory->is_refunded="yes";
                        $trustHistory->save();
                        $trustHistory->refresh();
            
                        $newTrustHistory = TrustHistory::create([
                            "client_id" => $UsersAdditionalInfo->user_id,
                            "refund_amount" => $request->amount,
                            "payment_date" => date('Y-m-d',strtotime($request->payment_date)),
                            "payment_method" => "Trust Refund",
                            "fund_type" => 'refund payment',
                            "current_trust_balance" => @$UsersAdditionalInfo->trust_account_balance,
                            "notes" => $request->notes,
                            "refund_ref_id" => $trustHistory->id,
                            "created_by" => auth()->id(),
                            "firm_id" => auth()->user()->firm_name,
                            "related_to_invoice_id" => $trustHistory->related_to_invoice_id,
                            "related_to_invoice_payment_id" => $entryDone->id,
                            "allocated_to_case_id" => $trustHistory->allocated_to_case_id,
                            "allocated_to_lead_case_id" => @$trustHistory->allocated_to_lead_case_id,
                        ]);

                        if($trustHistory->allocated_to_case_id && $trustHistory->fund_type == 'payment') {
                            CaseMaster::where('id', $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $request->amount);
                            CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->increment('allocated_trust_balance', $request->amount);
                        }
                        if($trustHistory->allocated_to_lead_case_id && $trustHistory->fund_type == 'payment') {
                            LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->increment('allocated_trust_balance', $request->amount);
                        }
                        $this->updateNextPreviousTrustBalance($trustHistory->client_id);
                    }
                } else if($invoiceHistory->payment_from == "credit" && $invoiceHistory->pay_method == "Non-Trust Credit Account") {
                    //Insert invoice payment record.
                    $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("deposit_into","Operating Account")->orderBy("created_at","DESC")->first();
                    if($currentBalance['total']-$request->amount<=0){
                        $finalAmt=0;
                    }else{
                        $finalAmt=$currentBalance['total']-$request->amount;
                    }

                    $entryDone=  InvoicePayment::create([
                        'invoice_id'=>$findInvoice['id'],
                        'payment_from'=>'client',
                        'amount_refund'=>$request->amount,
                        'amount_paid'=>0.00,
                        'payment_method'=>"Refund",
                        'deposit_into'=>NULL,
                        'deposit_into_id' => @$UsersAdditionalInfo->user_id,
                        'notes'=>$request->notes,
                        'refund_ref_id'=>$invoiceHistory->invoice_payment_id,
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                        'notes'=>$request->notes,
                        'status'=>"1",
                        'entry_type'=>"1",
                        'total'=>$finalAmt,
                        'firm_id'=>Auth::User()->firm_name,
                        'ip_unique_id'=>Hash::make(time().rand(1,20000)),
                        'created_at'=>date('Y-m-d H:i:s'),
                        'created_by'=>Auth::user()->id 
                    ]);
                    $invoiceHistoryNew['invoice_payment_id']=$entryDone->id;
                    $invoiceHistoryNew['pay_method']="Refund";
                    $invoiceHistoryNew['payment_from']="credit";

                    $creditHistory = DepositIntoCreditHistory::where("related_to_invoice_payment_id", $invoiceHistory->invoice_payment_id)->first();
                    if($creditHistory) {
                        $UsersAdditionalInfo = UsersAdditionalInfo::where("user_id",$creditHistory->user_id)->first();
                        if($creditHistory->payment_type == "payment") {
                            $fund_type='refund payment';
                            $UsersAdditionalInfo->fill(['credit_account_balance' => ($UsersAdditionalInfo->credit_account_balance + $request->amount)])->save();
                        }
                        $UsersAdditionalInfo->refresh();
                        $creditHistory->is_refunded="yes";
                        $creditHistory->save();
            
                        $depCredHis = DepositIntoCreditHistory::create([
                            "user_id" => $creditHistory->user_id,
                            "deposit_amount" => $request->amount,
                            "payment_date" => convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                            "payment_method" => "refund",
                            "payment_type" => $fund_type,
                            "total_balance" => $UsersAdditionalInfo->credit_account_balance,
                            "notes" => $request->notes,
                            "refund_ref_id" => $creditHistory->id,
                            "created_by" => auth()->id(),
                            "firm_id" => auth()->user()->firm_name,
                            "related_to_invoice_id" => $creditHistory->related_to_invoice_id,
                            "related_to_invoice_payment_id" => $entryDone->id,
                        ]);

                        $this->updateNextPreviousCreditBalance($creditHistory->user_id);
                    }
                } else if(in_array($invoiceHistory->payment_from, ["client_online", "online"]) && $invoiceHistory->online_payment_status == "paid") {
                    $onlinePaymentDetail = InvoiceOnlinePayment::where("invoice_history_id", $request->transaction_id)->first();
                    if($onlinePaymentDetail && $onlinePaymentDetail->payment_method == 'card') {
                        $UsersAdditionalInfo = UsersAdditionalInfo::where("user_id", $onlinePaymentDetail->user_id)->first();
                        $firmOnlinePaymentSetting = getFirmOnlinePaymentSetting();
                        \Conekta\Conekta::setApiKey($firmOnlinePaymentSetting->private_key);
                        $order = \Conekta\Order::find($onlinePaymentDetail->conekta_order_id);
                        $order->refund([
                            'reason' => 'requested_by_client',
                            'amount' => (int) $request->amount,
                        ]);
                        
                        if(in_array($order->payment_status, ["refunded", "partially_refunded"])) {
                            $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                                'invoice_id' => $onlinePaymentDetail->invoice_id,
                                'user_id' => $onlinePaymentDetail->user_id,
                                'payment_method' => 'card',
                                'amount' => $request->amount,
                                'conekta_order_id' => $order->id,
                                // 'conekta_charge_id' => @$order->charges[0]->id ?? Null,
                                'conekta_customer_id' => $onlinePaymentDetail->conekta_customer_id,
                                'conekta_payment_status' => $order->payment_status,
                                'status' => 'refund entry',
                                'refund_reference_id' => $onlinePaymentDetail->id,
                                'created_by' => $authUser->id,
                                'firm_id' => $authUser->firm_name,
                                'conekta_order_object' => $order,
                            ]);
                            
                            // Update payment detail status
                            $onlinePaymentDetail->fill(["status" => ($mt == $request->amount) ? 'full refund' : 'partial refund', ])->save();
                            //Insert invoice payment record.
                            $entryDone = InvoicePayment::create([
                                'invoice_id' => $findInvoice->id,
                                'payment_from'=>'online',
                                'amount_refund'=>$request->amount,
                                'amount_paid'=>0.00,
                                'payment_method'=>"Refund",
                                'notes'=>$request->notes,
                                'refund_ref_id'=>$invoiceHistory->invoice_payment_id,
                                'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), $authUser->user_timezone),
                                'status'=>"1",
                                'entry_type'=>"0",
                                'ip_unique_id'=>Hash::make(time().rand(1,20000)),
                                'firm_id' => $authUser->firm_name,
                                'created_at'=>date('Y-m-d H:i:s'),
                                'created_by'=>$authUser->id 
                            ]);

                            $invoiceHistoryNew['invoice_payment_id'] = $entryDone->id;
                            $invoiceHistoryNew['pay_method'] = "Refund";
                            $invoiceHistoryNew['payment_from'] = "online";
                            $invoiceHistoryNew['online_payment_status'] = $order->payment_status;
                        }
                    } else {
                        //Insert invoice payment record.
                        $entryDone = InvoicePayment::create([
                            'invoice_id'=>$findInvoice['id'],
                            'payment_from'=>'online',
                            'amount_refund'=>$request->amount,
                            'amount_paid'=>0.00,
                            'payment_method'=> ($onlinePaymentDetail->payment_method == 'cash') ? "Oxxo Cash Refund" : "SPEI Refund",
                            'notes'=>$request->notes,
                            'refund_ref_id'=>$invoiceHistory->invoice_payment_id,
                            'payment_date'=>convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                            'status'=>"1",
                            'entry_type'=>"0",
                            'firm_id'=>$authUser->firm_name,
                            'created_by'=>$authUser->id 
                        ]);
                        $invoiceHistoryNew['invoice_payment_id']=$entryDone->id;
                        $invoiceHistoryNew['pay_method'] = ($onlinePaymentDetail->payment_method == 'cash') ? "Oxxo Cash Refund" : "SPEI Refund";
                        $invoiceHistoryNew['payment_from']="online";
                    }
                } else {

                }

                $invoiceHistory->status = ($mt==$request['amount']) ? '2' : '3';
                $invoiceHistory->save();

                $invoiceHistoryNew['invoice_id']=$findInvoice['id'];
                $invoiceHistoryNew['acrtivity_title']='Payment Refund';
                $invoiceHistoryNew['amount']=$request['amount'];
                $invoiceHistoryNew['responsible_user']=$authUser->id;
                $invoiceHistoryNew['notes']=$request->notes;
                $invoiceHistoryNew['status']="4";
                $invoiceHistoryNew['refund_ref_id']=$request->transaction_id;
                $invoiceHistoryNew['created_by']=$authUser->id;
                $invoiceHistoryNew['created_at']=date('Y-m-d H:i:s');
                $newHistoryId = $this->invoiceHistory($invoiceHistoryNew);

                // Update online conekta payment record
                if($invoiceHistory->payment_from == "online" && $invoiceHistory->online_payment_status == "paid" && $invoiceHistory->pay_method == "Card") {
                    $invoiceOnlinePayment->fill(['invoice_history_id' => $newHistoryId])->save();
                }

                $request->request->add(['invoice_history_id' => $newHistoryId]);
                $request->request->add(['trust_history_id' => $newTrustHistory->id ?? NULL]);
                $request->request->add(["trust_account" => @$UsersAdditionalInfo->user_id]);
                $request->request->add(["contact_id" => @$UsersAdditionalInfo->user_id]);

                if($invoiceHistory->payment_from == "offline" && !in_array($invoiceHistory->pay_method, ["Trust", "Non-Trust Credit Account"])) {
                    if($invoiceHistory->deposit_into == "Credit" || $invoiceHistory->deposit_into == "Trust Account") {
                        // For account activity
                        $request->request->add(["payment_type" => "refund payment deposit"]);
                        $this->updateTrustAccountActivity($request, $amtAction = "sub", $findInvoice, $isDebit = "yes");
                    } else {
                        // For account activity > payment history
                        $request->request->add(["payment_type" => "refund payment"]);
                        $this->updateClientPaymentActivity($request, $findInvoice, $isDebit = "yes", $amtAction = "sub");
                    }
                } else if($invoiceHistory->payment_from == "trust" && $invoiceHistory->pay_method == "Trust") {
                    // For account activity
                    $request->request->add(["payment_type" => "refund payment"]);
                    $this->updateTrustAccountActivity($request, null, $findInvoice);

                    // For account activity > payment history
                    $this->updateClientPaymentActivity($request, $findInvoice, $isDebit = "yes", $amtAction = "sub");
                } else if(in_array($invoiceHistory->payment_from, ["client_online", "online"])) {
                    // For account activity
                    $request->request->add(["payment_type" => "refund payment"]);
                    $this->updateClientPaymentActivity($request, $findInvoice, $isDebit = "yes", $amtAction = "sub");
                }

                //Add Invoice history for activity
                $data=[];
                $data['case_id']=$findInvoice['case_id'];
                $data['user_id']=$findInvoice['user_id'];
                $data['activity']='refunded a payment of $'.number_format($request['amount'],2);
                $data['activity_for']=$findInvoice['id'];
                $data['type']='invoices';
                $data['action']='refund';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
                
                //Case Activity
                if($findInvoice['case_id'] > 0){
                    $caseActivityData=[];
                    $caseActivityData['activity_title']='refunded a payment of $'.number_format($request['amount'],2).' for invoice';
                    $caseActivityData['case_id']=$findInvoice['case_id'];
                    $caseActivityData['activity_type']='refund_payment';
                    $caseActivityData['extra_notes']=$findInvoice['id'];
                    $caseActivityData['staff_id']=$findInvoice['user_id'];
                    $this->caseActivity($caseActivityData);
                }

                // Update invoice status and paid/due amount
                $this->updateInvoiceAmount($findInvoice['id']);

                // Update installment payment status and amount
                $this->updateInvoiceInstallment($request->amount, $findInvoice['id']);
                dbCommit();
                session(['popup_success' => 'Refund successful']);
                return response()->json(['errors'=>'']);
            } catch(Exception $e) {
                dbEnd();
                return response()->json(['errors'=> $e->getMessage()]);
            }
            exit;   
        }
    }

    /**
     * To update invoice installment status and amount
     */
    public function updateInvoiceInstallment($requestAmt, $invoiceId)
    {
        // Update installment payment status and amount
        $installments = InvoiceInstallment::where('invoice_id', $invoiceId)->where("adjustment", '>', 0)->orderBy('due_date', 'desc')->get();
        if($installments) {
            foreach($installments as $key => $item) {
                if($requestAmt >= $item->adjustment) {
                    $requestAmt = $requestAmt - $item->adjustment;
                    $item->fill(['status' => 'unpaid', 'adjustment' => 0])->save();
                } else if($requestAmt > 0 && $requestAmt < $item->adjustment) {
                    $item->fill(['status' => 'unpaid', 'adjustment' => $item->adjustment - $requestAmt])->save();
                    $requestAmt = 0;
                } else {
                }
            }
        }
    }

    public function deletePaymentEntry(Request $request)
    {
        // return $request->all();
        $PaymentMaster = InvoiceHistory::find($request->payment_id);
        $validator = \Validator::make($request->all(), [
            'payment_id' => 'required|numeric',
            'payment_id' => [function ($attribute, $value, $fail) use($PaymentMaster) {
                if (empty($PaymentMaster) || in_array($PaymentMaster->status, ['2','3'])) {
                    $fail('This transaction cannot be deleted');
                }
            }]
            
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            // return 'else';
            try {
                dbStart();
                $invoicePayment = InvoicePayment::where("id", $PaymentMaster->invoice_payment_id)->first();
                if($PaymentMaster->payment_from == "trust" && in_array($PaymentMaster->pay_method, ["Trust", "Trust Refund"])){
                    // Update trust history
                    $trustHistory = TrustHistory::where("related_to_invoice_payment_id", $invoicePayment->id)->first();
                    if($trustHistory) {
                        if($trustHistory->fund_type == "refund payment") {
                            $updateRedord= TrustHistory::find($trustHistory->refund_ref_id);
                            $updateRedord->is_refunded="no";
                            $updateRedord->save();
                            UsersAdditionalInfo::where('user_id',$trustHistory->client_id)->decrement('trust_account_balance', $trustHistory->refund_amount);
                            if($trustHistory->allocated_to_case_id) {
                                CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->refund_amount);
                                CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->refund_amount);
                            }
                            if($trustHistory->allocated_to_lead_case_id) {
                                LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $trustHistory->refund_amount);
                            }
                        } else {
                            UsersAdditionalInfo::where('user_id',$trustHistory->client_id)->increment('trust_account_balance', $trustHistory->amount_paid);
                            if($trustHistory->allocated_to_case_id) {
                                CaseMaster::where('id', $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->amount_paid);
                                CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->amount_paid);
                            }
                            if($trustHistory->allocated_to_lead_case_id) {
                                LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->increment('allocated_trust_balance', $trustHistory->refund_amount);
                            }
                        }
                        $clientId = $trustHistory->client_id;
                        TrustHistory::where("related_to_invoice_payment_id", $invoicePayment->id)->delete();
                        $this->updateNextPreviousTrustBalance($clientId);
                    }
                } else if($PaymentMaster->payment_from == "credit" && in_array($PaymentMaster->pay_method, ["Non-Trust Credit Account", "Refund"])){
                    // Update credit history
                    $creditHistory = DepositIntoCreditHistory::where("related_to_invoice_payment_id", $invoicePayment->id)->first();
                    if($creditHistory) {
                        if($creditHistory->payment_type == "refund payment") {
                            $updateRedord= DepositIntoCreditHistory::find($creditHistory->refund_ref_id);
                            $updateRedord->is_refunded="no";
                            $updateRedord->save();
                            UsersAdditionalInfo::where('user_id',$creditHistory->user_id)->decrement('credit_account_balance', $creditHistory->deposit_amount);
                        } else {
                            UsersAdditionalInfo::where('user_id',$creditHistory->user_id)->increment('credit_account_balance', $creditHistory->deposit_amount);
                        }
                        $userId = $creditHistory->user_id;
                        $creditHistory->delete();
                        $this->updateNextPreviousCreditBalance($userId);
                    }            
                } else if($PaymentMaster->payment_from == "offline") {
                    if($PaymentMaster->deposit_into == "Credit") {
                        $creditHistory = DepositIntoCreditHistory::where("related_to_invoice_payment_id", $invoicePayment->id)->first();
                        if($creditHistory) {
                            if($creditHistory->payment_type == "refund payment") {
                                $updateRedord= DepositIntoCreditHistory::find($creditHistory->refund_ref_id);
                                $updateRedord->is_refunded="no";
                                $updateRedord->save();
                                UsersAdditionalInfo::where('user_id',$creditHistory->user_id)->decrement('credit_account_balance', $creditHistory->deposit_amount);
                            } else if($creditHistory->payment_type == "payment deposit") {
                                UsersAdditionalInfo::where('user_id',$creditHistory->user_id)->decrement('credit_account_balance', $creditHistory->deposit_amount);
                            } else {
                                UsersAdditionalInfo::where('user_id',$creditHistory->user_id)->increment('credit_account_balance', $creditHistory->deposit_amount);
                            }
                            $userId = $creditHistory->user_id;
                            $creditHistory->delete();
                            $this->updateNextPreviousCreditBalance($userId);
                        }            
                    } else if($PaymentMaster->deposit_into == "Trust Account") {
                        $trustHistory = TrustHistory::where("related_to_invoice_payment_id", $invoicePayment->id)->first();
                        if($trustHistory) {
                            if($trustHistory->fund_type == "refund payment") {
                                $updateRedord= TrustHistory::find($trustHistory->refund_ref_id);
                                $updateRedord->is_refunded="no";
                                $updateRedord->save();
                                UsersAdditionalInfo::where('user_id',$trustHistory->client_id)->decrement('trust_account_balance', $trustHistory->refund_amount);
                                if($trustHistory->allocated_to_case_id) {
                                    CaseClientSelection::where("case_id", $trustHistory->allocated_to_case_id)->where("selected_user", $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->refund_amount);
                                    CaseMaster::where("id", $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->refund_amount);
                                }
                                if($trustHistory->allocated_to_lead_case_id) {
                                    LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $trustHistory->refund_amount);
                                }
                            } else if($trustHistory->fund_type == "payment deposit") {
                                UsersAdditionalInfo::where('user_id',$trustHistory->client_id)->decrement('trust_account_balance', $trustHistory->amount_paid);
                                if($trustHistory->allocated_to_case_id) {
                                    CaseClientSelection::where("case_id", $trustHistory->allocated_to_case_id)->where("selected_user", $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->amount_paid);
                                    CaseMaster::where("id", $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->amount_paid);
                                }
                                if($trustHistory->allocated_to_lead_case_id) {
                                    LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $trustHistory->amount_paid);
                                }
                            } else if($trustHistory->fund_type == "refund payment deposit") {
                                $updateRedord= TrustHistory::find($trustHistory->refund_ref_id);
                                $updateRedord->is_refunded="no";
                                $updateRedord->save();
                                UsersAdditionalInfo::where('user_id',$trustHistory->client_id)->increment('trust_account_balance', $trustHistory->refund_amount);
                                if($trustHistory->allocated_to_case_id) {
                                    CaseClientSelection::where("case_id", $trustHistory->allocated_to_case_id)->where("selected_user", $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->refund_amount);
                                    CaseMaster::where("id", $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->refund_amount);
                                }
                                if($trustHistory->allocated_to_lead_case_id) {
                                    LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->increment('allocated_trust_balance', $trustHistory->refund_amount);
                                }
                            } else {
                                UsersAdditionalInfo::where('user_id',$trustHistory->client_id)->increment('trust_account_balance', $trustHistory->amount_paid);
                                if($trustHistory->allocated_to_case_id) {
                                    CaseClientSelection::where("case_id", $trustHistory->allocated_to_case_id)->where("selected_user", $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->amount_paid);
                                    CaseMaster::where("id", $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->amount_paid);
                                }
                                if($trustHistory->allocated_to_lead_case_id) {
                                    LeadAdditionalInfo::where('user_id', $trustHistory->allocated_to_lead_case_id)->increment('allocated_trust_balance', $trustHistory->amount_paid);
                                }
                            }
                            $clientId = $trustHistory->client_id;
                            $trustHistory->delete();
                            $this->updateNextPreviousTrustBalance($clientId);
                        }
                    }
                }
                // Update refund reference record status
                $refundRefHistory = InvoiceHistory::find($PaymentMaster->refund_ref_id);
                if($refundRefHistory) {
                    $refundRefHistory->status="1";
                    $refundRefHistory->save();
                }            

                if($invoicePayment && !in_array($invoicePayment->payment_method, ["Refund", "Trust Refund", "Oxxo Cash Refund", "SPEI Refund"])) {
                    $this->updateInvoiceInstallment($invoicePayment->amount_paid, $PaymentMaster->invoice_id);
                } else {
                    // For update installment amount and status
                    $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id", $PaymentMaster->invoice_id)->first();
                    if(!empty($getInstallMentIfOn)){
                        $this->installmentManagement($invoicePayment->amount_refund, $PaymentMaster->invoice_id);
                    }
                }
                $invoicePayment->delete();
                $this->updateInvoiceAmount($PaymentMaster->invoice_id);

                // For account activity
                $this->deleteTrustAccountActivity($PaymentMaster->id);

                InvoiceHistory::where('id',$request->payment_id)->delete();

                //Add Invoice history
                $Invoices = Invoices::find($PaymentMaster->invoice_id);
                $data=[];
                $data['case_id']=$Invoices['case_id'];
                $data['user_id']=$Invoices['user_id'];
                $data['activity']='deleted a payment from invoice';
                $data['activity_for']=$Invoices['id'];
                $data['type']='invoices';
                $data['action']='pay_delete';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
                
                dbCommit();
                session(['popup_success' => 'Entry was deleted']);
                return response()->json(['errors'=>'']);
                exit;   
            } catch (Exception $e) {
                dbEnd();
                return response()->json(['errors'=> $e->getMessage()]);
            }
        }
    }

    public function InvoiceHistoryInlineView(Request $request)
    {
       
        $idEncoded=$request->id;
        $History=InvoiceHistory::find($idEncoded);
        
        $invoice_idEncoded=$History['invoice_id'];
        $Invoice=Invoices::where("id",$History['invoice_id'])->first();
        $invoice_id=$Invoice['id'];
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
       
        $caseMaster=CaseMaster::find($Invoice['case_id']);
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        $firmData=Firm::find($userData['firm_name']);

        $InvoiceHistory=InvoiceHistory::find($request->id);
        $filename="Invoice_".$invoice_id.'.pdf';
        $PDFData=view('billing.invoices.viewInvoiceHistoryPdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','InvoiceHistory'));
        // $pdf = new Pdf;
        // $pdf->setOptions(['javascript-delay' => 5000]);
        // if($_SERVER['SERVER_NAME']=='localhost'){
        //     $pdf->binary = WKHTMLTOPDF_PATH;
        // }
        // $pdf->addPage($PDFData);
        // $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->saveAs(public_path("download/pdf/".$filename));
        // $path = public_path("download/pdf/".$filename);
        $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        // return response()->download($path);
        // exit;
        return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
        exit;
    }

    public function setBulkStatusActionForm(Request $request)
    {
        $request->merge([
            'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->invoice_id));
            foreach($data as $k=>$v){
                $Invoice = Invoices::whereId($v)->with('invoiceFirstInstallment')->first();
                if($request->status == 'Draft'){
                    if(!in_array($Invoice->status,["Forwarded"])){
                        $Invoice->is_force_status=1;
                        $Invoice->status=$request->status;
                        $Invoice->save();    
                        session(['popup_success' => 'Selected invoices status has been updated.']);
                    }
                }else{
                    if(!in_array($Invoice->status,["Paid","Partial","Forwarded"])){
                        $Invoice->status=$request->status;
                        $Invoice->save();    
                        session(['popup_success' => 'Selected invoices status has been updated.']);
                    }
                }
            }
            return response()->json(['errors'=>'']);
            exit;  
        }  
    }

    public function setBulkSharesActionForm(Request $request)
    {
        $request->merge([
            'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->invoice_id));
            $errors = '';
            $notSharedlist = '';
            foreach($data as $k1=>$v1){
                $Invoice=Invoices::find($v1);
                if($Invoice->case_id == null && $Invoice->is_lead_invoice == 'yes'){
                    $leadData=User::find($Invoice->user_id);
                    if($request->status == "AC"){  //BC=Billing Contact only
                        $firmData=Firm::find(Auth::User()->firm_name);
                        $getTemplateData = EmailTemplate::find(8);
                        $token=route("bills/invoice/{id}", $Invoice->decode_id);                        
                                
                        $mail_body = $getTemplateData->content;
                        $mail_body = str_replace('{message}', $leadData->email_message, $mail_body);
                        $mail_body = str_replace('{token}', $token,$mail_body);
                        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                        $user = [
                            "from" => FROM_EMAIL,
                            "from_title" => FROM_EMAIL_TITLE,
                            "subject" => $firmData->firm_name." has sent you an invoice",
                            "to" => $leadData->email,
                            "full_name" => "",
                            "mail_body" => $mail_body
                        ];
                        $sendEmail = $this->sendMail($user);
                    }else{
                        $notSharedlist .='<li>'. sprintf('%06d', @$Invoice['id']).' ('.@$leadData['full_name'].')</li>';
                    }
                }else if($Invoice->case_id == 0){
                    if($request->status == "BC"){  //BC=Billing Contact only
                        $clientData = User::whereId($v)->with(["userAdditionalInfo" => function($query) {
                            $query->select("user_id", "client_portal_enable");
                        }])->first();
                        if(!empty($clientData)){
                            $firmData=Firm::find(Auth::User()->firm_name);
                            $getTemplateData = EmailTemplate::find(12);
                            // $token=url('activate_account/bills=&web_token='.$Invoice['invoice_unique_token']);
                            $token = route("client/bills/detail", $Invoice->decode_id);
                            $mail_body = $getTemplateData->content;
                            $mail_body = str_replace('{message}', $request->message, $mail_body);
                            $mail_body = str_replace('{token}', $token, $mail_body);
                            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                            $user = [
                                "from" => FROM_EMAIL,
                                "from_title" => FROM_EMAIL_TITLE,
                                "subject" => $firmData->firm_name." has sent you an invoice",
                                "to" => $clientData->email,
                                "full_name" => "",
                                "mail_body" => $mail_body
                            ];
                            $sendEmail = $this->sendMail($user);
                        }else{
                            $notSharedlist .='<li>'. sprintf('%06d', @$Invoice['id']).' ('.@$leadData['full_name'].')</li>';
                        }
                    }else{
                        $leadData=User::find($Invoice->user_id);
                        $notSharedlist .='<li>'. sprintf('%06d', @$Invoice['id']).' ('.@$leadData['full_name'].')</li>';
                    }
                }else{
                    if($request->status=="BC"){  //BC=Billing Contact only
                        $CaseClientSelection=CaseClientSelection::select("selected_user")->where("is_billing_contact","yes")->where("case_id",$Invoice['case_id'])->get()->pluck("selected_user");
                    }else{
                        $CaseClientSelection=CaseClientSelection::select("selected_user")->where("case_id",$Invoice['case_id'])->get()->pluck("selected_user");
                    }
                    if(!$CaseClientSelection->isEmpty()){
                        foreach($CaseClientSelection as $k=>$v){
                            $clientData = User::whereId($v)->with(["userAdditionalInfo" => function($query) {
                                $query->select("user_id", "client_portal_enable");
                            }])->first();
                            if(!empty($clientData)){
                                if($clientData->user_level == 2 && $clientData->userAdditionalInfo->client_portal_enable == 1){
                                    $firmData=Firm::find(Auth::User()->firm_name);
                                    $getTemplateData = EmailTemplate::find(12);
                                    // $token=url('activate_account/bills=&web_token='.$Invoice['invoice_unique_token']);
                                    $token = route("client/bills/detail", $Invoice->decode_id);
                                    $mail_body = $getTemplateData->content;
                                    $mail_body = str_replace('{message}', $request->message, $mail_body);
                                    $mail_body = str_replace('{token}', $token, $mail_body);
                                    $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                                    $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                                    $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                                    $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                                    $user = [
                                        "from" => FROM_EMAIL,
                                        "from_title" => FROM_EMAIL_TITLE,
                                        "subject" => $firmData->firm_name." has sent you an invoice",
                                        "to" => $clientData->email,
                                        "full_name" => "",
                                        "mail_body" => $mail_body
                                    ];
                                    $sendEmail = $this->sendMail($user);
                                }else{
                                    $notSharedlist .='<li>'. sprintf('%06d', @$Invoice['id']).' ('.@$clientData['full_name'].')</li>';   
                                }
                            }else{
                                $notSharedlist .='<li>'. sprintf('%06d', @$Invoice['id']).' ('.@$clientData['full_name'].')</li>';
                            }
                        }
                    }
                }
            }
            return response()->json(['errors'=>$errors, 'list'=> $notSharedlist]);
            exit;  
        } 
    }

    public function deleteBulkInvoice(Request $request)
    {
        $request->merge([
            'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->invoice_id));
            $nonDeletedInvoice = [];
            foreach($data as $k1=>$v1){
                $Invoices = Invoices::whereId($v1)->first();
                if(!empty($Invoices)){
                    if($Invoices->status != "Forwarded") {
                        //Add Invoice history
                        $data=[];
                        $data['case_id']=$Invoices['case_id'];
                        $data['user_id']=$Invoices['user_id'];
                        $data['activity']='deleted an invoice';
                        $data['activity_for']=$Invoices['id'];
                        $data['type']='invoices';
                        $data['action']='delete';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);
                        
                        // Invoices::where("id", $v1)->delete();
                        //Remove flat fee entry for the invoice and reactivated time entry
                        $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::where("invoice_id",$v1)->get();
                        foreach($FlatFeeEntryForInvoice as $k=>$v){
                            DB::table('flat_fee_entry')->where("id",$v->flat_fee_entry_id)->update([
                            'status'=>'unpaid',
                            'invoice_link' => null,
                            'token_id'=>NULL,
                            ]);
                            FlatFeeEntryForInvoice::where("id", $v->id)->delete();
                        }
                        //Removed time entry id
                        $TimeEntryForInvoice=TimeEntryForInvoice::where("invoice_id",$v1)->get();
                        foreach($TimeEntryForInvoice as $k=>$v){
                            DB::table('task_time_entry')->where("id",$v->time_entry_id)->update([
                                'status'=>'unpaid',
                                'invoice_link'=>NULL,
                                'token_id'=>NULL,
                            ]);
                            TimeEntryForInvoice::where("id",$v->time_entry_id)->delete();
                        }
                        //Removed expense entry
                        $ExpenseForInvoice=ExpenseForInvoice::where("invoice_id",$v1)->get();
                        foreach($ExpenseForInvoice as $k=>$v){
                            DB::table('expense_entry')->where("id",$v->expense_entry_id)->update([
                                'status'=>'unpaid',
                                'invoice_link'=>NULL,
                                'token_id'=>NULL,
                            ]);
                            ExpenseForInvoice::where("id",$v->expense_entry_id)->delete();
                        }
                        //Removed shared invoice 
                        $SharedInvoice=SharedInvoice::where("invoice_id",$v1)->delete();

                        //Removed invoice adjustment entry
                        $InvoiceAdjustment=InvoiceAdjustment::where("invoice_id",$v1)->delete();

                        // Update trust balance
                        $invoicePaymentFromTrust = InvoicePayment::where("invoice_id", $v1)->where("payment_from", "trust")->get();
                        $accessUser = UsersAdditionalInfo::where("user_id", $Invoices->user_id)->first();
                        if($accessUser && $invoicePaymentFromTrust) {
                            $paidAmount = $invoicePaymentFromTrust->sum('amount_paid');
                            $refundAmount = $invoicePaymentFromTrust->sum('amount_refund');
                            $accessUser->fill(['trust_account_balance' => $accessUser->trust_account_balance + ($paidAmount - $refundAmount)])->save();
                        }

                        InvoicePaymentPlan::where("invoice_id",$v1)->delete();
                        InvoiceInstallment::where("invoice_id",$v1)->delete();
                        InvoicePayment::where("invoice_id",$v1)->delete();

                        // Delete Invoice
                        $Invoices->delete();
                    } else {
                        array_push($nonDeletedInvoice, $Invoices->id);
                    }
                }
            }
            // return $nonDeletedInvoice;
            if(count($nonDeletedInvoice)) {
                $nonDeleted = Invoices::whereIn("invoices.id",$nonDeletedInvoice)->with("portalAccessUserAdditionalInfo")->get();
                if($nonDeleted) {
                    foreach($nonDeleted as $key => $item) {
                        if(count($item->invoiceForwardedToInvoice) == 0) {
                            $this->updateInvoiceAmount($item->id);
                        }
                    }
                }
                $view = view('billing.invoices.partials.load_invoice_not_deleted',compact('nonDeleted'))->render();
            }
            // return $view;
            return response()->json(['errors'=>'', 'view' => $view ?? ""]);
            exit;  
        } 
    }
    public function adjustmentBulkInvoiceForm(Request $request)
    {
        $request->merge([
            'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $data = json_decode(stripslashes($request->invoice_id));

            $discount_type=$request->discount_type;
            $discount_applied_to=$request->discount_applied_to;
            $amount	=$request->amount;
            $amountType=$request->amountType;
            $notes=$request->notes;
            $notSavedInvoice='';
            foreach($data as $k1=>$v1){
                $Applied=FALSE;
                $Invoices=Invoices::find($v1);
                $CaseMaster=CaseMaster::find($Invoices['case_id']);
                $InvoiceAdjustment = new InvoiceAdjustment;
                
                $FlatFeeEntryForInvoiceTotal = $TimeEntryForInvoiceTotal = $GrandTotalByInvoiceExp = $InvoiceAdjustmentTotal = $forwardedInvoicesGrandTotal = 0;

                $InvoiceAdjustmentData=InvoiceAdjustment::where("invoice_id",$v1)->get(); 
                foreach($InvoiceAdjustmentData as $k => $v){
                    $InvoiceAdjustmentTotal = ($v->item == 'discount') ? ($InvoiceAdjustmentTotal - str_replace(",","",$v->amount)) : ($InvoiceAdjustmentTotal + str_replace(",","",$v->amount));
                }

                $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
                ->where("invoice_id",$v1)
                ->where("flat_fee_entry.time_entry_billable","yes")
                ->sum('cost');               
                
                $FlatFeeEntryForInvoiceTotal= str_replace(',', '',number_format(str_replace(',', '',$FlatFeeEntryForInvoice), 2));
                $TimeEntryForInvoice=TimeEntryForInvoice::join("task_time_entry","task_time_entry.id","=","time_entry_for_invoice.time_entry_id")->where("task_time_entry.time_entry_billable","yes")->where("invoice_id",$v1)->get();
                if(count($TimeEntryForInvoice) > 0){
                    $TotalAmt=0;
                    foreach($TimeEntryForInvoice as $kk=>$vv){
                        if($vv->rate_type=="hr"){
                            $TotalAmt=(str_replace(",","",$vv->entry_rate)*str_replace(",","",$vv->duration));
                        }else{
                            $TotalAmt=str_replace(",","",$vv->entry_rate);
                        }
                        $TimeEntryForInvoiceTotal+=str_replace(",","",$TotalAmt);
                    }
                    $TimeEntryForInvoiceTotal = str_replace(",", '',number_format($TimeEntryForInvoiceTotal,2));
                }

                $ExpenseForInvoice=ExpenseForInvoice::join("expense_entry","expense_entry.id","=","expense_for_invoice.expense_entry_id")->where("expense_entry.time_entry_billable","yes")->where("invoice_id",$v1)->get();
                if(count($ExpenseForInvoice) > 0){
                    $TotalAmtExp=0;
                    foreach($ExpenseForInvoice as $kk1=>$vv1){
                        $TotalAmtExp=(str_replace(",","",$vv1->cost)*str_replace(",","",$vv1->duration));
                        $GrandTotalByInvoiceExp+=str_replace(",","",$TotalAmtExp);
                    }
                    $GrandTotalByInvoiceExp = str_replace(",", '', number_format($GrandTotalByInvoiceExp,2));
                }
                
                $subTotal= ($FlatFeeEntryForInvoiceTotal + $TimeEntryForInvoiceTotal+ $GrandTotalByInvoiceExp); 
                //forwarded invoices applied or not
                $forwardedInvoices = Invoices::whereId($v1)->with("forwardedInvoices")->first();
                if(!empty($forwardedInvoices)){
                    foreach($forwardedInvoices->forwardedInvoices as $inv){
                        $forwardedInvoicesGrandTotal+= (str_replace(",","",$inv['total_amount']) - str_replace(",","",$inv['paid_amount']));
                    }
                    $forwardedInvoicesGrandTotal = str_replace(",", '', number_format($forwardedInvoicesGrandTotal,2));
                }
                
                $finalAmount=0;
                if($Invoices->status != 'Forwarded' && $Invoices->is_lead_invoice == 'no'){         
                $InvoiceAdjustment->case_id =$Invoices['case_id'];
                $InvoiceAdjustment->token = base64_encode($v1);
                $InvoiceAdjustment->invoice_id =$Invoices['id'];
                $InvoiceAdjustment->item=$discount_type;
                $InvoiceAdjustment->applied_to=$discount_applied_to;
                $InvoiceAdjustment->ad_type=$amountType;
                if($amountType=='percentage'){
                    $InvoiceAdjustment->percentages =(float)$amount;
                }else{
                    $InvoiceAdjustment->percentages=NULL;
                }

                if($discount_applied_to=="flat_fees"){                    
                    if($FlatFeeEntryForInvoiceTotal > 0){                        
                        $InvoiceAdjustment->basis =str_replace(",","",$FlatFeeEntryForInvoiceTotal);
                        if($FlatFeeEntryForInvoiceTotal >= $amount){
                            if($amountType=="percentage"){
                                $finalAmount=($amount/100)*$FlatFeeEntryForInvoiceTotal;
                            }else{
                                $finalAmount=$amount;
                            }
                            $Applied=TRUE;        
                        }                
                    }else{
                        $Applied=FALSE;   
                    }        
                }
                if($discount_applied_to=="time_entries"){
                    if($TimeEntryForInvoiceTotal > 0){
                        $InvoiceAdjustment->basis =str_replace(",","",$TimeEntryForInvoiceTotal);
                        if($TimeEntryForInvoiceTotal >= $amount){
                            if($amountType=="percentage"){
                                $finalAmount=($amount/100)*$TimeEntryForInvoiceTotal;
                            }else{
                                $finalAmount=$amount;
                            }
                            $Applied=TRUE;
                        }
                    }else{
                        $Applied=FALSE;   
                    }                    
                }

                if($discount_applied_to=="expenses"){
                    if($GrandTotalByInvoiceExp > 0){
                        $InvoiceAdjustment->basis =str_replace(",","",$GrandTotalByInvoiceExp);
                        if($GrandTotalByInvoiceExp >= $amount){
                            if($amountType=="percentage"){
                                $finalAmount=($amount/100)*$GrandTotalByInvoiceExp;
                            }else{
                                $finalAmount=$amount;
                            }
                            $Applied=TRUE;
                        }
                    }else{
                        $Applied=FALSE;   
                    } 
                }
                if($discount_applied_to=="sub_total"){  
                    //forwarded invoices applied or not
                    $forwardedInvoicesTotal = 0;
                    foreach($forwardedInvoices->forwardedInvoices as $inv){
                        $forwardedInvoicesTotal+= str_replace(",","",$inv['total_amount']);
                    }   
                    $balanceForwardInvoice = InvoiceAdjustment::where("invoice_id",$v1)->where('applied_to','sub_total')->get();
                                       
                    if($subTotal>0){
                        $InvoiceAdjustment->basis =str_replace(",","",$subTotal);
                        if($amountType=="percentage"){
                            $finalAmount=($amount/100)*$subTotal;
                            $Applied=TRUE;
                        }else{
                            $finalAmount=$amount;
                            $Applied= ($amount > $subTotal) ? FALSE : TRUE;
                        }
                    }else if(count($balanceForwardInvoice) == 0 || $forwardedInvoicesTotal <= $subTotal){
                        $Applied=TRUE;
                    }else{ 
                        $Applied=FALSE;
                    }                    
                }
                
                if($discount_applied_to=="balance_forward_total"){
                    //forwarded invoices applied or not
                    $forwardedInvoices = Invoices::whereId($v1)->with("forwardedInvoices")->first();
                    $forwardedInvoicesTotal = 0;
                    foreach($forwardedInvoices->forwardedInvoices as $inv){
                        $forwardedInvoicesTotal+= (str_replace(",","",$inv['total_amount']) - str_replace(",","",$inv['paid_amount']));
                    }
                    if($forwardedInvoicesTotal > 0.01) {
                        $InvoiceAdjustment->basis =str_replace(",","",$forwardedInvoicesTotal);
                        if($amountType=="percentage"){
                            $finalAmount=($amount/100)*$forwardedInvoicesTotal;
                            $Applied=TRUE;
                        }else{
                            if($forwardedInvoicesGrandTotal > 0.01){
                                $Applied= TRUE;
                                $finalAmount=$amount;
                            }else{
                                $Applied= ($amount >= $subTotal) ? FALSE : TRUE;
                            }
                        }        
                    }else{
                        $balanceForwardInvoice = InvoiceAdjustment::where("invoice_id",$v1)->where('applied_to','balance_forward_total')->get();
                        if(count($balanceForwardInvoice) > 0){
                            if($request->basic >= $amount){
                                $InvoiceAdjustment->basis =str_replace(",","",$request->basic);
                                $Applied=TRUE;
                            }
                        }else{
                            $Applied=FALSE;
                        }
                    }
                }
               
                $InvoiceAdjustment->amount =str_replace(",","",$finalAmount);
                $InvoiceAdjustment->notes =$notes;
                $InvoiceAdjustment->created_at=date('Y-m-d h:i:s'); 
                $InvoiceAdjustment->created_by=Auth::User()->id; 
                }
                
                if($Applied==TRUE){
                    
                    if($Invoices->payment_plan_enabled == 'no'){
                        $InvoiceAdjustment->save();
                        // echo $InvoiceAdjustmentTotal.'---->'.$forwardedInvoicesGrandTotal.'---->'.$subTotal.'---->'.$finalAmount;
                        if($discount_type=="discount"){
                            $subTotalSave=$InvoiceAdjustmentTotal + $forwardedInvoicesGrandTotal + ($subTotal-$finalAmount);
                            if($subTotalSave<0){
                                $subTotalSave=0;
                            }
                        }else{
                            $subTotalSave=$InvoiceAdjustmentTotal + $forwardedInvoicesGrandTotal + ($subTotal + $finalAmount);
                        }
                        // dd($subTotalSave);
                        $Invoices->total_amount=$subTotalSave;
                        // $Invoices->token=base64_decode($v1);
                        $Invoices->due_amount=$subTotalSave;
                        $Invoices->save();
                    }else{
                        $notSavedInvoice .='<li>'. sprintf('%06d', @$Invoices['id']).' ('.@$CaseMaster['case_title'].')</li>';    
                    }
                }else{
                    $notSavedInvoice .='<li>'. sprintf('%06d', @$Invoices['id']).' ('.@$CaseMaster['case_title'].')</li>';
                }
            }
            return response()->json(['errors'=>'','list'=> $notSavedInvoice]);
            exit;             
        } 
    }

    /**
     * Apply trust balance from invoice list
     */
    public function applyTrustBalanceForm(Request $request)
    {
        // return $request->all();
        $request->merge([
            'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            try {
            dbStart();
            $data = json_decode(stripslashes($request->invoice_id));
            $notSavedInvoice=$savedInvoice=[];
            foreach($data as $k1=>$v1){
                dbStart();
                $Invoices=Invoices::find($v1);
                $invoice_id=$Invoices['id'];
                $paid=$Invoices['paid_amount'];
                $invoice=$Invoices['total_amount'];
                $finalAmt=$invoice-$paid;

                $caseClientCount = 1;
                if($Invoices->is_lead_invoice == 'no') {
                    $caseClientCount = CaseClientSelection::where("case_id", $Invoices->case_id)->count();
                }
                // $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$Invoices['user_id'])->first();
                $userData = UsersAdditionalInfo::where('user_id', $Invoices['user_id'])->first();

                //Get the trust account balance and invoice due amount
                if($userData->unallocate_trust_balance > 0 && $caseClientCount == 1 && $Invoices->status != "Forwarded" && $Invoices->status != "Paid")
                {
                    if($finalAmt >= $userData->unallocate_trust_balance ){
                        $finalAmt = $userData->unallocate_trust_balance;
                    }
                    //Insert invoice payment record.
                    $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","trust")->orderBy("created_at","DESC")->first();
                                    
                    //Insert invoice payment record.
                    $entryDone= DB::table('invoice_payment')->insert([
                        'invoice_id'=>$invoice_id,
                        'payment_from'=>'trust',
                        'amount_paid'=>$finalAmt,
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                        'notes'=>NULL,
                        'status'=>"0",
                        'entry_type'=>"0",
                        'deposit_into'=>"Operating Account",
                        'payment_from_id'=>$userData['user_id'],
                        'total'=>($currentBalance['total']+$finalAmt),
                        'firm_id'=>Auth::User()->firm_name,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'created_by'=>Auth::user()->id 
                    ]);
                    $lastInvoicePaymentId= DB::getPdo()->lastInsertId();
                    $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                    $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                    $InvoicePayment->save();

                    //Deduct invoice amount when payment done
                    $this->updateInvoiceAmount($invoice_id);

                    // Deduct amount from trust account after payment.
                    UsersAdditionalInfo::where('user_id',$userData['user_id'])->decrement('trust_account_balance', $finalAmt);
                    $userData->refresh();

                    $request->request->add(['amount' => $finalAmt]);
                    $request->request->add(['trust_account' => $userData->user_id]);
                    $request->request->add(['contact_id' => $userData->user_id]);

                    $TrustInvoice=new TrustHistory;
                    $TrustInvoice->client_id=$userData->user_id;
                    $TrustInvoice->payment_method='Trust';
                    $TrustInvoice->amount_paid=$request->amount;
                    $TrustInvoice->current_trust_balance=@$userData->trust_account_balance;
                    $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone);
                    $TrustInvoice->notes=$request->notes;
                    $TrustInvoice->fund_type='payment';
                    $TrustInvoice->related_to_invoice_id = $invoice_id;
                    $TrustInvoice->created_by=Auth::user()->id; 
                    $TrustInvoice->allocated_to_case_id = NULL;
                    $TrustInvoice->related_to_invoice_payment_id = $lastInvoicePaymentId;
                    $TrustInvoice->save();

                    $invoiceHistory=[];
                    $invoiceHistory['invoice_id']=$invoice_id;
                    $invoiceHistory['acrtivity_title']='Payment Received';
                    $invoiceHistory['pay_method']='Trust';
                    $invoiceHistory['amount']=$finalAmt;
                    $invoiceHistory['responsible_user']=Auth::User()->id;
                    $invoiceHistory['deposit_into']='Operating Account';
                    $invoiceHistory['deposit_into_id']=($userData['user_id'])??NULL;
                    $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                    $invoiceHistory['notes']=NULL;
                    $invoiceHistory['status']="1";
                    $invoiceHistory['created_by']=Auth::User()->id;
                    $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                    $newHistoryId = $this->invoiceHistory($invoiceHistory);

                    $request->request->add(["invoice_history_id" => $newHistoryId]);
                    $request->request->add(["trust_history_id" => @$TrustInvoice->id]);
                    $request->request->add(["payment_type" => 'payment']);


                    //Add Invoice history
                    $InvoiceData=Invoices::find($invoice_id);
                    $data=[];
                    $data['case_id']=$InvoiceData['case_id'];
                    $data['user_id']=$InvoiceData['user_id'];
                    $data['activity']='accepted a payment of $'.number_format($finalAmt,2).' (Trust)';
                    $data['activity_for']=$InvoiceData['id'];
                    $data['type']='invoices';
                    $data['action']='pay';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);


                    //Get previous amount
                    /* $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
                    $activityHistory=[];
                    $activityHistory['user_id']=$InvoiceData['user_id'];
                    $activityHistory['related_to']=$InvoiceData['id'];
                    $activityHistory['case_id']=$InvoiceData['case_id'];
                    $activityHistory['credit_amount']=0.00;
                    $activityHistory['debit_amount']=$finalAmt;
                    $activityHistory['total_amount']=$AccountActivityData['total_amount']-$finalAmt;
                    $activityHistory['entry_date']=date('Y-m-d');
                    $activityHistory['notes']=NULL;
                    $activityHistory['status']="unsent";
                    $activityHistory['pay_type']="trust";
                    $activityHistory['firm_id']=Auth::User()->firm_name;
                    $activityHistory['section']="invoice";
                    $activityHistory['created_by']=Auth::User()->id;
                    $activityHistory['created_at']=date('Y-m-d H:i:s');
                    $this->saveAccountActivity($activityHistory); */
                    $this->updateTrustAccountActivity($request, $amtAction = 'sub', $InvoiceData, $isDebit = "yes");

                    
                    //Get previous amount
                    /* $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                    $activityHistory=[];
                    $activityHistory['user_id']=$InvoiceData['user_id'];
                    $activityHistory['related_to']=$InvoiceData['id'];
                    $activityHistory['case_id']=$InvoiceData['case_id'];
                    $activityHistory['debit_amount']=0.00;
                    $activityHistory['credit_amount']=$finalAmt;
                    $activityHistory['total_amount']=$AccountActivityData['total_amount']+$finalAmt;
                    $activityHistory['entry_date']=date('Y-m-d');
                    $activityHistory['notes']=NULL;
                    $activityHistory['status']="unsent";
                    $activityHistory['pay_type']="client";
                    $activityHistory['from_pay']="trust";
                    $activityHistory['firm_id']=Auth::User()->firm_name;
                    $activityHistory['section']="invoice";
                    $activityHistory['created_by']=Auth::User()->id;
                    $activityHistory['created_at']=date('Y-m-d H:i:s');
                    $this->saveAccountActivity($activityHistory); */
                    $this->updateClientPaymentActivity($request, $InvoiceData);

                    $savedInvoice[]=$invoice_id;
                }else{
                    $notSavedInvoice[]=$invoice_id;
                }
                dbCommit();
            }
            dbEnd();
            return response()->json(['errors'=>'','savedInvoice'=>$savedInvoice,'notSavedInvoice'=>$notSavedInvoice]);
            exit;  
            } catch (Exception $e) {
                dbEnd();
                return response()->json(['errors' => $e->getMessage()]);
            }
        } 
    }

    /**
     * Apply trust balance from invoice list response
     */
    public function trustBalanceResponse(Request $request)
    {
        $id=Auth::user()->id;
         $user = User::find($id);
         $savedInvoice=[];
        if(!empty($user)){
            $appliedInvoice= (isset($request->response['savedInvoice'])) ? $request->response['savedInvoice'] : [];
            $nonappliedInvoice= (isset($request->response['notSavedInvoice'])) ? $request->response['notSavedInvoice'] : [];

            // $SavedInvoices=Invoices::select("case_master.*","invoices.*","users_additional_info.*")->whereIn("invoices.id",$appliedInvoice);
            // $SavedInvoices=$SavedInvoices->leftJoin("case_master","case_master.id","=","invoices.case_id");
            // $SavedInvoices=$SavedInvoices->leftJoin("users_additional_info","users_additional_info.user_id","=","invoices.user_id");
            // $SavedInvoices=$SavedInvoices->get();

            $SavedInvoices = Invoices::whereIn("id",$appliedInvoice)->with("case", "portalAccessUserAdditionalInfo", "leadAdditionalInfo")->get();
            $NonSavedInvoices = Invoices::whereIn("id",$nonappliedInvoice)->with("case", "leadAdditionalInfo")->get();
           
            // $NonSavedInvoices=Invoices::select("case_master.case_title","invoices.id")->whereIn("invoices.id",$nonappliedInvoice);
            // $NonSavedInvoices=$NonSavedInvoices->leftJoin("case_master","case_master.id","=","invoices.case_id");
            // $NonSavedInvoices=$NonSavedInvoices->with("leadAdditionalInfo")->get();

            return view('billing.invoices.trustBalanceAppliedResult',compact('SavedInvoices','NonSavedInvoices'));
        }else{
            return view('pages.404');
        }
    }
    /********************************Account Activity ***************************** */
    public function account_activity()
    {
        $id=Auth::user()->id;
         $user = User::find($id);
        if(!empty($user)){
            return view('billing.account_activity.account_activitiy');
        }else{
            return view('pages.404');
        }
    }

    public function loadAccountActivityV1()
    {   
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = InvoicePayment::leftJoin("users","invoice_payment.created_by","=","users.id")
        ->select('invoice_payment.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("invoice_payment.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("entry_type","1");

        if(isset($requestData['account']) && $requestData['account']!=''){
            if($requestData['account']=="trust_account"){
                $FetchQuery = $FetchQuery->where("deposit_into","Trust Account");
            }else{
                $FetchQuery = $FetchQuery->where("deposit_into","Operating Account");
            }
        }
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $FetchQuery = $FetchQuery->whereBetween('payment_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        $totalData=$FetchQuery->count();
        $totalFiltered = $totalData; 

        $FetchQuery = $FetchQuery->offset($requestData['start'])->limit($requestData['length']);
        $FetchQuery = $FetchQuery->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'] ?? 'desc');
        $FetchQuery = $FetchQuery->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $FetchQuery 
        );
        echo json_encode($json_data);  
    }
    /* public function loadMixAccountActivityOld()
    {   
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);

        if(isset($requestData['account']) && $requestData['account']!=''){
            if($requestData['account']=="trust_account"){
                $FetchQuery = $FetchQuery->where("from_pay","trust");
            }else{
                $FetchQuery = $FetchQuery->where("from_pay","none");
            }
        }
        $FetchQuery = $FetchQuery->where("case_id",$requestData['case_id'])->where("credit_amount","!=",0);
       
       
        $totalData=$FetchQuery->count();
        $totalFiltered = $totalData; 

        $FetchQuery = $FetchQuery->offset($requestData['start'])->limit($requestData['length']);
        $FetchQuery = $FetchQuery->orderBy("id","DESC");
        $FetchQuery = $FetchQuery->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $FetchQuery 
        );
        echo json_encode($json_data);  
    } */

    public function loadMixAccountActivity(Request $request)
    {           
        $FetchQuery = InvoiceHistory::whereHas("invoice", function($query) use($request) {
                        $query->where("case_id", $request->case_id);
                    })->whereIn('acrtivity_title', ['Payment Received','Payment Refund'])->orderBY("created_at", "desc")->with("invoice")->get();
        $trustHistory = TrustHistory::where("allocated_to_case_id", $request->case_id)->where("fund_type", "diposit")
                        ->orderBY("created_at", "desc")->with(['invoice', 'createdByUser'])->get();
        // $all = $FetchQuery->merge($trustHistory);

        $merged = array_merge($FetchQuery->toArray(), $trustHistory->toArray());
        usort($merged, fn($a, $b) => strtotime($a['created_at']) < strtotime($b['created_at']));
        // return $merged;
        $result = collect($merged);

        $totalData=$result->count();
        $totalFiltered = $totalData; 
        $result1 = $result->skip($request->start)->take($request->length);

        $json_data = array(
            "draw"            => intval( $request->draw ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $result1->values()->toArray()
        );
        echo json_encode($json_data);  
    }

    public function loadAccountActivity()
    {   
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","client");

        if(isset($requestData['account']) && $requestData['account']!=''){
            if($requestData['account']=="trust_account"){
                $FetchQuery = $FetchQuery->where("from_pay","trust");
            }else{
                $FetchQuery = $FetchQuery->where("from_pay","none");
            }
        }
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[0]))))), auth()->user()->user_timezone ?? 'UTC')));
            $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[1]))))), auth()->user()->user_timezone ?? 'UTC')));
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [$startDt,$endDt]);
            // $FetchQuery = $FetchQuery->whereBetween('entry_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        
       
        $totalData=$FetchQuery->count();
        $totalFiltered = $totalData; 

        $FetchQuery = $FetchQuery->offset($requestData['start'])->limit($requestData['length']);
        $FetchQuery = $FetchQuery->orderBy("id","DESC");
        $FetchQuery = $FetchQuery->with('leadAdditionalInfo')->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $FetchQuery 
        );
        echo json_encode($json_data);  
    }

    public function trust_account_activity()
    {
        $id=Auth::user()->id;
         $user = User::find($id);
        if(!empty($user)){
            return view('billing.account_activity.trust_account_activitiy');
        }else{
            return view('pages.404');
        }
    }

    public function loadTrustAccountActivityV1()
    {   
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = InvoicePayment::leftJoin("users","invoice_payment.created_by","=","users.id")
        ->select('invoice_payment.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("invoice_payment.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("entry_type","0");
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $FetchQuery = $FetchQuery->whereBetween('payment_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        $totalData=$FetchQuery->count();
        $totalFiltered = $totalData; 

        $FetchQuery = $FetchQuery->offset($requestData['start'])->limit($requestData['length']);
        $FetchQuery = $FetchQuery->orderBy("id","DESC");
        $FetchQuery = $FetchQuery->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $FetchQuery 
        );
        echo json_encode($json_data);  
    }
    public function loadTrustAccountActivity()
    {   
        $columns = array('id', 'id', 'id', 'id', 'id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","trust");
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[0]))))), auth()->user()->user_timezone ?? 'UTC')));
            $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[1]))))), auth()->user()->user_timezone ?? 'UTC')));
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [$startDt,$endDt]);            
            // $FetchQuery = $FetchQuery->whereBetween('entry_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        $totalData=$FetchQuery->count();
        $totalFiltered = $totalData; 

        $FetchQuery = $FetchQuery->offset($requestData['start'])->limit($requestData['length']);
        $FetchQuery = $FetchQuery->orderBy('id', 'desc');
        $FetchQuery = $FetchQuery->with('leadAdditionalInfo')->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $FetchQuery 
        );
        echo json_encode($json_data);  
    }
    public function printTrustAccountActivity(Request $request)
    {
       
        $columns = array('id', 'id', 'id', 'id', 'id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","trust");
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[0]))))), auth()->user()->user_timezone ?? 'UTC')));
            $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[1]))))), auth()->user()->user_timezone ?? 'UTC')));
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [$startDt,$endDt]); 
        }
        $FetchQuery = $FetchQuery->orderBy("id","DESC");
        $FetchQuery = $FetchQuery->with('leadAdditionalInfo')->get();
        if(isset($request->exportType)){
            $casesCsvData=[];
            if(count($FetchQuery) > 0){
                $fileDestination = 'export/'.date('Y-m-d').'/'.Auth::User()->firm_name;
                $folderPath = public_path($fileDestination);

                File::deleteDirectory($folderPath);
                if(!is_dir($folderPath)) {
                    File::makeDirectory($folderPath, $mode = 0777, true, true);
                }    
                
                if(!File::isDirectory($folderPath)){
                    File::makeDirectory($folderPath, 0777, true, true);    
                }
                
                $casesCsvData[]="Date|Related To|Contact|Case Name|Entered By|Notes|Payment Notes|Payment Method|Refund|Refunded|Rejection|Rejected|Amount|Trust|Trust payment|Total|LegalCase ID";
                foreach($FetchQuery as $k=>$v){
                    $Contact = json_decode($v->contact);
                    $Case = json_decode($v->case);
                    if($v->case_id==null && $v->leadAdditionalInfo != null) {
                        $case_title = $v->leadAdditionalInfo->potential_case_title ?? $Contact->name;
                    }else{
                        $case_title = $Case->case_title ?? '';
                    }
                    $casesCsvData[] = date('m/d/Y', strtotime(convertUTCToUserDate(date("Y-m-d", strtotime($v->entry_date)), auth()->user()->user_timezone ?? 'UTC')))."|".(($v->section=="request") ? "#R-".$v->related : $v->related)."|".@$Contact->name."|".$case_title."|".$v->entered_by."|".$v->payment_note."|".$v->notes."|".$v->payment_method."|".(($v->payment_method == 'Refund') ? 'true' : 'false')."|".(($v->payment_method == 'Refunded') ? 'true' : 'false')."|".(($v->payment_method == 'Rejection') ? 'true' : 'false')."|".(($v->payment_method == 'Rejected') ? 'true' : 'false')."|".(($v->d_amt > 0) ? "-".$v->d_amt : $v->c_amt)."|".(($v->payment_method == 'Trust') ? 'true' : 'false')."|".(($v->payment_method == 'Trust') ? 'true' : 'false')."|".$v->t_amt."|".$v->id;
                }

                $file_path =  $folderPath.'/account_activities.csv';  
                $file = fopen($file_path,"w+");
                foreach ($casesCsvData as $exp_data){
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
                }   
                fclose($file); 
                $Path= asset($fileDestination.'/account_activities.csv');
            }
            return response()->json(['errors'=>'','url'=>$Path,'msg'=>"Building File... it will downloading automaticaly"]);
            exit;
        }else{
            $filename="trust_account_activity".time().'.pdf';
            return view('billing.account_activity.trustAccountActivityPdf',compact('FetchQuery','requestData'));
        }
        // $pdf = new Pdf;
        // if($_SERVER['SERVER_NAME']=='localhost'){
        //     $pdf->binary = WKHTMLTOPDF_PATH;
        // }
        // $pdf->addPage($PDFData);
        // $pdf->saveAs(public_path("download/pdf/".$filename));
        // $path = public_path("download/pdf/".$filename);
        // $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);        
        // return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
        // exit;
    }

    public function printAccountActivity(Request $request)
     {
        
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","client");

        if(isset($requestData['account']) && $requestData['account']!=''){
            if($requestData['account']=="trust_account"){
                $FetchQuery = $FetchQuery->where("from_pay","trust");
            }else{
                $FetchQuery = $FetchQuery->where("from_pay","none");
            }
        }
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[0]))))), auth()->user()->user_timezone ?? 'UTC')));
            $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($cutDate[1]))))), auth()->user()->user_timezone ?? 'UTC')));
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [$startDt,$endDt]);
        }
        $FetchQuery = $FetchQuery->orderBy("id","DESC");
        $FetchQuery = $FetchQuery->with('leadAdditionalInfo')->get();
        
        if(isset($request->exportType)){
            $casesCsvData=[];
            $Path='';
            if(count($FetchQuery) > 0){
                $fileDestination = 'export/'.date('Y-m-d').'/'.Auth::User()->firm_name;
                $folderPath = public_path($fileDestination);

                File::deleteDirectory($folderPath);
                if(!is_dir($folderPath)) {
                    File::makeDirectory($folderPath, $mode = 0777, true, true);
                }    
                
                if(!File::isDirectory($folderPath)){
                    File::makeDirectory($folderPath, 0777, true, true);    
                }
                
                $casesCsvData[]="Date|Related To|Contact|Case Name|Entered By|Notes|Payment Notes|Payment Method|Refund|Refunded|Rejection|Rejected|Amount|Trust|Trust payment|Total|LegalCase ID";
                foreach($FetchQuery as $k=>$v){
                    $Contact = json_decode($v->contact);
                    $Case = json_decode($v->case);
                    if($v->case_id==null && $v->leadAdditionalInfo != null) {
                        $case_title = $v->leadAdditionalInfo->potential_case_title ?? ($Contact->name ?? "");
                    }else{
                        $case_title = $Case->case_title ?? 'none';
                    }
                    $casesCsvData[] = date('m/d/Y', strtotime(convertUTCToUserDate(date("Y-m-d", strtotime($v->entry_date)), auth()->user()->user_timezone ?? 'UTC')))."|".(($v->section=="request") ? "#R-".$v->related : "#".$v->related)."|".($Contact->name ?? '')."|".$case_title."|".$v->entered_by."|".$v->payment_note."|".$v->notes."|".$v->payment_method."|".(($v->payment_method == 'Refund') ? 'true' : 'false')."|".(($v->payment_method == 'Refunded') ? 'true' : 'false')."|".(($v->payment_method == 'Rejection') ? 'true' : 'false')."|".(($v->payment_method == 'Rejected') ? 'true' : 'false')."|".(($v->d_amt > 0) ? "-".$v->d_amt : $v->c_amt)."|".(($v->payment_method == 'Trust') ? 'true' : 'false')."|".(($v->payment_method == 'Trust') ? 'true' : 'false')."|".$v->t_amt."|".$v->id;
                }

                $file_path =  $folderPath.'/account_activities.csv';  
                $file = fopen($file_path,"w+");
                foreach ($casesCsvData as $exp_data){
                //fputs($exp_data, chr(0xEF) . chr(0xBB) . chr(0xBF) );
                // $exp_data =  mb_convert_encoding($exp_data, "ISO-8859-1", "UFT-8");
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
                }   
                fclose($file); 
                $Path.= asset($fileDestination.'/account_activities.csv');
            }
            return response()->json(['errors'=>'','url'=>$Path,'msg'=>"Building File... it will downloading automaticaly"]);
            exit;
        }else{
            $filename="account_activity".time().'.pdf';
            return view('billing.account_activity.accountActivityPdf',compact('FetchQuery','requestData'));
        }
        //  $pdf = new Pdf;
        //  // $pdf->setOptions(['javascript-delay' => 5000]);
        //  if($_SERVER['SERVER_NAME']=='localhost'){
        //      $pdf->binary = WKHTMLTOPDF_PATH;
        //  }
        //  $pdf->addPage($PDFData);
        //  // $pdf->setOptions(['javascript-delay' => 5000]);
        //  $pdf->saveAs(public_path("download/pdf/".$filename));
        //  $path = public_path("download/pdf/".$filename);
         // return response()->download($path);
         // exit;
        //  $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        //  return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename, 'PDFData' => $PDFData], 200);
        //  exit;
     }
    /********************************Account Activity ***************************** */

    /********************************Potential Case Invoice View ***************************** */
      public function viewPotentailInvoice(Request $request)
      {
          $invoiceID=base64_decode($request->id);
          // echo Hash::make($invoiceID);
          $findInvoice=Invoices::find($invoiceID);
        //   dd($findInvoice);
          if(empty($findInvoice) || $findInvoice->created_by!=Auth::User()->id)
          {
              return view('pages.404');
          }else{
              $LeadDetails=User::where("id",$findInvoice['user_id'])->withTrashed()->firstOrFail();
              
              $firmData = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->first();
  
              $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoiceID)->orderBy("id","DESC")->get();

              $lastEntry= $InvoiceHistory->first();
            
              $userMaster=User::find($findInvoice->user_id);
              
              $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoiceID)->get();
  
              $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoiceID)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();
  
  
              $SharedInvoiceCount=SharedInvoice::Where("invoice_id",$invoiceID)->count();
              
              return view('billing.invoices.viewIpotentialnvoice',compact('findInvoice','InvoiceHistory','lastEntry','firmData','userMaster','SharedInvoiceCount','InvoiceInstallment','InvoiceHistoryTransaction','LeadDetails'));     
              exit; 
          }
      }
    /********************************Potential Case Invoice View ***************************** */


    /********************************Insights finacials ******************************/
    public function insights_financials()
    {
        $id=Auth::user()->id;
         $user = User::find($id);
        if(!empty($user)){

            /// Case Revenue Collected vs. Billed
            //$totalCollectedInvoicedAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Paid')->where("invoices.created_by",$id)->sum("paid_amount");
            $Invoices = Invoices::where("invoices.created_by",$id);
            $Invoices= $Invoices->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $Invoices= $Invoices->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            $Invoices= $Invoices->where("paid_amount","!=",0);
            //    $Invoices= $Invoices->where("invoices.status",'Paid');
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                $Invoices= $Invoices->where("case_practice_area.id",$_GET['practice_area']);
            }
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $Invoices= $Invoices->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $Invoices= $Invoices->where("case_master.case_office","1");
            }
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $Invoices= $Invoices->whereIn("case_master.id",$leadAttorneysList);
            }  
            $Invoices= $Invoices->select("*");
            $CAmt=$Invoices->sum("paid_amount");
            $totalCollectedInvoicedAmount=$CAmt;

            /// Case Revenue Collected vs. Billed
            /// Case Revenue Collected vs. Billed

            $InvoicesTotal = Invoices::where("invoices.created_by",$id);
            $InvoicesTotal= $InvoicesTotal->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $InvoicesTotal= $InvoicesTotal->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                    $InvoicesTotal= $InvoicesTotal->where("case_practice_area.id",$_GET['practice_area']);
            }
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $InvoicesTotal= $InvoicesTotal->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $InvoicesTotal= $InvoicesTotal->where("case_master.case_office","1");
            }  
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $InvoicesTotal= $InvoicesTotal->whereIn("case_master.id",$leadAttorneysList);
            }  
            $InvoicesTotal= $InvoicesTotal->select("*");
            $TAmt=$InvoicesTotal->sum("total_amount");
            $totalInvoicedAmount=$TAmt;
            //    $totalInvoicedAmount=Invoices::where("invoices.created_by",$id)->sum("total_amount");
            /// Case Revenue Collected vs. Billed

            //Collected By Practice Area
            $InvoicesCollectedByPA = Invoices::where("invoices.created_by",$id);
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("paid_amount","!=",0);
            //    $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("invoices.status",'Paid');
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("case_practice_area.id",$_GET['practice_area']);
            }   
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("case_master.case_office","1");
            } 
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->whereIn("case_master.id",$leadAttorneysList);
            }   
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->select("*");
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->get();

            $practiceArea=[];
            foreach($InvoicesCollectedByPA as $k=>$v){
                if($v->practice_area!="-1"){
                    $practiceArea[$v->title][]=$v->paid_amount;
                }else{
                    $practiceArea['Unspecified'][]=$v->paid_amount;
                }
            }
            $finalPracticeAreaListPercent=[];
            $totalSumPA=0;
            foreach($practiceArea as $g=>$h){
                $finalPracticeAreaListPercent[$g]['totals']=number_format(array_sum($h),2);
                $finalPracticeAreaListPercent[$g]['title']=$g;
                $totalSumPA+=array_sum($h);
            }
            //Collected By Practice Area


            //Collected By Billing Type
            $InvoicesCollectedByBT = Invoices::where("invoices.created_by",$id);
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->where("paid_amount","!=",0);
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->where("case_practice_area.id",$_GET['practice_area']);
            }
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->where("case_master.case_office","1");
            }  
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->whereIn("case_master.id",$leadAttorneysList);
            }   
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->select("*","invoices.id as iid");
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->get();
            //    print_r($InvoicesCollectedByBT);exit;
            //    $Invoices = Invoices::where("invoices.created_by",$id)
            //    ->where("paid_amount","!=",0)
            //    ->select("*")
            //    ->get();
            //   print_r($InvoicesCollectedByBT);exit;
            $BillingType=[];
            foreach($InvoicesCollectedByBT as $mixkey=>$mixVal){
            //    echo $mixVal->iid;
                $ExpenseForInvoice=ExpenseForInvoice::where("invoice_id",$mixVal->iid)->get()->pluck("expense_entry_id");
                $TimeEntryForInvoice=TimeEntryForInvoice::where("invoice_id",$mixVal->iid)->get()->pluck("time_entry_id");
                if(!$ExpenseForInvoice->isEmpty() && !$TimeEntryForInvoice->isEmpty()){
                    $BillingType['Mixed'][]=$mixVal->iid;
                }else{
                    $BillingType['Unspecified'][]=$mixVal->iid;
                }
            }
            //    print_r($BillingType);exit;
            $displayChartBillingType=[];
            if(isset($BillingType['Mixed'])){
                $displayChartBillingType['Mixed']=Invoices::whereIn("id",$BillingType['Mixed'])->sum("paid_amount");
            }
            if(isset($BillingType['Unspecified'])){
                $displayChartBillingType['Unspecified']=Invoices::whereIn("id",$BillingType['Unspecified'])->sum("paid_amount");
            }

            //    print_r($displayChartBillingType);
            //Collected By Billing Type


            //Hours Recorded by Employee
            $staffList = User::whereIn("user_level",[1,3])->where("firm_name",Auth::User()->firm_name)->where('user_status','1')->orderBy("created_at","ASC")->get();
            $timeEntryList=[];
            foreach($staffList as $staffKey=>$staffVal){
                
                $expenseTotalBillable=$timeTotalBillable=$expenseTotalNonBillable=$timeTotalNonBillable=$billableHours=$nonBillableHours=0;
                $ExpenseEntry=ExpenseEntry::select("*")->where("user_id",$staffVal->id);
                if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                    $dates=explode("-",$_GET['date_range']);
                    $ExpenseEntry=$ExpenseEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
                }
                $ExpenseEntry=$ExpenseEntry->get();
                
                // echo "<pre>";print_r($ExpenseEntry);die();
                foreach($ExpenseEntry as $kE=>$vE){
                    if($vE['time_entry_billable']=="yes"){
                        $billableHours+=$vE->duration;
                        $expenseTotalBillable+=($vE->cost*$vE->duration);
                    }else{
                        $nonBillableHours+=$vE->duration;
                        $expenseTotalNonBillable+=($vE->cost*$vE->duration);
                    }
                }

                $TimeEntry=TaskTimeEntry::select("*")->where("user_id",$staffVal->id);                
                if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                    $dates=explode("-",$_GET['date_range']);
                    
                    $TimeEntry=$TimeEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
                }
                $TimeEntry=$TimeEntry->get();
                // dd($TimeEntry);
                foreach($TimeEntry as $TK=>$TE){
                    if($TE['rate_type']=="flat"){
                        if($TE['time_entry_billable']=="yes"){
                                $billableHours+=$TE->duration;
                                $timeTotalBillable+=$TE['entry_rate'];
                        }else{
                                $nonBillableHours+=$TE->duration;
                                $timeTotalNonBillable+=$TE['entry_rate'];
                        }
                    }else{
                            if($TE['time_entry_billable']=="yes"){
                                $billableHours+=$TE->duration;
                                $timeTotalBillable+=($TE['entry_rate']*$TE['duration']);
                            }else{
                                $nonBillableHours+=$TE->duration;
                                $timeTotalNonBillable+=($TE['entry_rate']*$TE['duration']);
                            }
                    }
                }
                $timeEntryList[$staffVal->id]['user_name']=$staffVal->first_name.' '.$staffVal->last_name;
                $timeEntryList[$staffVal->id]['expense_total_billable']=$expenseTotalBillable;
                $timeEntryList[$staffVal->id]['expense_total_non_billable']=$expenseTotalNonBillable;
                $timeEntryList[$staffVal->id]['time_total_billable']=$timeTotalBillable;
                $timeEntryList[$staffVal->id]['time_total_non_billable']=$timeTotalNonBillable;
                $timeEntryList[$staffVal->id]['billable_entry']=($expenseTotalBillable+$timeTotalBillable);
                $timeEntryList[$staffVal->id]['non_billable_entry']=($expenseTotalNonBillable+$timeTotalNonBillable);
                $timeEntryList[$staffVal->id]['grand_total']=($expenseTotalNonBillable+$timeTotalNonBillable+$expenseTotalBillable+$timeTotalBillable);
                $timeEntryList[$staffVal->id]['billableHours']=$billableHours;
                $timeEntryList[$staffVal->id]['nonBillableHours']=$nonBillableHours;
                $timeEntryList[$staffVal->id]['grand_total_hrs']=($billableHours + $nonBillableHours);
                //Hours Recorded by Employee
            }

            // echo "<pre>";print_r($timeEntryList);exit;
            $firmData=Firm::find(Auth::User()->firm_name);

            $getChildUsers=$this->getParentAndChildUserIds();
            $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  


            $leadAttorneysCases= Invoices::where("invoices.created_by",$id)->select("*")->get()->pluck("case_id");
            $leadAttorneysList=  CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.user_title","users.email","case_staff.originating_attorney","case_staff.lead_attorney")->whereIn("case_id",$leadAttorneysCases)->whereIn("users.id",$getChildUsers)->groupBy("id")->get();

           return view('billing.insights_financials.insights_financials',compact('totalInvoicedAmount','totalCollectedInvoicedAmount','finalPracticeAreaListPercent','totalSumPA','displayChartBillingType','timeEntryList','firmData','practiceAreaList','user','leadAttorneysList'));
        }else{
            return view('pages.404');
        }
    }

    public function printInsightActivity(Request $request)
     {
        $id=Auth::user()->id;
        $user = User::find($id);
        if(!empty($user)){

            /// Case Revenue Collected vs. Billed
            //$totalCollectedInvoicedAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Paid')->where("invoices.created_by",$id)->sum("paid_amount");
            $Invoices = Invoices::where("invoices.created_by",$id);
            $Invoices= $Invoices->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $Invoices= $Invoices->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            $Invoices= $Invoices->where("paid_amount","!=",0);
            //    $Invoices= $Invoices->where("invoices.status",'Paid');
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                $Invoices= $Invoices->where("case_practice_area.id",$_GET['practice_area']);
            }
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $Invoices= $Invoices->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $Invoices= $Invoices->where("case_master.case_office","1");
            }
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $Invoices= $Invoices->whereIn("case_master.id",$leadAttorneysList);
            }  
            $Invoices= $Invoices->select("*");
            $CAmt=$Invoices->sum("paid_amount");
            $totalCollectedInvoicedAmount=$CAmt;

            /// Case Revenue Collected vs. Billed
            /// Case Revenue Collected vs. Billed

            $InvoicesTotal = Invoices::where("invoices.created_by",$id);
            $InvoicesTotal= $InvoicesTotal->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $InvoicesTotal= $InvoicesTotal->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                    $InvoicesTotal= $InvoicesTotal->where("case_practice_area.id",$_GET['practice_area']);
            }
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $InvoicesTotal= $InvoicesTotal->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $InvoicesTotal= $InvoicesTotal->where("case_master.case_office","1");
            }  
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $InvoicesTotal= $InvoicesTotal->whereIn("case_master.id",$leadAttorneysList);
            }  
            $InvoicesTotal= $InvoicesTotal->select("*");
            $TAmt=$InvoicesTotal->sum("total_amount");
            $totalInvoicedAmount=$TAmt;
            //    $totalInvoicedAmount=Invoices::where("invoices.created_by",$id)->sum("total_amount");
            /// Case Revenue Collected vs. Billed

            //Collected By Practice Area
            $InvoicesCollectedByPA = Invoices::where("invoices.created_by",$id);
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("paid_amount","!=",0);
            //    $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("invoices.status",'Paid');
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("case_practice_area.id",$_GET['practice_area']);
            }   
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->where("case_master.case_office","1");
            } 
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $InvoicesCollectedByPA= $InvoicesCollectedByPA->whereIn("case_master.id",$leadAttorneysList);
            }   
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->select("*");
            $InvoicesCollectedByPA= $InvoicesCollectedByPA->get();

            $practiceArea=[];
            foreach($InvoicesCollectedByPA as $k=>$v){
                if($v->practice_area!="-1"){
                    $practiceArea[$v->title][]=$v->paid_amount;
                }else{
                    $practiceArea['Unspecified'][]=$v->paid_amount;
                }
            }
            $finalPracticeAreaListPercent=[];
            $totalSumPA=0;
            foreach($practiceArea as $g=>$h){
                $finalPracticeAreaListPercent[$g]['totals']=number_format(array_sum($h),2);
                $finalPracticeAreaListPercent[$g]['title']=$g;
                $totalSumPA+=array_sum($h);
            }
            //Collected By Practice Area


            //Collected By Billing Type
            $InvoicesCollectedByBT = Invoices::where("invoices.created_by",$id);
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area");
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->where("paid_amount","!=",0);
            if(isset($_GET['practice_area']) && $_GET['practice_area']!=""){
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->where("case_practice_area.id",$_GET['practice_area']);
            }
            if(isset($_GET['date_range']) && $_GET['date_range']!=""){
                $dates=explode("-",$_GET['date_range']);
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->whereBetween('invoices.invoice_date', [date('Y-m-d',strtotime($dates[0])), date('Y-m-d',strtotime($dates[1]))]);
            }
            if(isset($_GET['office']) && $_GET['office']!=""){
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->where("case_master.case_office","1");
            }  
            if(isset($_GET['lead_attorney']) && $_GET['lead_attorney']!=""){
                $leadAttorneysList=  CaseStaff::select("*")->where("case_staff.lead_attorney",$_GET['lead_attorney'])->get()->pluck("case_id");
                $InvoicesCollectedByBT= $InvoicesCollectedByBT->whereIn("case_master.id",$leadAttorneysList);
            }   
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->select("*","invoices.id as iid");
            $InvoicesCollectedByBT= $InvoicesCollectedByBT->get();
            //    print_r($InvoicesCollectedByBT);exit;
            //    $Invoices = Invoices::where("invoices.created_by",$id)
            //    ->where("paid_amount","!=",0)
            //    ->select("*")
            //    ->get();
            //   print_r($InvoicesCollectedByBT);exit;
            $BillingType=[];
            foreach($InvoicesCollectedByBT as $mixkey=>$mixVal){
            //    echo $mixVal->iid;
                $ExpenseForInvoice=ExpenseForInvoice::where("invoice_id",$mixVal->iid)->get()->pluck("expense_entry_id");
                $TimeEntryForInvoice=TimeEntryForInvoice::where("invoice_id",$mixVal->iid)->get()->pluck("time_entry_id");
                if(!$ExpenseForInvoice->isEmpty() && !$TimeEntryForInvoice->isEmpty()){
                    $BillingType['Mixed'][]=$mixVal->iid;
                }else{
                    $BillingType['Unspecified'][]=$mixVal->iid;
                }
            }
            //    print_r($BillingType);exit;
            $displayChartBillingType=[];
            if(isset($BillingType['Mixed'])){
                $displayChartBillingType['Mixed']=Invoices::whereIn("id",$BillingType['Mixed'])->sum("paid_amount");
            }
            if(isset($BillingType['Unspecified'])){
                $displayChartBillingType['Unspecified']=Invoices::whereIn("id",$BillingType['Unspecified'])->sum("paid_amount");
            }

            //    print_r($displayChartBillingType);
            //Collected By Billing Type


            //Hours Recorded by Employee
            $staffList = User::whereIn("user_type",[2,3])->where("firm_name",Auth::User()->firm_name)->orderBy("created_at","ASC")->get();
            $timeEntryList=[];
            foreach($staffList as $staffKey=>$staffVal){
                
                $expenseTotalBillable=$timeTotalBillable=$expenseTotalNonBillable=$timeTotalNonBillable=0;
                $ExpenseEntry=ExpenseEntry::select("*")->where("user_id",$staffVal->id)->get();
                foreach($ExpenseEntry as $kE=>$vE){
                    if($vE['time_entry_billable']=="yes"){
                        $expenseTotalBillable+=($vE->cost*$vE->duration);
                    }else{
                        $expenseTotalNonBillable+=($vE->cost*$vE->duration);
                    }
                }

                $TimeEntry=TaskTimeEntry::select("*")->where("user_id",$staffVal->id)->get();
                foreach($TimeEntry as $TK=>$TE){
                    if($TE['rate_type']=="flat"){
                        if($TE['time_entry_billable']=="yes"){
                                $timeTotalBillable+=$TE['entry_rate'];
                        }else{
                                $timeTotalNonBillable+=$TE['entry_rate'];
                        }
                    }else{
                            if($TE['time_entry_billable']=="yes"){
                                $timeTotalBillable+=($TE['entry_rate']*$TE['duration']);
                            }else{
                                $timeTotalNonBillable+=($TE['entry_rate']*$TE['duration']);
                            }
                    }
                }
                $timeEntryList[$staffVal->id]['user_name']=$staffVal->first_name.' '.$staffVal->last_name;
                $timeEntryList[$staffVal->id]['expense_total_billable']=$expenseTotalBillable;
                $timeEntryList[$staffVal->id]['expense_total_non_billable']=$expenseTotalNonBillable;
                $timeEntryList[$staffVal->id]['time_total_billable']=$timeTotalBillable;
                $timeEntryList[$staffVal->id]['time_total_non_billable']=$timeTotalNonBillable;
                $timeEntryList[$staffVal->id]['billable_entry']=($expenseTotalBillable+$timeTotalBillable);
                $timeEntryList[$staffVal->id]['non_billable_entry']=($expenseTotalNonBillable+$timeTotalNonBillable);
                $timeEntryList[$staffVal->id]['grand_total']=($expenseTotalNonBillable+$timeTotalNonBillable+$expenseTotalBillable+$timeTotalBillable);
                //Hours Recorded by Employee
            }

            // print_r($timeEntryList);exit;
            $firmData=Firm::find(Auth::User()->firm_name);

            $getChildUsers=$this->getParentAndChildUserIds();
            $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  


            $leadAttorneysCases= Invoices::where("invoices.created_by",$id)->select("*")->get()->pluck("case_id");
            $leadAttorneysList=  CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.user_title","users.email","case_staff.originating_attorney","case_staff.lead_attorney")->whereIn("case_id",$leadAttorneysCases)->whereIn("users.id",$getChildUsers)->groupBy("id")->get();

           
         
         $filename="insight_activity".time().'.pdf';
        //   $PDFData= view('billing.insights_financials.print_insights_financials_pdf',compact('totalInvoicedAmount','totalCollectedInvoicedAmount','finalPracticeAreaListPercent','totalSumPA','displayChartBillingType','timeEntryList','firmData','practiceAreaList','user','leadAttorneysList'));
        // //  PDF::loadHTML($PDFData)->setPaper('a4', 'landscape')->setWarnings(false)->save('public/myfile.pdf');
        //  return PDF::loadFile(public_path().'/fa.html')->save('public/my_stored_file.pdf')->stream('public/download.pdf');
        // dd($totalInvoicedAmount);
        return $PDFData= view('billing.insights_financials.print_insights_financials_pdf',compact('totalInvoicedAmount','totalCollectedInvoicedAmount','finalPracticeAreaListPercent','totalSumPA','displayChartBillingType','timeEntryList','firmData','practiceAreaList','user','leadAttorneysList'));
       
         $pdf = new Pdf;         
         $pdf->setOptions(['javascript-delay' => 5000]);

         if($_SERVER['SERVER_NAME']=='localhost'){
             $pdf->binary = WKHTMLTOPDF_PATH;
         }
         $pdf->addPage($PDFData);
         if (!$pdf->send()) {
             $error = $pdf->getError();
         }


        //  $pdf->addPage($PDFData);
        //  $pdf->setOptions(['javascript-delay' => 10000]);

        //  $pdf->saveAs(public_path("download/pdf/".$filename));
        //  $path = public_path("download/pdf/".$filename);
      
        //  return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
        //  exit;
        }
     }
    /********************************Insights finacials ******************************/


    public function createInvoiceBatch(Request $request)
    {        
        $request->merge([
            'case_id' => ($request->case_id!="[]") ? $request->case_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required|json',
            // 'discounts.amount.*'=> 'required|numeric',
            
        ],[
            'case_id.required'=>'Please select at least one case to bill',
            // 'discounts.amount.*'=>'discounts.amount.* percent value must be greater than 0'
        ]);
        
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            if(isset($request->discounts['amount']) && count($request->discounts['amount'])<0){
                $customeError=[];
                for($i=0;$i<count($request->discounts['amount']);$i++){
                    if($request->discounts['amount'][$i]==""){
                    
                    }
                    if($request->discounts['amount'][$i]<=0 || $request->discounts['amount'][$i]==""){
                        $customeError[]=ucfirst($request->discounts['discount_type'][$i])." percent value must be greater than 0";
                    }
                }
                if(!empty($customeError)){
                    return response()->json(['errors'=>$customeError]);
                }
            }
            dbStart();        
            try {
            $totalInvoice=[];
            $totalSent=$totalUnsent=$totalDraft=0;
            $allCases=json_decode($request->case_id);
            
            // print_r($allCases);exit;
            foreach($allCases as $caseVal){
                $caseClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$caseVal)->where('case_client_selection.is_billing_contact','yes')->select("*")->first();
                if(!empty($caseClient)){
                $InvoiceSave=new Invoices;
                $InvoiceSave->id=$request->invoice_number_padded;
                $InvoiceSave->user_id=$caseClient['selected_user'];
                $InvoiceSave->case_id=$caseVal;
                $InvoiceSave->invoice_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->batch['invoice_date'])))), auth()->user()->user_timezone ?? 'UTC');
                if($request->batch['payment_terms']==""){
                    $InvoiceSave->payment_term="5";
                }else{
                    $InvoiceSave->payment_term=$request->batch['payment_terms'];
                }
                $InvoiceSave->due_date=($request->batch['due_date']) ? date('Y-m-d',strtotime($request->batch['due_date'])) : NULL;   
                $InvoiceSave->automated_reminder="no";
                $InvoiceSave->payment_plan_enabled="no";
                if(isset($request->batch['draft'])){
                    $InvoiceSave->status='Draft';
                    $InvoiceSave->bill_sent_status='Draft';
                    $totalDraft++;
                }else{
                    $InvoiceSave->status='Unsent';
                    $InvoiceSave->bill_sent_status='Unsent';
                    $totalUnsent++;
                }
                $InvoiceSave->notes=($request->bill['notes'])??'';
                $InvoiceSave->created_by=Auth::User()->id; 
                $InvoiceSave->created_at=date('Y-m-d h:i:s'); 
                $InvoiceSave->save();

                $InvoiceSave->invoice_unique_token=Hash::make($InvoiceSave->id);
                $InvoiceSave->invoice_token=Str::random(250);
                $InvoiceSave->firm_id = auth()->user()->firm_name;
                $InvoiceSave->save();


                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$InvoiceSave->id;
                $invoiceHistory['acrtivity_title']='Invoice Created';
                $invoiceHistory['pay_method']=NULL;
                $invoiceHistory['amount']=NULL;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['deposit_into']=NULL;
                $invoiceHistory['notes']=NULL;
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $this->invoiceHistory($invoiceHistory);


                //Get the Time Entry list
                $TimeEntry=TaskTimeEntry::select("task_time_entry.*");
                $TimeEntry=$TimeEntry->where("task_time_entry.case_id",$caseVal);
                $TimeEntry=$TimeEntry->where("task_time_entry.status","unpaid");
                if(isset($request->batch['start_date']) && isset($request->batch['end_date'])){
                    $TimeEntry=$TimeEntry->whereBetween("task_time_entry.entry_date",[date('Y-m-d',strtotime($request->batch['start_date'])),date('Y-m-d',strtotime($request->batch['end_date']))]);
                }
                $TimeEntry=$TimeEntry->get();
 
                //Time entry referance
                $timeEntryTotal=0;
                if(!$TimeEntry->isEmpty()){
                    foreach($TimeEntry as $k=>$v){
                        $TimeEntryForInvoice=new TimeEntryForInvoice;
                        $TimeEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                        $TimeEntryForInvoice->time_entry_id=$v->id;
                        $TimeEntryForInvoice->created_by=Auth::User()->id; 
                        $TimeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                        $TimeEntryForInvoice->save();
                        DB::table('task_time_entry')->where("id",$v->id)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);

                        if($v->rate_type=="flat"){
                            $timeEntryTotal+=$v->entry_rate;
                        }else{
                            $timeEntryTotal+=($v->entry_rate*$v->duration);
                        }
                    }
                }
                
                //Get the Expense Entry list
                $ExpenseEntry=ExpenseEntry::select("expense_entry.*");
                $ExpenseEntry=$ExpenseEntry->where("expense_entry.case_id",$caseVal);
                $ExpenseEntry=$ExpenseEntry->where("expense_entry.status","unpaid");
                if(isset($request->batch['start_date']) && isset($request->batch['end_date'])){
                    // $ExpenseEntry=$ExpenseEntry->where("expense_entry.status","unpaid");
                    $ExpenseEntry = $ExpenseEntry->whereBetween("expense_entry.entry_date",[date('Y-m-d',strtotime($request->batch['start_date'])),date('Y-m-d',strtotime($request->batch['end_date']))]);
                }

                $ExpenseEntry=$ExpenseEntry->get();
                //Expense entry referance
                $expenseEntryTotal=0;
                if(!$ExpenseEntry->isEmpty()){
                    foreach($ExpenseEntry as $k=>$v){
                        $ExpenseEntryForInvoice=new ExpenseForInvoice;
                        $ExpenseEntryForInvoice->invoice_id=$InvoiceSave->id;                    
                        $ExpenseEntryForInvoice->expense_entry_id =$v->id;
                        $ExpenseEntryForInvoice->created_by=Auth::User()->id; 
                        $ExpenseEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                        $ExpenseEntryForInvoice->save();
                        DB::table('expense_entry')->where("id",$v->id)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                        $expenseEntryTotal+=($v->cost*$v->duration);
                    }
                }


                //  get the flat fee entry
                $flatFinalTotalBillable=0;   
                if(in_array($caseClient->billing_method,["flat","mixed"])){                                 
                    $FlatFeeEntry=FlatFeeEntry::select("id","cost","entry_date");
                    $FlatFeeEntry=$FlatFeeEntry->where("case_id",$caseVal);
                    $FlatFeeEntry=$FlatFeeEntry->where("status","unpaid");
                    if(isset($request->batch['start_date']) && isset($request->batch['end_date'])){
                        $FlatFeeEntry = $FlatFeeEntry->whereBetween("entry_date",[date('Y-m-d',strtotime($request->batch['start_date'])),date('Y-m-d',strtotime($request->batch['end_date']))]);
                    }
                    $FlatFeeEntry=$FlatFeeEntry->get();
                    if(count($FlatFeeEntry) > 0){
                        foreach($FlatFeeEntry as $k =>$v){
                            $FlatFeeEntryForInvoice=new FlatFeeEntryForInvoice;
                            $FlatFeeEntryForInvoice->invoice_id=$InvoiceSave->id;              
                            $FlatFeeEntryForInvoice->flat_fee_entry_id=$v->id;
                            $FlatFeeEntryForInvoice->created_by=Auth::User()->id; 
                            $FlatFeeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                            $FlatFeeEntryForInvoice->save();
                            DB::table('flat_fee_entry')->where("id",$v->id)->update([
                                'status'=>'paid',
                                'invoice_link'=>$InvoiceSave->id
                            ]);
                            $flatFinalTotalBillable += str_replace(",","",number_format($v->cost, 2));
                        }
                    }else{                              
                        $flatTotalBillable = 0;          
                        $flatFeeData = FlatFeeEntry::select("*")->where('case_id', $caseVal)->where("time_entry_billable","yes")->get();
                        foreach($flatFeeData as $TK=>$TE){
                            if($TE->status == 'paid'){
                                $flatTotalBillable+=str_replace(",","",number_format($TE['cost'], 2));
                            }
                        }
                        $flatFinalTotalBillable = ($caseClient->billing_amount - $flatTotalBillable);
                        $flatFinalTotalBillable = ($flatFinalTotalBillable > 0 ) ?  $flatFinalTotalBillable : 0;                        
                        if($flatFinalTotalBillable > 0){
                            $flatFeeEntry = FlatFeeEntry::create([
                                'case_id' => $caseVal,
                                'user_id' => auth()->id(),
                                'entry_date' => Carbon::now(),
                                'cost' =>  $flatFinalTotalBillable,
                                'time_entry_billable' => 'yes',
                                'status'=>'paid',
                                'invoice_link' => $InvoiceSave->id,
                                'firm_id' => Auth::User()->firm_name,
                                'created_by' => auth()->id(), 
                            ]);
                            $FlatFeeEntryForInvoice=new FlatFeeEntryForInvoice;
                            $FlatFeeEntryForInvoice->invoice_id=$InvoiceSave->id;              
                            $FlatFeeEntryForInvoice->flat_fee_entry_id=$flatFeeEntry->id;
                            $FlatFeeEntryForInvoice->created_by=Auth::User()->id; 
                            $FlatFeeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                            $FlatFeeEntryForInvoice->save();
                        }
                    }    
                }
                $subTotal=$flatFinalTotalBillable+$timeEntryTotal+$expenseEntryTotal;

                // print_r($request->all());
                //Invoice Shared With Client
                if(isset($request->batch['share']) && $request->batch['share']=="1"){

                    if($request->batch['sharing_user']=="billing_only"){
                            $CaseClientSelection=CaseClientSelection::select("selected_user")->where("is_billing_contact","yes")->where("case_id",$caseVal)->get()->pluck("selected_user");
                    }else{
                            $CaseClientSelection=CaseClientSelection::select("selected_user")->where("case_id",$caseVal)->get()->pluck("selected_user");
                    }
                   
                    if(!$CaseClientSelection->isEmpty()){
                        foreach($CaseClientSelection as $k=>$vselected_user){
                            $GetAccessDAta=UsersAdditionalInfo::select("*")->where('user_id',$vselected_user)->first();
                            if($GetAccessDAta['client_portal_enable']=='0')
                            {
                                    $user=User::find($vselected_user);
                                    $enableAccess=UsersAdditionalInfo::where('user_id',$vselected_user)->update(['client_portal_enable'=>"1"]);

                                    $getTemplateData = EmailTemplate::find(6);
                                    $fullName=$user['first_name']. ' ' .$user['last_name'];
                                    $email=$user['email'];
                                    $token=url('user/verify', $user->token);
                                    $mail_body = $getTemplateData->content;
                                    $mail_body = str_replace('{name}', $fullName, $mail_body);
                                    $mail_body = str_replace('{email}', $email,$mail_body);
                                    $mail_body = str_replace('{token}', $token,$mail_body);
                                    $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                                    $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
                                    $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
                                    $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
                                    $mail_body = str_replace('{refuser}', Auth::User()->first_name, $mail_body);                          
                                    $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                                    $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);       
                                    $refUser = Auth::User()->first_name . " ". Auth::User()->last_name;
                                    $userEmail = [
                                        "from" => FROM_EMAIL,
                                        "from_title" => FROM_EMAIL_TITLE,
                                        "subject" => $refUser." ".$getTemplateData->subject. " ". TITLE,
                                        "to" => $user['email'],
                                        "full_name" => $fullName,
                                        "mail_body" => $mail_body
                                        ];
                                    $sendEmail = $this->sendMail($userEmail);
                            }
                            
                            $firmData=Firm::find(Auth::User()->firm_name);
                            $getTemplateData = EmailTemplate::find(12);
                            // $token=url('activate_account/bills=&web_token='.$InvoiceSave->invoice_unique_token);
                            $token = route("client/bills/detail", $InvoiceSave->decode_id);
                            $mail_body = $getTemplateData->content;
                            $mail_body = str_replace('{message}', $request->message, $mail_body);
                            $mail_body = str_replace('{token}', $token, $mail_body);
                            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                            $clientData=User::find($vselected_user);
                            $user = [
                                "from" => FROM_EMAIL,
                                "from_title" => FROM_EMAIL_TITLE,
                                "subject" => $firmData->firm_name." has sent you an invoice",
                                "to" => $clientData['email'],
                                "full_name" => "",
                                "mail_body" => $mail_body
                            ];
                            $sendEmail = $this->sendMail($user);                            
                            
                            $SharedInvoice=new SharedInvoice;
                            $SharedInvoice->invoice_id=$InvoiceSave->id;                    
                            $SharedInvoice->user_id =$vselected_user;
                            $SharedInvoice->created_by=Auth::User()->id; 
                            $SharedInvoice->created_at=date('Y-m-d h:i:s'); 
                            $SharedInvoice->save();
    
                            $InvoiceSave->status='Sent';

                            $invoiceHistory=[];
                            $invoiceHistory['invoice_id']=$InvoiceSave->id;
                            $invoiceHistory['acrtivity_title']='Shared w/Contacts';
                            $invoiceHistory['pay_method']=NULL;
                            $invoiceHistory['amount']=NULL;
                            $invoiceHistory['responsible_user']=Auth::User()->id;
                            $invoiceHistory['deposit_into']=NULL;
                            $invoiceHistory['notes']=NULL;
                            $invoiceHistory['created_by']=Auth::User()->id;
                            $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                            $this->invoiceHistory($invoiceHistory);
                            $totalUnsent--;
                            $totalSent++;

                        }
                    }
                }
                
                // $CaseClientSelection=CaseClientSelection::select("selected_user","billing_amount")->where("billing_method","!=",NULL)->where("case_id",$caseVal)->first();

                // $flatFees=($CaseClientSelection['billing_amount'])??0;
                
                // dd($subTotal);
                // if(isset($request->discounts['discount_type'])){
                //     for($k=0;$k<count($request->discounts['discount_type']);$k++){
                //         $finalAmount=0;
                //         $InvoiceAdjustment = new InvoiceAdjustment;
                //         $InvoiceAdjustment->case_id =$caseVal;
                //         $InvoiceAdjustment->token =NULL;
                //         $InvoiceAdjustment->invoice_id =$InvoiceSave->id;
                //         $InvoiceAdjustment->item=$request->discounts['discount_type'][$k];
                //         $InvoiceAdjustment->applied_to=$request->discounts['discount_applied_to'][$k];
                //         $InvoiceAdjustment->ad_type=$request->discounts['discount_amount_type'][$k];
                //         $InvoiceAdjustment->percentages =(float)$request->discounts['amount'][$k];

                //         if($request->discounts['discount_applied_to'][$k]=="flat_fees"){
                //             $InvoiceAdjustment->basis =str_replace(",","",$flatFees);
                //             if($request->discounts['discount_amount_type'][$k]=="percentage"){
                //                 $finalAmount=($request->discounts['amount'][$k]/100)*$flatFees;
                //             }else{
                //                 $finalAmount=$request->discounts['amount'][$k];
                //             }

                //         }
                //         if($request->discounts['discount_applied_to'][$k]=="time_entries"){
                //         $InvoiceAdjustment->basis =str_replace(",","",$timeEntryTotal);
                //             if($request->discounts['discount_amount_type'][$k]=="percentage"){
                                
                //                 $finalAmount=($request->discounts['amount'][$k]/100)*$timeEntryTotal;
                //             }else{
                //                 $finalAmount=$request->discounts['amount'][$k];
                //             }
                //         }
                //         if($request->discounts['discount_applied_to'][$k]=="expenses"){
                //         $InvoiceAdjustment->basis =str_replace(",","",$expenseEntryTotal);
                //             if($request->discounts['discount_amount_type'][$k]=="percentage"){
                //                 $finalAmount=($request->discounts['amount'][$k]/100)*$expenseEntryTotal;
                //             }else{
                //                 $finalAmount=$request->discounts['amount'][$k];
                //             }
                //         }
                //         if($request->discounts['discount_applied_to'][$k]=="sub_total"){
                //             $InvoiceAdjustment->basis =str_replace(",","",$subTotal);
                //             if($request->discounts['discount_amount_type'][$k]=="percentage"){
                //                 $finalAmount=($request->discounts['amount'][$k]/100)*$subTotal;
                //             }else{
                //                 $finalAmount=$request->discounts['amount'][$k];
                //             }
                //         }
                        
                //         if($request->discounts['discount_applied_to'][$k]=="balance_forward_total"){
                //             $InvoiceAdjustment->basis =str_replace(",","",$request->basic);
                //         }
                    
                //         $InvoiceAdjustment->amount =str_replace(",","",$finalAmount);
                //         $InvoiceAdjustment->notes =$request->discounts['notes'][$k];
                //         $InvoiceAdjustment->created_at=date('Y-m-d h:i:s'); 
                //         $InvoiceAdjustment->created_by=Auth::User()->id; 
                //         $InvoiceAdjustment->save();
                        
                //         if($request->discounts['discount_type'][$k]=="discount"){
                //             $subTotal=$subTotal-$finalAmount;
                //         }else{
                //             $subTotal=$subTotal+$finalAmount;
                //         }
                    
                //     }
                // }
                $InvoiceSave->total_amount=$subTotal;
                $InvoiceSave->due_amount=$subTotal;
                
                // Store invoice view settings
                $jsonData = [];
                $preferenceSetting = InvoiceSetting::where("firm_id", auth()->user()->firm_name)->with("reminderSchedule")->first();
                $customizationSetting = InvoiceCustomizationSetting::where("firm_id", auth()->user()->firm_name)->with("flatFeeColumn", "timeEntryColumn", "expenseColumn")->first();
                if($preferenceSetting) {
                    $jsonData = [
                        'hours_decimal_point' => $preferenceSetting->time_entry_hours_decimal_point,
                        'payment_terms' => $preferenceSetting->time_entry_default_invoice_payment_terms,
                        'trust_credit_activity_on_invoice' => $preferenceSetting->default_trust_and_credit_display_on_new_invoices,
                        'default_terms_conditions' => $preferenceSetting->time_entry_default_terms_conditions,
                        'is_non_trust_retainers_credit_account' => $preferenceSetting->is_non_trust_retainers_credit_account,
                        // 'is_ledes_billing' => $preferenceSetting->is_ledes_billing,
                        'request_funds_preferences_default_msg' => $preferenceSetting->request_funds_preferences_default_msg,
                    ];
                    if($preferenceSetting->reminderSchedule) {
                        foreach($preferenceSetting->reminderSchedule as $key => $item) {
                            $jsonData['reminder'][] = [
                                'remind_type' => $item->remind_type,
                                'days' => $item->days,
                                'is_reminded' => "no",
                            ];
                        }
                    }
                }
                if($customizationSetting) {
                    $jsonData['invoice_theme'] = $customizationSetting->invoice_theme;
                    $jsonData['show_case_no_after_case_name'] = $customizationSetting->show_case_no_after_case_name;
                    $jsonData['non_billable_time_entries_and_expenses'] = $customizationSetting->non_billable_time_entries_and_expenses;
                    if($customizationSetting->flatFeeColumn) {
                        $jsonData['flat_fee'] = getColumnsIfYes($customizationSetting->flatFeeColumn->toArray());
                    }
                    if($customizationSetting->timeEntryColumn) {
                        $jsonData['time_entry'] = getColumnsIfYes($customizationSetting->timeEntryColumn->toArray());
                    }
                    if($customizationSetting->expenseColumn) {
                        $jsonData['expense'] = getColumnsIfYes($customizationSetting->expenseColumn->toArray());
                    }
                }
                $InvoiceSave->invoice_setting = $jsonData;
                $InvoiceSave->save();
                $totalInvoice[]=$InvoiceSave->id;
                }
            }

            $InvoiceBatch=new InvoiceBatch;
            if(!empty($totalInvoice)){
                $InvoiceBatch->invoice_id=implode(",",$totalInvoice);
            }
            $InvoiceBatch->total_invoice=count($totalInvoice);
            $InvoiceBatch->draft_invoice=$totalDraft;
            $InvoiceBatch->unsent_invoice=$totalUnsent;
            $InvoiceBatch->sent_invoice=$totalSent;
            $InvoiceBatch->batch_code=date('M, d Y',strtotime(convertUTCToUserTime(date("Y-m-d H:i:s", strtotime($request->batch['invoice_date'])), auth()->user()->user_tiezone ?? 'UTC')))."-".count($totalInvoice);
            $InvoiceBatch->firm_id=Auth::User()->firm_name; 
            $InvoiceBatch->created_by=Auth::User()->id; 
            $InvoiceBatch->save();
            dbCommit();
            return response()->json(['errors'=>'','countInvoice'=>count($totalInvoice), 'batchLink'=> route('bills/invoices').'?type=all&global_search='. base64_encode($InvoiceBatch->id).'-'.$InvoiceBatch->decode_type]);
            exit; 
            } catch (Exception $e) {
                dbEnd();
                return response()->json(['errors' => $e->getMessage()]);
            } 

        }
    }

    public function loadDepositIntoCredit(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'logged_in_user' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            // $user_id=$request->logged_in_user;
            // $userData = User::find($user_id);
            // if(!empty($userData)){
                
                // $CaseMasterClient = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as contact_name'),"id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
                $CaseMasterClient = firmClientList();
                // $CaseMasterCompany = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as contact_name'),"id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
                $CaseMasterCompany = firmCompanyList();
                /* if($request->case_id) {
                    $authUser = auth()->user();
                    $CaseMasterClient = User::whereHas("clientCases", function($query) use($request) {
                        $query->where("case_master.id", $request->case_id);
                    })->select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')->where("firm_name", $authUser->firm_name)
                    ->where('user_level', 2)->whereIn("user_status", [1,2])->get();

                    $CaseMasterCompany = User::whereHas("clientCases", function($query) use($request) {
                        $query->where("case_master.id", $request->case_id);
                    })->select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')
                    ->where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4)->get();
                } */
                return view('billing.dashboard.loadDepositIntoCredit',compact('CaseMasterClient','CaseMasterCompany'));
                exit;  
            // }else{
            //     return response()->json(['errors'=>'error']);
            // }
        }
         
    } 
    public function depositIntoNonTrustPopup(Request $request)
    {
       $validator = \Validator::make($request->all(), [
            'id' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user_id=$request->id;
            $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"credit_account_balance","users.id as uid","users.user_level")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$user_id)->first();

            if(!empty($userData)){
                $firmData=Firm::find(Auth::User()->firm_name);
                $fundRequestList = RequestedFund::where("client_id", $user_id)->where('deposit_into_type', 'credit')->where("amount_due",">",0)->get();
                return view('billing.dashboard.depositNonTrustFundPopup',compact('userData','fundRequestList','request'));
                exit;  
            }else{
                return response()->json(['errors'=>'error']);
            }
        }
    }

    public function saveDepositIntoNonTrustPopup(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        if(isset($request->applied_to) && $request->applied_to != 0) {
            $requestData = RequestedFund::find($request->applied_to);
            $finalAmt = $requestData->amount_requested - $requestData->amount_paid;
    
            $validator = \Validator::make($request->all(), [
                'payment_method' => 'required',
               'amount' => 'required|numeric|min:1|max:'.$finalAmt,
            ],[
                'amount.min'=>"Amount must be greater than $0.00",
                'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
            ]);
        } else {
            $validator = \Validator::make($request->all(), [
                'payment_method' => 'required',
                'amount' => 'required|numeric|min:1',
                'non_trust_account' => 'required'
            ],[
                'amount.min'=>"Amount must be greater than $0.00"
            ]);
        }

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            dbStart();
            if(isset($request->applied_to) && $request->applied_to!=0){
                $refundRequest = RequestedFund::find($request->applied_to);
                $refundRequest->fill([
                    'amount_due' => ($refundRequest->amount_due - $request->amount),
                    'amount_paid' => ($refundRequest->amount_paid + $request->amount),
                    'payment_date' => date('Y-m-d'),
                    'status' => 'partial',
                ])->save();
            }
            $userAddInfo = UsersAdditionalInfo::where("user_id", $request->non_trust_account)->first();

            $DepositIntoCreditHistory=new DepositIntoCreditHistory;
            $DepositIntoCreditHistory->user_id=$request->non_trust_account;
            $DepositIntoCreditHistory->deposit_amount=$request->amount;
            $DepositIntoCreditHistory->payment_method = $request->payment_method;
            $DepositIntoCreditHistory->payment_date = convertDateToUTCzone(date("Y-m-d", strtotime($request->payment_date)), auth()->user()->user_timezone);
            $DepositIntoCreditHistory->notes = $request->notes;
            $DepositIntoCreditHistory->total_balance = ($userAddInfo->credit_account_balance + $request->amount);
            $DepositIntoCreditHistory->payment_type = "deposit";
            $DepositIntoCreditHistory->firm_id=Auth::User()->firm_name;                
            $DepositIntoCreditHistory->related_to_fund_request_id = @$refundRequest->id;                
            $DepositIntoCreditHistory->created_by=Auth::User()->id;                
            $DepositIntoCreditHistory->created_at=date('Y-m-d H:i:s');                
            $DepositIntoCreditHistory->save();

            // Deposit into credit account
            if($userAddInfo) {
                $userAddInfo->fill([
                    "credit_account_balance" => ($userAddInfo->credit_account_balance + $request->amount),
                ])->save();
            }
            // For update next/previous credit balance
            $this->updateNextPreviousCreditBalance($request->non_trust_account);

            $request->request->add(["payment_type" => 'deposit']);
            $request->request->add(["contact_id" => $DepositIntoCreditHistory->user_id]);
            $request->request->add(["trust_history_id" => $DepositIntoCreditHistory->id]);
            $request->request->add(["applied_to" => @$refundRequest->id]);
            $this->updateClientPaymentActivity($request);

            $data=[];
            $data['deposit_id']=$DepositIntoCreditHistory->id;
            $data['deposit_for']=$DepositIntoCreditHistory->user_id;
            $data['user_id']=$DepositIntoCreditHistory->created_by;
            $data['client_id']=$DepositIntoCreditHistory->user_id;
            $data['activity']='accepted a deposit into credit of $'.$DepositIntoCreditHistory->deposit_amount.' ('.ucfirst($DepositIntoCreditHistory->payment_method).') for';
            $data['type']='credit';
            $data['action']='add';
            if(isset($request->applied_to) && $request->applied_to!=0){
                $data['deposit_id']=$request->applied_to;
                $data['activity']="accepted a payment of $".number_format($request->amount,2)." (".$request->payment_method.") for deposit request";
                $data['type']='fundrequest';
                $data['action']='pay';
            }
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            dbCommit();
            $firmData=Firm::find(Auth::User()->firm_name);
            $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
            // all good
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }
    /********************** Dashboard ******************/
    
    public function recordPayment(Request $request)
    {
        
       $validator = \Validator::make($request->all(), [
        //    'id' => 'required|min:1|max:255',
       ]);
       if ($validator->fails())
       {
           return response()->json(['errors'=>$validator->errors()->all()]);
       }else{
          $activityData=[];
          
         $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
         ->leftJoin("case_master","invoices.case_id","=","case_master.id")
         ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid")
         ->where("invoices.created_by",Auth::user()->id)
         ->where("invoices.status","!=","Paid");
      
         $Invoices=$Invoices->get();

           return view('billing.dashboard.loadInvoices',compact('activityData','Invoices'));     
           exit;    
       }
    }
    public function depositIntoTrust(Request $request)
    {
        
       $validator = \Validator::make($request->all(), [
        //    'id' => 'required|min:1|max:255',
       ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $activityData=[];
            /* $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
            ->leftJoin("case_master","invoices.case_id","=","case_master.id")
            ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","users.user_level","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid")
            ->where("invoices.created_by",Auth::user()->id)
            ->where("invoices.status","!=","Paid")
            ->groupBy("users.id");
        
            $Invoices=$Invoices->get(); */
            $ClientList = firmClientList();
            $CompanyList = firmCompanyList();
            if($request->case_id) {
                $authUser = auth()->user();
                $ClientList = User::whereHas("clientCases", function($query) use($request) {
                    $query->where("case_master.id", $request->case_id);
                })->select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')->where("firm_name", $authUser->firm_name)
                ->where('user_level', 2)->whereIn("user_status", [1,2])->get();

                $CompanyList = User::whereHas("clientCases", function($query) use($request) {
                    $query->where("case_master.id", $request->case_id);
                })->select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')
                ->where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4)->get();
            }

           return view('billing.dashboard.depositIntoTrust',compact('ClientList', 'CompanyList', 'activityData'/* ,'Invoices' */));     
           exit;    
       }
    }  

    /**
     * Get selected client case list with allocated trust balance
     */
    public function depositIntoTrustClientCase(Request $request)
    {
        // return $request->all();
        $user = User::whereId($request->user_id)->select("id", 'first_name', 'last_name','user_level', 'user_type')->first()->makeHidden(['caselist','createdby']);
        if($user) {
            $userAddInfo = UsersAdditionalInfo::where('user_id', $request->user_id)->select("trust_account_balance")->first();
            $allocatedTrustBalance = CaseClientSelection::where("selected_user", $request->user_id)->whereNull("deleted_at")->sum("allocated_trust_balance");
            $unallocatedTrustBalance = (@$userAddInfo->trust_account_balance - $allocatedTrustBalance) ?? 0.00;
            if($user->user_level == 5) {
                $result = LeadAdditionalInfo::where('user_id', $request->user_id)->select("user_id", "allocated_trust_balance", "potential_case_title")->get();
                $is_lead_case = 'yes';
            } else {
                $result = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")
                    ->where("case_client_selection.selected_user", $request->user_id)->whereNull("case_client_selection.deleted_at");
                if($request->case_id) {
                    $result = $result->where("case_client_selection.case_id", $request->case_id);
                }
                $result = $result->select("case_master.id", "case_master.case_title", "case_master.total_allocated_trust_balance","case_client_selection.allocated_trust_balance")
                            ->get()->makeHidden(['caseuser', 'upcoming_event', 'upcoming_tasks']);
                $is_lead_case = 'no';
            }            
            return response()->json(['errors' => '', 'result' => $result, 'user' => $user, 'is_lead_case' => $is_lead_case, /* 'userAddInfo' => $userAddInfo, */ 'unallocatedTrustBalance' => $unallocatedTrustBalance]);
        } else {
            return response()->json(['errors' => "User not found"]);
        }
    }

    /* public function depositIntoTrustByCase(Request $request)
    {
        
       $validator = \Validator::make($request->all(), [
        //    'id' => 'required|min:1|max:255',
       ]);
       if ($validator->fails())
       {
           return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $activityData=[];
            $caseClient = User::leftJoin("case_client_selection","case_client_selection.selected_user","=","users.id")
            ->select("case_client_selection.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","users.user_level")
            ->where("case_client_selection.case_id",$request->case_id);
            $caseClient=$caseClient->get();
            return view('billing.dashboard.depositIntoTrustByCase',compact('caseClient'));     
            exit;    
       }
    }   */

    public function depositIntoTrustPopup(Request $request)
    {
       $validator = \Validator::make($request->all(), [
            'id' => 'required|min:1|max:255',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user_id=$request->id;
            $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid","users.user_level")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$user_id)->first();

            if(!empty($userData)){
                $firmData=Firm::find(Auth::User()->firm_name);
                $fundRequestList = RequestedFund::select('requested_fund.*')->where("client_id",$user_id)->where("deposit_into_type", "trust");
                if($request->case_id != '' && $request->case_id != 0) {
                    $fundRequestList = $fundRequestList->where("allocated_to_case_id",$request->case_id);
                }
                $fundRequestList = $fundRequestList->where("amount_due",">",0)->get();
                $case = CaseMaster::whereId($request->case_id)->select('id', 'case_title', 'total_allocated_trust_balance')->first();
                return view('billing.dashboard.depositTrustFundPopup',compact('userData','fundRequestList', 'case','request'));
                exit;  
            }else{
                checkLeadInfoExists($user_id);
                return response()->json(['errors'=>'error']);
            }
        }
    }
    public function saveDepositIntoTrustPopup(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        if(isset($request->applied_to) && $request->applied_to!=0){
            $requestData=RequestedFund::find($request->applied_to);
            $amount_requested=$requestData['amount_requested'];
            $amount_due=$requestData['amount_due'];
            $amount_paid=$requestData['amount_paid'];
            $finalAmt=$amount_requested-$amount_paid;
    
            $validator = \Validator::make($request->all(), [
                'payment_method' => 'required',
               'amount' => 'required|numeric|min:1|max:'.$finalAmt,
                'trust_account' => 'required'
            ],[
                'amount.min'=>"Amount must be greater than $0.00",
                'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
            ]);
        }else{
            $validator = \Validator::make($request->all(), [
                'payment_method' => 'required',
                'trust_account' => 'required'
            ]);
        }
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }
        dbStart();
        try {
            if(isset($request->applied_to) && $request->applied_to!=0){
                $refundRequest=RequestedFund::find($request->applied_to);
                $refundRequest->amount_due=($refundRequest->amount_due-$request->amount);
                $refundRequest->amount_paid=($refundRequest->amount_paid+$request->amount);
                $refundRequest->payment_date=date('Y-m-d');
                $refundRequest->status='partial';
                $refundRequest->save();

                if($refundRequest->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $refundRequest->allocated_to_lead_case_id)->increment('allocated_trust_balance', $request->amount);
                }
            }

            //Deposit into trust account
            DB::table('users_additional_info')->where('user_id',$request->trust_account)->increment('trust_account_balance', $request['amount']);

            $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->trust_account)->first();
            
            $TrustInvoice=new TrustHistory;
            $TrustInvoice->client_id=$request->trust_account;
            $TrustInvoice->payment_method=$request->payment_method;
            $TrustInvoice->amount_paid=$request->amount;
            $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
            $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime($request->payment_date)), auth()->user()->user_timezone);
            $TrustInvoice->notes=$request->notes;
            $TrustInvoice->fund_type='diposit';
            $TrustInvoice->related_to_fund_request_id = @$refundRequest->id;
            $TrustInvoice->created_by=Auth::user()->id; 
            $TrustInvoice->allocated_to_case_id = $request->case_id;
            $TrustInvoice->allocated_to_lead_case_id = @$refundRequest->allocated_to_lead_case_id;
            $TrustInvoice->save();

            // For allocated case trust balance
            if($request->case_id != '') {
                CaseMaster::where('id', $request->case_id)->increment('total_allocated_trust_balance', $request['amount']);
                CaseClientSelection::where('case_id', $request->case_id)->where('selected_user', $request->trust_account)->increment('allocated_trust_balance', $request['amount']);
            }

            $this->updateNextPreviousTrustBalance($TrustInvoice->client_id);

            // Account activity
            $request->request->add(["payment_type" => 'deposit']);
            $request->request->add(["trust_history_id" => $TrustInvoice->id]);
            $this->updateTrustAccountActivity($request);

             $data=[];
            $data['user_id']=$request->trust_account;
            $data['client_id']=$request->trust_account;
            $data['deposit_for']=$request->trust_account;
            $data['activity']="accepted a deposit into trust of $".number_format($request->amount,2)." (".$request->payment_method.") for";
            $data['type']='deposit';
            $data['action']='add';
            if(isset($request->applied_to) && $request->applied_to!=0){
                $data['deposit_id']=$request->applied_to;
                $data['activity']="accepted a payment of $".number_format($request->amount,2)." (".$request->payment_method.") for deposit request";
                $data['type']='fundrequest';
                $data['action']='pay';
            }
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            
            $firmData=Firm::find(Auth::User()->firm_name);
            $msg="Thank you. Your deposit of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
            dbCommit();
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;  
        } catch (Exception $e) {
            dbEnd();
            return response()->json(['errors'=> $e->getMessage()]);
        }
    }
    
    public function loadAllHistory(Request $request)
    {
        $authUserNotificationSetting = auth()->user()->userNotificationSetting()->where('user_notification_settings.for_feed', 'yes');
        $authUserNotifyType = $authUserNotificationSetting->pluck('sub_type')->toArray();
        $authUserNotifyAction = $authUserNotificationSetting->whereIn('sub_type', ['invoices','time_entry'])->pluck('action')->toArray();
        // return $authUserNotificationSettingArr;

        $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
        ->leftJoin('users as u1','u1.id','=','all_history.client_id')
        ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->leftJoin('case_events','case_events.id','=','all_history.event_id')
        ->leftJoin('expense_entry','expense_entry.id','=','all_history.activity_for')
        ->leftJoin('task_time_entry','task_time_entry.id','=','all_history.time_entry_id')
        ->leftJoin('task','task.id','=','all_history.task_id')
        ->select("task_time_entry.deleted_at as timeEntry","expense_entry.id as ExpenseEntry","case_events.id as eventID", 
                "users.*","all_history.*","u1.user_level as ulevel","u1.user_title as utitle",
                DB::raw('CONCAT_WS(" ",u1.first_name,u1.last_name) as fullname'),
                "case_master.case_title","case_master.id","task_activity.title",
                "all_history.created_at as all_history_created_at",
                "case_master.case_unique_number", "case_events.event_title as eventTitle", "case_events.deleted_at as deleteEvents", "task.deleted_at as deleteTasks",'task.task_title as taskTitle')
        ->where('all_history.is_for_client','no')
        ->whereIn("all_history.type", ["invoices", "lead_invoice","time_entry","expenses","credit","deposit","fundrequest"])
        // ->whereIn("all_history.action", $authUserNotifyAction)
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        return view('billing.dashboard.AllHistory',compact('commentData'));
        exit;  
            
    }

    public function loadTimeEntryOverview(Request $request)
    {
        $FinalArray=[];
        $todayBillableTimeEntry=$todayNonBillableTimeEntry=$todayTotal=0;
        $thisBillableWeekTimeEntry=$thisNonBillableWeekTimeEntry=$weekTotal=0;
        $thisBillableMonthTimeEntry=$thisNonBillableMonthTimeEntry=$monthTotal=0;
        
        $getChildUsers=$this->getParentAndChildUserIds();
        
        //Todays Data
        $TodayTimeEntryData = TaskTimeEntry::select('task_time_entry.*')
        ->whereIn('created_by',$getChildUsers)
        ->where('entry_date',date('Y-m-d'))
        ->get();

        foreach($TodayTimeEntryData as $k1=>$v1){
            if($v1->time_entry_billable=="yes"){
                $todayBillableTimeEntry+=$v1->duration;
                if($v1->rate_type=='hr'){
                    $todayTotal+=$v1->duration * $v1->entry_rate;
                }else{
                    $todayTotal+=$v1->entry_rate;
                }
            }else{
                $todayNonBillableTimeEntry+=$v1->duration;
            }
        }
        $FinalArray['todayBillableTimeEntry']=$todayBillableTimeEntry;
        $FinalArray['todayNonBillableTimeEntry']=$todayNonBillableTimeEntry;
        $FinalArray['todayTotal']=$todayTotal;

        //This week data
        $startDate=date('Y-m-d',strtotime('this week'));
        $endDate=date('Y-m-d');
        $weekTimeEntryData = TaskTimeEntry::select('task_time_entry.*')
        ->whereIn('created_by',$getChildUsers)
        ->whereBetween('entry_date',[$startDate,$endDate])
        ->get();
        foreach($weekTimeEntryData as $k1=>$v1){
            if($v1->time_entry_billable=="yes"){
                $thisBillableWeekTimeEntry+=$v1->duration;
                if($v1->rate_type=='hr'){
                    $weekTotal+=$v1->duration * $v1->entry_rate;
                }else{
                    $weekTotal+=$v1->entry_rate;
                }
            }else{
                $thisNonBillableWeekTimeEntry+=$v1->duration;
            }
        }
        $FinalArray['thisBillableWeekTimeEntry']=$thisBillableWeekTimeEntry;
        $FinalArray['thisNonBillableWeekTimeEntry']=$thisNonBillableWeekTimeEntry;
        $FinalArray['weekTotal']=$weekTotal;

         //This Month data
        $startDate=date('Y-m-d',strtotime("first day of this month"));
         $endDate=date('Y-m-d');
        $monthTimeEntryData = TaskTimeEntry::select('task_time_entry.*')
        ->whereIn('created_by',$getChildUsers)
        ->whereBetween('entry_date',[$startDate,$endDate])
        ->get();
        foreach($monthTimeEntryData as $k1=>$v1){
            if($v1->time_entry_billable=="yes"){
                $thisBillableMonthTimeEntry+=$v1->duration;
                if($v1->rate_type=='hr'){
                    $monthTotal+=$v1->duration * $v1->entry_rate;
                }else{
                    $monthTotal+=$v1->entry_rate;
                }
            }else{
                $thisNonBillableMonthTimeEntry+=$v1->duration;
            }
        }
        $FinalArray['thisBillableMonthTimeEntry']=$thisBillableMonthTimeEntry;
        $FinalArray['thisNonBillableMonthTimeEntry']=$thisNonBillableMonthTimeEntry;
        $FinalArray['monthTotal']=$monthTotal;

        return view('billing.dashboard.loadTimeEntryOverview',compact('FinalArray'));
        exit;  
            
    }

    public function loadInvoiceOverview(Request $request)
    {
        $id=Auth::User()->id;
        if($request->fulldate==""){
            $startDate=date('Y-m-d',strtotime("first day of this month"));
            $endDate=date('Y-m-d');
        }else{
            $cutDate=explode("-",$request->fulldate);
            $startDate=date('Y-m-d',strtotime($cutDate[0]));
            $endDate=date('Y-m-d',strtotime($cutDate[1]));
        }
        $InvoicesPaidAmount = Invoices::where("invoices.created_by",$id)->where("invoices.status","Paid")->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("paid_amount");
           
        $InvoicesPaidPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status","Partial")->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("paid_amount");

        $InvoicesSentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Sent')->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("total_amount");

        $InvoicesDraftAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Draft')->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("total_amount");

        $InvoicesUnsentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Unsent')->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("total_amount");

        $InvoicesPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Partial')->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("paid_amount");
        
        $InvoicesOverdueAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Overdue')->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("due_amount");
      
        
        return view('billing.dashboard.InvoiceOverview',compact('InvoicesPaidAmount','InvoicesPaidPartialAmount','InvoicesSentAmount','InvoicesDraftAmount','InvoicesUnsentAmount','InvoicesPartialAmount','InvoicesOverdueAmount'));
        exit;  
            
    }
    public function loadTrustAccountOverview(Request $request)
    {
        $id=Auth::User()->id;
        
        $startDate=date('Y-m-d',strtotime("first day of this month"));
        $endDate=date('Y-m-d');
        
        $AccountActivityCredited=AccountActivity::select("credit_amount")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->whereBetween('entry_date',[$startDate,$endDate])->sum('credit_amount');
        $AccountActivityDebited=AccountActivity::select("debit_amount")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->whereBetween('entry_date',[$startDate,$endDate])->sum('debit_amount');
        
        return view('billing.dashboard.loadTrustAccountOverview',compact('AccountActivityCredited','AccountActivityDebited'));
        exit;  
            
    }

    public function loadCalender(Request $request)
    {
        //This Month data
        $getChildUsers=$this->getParentAndChildUserIds();

        $FinalArra=[];$monthTotal=$monthHours=0;
        $startDate=date('Y-m-d',strtotime("first day of this month"));
        $endDate=date('Y-m-d');
        $monthTimeEntryData = TaskTimeEntry::select('task_time_entry.*')
        ->whereIn('created_by',$getChildUsers)
        ->whereBetween('entry_date',[$startDate,$endDate])
        ->where('deleted_at',NULL)
        ->get();
        foreach($monthTimeEntryData as $k1=>$v1){
            if($v1->time_entry_billable=="yes"){
                if($v1->rate_type=='hr'){
                    $monthTotal+=$v1->duration * $v1->entry_rate;
                }else{
                    $monthTotal+=$v1->entry_rate;
                }
                $monthHours+=$v1->duration;
            }
        }
        $FinalArray['monthTotal']=$monthTotal;

        
       
        $startDate=date('Y-m-d',strtotime("first day of this month"));
        $endDate=date('Y-m-d',strtotime("last day of this month"));

        
        $monthTimeEntryDataForCalander = DB::table('task_time_entry')
        ->select(
            DB::raw('SUM(duration) as durationsum'),'entry_date'
        );
        if($request->type=="all"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereIn("time_entry_billable",["yes","no"]);
        }else if($request->type=="nb"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","no");
        }else if($request->type=="b"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","yes");
        }

        if(isset($request->forUser) && $request->forUser!="0"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("user_id",$request->forUser);
        }

        $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereBetween('entry_date',[$startDate,$endDate])
        ->groupBy('entry_date')
        ->get();  
        
        $CalenderArray=[];
        foreach($monthTimeEntryDataForCalander as $k=>$v){
            $CalenderArray[$k]['title']=number_format($v->durationsum,1);
            $CalenderArray[$k]['start']=$v->entry_date;
            $CalenderArray[$k]['end']=$v->entry_date;
        }
        $monthlyHours=[];
        if($request->type=="all" || $request->type=="b"){
            $monthlyHours = TaskTimeEntry::select('task_time_entry')
            ->whereIn('created_by',$getChildUsers)
            ->whereBetween('entry_date',[$startDate,$endDate])
            ->where("time_entry_billable","yes")
            ->sum("duration");
        }

        $type=$request->type;

        if(isset($request->from))
        {
            return view('billing.timesheet.loadCalender',compact('monthTimeEntryDataForCalander','FinalArray','CalenderArray','monthlyHours','type'));
        }else{
            return view('billing.dashboard.loadCalender',compact('monthTimeEntryDataForCalander','FinalArray','CalenderArray','monthlyHours','type'));
        }
        exit;  
            
    }

    public function loadDataOnly(Request $request)
    {
        //This Month data
        $getChildUsers=$this->getParentAndChildUserIds();
        $startDate=date('Y-m-d',strtotime($request->start));
        $endDate=date('Y-m-d',strtotime($request->end));
        $monthTimeEntryDataForCalander = DB::table('task_time_entry')
        ->select(
            DB::raw('SUM(duration) as durationsum'),'entry_date'
        );
        if($request->type=="all"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereIn("time_entry_billable",["yes","no"]);
        }else if($request->type=="nb"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","no");
        }else if($request->type=="b"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","yes");
        }
        if(isset($request->forUser) && $request->forUser!="0"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("user_id",$request->forUser);
        }

        $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereBetween('entry_date',[$startDate,$endDate])
        ->where('deleted_at',NULL)
        ->groupBy('entry_date')
        ->get();  
        
        $CalenderArray=[];
        $SetGoal=Auth::User()->set_goal;
        $SetFrequency=Auth::User()->goal_frequency;

        foreach($monthTimeEntryDataForCalander as $k=>$v){
            $entry_date=convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($v->entry_date)),Auth::User()->user_timezone);
            $CalenderArray[$k]['title']=number_format($v->durationsum,1);
            $CalenderArray[$k]['start']=date('Y-m-d',strtotime($entry_date));
            $CalenderArray[$k]['end']=date('Y-m-d',strtotime($entry_date));
            if($SetFrequency=="daily"){
                if($SetGoal > $v->durationsum ){
                    $CalenderArray[$k]['color']='#ff7e00';
                }else{
                    $CalenderArray[$k]['color']='#28a745';
                }
            }
        }
        return response()->json(['errors'=>'','CalenderArray'=>$CalenderArray]);
        exit;              
    }

    public function loadSummary(Request $request)
    {
        $getChildUsers=$this->getParentAndChildUserIds();

        $FinalArra=[];$monthTotal=$monthHours=0;
        $startDate=date('Y-m-d',strtotime($request->start));
        $endDate=date('Y-m-d',strtotime($request->end));
        $monthTimeEntryData = TaskTimeEntry::select('task_time_entry.*');
        // ->whereIn('created_by',$getChildUsers)
        if($request->forUser!=0){
            $monthTimeEntryData=$monthTimeEntryData->where('user_id',$request->forUser);
        }
        $monthTimeEntryData=$monthTimeEntryData->whereBetween('entry_date',[$startDate,$endDate])
        ->get();
        foreach($monthTimeEntryData as $k1=>$v1){
            if($v1->time_entry_billable=="yes"){
                if($v1->rate_type=='hr'){
                    $monthTotal+=$v1->duration * $v1->entry_rate;
                }else{
                    $monthTotal+=$v1->entry_rate;
                }
                $monthHours+=$v1->duration;
            }
        }
        $FinalArray['monthTotal']=$monthTotal;

        
        
        $monthTimeEntryDataForCalander = DB::table('task_time_entry')
        ->select(
            DB::raw('SUM(duration) as durationsum'),'entry_date'
        );
        if($request->type=="all"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereIn("time_entry_billable",["yes","no"]);
        }else if($request->type=="nb"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","no");
        }else if($request->type=="b"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","yes");
        }
        $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereBetween('entry_date',[$startDate,$endDate]);
        if($request->forUser!=0){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where('user_id',$request->forUser);
        }
        $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->groupBy('entry_date')
        ->get();  
        
        $CalenderArray=[];
        foreach($monthTimeEntryDataForCalander as $k=>$v){
            $CalenderArray[$k]['title']=number_format($v->durationsum,1);
            $CalenderArray[$k]['start']=$v->entry_date;
            $CalenderArray[$k]['end']=$v->entry_date;
        }
        $monthlyHours=0;
        if($request->type=="all" || $request->type=="b"){
            $monthlyHours = TaskTimeEntry::select('task_time_entry')
            ->whereIn('created_by',$getChildUsers)
            ->whereBetween('entry_date',[$startDate,$endDate]);
            if($request->forUser!=0){
                $monthlyHours=$monthlyHours->where('user_id',$request->forUser);
            }
            if($request->type=="all"){
                $monthlyHours=$monthlyHours->whereIn("time_entry_billable",["yes","no"]);
            }else{
                $monthlyHours=$monthlyHours->where("time_entry_billable","yes");
            }
            $monthlyHours=$monthlyHours->sum("duration");
        }

        $type=$request->type;
        if(isset($request->from))
        {
            return view('billing.timesheet.loadSummary',compact('monthTimeEntryDataForCalander','FinalArray','CalenderArray','monthlyHours','type'));
        }else{
            return view('billing.dashboard.loadSummary',compact('monthTimeEntryDataForCalander','FinalArray','CalenderArray','monthlyHours','type'));
        }
        exit;  
            
    }
    public function saveDailyGoal(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'hours_field' => 'required|numeric',
            'duration_field' => 'required|in:daily,monthly,weekly',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $UserSave=User::find(Auth::User()->id);
            $UserSave->set_goal=$request->hours_field;
            $UserSave->goal_frequency=$request->duration_field;
            $UserSave->save();
            return response()->json(['errors'=>'']);
            exit;    
        }
    }

    public function deleteGoalEntry(Request $request)
    {
       
        $UserSave=User::find(Auth::User()->id);
        $UserSave->set_goal=0.00;
        $UserSave->goal_frequency=NULL;
        $UserSave->save();
        return response()->json(['errors'=>'']);
        exit;    
    
    }
    /********************** Dashboard ******************/

    /********************** Timesheet ******************/
    public function viewTimesheet(Request $request)
    {
        //This Month data
        $getChildUsers=$this->getParentAndChildUserIds();
        $startDate=date('Y-m-d',strtotime($request->start));
        $endDate=date('Y-m-d',strtotime($request->end));
        $monthTimeEntryDataForCalander = DB::table('task_time_entry')
        ->select(
            DB::raw('SUM(duration) as durationsum'),'entry_date'
        );
        if($request->type=="all"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereIn("time_entry_billable",["yes","no"]);
        }else if($request->type=="nb"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","no");
        }else if($request->type=="b"){
            $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","yes");
        }
        $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereBetween('entry_date',[$startDate,$endDate])
        ->where('deleted_at',NULL)
        ->groupBy('entry_date')

        ->get();  
        
        $CalenderArray=[];
        $SetGoal=Auth::User()->set_goal;
        $SetFrequency=Auth::User()->goal_frequency;

        foreach($monthTimeEntryDataForCalander as $k=>$v){
            $CalenderArray[$k]['title']=number_format($v->durationsum,1);
            $CalenderArray[$k]['start']=$v->entry_date;
            $CalenderArray[$k]['end']=$v->entry_date;
            if($SetFrequency=="daily"){
                if($SetGoal > $v->durationsum ){
                    $CalenderArray[$k]['color']='#ff7e00';
                }else{
                    $CalenderArray[$k]['color']='#28a745';
                }
            }
        }
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();

        $type=$request->type;
        return view('billing.timesheet.viewTimesheet',compact('monthTimeEntryDataForCalander','CalenderArray','type','loadFirmStaff'));
        exit;  
    }
    public function loadAllSavedTimeEntry(Request $request)
    {
        $curDate=$request->currentDate;
        $dateText=date('l F d,Y',strtotime($request->currentDate));
        $entryDate=date('Y-m-d',strtotime($request->currentDate));

        $TaskTimeEntry =TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
        ->select('task_time_entry.*',"task_activity.title as activity_title","case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,"case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid");
        if($request->forUser!=0){
            $TaskTimeEntry =$TaskTimeEntry->where("user_id",$request->forUser);
        }
        $TaskTimeEntry =$TaskTimeEntry->where('entry_date',$entryDate)
        ->get();  
        $TimeEntryArrayByUser=[];
        
        
        foreach($TaskTimeEntry as $k=>$v){
            $TimeEntryArrayByUser[$v->uid][]=$v;
        }
        $nameAndTotals=[];
        foreach($TimeEntryArrayByUser as $k=>$v){
            $total=$masterHours=$MasterTotal=0;
            foreach($v as $vv=>$kk){
                if($kk['rate_type']=="hr"){
                    $masterHours+=$kk['duration'];
                    $total=$kk['duration']*$kk['entry_rate'];
                }else{
                    $total=$kk['entry_rate'];
                }
                if($kk['time_entry_billable']=="yes"){
                    $MasterTotal+=$total;
                }
                $nameAndTotals[$kk['uid']]['name']=$kk['user_name'];
                $nameAndTotals[$kk['uid']]['totalAmt']=$MasterTotal;
                $nameAndTotals[$kk['uid']]['totalHrs']=$masterHours;

            }
        }
     
        return view('billing.timesheet.loadTimeEntryPopup',compact('dateText','TaskTimeEntry','TimeEntryArrayByUser','nameAndTotals','curDate'));
        exit;
            
    }
    public function reloadTimeEntry(Request $request)
    {
        $curDate=$request->curdate;
       
        $entryDateMatch=date('Y-m-d',strtotime($request->curdate));
        if($request->type=="next"){
            $curDate=$entryDate=date('Y-m-d', strtotime('+1 day', strtotime($entryDateMatch)));
        }else{
            $curDate=$entryDate=date('Y-m-d', strtotime('-1 day', strtotime($entryDateMatch)));
        }
        $dateText=date('l F d,Y',strtotime($curDate));
        $TaskTimeEntry =TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
        ->select('task_time_entry.*',"task_activity.title as activity_title","case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,"case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid");
        if($request->forUser!=0){
            $TaskTimeEntry =$TaskTimeEntry->where("user_id",$request->forUser);
        }
        $TaskTimeEntry =$TaskTimeEntry->where('entry_date',$entryDate)
        ->get();  
        $TimeEntryArrayByUser=[];
        
        
        foreach($TaskTimeEntry as $k=>$v){
            $TimeEntryArrayByUser[$v->uid][]=$v;
        }
        $nameAndTotals=[];
        foreach($TimeEntryArrayByUser as $k=>$v){
            $total=$masterHours=$MasterTotal=0;
            foreach($v as $vv=>$kk){
                if($kk['rate_type']=="hr"){
                    $masterHours+=$kk['duration'];
                    $total=$kk['duration']*$kk['entry_rate'];
                }else{
                    $total=$kk['entry_rate'];
                }
                if($kk['time_entry_billable']=="yes"){
                    $MasterTotal+=$total;
                }
                $nameAndTotals[$kk['uid']]['name']=$kk['user_name'];
                $nameAndTotals[$kk['uid']]['totalAmt']=$MasterTotal;
                $nameAndTotals[$kk['uid']]['totalHrs']=$masterHours;

            }
        }
     
        return view('billing.timesheet.loadTimeEntryPopup',compact('dateText','TaskTimeEntry','TimeEntryArrayByUser','nameAndTotals','curDate'));
        exit;
            
    }
    /********************** Timesheet ******************/


     /********************** Payment Plans ******************/
     public function paymentPlans(Request $request)
     {
      
         //This Month data
         $getChildUsers=$this->getParentAndChildUserIds();
         $startDate=date('Y-m-d',strtotime($request->start));
         $endDate=date('Y-m-d',strtotime($request->end));
         $monthTimeEntryDataForCalander = DB::table('task_time_entry')
         ->select(
             DB::raw('SUM(duration) as durationsum'),'entry_date'
         );
         if($request->type=="all"){
             $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereIn("time_entry_billable",["yes","no"]);
         }else if($request->type=="nb"){
             $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","no");
         }else if($request->type=="b"){
             $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->where("time_entry_billable","yes");
         }
         $monthTimeEntryDataForCalander=$monthTimeEntryDataForCalander->whereBetween('entry_date',[$startDate,$endDate])
         ->where('deleted_at',NULL)
         ->groupBy('entry_date')
 
         ->get();  
         
         $CalenderArray=[];
         $SetGoal=Auth::User()->set_goal;
         $SetFrequency=Auth::User()->goal_frequency;
 
         foreach($monthTimeEntryDataForCalander as $k=>$v){
             $CalenderArray[$k]['title']=number_format($v->durationsum,1);
             $CalenderArray[$k]['start']=$v->entry_date;
             $CalenderArray[$k]['end']=$v->entry_date;
             if($SetFrequency=="daily"){
                 if($SetGoal > $v->durationsum ){
                     $CalenderArray[$k]['color']='#ff7e00';
                 }else{
                     $CalenderArray[$k]['color']='#28a745';
                 }
             }
         }
        //  $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        
        $loadFirmStaff = firmUserList();
 
         $caseList = InvoiceInstallment::leftJoin("invoices","invoices.id","=","invoice_installment.invoice_id")
        ->leftJoin("case_master","case_master.id","=","invoices.case_id")
        ->select("*")
        ->where("invoice_installment.firm_id",Auth::User()->firm_name)
        ->groupBy("case_id")
        ->get();

         return view('billing.payment_plan.payment_plan',compact('monthTimeEntryDataForCalander','CalenderArray','loadFirmStaff','caseList'));
         exit;  
     }

     public function loadPlannedPayment(Request $request)
    {
        $startDate=date('Y-m-d');
        $endDate=date('Y-m-d', strtotime("+30 days"));

        $ManualInvoiceInstallment=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
        ->whereBetween('due_date',[$startDate,$endDate])
        ->where("status","unpaid")
        ->sum('installment_amount');

        $AutoInvoiceInstallment=0;
        
        $totalAmount=$ManualInvoiceInstallment+$AutoInvoiceInstallment;
       
        $isEmpty=0;
        if($totalAmount>0){
            $isEmpty=1;
            $autoPayPercentage=number_format($AutoInvoiceInstallment/$totalAmount*100,2);
            $manualPayPercentage=number_format($ManualInvoiceInstallment/$totalAmount*100,2);
        }else{
            $autoPayPercentage=$manualPayPercentage=0;
        }

        return view('billing.payment_plan.loadPlannedPayment',compact('ManualInvoiceInstallment','AutoInvoiceInstallment','totalAmount','autoPayPercentage','manualPayPercentage','isEmpty'));
        exit;  
            
    }

    public function loadAveragePlannedPayment(Request $request)
    {
        $startDate=date('Y-m-d', strtotime("-90 days"));
        $endDate=date('Y-m-d');
        if($request->payType=="all"){
            $status=["auto","manual"];
        }else if($request->payType=="automatic_payments"){
            $status=["auto"];
        }else if($request->payType=="manual_payment"){
            $status=["manual"];
        }
        $ManualInvoiceInstallment1=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
        ->whereBetween('due_date',[$startDate,$endDate])
        ->where("status","paid")
        ->whereIn("pay_type",$status);

        $totalSum=$ManualInvoiceInstallment1->sum('installment_amount');
        $totalInstalment=$ManualInvoiceInstallment1->count('id');
        if($totalInstalment>0){
            $AverageAmount=$totalSum/$totalInstalment;
        }else{
            $AverageAmount=0;
        }

        return view('billing.payment_plan.loadAveragePlannedPayment',compact('AverageAmount','totalSum','totalInstalment'));
        exit;  
            
    }

    public function PaymentInstallmentsOverTime(Request $request)
    {
        $date =date('Y-m-d', strtotime("-5 months"));
        $end_date =date('Y-m-d', strtotime("+6 months"));

        $collectedPaymentDataArray=$plannedPaymentDataArray=$manualPaymentDataArray=$AutomaticPaymentDataArray=[];
        while (strtotime($date) <= strtotime($end_date)) {
            $FDay=date("Y-m-1", strtotime($date));
            $EDay=date("Y-m-t", strtotime($date));

            //CollectedPaymentBar
            $collectedPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
            ->whereBetween('due_date',[$FDay,$EDay])
            ->where("status","paid")
            ->whereIn("pay_type",["auto","manual"]);
            $totalSum=$collectedPaymentData->sum('installment_amount');
            $totalInstalment=$collectedPaymentData->count('id');
            $collectedPaymentDataArray[]=array(
                'x'=>date("1 M Y", strtotime($date)),
                'y'=> $totalSum,
                'month'=>date("M Y", strtotime($date)),
                'title1' => $this->getTotalSummaryData($FDay,$EDay),
                'title2' => $this->getAutopaySummaryData($FDay,$EDay),
                'title3' => $this->getManualSummaryData($FDay,$EDay)
            );

             //Planned Payment Bar
             $plannedPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
             ->whereBetween('due_date',[$FDay,$EDay])
             ->where("status","unpaid")
             ->whereIn("pay_type",["auto","manual"]);
             $totalSum2=$plannedPaymentData->sum('installment_amount');
             $totalInstalment2=$plannedPaymentData->count('id');
             $plannedPaymentDataArray[]=array(
                 'x'=>date("1 M Y", strtotime($date)),
                 'y'=> $totalSum2,
                 'month'=>date("M Y", strtotime($date)),
                  'title1' => $this->getTotalSummaryData($FDay,$EDay),
                 'title2' => $this->getAutopaySummaryData($FDay,$EDay),
                 'title3' => $this->getManualSummaryData($FDay,$EDay)
             );

              //Manual Payment line
              $manualPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
              ->whereBetween('due_date',[$FDay,$EDay])
              ->where("status","unpaid")
              ->whereIn("pay_type",["manual"]);
              $totalSum2=$manualPaymentData->sum('installment_amount');
              $totalInstalment2=$manualPaymentData->count('id');
              $manualPaymentDataArray[]=array(
                  'x'=>date("1 M Y", strtotime($date)),
                  'y'=> $totalSum2,
                  'month'=>date("M Y", strtotime($date)),
                   'title1' => $this->getTotalSummaryData($FDay,$EDay),
                  'title2' => $this->getAutopaySummaryData($FDay,$EDay),
                  'title3' => $this->getManualSummaryData($FDay,$EDay)
              );

            //Automatic Payment line
            $automaticPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
            ->whereBetween('due_date',[$FDay,$EDay])
            ->where("status","unpaid")
            ->whereIn("pay_type",["auto"]);
            $totalSum2=$automaticPaymentData->sum('installment_amount');
            $totalInstalment2=$automaticPaymentData->count('id');
            $automaticPaymentDataArray[]=array(
                'x'=>date("1 M Y", strtotime($date)),
                'y'=> $totalSum2,
                'month'=>date("M Y", strtotime($date)),
                'title1' => $this->getTotalSummaryData($FDay,$EDay),
                'title2' => $this->getAutopaySummaryData($FDay,$EDay),
                'title3' => $this->getManualSummaryData($FDay,$EDay)
            );
              
            $date = date ("Y-m-d", strtotime("+1 month", strtotime($date)));
        }
        
        return view('billing.payment_plan.PaymentInstallmentsOverTime',compact('collectedPaymentDataArray','plannedPaymentDataArray','manualPaymentDataArray','automaticPaymentDataArray'));
        exit;  
            
    }

    public function getTotalSummaryData($sdate,$edate){
        $collectedPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
        ->whereBetween('due_date',[$sdate,$edate])
        ->whereIn("pay_type",["auto","manual"]);
        $totalSum=$collectedPaymentData->sum('installment_amount');
        $totalInstalment=$collectedPaymentData->count('id');

        return 'Total: '.$totalInstalment ." Plans <br>$".number_format($totalSum,2);
    }

    public function getAutopaySummaryData($sdate,$edate){
        $collectedPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
        ->whereBetween('due_date',[$sdate,$edate])
        ->whereIn("pay_type",["auto"]);
        $totalSum=$collectedPaymentData->sum('installment_amount');
        $totalInstalment=$collectedPaymentData->count('id');

        return 'AutoPay: '.$totalInstalment ." Plans <br>$".number_format($totalSum,2);
    }
    
    public function getManualSummaryData($sdate,$edate){
        $collectedPaymentData=InvoiceInstallment::where("firm_id",Auth::User()->firm_name)
        ->whereBetween('due_date',[$sdate,$edate])
        ->whereIn("pay_type",["manual"]);
        $totalSum=$collectedPaymentData->sum('installment_amount');
        $totalInstalment=$collectedPaymentData->count('id');

        return 'Manual: '.$totalInstalment ." Plans <br> $".number_format($totalSum,2);
    }
    public function loadAllPlans()
    {   
        $columns = array('invoice_installment.id','invoice_id',"contact_name","case_title","total_amt","total_paid","total_due","completed","id","next_payment_on","next_payment_amount","final_date");
        $requestData= $_REQUEST;
        
        $case = InvoiceInstallment::leftJoin("invoices","invoices.id","=","invoice_installment.invoice_id")
        ->leftJoin("case_master","case_master.id","=","invoices.case_id")
        ->leftJoin("users","invoices.user_id","=","users.id")
        ->select('invoices.*',"invoice_installment.*","case_master.*","users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as contact_name'),"users.id as uid",DB::raw('(SELECT SUM(installment_amount) FROM invoice_installment WHERE invoice_installment.invoice_id = invoices.id AND invoice_installment.status="paid") as paid_balance'),DB::raw('(SELECT SUM(installment_amount) FROM invoice_installment WHERE invoice_installment.invoice_id = invoices.id) as total_balance'),DB::raw('(SELECT SUM(installment_amount) FROM invoice_installment WHERE invoice_installment.invoice_id = invoices.id AND invoice_installment.status="unpaid" ) as due_balance'),DB::raw('(SELECT SUM(installment_amount) FROM invoice_installment WHERE invoice_installment.invoice_id = invoices.id AND invoice_installment.status="unpaid" ) as due_balance'));
       
        if(isset($requestData['court_case']) && $requestData['court_case']!=''){
            $case = $case->where("case_master.id",$requestData['court_case']);
        }
        
        if(isset($requestData['payment_type']) && $requestData['payment_type']!=''){
            if( $requestData['payment_type']=="on"){
                $case = $case->where("invoice_installment.pay_type",'auto');
            }else{
                $case = $case->where("invoice_installment.pay_type",'manual');
            }
        }
        // if(isset($requestData['from']) && $requestData['from']!='' && isset($requestData['to']) && $requestData['to']!=''){
        //     $case = $case->whereBetween("task_time_entry.entry_date",[date('Y-m-d',strtotime($requestData['from'])),date('Y-m-d',strtotime($requestData['to']))]);
        // }
        
        // if(isset($requestData['type']) && $requestData['type']=='own'){
        //     $case = $case->where("task_time_entry.user_id",Auth::User()->id);
        // }
        // if(isset($requestData['st']) && $requestData['st']!=''){
        //     $case = $case->where("task_activity.title",'like', '%' . $requestData['st'] . '%');
        //     $case = $case->orWhere("task_time_entry.description",'like', '%' . $requestData['st'] . '%');
        // }

        // if(isset($requestData['i']) && $requestData['i']=='i'){
        //     $case = $case->where("task_time_entry.invoice_link","!=",NULL);
        // }else if(isset($requestData['i']) && $requestData['i']=='o'){
        //     $case = $case->where("task_time_entry.invoice_link",NULL);
        // }
        $case = $case->groupBy("invoice_installment.invoice_id");
        $totalData=$case->count();
        $totalFiltered = $totalData; 

        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        // $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $case = $case->get();
        if( $requestData['order'][0]['dir']=="desc"){
            $case = $case->sortByDesc($columns[$requestData['order'][0]['column']])->values();
        }else{
            $case = $case->sortBy($columns[$requestData['order'][0]['column']])->values();
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $case 
        );
        echo json_encode($json_data);  
    }

    //Installment managment (Maintain paid and unpaid status base on pay amount)
    // MOved this function to CreditAccountTrait
    /* public function installmentManagement($paidAmt,$invoice_id){
        $invoice_installment=InvoiceInstallment::where("invoice_id",$invoice_id)->where("status","unpaid")->orderBy("due_date","ASC")->get();
        $arrayGrid=array();
        foreach($invoice_installment as $k=>$v){
            $arrayGrid[$k]['id']=$v->id;
            $arrayGrid[$k]['installment_amt']=$v->installment_amount;
            $arrayGrid[$k]['total_paid_amt']=$v->adjustment;
            $arrayGrid[$k]['now_pay']=$arrayGrid[$k]['installment_amt']-$arrayGrid[$k]['total_paid_amt'];
            if($arrayGrid[$k]['now_pay']>=$paidAmt){
                $arrayGrid[$k]['actual_pay_amt']=$paidAmt;
            }else{
                $arrayGrid[$k]['actual_pay_amt']=$arrayGrid[$k]['now_pay'];
            }
            $arrayGrid[$k]['available_bal']=$paidAmt-$arrayGrid[$k]['now_pay'];
            $paidAmt-=$arrayGrid[$k]['now_pay'];
        }
        foreach($arrayGrid as $G=>$H){
            if($H['actual_pay_amt']>=0){
                DB::table('invoice_installment')->where("id",$H['id'])->update([
                    'paid_date'=>date('Y-m-d h:i:s'),
                    'adjustment'=>DB::raw('adjustment + ' . $H['actual_pay_amt'])
                ]);  
                $invoice_installment=InvoiceInstallment::find($H['id']);
                if($invoice_installment['installment_amount']==$invoice_installment['adjustment']){
                    $invoice_installment->status="paid";   
                }
                $invoice_installment->save();
            }
        }
    } */
    
     /********************** Payment Plans ******************/

     public function downloadBulkInvoice(Request $request)
     {
         $request->merge([
             'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
         ]);
         $validator = \Validator::make($request->all(), [
             'invoice_id' => 'required|json',
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
             $data = json_decode(stripslashes($request->invoice_id));
                $pdfData=[];
                foreach($data as $k1=>$v1){
                     $Invoice=Invoices::find($v1);
                     $invoice_id=$Invoice['id'];
                     $pdfData[$invoice_id]['Invoice']=$Invoice;

                     $userData = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
                    $pdfData[$invoice_id]['userData']=$userData;

                    $caseMaster=CaseMaster::find($Invoice['case_id']);
                    $pdfData[$invoice_id]['caseMaster']=$caseMaster;

                    //Getting firm related data
                    $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.is_primary","yes")->where("firm_address.firm_id",$userData['firm_name'])->first();
                    $pdfData[$invoice_id]['firmAddress']=$firmAddress;

            
                    $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();
                    $pdfData[$invoice_id]['TimeEntryForInvoice']=$TimeEntryForInvoice;

                    $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();
                    $pdfData[$invoice_id]['ExpenseForInvoice']=$ExpenseForInvoice;

                     //Get the flat fee Entry list
                    $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
                    ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
                    ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
                    ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
                    ->get();
                    $pdfData[$invoice_id]['FlatFeeEntryForInvoice']=$FlatFeeEntryForInvoice;

                    $firmData=Firm::find($userData['firm_name']);
                    $pdfData[$invoice_id]['firmData']=$firmData;

                    //Get the Adjustment list
                    $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();                    
                    $pdfData[$invoice_id]['InvoiceAdjustment']=$InvoiceAdjustment;

            
                    $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
                    $pdfData[$invoice_id]['InvoiceHistory']=$InvoiceHistory;

                    $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
                    $pdfData[$invoice_id]['InvoiceInstallment']=$InvoiceInstallment;

                    $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();
                    $pdfData[$invoice_id]['InvoiceHistoryTransaction']=$InvoiceHistoryTransaction;

            
                }

                $filename="Invoices_".time().'.pdf';
                 $PDFData=view('billing.invoices.viewBulkInvoicePdf',compact('pdfData'));
                // $pdf = new Pdf;
                // if($_SERVER['SERVER_NAME']=='localhost'){
                //     $pdf->binary = EXE_PATH;
                // }
                // $pdf->addPage($PDFData);
                // $pdf->setOptions(['javascript-delay' => 5000]);
                // $pdf->saveAs(public_path("download/pdf/".$filename));
                // $path = public_path("download/pdf/".$filename);
                $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
                return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
                exit;
            
         } 
     }
     public function downloaInvoivePdf1(Request $request)
     {
         
         $invoice_id=base64_decode($request->id);
         $Invoice=Invoices::where("id",$invoice_id)->first();
         $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
        
         $caseMaster=CaseMaster::find($Invoice['case_id']);
         //Getting firm related data
         $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
         
 
         $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();
 
         $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();
         $firmData=Firm::find($userData['firm_name']);
 
         //Get the Adjustment list
         $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->where("invoice_adjustment.amount",">",0)->get();
 
         $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
 
         $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
         $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();
 
         $filename="Invoice_".$invoice_id.'.pdf';
         $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistory','InvoiceInstallment','InvoiceHistoryTransaction'));
        //  $pdf = new Pdf;
        //  if($_SERVER['SERVER_NAME']=='localhost'){
        //      $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        //  }
        //  $pdf->addPage($PDFData);
        //  $pdf->setOptions(['javascript-delay' => 5000]);
        //  $pdf->saveAs(public_path("download/pdf/".$filename));
        //  $path = public_path("download/pdf/".$filename);
         // return response()->download($path);
         // exit;
         $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
         return response()->json([ 'success' => true, "url"=>$pdfUrl,"file_name"=>$filename], 200);
         exit;
     }
     public function printView(Request $request)
     {
        $file=$request->path;
        return view('print.index',compact('file'));
        exit;  
     }

     public function loadCaseList(Request $request)
    {
        $case_id=$request->case_id;
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        // $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        return view('billing.loadCaseList', compact('CaseMasterData','case_id'));
       
    }
    /**
     * Get invoice payment history
     */
    public function invoicePaymentHistory(Request $request)
    {
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id", $request->id)->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC")->get();
        return view("billing.invoices.partials.load_invoice_payment_history", ["InvoiceHistoryTransaction" => $InvoiceHistoryTransaction])->render();
    }

    /**
     * save non billable flat fee/time entry/expense
     */
    public function saveNonbillableCheck(Request $request)
    {
        // return $request->all();
        if($request->check_type == "time") {
            $checkEntry = TaskTimeEntry::whereId($request->id)->first();
            if(!empty($checkEntry)){                              
                if($checkEntry->rate_type == 'flat'){
                    $totalTime = str_replace(",","",$checkEntry->duration);                    
                }else{
                    $totalTime = str_replace(",","",$checkEntry->entry_rate) * str_replace(",","",$checkEntry->duration);
                } 
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $checkEntry->case_id)->where('token',$request->token_id)->get();  
                if(count($InvoiceAdjustment) > 0){
                    foreach($InvoiceAdjustment as $k=>$v){
                        if($v->applied_to == 'sub_total' || $v->applied_to == 'time_entries'){
                            if($request->is_check == 'no'){ 
                                $invoiceAdjustTotal = $v->basis - $totalTime;
                            }else{
                                $invoiceAdjustTotal = $v->basis + $totalTime;
                            }
                            $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                        
                            if($invoiceAmount <= 0){
                                InvoiceAdjustment::where("id",$v->id)->delete();
                            }else{                             
                                InvoiceAdjustment::where("id",$v->id)->update([
                                    'basis' => $invoiceAdjustTotal,
                                    'amount'=> $invoiceAmount
                                ]);
                            }
                        }
                    }
                }                
            }
        } else if($request->check_type == "flat") {
            $checkEntry = FlatFeeEntry::whereId($request->id)->first();
            if(!empty($checkEntry)){                              
                $totalTime = str_replace(",","",$checkEntry->cost); 
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $checkEntry->case_id)->where('token',$request->token_id)->get();  
                if(count($InvoiceAdjustment) > 0){
                    foreach($InvoiceAdjustment as $k=>$v){
                        if($v->applied_to == 'sub_total' || $v->applied_to == 'flat_fees'){
                            if($request->is_check == 'no'){ 
                                $invoiceAdjustTotal = $v->basis - $totalTime;
                            }else{
                                $invoiceAdjustTotal = $v->basis + $totalTime;
                            }
                            $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                            if($invoiceAmount <= 0){
                                InvoiceAdjustment::where("id",$v->id)->delete();
                            }else{                             
                                InvoiceAdjustment::where("id",$v->id)->update([
                                    'basis' => $invoiceAdjustTotal,
                                    'amount'=> $invoiceAmount
                                ]);
                            }
                        }
                    }
                }                
            }
        } else if($request->check_type == "expense") {
            $checkEntry = ExpenseEntry::whereId($request->id)->first();
            if(!empty($checkEntry)){             
                $totalTime = str_replace(",","",$checkEntry->cost) * str_replace(",","",$checkEntry->duration);           
                $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $checkEntry->case_id)->where('token',$request->token_id)->get();  
                if(count($InvoiceAdjustment) > 0){
                    foreach($InvoiceAdjustment as $k=>$v){
                        if($v->applied_to == 'sub_total' || $v->applied_to == 'expenses'){
                            if($request->is_check == 'no'){ 
                                $invoiceAdjustTotal = $v->basis - $totalTime;
                            }else{
                                $invoiceAdjustTotal = $v->basis + $totalTime;
                            }
                            $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                            if($invoiceAmount <= 0){
                                InvoiceAdjustment::where("id",$v->id)->delete();
                            }else{                             
                                InvoiceAdjustment::where("id",$v->id)->update([
                                    'basis' => $invoiceAdjustTotal,
                                    'amount'=> $invoiceAmount
                                ]);
                            }
                        }
                    }
                }                
            }
        } else {
            $checkEntry = "";
        }
        if($checkEntry) {
            $checkEntry->update(["time_entry_billable" => $request->is_check]);
        } else {
            return response()->json(["status" => "error", 'msg' => "No record found"]);
        }
        return response()->json(['status' => "success", 'msg' => "Record updated", "totalTime" => $totalTime]);
    }

    public function saveforwardInvoiceCheck(Request $request){
        $totalTime = str_replace(",","",$request->due); 
        $InvoiceAdjustment = InvoiceAdjustment::where('ad_type','percentage')->where('case_id', $request->case_id)->where('token',$request->token_id)->get();  
        if(count($InvoiceAdjustment) > 0){
            foreach($InvoiceAdjustment as $k=>$v){
                if($v->applied_to == 'balance_forward_total'){
                    if($request->is_check == 'no'){ 
                        $invoiceAdjustTotal = $v->basis - $totalTime;
                    }else{
                        $invoiceAdjustTotal = $v->basis + $totalTime;
                    }
                    $invoiceAmount = ($invoiceAdjustTotal * $v->percentages ) / 100; 
                    if($invoiceAmount <= 0){
                        InvoiceAdjustment::where("id",$v->id)->delete();
                    }else{                             
                        InvoiceAdjustment::where("id",$v->id)->update([
                            'basis' => $invoiceAdjustTotal,
                            'amount'=> $invoiceAmount
                        ]);
                    }
                }
            }
            if($request->page == 'edit'){
                if($request->is_check == 'no'){
                    DB::statement("DELETE FROM `invoice_forwarded_invoices` WHERE `forwarded_invoice_id` = '".$request->id."' LIMIT 1;");
                }else{               
                    DB::statement("INSERT INTO `invoice_forwarded_invoices` (`invoice_id`, `forwarded_invoice_id`) values ('".base64_decode($request->token_id) ."', '".$request->id."')");
                }
            }
            return response()->json(['status' => "success", 'msg' => "Record updated", "InvoiceAdjustment" => count($InvoiceAdjustment)]);
        } 
        return response()->json(['status' => "no adjustment record"]);
    }

    /**
     * Get invoice activity history
     */
    public function invoiceActivityHistory(Request $request)
    {
        $InvoiceHistory=InvoiceHistory::where("invoice_id",$request->id)->orderBy("id","DESC")->get();
        $lastEntry= $InvoiceHistory->first();
        return view("billing.invoices.partials.load_invoice_activity_history", ["InvoiceHistory" => $InvoiceHistory, 'lastEntry' => $lastEntry])->render();
    }

    /**
     * Get invoice account history
     */
    public function invoiceAccountHistory(Request $request)
    {
        $findInvoice=Invoices::whereId($request->id)->with(["forwardedInvoices", "applyCreditFund", "applyTrustFund"])->first();
        $caseMaster=CaseMaster::whereId($findInvoice->case_id)->with("caseAllClient", "caseAllClient.userAdditionalInfo", "caseAllClient.userTrustAccountHistory")->first();
        return view("billing.invoices.partials.load_invoice_account_summary", ["findInvoice" => $findInvoice, "caseMaster" => $caseMaster])->render();
    }

    /**
     * Invoice record payment from credit account
     */
    public function saveInvoicePaymentFromCredit(Request $request)
    {
        // $invoiceId=Invoices::where("invoice_unique_token",$request->invoice_id)->first();
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($request->invoice_id);
        $finalAmt = $InvoiceData['total_amount'] - $InvoiceData['paid_amount'];
        $userAddInfo = UsersAdditionalInfo::where("user_id", $request->credit_account)->first();

        $validator = \Validator::make($request->all(), [
            'credit_account' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt.'|lte:'.$userAddInfo['credit_account_balance'],
            'invoice_id' => 'required'
        ],[
            'amount.min'=>"Amount must be greater than $0.00",
            'amount.lte'=>"Account does not have sufficient funds",
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            dbStart();
            try {
                $authUser = auth()->user();
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","credit")->orderBy("created_at","DESC")->first();
                
                $InvoicePayment = InvoicePayment::create([
                    'invoice_id' => $InvoiceData->id,
                    'payment_from' => 'credit',
                    'amount_paid' => @$request->amount ?? 0,
                    'payment_date' => convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    'notes' => $request->notes,
                    'status' => "0",
                    'entry_type' => "0",
                    'payment_from_id' => $request->credit_account,
                    'deposit_into' => "Operating Account",
                    'total' => (@$currentBalance['total'] ?? 0 + $request->amount),
                    'firm_id' => $authUser->firm_name,
                    'created_by' => $authUser->id,
                ]);
                $InvoicePayment->fill(['ip_unique_id' => Hash::make($InvoicePayment->id)])->save();
            
                //Deduct invoice amount when payment done
                $this->updateInvoiceAmount($InvoiceData->id);

                // Deduct amount from credit account after payment.
                if($userAddInfo) {
                    $userAddInfo->fill([
                        'credit_account_balance' => ($userAddInfo->credit_account_balance) ? $userAddInfo->credit_account_balance - $request->amount ?? 00 : $userAddInfo->credit_account_balance
                    ])->save();
                }

                //Code For installment amount
                $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$InvoiceData->id)->first();
                if(!empty($getInstallMentIfOn)){
                    $this->installmentManagement($request->amount, $InvoiceData->id);
                }
                    
                // Add credit history
                DepositIntoCreditHistory::create([
                    "user_id" => $request->credit_account,
                    "payment_method" => "payment",
                    "deposit_amount" => $request->amount ?? 0,
                    "payment_date" => convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone),
                    "payment_type" => "payment",
                    "total_balance" => $userAddInfo->credit_account_balance,
                    "related_to_invoice_id" => $InvoiceData->id,
                    "created_by" => $authUser->id,
                    "firm_id" => $authUser->firm_name,
                    "related_to_invoice_payment_id" => $InvoicePayment->id,
                ]);

                dbCommit();
                //Response message
                $firmData=Firm::find($authUser->firm_name);
                $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                // all good


                $invoiceHistory=[];
                $invoiceHistory['invoice_id']=$InvoiceData->id;
                $invoiceHistory['acrtivity_title']='Payment Received';
                $invoiceHistory['pay_method']='Credit';
                $invoiceHistory['amount']=$request->amount;
                $invoiceHistory['responsible_user']=Auth::User()->id;
                $invoiceHistory['payment_from']='credit';
                $invoiceHistory['deposit_into']='Operating Account';
                $invoiceHistory['deposit_into_id']=($request->credit_account)??NULL;
                $invoiceHistory['invoice_payment_id']=$InvoicePayment->id;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $newHistoryId = $this->invoiceHistory($invoiceHistory);

                 //Add Invoice history
                 $data=[];
                 $data['case_id']=$InvoiceData['case_id'];
                 $data['user_id']=$request->credit_account;
                 $data['activity']='accepted a payment of $'.number_format($request->amount,2).' (Non-Trust Credit Account)';
                 $data['activity_for']=$InvoiceData['id'];
                 $data['type']='invoices';
                 $data['action']='pay';
                 $CommonController= new CommonController();
                 $CommonController->addMultipleHistory($data);
                
                return response()->json(['errors'=>'','msg'=>$msg]);
                exit;  

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors'=> [$e->getMessage()]]); //$e->getMessage()
                 exit;   
            } 
        }
    }

    /**
     * Apply credit balance from invoice list
     */
    public function applyCreditBalanceForm(Request $request)
    {
        // return $request->all();
        $request->merge([
            'invoice_id' => ($request->invoice_id!="[]") ? $request->invoice_id : NULL
        ]);
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required|json',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data = json_decode(stripslashes($request->invoice_id));
            $notSavedInvoice=$savedInvoice=[];
            $authUser = auth()->user();
            foreach($data as $k1=>$v1){
                dbStart();
                $Invoices=Invoices::find($v1);
                $invoice_id=$Invoices['id'];
                $finalAmt = $Invoices['total_amount'] - $Invoices['paid_amount'];

                $caseClientCount = 1;
                if($Invoices->is_lead_invoice == 'no') {
                    $caseClientCount = CaseClientSelection::where("case_id", $Invoices->case_id)->count();
                }
                // $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"credit_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$Invoices['user_id'])->first();
                $userData = UsersAdditionalInfo::where('user_id', $Invoices['user_id'])->first();
                //Get the credit account balance and invoice due amount
                if($userData['credit_account_balance']>0 && $caseClientCount == 1 && $Invoices->status != "Forwarded" && $Invoices->status != "Paid")
                {
                    if($finalAmt >= $userData['credit_account_balance'] ){
                        $finalAmt = $userData['credit_account_balance'];
                    }
                    //Insert invoice payment record.
                    $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","credit")->orderBy("created_at","DESC")->first();
                
                    $InvoicePayment = InvoicePayment::create([
                        'invoice_id' => $Invoices->id,
                        'payment_from' => 'credit',
                        'amount_paid' => @$finalAmt ?? 0,
                        'payment_date' => convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                        'notes' => $request->notes,
                        'status' => "0",
                        'entry_type' => "0",
                        'payment_from_id' => $Invoices['user_id'],
                        'deposit_into' => "Operating Account",
                        'total' => (@$currentBalance['total'] ?? 0 + $finalAmt),
                        'firm_id' => $authUser->firm_name,
                        'created_by' => $authUser->id,
                    ]);
                    $InvoicePayment->fill(['ip_unique_id' => Hash::make($InvoicePayment->id)])->save();
                
                    //Deduct invoice amount when payment done
                    $this->updateInvoiceAmount($Invoices->id);

                    // Deduct amount from credit account after payment.
                    UsersAdditionalInfo::where('user_id',$userData['user_id'])->decrement('credit_account_balance', $finalAmt);
                    $userData->refresh();
                        
                    // Add credit history
                    DepositIntoCreditHistory::create([
                        "user_id" => $userData['user_id'],
                        "payment_method" => "payment",
                        "deposit_amount" => $finalAmt ?? 0,
                        "payment_date" => convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                        "payment_type" => "payment",
                        "total_balance" => @$userData->credit_account_balance,
                        "related_to_invoice_id" => $Invoices->id,
                        "created_by" => $authUser->id,
                        "firm_id" => $authUser->firm_name,
                        "related_to_invoice_payment_id" => $InvoicePayment->id,
                    ]);

                    dbCommit();

                    $invoiceHistory=[];
                    $invoiceHistory['invoice_id']=$invoice_id;
                    $invoiceHistory['acrtivity_title']='Payment Received';
                    $invoiceHistory['pay_method']='Trust';
                    $invoiceHistory['amount']=$finalAmt;
                    $invoiceHistory['responsible_user']=Auth::User()->id;
                    $invoiceHistory['deposit_into']='Operating Account';
                    $invoiceHistory['deposit_into_id']=($userData['user_id'])??NULL;
                    $invoiceHistory['invoice_payment_id']=$InvoicePayment->id;
                    $invoiceHistory['notes']=NULL;
                    $invoiceHistory['status']="1";
                    $invoiceHistory['created_by']=Auth::User()->id;
                    $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                    $this->invoiceHistory($invoiceHistory);


                    //Add Invoice history
                    $InvoiceData=Invoices::find($invoice_id);
                    $data=[];
                    $data['case_id']=$InvoiceData['case_id'];
                    $data['user_id']=$InvoiceData['user_id'];
                    $data['activity']='accepted a payment of $'.number_format($finalAmt,2).' (Credit)';
                    $data['activity_for']=$InvoiceData['id'];
                    $data['type']='invoices';
                    $data['action']='pay';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                    $savedInvoice[]=$invoice_id;
                }else{
                    $notSavedInvoice[]=$invoice_id;
                }
            }
            return response()->json(['errors'=>'','savedInvoice'=>$savedInvoice,'notSavedInvoice'=>$notSavedInvoice, 'fund_type' => 'credit']);
            exit;  
        } 
    }

    /**
     * Appled credit balance from invoice list response 
     */
    public function creditBalanceResponse(Request $request)
    {
        $id=Auth::user()->id;
         $user = User::find($id);
         $savedInvoice=[];
        if(!empty($user)){
            $appliedInvoice= (isset($request->response['savedInvoice'])) ? $request->response['savedInvoice'] : [];
            $nonappliedInvoice= (isset($request->response['notSavedInvoice'])) ? $request->response['notSavedInvoice'] : [];

            $SavedInvoices = Invoices::whereIn("id",$appliedInvoice)->with("case", "portalAccessUserAdditionalInfo", "leadAdditionalInfo")->get();
            $NonSavedInvoices = Invoices::whereIn("id",$nonappliedInvoice)->with("case", "portalAccessUserAdditionalInfo", "leadAdditionalInfo")->get();
           
            // $NonSavedInvoices=Invoices::select("case_master.case_title","invoices.id")->whereIn("invoices.id",$nonappliedInvoice);
            // $NonSavedInvoices=$NonSavedInvoices->leftJoin("case_master","case_master.id","=","invoices.case_id");
            // $NonSavedInvoices=$NonSavedInvoices->get();
            $fund_type = $request->fund_type ?? 'credit';
            return view('billing.invoices.trustBalanceAppliedResult',compact('SavedInvoices','NonSavedInvoices', 'fund_type'));
        }else{
            return view('pages.404');
        }
    }

    /**
     * INvoice online payment from lawyer portal, Get credit/debit card detail and do payment, cash payment and bank transfer payment 
     */
    public function payOnlinePayment(Request $request)
    {   
        // return $request->all();
        DB::beginTransaction();
        try {
            $firmOnlinePaymentSetting = getFirmOnlinePaymentSetting();
            \Conekta\Conekta::setApiKey($firmOnlinePaymentSetting->private_key);
            $client = User::whereId($request->client_id)->first();
            if($client) {
                if(empty($client->conekta_customer_id)) {
                    $customer = \Conekta\Customer::create([
                                    "name"=> $client->full_name,
                                    "email"=> $client->email,
                                    // "phone"=> $request->phone_number ?? $client->mobile_number,
                                    "phone"=> "55-5555-5555",
                                ]);
                    $client->fill(['conekta_customer_id' => $customer->id])->save();
                    $client->refresh();
                }
                $msg = "Las indicaciones para hacer el pago se han enviado correctamente al correo de su cliente. En cuanto su cliente haga el pago se lo haremos saber por correo y se verá reflejado en el sistema.";
                if($request->online_payment_method == "credit-card") {
                    $this->cardPayment($request, $client);
                    $msg = "Se ha recibido correctamente el pago de ".number_format($request->amount,2)." pesos con Tarjeta de débito o crédito.";
                } else if($request->online_payment_method == "cash") {
                    $this->cashPayment($request, $client);
                } else if($request->online_payment_method == "bank-transfer") {
                    $this->bankPayment($request, $client);
                } else {}
                // $firmData=Firm::find($client->firm_name);
                // $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
                return response()->json(['errors'=>'', 'msg' => $msg]);
            }
        } catch (\Conekta\AuthenticationError $e){
            DB::rollback();
            return response()->json(['errors'=> $e->getMessage()]);
        } catch (\Conekta\ApiError $e){
            DB::rollback();
            return response()->json(['errors'=> $e->getMessage()]);
        } catch (\Conekta\ProcessingError $e){
            DB::rollback();
            return response()->json(['errors'=> $e->getMessage()]);
        } catch (\Conekta\ParameterValidationError $e){
            DB::rollback();
            return response()->json(['errors'=> $e->getMessage()]);
        } catch (\Conekta\Handler $e){
            DB::rollback();
            return response()->json(['errors'=> $e->getMessage()]);
        } catch (Exception $e){
            DB::rollback();
            return response()->json(['errors'=> $e->getMessage()]);
        }
    }

    /**
     * Card payment
     */
    public function cardPayment($request, $client)
    {
        $customerId = $client->conekta_customer_id;
        $payableAmount = $request->amount;
        $validOrderWithCharge = [
            'line_items' => [
                [
                    'name' => ucfirst($request->type).' number '.$request->payable_record_id,
                    'unit_price' => (int)$payableAmount * 100,
                    'quantity' => 1,
                ]
            ],
            'customer_info' => array(
                'customer_id' => $customerId
            ),
            'currency'    => 'MXN',
            'metadata'    => array('payment' => 'Invoice/FundRequest cash payment')
        ];
        if($request->emi_month == 0) {
            $validOrderWithCharge['charges'] = [
                [
                    'payment_method' => [
                        'type'       => 'card',
                        'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000",
                        'token_id' => $request->conekta_token_id,
                    ],
                    'amount' => (int)$payableAmount * 100,
                ]
            ];
        } else {
            $validOrderWithCharge['charges'] = [
                [
                    'payment_method' => [
                        'type'       => 'card',
                        'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000",
                        'token_id' => $request->conekta_token_id,
                        'monthly_installments' => (int)$request->emi_month
                    ],
                    'amount' => (int)$payableAmount * 100,
                ]
            ];
        }
        $authUser = auth()->user();
        if($request->type == 'fundrequest') {
            $fundRequest = RequestedFund::whereId($request->payable_record_id)->where('status', '!=', 'paid')->first();
            if($fundRequest && $fundRequest->status != 'paid') {
                $order = \Conekta\Order::create($validOrderWithCharge);
                if($order->payment_status == 'paid') {
                    $requestOnlinePayment = RequestedFundOnlinePayment::create([
                        'fund_request_id' => $fundRequest->id,
                        'user_id' => $client->id,
                        'payment_method' => 'card',
                        'amount' => $payableAmount,
                        'card_emi_month' => $request->emi_month ?? 0,
                        'conekta_order_id' => $order->id,
                        'conekta_charge_id' => $order->charges[0]->id ?? Null,
                        'conekta_customer_id' => $customerId,
                        'conekta_payment_status' => $order->payment_status,
                        'created_by' => auth()->id(),
                        'firm_id' => $client->firm_name,
                        'conekta_order_object' => $order,
                        'status' => 'deposit',
                    ]);

                    // Update fund request paid/due amount and status
                    $remainAmt = $fundRequest->amount_due - $payableAmount;
                    $fundRequest->fill([
                        'amount_due' => $remainAmt,
                        'amount_paid' => ($fundRequest->amount_paid + $payableAmount),
                        'payment_date' => date('Y-m-d'),
                        'status' => ($remainAmt == 0) ? 'paid' : 'partial',
                        'online_payment_status' => 'paid',
                    ])->save();

                    //Deposit into trust account
                    $userAdditionalInfo = UsersAdditionalInfo::select("trust_account_balance", "credit_account_balance")->where("user_id", $client->id)->first();
                    if($fundRequest->deposit_into_type == "trust") {
                        UsersAdditionalInfo::where("user_id", $client->id)->increment('trust_account_balance', $payableAmount);
                        $trustHistory = TrustHistory::create([
                            'client_id' => $client->id,
                            'payment_method' => 'card',
                            'amount_paid' => $payableAmount,
                            'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
                            'payment_date' => date('Y-m-d'),
                            'fund_type' => 'diposit',
                            'related_to_fund_request_id' => $fundRequest->id,
                            'allocated_to_case_id' => $fundRequest->allocated_to_case_id,
                            'created_by' => $client->id,
                            'online_payment_status' => $order->payment_status,
                        ]);
                        $requestOnlinePayment->fill(['trust_history_id' => $trustHistory->id])->save();

                        // For allocated case trust balance
                        if($fundRequest->allocated_to_case_id != '') {
                            CaseMaster::where('id', $fundRequest->allocated_to_case_id)->increment('total_allocated_trust_balance', $payableAmount);
                            CaseClientSelection::where('case_id', $fundRequest->allocated_to_case_id)->where('selected_user', $client->id)->increment('allocated_trust_balance', $payableAmount);
                        }
                        // For update next/previous trust balance
                        $this->updateNextPreviousTrustBalance($trustHistory->client_id);
                        
                        // Add fund request account activity
                        $AccountActivityData = AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
                        AccountActivity::create([
                            'user_id' => $client->id,
                            'case_id'=> $fundRequest->allocated_to_case_id ?? Null,
                            'credit_amount' => $payableAmount ?? 0.00,
                            'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                            'entry_date' => date('Y-m-d'),
                            'payment_method' => "Card",
                            'payment_type' => "deposit",
                            'pay_type' => "trust",
                            'from_pay' => "online",
                            'trust_history_id' => $trustHistory->id ?? Null,
                            'firm_id' => $client->firm_name,
                            'section' => "request",
                            'related_to' => $fundRequest->id,
                            'created_by'=>$client->id,
                        ]);

                    } else {
                        // Deposit into credit account
                        UsersAdditionalInfo::where("user_id", $client->id)->increment('credit_account_balance', $payableAmount);
                        $creditHistory = DepositIntoCreditHistory::create([
                            'user_id' => $client->id,
                            'deposit_amount' => $payableAmount,
                            'payment_method' => "card",
                            'payment_date' => date("Y-m-d"),
                            'total_balance' => @$userAdditionalInfo->credit_account_balance,
                            'payment_type' => "deposit",
                            'firm_id' => $client->firm_name,
                            'related_to_fund_request_id' => $fundRequest->id,
                            'created_by' => $client->id,
                            'online_payment_status' => $order->payment_status,
                        ]);
                        $requestOnlinePayment->fill(['credit_history_id' => $creditHistory->id])->save();

                        // For update next/previous credit balance
                        $this->updateNextPreviousCreditBalance($client->id);

                        // Add fund request account activity
                        $AccountActivityData=AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                        AccountActivity::create([
                            'user_id' => $client->id,
                            'credit_amount' => $payableAmount ?? 0.00,
                            'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                            'entry_date' => date('Y-m-d'),
                            'payment_method' => 'Card',
                            'payment_type' => "deposit",
                            'trust_history_id' => $creditHistory->id ?? Null,
                            'status' => "unsent",
                            'pay_type' => "client",
                            'from_pay' => "client",
                            'firm_id' => $client->firm_name,
                            'section' => "request",
                            'related_to' => $fundRequest->id,
                            'created_by' => $client->id,
                        ]);
                    }
                    
                    $data=[];
                    $data['user_id'] = $client->id;
                    $data['client_id'] = $client->id;
                    $data['deposit_for'] = $client->id;
                    $data['deposit_id']=$fundRequest->id;
                    $data['activity']="pay a payment of $".number_format($payableAmount, 2)." (Card) for deposit request";
                    $data['type']='fundrequest';
                    $data['action']='pay';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);

                    // For client activity
                    $data['activity'] = 'pay a payment of $'.number_format($payableAmount,2).' (Card) for fund request';
                    $data['is_for_client'] = 'yes';
                    $CommonController->addMultipleHistory($data);

                    // Send confirm email to client
                    $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $client, $emailTemplateId = 30, $requestOnlinePayment, 'client', 'fundrequest'));

                    // Send confirm email to lawyer/invoice created user
                    $user = User::whereId($fundRequest->created_by)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $user, $emailTemplateId = 31, $requestOnlinePayment, 'user', 'fundrequest'));
                    
                    // Send confirm email to firm owner/lead attorney
                    $firmOwner = User::where('firm_name', $client->firm_name)->where('parent_user', 0)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $firmOwner, $emailTemplateId = 31, $requestOnlinePayment, 'user', 'fundrequest'));

                    DB::commit();
                }
            }
        } else {
            $invoice = Invoices::whereId($request->payable_record_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
            if($invoice && !in_array($invoice->status, ['Paid','Forwarded'])) {                    
                $order = \Conekta\Order::create($validOrderWithCharge);
                if($order->payment_status == 'paid') {
                    $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                        'invoice_id' => $invoice->id,
                        'user_id' => $client->id,
                        'payment_method' => 'card',
                        'amount' => $payableAmount,
                        'card_emi_month' => $request->emi_month ?? 0,
                        'conekta_order_id' => $order->id,
                        'conekta_charge_id' => $order->charges[0]->id ?? Null,
                        'conekta_customer_id' => $customerId,
                        'conekta_payment_status' => $order->payment_status,
                        'created_by' => $authUser->id,
                        'firm_id' => $client->firm_name,
                        'conekta_order_object' => $order,
                    ]);

                    //Insert invoice payment record.
                    $InvoicePayment=InvoicePayment::create([
                        'invoice_id' => $invoice->id,
                        'payment_from' => 'online',
                        'amount_paid' => $payableAmount,
                        'payment_method' => 'Card',
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                        'status'=>"0",
                        'entry_type'=>"2",
                        'payment_from_id' => $client->id,
                        'firm_id' => $client->firm_name,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $authUser->id 
                    ]);

                    //Code For installment amount
                    $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$invoice->id)->first();
                    if(!empty($getInstallMentIfOn)){
                        $this->installmentManagement($payableAmount,$invoice->id, $onlinePaymentStatus = 'paid');
                    }

                    // Update invoice online payment status
                    $invoice->fill(['online_payment_status' => $order->payment_status])->save();

                    // Update invoice paid/due amount and status
                    $this->updateInvoiceAmount($invoice->id);

                    $invoiceHistory = InvoiceHistory::create([
                        'invoice_id' => $invoice->id,
                        'acrtivity_title' => 'Payment Received',
                        'pay_method' => 'Card',
                        'amount' => $payableAmount,
                        'responsible_user' => $authUser->id,
                        'payment_from' => 'online',
                        'invoice_payment_id' => $InvoicePayment->id,
                        'status' => "1",
                        'online_payment_status' => $order->payment_status,
                        'created_by' => $authUser->id,
                        'created_at' => Carbon::now(),
                    ]);
                    $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();

                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                    AccountActivity::create([
                        'user_id' => $client->id,
                        'case_id' => $invoice->case_id,
                        'credit_amount' => $payableAmount ?? 0.00,
                        'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                        'entry_date' => date('Y-m-d'),
                        'payment_method' => 'Card',
                        'payment_type' => "payment",
                        'invoice_history_id' => $invoiceHistory->id ?? Null,
                        'status' => "unsent",
                        'pay_type' => "client",
                        'from_pay' => "online",
                        'firm_id' => $client->firm_name,
                        'section' => "invoice",
                        'related_to' => $invoice->id,
                        'created_by' => $authUser->id,
                    ]);
                        
                    //Add Invoice activity
                    $data=[];
                    $data['case_id'] = $invoice->case_id;
                    $data['user_id'] = $invoice->user_id;
                    $data['activity']='accepted a payment of $'.number_format($payableAmount,2).' (Card)';
                    $data['activity_for']=$invoice->id;
                    $data['type']='invoices';
                    $data['action']='pay';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);

                    // For client activity
                    $data['client_id'] = $client->id;
                    $data['activity'] = 'pay a payment of $'.number_format($payableAmount,2).' (Card) for invoice';
                    $data['is_for_client'] = 'yes';
                    $CommonController->addMultipleHistory($data);

                    // Send confirm email to client
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 30, $invoiceOnlinePayment, 'client', 'invoice'));

                    // Send confirm email to lawyer/invoice created user
                    $user = User::whereId($invoice->created_by)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $user, $emailTemplateId = 31, $invoiceOnlinePayment, 'user', 'invoice'));
                    
                    // Send confirm email to firm owner/lead attorney
                    $firmOwner = User::where('firm_name', $client->firm_name)->where('parent_user', 0)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $firmOwner, $emailTemplateId = 31, $invoiceOnlinePayment, 'user', 'invoice'));

                    DB::commit();
                }
            }
        }
    }

    /**
     * Get cash payment detail and do payment
     */
    public function cashPayment($request, $client)
    {
        $customerId = $client->conekta_customer_id;           
        $amount = $request->amount;
        $validOrderWithCharge = [
            'line_items' => [
                [
                    'name' => ucfirst($request->type).' number '.$request->payable_record_id,
                    'unit_price' => (int)$amount * 100,
                    'quantity' => 1,
                ]
            ],
            'customer_info' => array(
                'customer_id' => $customerId
            ),
            'charges' => [
                [
                    'payment_method' => [
                        'type'       => 'oxxo_cash',
                        'expires_at' => strtotime(Carbon::now()->addDays(7)),
                    ],
                    'amount' => (int)$amount * 100,
                ]
            ],
            'currency'    => 'MXN',
            'metadata'    => array('payment' => 'Invoice/FundRequest cash payment')
        ];
        if($request->type == 'fundrequest') {
            $fundRequest = RequestedFund::whereId($request->payable_record_id)->where('status', '!=', 'paid')->first();
            if($fundRequest && $fundRequest->status != 'paid') {
                $order = \Conekta\Order::create($validOrderWithCharge);
                if($order->payment_status == 'pending_payment') {
                    $requestOnlinePayment = RequestedFundOnlinePayment::create([
                        'fund_request_id' => $fundRequest->id,
                        'user_id' => $client->id,
                        'payment_method' => 'cash',
                        'amount' => $amount,
                        'card_emi_month' => $request->emi_month ?? 0,
                        'conekta_order_id' => $order->id,
                        'conekta_charge_id' => $order->charges[0]->id ?? Null,
                        'conekta_payment_reference_id' => $order->charges[0]->payment_method->reference ?? Null,
                        'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                        'conekta_customer_id' => $customerId,
                        'conekta_payment_status' => $order->payment_status,
                        'created_by' => auth()->id(),
                        'firm_id' => $client->firm_name,
                        'conekta_order_object' => $order,
                        'status' => 'deposit',
                    ]);

                    // Update fund request paid/due amount and status
                    $fundRequest->fill([
                        'online_payment_status' => 'pending',
                    ])->save();

                    // Cash payment reference email to client
                    $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $client, $emailTemplateId = 32, $requestOnlinePayment, 'cash_reference_client', 'fundrequest'));

                    DB::commit();
                }
            }
        }
        else {
            $authUser = auth()->user();
            $invoice = Invoices::whereId($request->payable_record_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
            if($invoice && !in_array($invoice->status, ['Paid','Forwarded'])) {
                $order = \Conekta\Order::create($validOrderWithCharge);
                if($order->payment_status == 'pending_payment') {
                    $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                        'invoice_id' => $invoice->id,
                        'user_id' => $client->id,
                        'payment_method' => 'cash',
                        'amount' => $amount,
                        'conekta_order_id' => $order->id,
                        'conekta_charge_id' => $order->charges[0]->id ?? Null,
                        'conekta_payment_reference_id' => $order->charges[0]->payment_method->reference ?? Null,
                        'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                        'conekta_customer_id' => $customerId,
                        'conekta_payment_status' => $order->payment_status,
                        'created_by' => $authUser->id,
                        'firm_id' => $client->firm_name,
                        'conekta_order_object' => $order,
                    ]);

                    $invoice->fill(['online_payment_status' => 'pending'])->save();

                    $invoiceHistory=[];
                    $invoiceHistory['deposit_into'] = $request->deposit_into;
                    $request->request->add(['payment_type' => 'payment']);

                    //Insert invoice payment record.
                    $InvoicePayment=InvoicePayment::create([
                        'invoice_id' => $invoice->id,
                        'payment_from' => 'online',
                        'amount_paid' => $amount,
                        'payment_method' => 'Oxxo Cash',
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                        'status'=>"2",
                        'entry_type'=>"2",
                        'payment_from_id' => $client->id,
                        'firm_id' => $client->firm_name,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $authUser->id 
                    ]);

                    //Code For installment amount
                    $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$invoice->id)->first();
                    if(!empty($getInstallMentIfOn)){
                        $this->installmentManagement($amount,$invoice->id, $onlinePaymentStatus = 'pending');
                    }

                    $invoiceHistory = InvoiceHistory::create([
                        'invoice_id' => $invoice->id,
                        'acrtivity_title' => 'Awaiting Online Payment',
                        'pay_method' => 'Oxxo Cash',
                        'amount' => $amount,
                        'responsible_user' => $authUser->id,
                        'payment_from' => 'online',
                        'invoice_payment_id' => $InvoicePayment->id,
                        'status' => "0",
                        'online_payment_status' => 'pending',
                        'created_by' => $authUser->id,
                        'created_at' => Carbon::now(),
                    ]);

                    $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();
                        
                    // Cash payment reference email to client
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 32, $invoiceOnlinePayment, 'cash_reference_client', 'invoice'));

                    DB::commit();
                }
            }
        }
    }

    /**
     * Get bank transfer payment detail and do payment
     */
    public function bankPayment($request, $client)
    {
        $customerId = $client->conekta_customer_id;
        $amount = $request->amount;
        $authUser = auth()->user();
        $validOrderWithCharge = [
            'line_items' => [
                [
                    'name' => ucfirst($request->type).' number '.$request->payable_record_id,
                    'unit_price' => (int)$amount * 100,
                    'quantity' => 1,
                ]
            ],
            'customer_info' => array(
                'customer_id' => $customerId
            ),
            'charges' => [
                [
                    'payment_method' => [
                        'type'       => 'spei',
                        'expires_at' => strtotime(Carbon::now()->addDays(7)),
                    ],
                    'amount' => (int)$amount * 100,
                ]
            ],
            'currency'    => 'MXN',
            'metadata'    => array('payment' => 'Invoice/FundRequest bank payment')
        ];
        if($request->type == 'fundrequest') {
            $fundRequest = RequestedFund::whereId($request->payable_record_id)->where('status', '!=', 'paid')->first();
            if($fundRequest && $fundRequest->status != 'paid') {
                $order = \Conekta\Order::create($validOrderWithCharge);
                if($order->payment_status == 'pending_payment') {
                    $requestOnlinePayment = RequestedFundOnlinePayment::create([
                        'fund_request_id' => $fundRequest->id,
                        'user_id' => $client->id,
                        'payment_method' => 'bank transfer',
                        'amount' => $amount,
                        'card_emi_month' => $request->emi_month ?? 0,
                        'conekta_order_id' => $order->id,
                        'conekta_charge_id' => $order->charges[0]->id ?? Null,
                        'conekta_payment_reference_id' => $order->charges[0]->payment_method->clabe ?? Null, // CLABE number for bank transfer
                        'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                        'conekta_customer_id' => $customerId,
                        'conekta_payment_status' => $order->payment_status,
                        'created_by' => auth()->id(),
                        'firm_id' => $client->firm_name,
                        'conekta_order_object' => $order,
                        'status' => 'deposit',
                    ]);

                    // Update fund request paid/due amount and status
                    $fundRequest->fill([
                        'online_payment_status' => 'pending',
                    ])->save();

                    // Bank payment reference email to client
                    $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $client, $emailTemplateId = 35, $requestOnlinePayment, 'bank_reference_client', 'fundrequest'));

                    DB::commit();
                }
            }
        }
        else {
            $invoice = Invoices::whereId($request->payable_record_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
            if($invoice && !in_array($invoice->status, ['Paid','Forwarded'])) {
                $order = \Conekta\Order::create($validOrderWithCharge);
                if($order->payment_status == 'pending_payment') {
                    $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                        'invoice_id' => $invoice->id,
                        'user_id' => $client->id,
                        'payment_method' => 'bank transfer',
                        'amount' => $amount,
                        'conekta_order_id' => $order->id,
                        'conekta_charge_id' => $order->charges[0]->id ?? Null,
                        'conekta_payment_reference_id' => $order->charges[0]->payment_method->clabe ?? Null, // CLABE number for bank transfer
                        'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                        'conekta_customer_id' => $customerId,
                        'conekta_payment_status' => $order->payment_status,
                        'created_by' => $authUser->id,
                        'firm_id' => $client->firm_name,
                        'conekta_order_object' => $order,
                    ]);

                    $invoice->fill(['online_payment_status' => 'pending'])->save();

                    //Insert invoice payment record.
                    $InvoicePayment=InvoicePayment::create([
                        'invoice_id' => $invoice->id,
                        'payment_from' => 'online',
                        'amount_paid' => $amount,
                        'payment_method' => 'SPEI',
                        'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                        'status'=>"2",
                        'entry_type'=>"2",
                        'payment_from_id' => $client->id,
                        'firm_id' => $client->firm_name,
                        'created_by' => $authUser->id 
                    ]);

                    //Code For installment amount
                    $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$invoice->id)->first();
                    if(!empty($getInstallMentIfOn)){
                        $this->installmentManagement($amount,$invoice->id, $onlinePaymentStatus = 'pending');
                    }

                    $invoiceHistory = InvoiceHistory::create([
                        'invoice_id' => $invoice->id,
                        'acrtivity_title' => 'Awaiting Online Payment',
                        'pay_method' => 'SPEI',
                        'amount' => $amount,
                        'responsible_user' => $authUser->id,
                        'payment_from' => 'online',
                        'invoice_payment_id' => $InvoicePayment->id,
                        'status' => "0",
                        'online_payment_status' => 'pending',
                        'created_by' => $authUser->id,
                    ]);

                    $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();

                    // Bank payment reference email to client
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 35, $invoiceOnlinePayment, 'bank_reference_client', 'invoice'));

                    DB::commit();
                }
            }
        }
    }
}
  