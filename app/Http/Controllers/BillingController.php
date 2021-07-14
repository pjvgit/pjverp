<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Validator,Session,Mail,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\CaseEventLocation;
use App\Firm,App\FirmAddress,App\PotentialCaseInvoice;
use App\FirmEventReminder,App\FirmSolReminder,App\FlatFeeEntry;
use App\TaskTimeEntry,App\CaseMaster,App\TaskActivity,App\CaseTaskLinkedStaff;
use App\ExpenseEntry,App\RequestedFund,App\InvoiceAdjustment;
use App\Invoices,App\CaseClientSelection,App\UsersAdditionalInfo,App\CasePracticeArea,App\InvoicePayment;
use App\TimeEntryForInvoice,App\ExpenseForInvoice,App\SharedInvoice,App\InvoicePaymentPlan,App\InvoiceInstallment;
use App\InvoiceHistory,App\LeadAdditionalInfo,App\CaseStaff,App\InvoiceBatch,App\DepositIntoTrust,App\AllHistory,App\AccountActivity,App\DepositIntoCreditHistory,App\FlatFeeEntryForInvoice,App\TrustHistory;
use App\CaseStage,App\TempUserSelection;
use App\Jobs\InvoiceReminderEmailJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
// use PDF;
use Illuminate\Support\Str;
class BillingController extends BaseController
{
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
            $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
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
        ->select('task_time_entry.*',"task_activity.title as activity_title","case_master.case_title as ctitle","case_master.case_unique_number as case_unique_number"  ,"case_master.id as cid",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid");

        if(isset($requestData['c']) && $requestData['c']!=''){
            $case = $case->where("case_master.id",$requestData['c']);
        }
        if(isset($requestData['from']) && $requestData['from'] !='' && isset($requestData['to']) && $requestData['to']!=''){
            $case = $case->where('task_time_entry.entry_date', '>=', date('Y-m-d',strtotime($requestData['from'])))
                           ->where('task_time_entry.entry_date', '<=', date('Y-m-d',strtotime($requestData['to'])));
        }
        if(isset($requestData['from']) && $requestData['from'] !='' && isset($requestData['to']) && $requestData['to'] ==''){
            $case = $case->where('task_time_entry.entry_date', '>=', date('Y-m-d',strtotime($requestData['from'])));
        }
        if(isset($requestData['from']) && $requestData['from'] =='' && isset($requestData['to']) && $requestData['to'] !=''){
            $case = $case->where('task_time_entry.entry_date', '<=', date('Y-m-d',strtotime($requestData['to'])));
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

        $totalData=$case->count();
        $totalFiltered = $totalData; 

        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $case = $case->get();

       
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
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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

        return view('billing.time_entry.loadTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity',"from","curDate","case_id","default_rate"));     
        exit;    
    } 
    public function loadTimeEntryPopupDontRefresh(Request $request)
    {  
        $case_id=$request->case_id;
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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
        $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
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
            TaskTimeEntry::where("id", $id)->delete();
            
            
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

        $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);

        $case = $case->get();
        
        $filename="time_entry_".time().'.pdf';
         $PDFData=view('billing.time_entry.viewTimeEntryPdf',compact('case'));
        $pdf = new Pdf;
        // $pdf->setOptions(['javascript-delay' => 5000]);
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        // $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->saveAs(public_path("download/pdf/".$filename));
        $path = public_path("download/pdf/".$filename);
        // return response()->download($path);
        // exit;
        return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
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
            $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
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
        if(!isset($requestData['order'][0]['dir'])){
            $requestData['order'][0]['dir']="DESC";
        }
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
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
        $CaseMasterData = CaseMaster::whereIn('created_by',$getChildUsers)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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
        $ExpenseEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
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
            
            for($i=1;$i<=count($request->case_or_lead)-1;$i++){
                $TaskTimeEntry = new ExpenseEntry; 
                $TaskTimeEntry->case_id =$request->case_or_lead[$i];
                $TaskTimeEntry->user_id =$request->staff_user;
                $TaskTimeEntry->activity_id=$request->activity[$i];
                if(isset($request->billable[$i]) && $request->billable[$i]=="on"){
                    $TaskTimeEntry->time_entry_billable="yes";
                }else{
                    $TaskTimeEntry->time_entry_billable="no";
                }
                $TaskTimeEntry->description=$request->description[$i];
                $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
                $TaskTimeEntry->cost=$request->cost[$i];
                $TaskTimeEntry->duration =$request->duration[$i];
                $TaskTimeEntry->created_at=date('Y-m-d h:i:s'); 
                $TaskTimeEntry->created_by=Auth::User()->id; 
                $TaskTimeEntry->save();

                //Add expense history
                $data=[];
                $data['case_id']=$TaskTimeEntry->case_id;
                $data['user_id']=$TaskTimeEntry->user_id;
                $data['activity']='added an expense';
                $data['activity_for']=$TaskTimeEntry->activity_id;
                $data['expense_id']=$TaskTimeEntry->id;
                $data['type']='expenses';
                $data['action']='add';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            }
            
            return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
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
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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

            ExpenseEntry::where("id", $id)->delete();
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
        $ExpenseEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
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

        return response()->json(['errors'=>'','id'=>$ExpenseEntry->id]);
        exit;
      }
    }
    public function bulkAssignCase(Request $request)
    {
        $getChildUsers=$this->getParentAndChildUserIds();
        $CaseMasterData = CaseMaster::whereIn('created_by',$getChildUsers)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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

        $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);

        $case = $case->get();
        
        $filename="expenses_entry_".time().'.pdf';
         $PDFData=view('billing.expenses.viewExpenseEntryPdf',compact('case'));
        $pdf = new Pdf;
        // $pdf->setOptions(['javascript-delay' => 5000]);
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        // $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->saveAs(public_path("download/pdf/".$filename));
        $path = public_path("download/pdf/".$filename);
        // return response()->download($path);
        // exit;
        return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
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
         $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
         $case = $case->get();
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
         $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
         $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);
 
         $case = $case->get();
         
         $filename="requested_funds_".time().'.pdf';
         $PDFData=view('billing.requested_fund.viewRequestedFundsPdf',compact('case'));
         $pdf = new Pdf;
        //  $pdf->setOptions(['javascript-delay' => 5000]);
         if($_SERVER['SERVER_NAME']=='localhost'){
             $pdf->binary = WKHTMLTOPDF_PATH;
         }
         $pdf->addPage($PDFData);
        //  $pdf->setOptions(['javascript-delay' => 5000]);
         $pdf->saveAs(public_path("download/pdf/".$filename));
         $path = public_path("download/pdf/".$filename);
         return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
         exit;
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
         $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
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
 
         $case = $case->offset($requestData['current_page']*$requestData['length'])->limit($requestData['length']);
         $case = $case->orderBy($columns[$requestData['orderon'][0][0]], $requestData['orderon'][0][1]);
 
         $case = $case->get();
         
         $filename="saved_activity".time().'.pdf';
        $PDFData=view('billing.activities.savedActivityPdf',compact('case'));
         $pdf = new Pdf;
         // $pdf->setOptions(['javascript-delay' => 5000]);
         if($_SERVER['SERVER_NAME']=='localhost'){
             $pdf->binary = WKHTMLTOPDF_PATH;
         }
         $pdf->addPage($PDFData);
         // $pdf->setOptions(['javascript-delay' => 5000]);
         $pdf->saveAs(public_path("download/pdf/".$filename));
         $path = public_path("download/pdf/".$filename);
         // return response()->download($path);
         // exit;
         return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
         exit;
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
            ->where("invoices.created_by",$id);
            $InvoiceCounter=$Invoices->count();
            if($Invoices->get()){
                foreach($Invoices->get() as $k=>$v){
                    if($v->due_date!=NULL && $v->due_date < date('Y-m-d')){
                        $updateInvoice= Invoices::find($v->id);
                        $updateInvoice->status="Overdue";
                        $updateInvoice->save();
                    }   
                }
            }

            $InvoicesPaidAmount = Invoices::where("invoices.created_by",$id)->where("invoices.status","Paid")->where("invoices.created_by",$id)->sum("paid_amount");
           
            $InvoicesPaidPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status","Partial")->where("invoices.created_by",$id)->sum("paid_amount");

            $InvoicesSentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Sent')->where("invoices.created_by",$id)->sum("total_amount");

            $InvoicesDraftAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Draft')->where("invoices.created_by",$id)->sum("total_amount");

            $InvoicesUnsentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Unsent')->where("invoices.created_by",$id)->sum("total_amount");

            $InvoicesPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Partial')->where("invoices.created_by",$id)->sum("paid_amount");
            
            $InvoicesOverdueAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Overdue')->where("invoices.created_by",$id)->sum("due_amount");
            
            $getCaseIds = Invoices::select("case_id")->get()->pluck('case_id');
            $getClientIds = Invoices::select("user_id")->get()->pluck('user_id');

            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->whereIn("id",$getCaseIds)->where('is_entry_done',"1")->get();
           
            $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->whereIn("id",$getClientIds)->get();
           
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


         $columns = array('id', 'contact_name', 'id','id', 'contact_name', 'ctitle','total_amount','paid_amount','due_amount','invoices.due_date','invoices.created_at');
         $requestData= $_REQUEST;
         $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
         ->leftJoin("case_master","invoices.case_id","=","case_master.id")
         ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid")->where("invoices.created_by",Auth::user()->id);
      

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
         
         $totalData=$Invoices->count();
         $totalFiltered = $totalData; 
        $Invoices = $Invoices->offset($requestData['start'])->limit($requestData['length']);
        $Invoices = $Invoices->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $Invoices = $Invoices->with("invoiceForwardedToInvoice")->get();
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
            $Invoices = $Invoices->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
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
        ['min'=>'No users selected',
        'required'=>'No users selected']);
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
            $token=url('activate_account/bills=&web_token='.$Invoices->invoice_unique_token);

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
                $firmData=Firm::find(Auth::User()->firm_name);
                $caseMaster=CaseMaster::select("case_title")->find($invoiceData['case_id']);
                $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$invoiceData['user_id'])->first();


                $trustAccounts = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->join('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid","users.user_level","users_additional_info.trust_account_balance")->where("case_client_selection.case_id",$invoiceData['case_id'])->groupBy("case_client_selection.selected_user")->get();
      

                return view('billing.invoices.payInvoice',compact('userData','firmData','invoice_id','invoiceData','caseMaster','trustAccounts'));
                exit;    
            }else{
                return response()->json(['errors'=>'error']);
            }
        }
    }

    public function saveTrustInvoicePayment(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);
        $InvoiceData=Invoices::find($request->invoice_id);
        $paid=$InvoiceData['paid_amount'];
        $invoice=$InvoiceData['total_amount'];
        $finalAmt=$invoice-$paid;

        $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$InvoiceData['user_id'])->first();

        $validator = \Validator::make($request->all(), [
            'trust_account' => 'required',
            'amount' => 'required|numeric|min:1|max:'.$finalAmt.'|lte:'.$userData['trust_account_balance'],
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
               $entryDone= DB::table('invoice_payment')->insert([
                    'invoice_id'=>$request->invoice_id,
                    'payment_from'=>'trust',
                    'amount_paid'=>$request->amount,
                    'payment_date'=>date('Y-m-d',strtotime($request->payment_date)),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"0",
                    'deposit_into'=>"Operating Account",
                    'payment_from_id'=>$request->trust_account,
                    'total'=>($currentBalance['total']+$request->amount),
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);
                $lastInvoicePaymentId= DB::getPdo()->lastInsertId();
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();
               
                //Deduct invoice amount when payment done
                $totalPaid=InvoicePayment::where("invoice_id",$request->invoice_id)->get()->sum("amount_paid");
                
                if(($totalPaid-$InvoiceData['total_amount'])==0){
                    $status="Paid";
                }else{
                    $status="Partial";
                }
                DB::table('invoices')->where("id",$request->invoice_id)->update([
                    'paid_amount'=>$totalPaid,
                    'due_amount'=>($InvoiceData['total_amount'] - $totalPaid),
                    'status'=>$status,
                ]);

                // Deduct amount from trust account after payment.
                $trustAccountAmount=($userData['trust_account_balance']-$request->amount);
                UsersAdditionalInfo::where('user_id',$InvoiceData['user_id'])
                ->update(['trust_account_balance'=>$trustAccountAmount]);

                DB::commit();

               

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
                $invoiceHistory['deposit_into']='Operating Account';
                $invoiceHistory['deposit_into_id']=($request->trust_account)??NULL;
                $invoiceHistory['invoice_payment_id']=$lastInvoicePaymentId;
                $invoiceHistory['notes']=$request->notes;
                $invoiceHistory['status']="1";
                $invoiceHistory['created_by']=Auth::User()->id;
                $invoiceHistory['created_at']=date('Y-m-d H:i:s');
                $this->invoiceHistory($invoiceHistory);


                //Add Invoice history
                $InvoiceData=Invoices::find($request->invoice_id);
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
                $activityHistory['debit_amount']=0.00;
                $activityHistory['credit_amount']=$request->amount;
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
                return response()->json(['errors'=>["Server error."]]); //$e->getMessage()
                 exit;   
            }
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }
   
    public function saveInvoicePayment(Request $request)
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
               $entryDone= DB::table('invoice_payment')->insert([
                    'invoice_id'=>$request->invoice_id,
                    'payment_from'=>'client',
                    'amount_paid'=>$request->amount,
                    'payment_method'=>$request->payment_method,
                    'deposit_into'=>$request->deposit_into,
                    'notes'=>$request->notes,
                    'deposit_into_id'=>($request->trust_account)??NULL,
                    'payment_date'=>date('Y-m-d',strtotime($request->payment_date)),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"1",
                    'total'=>$s,
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);

                $lastInvoicePaymentId= DB::getPdo()->lastInsertId();
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();

                //Deduct invoice amount when payment done
                $totalPaid=InvoicePayment::where("invoice_id",$request->invoice_id)->get()->sum("amount_paid");
                if(($totalPaid-$InvoiceData['total_amount'])==0){
                    $status="Paid";
                }else{
                    $status="Partial";
                }
                DB::table('invoices')->where("id",$request->invoice_id)->update([
                    'paid_amount'=>$totalPaid,
                    'due_amount'=>($InvoiceData['total_amount'] - $totalPaid),
                    'status'=>$status,
                ]);

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

                 //Removed time entry id
                 $TimeEntryForInvoice=TimeEntryForInvoice::where("invoice_id",$id)->get();
                 foreach($TimeEntryForInvoice as $k=>$v){
                     DB::table('task_time_entry')->where("id",$v->time_entry_id)->update([
                         'status'=>'unpaid',
                         'invoice_link'=>NULL
                     ]);
 
                     $deleteFromLinkTable=TimeEntryForInvoice::where("id",$v->time_entry_id)->delete();
                 }
                 //Removed expense entry
                 $ExpenseForInvoice=ExpenseForInvoice::where("invoice_id",$id)->get();
                 foreach($ExpenseForInvoice as $k=>$v){
                     DB::table('expense_entry')->where("id",$v->expense_entry_id)->update([
                         'status'=>'unpaid',
                         'invoice_link'=>NULL
                     ]);
                     $ExpenseForInvoice=ExpenseForInvoice::where("id",$v->expense_entry_id)->delete();
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
            $getChildUsers=$this->getParentAndChildUserIds();
            // $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
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
            $Invoices = $Invoices->where("case_master.created_by",Auth::User);

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
        $Invoices = $Invoices->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $Invoices = $Invoices->groupBy("case_master.id");
        $Invoices = $Invoices->get();
        $json_data = array(
           "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $Invoices 
        );
        echo json_encode($json_data);  
    }
    public function getInvoicePendingCase()
    {
        $getChildUsers=$this->getParentAndChildUserIds();

        $TaskTimeEntryIds = CaseMaster::join("task_time_entry","task_time_entry.case_id","=","case_master.id")
        ->select("case_master.id","task_time_entry.case_id")->where("task_time_entry.status","unpaid")->whereIn("case_master.created_by",$getChildUsers)->get()->pluck('case_id')->toArray();


        $ExpenseEntryIds = CaseMaster::join("expense_entry","expense_entry.case_id","=","case_master.id")
        ->select("case_master.id","expense_entry.case_id")->where("expense_entry.status","unpaid")->whereIn("case_master.created_by",$getChildUsers)->get()->pluck('case_id')->toArray();
        
        $uniqueCase=array_unique(array_merge($TaskTimeEntryIds,$ExpenseEntryIds));
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
            $userData=User::find($client_id);
            // $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$client_id)->first();

            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
            ->where("user_id",$client_id)
            ->first();


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
            
            return view('billing.invoices.scratch_invoices',compact('ClientList','CompanyList','client_id','case_id','caseListByClient','caseMaster','TimeEntry','ExpenseEntry','InvoiceAdjustment','userData','UsersAdditionalInfo','getAllClientForSharing','maxInvoiceNumber','adjustment_token','from_date','bill_to_date','filterByDate','selectedClient'));
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


            $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();

            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.selected_user",$client_id)->get()->pluck("case_id");

            //List all case by client 
            $caseListByClient = CaseMaster::select("*")->whereIn('case_master.id',$caseCllientSelection)->select("*")->get();
            
            //Get the case data
            $caseMaster = CaseMaster::find($case_id);

            //Get the Time Entry list
            $TimeEntry=TaskTimeEntry::leftJoin("users","users.id","=","task_time_entry.user_id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select("task_time_entry.*","task_activity.*","users.*","task_time_entry.id as itd")->where("task_time_entry.case_id",$case_id)
            // ->where("task_time_entry.time_entry_billable","yes")
            ->where("task_time_entry.status","unpaid")
            ->where(function($TimeEntry) use($request){
                $TimeEntry->where("task_time_entry.token_id","!=",$request->token);
                $TimeEntry->orwhere("task_time_entry.token_id",NULL);
            });

            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                $TimeEntry=$TimeEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($request->from_date)),date('Y-m-d',strtotime($request->bill_to_date))]);
                $from_date=$request->from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }
            $TimeEntry=$TimeEntry->get();
        
            //Get the Expense Entry list
            $ExpenseEntry=ExpenseEntry::leftJoin("users","users.id","=","expense_entry.user_id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select("expense_entry.*","task_activity.*","users.*","expense_entry.id as eid")->where("expense_entry.case_id",$case_id)
            // ->where("expense_entry.time_entry_billable","yes")
            // ->where("expense_entry.remove_from_current_invoice","no")
            // ->where("expense_entry.token_id","!=",$request->token) 
            ->where("expense_entry.status","unpaid")
            ->where(function($ExpenseEntry) use($request){
                $ExpenseEntry->where("expense_entry.token_id","!=",$request->token);
                $ExpenseEntry->orwhere("expense_entry.token_id",NULL);
            });

            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                $ExpenseEntry=$ExpenseEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($request->from_date)),date('Y-m-d',strtotime($request->bill_to_date))]);
                $from_date=$request->from_date;
                $bill_to_date=$request->bill_to_date;
                $filterByDate='yes';
            }
            $ExpenseEntry=$ExpenseEntry->get();


            // //Get Flat fees entry
            if($caseMaster) {
                $totalFlatFee = FlatFeeEntry::where('case_id', $case_id)->sum('cost');
                if($caseMaster->billing_method == "flat") {
                    $remainFlatFee = $caseMaster->billing_amount - $totalFlatFee;
                    if($remainFlatFee > 0) {
                        FlatFeeEntry::create([
                            'case_id' => $caseMaster->id,
                            'user_id' => auth()->id(),
                            'entry_date' => Carbon::now(),
                            'cost' =>  $remainFlatFee,
                            'time_entry_billable' => 'yes',
                            'created_by' => auth()->id(), 
                        ]);
                    }
                }
            }
            $FlatFeeEntry=FlatFeeEntry::leftJoin("users","users.id","=","flat_fee_entry.user_id")->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")->where("flat_fee_entry.case_id",$case_id)
            ->where("flat_fee_entry.status","unpaid")
            ->where(function($FlatFeeEntry) use($request){
                $FlatFeeEntry->where("flat_fee_entry.token_id","!=",$request->token);
                $FlatFeeEntry->orwhere("flat_fee_entry.token_id",NULL);
            });

            if(isset($request->from_date) && isset($request->bill_to_date) && $request->from_date!=NULL && $request->bill_to_date!=NULL){
                $FlatFeeEntry=$FlatFeeEntry->whereBetween('entry_date', [date('Y-m-d',strtotime($request->from_date)),date('Y-m-d',strtotime($request->bill_to_date))]);
            }
            $FlatFeeEntry=$FlatFeeEntry->get();

            //Get the Adjustment list
            $InvoiceAdjustment=InvoiceAdjustment::select("*")
            ->where("invoice_adjustment.case_id",$case_id)
            ->where("invoice_adjustment.token",$request->token)
            ->get();


            $maxInvoiceNumber = DB::table("invoices")->max("id") + 1;

            $adjustment_token=$request->token;

            // Get unpaid balances invoices list
            $unpaidInvoices = [];
            if($caseMaster) {
                $unpaidInvoices = Invoices::where("case_id", $caseMaster->id)->where("due_amount", ">", 0)->where("status", "!=", "Forwarded")->get();
            }

            return view('billing.invoices.new_invoices',compact('ClientList','CompanyList','client_id','case_id','caseListByClient','caseMaster','TimeEntry','ExpenseEntry','InvoiceAdjustment','userData','UsersAdditionalInfo','getAllClientForSharing','maxInvoiceNumber','adjustment_token','from_date','bill_to_date','filterByDate','FlatFeeEntry', 'tempInvoiceToken', 'unpaidInvoices'));
        }else{
            return view('pages.404');
        }
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
            if($request->action=="delete"){
                TimeEntryForInvoice::where("time_entry_id", $id)->delete();
                TaskTimeEntry::where("id", $id)->delete();
            }else{
                // TaskTimeEntry::where('id',$id)->update(['remove_from_current_invoice'=>'yes']);
                TimeEntryForInvoice::where("time_entry_id", $id)->delete();
                TaskTimeEntry::where('id',$id)->update(['token_id'=>$request->token_id]);
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
            if($request->action=="delete"){
                FlatFeeEntryForInvoice::where("flat_fee_entry_id", $id)->delete();
                FlatFeeEntry::where("id", $id)->delete();
            }else{
                FlatFeeEntryForInvoice::where("flat_fee_entry_id", $id)->delete();
                FlatFeeEntry::where('id',$id)->update(['token_id'=>$request->token_id]);
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
        
            $id=base64_decode($request->case_id);
            // TaskTimeEntry::where("case_id", $id)->delete();
            TaskTimeEntry::where('case_id',$id)->where('status','unpaid')->update(['token_id'=>$request->token_id]);
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
                if($request->id=="0"){
                    $case_id=0;
                }else{
                    $case_id=base64_decode($request->id);
                }
                $CaseMasterData = CaseMaster::find($case_id);
                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                // $invoice_token = $request->invoice_token;
                return view('billing.invoices.addSingleFlatFeeEntryPopup',compact('CaseMasterData','loadFirmStaff','case_id','invoice_id'/* , 'invoice_token' */));     
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
            $FlatFeeEntry->case_id =($request->case_id)??'none';
            $FlatFeeEntry->user_id =$request->staff_user;
            if(isset($request->invoice_id)){
                $FlatFeeEntry->invoice_link =$request->invoice_id;
            }
            $FlatFeeEntry->description=$request->case_description;
            $FlatFeeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
            $FlatFeeEntry->time_entry_billable='yes';
            $FlatFeeEntry->cost=str_replace(",","",$request->rate_field_id);
            $FlatFeeEntry->created_by=Auth::User()->id; 
            // $FlatFeeEntry->token_id=$request->token_id; 
            $FlatFeeEntry->save();

            if(isset($request->invoice_id)){
                $FlatFeeEntryForInvoice=new FlatFeeEntryForInvoice;
                $FlatFeeEntryForInvoice->invoice_id=$FlatFeeEntry->invoice_link;                    
                $FlatFeeEntryForInvoice->flat_fee_entry_id=$FlatFeeEntry->id;
                $FlatFeeEntryForInvoice->created_by=Auth::User()->id; 
                $FlatFeeEntryForInvoice->created_at=date('Y-m-d h:i:s'); 
                $FlatFeeEntryForInvoice->save();
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
               
                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();

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
            $FlatFeeEntry->user_id =$request->staff_user;
            $FlatFeeEntry->description=$request->case_description;
            $FlatFeeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
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

                $defaultRate='';
                $CaseMasterData = CaseMaster::find($case_id);

                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.addSingleTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','case_id','invoice_id'));     
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
            $TaskTimeEntry->case_id =($request->case_id)??'none';
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
            $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
            $TaskTimeEntry->entry_rate=str_replace(",","",$request->rate_field_id);
            $TaskTimeEntry->rate_type=$request->rate_type_field_id;
            $TaskTimeEntry->duration =$request->duration_field;
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
           

            //Add time entry history
            $data=[];
            $data['case_id']=$TaskTimeEntry->case_id;
            $data['user_id']=$TaskTimeEntry->user_id;
            $data['activity']='added a time entry';
            $data['activity_for']=$TaskTimeEntry->id;
            $data['expense_id']=$TaskTimeEntry->id;
            $data['type']='time_entry_id';
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

                $defaultRate='';
                $CaseMasterData = CaseMaster::find($case_id);

                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.editSingleTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','case_id','TaskTimeEntry'));     
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
            $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
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
            if($request->action=="delete"){
                ExpenseForInvoice::where("expense_entry_id", $id)->delete();
                ExpenseEntry::where("id", $id)->delete();
            }else{
                ExpenseForInvoice::where("expense_entry_id", $id)->delete();
                ExpenseEntry::where('id',$id)->update(['token_id'=>$request->token_id]);
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

                $defaultRate='';
                $CaseMasterData = CaseMaster::find($case_id);

                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
                $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
                
                return view('billing.invoices.addSingleExpenseEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','case_id','invoice_id'));     
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
        $ExpenseEntry->case_id =($request->case_id)??'none';
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
        $ExpenseEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
        $ExpenseEntry->cost=str_replace(",","",$request->rate_field_id);
        $ExpenseEntry->duration =$request->duration_field;
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

                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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
        $ExpenseEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
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
        $InvoiceAdjustment->case_id =($request->case_id)??'none';
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
                $InvoiceAdjustment->forceDelete();
                return response()->json(['errors'=>'']);
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
            $token=url('firmuser/verify', $user->token);
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
            'total_text' => 'required',
            // 'timeEntrySelectedArray'=>'required_without:expenseEntrySelectedArray|array',
            // 'expenseEntrySelectedArray'=>'required_without:timeEntrySelectedArray|array',
        ];
        if(!empty($request->flatFeeEntrySelectedArray) && count($request->flatFeeEntrySelectedArray)) {
            $rules['timeEntrySelectedArray'] = 'nullable|array';
            $rules['expenseEntrySelectedArray'] = 'nullable|array';
        } else {
            $rules['timeEntrySelectedArray'] = 'required_without:expenseEntrySelectedArray|array';
            $rules['expenseEntrySelectedArray'] = 'required_without:timeEntrySelectedArray|array';
        }
        $request->validate($rules,
        [
            "invoice_number_padded.unique"=>"Invoice number is already taken",
            "invoice_number_padded.required"=>"Invoice number must be greater than 0",
            "invoice_number_padded.numeric"=>"Invoice number must be greater than 0",
            "contact.required"=>"Billing user can't be blank",
            "timeEntrySelectedArray.required_without"=>"You are attempting to save a blank invoice, please add time entries activity.",
            "expenseEntrySelectedArray.required_without"=>"You are attempting to save a blank invoice, please add expenses activity"
        ]);          
       
            // print_r($request->all());exit;
            DB::table('invoices')->where("deleted_at","!=",NULL)->where("id",$request->invoice_number_padded)->delete();
            $InvoiceSave=new Invoices;
            $InvoiceSave->id=$request->invoice_number_padded;
            $InvoiceSave->user_id=$request->contact;
            $InvoiceSave->case_id= ($request->court_case_id == "none") ? 0 : $request->court_case_id;
            $InvoiceSave->invoice_date=date('Y-m-d',strtotime($request->bill_invoice_date));
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
            $InvoiceSave->status=$request->bill_sent_status;
            $InvoiceSave->total_amount=$request->final_total_text;
            $InvoiceSave->due_amount=$request->final_total_text;
            $InvoiceSave->terms_condition=$request->bill['terms_and_conditions'];
            $InvoiceSave->notes=$request->bill['bill_notes'];
            $InvoiceSave->status=$request->bill_sent_status;
            $InvoiceSave->created_by=Auth::User()->id; 
            $InvoiceSave->created_at=date('Y-m-d h:i:s'); 
            $InvoiceSave->firm_id = auth()->user()->firm_name;
            $InvoiceSave->save();

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


            InvoiceAdjustment::where('token',$request->adjustment_token)->update(['invoice_id'=>$InvoiceSave->id]);

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
                    if(empty($request->linked_staff_checked_share) || !in_array($v,$request->linked_staff_checked_share)){
                        $TimeEntryForInvoice->save();
                        DB::table('task_time_entry')->where("id",$v)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                    }

                   
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
                    if(empty($request->invoice_expense_entry_nonbillable_time) || !in_array($v,$request->invoice_expense_entry_nonbillable_time)){
                        $ExpenseEntryForInvoice->save();
                        DB::table('expense_entry')->where("id",$v)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                    }
                   
                }
            }
            
            //Invoice Shared With Client
            if(!empty($request->portalAccess)){
                SharedInvoice::where("invoice_id",$InvoiceSave->id)->delete();
                $InvoiceSave->status="Sent";
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

            $decodedId=base64_encode($InvoiceSave->id);
            return redirect('bills/invoices/view/'.$decodedId);
            // return response()->json(['errors'=>'','invoice_id'=>$InvoiceSave->id]);
            exit;

        
    }

    //View Invoice 
    public function viewInvoice(Request $request)
    {
        $invoiceID=base64_decode($request->id);
        // echo Hash::make($invoiceID);
        $findInvoice=Invoices::whereId($invoiceID)->with("forwardedInvoices")->first();
        if(empty($findInvoice) || $findInvoice->created_by!=Auth::User()->id)
        {
            return view('pages.404');
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
            $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoiceID)->get();

            $caseMaster=CaseMaster::find($findInvoice->case_id);
            $userMaster=User::find($findInvoice->user_id);
            
            $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoiceID)->get();

            $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoiceID)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();


            $SharedInvoiceCount=SharedInvoice::Where("invoice_id",$invoiceID)->count();
            // if(!file_exists(public_path('download/pdf/'."Invoice_".$invoiceID.".pdf")))
            if(!file_exists(Storage::path('download/pdf/Invoice_'.$invoiceID.".pdf")))
            {
                $this->generateInvoicePdfAndSave($request);
            }

            $invoiceNo = sprintf('%06d', $findInvoice->id);
            if($request->ajax()) {
                return view('billing.invoices.partials.load_invoice_detail',compact('findInvoice','InvoiceHistory','lastEntry','firmData','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','caseMaster','userMaster','SharedInvoiceCount','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice', 'invoiceNo'))->render();
            }

            return view('billing.invoices.viewInvoice',compact('findInvoice','InvoiceHistory','lastEntry','firmData','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','caseMaster','userMaster','SharedInvoiceCount','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice', 'invoiceNo'));     
            exit; 
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
            $InvoiceHistory->deposit_into= $historyData['deposit_into'];
            $InvoiceHistory->deposit_into_id= ($historyData['deposit_into_id'])??NULL;
            $InvoiceHistory->invoice_payment_id= ($historyData['invoice_payment_id'])??NULL;
            $InvoiceHistory->notes= $historyData['notes'];
            $InvoiceHistory->status= ($historyData['status'])??0;
            $InvoiceHistory->refund_ref_id= ($historyData['refund_ref_id'])??NULL;
            $InvoiceHistory->created_by=$historyData['created_by'];
            $InvoiceHistory->created_at=$historyData['created_at'];
            $InvoiceHistory->save();
            return true;
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
                     'status'=>'unpaid'
                 ]);
                 FlatFeeEntryForInvoice::where("id", $v->id)->delete();
             }

             
            //Remove time entry for the invoice and reactivated time entry
            $timeEntryData=TimeEntryForInvoice::where("invoice_id",$id)->get();
            foreach($timeEntryData as $k=>$v){
                DB::table('task_time_entry')->where("id",$v->time_entry_id)->update([
                    'status'=>'unpaid'
                ]);
                TimeEntryForInvoice::where("id", $v->id)->delete();
            }

            //Remove Expense for the invoice and reactivated expense entry
            $expenseEntryData=ExpenseForInvoice::where("invoice_id",$id)->get();
            foreach($expenseEntryData as $k=>$v){
                DB::table('expense_entry')->where("id",$v->expense_entry_id)->update([
                    'status'=>'unpaid'
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
                
                $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users.last_login")->where("case_client_selection.case_id",$Invoices['case_id'])->get();

                foreach($getAllClientForSharing as $k=>$v){
                    $checkedUser=SharedInvoice::where("invoice_id",$Invoices['id'])->where("user_id",$v->user_id)->first();
                    if(!empty($checkedUser)){
                        $v['shared']="yes";
                        $v['sharedDate']=$checkedUser['created_at'];
                        $v['isViewd']=$checkedUser['is_viewed'];

                    }else{
                        $v['shared']="no";
                        $v['sharedDate']=NULL;
                        $v['isViewd']=$checkedUser['is_viewed'] ?? "no";
                    }
                }
                $SharedInvoice=SharedInvoice::where("invoice_id",$Invoices['id'])->pluck("user_id");
                return view('billing.invoices.shareInvoice',compact('Invoices','getAllClientForSharing','SharedInvoice'));     
                exit; 
            }else{
                return view('pages.404');
            }
        }
    }

    public function saveShareInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $Invoices=Invoices::find($request->invoice_id);
            if(!empty($Invoices)){
                foreach($request->invoice_shared as $k=>$v){
                    $SharedInvoice=SharedInvoice::where("user_id",$k)->Where("invoice_id",$request->invoice_id)->count();
                    if($SharedInvoice<=0){
                        $SharedInvoice=new SharedInvoice;
                        $SharedInvoice->invoice_id=$request->invoice_id;                    
                        $SharedInvoice->user_id =$k;
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
                    }
                }
                session(['popup_success' => 'Sharing updated']);
                return response()->json(['errors'=>'']);
            }else{
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
        ['min'=>'No users selected',
        'required'=>'No users selected']);
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
                }
                $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
                if($emailTemplate) {
                    dispatch(new InvoiceReminderEmailJob($FindInvoice, $findUSer, $emailTemplate));
                }
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
        $Invoice=Invoices::where("id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$Invoice['user_id'])->first();
       
        $caseMaster=CaseMaster::find($Invoice['case_id']);
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        

        $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();

        $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();

        //Get the Adjustment list
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->get();
        $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();
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
            return view('billing.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceInstallment','InvoiceHistory','InvoiceHistoryTransaction','FlatFeeEntryForInvoice'));
        }
    }
    public function downloaInvoivePdf(Request $request)
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
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->get();

        $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();

        $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();

        //Get the flat fee Entry list
        $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
        ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
        ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
        ->where("flat_fee_entry_for_invoice.invoice_id",$invoice_id)
        ->get();
        
        $filename="Invoice_".$invoice_id.'.pdf';
        $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistory','InvoiceInstallment','InvoiceHistoryTransaction','FlatFeeEntryForInvoice'));
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
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->get();

        $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();

        $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();

        
        $filename="Invoice_".$invoice_id.'.pdf';
        $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceInstallment','InvoiceHistory','InvoiceHistoryTransaction'));
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
       
        $caseMaster=CaseMaster::find($Invoice['case_id']);
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        $firmData=Firm::find($userData['firm_name']);


        $TimeEntryForInvoice = TimeEntryForInvoice::join("task_time_entry",'task_time_entry.id',"=","time_entry_for_invoice.time_entry_id")->leftJoin("users","task_time_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")->select('users.*','task_time_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("time_entry_for_invoice.invoice_id",$invoice_id)->get();

        $ExpenseForInvoice = ExpenseForInvoice::leftJoin("expense_entry",'expense_entry.id',"=","expense_for_invoice.expense_entry_id")->leftJoin("users","expense_entry.user_id","=","users.id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select('users.*','expense_entry.*',"task_activity.title as activity_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as user_name'),"users.id as uid")->where("expense_for_invoice.invoice_id",$invoice_id)->get();

        //Get the Adjustment list
        $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->get();
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();

        $InvoiceInstallment=InvoiceInstallment::select("*")
        ->where("invoice_installment.invoice_id",$invoice_id)
        ->get();
        $filename="Invoice_".$invoice_id.'.pdf';
        $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistoryTransaction','InvoiceInstallment'));
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
                $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable","users.last_login")->where("case_client_selection.case_id",$Invoices['case_id'])->get();


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
        ['min'=>'No users selected',
        'required'=>'No users selected']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $FindInvoice=Invoices::find($request->invoice_id);
            if($FindInvoice->status=="Draft" || $FindInvoice->status=="Unsent"){
                $FindInvoice->status="Sent";
                $FindInvoice->save();
            }
            if($FindInvoice) {
                $FindInvoice->fill(['is_sent' => 'yes'])->save();
            }
            $invoice_id=$request->invoice_id;
            foreach($request->client as $k=>$v){
                $findUSer=User::find($v);
                $email=$findUSer['email'];
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
                $files = [asset(Storage::url("download/pdf/Invoice_".$invoice_id.".pdf"))];
                $sendEmail = $this->sendMailWithAttachment($user,$files);
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
        $findInvoice=Invoices::whereId($invoiceID)->with("forwardedInvoices")->first();
        if(empty($findInvoice) || $findInvoice->created_by!=Auth::User()->id)
        {
            return view('pages.404');
        }else{
            //Get all client related to firm
            // $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
            $ClientList = userClientList();

            //Get all company related to firm
            // $CompanyList = User::select("email","first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
            $CompanyList = userCompanyList();
        
            $case_id=$findInvoice->case_id;
            $caseClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$case_id)->where('case_client_selection.is_billing_contact','yes')->select("*")->first();
          
            $client_id=$findInvoice->user_id;

            $userData=User::find($client_id);
            // $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$client_id)->first();

            $UsersAdditionalInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->leftJoin('countries','users.country','=','countries.id');
            $UsersAdditionalInfo = $UsersAdditionalInfo->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,users_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"users_additional_info.*")
            ->where("user_id",$client_id)
            ->first();


            $getAllClientForSharing=  CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as unm'),"users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();

            $caseCllientSelection = CaseClientSelection::select("*")->where("case_client_selection.selected_user",$client_id)->get()->pluck("case_id");

            //List all case by client 
            $caseListByClient = CaseMaster::select("*")->whereIn('case_master.id',$caseCllientSelection)->select("*")->get();
            
            //Get the case data
            $caseMaster = CaseMaster::find($case_id);

            //Get the Time Entry list
            $TimeEntry=TimeEntryForInvoice::leftJoin("task_time_entry","time_entry_for_invoice.time_entry_id","=","task_time_entry.id")
            ->leftJoin("users","users.id","=","task_time_entry.user_id")
            ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
            ->select("task_time_entry.*","task_activity.*","users.*","task_time_entry.id as itd")
            ->where("time_entry_for_invoice.invoice_id",$invoiceID)
            // ->where("task_time_entry.remove_from_current_invoice","no")
            ->get();
        
            //Get the Expense Entry list
            $ExpenseEntry=ExpenseForInvoice::leftJoin("expense_entry","expense_for_invoice.expense_entry_id","=","expense_entry.id")
            ->leftJoin("users","users.id","=","expense_entry.user_id")->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")->select("expense_entry.*","task_activity.*","users.*","expense_entry.id as eid")->where("expense_entry.case_id",$case_id)
            // ->where("expense_entry.remove_from_current_invoice","no")
            ->where("expense_for_invoice.invoice_id",$invoiceID)
            ->get();

            //Get the Adjustment list
            $Adjustment_token = InvoiceAdjustment::where("invoice_adjustment.token",$request->token)->get();
            if($Adjustment_token->count() > 0 )
                $InvoiceAdjustment=InvoiceAdjustment::select("*")
                ->where("invoice_adjustment.token",$request->token)
                ->where("invoice_adjustment.case_id",$case_id)->get();
            else
                $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoiceID)->get();

            $InvoiceInstallment=InvoiceInstallment::select("*")
            ->where("invoice_installment.invoice_id",$invoiceID)
            ->get();
            

            
            //Get the flat fee Entry list
            $FlatFeeEntryForInvoice=FlatFeeEntryForInvoice::leftJoin("flat_fee_entry","flat_fee_entry_for_invoice.flat_fee_entry_id","=","flat_fee_entry.id")
            ->leftJoin("users","users.id","=","flat_fee_entry.user_id")
            ->select("flat_fee_entry.*","users.*","flat_fee_entry.id as itd")
            ->where("flat_fee_entry_for_invoice.invoice_id",$invoiceID)
            ->get();

            $SharedInvoice=SharedInvoice::select("*")->where("invoice_id",$invoiceID)->get()->pluck('user_id')->toArray();

            $adjustment_token=$request->token;

            // Get unpaid balances invoices list
            $unpaidInvoices = [];
            if($caseMaster) {
                $unpaidInvoices = Invoices::where("case_id", $caseMaster->id)->where("due_amount", ">", 0)->where("id", "!=", $findInvoice->id)->get();
            }

            return view('billing.invoices.edit_invoices',compact('ClientList','CompanyList','client_id','case_id','caseListByClient','caseMaster','TimeEntry','ExpenseEntry','InvoiceAdjustment','userData','UsersAdditionalInfo','getAllClientForSharing','adjustment_token','findInvoice','InvoiceInstallment','SharedInvoice','FlatFeeEntryForInvoice', 'unpaidInvoices'));
        }
    }

    public function updateInvoiceEntry(Request $request)
    {
        // return $request->all();
        $rules = [
            'invoice_number_padded' => 'required|numeric',
            'court_case_id' => 'required'/* |numeric */,
            'contact' => 'required|numeric',
            'total_text' => 'required',
            // 'timeEntrySelectedArray'=>'required_without:expenseEntrySelectedArray|array',
            // 'expenseEntrySelectedArray'=>'required_without:timeEntrySelectedArray|array',
        ];
        if(!empty($request->flatFeeEntrySelectedArray) && count($request->flatFeeEntrySelectedArray)) {
            $rules['timeEntrySelectedArray'] = 'nullable|array';
            $rules['expenseEntrySelectedArray'] = 'nullable|array';
        } else {
            $rules['timeEntrySelectedArray'] = 'required_without:expenseEntrySelectedArray|array';
            $rules['expenseEntrySelectedArray'] = 'required_without:timeEntrySelectedArray|array';
        }
        $request->validate($rules,
        [
            "invoice_number_padded.required"=>"Invoice number must be greater than 0",
            "invoice_number_padded.numeric"=>"Invoice number must be greater than 0",
            "contact.required"=>"Billing user can't be blank",
            "timeEntrySelectedArray.required"=>"You are attempting to save a blank invoice, please add time entries activity.",
            "expenseEntrySelectedArray.required"=>"You are attempting to save a blank invoice, please add expenses activity"
        ]);

            // print_r($request->all());exit;
            $InvoiceSave=Invoices::find($request->invoice_id);
            $InvoiceSave->user_id=$request->contact;
            $InvoiceSave->case_id=($request->court_case_id == "none") ? 0 : $request->court_case_id;
            $InvoiceSave->invoice_date=date('Y-m-d',strtotime($request->bill_invoice_date));
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
            }
            $InvoiceSave->is_sent = ($request->bill_sent_status == "Sent") ? "yes" : "no";
            $InvoiceSave->firm_id = auth()->user()->firm_name; 
            $InvoiceSave->updated_by=Auth::User()->id; 
            $InvoiceSave->updated_at=date('Y-m-d h:i:s'); 
            $InvoiceSave->save();

            session(['invoiceUpdate' => true]);

        // print_r($request->all());exit;


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
                    if(empty($request->linked_staff_checked_share) || !in_array($v,$request->linked_staff_checked_share)){
                        DB::table('task_time_entry')->where("id",$v)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                      
                    }

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
                    if(empty($request->invoice_expense_entry_nonbillable_time) || !in_array($v,$request->invoice_expense_entry_nonbillable_time)){
                       
                        DB::table('expense_entry')->where("id",$v)->update([
                            'status'=>'paid',
                            'invoice_link'=>$InvoiceSave->id
                        ]);
                    }
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

            if(isset($request->payment_plan) && isset($request->amount_per_installment_field) && isset($request->number_installment_field)){
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
                $InvoiceSave->forwardedInvoices()->sync([]);
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
            $invoice_id=$FindInvoice['id'];
            foreach($SharedInvoice as $k=>$v){
                $findUSer=User::find($v->user_id);
                $email=$findUSer['email'];
                $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];

                $firmData=Firm::find(Auth::User()->firm_name);
                $getTemplateData = EmailTemplate::find(16);
                $token=url('activate_account/bills='.base64_encode($email).'&web_token='.$FindInvoice['invoice_unique_token']);
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
            $invoiceData=Invoices::where("invoice_unique_token",$invoice_id)->first();
            if(!empty($invoiceData)){
                $firmData=Firm::find(Auth::User()->firm_name);
                $caseMaster=CaseMaster::select("case_title")->find($invoiceData['case_id']);
                $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$invoiceData['user_id'])->first();


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
               $entryDone= DB::table('invoice_payment')->insert([
                    'invoice_id'=>$invoiceId['id'],
                    'payment_from'=>'trust',
                    'amount_paid'=>$request->amount,
                    'payment_date'=>date('Y-m-d',strtotime($request->payment_date)),
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
                $lastInvoicePaymentId= DB::getPdo()->lastInsertId();
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();
               
                //Deduct invoice amount when payment done
                $totalPaid=InvoicePayment::where("invoice_id",$invoiceId['id'])->get()->sum("amount_paid");
                
                if(($totalPaid-$InvoiceData['total_amount'])==0){
                    $status="Paid";
                }else{
                    $status="Partial";
                }
                DB::table('invoices')->where("id",$invoiceId['id'])->update([
                    'paid_amount'=>$totalPaid,
                    'due_amount'=>($InvoiceData['total_amount'] - $totalPaid),
                    'status'=>$status,
                ]);

                // Deduct amount from trust account after payment.
                $trustAccountAmount=($userData['trust_account_balance']-$request->amount);
                UsersAdditionalInfo::where('user_id',$InvoiceData['user_id'])
                ->update(['trust_account_balance'=>$trustAccountAmount]);

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
                 $data['case_id']=$Invoices['case_id'];
                 $data['user_id']=$Invoices['user_id'];
                 $data['activity']='accepted a payment of $'.number_format($request->amount,2).' (Trust)';
                 $data['activity_for']=$Invoices['id'];
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
                return response()->json(['errors'=>["Server error."]]); //$e->getMessage()
                 exit;   
            }
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }

    public function saveInvoicePaymentWithHistory(Request $request)
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
               $entryDone= DB::table('invoice_payment')->insert([
                    'invoice_id'=>$invoiceId['id'],
                    'payment_from'=>'client',
                    'amount_paid'=>$request->amount,
                    'payment_method'=>$request->payment_method,
                    'deposit_into'=>$request->deposit_into,
                    'deposit_into_id'=>($request->trust_account)??NULL,
                    'notes'=>$request->notes,
                    'payment_date'=>date('Y-m-d',strtotime($request->payment_date)),
                    'notes'=>$request->notes,
                    'status'=>"0",
                    'entry_type'=>"1",
                    'total'=>$s,
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);

                $lastInvoicePaymentId= DB::getPdo()->lastInsertId();
                $InvoicePayment=InvoicePayment::find($lastInvoicePaymentId);
                $InvoicePayment->ip_unique_id=Hash::make($lastInvoicePaymentId);
                $InvoicePayment->save();

                //Deduct invoice amount when payment done
                // $totalPaid=InvoicePayment::where("invoice_id",$invoiceId['id'])->get()->sum("amount_paid");
                $allPayment = InvoicePayment::where("invoice_id", $invoiceId['id'])->get();
                $totalPaid = $allPayment->sum("amount_paid");
                $totalRefund = $allPayment->sum("amount_refund");
                $remainPaidAmt = ($totalPaid - $totalRefund);
                $dueAmount = ($InvoiceData['total_amount'] - $remainPaidAmt);
                /* if(($totalPaid-$InvoiceData['total_amount'])==0){
                    $status="Paid";
                }else{
                    $status="Partial";
                } */
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
                ]);

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

    public function refundPopup(Request $request)
    {
      
        $findEntry=InvoiceHistory::find($request->transaction_id);
        $findInvoice=Invoices::find($findEntry['invoice_id']);
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($findInvoice['user_id']);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$findInvoice['user_id'])->first();
        return view('billing.invoices.refundEntry',compact('userData','UsersAdditionalInfo','findEntry','findInvoice'));     
        exit;    
    } 
    public function saveRefundPopup(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $GetAmount=InvoiceHistory::find($request->transaction_id);
        $findInvoice=Invoices::find($GetAmount['invoice_id']);

        // if($GetAmount->deposit_into=="Operating Account"){
        //     $mt=$GetAmount->amount;
        // }
        $mt=$GetAmount->amount; 
    //  echo $request['amount'];exit;
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|max:'.$mt,
        ],[
            'amount.max' => 'Refund cannot be more than $'.number_format($mt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            DB::table('invoices')->where('id',$findInvoice['id'])->update(['status'=> 'Partial']);


            if($GetAmount['deposit_into']=="Trust Account"){
                DB::table('users_additional_info')->where('user_id',$GetAmount['deposit_into_id'])->decrement('trust_account_balance', $request['amount']);

                if($mt==$request['amount']){
                    $status="2";
                }else{
                    $status="3";
                }
                DB::table('invoice_history')->where('id',$request->transaction_id)->update(['status'=> $status]);

                $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$GetAmount['deposit_into_id'])->first();
            }else{
                if($mt==$request['amount']){
                    $status="2";
                }else{
                    $status="3";
                }
                DB::table('invoice_history')->where('id',$request->transaction_id)->update(['status'=> $status]);

            }

            $invoiceHistory=[];
            $invoiceHistory['invoice_id']=$findInvoice['id'];
            $invoiceHistory['acrtivity_title']='Payment Refund';
            if($GetAmount->deposit_into=="Operating Account"){
                $invoiceHistory['pay_method']="Refund";
            }else{
                $invoiceHistory['pay_method']="Trust Refund";
            }
            $invoiceHistory['amount']=$request['amount'];
            $invoiceHistory['responsible_user']=Auth::User()->id;
            $invoiceHistory['deposit_into']=NULL;
            $invoiceHistory['notes']=$request->notes;
            $invoiceHistory['status']="4";
            $invoiceHistory['refund_ref_id']=$request->transaction_id;
            $invoiceHistory['created_by']=Auth::User()->id;
            $invoiceHistory['created_at']=date('Y-m-d H:i:s');
            // $this->invoiceHistory($invoiceHistory);

            if($GetAmount->deposit_into=="Operating Account"){
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("deposit_into","Operating Account")->orderBy("created_at","DESC")->first();
                if($currentBalance['total']-$request->amount<=0){
                    $finalAmt=0;
                }else{
                    $finalAmt=$currentBalance['total']-$request->amount;
                }
                // $entryDone= DB::table('invoice_payment')->insert([
                $entryDone= DB::table('invoice_payment')->insertGetId([
                    'invoice_id'=>$findInvoice['id'],
                    'payment_from'=>'client',
                    'amount_refund'=>$request->amount,
                    'amount_paid'=>0.00,
                    'payment_method'=>"Refund",
                    'deposit_into'=>NULL,
                    'notes'=>$request->notes,
                    // 'refund_ref_id'=>$request->transaction_id, // payment history table reference id
                    'refund_ref_id'=>$GetAmount->invoice_payment_id,
                    'payment_date'=>date('Y-m-d',strtotime($request->payment_date)),
                    'notes'=>$request->notes,
                    'status'=>"1",
                    'entry_type'=>"1",
                    'total'=>$finalAmt,
                    'firm_id'=>Auth::User()->firm_name,
                    'ip_unique_id'=>Hash::make(time().rand(1,20000)),
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);
            }else{
                //Insert invoice payment record.
                $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("deposit_into","Trust Account")->orderBy("created_at","DESC")->first();
                if($currentBalance['total']-$request->amount<=0){
                    $finalAmt=0;
                }else{
                    $finalAmt=$currentBalance['total']-$request->amount;
                }
                // $entryDone= DB::table('invoice_payment')->insert([
                $entryDone= DB::table('invoice_payment')->insertGetId([
                    'invoice_id'=>$findInvoice['id'],
                    'payment_from'=>'trust',
                    'amount_refund'=>$request->amount,
                    'amount_paid'=>0.00,
                    'payment_method'=>"Trust Refund",
                    'deposit_into'=>NULL,
                    'notes'=>$request->notes,
                    // 'refund_ref_id'=>$request->transaction_id, // payment history table reference id
                    'refund_ref_id'=>$GetAmount->invoice_payment_id,
                    'payment_date'=>date('Y-m-d',strtotime($request->payment_date)),
                    'notes'=>$request->notes,
                    'status'=>"1",
                    'entry_type'=>"0",
                    'total'=>$finalAmt,
                    'ip_unique_id'=>Hash::make(time().rand(1,20000)),
                    'firm_id'=>Auth::User()->firm_name,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>Auth::user()->id 
                ]);
            }
            $invoiceHistory['invoice_payment_id']=$entryDone;
            $this->invoiceHistory($invoiceHistory);
            $allPayment = InvoicePayment::where("invoice_id", $findInvoice['id'])->get();
            $totalPaid = $allPayment->sum("amount_paid");
            $totalRefund = $allPayment->sum("amount_refund");
            $remainPaidAmt = ($totalPaid - $totalRefund);
            if($remainPaidAmt == 0) {
                $status="Unsent";
            } else {
                $status="Partial";
            }
            DB::table('invoices')->where("id", $findInvoice['id'])->update([
                'paid_amount'=>$remainPaidAmt,
                'due_amount'=>($findInvoice['total_amount'] - $remainPaidAmt),
                'status'=>$status,
            ]);

            session(['popup_success' => 'Withdraw fund successful']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function deletePaymentEntry(Request $request)
    {
        // return $request->all();
        $validator = \Validator::make($request->all(), [
            'payment_id' => 'required|numeric'
            
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $TrustInvoice=InvoiceHistory::find($request->payment_id);
            // $PaymentMaster=InvoiceHistory::find($TrustInvoice['refund_ref_id']);
            $PaymentMaster=InvoiceHistory::find($request->payment_id);
            if($PaymentMaster['deposit_into']=="Trust Account"){
                $IPayment=InvoicePayment::find($PaymentMaster['invoice_payment_id']);
                
                DB::table('users_additional_info')->where('user_id',$IPayment['deposit_into_id'])->decrement('trust_account_balance', $TrustInvoice->amount);
               
                $updateBalaance=UsersAdditionalInfo::where("user_id",$IPayment['deposit_into_id'])->first();
                if($updateBalaance['trust_account_balance']<=0){
                    DB::table('users_additional_info')->where('user_id',$IPayment['deposit_into_id'])->update(['trust_account_balance'=> "0.00"]);
                }
            }
            $PaymentMaster->status="1";
            $PaymentMaster->save();

            $invoicePayment = InvoicePayment::where("id", $PaymentMaster->invoice_payment_id)->first();
            if($invoicePayment && !in_array($invoicePayment->payment_method, ["Refund", "Trust Refund"])) {
                $invoicePayment->delete();
                $this->updateInvoiceAmount($PaymentMaster->invoice_id);
            } else {
                $refundPaymentReference = InvoiceHistory::whereId($PaymentMaster->refund_ref_id)->first();
                $refundPaymentReference->fill(["status" => "1"])->save();
                // return $refundPaymentReference;
                $invoicePayment->delete();
                $this->updateInvoiceAmount($PaymentMaster->invoice_id);
            }
            InvoiceHistory::where('id',$request->payment_id)->delete();
            session(['popup_success' => 'Entry was deleted']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    /**
     * Update invoice paid/due amount and status
     */
    public function updateInvoiceAmount($invoiceId)
    {
        $allPayment = InvoicePayment::where("invoice_id", $invoiceId)->get();
        $totalPaid = $allPayment->sum("amount_paid");
        $totalRefund = $allPayment->sum("amount_refund");
        $remainPaidAmt = ($totalPaid - $totalRefund);
        if($remainPaidAmt == 0) {
            $status="Unsent";
        } else {
            $status="Partial";
        }
        $invoice = Invoices::whereId($invoiceId)->first();
        $invoice->fill([
            'paid_amount'=> $remainPaidAmt,
            'due_amount'=> ($invoice->total_amount - $remainPaidAmt),
            'status'=>$status,
        ])->save();
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
        $pdf = new Pdf;
        $pdf->setOptions(['javascript-delay' => 5000]);
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->saveAs(public_path("download/pdf/".$filename));
        $path = public_path("download/pdf/".$filename);
        // return response()->download($path);
        // exit;
        return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
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

                $Invoice=Invoices::find($v);
                if(!in_array($Invoice->status,["Paid","Partial","Forwarded"])){
                    $Invoice->status=$request->status;
                    $Invoice->save();    
                }
            }
            session(['popup_success' => 'Selected invoices status has been updated.']);
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
        
            foreach($data as $k1=>$v1){
                    $Invoice=Invoices::find($v1);
                    if($request->current_action=="BC"){  //BC=Billing Contact only
                        $CaseClientSelection=CaseClientSelection::select("selected_user")->where("is_billing_contact","yes")->where("case_id",$Invoice['case_id'])->get()->pluck("selected_user");
                    }else{
                        $CaseClientSelection=CaseClientSelection::select("selected_user")->where("case_id",$Invoice['case_id'])->get()->pluck("selected_user");
                    }
                    if(!$CaseClientSelection->isEmpty()){
                        foreach($CaseClientSelection as $k=>$v){

                            $firmData=Firm::find(Auth::User()->firm_name);
                            $getTemplateData = EmailTemplate::find(12);
                            $token=url('activate_account/bills=&web_token='.$Invoice['invoice_unique_token']);

                            $mail_body = $getTemplateData->content;
                            $mail_body = str_replace('{message}', $request->message, $mail_body);
                            $mail_body = str_replace('{token}', $token, $mail_body);
                            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                            $clientData=User::find($v);
                            $user = [
                                "from" => FROM_EMAIL,
                                "from_title" => FROM_EMAIL_TITLE,
                                "subject" => $firmData->firm_name." has sent you an invoice",
                                "to" => $clientData->email,
                                "full_name" => "",
                                "mail_body" => $mail_body
                            ];
                            $sendEmail = $this->sendMail($user);
                        }
                    }
                }
            // session(['popup_success' => 'Selected invoices status has been updated.']);
            return response()->json(['errors'=>'']);
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

                        //Removed time entry id
                        $TimeEntryForInvoice=TimeEntryForInvoice::where("invoice_id",$v1)->get();
                        foreach($TimeEntryForInvoice as $k=>$v){
                            DB::table('task_time_entry')->where("id",$v->time_entry_id)->update([
                                'status'=>'unpaid',
                                'invoice_link'=>NULL
                            ]);

                            $deleteFromLinkTable=TimeEntryForInvoice::where("id",$v->time_entry_id)->delete();
                        }
                        //Removed expense entry
                        $ExpenseForInvoice=ExpenseForInvoice::where("invoice_id",$v1)->get();
                        foreach($ExpenseForInvoice as $k=>$v){
                            DB::table('expense_entry')->where("id",$v->expense_entry_id)->update([
                                'status'=>'unpaid',
                                'invoice_link'=>NULL
                            ]);
                            $ExpenseForInvoice=ExpenseForInvoice::where("id",$v->expense_entry_id)->delete();
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

                        // Update forwarded invoices
                        $forwardedInvoices = Invoices::whereIn("id", $Invoices->forwardedInvoices->pluck("id")->toArray())->get();
                        if($forwardedInvoices) {
                            foreach($forwardedInvoices as $key => $item) {
                                $this->updateInvoiceAmount($item->id);
                                array_push($nonDeletedInvoice, $item->id);
                            }
                        }

                        InvoicePaymentPlan::where("invoice_id",$v1)->delete();
                        InvoiceInstallment::where("invoice_id",$v1)->delete();
                        InvoicePayment::where("invoice_id",$v1)->delete();

                        // Delete Invoice
                        $Invoices->delete();
                    } else {
                        if(count($Invoices->invoiceForwardedToInvoice)) {
                            array_push($nonDeletedInvoice, $Invoices->id);
                        } else {
                            $this->updateInvoiceAmount($Invoices->id);
                        }
                    }
                }
            }
            // return $nonDeletedInvoice;
            // return $deletedIds = array_intersect($data, $nonDeletedInvoice);
            if(count($nonDeletedInvoice)) {
                $deletedIds = array_intersect($data, $nonDeletedInvoice);
                $nonDeleted = Invoices::whereIn("invoices.id",$deletedIds)->with("portalAccessUserAdditionalInfo")->get();
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
            $notSavedInvoice=[];
            foreach($data as $k1=>$v1){
                $Applied=FALSE;
                $Invoices=Invoices::find($v1);
                $CaseMaster=CaseMaster::find($Invoices['case_id']);
                $subTotal=$Invoices['total_amount'];

                $finalAmount=0;
                $InvoiceAdjustment = new InvoiceAdjustment;
                $InvoiceAdjustment->case_id =$Invoices['case_id'];
                $InvoiceAdjustment->token =NULL;
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
                    $CaseClientSelection=CaseClientSelection::select("selected_user","billing_amount")->where("billing_method","!=",NULL)->where("case_id",$Invoices['case_id'])->first();
                    $flatFees=($CaseClientSelection['billing_amount'])??0;
                    if($flatFees>0){
                        $InvoiceAdjustment->basis =str_replace(",","",$flatFees);
                        if($amountType=="percentage"){
                            $finalAmount=($amount/100)*$flatFees;
                        }else{
                            $finalAmount=$amount;
                        }
                        $Applied=TRUE;
                    }else{
                        $Applied=FALSE;
                    }
                }
                if($discount_applied_to=="time_entries"){
                    $TimeEntryForInvoice=TimeEntryForInvoice::join("task_time_entry","task_time_entry.id","=","time_entry_for_invoice.time_entry_id")->where("invoice_id",$v1)->get();
                    $GrandTotalByInvoice=$TotalAmt=0;
                    foreach($TimeEntryForInvoice as $kk=>$vv){
                        if($vv->rate_type=="hr"){
                            $TotalAmt=($vv->entry_rate*$vv->duration);
                        }else{
                            $TotalAmt=$vv->duration;
                        }
                        $GrandTotalByInvoice+=$TotalAmt;
                    }
                    $InvoiceAdjustment->basis =str_replace(",","",$GrandTotalByInvoice);
                    if($amountType=="percentage"){
                        $finalAmount=($amount/100)*$GrandTotalByInvoice;
                    }else{
                        $finalAmount=$amount;
                    }
                    $Applied=TRUE;
                }


                if($discount_applied_to=="expenses"){
                    $ExpenseForInvoice=ExpenseForInvoice::join("expense_entry","expense_entry.id","=","expense_for_invoice.expense_entry_id")->where("invoice_id",$v1)->get();
                    $GrandTotalByInvoiceExp=$TotalAmtExp=0;
                    foreach($ExpenseForInvoice as $kk1=>$vv1){
                        $TotalAmtExp=($vv1->cost*$vv1->duration);
                        $GrandTotalByInvoiceExp+=$TotalAmtExp;
                    }
                    $InvoiceAdjustment->basis =str_replace(",","",$GrandTotalByInvoiceExp);
                    if($amountType=="percentage"){
                        $finalAmount=($amount/100)*$GrandTotalByInvoiceExp;
                    }else{
                        $finalAmount=$amount;
                    }
                    $Applied=TRUE;
                }
                if($discount_applied_to=="sub_total"){
                    
                    if($subTotal>0){
                        $InvoiceAdjustment->basis =str_replace(",","",$subTotal);
                        if($amountType=="percentage"){
                            $finalAmount=($amount/100)*$subTotal;
                        }else{
                            $finalAmount=$amount;
                        }
                        $Applied=TRUE;
                    }else{
                        $Applied=FALSE;
                    }
                }
                
                if($discount_applied_to=="balance_forward_total"){
                    $InvoiceAdjustment->basis =str_replace(",","",$request->basic);
                    $Applied=TRUE;
                }
                
                $InvoiceAdjustment->amount =str_replace(",","",$finalAmount);
                $InvoiceAdjustment->notes =$notes;
                $InvoiceAdjustment->created_at=date('Y-m-d h:i:s'); 
                $InvoiceAdjustment->created_by=Auth::User()->id; 

                if($Applied==TRUE){
                    $InvoiceAdjustment->save();
                    if($discount_type=="discount"){
                        $subTotalSave=$subTotal-$finalAmount;
                        if($subTotalSave<0){
                            $subTotalSave=0;
                        }
                    }else{
                         $subTotalSave=$subTotal+$finalAmount;
                    }
                    $Invoices->total_amount=$subTotalSave;
                    // $Invoices->token=base64_decode($v1);
                    $Invoices->due_amount=$subTotalSave;
                    $Invoices->save();
                }else{
                    $notSavedInvoice[]='<li>'. sprintf('%06d', @$Invoices['id']).'('.@$CaseMaster['case_title'].')</li>';
                }
            }
            return response()->json(['errors'=>'','list'=> $notSavedInvoice]);
            exit;  
        } 
    }

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
            $data = json_decode(stripslashes($request->invoice_id));
            $notSavedInvoice=$savedInvoice=[];
            foreach($data as $k1=>$v1){
               
                $Invoices=Invoices::find($v1);
                $invoice_id=$Invoices['id'];
                $paid=$Invoices['paid_amount'];
                $invoice=$Invoices['total_amount'];
                $finalAmt=$invoice-$paid;

                $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$Invoices['user_id'])->first();

                //Get the trust account balance and invoice due amount
                if($userData['trust_account_balance']>0 && $Invoices->status != "Forwarded")
                {
                    if($finalAmt >= $userData['trust_account_balance'] ){
                        $trustAccountAmount=0;
                        $finalAmt = $userData['trust_account_balance'];
                    }else{
                        $trustAccountAmount=($userData['trust_account_balance']-$finalAmt);
                    }
                    //Insert invoice payment record.
                    $currentBalance=InvoicePayment::where("firm_id",Auth::User()->firm_name)->where("payment_from","trust")->orderBy("created_at","DESC")->first();
                                    
                    //Insert invoice payment record.
                    $entryDone= DB::table('invoice_payment')->insert([
                        'invoice_id'=>$invoice_id,
                        'payment_from'=>'trust',
                        'amount_paid'=>$finalAmt,
                        'payment_date'=>date('Y-m-d'),
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
                    $totalPaid=InvoicePayment::where("invoice_id",$invoice_id)->get()->sum("amount_paid");
                    
                    if(($Invoices['total_amount'] - $totalPaid) == 0){
                        $status="Paid";
                    }else{
                        $status="Partial";
                    }
                    DB::table('invoices')->where("id",$invoice_id)->update([
                        'paid_amount'=>$totalPaid,
                        'due_amount'=>($Invoices['total_amount'] - $totalPaid),
                        'status'=>$status,
                    ]);

                    // Deduct amount from trust account after payment.
                    UsersAdditionalInfo::where('user_id',$Invoices['user_id'])->update(['trust_account_balance'=>$trustAccountAmount]);

                    DB::commit();
                    //Response message
                    $firmData=Firm::find(Auth::User()->firm_name);
                    $msg="Thank you. Your payment of $".number_format($finalAmt,2)." has been sent to ".$firmData['firm_name']." ";
                    // all good


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
                    $this->invoiceHistory($invoiceHistory);


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
                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
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
                    $this->saveAccountActivity($activityHistory);

                    
                    //Get previous amount
                    $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
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
                    $this->saveAccountActivity($activityHistory);
                    $savedInvoice[]=$invoice_id;
                }else{
                    $notSavedInvoice[]=$invoice_id;
                }
            }
            return response()->json(['errors'=>'','savedInvoice'=>$savedInvoice,'notSavedInvoice'=>$notSavedInvoice]);
            exit;  
        } 
    }
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

            $SavedInvoices = Invoices::whereIn("id",$appliedInvoice)->with("case", "portalAccessUserAdditionalInfo")->get();
           
            $NonSavedInvoices=Invoices::select("case_master.case_title","invoices.id")->whereIn("invoices.id",$nonappliedInvoice);
            $NonSavedInvoices=$NonSavedInvoices->leftJoin("case_master","case_master.id","=","invoices.case_id");
            $NonSavedInvoices=$NonSavedInvoices->get();

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
        $FetchQuery = $FetchQuery->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $FetchQuery = $FetchQuery->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $FetchQuery 
        );
        echo json_encode($json_data);  
    }
    public function loadMixAccountActivity()
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
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
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
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","trust");
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        $totalData=$FetchQuery->count();
        $totalFiltered = $totalData; 

        $FetchQuery = $FetchQuery->offset($requestData['start'])->limit($requestData['length']);
        $FetchQuery = $FetchQuery->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $FetchQuery = $FetchQuery->get();
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
       
        $columns = array('id', 'title', 'default_description', 'flat_fees', 'firm_id','id','id','id','id','id',);
        $requestData= $_REQUEST;
        
        $FetchQuery = AccountActivity::leftJoin("users","account_activity.created_by","=","users.id")
        ->select('account_activity.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'),"users.id as uid");
        $FetchQuery = $FetchQuery->where("account_activity.firm_id",Auth::User()->firm_name);
        $FetchQuery = $FetchQuery->where("pay_type","trust");
       
        if(isset($requestData['range']) && $requestData['range']!=''){
            $cutDate=explode("-",$requestData['range']);
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        $FetchQuery = $FetchQuery->orderBy("id","DESC");
        $FetchQuery = $FetchQuery->get();
        
        $filename="trust_account_activity".time().'.pdf';
        $PDFData=view('billing.account_activity.trustAccountActivityPdf',compact('FetchQuery','requestData'));
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->saveAs(public_path("download/pdf/".$filename));
        $path = public_path("download/pdf/".$filename);
        return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
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
            $FetchQuery = $FetchQuery->whereBetween('entry_date', [date('Y-m-d',strtotime($cutDate[0])),date('Y-m-d',strtotime($cutDate[1]))]);
        }
        $FetchQuery = $FetchQuery->orderBy("id","DESC");

         $FetchQuery = $FetchQuery->get();
         
         $filename="account_activity".time().'.pdf';
        $PDFData=view('billing.account_activity.accountActivityPdf',compact('FetchQuery','requestData'));
         $pdf = new Pdf;
         // $pdf->setOptions(['javascript-delay' => 5000]);
         if($_SERVER['SERVER_NAME']=='localhost'){
             $pdf->binary = WKHTMLTOPDF_PATH;
         }
         $pdf->addPage($PDFData);
         // $pdf->setOptions(['javascript-delay' => 5000]);
         $pdf->saveAs(public_path("download/pdf/".$filename));
         $path = public_path("download/pdf/".$filename);
         // return response()->download($path);
         // exit;
         return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
         exit;
     }
    /********************************Account Activity ***************************** */

    /********************************Potential Case Invoice View ***************************** */
      public function viewPotentailInvoice(Request $request)
      {
          $invoiceID=base64_decode($request->id);
          // echo Hash::make($invoiceID);
          $findInvoice=PotentialCaseInvoice::find($invoiceID);
          if(empty($findInvoice) || $findInvoice->created_by!=Auth::User()->id)
          {
              return view('pages.404');
          }else{
              $LeadDetails=User::find($findInvoice['lead_id']);
              $firmData = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->first();
  
              $InvoiceHistory=InvoiceHistory::leftJoin("users","invoice_history.lead_id","=","users.id")->select("invoice_history.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as entered_by'))->where("lead_invoice_id",$invoiceID)->orderBy("invoice_history.id","DESC")->get();
  
              $lastEntry= $InvoiceHistory->first();
            
              $userMaster=User::find($findInvoice->user_id);
              
              $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoiceID)->get();
  
              $InvoiceHistoryTransaction=InvoiceHistory::where("lead_invoice_id",$invoiceID)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();
  
  
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
            $staffList = User::whereIn("user_level",[1,3])->where("firm_name",Auth::User()->firm_name)->orderBy("created_at","ASC")->get();
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
            $totalInvoice=[];
            $totalSent=$totalUnsent=$totalDraft=0;
            $allCases=json_decode($request->case_id);
            // print_r($allCases);exit;
            foreach($allCases as $caseVal){
                $caseClient = CaseMaster::leftJoin("case_client_selection","case_client_selection.case_id","=","case_master.id")->where("case_master.id",$caseVal)->where('case_client_selection.is_billing_contact','yes')->select("*")->first();

                $InvoiceSave=new Invoices;
                $InvoiceSave->id=$request->invoice_number_padded;
                $InvoiceSave->user_id=$caseClient['selected_user'];
                $InvoiceSave->case_id=$caseVal;
                $InvoiceSave->invoice_date=date('Y-m-d',strtotime($request->batch['invoice_date']));
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
                }else{
                    $InvoiceSave->status='Unsent';
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
                    $$TimeEntry=$TimeEntry->whereBetween("task_time_entry.entry_date",[date('Y-m-d',strtotime($request->batch['start_date'])),date('Y-m-d',strtotime($request->batch['end_date']))]);
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
                                    $token=url('firmuser/verify', $user->token);
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
                            $token=url('activate_account/bills=&web_token='.$InvoiceSave->invoice_unique_token);

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
                            $totalSent++;

                        }
                    }
                }
                $totalDraft++;
                $CaseClientSelection=CaseClientSelection::select("selected_user","billing_amount")->where("billing_method","!=",NULL)->where("case_id",$caseVal)->first();

                $flatFees=($CaseClientSelection['billing_amount'])??0;
                $subTotal=$flatFees+$timeEntryTotal+$expenseEntryTotal;
                if(isset($request->discounts['discount_type'])){
                    for($k=0;$k<count($request->discounts['discount_type']);$k++){
                        $finalAmount=0;
                        $InvoiceAdjustment = new InvoiceAdjustment;
                        $InvoiceAdjustment->case_id =$caseVal;
                        $InvoiceAdjustment->token =NULL;
                        $InvoiceAdjustment->invoice_id =$InvoiceSave->id;
                        $InvoiceAdjustment->item=$request->discounts['discount_type'][$k];
                        $InvoiceAdjustment->applied_to=$request->discounts['discount_applied_to'][$k];
                        $InvoiceAdjustment->ad_type=$request->discounts['discount_amount_type'][$k];
                        $InvoiceAdjustment->percentages =(float)$request->discounts['amount'][$k];

                        if($request->discounts['discount_applied_to'][$k]=="flat_fees"){
                            $InvoiceAdjustment->basis =str_replace(",","",$flatFees);
                            if($request->discounts['discount_amount_type'][$k]=="percentage"){
                                $finalAmount=($request->discounts['amount'][$k]/100)*$flatFees;
                            }else{
                                $finalAmount=$request->discounts['amount'][$k];
                            }

                        }
                        if($request->discounts['discount_applied_to'][$k]=="time_entries"){
                        $InvoiceAdjustment->basis =str_replace(",","",$timeEntryTotal);
                            if($request->discounts['discount_amount_type'][$k]=="percentage"){
                                
                                $finalAmount=($request->discounts['amount'][$k]/100)*$timeEntryTotal;
                            }else{
                                $finalAmount=$request->discounts['amount'][$k];
                            }
                        }
                        if($request->discounts['discount_applied_to'][$k]=="expenses"){
                        $InvoiceAdjustment->basis =str_replace(",","",$expenseEntryTotal);
                            if($request->discounts['discount_amount_type'][$k]=="percentage"){
                                $finalAmount=($request->discounts['amount'][$k]/100)*$expenseEntryTotal;
                            }else{
                                $finalAmount=$request->discounts['amount'][$k];
                            }
                        }
                        if($request->discounts['discount_applied_to'][$k]=="sub_total"){
                            $InvoiceAdjustment->basis =str_replace(",","",$subTotal);
                            if($request->discounts['discount_amount_type'][$k]=="percentage"){
                                $finalAmount=($request->discounts['amount'][$k]/100)*$subTotal;
                            }else{
                                $finalAmount=$request->discounts['amount'][$k];
                            }
                        }
                        
                        if($request->discounts['discount_applied_to'][$k]=="balance_forward_total"){
                            $InvoiceAdjustment->basis =str_replace(",","",$request->basic);
                        }
                    
                        $InvoiceAdjustment->amount =str_replace(",","",$finalAmount);
                        $InvoiceAdjustment->notes =$request->discounts['notes'][$k];
                        $InvoiceAdjustment->created_at=date('Y-m-d h:i:s'); 
                        $InvoiceAdjustment->created_by=Auth::User()->id; 
                        $InvoiceAdjustment->save();
                        
                        if($request->discounts['discount_type'][$k]=="discount"){
                            $subTotal=$subTotal-$finalAmount;
                        }else{
                            $subTotal=$subTotal+$finalAmount;
                        }
                    
                    }
                }
                $InvoiceSave->total_amount=$subTotal;
                $InvoiceSave->due_amount=$subTotal;
                $InvoiceSave->save();
                $totalInvoice[]=$InvoiceSave->id;
            }

            $InvoiceBatch=new InvoiceBatch;
            if(!empty($totalInvoice)){
                $InvoiceBatch->invoice_id=implode(",",$totalInvoice);
            }
            $InvoiceBatch->draft_invoice=$totalDraft;
            $InvoiceBatch->unsent_invoice=$totalUnsent;
            $InvoiceBatch->sent_invoice=$totalSent;
            $InvoiceBatch->batch_code=date('M, d Y')."-".count($totalInvoice);
            $InvoiceBatch->firm_id=Auth::User()->firm_name; 
            $InvoiceBatch->created_by=Auth::User()->id; 
            $InvoiceBatch->save();

            return response()->json(['errors'=>'','countInvoice'=>count($totalInvoice)]);
            exit;  

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
            $user_id=$request->logged_in_user;
            $userData = User::find($user_id);
            if(!empty($userData)){
                
                $CaseMasterClient = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as contact_name'),"id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();

                $CaseMasterCompany = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as contact_name'),"id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();

                return view('billing.dashboard.loadDepositIntoCredit',compact('CaseMasterClient','CaseMasterCompany'));
                exit;  
            }else{
                return response()->json(['errors'=>'error']);
            }
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
            $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid","users.user_level")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$user_id)->first();

            if(!empty($userData)){
                $firmData=Firm::find(Auth::User()->firm_name);
                $clientList = RequestedFund::select('requested_fund.*')->where("requested_fund.client_id",$user_id)->where("amount_due",">",0)->get();
                return view('billing.dashboard.depositNonTrustFundPopup',compact('userData','clientList'));
                exit;  
            }else{
                return response()->json(['errors'=>'error']);
            }
        }
    }

    public function saveDepositIntoNonTrustPopup(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);
        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required',
            'amount' => 'required|numeric|min:1',
            'non_trust_account' => 'required'
        ],[
            'amount.min'=>"Amount must be greater than $0.00"
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $DepositIntoCreditHistory=new DepositIntoCreditHistory;
            $DepositIntoCreditHistory->user_id=$request->non_trust_account;
            $DepositIntoCreditHistory->deposit_amount=$request->amount;
            $DepositIntoCreditHistory->firm_id=Auth::User()->firm_name;                
            $DepositIntoCreditHistory->created_by=Auth::User()->id;                
            $DepositIntoCreditHistory->created_at=date('Y-m-d H:i:s');                
            $DepositIntoCreditHistory->save();

            //Deposit into trust account
            $userDataForDeposit = UsersAdditionalInfo::select("trust_account_balance","user_id")->where("user_id",$request->non_trust_account)->first();
            DB::table('users_additional_info')->where("user_id",$request->non_trust_account)->update([
                'trust_account_balance'=>($userDataForDeposit['trust_account_balance'] + $request->amount),
            ]);

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
         $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
         ->leftJoin("case_master","invoices.case_id","=","case_master.id")
         ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.id as uid","users.user_level","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid")
         ->where("invoices.created_by",Auth::user()->id)
         ->where("invoices.status","!=","Paid")
         ->groupBy("users.id");
      
         $Invoices=$Invoices->get();

           return view('billing.dashboard.depositIntoTrust',compact('activityData','Invoices'));     
           exit;    
       }
    }  
    public function depositIntoTrustByCase(Request $request)
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
    }  

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
                $clientList = RequestedFund::select('requested_fund.*')->where("requested_fund.client_id",$user_id)->where("amount_due",">",0)->get();
                return view('billing.dashboard.depositTrustFundPopup',compact('userData','clientList'));
                exit;  
            }else{
                return response()->json(['errors'=>'error']);
            }
        }
    }
    public function saveDepositIntoTrustPopup(Request $request)
    {
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
        }else{
            if(isset($request->applied_to) && $request->applied_to!=0){
                $refundRequest=RequestedFund::find($request->applied_to);
                $refundRequest->amount_due=($refundRequest->amount_due-$request->amount);
                $refundRequest->amount_paid=($refundRequest->amount_paid+$request->amount);
                $refundRequest->save();
            }
            // $deposit_into_trust=new DepositIntoTrust;
            // $deposit_into_trust->user_id=$request->trust_account;
            // $deposit_into_trust->invoice_id=NULL;
            // if(isset($request->applied_to) && $request->applied_to!=0){
            //     $deposit_into_trust->requested_id=$request->applied_to;
            // }else{
            //     $deposit_into_trust->requested_id=NULL;
            // }
            // $deposit_into_trust->credit_amount=$request->amount;
            // $deposit_into_trust->debit_amount=0.00;
            // $deposit_into_trust->payment_date=date('Y-m-d',strtotime($request->payment_date));
            // $deposit_into_trust->status='unsent';                    
            // $deposit_into_trust->notes=$request->notes;
            // $deposit_into_trust->pay_type='trust';
            // $deposit_into_trust->created_by=Auth::User()->id;                
            // $deposit_into_trust->created_at=date('Y-m-d H:i:s');                
            // $deposit_into_trust->save();

            //Deposit into trust account
            DB::table('users_additional_info')->where('user_id',$request->trust_account)->increment('trust_account_balance', $request['amount']);

            $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->trust_account)->first();
            
            $TrustInvoice=new TrustHistory;
            $TrustInvoice->client_id=$request->trust_account;
            $TrustInvoice->payment_method=$request->payment_method;
            $TrustInvoice->amount_paid=$request->amount;
            $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
            $TrustInvoice->payment_date=date('Y-m-d',strtotime($request->payment_date));
            $TrustInvoice->notes=$request->notes;
            $TrustInvoice->fund_type='diposit';
            $TrustInvoice->created_by=Auth::user()->id; 
            $TrustInvoice->save();

            
            // $userDataForDeposit = UsersAdditionalInfo::select("trust_account_balance","user_id")->where("user_id",$request->trust_account)->first();
            // DB::table('users_additional_info')->where("user_id",$request->trust_account)->update([
            //     'trust_account_balance'=>($userDataForDeposit['trust_account_balance'] + $request->amount),
            // ]);

            // $deposit_into_trust->total_amount=$userDataForDeposit['trust_account_balance'] + $request->amount;
            // $deposit_into_trust->save();


             //Get previous amount
             $AccountActivityData=AccountActivity::select("*")->where("firm_id",Auth::User()->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
             $activityHistory=[];
             $activityHistory['user_id']=$request->trust_account;
             $activityHistory['case_id']=NULL;
             $activityHistory['credit_amount']=$request->amount;
             $activityHistory['debit_amount']=0.00;
             $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;
             $activityHistory['entry_date']=date('Y-m-d');
             $activityHistory['notes']=$request->notes;
             $activityHistory['status']="unsent";
             $activityHistory['pay_type']="trust";
             $activityHistory['firm_id']=Auth::user()->firm_name;
             if(isset($request->applied_to) && $request->applied_to!=0){
                $activityHistory['section']="request";
                $activityHistory['related_to']=$request->applied_to;
             }else{
                $activityHistory['section']="other";
                $activityHistory['related_to']=NULL;
            }
             $activityHistory['created_by']=Auth::User()->id;
             $activityHistory['created_at']=date('Y-m-d H:i:s');
             $this->saveAccountActivity($activityHistory);

             $data=[];
            $data['user_id']=$request->trust_account;
            $data['client_id']=$request->trust_account;
            $data['activity']="accepted a deposit into trust of $".number_format($request->amount,2)." (".$request->payment_method.") for";
            $data['type']='deposit';
            $data['action']='add';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            
            $firmData=Firm::find(Auth::User()->firm_name);
            $msg="Thank you. Your deposit of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
            
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }

    public function loadExpenseHistory(Request $request)
    {
  
        
        $commentData = AllHistory::join('users','users.id','=','all_history.created_by')
        ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
        ->where("all_history.type","expenses")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        return view('billing.dashboard.expenseHistory',compact('commentData'));
        exit;  
            
    }
    public function loadTimeEntryHistory(Request $request)
    {
  
        $commentData = AllHistory::join('users','users.id','=','all_history.created_by')
        ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
        ->where("all_history.type","time_entry")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        return view('billing.dashboard.TimeEntryHistory',compact('commentData'));
        exit;  
            
    } 
    
    public function loadInvoiceHistory(Request $request)
    {
  
        $commentData = AllHistory::join('users','users.id','=','all_history.created_by')
        ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->leftJoin('invoices','invoices.id','=','all_history.activity_for')
        ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number","invoices.deleted_at as deleteInvoice")
        ->where("all_history.type","invoices")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        return view('billing.dashboard.InvoiceHistory',compact('commentData'));
        exit;  
            
    }
    
    public function loadAllHistory(Request $request)
    {
  
        $commentData = AllHistory::join('users','users.id','=','all_history.created_by')
        ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->leftJoin('case_events','case_events.id','=','all_history.event_id')
        ->select("case_events.id as eventID","users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
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
        $InvoicesPaidAmount = Invoices::where("invoices.created_by",$id)->where("invoices.status","Paid")->whereBetween('invoices.invoice_date',[$startDate,$endDate])->where("invoices.created_by",$id)->sum("paid_amount");
           
        $InvoicesPaidPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status","Partial")->where("invoices.created_by",$id)->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("paid_amount");

        $InvoicesSentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Sent')->where("invoices.created_by",$id)->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("total_amount");

        $InvoicesDraftAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Draft')->where("invoices.created_by",$id)->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("total_amount");

        $InvoicesUnsentAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Unsent')->where("invoices.created_by",$id)->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("total_amount");

        $InvoicesPartialAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Partial')->where("invoices.created_by",$id)->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("paid_amount");
        
        $InvoicesOverdueAmount=Invoices::where("invoices.created_by",$id)->where("invoices.status",'Overdue')->where("invoices.created_by",$id)->whereBetween('invoices.invoice_date',[$startDate,$endDate])->sum("due_amount");
      
        
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
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();

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
         $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
 
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
    public function installmentManagement($paidAmt,$invoice_id){
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
    }
    
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

                    $firmData=Firm::find($userData['firm_name']);
                    $pdfData[$invoice_id]['firmData']=$firmData;

                    //Get the Adjustment list
                    $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->get();                    
                    $pdfData[$invoice_id]['InvoiceAdjustment']=$InvoiceAdjustment;

            
                    $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
                    $pdfData[$invoice_id]['InvoiceHistory']=$InvoiceHistory;

                    $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
                    $pdfData[$invoice_id]['InvoiceInstallment']=$InvoiceInstallment;

                    $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();
                    $pdfData[$invoice_id]['InvoiceHistoryTransaction']=$InvoiceHistoryTransaction;

            
                }

                $filename="Invoices_".time().'.pdf';
                 $PDFData=view('billing.invoices.viewBulkInvoicePdf',compact('pdfData'));
                $pdf = new Pdf;
                if($_SERVER['SERVER_NAME']=='localhost'){
                    $pdf->binary = EXE_PATH;
                }
                $pdf->addPage($PDFData);
                $pdf->setOptions(['javascript-delay' => 5000]);
                $pdf->saveAs(public_path("download/pdf/".$filename));
                $path = public_path("download/pdf/".$filename);
                return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
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
         $InvoiceAdjustment=InvoiceAdjustment::select("*")->where("invoice_adjustment.invoice_id",$invoice_id)->get();
 
         $InvoiceHistory=InvoiceHistory::where("invoice_id",$invoice_id)->orderBy("id","DESC")->get();
 
         $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoice_id)->get();
         $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id",$invoice_id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();
 
         $filename="Invoice_".$invoice_id.'.pdf';
         $PDFData=view('billing.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','Invoice','firmAddress','caseMaster','TimeEntryForInvoice','ExpenseForInvoice','InvoiceAdjustment','InvoiceHistory','InvoiceInstallment','InvoiceHistoryTransaction'));
         $pdf = new Pdf;
         if($_SERVER['SERVER_NAME']=='localhost'){
             $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
         }
         $pdf->addPage($PDFData);
         $pdf->setOptions(['javascript-delay' => 5000]);
         $pdf->saveAs(public_path("download/pdf/".$filename));
         $path = public_path("download/pdf/".$filename);
         // return response()->download($path);
         // exit;
         return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
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
        $loadFirmStaff = User::select("first_name","last_name","id","user_title")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        return view('billing.loadCaseList', compact('CaseMasterData','case_id'));
       
    }
    /**
     * Get invoice payment history
     */
    public function invoicePaymentHistory(Request $request)
    {
        $InvoiceHistoryTransaction=InvoiceHistory::where("invoice_id", $request->id)->whereIn("acrtivity_title",["Payment Received","Payment Refund"])->orderBy("id","DESC")->get();
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
        } else if($request->check_type == "flat") {
            $checkEntry = FlatFeeEntry::whereId($request->id)->first();
        } else if($request->check_type == "expense") {
            $checkEntry = ExpenseEntry::whereId($request->id)->first();
        } else {
            $checkEntry = "";
        }
        if($checkEntry) {
            $checkEntry->update(["time_entry_billable" => $request->is_check]);
        } else {
            return response()->json(["status" => "error", 'msg' => "No record found"]);
        }
        return response()->json(['status' => "success", 'msg' => "Record updated"]);
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
}
  