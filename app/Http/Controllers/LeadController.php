<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\LeadStatus,App\ReferalResource;
use App\LeadAdditionalInfo,App\NotHireReasons;
use App\ContractUserCase,App\CaseMaster,App\ContractUserPermission,App\ContractAccessPermission;
use App\DeactivatedUser,App\TempUserSelection,App\CasePracticeArea,App\CaseStage,App\CaseClientSelection;
use App\CaseStaff,App\CaseUpdate,App\CaseStageUpdate,App\CaseActivity,App\UsersAdditionalInfo;
use App\CaseEvent,App\CaseEventLocation,App\EventType;
use Carbon\Carbon,App\CaseEventReminder,App\CaseEventLinkedStaff;
use App\Http\Controllers\CommonController,App\CaseSolReminder;
use DateInterval,DatePeriod,App\CaseEventComment;
use App\Task,App\CaseTaskReminder,App\CaseTaskLinkedStaff,App\TaskChecklist;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\IntakeForm,App\Firm,App\IntakeFormFields;
use App\LeadNotesActivity,App\LeadNotes,App\LeadNotesActivityHistory;
use App\LeadCaseActivityHistory,App\CaseNotes,App\CaseIntakeForm;
use App\CaseIntakeFormFieldsData,App\PotentialCaseInvoice;
use mikehaertl\wkhtmlto\Pdf;
// use PDF;
use App\Calls,App\FirmAddress,App\PotentialCaseInvoicePayment,App\OnlineLeadSubmit;
class LeadController extends BaseController
{
    public function __construct()
    {
       
    }
    public function index()
    {
      
        $LeadStatus=LeadStatus::where("firm_id",Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        $allLEadByGroup=[];
        $extraInfo=[];
        foreach($LeadStatus as $k=>$v){
            $allLeads = User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id');
            $allLeads = $allLeads->select("users.*","lead_additional_info.*");
            $allLeads = $allLeads->where("users.user_type","5");
            $allLeads = $allLeads->where("users.user_level","5");
            $allLeads = $allLeads->where("lead_additional_info.is_converted","no");
            $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
            $allLeads = $allLeads->where("lead_additional_info.lead_status",$v->id);
            $allLeads = $allLeads->where("lead_additional_info.deleted_at",NULL);
            $allLeads = $allLeads->where("lead_additional_info.do_not_hire_reason",NULL);
            
            if(isset($_GET['ld']) && $_GET['ld']!=''){
                $allLeads = $allLeads->where("lead_additional_info.user_id",$_GET['ld']);
            }
            if(isset($_GET['pa']) &&$_GET['pa']!=''){
                $allLeads = $allLeads->where("lead_additional_info.practice_area",$_GET['pa']);
            }
            if(isset($_GET['ol']) &&$_GET['ol']!=''){
                $allLeads = $allLeads->where("lead_additional_info.office",$_GET['ol']);
            }
            if(isset($_GET['at']) && $_GET['at']!=''){
                if($_GET['at']=='unassigned'){
                    $allLeads = $allLeads->where("lead_additional_info.assigned_to",NULL);
                }elseif($_GET['at']=='me'){
                    $allLeads = $allLeads->where("lead_additional_info.assigned_to",Auth::User()->id);
                } 
            }
            $allLeads = $allLeads->orderBy("lead_additional_info.sort_order",'ASC');
            $allLeads = $allLeads->get();
           
            $allLEadByGroup[$v->id]=$allLeads;
            $extraInfo[$v->id]['sum']=$allLeads->sum('potential_case_value'); 
            $extraInfo[$v->id]['totalLeads']=$allLeads->count(); 
        }
        
        $allLeadsDropdown = LeadAdditionalInfo::leftJoin('users','lead_additional_info.user_id','=','users.id')
            ->select("users.*")
            ->where("users.user_type","5")
            ->where("users.user_level","5")
            ->where("users.firm_name",Auth::User()->firm_name)
            ->groupBy("lead_additional_info.user_id")
            ->get();

          
        $allPracticeAreaDropdown = LeadAdditionalInfo::leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id')
            ->select("case_practice_area.*","lead_additional_info.practice_area")
            ->groupBy("lead_additional_info.practice_area")
            ->get();

            $leadCount = OnlineLeadSubmit::where("firm_id",Auth::User()->firm_name)->count();

            
        // print_r($allPracticeAreaDropdown);exit;
         return view('lead.index',compact('LeadStatus','allLEadByGroup','extraInfo','allLeadsDropdown','allPracticeAreaDropdown','leadCount'));
    }
    public function active()
    {
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->pluck('title','id');
        $allLeadsDropdown = LeadAdditionalInfo::leftJoin('users','lead_additional_info.user_id','=','users.id')
        ->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))
        ->where("users.user_type","5")
        ->where("users.user_level","5")
        ->where("lead_additional_info.is_converted","no")
        ->where("users.firm_name",Auth::User()->firm_name)
        ->where("lead_additional_info.user_status","1") //1 :Active
        ->groupBy("lead_additional_info.user_id")
        ->get();
        $leadCount = OnlineLeadSubmit::where("firm_id",Auth::User()->firm_name)->count();
        return view('lead.active',compact('ReferalResource','allLeadsDropdown','leadCount'));
    }
    public function loadActive()
    {   

        $columns = array('users.id','users.id','users.id','created_by_name','res_title' ,'status_title','practice_area_title','potential_case_value','assigned_to_name','lead_additional_info.created_at');
        $requestData= $_REQUEST;
        
        $allLeads = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id');
        $allLeads = $allLeads->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
        $allLeads = $allLeads->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
        $allLeads = $allLeads->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
        $allLeads = $allLeads->leftJoin('users as uu','lead_additional_info.assigned_to','=','uu.id');
        $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),DB::raw('CONCAT_WS(" ",uu.first_name,uu.middle_name,uu.last_name) as assigned_to_name'));
        $allLeads = $allLeads->where("users.user_type","5");
        $allLeads = $allLeads->where("users.user_level","5");
        $allLeads = $allLeads->where("lead_additional_info.is_converted","no");
        $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
        $allLeads = $allLeads->where("lead_additional_info.user_status","1");  //1:Active

        if($requestData['id']!=''){
            $allLeads = $allLeads->where("lead_additional_info.user_id",$requestData['id']);  //1:Active
        }
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }

    public function loadLocation()
    {   

        $columns = array('id', 'location_name');
        $requestData= $_REQUEST;
        
        $CaseEventLocation = CaseEventLocation::leftJoin("users","case_event_location.created_by","=","users.id")->leftJoin('countries','case_event_location.country',"=","countries.id")->select('case_event_location.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid",'countries.name');
        
        $totalData=$CaseEventLocation->count();
        $totalFiltered = $totalData; 
        if( !empty($requestData['search']['value']) ) {   
            $CaseEventLocation = $CaseEventLocation->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(address1, " ", address2)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('location_name ', 'like', "%".$requestData['search']['value']."%" );
                });
            });
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $CaseEventLocation->count(); 
        }
        $CaseEventLocation = $CaseEventLocation->offset($requestData['start'])->limit($requestData['length']);
        $CaseEventLocation = $CaseEventLocation->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $CaseEventLocation = $CaseEventLocation->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $CaseEventLocation 
        );
        echo json_encode($json_data);  
    }

    public function loadAddLocationPopup()
    {
        $country = Countries::get();
        return view('location.loadAddLocationPopup',compact('country'));
    }
    public function saveAddLocationPopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseEventLocation=new CaseEventLocation;
            $CaseEventLocation->location_name=$request->location_name; 
            $CaseEventLocation->address1=$request->address1;
            $CaseEventLocation->address2=$request->address2;
            $CaseEventLocation->city=$request->city;
            $CaseEventLocation->state=$request->state;
            $CaseEventLocation->postal_code=$request->zip;
            $CaseEventLocation->country=$request->country;
            $CaseEventLocation->location_future_use='yes';
            $CaseEventLocation->created_by =Auth::User()->id;
            $CaseEventLocation->save();
            return response()->json(['errors'=>'','id'=>$CaseEventLocation->id]);
            exit;
        }
    }

    public function loadEditLocationPopup(Request $request)
    {
        $country = Countries::get();
        $CaseEventLocation=CaseEventLocation::find($request->id);
        return view('location.loadEditLocationPopup',compact('country','CaseEventLocation'));
    }
    public function saveEditLocationPopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseEventLocation=CaseEventLocation::find($request->id);
            $CaseEventLocation->location_name=$request->location_name; 
            $CaseEventLocation->address1=$request->address1;
            $CaseEventLocation->address2=$request->address2;
            $CaseEventLocation->city=$request->city;
            $CaseEventLocation->state=$request->state;
            $CaseEventLocation->postal_code=$request->zip;
            $CaseEventLocation->country=$request->country;
            $CaseEventLocation->updated_by =Auth::User()->id;
            $CaseEventLocation->save();
            return response()->json(['errors'=>'','id'=>$CaseEventLocation->id]);
            exit;
        }
    }

    public function deleteLocation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            CaseEvent::where('event_location_id',$request->location_id)->update(['event_location_id'=>NULL]);
            CaseEventLocation::where('id',$request->location_id)->delete();
            return response()->json(['errors'=>'','id'=>$request->location_id]);
            exit;
        }
        
    }
    public function addLead()
    {
        $country = Countries::get();
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $LeadStatus=LeadStatus::select('*')->where('firm_id',Auth::User()->firm_name)->orderBy('status_order','ASC')->get();
     
        $getChildUsers=$this->getParentAndChildUserIds();
        $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
        $CaseMasterClient = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        
        $firmStaff = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',3)->where("parent_user",Auth::user()->id)->orWhere("id",Auth::user()->id)->get();
       
        return view('lead.addLead',compact('country','ReferalResource','LeadStatus','CasePracticeArea','CaseMasterClient','CaseMasterCompany','firmStaff'));
    }
    public function checkUserEmail(Request $request) {
        $contacts = User::where('email',$request->email)->count();
        if ($contacts == 0) {
            return 'true';
        } else {
            return 'false';
        }
    }
    public function saveLead(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'middle_name' => 'max:255',
            'last_name' => 'required|max:255',
            // 'email' => 'required|unique:users,email,NULL,id,deleted_at,NULL',
            'email' => 'nullable|email|unique:users,email,NULL,id,firm_name,'.Auth::User()->firm_name,

            ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $UserMaster = new User;
            $UserMaster->first_name=$request->first_name;
            $UserMaster->middle_name=$request->middle_name;
            $UserMaster->last_name=$request->last_name;
            $UserMaster->email=$request->email;
            $UserMaster->mobile_number=$request->cell_phone;
            $UserMaster->work_phone=$request->work_phone;
            $UserMaster->home_phone=$request->home_phone;
            $UserMaster->street=$request->address;
            $UserMaster->city=$request->city;
            $UserMaster->state=$request->state;
            $UserMaster->postal_code=$request->postal_code;
            $UserMaster->country=$request->country;
            $UserMaster->password='';
            $UserMaster->firm_name=Auth::User()->firm_name;
            $UserMaster->token  = Str::random(40);
            $UserMaster->user_type='5';  // 5  :Lead
            $UserMaster->user_level='5'; // 5  :Lead
            $UserMaster->user_title='';
            $UserMaster->user_timezone=Auth::User()->user_timezone;
            $UserMaster->parent_user =Auth::User()->id;
            $UserMaster->created_by =Auth::User()->id;
            $UserMaster->save();

            $LeadAdditionalInfoMaster = new LeadAdditionalInfo;
            $LeadAdditionalInfoMaster->user_id=$UserMaster->id;
            $LeadAdditionalInfoMaster->address2=$request->address2;
            $LeadAdditionalInfoMaster->dob=($request->dob) ? date('Y-m-d',strtotime($request->dob)) : NULL;
            $LeadAdditionalInfoMaster->driver_license=$request->driver_license;
            $LeadAdditionalInfoMaster->license_state=$request->driver_state;

            if($request->referal_source_text!=''){
                $ReferalResource=new ReferalResource;
                $ReferalResource->title=$request->referal_source_text;
                $ReferalResource->status="1";
                $ReferalResource->stage_order=ReferalResource::where('firm_id',Auth::User()->firm_name)->max('stage_order') + 1;
                $ReferalResource->firm_id=Auth::User()->firm_name;
                $ReferalResource->save();
                $LeadAdditionalInfoMaster->referal_source=$ReferalResource->id;
            }else{
                $LeadAdditionalInfoMaster->referal_source=$request->referal_source;
            }
            $LeadAdditionalInfoMaster->refered_by=$request->refered_by;
            $LeadAdditionalInfoMaster->lead_detail=$request->lead_detail;
            $LeadAdditionalInfoMaster->potential_case_title="Potential Case: ".$request->first_name. " ".$request->middle_name." ".$request->last_name;
            $LeadAdditionalInfoMaster->date_added=($request->date_added) ? date('Y-m-d',strtotime($request->date_added)) : NULL;
            $LeadAdditionalInfoMaster->lead_status=$request->lead_status;
            $LeadAdditionalInfoMaster->practice_area=$request->practice_area;
            $LeadAdditionalInfoMaster->potential_case_value=str_replace(",","",$request->potential_case_value);
            $LeadAdditionalInfoMaster->assigned_to=$request->assigned_to;
            $LeadAdditionalInfoMaster->office="1";
            $LeadAdditionalInfoMaster->potential_case_description=$request->notes;
            $LeadAdditionalInfoMaster->user_status='1';
            $LeadAdditionalInfoMaster->conflict_check=($request->conflict_check)?'yes':'no';
            if($LeadAdditionalInfoMaster->conflict_check=="yes"){
             $LeadAdditionalInfoMaster->conflict_check_at=date('Y-m-d h:i:s');
            }
            $LeadAdditionalInfoMaster->conflict_check_description=$request->conflict_check_description;
            $LeadAdditionalInfoMaster->notes=$request->notes;
            $LeadAdditionalInfoMaster->sort_order=LeadAdditionalInfo::where('firm_id',Auth::User()->firm_name)->where('lead_status',$request->lead_status)->max('sort_order') + 1;
            $LeadAdditionalInfoMaster->created_by =Auth::User()->id;
            $LeadAdditionalInfoMaster->save();

            $noteHistory=[];
            $noteHistory['acrtivity_title']='added a lead';
            $noteHistory['activity_by']=Auth::User()->id;
            $noteHistory['for_lead']=$UserMaster->id;
            $this->noteActivity($noteHistory);
            session(['popup_success' => 'Your lead has been created.']);

        }
        return response()->json(['errors'=>'','user_id'=>$UserMaster->id]);
        exit;
    }

    public function changeLeadOrder(Request $request)
    {
        $order=0;
        LeadAdditionalInfo::where('user_id',$request->lead_id)->update(['lead_status'=>$request->target_board,'sort_order'=>$request->new_index]);
       

        $leadData=LeadAdditionalInfo::where('firm_id',Auth::User()->firm_name)->where('lead_status',$request->target_board)->orderBy("sort_order","ASC")->get();
        
        if(!$leadData->isEmpty()){
            foreach($leadData as $k=>$v){
                if($request->new_index!=$order){
                    LeadAdditionalInfo::where('id',$v->id)->update(['sort_order'=>$order]);
                }
                $order++;
            }
        }
      

        return response()->json(['errors'=>'','lead_id'=>$request->lead_id]);
        exit;
    }

    public function reorderStages(Request $request)
    {
        $i = 1;
        foreach ($request['item'] as $value) {
            LeadStatus::where('id',$value)->where('firm_id',Auth::User()->firm_name)->update(['status_order' => $i]);
            $i++;
        }
        return response()->json(['errors'=>'']);
        exit;
    }

    public function addStatus()
    {
        return view('lead.addStatus');
    }

    public function saveStatus(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'status_name' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $existStatus=LeadStatus::select('id')->where('title',$request->status_name)->where("firm_id",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $LeadStatus = new LeadStatus;
                $LeadStatus->title=$request->status_name;
                $LeadStatus->firm_id=Auth::User()->firm_name;
                $LeadStatus->status_order=LeadStatus::where('firm_id',Auth::User()->firm_name)->max('status_order') + 1;
                $LeadStatus->save();
                session(['popup_success' => 'Your status has been added.']);
                return response()->json(['errors'=>'','LeadStatus'=>$LeadStatus->id]);
                exit;
            }else{
                $CustomError[]='Status already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
          
        }
        
    }
    public function editStatus(Request $request)
    {
        $LeadStatus=LeadStatus::find($request->status_id);
        return view('lead.editStatus',compact('LeadStatus'));
    }
    public function updateStatus(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'status_name' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $existStatus=LeadStatus::select('id')->where('id',"!=",$request->id)->where('title',$request->status_name)->where("firm_id",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $LeadStatus = LeadStatus::find($request->id);
                $LeadStatus->title=$request->status_name;$LeadStatus->save();
                session(['popup_success' => 'Your status has been updated.']);
                return response()->json(['errors'=>'','LeadStatus'=>$LeadStatus->id]);
                exit;
            }else{
                $CustomError[]='Status already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
          
        }
        
    }

    public function deleteStatus(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'status_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            LeadAdditionalInfo::where('lead_status',$request->status_id)->update(['lead_status'=>NULL]);
            LeadStatus::where('id',$request->status_id)->delete();
            return response()->json(['errors'=>'','id'=>$request->status_id]);
            exit;
        }
        
    }

    //LEad Setting

    public function leadSetting()
    {
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $LeadStatus=LeadStatus::where("firm_id",Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        $HireReason=NotHireReasons::where("firm_id",Auth::User()->firm_name)->get();
        return view('lead_setting.index',compact('ReferalResource','LeadStatus','HireReason'));
    }

    public function addReferalSource()
    {
        return view('lead_setting.add_referal');
    }

    public function saveReferalSource(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'lead_referral_source' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $existStatus=ReferalResource::select('id')->where('title',$request->lead_referral_source)->where("firm_id",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $ReferalResource = new ReferalResource;
                $ReferalResource->title=$request->lead_referral_source;
                $ReferalResource->firm_id=Auth::User()->firm_name;
                $ReferalResource->stage_order=ReferalResource::where('firm_id',Auth::User()->firm_name)->max('stage_order') + 1;
                $ReferalResource->save();
                session(['popup_success' => 'Your lead referral source has been added.']);
                return response()->json(['errors'=>'','ReferalResource'=>$ReferalResource->id]);
                exit;
            }else{
                $CustomError[]='Lead referral source already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
          
        }
        
    }

    public function editReferalResource(Request $request)
    {
        $ReferalResource=ReferalResource::find($request->id);
        return view('lead_setting.edit_referal',compact('ReferalResource'));
    }

    public function updateReferalSource(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'lead_referral_source' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $existStatus=ReferalResource::select('id')->where('title',$request->lead_referral_source)->where("firm_id",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $ReferalResource = ReferalResource::find($request->id);
                $ReferalResource->title=$request->lead_referral_source;
                $ReferalResource->save();
                session(['popup_success' => 'Your lead referral source has been updated.']);
                return response()->json(['errors'=>'','ReferalResource'=>$ReferalResource->id]);
                exit;
            }else{
                $CustomError[]='Lead referral source already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
          
        }
        
    }
    public function deleteReferalSource(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'referral_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            ReferalResource::where('id',$request->referral_id)->delete();
            session(['popup_success' => 'Your lead referral source has been deleted.']);

            return response()->json(['errors'=>'','id'=>$request->status_id]);
            exit;
        }
        
    }

    public function addReason()
    {
        return view('lead_setting.addReason');
    }

    public function saveReason(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'no_hire_reason_name' => 'required|max:60',

        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $existStatus=NotHireReasons::select('id')->where('title',$request->no_hire_reason_name)->where("firm_id",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $NotHireReasons = new NotHireReasons;
                $NotHireReasons->title=$request->no_hire_reason_name;
                $NotHireReasons->firm_id=Auth::User()->firm_name;
                $NotHireReasons->save();
                session(['popup_success' => 'Your no hire reason has been added.']);
                return response()->json(['errors'=>'','NotHireReasons'=>$NotHireReasons->id]);
                exit;
            }else{
                $CustomError[]='No hire reason already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
          
        }
        
    }
    public function editReason(Request $request)
    {
        $NotHireReasons=NotHireReasons::find($request->id);
        return view('lead_setting.editReason',compact('NotHireReasons'));
    }
    public function updateReason(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'no_hire_reason_name' => 'required|max:60',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $existStatus=NotHireReasons::select('id')->where('id',"!=",$request->id)->where('title',$request->no_hire_reason_name)->where("firm_id",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $NotHireReasons = NotHireReasons::find($request->id);
                $NotHireReasons->title=$request->no_hire_reason_name;
                $NotHireReasons->save();
                session(['popup_success' => 'Your no hire reason has been updated.']);
                return response()->json(['errors'=>'','NotHireReasons'=>$NotHireReasons->id]);
                exit;
            }else{
                $CustomError[]='No hire reason already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
          
        }
        
    }

    public function deleteReason(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'reason_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            NotHireReasons::where('id',$request->reason_id)->delete();
            session(['popup_success' => 'No hire reason was deleted.']);

            return response()->json(['errors'=>'','id'=>$request->status_id]);
            exit;
        }
        
    }

    public function changeReferalResource(Request $request)
    {
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $id=$request->id;  
        $referal_source=$request->referal_source;
        
        return view('lead.changeReferal',compact('ReferalResource','id','referal_source'));
    }
    public function changeSaveReferalResource(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'referal_source' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $LeadAdditionalInfo = LeadAdditionalInfo::find($request->id);
            $LeadAdditionalInfo->referal_source=$request->referal_source;
            $LeadAdditionalInfo->save();
            return response()->json(['errors'=>'','LeadAdditionalInfo'=>$LeadAdditionalInfo->id]);
            exit;
        }
        
    }

    public function doNotHire(Request $request)
    {
        $id=$request->id;
        $HireReason=NotHireReasons::where("firm_id",Auth::User()->firm_name)->get();

        return view('lead.doNotHire',compact('HireReason','id'));
    }

    public function SavedoNotHire(Request $request)
    {
        if($request->not_hire_reasons_text!=""){
            $validator = \Validator::make($request->all(), [
                'not_hire_reasons_text' => 'required'
            ]);
        }else{
            $validator = \Validator::make($request->all(), [
                'not_hire_reasons_id' => 'required'
            ]);
        }
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $LeadAdditionalInfo = LeadAdditionalInfo::where('user_id',$request->id)->first();
            if($request->not_hire_reasons_text!=""){
                $existStatus=NotHireReasons::select('id')->where('title',$request->not_hire_reasons_text)->where("firm_id",Auth::User()->firm_name)->count();
                if($existStatus=="0"){
                    $NotHireReasons = new NotHireReasons;
                    $NotHireReasons->title=$request->not_hire_reasons_text;
                    $NotHireReasons->firm_id=Auth::User()->firm_name;
                    $NotHireReasons->created_by=Auth::User()->id;
                    $NotHireReasons->save();
                    $LeadAdditionalInfo->do_not_hire_reason=$NotHireReasons->id;
                }else{
                    $CustomError[]='No hire reason already exists';
                    return response()->json(['errors'=>$CustomError]);
                    exit;
                }
            }else{
                $LeadAdditionalInfo->do_not_hire_reason=$request->not_hire_reasons_id;
            }
            $LeadAdditionalInfo->do_not_hire_on=date('Y-m-d');
            $LeadAdditionalInfo->user_status="2";  //2:Do Not Hire
            $LeadAdditionalInfo->save();
            return response()->json(['errors'=>'','LeadAdditionalInfo'=>$LeadAdditionalInfo->id]);
            exit;
        }
    }

    public function editLead(Request $request)
    {
        $id=$request->id;
        $UserMaster=User::find($id);
        $LeadAdditionalInfo=LeadAdditionalInfo::where("user_id",$id)->first();
        $country = Countries::get();
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $LeadStatus=LeadStatus::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        
        $getChildUsers=$this->getParentAndChildUserIds();
        $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
        $CaseMasterClient = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        
        $firmStaff = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',3)->where("parent_user",Auth::user()->id)->orWhere("id",Auth::user()->id)->get();
       
        return view('lead.editLead',compact('UserMaster','LeadAdditionalInfo','country','ReferalResource','LeadStatus','CasePracticeArea','CaseMasterClient','CaseMasterCompany','firmStaff'));
    }
    public function updateLead(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'middle_name' => 'max:255',
            'email' => 'nullable|unique:users,email,'.$request->id.',id,deleted_at,NULL',

            ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $UserMaster =User::firstOrNew(array('id' => $request->id));
            $UserMaster->first_name=$request->first_name;
            $UserMaster->middle_name=$request->middle_name;
            $UserMaster->last_name=$request->last_name;
            $UserMaster->email=$request->email;
            $UserMaster->mobile_number=$request->cell_phone;
            $UserMaster->work_phone=$request->work_phone;
            $UserMaster->home_phone=$request->home_phone;
            $UserMaster->street=$request->address;
            $UserMaster->city=$request->city;
            $UserMaster->state=$request->state;
            $UserMaster->postal_code=$request->postal_code;
            $UserMaster->country=$request->country;
            $UserMaster->user_title='';
            $UserMaster->save();

            $LeadAdditionalInfoMaster =LeadAdditionalInfo::firstOrNew(array('id' => $request->user_id));
            $LeadAdditionalInfoMaster->address2=$request->address2;
            $LeadAdditionalInfoMaster->dob=($request->dob) ? date('Y-m-d',strtotime($request->dob)) : NULL;
            $LeadAdditionalInfoMaster->driver_license=$request->driver_license;
            $LeadAdditionalInfoMaster->license_state=$request->driver_state;

            if($request->referal_source_text!=''){
                $ReferalResource=new ReferalResource;
                $ReferalResource->title=$request->referal_source_text;
                $ReferalResource->status="1";
                $ReferalResource->stage_order=ReferalResource::where('firm_id',Auth::User()->firm_name)->max('stage_order') + 1;
                $ReferalResource->firm_id=Auth::User()->firm_name;
                $ReferalResource->save();
                $LeadAdditionalInfoMaster->referal_source=$ReferalResource->id;
            }else{
                $LeadAdditionalInfoMaster->referal_source=$request->referal_source;
            }
            $LeadAdditionalInfoMaster->refered_by=$request->refered_by;
            $LeadAdditionalInfoMaster->lead_detail=$request->lead_detail;

            if(!isset($request->fromdetail)){
                $LeadAdditionalInfoMaster->date_added=($request->date_added) ? date('Y-m-d',strtotime($request->date_added)) : NULL;
                $LeadAdditionalInfoMaster->lead_status=$request->lead_status;
                $LeadAdditionalInfoMaster->practice_area=$request->practice_area;
                $LeadAdditionalInfoMaster->potential_case_value=str_replace(",","",$request->potential_case_value);
                $LeadAdditionalInfoMaster->assigned_to=$request->assigned_to;
                $LeadAdditionalInfoMaster->office="1";
                $LeadAdditionalInfoMaster->potential_case_description=$request->potential_case_description;
               // $LeadAdditionalInfoMaster->user_status='1';
                $LeadAdditionalInfoMaster->conflict_check=($request->conflict_check)?'yes':'no';
                $LeadAdditionalInfoMaster->conflict_check_description=$request->conflict_check_description;
                $LeadAdditionalInfoMaster->notes=$request->notes;
                $LeadAdditionalInfoMaster->sort_order=LeadAdditionalInfo::where('firm_id',Auth::User()->firm_name)->where('lead_status',$request->lead_status)->max('sort_order') + 1;
            }
            
            $LeadAdditionalInfoMaster->save();
            
            $noteHistory=[];
            $noteHistory['acrtivity_title']='edited a lead';
            $noteHistory['activity_by']=Auth::User()->id;
            $noteHistory['for_lead']=$request->id;
            $this->noteActivity($noteHistory);
            session(['popup_success' => 'Your lead has been updated.']);
        }
        return response()->json(['errors'=>'','user_id'=>$UserMaster->id]);
        exit;
    }
    public function deleteLead(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            // $userData=LeadAdditionalInfo::find($request->user_id);
            $userData=LeadAdditionalInfo::where('user_id',$request->user_id)->first();
            if(!empty($userData)){
                LeadAdditionalInfo::where('user_id',$request->user_id)->delete();
                User::where('id',$request->user_id)->delete();
               
                $noteHistory=[];
                $noteHistory['acrtivity_title']='deleted a lead';
                $noteHistory['activity_by']=Auth::User()->id;
                $noteHistory['for_lead']=$request->user_id;
                $this->noteActivity($noteHistory);  

                Task::where('lead_id',$request->user_id)->delete();
                CaseEvent::where('lead_id',$request->user_id)->delete();
                
            }
            return response()->json(['errors'=>'','id'=>$request->user_id]);
            exit;
        }
        
    }
    
    public function loadStep1(Request $request)
    {
        $id=$request->id;
        $UserMaster=User::find($id);
        $LeadAdditionalInfo=LeadAdditionalInfo::where("user_id",$id)->first();
        $country = Countries::get();
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $LeadStatus=LeadStatus::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        
        $getChildUsers=$this->getParentAndChildUserIds();
        $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
        $CaseMasterClient = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        
        $firmStaff = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',3)->where("parent_user",Auth::user()->id)->orWhere("id",Auth::user()->id)->get();
       
        return view('lead.loadStep1',compact('UserMaster','LeadAdditionalInfo','country','ReferalResource','LeadStatus','CasePracticeArea','CaseMasterClient','CaseMasterCompany','firmStaff'));
    }

    public function saveStep1(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,'.$request->id.',id,deleted_at,NULL',

            ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $UserMaster =User::firstOrNew(array('id' => $request->id));
            $UserMaster->first_name=$request->first_name;
            $UserMaster->middle_name=$request->middle_name;
            $UserMaster->last_name=$request->last_name;
            $UserMaster->email=$request->email;
            $UserMaster->mobile_number=$request->cell_phone;
            $UserMaster->work_phone=$request->work_phone;
            $UserMaster->home_phone=$request->home_phone;
            $UserMaster->street=$request->address;
            $UserMaster->city=$request->city;
            $UserMaster->state=$request->state;
            $UserMaster->postal_code=$request->postal_code;
            $UserMaster->country=$request->country;
            $UserMaster->save();

            $LeadAdditionalInfoMaster =LeadAdditionalInfo::firstOrNew(array('id' => $request->user_id));
            $LeadAdditionalInfoMaster->address2=$request->address2;
            $LeadAdditionalInfoMaster->dob=($request->dob) ? date('Y-m-d',strtotime($request->dob)) : NULL;
            $LeadAdditionalInfoMaster->driver_license=$request->driver_license;
            $LeadAdditionalInfoMaster->license_state=$request->driver_state;
            if($request->client_portal_enable=="on"){
                $LeadAdditionalInfoMaster->client_portal_enable="1";
            }else{
                $LeadAdditionalInfoMaster->client_portal_enable="0";
            }
            $LeadAdditionalInfoMaster->license_state=$request->driver_state;
            $LeadAdditionalInfoMaster->save();
        }
        return response()->json(['errors'=>'','user_id'=>$UserMaster->id]);
        exit;
    }

    public function loadStep2(Request $request)
    {
        $id=$request->id;
        $UserMaster=User::find($id);
        $LeadAdditionalInfo=LeadAdditionalInfo::where("user_id",$id)->first();
        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          
        return view('lead.loadStep2',compact('practiceAreaList','caseStageList','UserMaster','id','LeadAdditionalInfo'));
    }
    // Save step 2 data to database.
    public function saveStep2(Request $request)
    {
        if($request->case_id!=''){
            $validator = \Validator::make($request->all(), [
                'case_name' => 'required|unique:case_master,case_title,'.$request->case_id.',id,deleted_at,NULL',
            ]);
        }else{
            $validator = \Validator::make($request->all(), [
                'case_name' => 'required|unique:case_master,case_title'
            ]);
        }
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            if($request->case_id!=''){
                $CaseMaster = CaseMaster::firstOrNew(array('id' => $request->case_id));
            }else{
                $CaseMaster = new CaseMaster;
            }
            if(isset($request->case_name)) { $CaseMaster->case_title=$request->case_name; }
            if(isset($request->case_number)) { $CaseMaster->case_number =$request->case_number; }
            if(isset($request->case_status)) { $CaseMaster->case_status=$request->case_status; }
            if(isset($request->case_description)) { $CaseMaster->case_description=$request->case_description; }
            if(isset($request->case_open_date)) {
                $var = $request->case_open_date;
                $CaseMaster->case_open_date= date('Y-m-d', strtotime($var));
            }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) {
                $var = $request->case_statute;
                $CaseMaster->case_statute_date= date('Y-m-d', strtotime($var));
            }
             if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1"; 
                if(isset($request->conflict_check_description)) { $CaseMaster->conflict_check_description=$request->conflict_check_description; }
            }
            $CaseMaster->case_unique_number=strtoupper(uniqid()); 
            if(isset($request->practice_area_text)) { 
                $CasePracticeArea = new CasePracticeArea;
                $CasePracticeArea->title=$request->practice_area_text; 
                $CasePracticeArea->created_by=Auth::User()->id; 
                $CasePracticeArea->save();
                
                $CaseMaster->practice_area=$CasePracticeArea->id;
            }else{
                if(isset($request->practice_area)) { $CaseMaster->practice_area=$request->practice_area; }
            }
           
            $CaseMaster->created_by=Auth::User()->id; 
            $CaseMaster->is_entry_done="0"; 
            $CaseMaster->save();

            if(isset($request->case_statute)){
                for($i=0;$i<count($request->reminder_type)-1;$i++){
                    $CaseSolReminder = new CaseSolReminder;
                    $CaseSolReminder->case_id=$CaseMaster->id; 
                    $CaseSolReminder->reminder_type=$request['reminder_type'][$i]; 
                    $CaseSolReminder->reminer_number=$request['reminder_days'][$i];
                    $CaseSolReminder->created_by=Auth::User()->id; 
                    $CaseSolReminder->save();
                }
            }

            //Activity tab
            $data=[];
            $data['activity_title']='added case';
            $data['case_id']=$CaseMaster->id;
            $data['activity_type']='';
            $this->caseActivity($data);
        }
        return response()->json(['errors'=>'','case_id'=>$CaseMaster->id,'id'=>$request->id,'user_id'=>$request->user_id]);
        exit;
    }

      //Load step 3 when click next button in step 2
      public function loadStep3(Request $request)
      {
            $id=$request->id;
            $UserMaster=User::find($id);
            $LeadAdditionalInfo=LeadAdditionalInfo::where("user_id",$id)->first();
            $case_id=$request->case_id;
            $selectdUSerList = User::select("*")->where("id",$id)->get();
            return view('lead.loadStep3',compact('selectdUSerList','case_id','id','UserMaster','LeadAdditionalInfo'));
      }
      //Save step 3 data to database.
    public function saveStep3(Request $request)
    {
        if(isset($request->default_rate)) {$request->default_rate=str_replace(",","",$request->default_rate); }
   
        $validator = \Validator::make([$request->default_rate], [
            'default_rate' => 'nullable|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $CaseClientSelection =CaseClientSelection::firstOrNew(array('case_id' => $request->case_id));;
            $CaseClientSelection->case_id=$request->case_id; 
            $CaseClientSelection->selected_user=$request->id; 
            $CaseClientSelection->created_by=Auth::user()->id; 
            if($request->id == $request->billing_contact){
                $CaseClientSelection->is_billing_contact='yes';
                if(isset($request->billingMethod)) { $CaseClientSelection->billing_method=$request->billingMethod; }
                if(isset($request->default_rate)) { $CaseClientSelection->billing_amount=$request->default_rate; }
            }   
            $CaseClientSelection->save();
            
            //Activity tab
            $datauser=[];
            $datauser['activity_title']='linked client';
            $datauser['case_id']=$request->case_id;
            $datauser['staff_id']=$request->user_id;
            $this->caseActivity($datauser);
            return response()->json(['errors'=>'','case_id'=>$request->case_id]);
            exit;
        }
    }
    
    public function loadStep4(Request $request)
    {
        $case_id=$request->case_id;
        $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
        return view('lead.loadStep4',compact('loadFirmUser','case_id'));
    }

    public function saveStep4(Request $request)
    {
        if(isset($request['selectedUSer'])){
            foreach($request['selectedUSer'] as $key=>$val){
                $CaseStaff = new CaseStaff;
                $CaseStaff->case_id=$request->case_id; 
                $CaseStaff->user_id=$key; 
                $CaseStaff->created_by=Auth::user()->id; 
                $CaseStaff->lead_attorney=$request['lead_attorney'];
                $CaseStaff->originating_attorney=$request['originating_attorney'];

                $CaseStaff->rate_type=($request['rate_type'][$key]=='Case_Rate')? "1" : "0";
                if( $CaseStaff->rate_type == "1"){ 
                    $CaseStaff->rate_amount=str_replace(",","",$request['new_rate'][$key]);
                }
                $CaseStaff->save();
                //Activity tab
                $datauser=[];
                $datauser['activity_title']='linked staff';
                $datauser['case_id']=$request->case_id;
                $datauser['staff_id']=$key;
                $this->caseActivity($datauser);
            }
            $caseStatusChange=CaseMaster::find($request->case_id);
            $caseStatusChange->is_entry_done="1";
            $caseStatusChange->save();

            $caseStageHistory = new CaseStageUpdate;
            $caseStageHistory->stage_id=($caseStatusChange->case_status)??NULL;
            $caseStageHistory->case_id=$caseStatusChange->id;
            $caseStageHistory->created_by=Auth::user()->id; 
            $caseStageHistory->created_at=$caseStatusChange->case_open_date; 
            $caseStageHistory->save();

            if($caseStatusChange->case_statute!=NULL){
                $this->saveSOLEventIntoCalender($request->case_id);
            }

            $CaseClientIs =CaseClientSelection::select("*")->where('case_id',$request->case_id)->first();
            if(!empty($CaseClientIs)){
                User::where('id',$CaseClientIs->selected_user)->update(['user_level'=>"2"]);//Change user type from lead to client
                $getLEadInfo= LeadAdditionalInfo::select("*")->where("user_id",$CaseClientIs->selected_user)->first();

                $UsersAdditionalInfo= new UsersAdditionalInfo;
                $UsersAdditionalInfo->user_id=$getLEadInfo->user_id;
                $UsersAdditionalInfo->contact_group_id="1";
                $UsersAdditionalInfo->company_id=NULL;
                $UsersAdditionalInfo->address2=$getLEadInfo->address2;
                $UsersAdditionalInfo->dob=$getLEadInfo->dob;
                $UsersAdditionalInfo->fax_number=NULL;
                $UsersAdditionalInfo->job_title=NULL;
                $UsersAdditionalInfo->driver_license=$getLEadInfo->driver_license;
                $UsersAdditionalInfo->license_state=$getLEadInfo->license_state;
                $UsersAdditionalInfo->website=NULL;
                $UsersAdditionalInfo->notes=$getLEadInfo->notes;
                $UsersAdditionalInfo->client_portal_enable=$getLEadInfo->client_portal_enable;
                $UsersAdditionalInfo->created_at =$getLEadInfo->created_at;
                $UsersAdditionalInfo->created_by =$getLEadInfo->created_by;
                $UsersAdditionalInfo->save();

                // $deleteLead= LeadAdditionalInfo::where("user_id",$CaseClientIs->selected_user)->delete();
                LeadAdditionalInfo::where("user_id",$CaseClientIs->selected_user)->update(['is_converted'=>'yes','converted_date'=>date('Y-m-d')]);


            }
            session(['popup_success' => 'Case has been created.']);
        }else{
            return response()->json(['errors'=>'Please select at least one staff member.']);
        }
       
        return response()->json(['errors'=>'','user_id'=>$request->user_id,'case_unique_number'=>$caseStatusChange->case_unique_number]);
        exit;
    }

    public function exportLead(Request $request)
    {
       
            //$CsvData[]='Lead Name,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,Current Status,Days since added,Practice Area,	Details	Value,Lead Source,Lead Referred By'; 
            $CsvData[] = 'First Name,Middle Name,Last Name,Street,Street 2,City,State,Zip Code,Country,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,Current Status,Days since added,Practice Area,Office,Details,Value,Lead Source,Lead Referred By,Birthday,License Number,License State,Added Date,Assign To,Potential Case Description';
            $allLeads = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id');
            $allLeads = $allLeads->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
            $allLeads = $allLeads->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
            $allLeads = $allLeads->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
            $allLeads = $allLeads->leftJoin('countries','users.country','=','countries.id');
            $allLeads = $allLeads->leftJoin('users as refuser','lead_additional_info.refered_by','=','refuser.id');
            $allLeads = $allLeads->leftJoin('users as assignRef','lead_additional_info.assigned_to','=','assignRef.id');
            $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),DB::raw('CONCAT_WS(" ",refuser.first_name,refuser.middle_name,refuser.last_name) as refuser_by_name'),DB::raw('CONCAT_WS(" ",assignRef.first_name,assignRef.middle_name,assignRef.last_name) as assignRef_by_name'),'countries.name as countries_name');
            $allLeads = $allLeads->where("users.user_type","5");
            $allLeads = $allLeads->where("users.user_level","5");
            $allLeads = $allLeads->where("lead_additional_info.is_converted","no");
            $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
            $allLeads = $allLeads->where("lead_additional_info.user_status","1");  //1:Active
            $allLeads=$allLeads->get();


           // First Name,Middle Name,Last Name,Street,Street 2,City,State,Zip Code,Country,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,Current Status,Days since added,Practice Area,Office,Details,Value,Lead Source,Lead Referred By,Birthday,License Number,License State,Added Date,Assign To,Potential Case Description

            $j=1; 
            // dump($allLeads);
            // dump($allLeads->pluck('potential_case_value'));
            // dd($allLeads->pluck('country'));  
            // dump($allLeads[10]);
            // dump($allLeads[10]['potential_case_value']);
            for($i=0;$i<$allLeads->count();$i++){   
                $office=($allLeads[$i]['office']) ? "Primary": '';
                $days = (strtotime(date('Y-m-d')) - strtotime($allLeads[$i]['created_at'])) / (60 * 60 * 24);
                $name=$allLeads[$i]['created_by_name'];
                $CsvData[]=$allLeads[$i]['first_name'].",".$allLeads[$i]['middle_name'].",".$allLeads[$i]['last_name'].",".$allLeads[$i]['street'].",".$allLeads[$i]['address2'].",".$allLeads[$i]['city'].",".$allLeads[$i]['state'].",".$allLeads[$i]['postal_code'].",".$allLeads[$i]['countries_name'].",".$allLeads[$i]['email'].",".$allLeads[$i]['work_phone'].",".$allLeads[$i]['home_phone'].",".$allLeads[$i]['mobile_number'].",".$allLeads[$i]['status_title'].",".ceil($days).",".$allLeads[$i]['practice_area_title'].",".$office.",".$allLeads[$i]['lead_detail'].","."$".$allLeads[$i]['potential_case_value'].",".$allLeads[$i]['res_title'].",".$allLeads[$i]['refuser_by_name'].",".$allLeads[$i]['dob'].",".$allLeads[$i]['driver_license'].",".$allLeads[$i]['license_state'].",".$allLeads[$i]['date_added'].",".$allLeads[$i]['assignRef_by_name'].",".$allLeads[$i]['notes'];
                $j++;
            }
           $CsvData[]="";
        //    dd($CsvData);
            $filename='active_leads_'."_".date('Ymd-His').".csv";
            $file_path=public_path().'/download/'.$filename;   
            $file_path_download=url('/public').'/download/'.$filename;   
            $file = fopen($file_path,"w+");
            foreach ($CsvData as $exp_data){
              fputcsv($file,explode(',',$exp_data));
            }   
            fclose($file);          
            $headers = ['Content-Type' => 'application/csv'];
           return response()->download($file_path,$filename,$headers );
    }
    public function loadAssignPopup()
    {
        $firmStaff = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',3)->where("parent_user",Auth::user()->id)->orWhere("id",Auth::user()->id)->get();

        return view('lead.assignLeads',compact('firmStaff'));
    }    
    public function saveBulkAssignLeads(Request $request)
    {
        $staffId=$request->assign_leads;
        $data = json_decode(stripslashes($request->leads_id));
        foreach($data as $k=>$v){
            LeadAdditionalInfo::where('id',$v)->update(['assigned_to'=>$staffId]);
        }
        session(['popup_success' => 'Your leads have been updated.']);
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;  
    }

    public function loadChangeBulkStatus()
    {
        $LeadStatus=LeadStatus::where("firm_id",Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        return view('lead.changeBulkStatus',compact('LeadStatus'));
    }    
    public function saveChangeBulkStatus(Request $request)
    {
        $status=$request->status;
        $data = json_decode(stripslashes($request->leads_id));
        foreach($data as $k=>$v){
            LeadAdditionalInfo::where('id',$v)->update(['lead_status'=>$status]);
        }
        session(['popup_success' => 'Your leads have been updated.']);
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;  
    }
    public function loadChangeBulkDonothire(Request $request)
    {
        $HireReason=NotHireReasons::where("firm_id",Auth::User()->firm_name)->get();
        return view('lead.doNotHireBulk',compact('HireReason'));
    }
    public function saveChangeBulkDonothire(Request $request)
    {
        if($request->not_hire_reasons_text!=""){
            $validator = \Validator::make($request->all(), [
                'not_hire_reasons_text' => 'required'
            ]);
        }else{
            $validator = \Validator::make($request->all(), [
                'not_hire_reasons_id' => 'required'
            ]);
        }
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            if($request->not_hire_reasons_text!=""){
                $existStatus=NotHireReasons::select('id')->where('title',$request->not_hire_reasons_text)->where("firm_id",Auth::User()->firm_name)->count();
                if($existStatus=="0"){
                    $NotHireReasons = new NotHireReasons;
                    $NotHireReasons->title=$request->not_hire_reasons_text;
                    $NotHireReasons->firm_id=Auth::User()->firm_name;
                    $NotHireReasons->created_by=Auth::User()->id;
                    $NotHireReasons->save();
                    $finalResonId=$NotHireReasons->id;
                }else{
                    $CustomError[]='No hire reason already exists';
                    return response()->json(['errors'=>$CustomError]);
                    exit;
                }
            }else{
                $finalResonId=$request->not_hire_reasons_id;
            }

            $data = json_decode(stripslashes($request->leads_id));
            foreach($data as $k=>$v){
                LeadAdditionalInfo::where('id',$v)->update(['do_not_hire_reason'=>$finalResonId,'user_status'=>"2",'do_not_hire_on'=>('Y-m-d')]);
            }
            
            session(['popup_success' => 'Your leads have been updated.']);
            return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
            exit;  
        }
    }

    public function deleteBulkLead(Request $request)
    {
        $HireReason=NotHireReasons::where("firm_id",Auth::User()->firm_name)->get();
        return view('lead.doNotHireBulk',compact('HireReason'));
    }
    public function saveDeleteBulkLead(Request $request)
    {
        $data = json_decode(stripslashes($request->leads_id));
        foreach($data as $k=>$v){
            LeadAdditionalInfo::where('id',$v)->delete();
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;  
    }
    public function deleteSubmittedLead(Request $request)
    {
        $data = json_decode(stripslashes($request->leads_id));
        foreach($data as $k=>$v){
            OnlineLeadSubmit::where('id',$v)->delete();
        }
        return response()->json(['errors'=>'','msg'=>'Online lead deleted successfully']);
        exit;  
    }
    public function donthire()
    {
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->pluck('title','id');
        $allLeadsDropdown = LeadAdditionalInfo::leftJoin('users','lead_additional_info.user_id','=','users.id')
        ->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))
        ->where("users.user_type","5")
        ->where("users.user_level","5")
        ->where("lead_additional_info.is_converted","no")
        ->where("users.firm_name",Auth::User()->firm_name)
        ->where("lead_additional_info.user_status","2") //2:Do not hire
        ->groupBy("lead_additional_info.user_id")
        ->get();
        $leadCount = OnlineLeadSubmit::where("firm_id",Auth::User()->firm_name)->count();

        return view('lead.donthire',compact('ReferalResource','allLeadsDropdown','leadCount'));
    }
    public function loadDonthire()
    {   

        $columns = array('users.id','users.id','users.id','created_by_name','res_title' ,'not_hire_reasons_title','not_hire_reasons.created_at','practice_area_title','potential_case_value','assigned_to_name','lead_additional_info.created_at');
        $requestData= $_REQUEST;
        
        $allLeads = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id');
        $allLeads = $allLeads->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
        $allLeads = $allLeads->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
        $allLeads = $allLeads->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
        $allLeads = $allLeads->leftJoin('not_hire_reasons','lead_additional_info.do_not_hire_reason','=','not_hire_reasons.id');
        $allLeads = $allLeads->leftJoin('users as uu','lead_additional_info.assigned_to','=','uu.id');
        $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),DB::raw('CONCAT_WS(" ",users.first_name,uu.middle_name,uu.last_name) as assigned_to_name'),'not_hire_reasons.title as not_hire_reasons_title');
        $allLeads = $allLeads->where("users.user_type","5");
        $allLeads = $allLeads->where("users.user_level","5");
        $allLeads = $allLeads->where("lead_additional_info.is_converted","no");
        $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
        $allLeads = $allLeads->where("lead_additional_info.user_status","2");  

        if($requestData['id']!=''){
            $allLeads = $allLeads->where("lead_additional_info.user_id",$requestData['id']); 
        }
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }
    public function exportdonthireLead(Request $request)
    {
        
            // $CsvData[]='Lead Name,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,No Hire Reason,No Hire At,Practice Area,Details Value,Lead Source,Lead Referred By';
            $CsvData[] = 'First Name,Middle Name,Last Name,Street,Street 2,City,State,Zip Code,Country,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,Current Status,Days since added,Practice Area,Office,Details,Value,Lead Source,Lead Referred By,Birthday,License Number,License State,Added Date,Assign To,Potential Case Description'; 
           
            $allLeads = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id');
            $allLeads = $allLeads->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
            $allLeads = $allLeads->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
            $allLeads = $allLeads->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
            $allLeads = $allLeads->leftJoin('countries','users.country','=','countries.id');
            $allLeads = $allLeads->leftJoin('not_hire_reasons','lead_additional_info.do_not_hire_reason','=','not_hire_reasons.id');

            $allLeads = $allLeads->leftJoin('users as refuser','lead_additional_info.refered_by','=','refuser.id');
            $allLeads = $allLeads->leftJoin('users as assignRef','lead_additional_info.assigned_to','=','assignRef.id');
            // $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),'not_hire_reasons.title as not_hire_reasons_title');
            $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),DB::raw('CONCAT_WS(" ",refuser.first_name,refuser.middle_name,refuser.last_name) as refuser_by_name'),DB::raw('CONCAT_WS(" ",assignRef.first_name,assignRef.middle_name,assignRef.last_name) as assignRef_by_name'),'countries.name as countries_name');
            $allLeads = $allLeads->where("users.user_type","5");
            $allLeads = $allLeads->where("users.user_level","5");
            $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
            $allLeads = $allLeads->where("lead_additional_info.user_status","2");  
            $allLeads = $allLeads->where("lead_additional_info.is_converted","no");
            $allLeads=$allLeads->get();

            $j=1;   
            for($i=0;$i<$allLeads->count();$i++){   
                $office=($allLeads[$i]['office']) ? "Primary": '';
                $days = (strtotime(date('Y-m-d')) - strtotime($allLeads[$i]['created_at'])) / (60 * 60 * 24);
                $name=$allLeads[$i]['created_by_name'];
                // $CsvData[]=$allLeads[$i]['first_name'].",".$allLeads[$i]['email'].",".$allLeads[$i]['work_phone'].",".$allLeads[$i]['home_phone'].",".$allLeads[$i]['mobile_number'].",".$allLeads[$i]['not_hire_reasons_title']."".$allLeads[$i]['donthire_date'].","."$".number_format($allLeads[$i]['potential_case_value'],2).",".$allLeads[$i]['res_title'].",".$allLeads[$i]['refered_by'];
                $CsvData[]=$allLeads[$i]['first_name'].",".$allLeads[$i]['middle_name'].",".$allLeads[$i]['last_name'].",".$allLeads[$i]['street'].",".$allLeads[$i]['address2'].",".$allLeads[$i]['city'].",".$allLeads[$i]['state'].",".$allLeads[$i]['postal_code'].",".$allLeads[$i]['countries_name'].",".$allLeads[$i]['email'].",".$allLeads[$i]['work_phone'].",".$allLeads[$i]['home_phone'].",".$allLeads[$i]['mobile_number'].",".$allLeads[$i]['status_title'].",".ceil($days).",".$allLeads[$i]['practice_area_title'].",".$office.",".$allLeads[$i]['lead_detail'].","."$".$allLeads[$i]['potential_case_value'].",".$allLeads[$i]['res_title'].",".$allLeads[$i]['refuser_by_name'].",".$allLeads[$i]['dob'].",".$allLeads[$i]['driver_license'].",".$allLeads[$i]['license_state'].",".$allLeads[$i]['date_added'].",".$allLeads[$i]['assignRef_by_name'].",".$allLeads[$i]['notes'];
                $j++;

            }
           $CsvData[]="";
            $filename='no_hire_leads_'."_".date('Ymd-His').".csv";
            $file_path=public_path().'/download/'.$filename;   
            $file_path_download=url('/public').'/download/'.$filename;   
            $file = fopen($file_path,"w+");
            foreach ($CsvData as $exp_data){
              fputcsv($file,explode(',',$exp_data));
            }   
            fclose($file);          
            $headers = ['Content-Type' => 'application/csv'];
           return response()->download($file_path,$filename,$headers );
    }
    public function reactiveLead(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'reactivate_user_id' => 'required|numeric'
        ]);
        
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $reactivate_user_id=$request->reactivate_user_id;
            LeadAdditionalInfo::where('id',$reactivate_user_id)->update(['user_status'=>"1",'do_not_hire_reason'=>NULL,'do_not_hire_on'=>NULL]);
            return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
            exit;  
        }
    }
    /***************************************/
    public function converted()
    {
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->pluck('title','id');
        $allLeadsDropdown = LeadAdditionalInfo::leftJoin('users','lead_additional_info.user_id','=','users.id')
        ->select("users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))
        ->where("users.user_type","5")
        ->where("users.user_level","5")
        ->where("users.firm_name",Auth::User()->firm_name)
        ->where("lead_additional_info.user_status","1") //1 :Active
        ->where("lead_additional_info.is_converted","yes")
        ->groupBy("lead_additional_info.user_id")
        ->get();
        $leadCount = OnlineLeadSubmit::where("firm_id",Auth::User()->firm_name)->count();

        return view('lead.converted',compact('ReferalResource','allLeadsDropdown','leadCount'));
    }
    public function loadConverted()
    {   

        $columns = array('users.id','users.id','created_by_name','res_title' ,'practice_area_title','potential_case_value','assigned_to_name','lead_additional_info.created_at','lead_additional_info.converted_date');
        $requestData= $_REQUEST;
        
        $allLeads = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id');
        $allLeads = $allLeads->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
        $allLeads = $allLeads->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
        $allLeads = $allLeads->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
        $allLeads = $allLeads->leftJoin('users as uu','lead_additional_info.assigned_to','=','uu.id');
        $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),DB::raw('CONCAT_WS(" ",users.first_name,uu.middle_name,uu.last_name) as assigned_to_name'));
        $allLeads = $allLeads->where("users.user_type","5");
        // $allLeads = $allLeads->where("users.user_level","5");
        $allLeads = $allLeads->where("lead_additional_info.is_converted","yes");
        $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
        $allLeads = $allLeads->where("lead_additional_info.user_status","1");  //1:Active

        if($requestData['id']!=''){
            $allLeads = $allLeads->where("lead_additional_info.user_id",$requestData['id']);  //1:Active
        }
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }
    public function exportConvertedLead(Request $request)
    {
        
            // $CsvData[]='Lead Name,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,No Hire Reason,No Hire At,Practice Area,Details Value,Lead Source,Lead Referred By'; 

            $CsvData[] = 'First Name,Middle Name,Last Name,Street,Street 2,City,State,Zip Code,Country,Lead Contact Email,Lead Contact Work #,Lead Contact Home #,Lead Contact Cell #,Current Status,Days since added,Practice Area,Office,Details,Value,Lead Source,Lead Referred By,Birthday,License Number,License State,Added Date,Assign To,Potential Case Description';
           
            $allLeads = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id');
            $allLeads = $allLeads->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
            $allLeads = $allLeads->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
            $allLeads = $allLeads->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
            $allLeads = $allLeads->leftJoin('countries','users.country','=','countries.id');
            $allLeads = $allLeads->leftJoin('users as refuser','lead_additional_info.refered_by','=','refuser.id');
            $allLeads = $allLeads->leftJoin('users as assignRef','lead_additional_info.assigned_to','=','assignRef.id');
            $allLeads = $allLeads->leftJoin('not_hire_reasons','lead_additional_info.do_not_hire_reason','=','not_hire_reasons.id');
            // $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),'not_hire_reasons.title as not_hire_reasons_title');
            $allLeads = $allLeads->select("users.*","lead_additional_info.*","referal_resource.title as res_title","lead_status.title as status_title","case_practice_area.title as practice_area_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),DB::raw('CONCAT_WS(" ",refuser.first_name,refuser.middle_name,refuser.last_name) as refuser_by_name'),DB::raw('CONCAT_WS(" ",assignRef.first_name,assignRef.middle_name,assignRef.last_name) as assignRef_by_name'),'countries.name as countries_name');
            $allLeads = $allLeads->where("users.user_type","5");
            // $allLeads = $allLeads->where("users.user_level","5");
            $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
            $allLeads = $allLeads->where("lead_additional_info.user_status","1");  
            $allLeads = $allLeads->where("lead_additional_info.is_converted","yes");
            $allLeads=$allLeads->get();
            
            $j=1;   
            for($i=0;$i<$allLeads->count();$i++){ 
                $office=($allLeads[$i]['office']) ? "Primary": '';
                $days = (strtotime(date('Y-m-d')) - strtotime($allLeads[$i]['created_at'])) / (60 * 60 * 24);  
                // $CsvData[]=$allLeads[$i]['created_by_name'].",".$allLeads[$i]['email'].",".$allLeads[$i]['work_phone'].",".$allLeads[$i]['home_phone'].",".$allLeads[$i]['mobile_number'].",".$allLeads[$i]['not_hire_reasons_title'].",".$allLeads[$i]['do_not_hire_on'].",".$allLeads[$i]['res_title'].","."$".number_format($allLeads[$i]['potential_case_value'],2).",".$allLeads[$i]['res_title'].",".$allLeads[$i]['refered_by'];
                $CsvData[]=$allLeads[$i]['first_name'].",".$allLeads[$i]['middle_name'].",".$allLeads[$i]['last_name'].",".$allLeads[$i]['street'].",".$allLeads[$i]['address2'].",".$allLeads[$i]['city'].",".$allLeads[$i]['state'].",".$allLeads[$i]['postal_code'].",".$allLeads[$i]['countries_name'].",".$allLeads[$i]['email'].",".$allLeads[$i]['work_phone'].",".$allLeads[$i]['home_phone'].",".$allLeads[$i]['mobile_number'].",".$allLeads[$i]['status_title'].",".ceil($days).",".$allLeads[$i]['practice_area_title'].",".$office.",".$allLeads[$i]['lead_detail'].","."$".$allLeads[$i]['potential_case_value'].",".$allLeads[$i]['res_title'].",".$allLeads[$i]['refuser_by_name'].",".$allLeads[$i]['dob'].",".$allLeads[$i]['driver_license'].",".$allLeads[$i]['license_state'].",".$allLeads[$i]['date_added'].",".$allLeads[$i]['assignRef_by_name'].",".$allLeads[$i]['notes'];
                $j++;
            }
           $CsvData[]="";
            $filename='converted_leads_'."_".date('Ymd-His').".csv";
            $file_path=public_path().'/download/'.$filename;   
            $file_path_download=url('/public').'/download/'.$filename;   
            $file = fopen($file_path,"w+");
            foreach ($CsvData as $exp_data){
              fputcsv($file,explode(',',$exp_data));
            }   
            fclose($file);          
            $headers = ['Content-Type' => 'application/csv'];
           return response()->download($file_path,$filename,$headers );
    }
    /****************ONLINE LEADS***********************/
    public function onlineleads()
    {
        $leads=CaseIntakeFormFieldsData::where("firm_id",Auth::User()->firm_name)->get();

        $leadCount = OnlineLeadSubmit::where("firm_id",Auth::User()->firm_name)->count();
        // $OnlineLeadSubmit=OnlineLeadSubmit::get();
        // foreach($OnlineLeadSubmit as $k=>$v){
        // $OnlineLeadSubmit =OnlineLeadSubmit::find($v->id);
        // $CommonController= new CommonController();
        // $OnlineLeadSubmit->unique_token=$CommonController->getUniqueToken();
        // $OnlineLeadSubmit->save();
        // }

        return view('lead.onlineleads',compact('leads','leadCount'));
    }
    public function loadOnlineLeads()
    {   

        $columns = array('id','id','id','leadName','created_at','id');
        $requestData= $_REQUEST;
        $allLeads = OnlineLeadSubmit::select("online_lead_submit.*",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as leadName'));
        $allLeads = $allLeads->where("firm_id",Auth::User()->firm_name);
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }
    public function approveLead(Request $request)
    {
        $id=$request->id;

        $OnlineLeadSubmit=OnlineLeadSubmit::find($id);
        $CaseIntakeFormFieldsData=CaseIntakeFormFieldsData::find($OnlineLeadSubmit->case_intake_form_fields_data_id);
        $formRow=json_decode($CaseIntakeFormFieldsData['form_value']);
    
        
        $UserMaster = new User;
        $UserMaster->first_name=$formRow->first_name;
        $UserMaster->middle_name=$formRow->middle_name;
        $UserMaster->last_name=$formRow->last_name;
        $UserMaster->email=$formRow->email;
        $UserMaster->firm_name=$OnlineLeadSubmit['firm_name'];
        $UserMaster->work_phone=$OnlineLeadSubmit['work_phone'];
        $UserMaster->home_phone=$OnlineLeadSubmit['home_phone'];
        $UserMaster->apt_unit=$CaseIntakeFormFieldsData['address1'];
        $UserMaster->city=$OnlineLeadSubmit['city'];
        $UserMaster->state=$OnlineLeadSubmit['state'];
        $UserMaster->postal_code=$OnlineLeadSubmit['postal'];
        $UserMaster->country=$OnlineLeadSubmit['country'];
        $UserMaster->token  = Str::random(40);
        $UserMaster->user_type='5';  // 5  :Lead
        $UserMaster->user_level='5'; // 5  :Lead
        $UserMaster->user_title='';
        $UserMaster->parent_user =Auth::User()->id;
        $UserMaster->firm_name =Auth::User()->firm_name;
        $UserMaster->created_by =Auth::User()->id;
        $UserMaster->save();

        $leadStatus=LeadStatus::select('id','title')->where("firm_id",$OnlineLeadSubmit['firm_id'])->orderBy('status_order',"ASC")->first();
        $getChildUsers=$this->getParentAndChildUserIds();
        $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->orderBy("id","ASC")->first();

        $LeadAdditionalInfoMaster = new LeadAdditionalInfo;
        $LeadAdditionalInfoMaster->user_id=$UserMaster->id;
        $LeadAdditionalInfoMaster->address2=$CaseIntakeFormFieldsData['address2'];
        if($CaseIntakeFormFieldsData['birthday']!=""){
            $LeadAdditionalInfoMaster->dob=date('Y-m-d',strtotime($CaseIntakeFormFieldsData['birthday']));
        }
        $LeadAdditionalInfoMaster->driver_license=($CaseIntakeFormFieldsData['driver_license_number'])??'';
        $LeadAdditionalInfoMaster->license_state=($CaseIntakeFormFieldsData['driver_license_state'])??'';
        $LeadAdditionalInfoMaster->potential_case_title="Potential Case: ".$formRow->first_name. " ".$formRow->middle_name." ".$formRow->last_name;
        $LeadAdditionalInfoMaster->date_added=date('Y-m-d',strtotime($CaseIntakeFormFieldsData['created_at']));
        $LeadAdditionalInfoMaster->lead_status=$leadStatus['id'];
        $LeadAdditionalInfoMaster->practice_area=$CasePracticeArea['id'];
        $LeadAdditionalInfoMaster->office="1";
        $LeadAdditionalInfoMaster->user_status='1';
        $LeadAdditionalInfoMaster->sort_order=LeadAdditionalInfo::where('firm_id',Auth::User()->firm_name)->where('lead_status',$request->lead_status)->max('sort_order') + 1;
        $LeadAdditionalInfoMaster->created_by =Auth::User()->id;
        $LeadAdditionalInfoMaster->save();

        $noteHistory=[];
        $noteHistory['acrtivity_title']='added a lead';
        $noteHistory['activity_by']=Auth::User()->id;
        $noteHistory['for_lead']=$UserMaster->id;
        $this->noteActivity($noteHistory);
       
        OnlineLeadSubmit::where("id",$id)->delete();
        $LeadSuccess="The lead has been approved and moved to the ".$leadStatus['title']." status column.";
        session(['popup_success' =>$LeadSuccess]);
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;  
        
    }

    public function approveBulkLead(Request $request)
    {
        $data = json_decode(stripslashes($request->leads_id));
        foreach($data as $k=>$v){
            $OnlineLeadSubmit=OnlineLeadSubmit::find($v);
            $CaseIntakeFormFieldsData=CaseIntakeFormFieldsData::find($OnlineLeadSubmit->case_intake_form_fields_data_id);
            $formRow=json_decode($CaseIntakeFormFieldsData['form_value']);
        
            
            $UserMaster = new User;
            $UserMaster->first_name=$formRow->first_name;
            $UserMaster->middle_name=$formRow->middle_name;
            $UserMaster->last_name=$formRow->last_name;
            $UserMaster->email=$formRow->email;
            $UserMaster->firm_name=$OnlineLeadSubmit['firm_name'];
            $UserMaster->work_phone=$OnlineLeadSubmit['work_phone'];
            $UserMaster->home_phone=$OnlineLeadSubmit['home_phone'];
            $UserMaster->apt_unit=$CaseIntakeFormFieldsData['address1'];
            $UserMaster->city=$OnlineLeadSubmit['city'];
            $UserMaster->state=$OnlineLeadSubmit['state'];
            $UserMaster->postal_code=$OnlineLeadSubmit['postal'];
            $UserMaster->country=$OnlineLeadSubmit['country'];
            $UserMaster->token  = Str::random(40);
            $UserMaster->user_type='5';  // 5  :Lead
            $UserMaster->user_level='5'; // 5  :Lead
            $UserMaster->user_title='';
            $UserMaster->parent_user =Auth::User()->id;
            $UserMaster->created_by =Auth::User()->id;
            $UserMaster->save();

            $leadStatus=LeadStatus::select('id','title')->where("firm_id",$OnlineLeadSubmit['firm_id'])->orderBy('status_order',"ASC")->first();
            $getChildUsers=$this->getParentAndChildUserIds();
            $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->orderBy("id","ASC")->first();

            $LeadAdditionalInfoMaster = new LeadAdditionalInfo;
            $LeadAdditionalInfoMaster->user_id=$UserMaster->id;
            $LeadAdditionalInfoMaster->address2=$CaseIntakeFormFieldsData['address2'];
            if($CaseIntakeFormFieldsData['birthday']!=""){
                $LeadAdditionalInfoMaster->dob=date('Y-m-d',strtotime($CaseIntakeFormFieldsData['birthday']));
            }
            $LeadAdditionalInfoMaster->driver_license=($CaseIntakeFormFieldsData['driver_license_number'])??'';
            $LeadAdditionalInfoMaster->license_state=($CaseIntakeFormFieldsData['driver_license_state'])??'';
            $LeadAdditionalInfoMaster->potential_case_title="Potential Case: ".$formRow->first_name. " ".$formRow->middle_name." ".$formRow->last_name;
            $LeadAdditionalInfoMaster->date_added=date('Y-m-d',strtotime($CaseIntakeFormFieldsData['created_at']));
            $LeadAdditionalInfoMaster->lead_status=$leadStatus['id'];
            $LeadAdditionalInfoMaster->practice_area=$CasePracticeArea['id'];
            $LeadAdditionalInfoMaster->office="1";
            $LeadAdditionalInfoMaster->user_status='1';
            $LeadAdditionalInfoMaster->sort_order=LeadAdditionalInfo::where('firm_id',Auth::User()->firm_name)->where('lead_status',$request->lead_status)->max('sort_order') + 1;
            $LeadAdditionalInfoMaster->created_by =Auth::User()->id;
            // $LeadAdditionalInfoMaster->save();

            $noteHistory=[];
            $noteHistory['acrtivity_title']='added a lead';
            $noteHistory['activity_by']=Auth::User()->id;
            $noteHistory['for_lead']=$UserMaster->id;
            $this->noteActivity($noteHistory);
        
            OnlineLeadSubmit::where("id",$v)->delete();

        }
        $totalLead=count($data);
        $LeadSuccess=$totalLead." lead has been approved and moved to the ".$leadStatus['title']." status column.";
        session(['popup_success' =>$LeadSuccess]);
        return response()->json(['errors'=>'','msg'=>'Online lead deleted successfully']);
        exit;  
    }

    public function deleteLeadConfirm(Request $request)
    {
        $id=$request->id;
        OnlineLeadSubmit::where("id",$id)->delete();
        return response()->json(['errors'=>'','msg'=>'Your lead ahas been deleted.']);
        exit;  
        
    }
    /****************ONLINE LEADS***********************/


    /*****************LEAD TASKS**********************/
    public function leadTasks()
    {
        return view('lead.tasks.index');
    }

    public function loadLeadTask(){
        
        $columns = array('id', 'case_title', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        
        $task = Task::join("users","task.lead_id","=","users.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        
    
         //Filter applied for task status
         if(isset($requestData['status'])){
            $task = $task->where("task.status",$requestData['status']);
        }else{
            $task = $task->where("task.status","0");
        }
       
         //Filter applied on user assign column
         if(isset($requestData['at']) && $requestData['at']=='me'){
            $taskAssigneME=CaseTaskLinkedStaff::select('task_id')->where("user_id",Auth::User()->id)->get()->pluck('task_id');
            $task = $task->whereIn("task.id",$taskAssigneME);
        }else{
            $taskAssigneOther=CaseTaskLinkedStaff::select('task_id')->get()->pluck('task_id');
            $task = $task->whereIn("task.id",$taskAssigneOther);
        }
        // $task = $task->where("task.lead_id","!=",NULL);
        $task = $task->where("users.firm_name",Auth::user()->firm_name);
        $task = $task->where("users.user_type","5");
        $totalData=$task->count();
        $totalFiltered = $totalData; 
        $task = $task->offset($requestData['start'])->limit($requestData['length']);
        $task = $task->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $task = $task->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $task 
        );
        echo json_encode($json_data);  
    }
    public function loadAddTaskPopup(Request $request)
    {
        $case_id='';
        $lead_id=$request->user_id;
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        $country = Countries::get();
        $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
        $currentDateTime=$this->getCurrentDateAndTime();
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();
         
         if(isset($request->case_id)){
             $case_id=$request->case_id;
         }

         return view('lead.tasks.loadAddTaskPopup',compact('lead_id','caseLeadList','CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id'));          
    }

    public function loadRightSection(Request $request)
    {       
        $caseLinkeSaved=array();
        $caseLinkedSavedAssigned=array();
        $case_id=$request->case_id;
        $task_id=$request->task_id;
        $caseNonLinkedAssigned=[];
        $from=$request->from;
  
        //Load Lead And Client
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id")->where("case_client_selection.case_id",$case_id)->get();
  
        //Load Non link staff list
        $caseNoneLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');
        $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereNotIn('id',$caseNoneLinkedStaffList)->get();
  
       //Load Linked staff
        $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
      
    
        if(isset($task_id) && $task_id!=''){
       
          $caseLinkedSavedAssigned = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
         $caseLinkedSavedAssigned= $caseLinkedSavedAssigned->toArray();
    
         $caseNonLinkedAssigned = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","no")->where("task_linked_staff.task_id",$task_id)->get()->pluck('user_id');
          $caseNonLinkedAssigned= $caseNonLinkedAssigned->toArray();
          $from="edit";
  
        }
       
        return view('lead.tasks.loadTaskRightSection',compact('caseCllientSelection','loadFirmUser','from','task_id','caseLinkedStaffList','caseNonLinkedAssigned','caseLinkedSavedAssigned'));     
        exit;   

        // $caseLinkeSaved=array();
        // $caseLinkeSavedAttending=array();
        // $case_id=$request->case_id;
        // $task_id=$request->task_id;
        // $nonLinkedSaved=[];
        // $from=$request->from;
        // $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id")->where("case_client_selection.case_id",$case_id)->get();
        
        // $caseNoneLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');
        // $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereNotIn('id',$caseNoneLinkedStaffList)->get();

        // if(isset($request->task_id) && $request->task_id!=''){
        //      $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","no")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
        //     $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
        // }
       
        
        // $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
      
        // if(isset($task_id) && $task_id!=''){
        //   $caseLinkeSaved = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$task_id)->get()->pluck('user_id');
        //   $caseLinkeSaved= $caseLinkeSaved->toArray();

        //   $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$task_id)->get()->pluck('user_id');
        //   $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
        // }
       
        // return view('lead.tasks.loadTaskRightSection',compact('caseCllientSelection','loadFirmUser','caseLinkeSavedAttending','from','task_id','nonLinkedSaved','caseLinkedStaffList','caseLinkeSaved'));     
        // exit;    
   }
   public function loadAllStaffMember(Request $request)
   {

         $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->get();

         $SavedStaff=$from='';
         if(isset($request->edit)){
           $SavedStaff=CaseTaskLinkedStaff::select('user_id')->where("task_id", $request->task_id)->get()->pluck('user_id')->toArray();
           $from='edit';  
       }
         return view('lead.tasks.firmStaff',compact('loadFirmStaff','SavedStaff','from'));     
         exit;    
    }

    public function loadTimeEstimationCaseWiseUsersList(Request $request)
    {
        if(isset($request->userList)){
           $userList=json_decode($request->userList, TRUE);

           // $loadFirmStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$request->case_id)->whereIn("users.id",$userList)->get();
           $loadFirmStaff = User::select("users.*")->whereIn("users.id",$userList)->get();
        }else{
           // $loadFirmStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$request->case_id)->get();
           $loadFirmStaff = CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title")->select("users.*")->where("task_linked_staff.task_id",$request->task_id)->get();

       }

       $fillsedHours='';
       if($request->edit=="edit"){
          $fillsedHours=CaseTaskLinkedStaff::select('time_estimate_total',"user_id")->where("task_id", $request->task_id)->get();
           foreach($fillsedHours as $k=>$v){
               $fillsedHours[$v->user_id]=$v->time_estimate_total;
           }
       }
        return view('task.loadTimeEstimationUsersList',compact('loadFirmStaff','fillsedHours'));     
        exit;    
   }

    public function saveAddTaskPopup(Request $request)
    {
      
        $validator = \Validator::make($request->all(), [
            'task_name' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskMaster = new Task;
           
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $TaskMaster->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $TaskMaster->lead_id=$request->text_lead_id; 
                    }    
                } 
                $TaskMaster->no_case_link="yes";
            }else{
                $TaskMaster->no_case_link="no";
            }
            if(isset($request->task_name)) { $TaskMaster->task_title=$request->task_name; }else{ $TaskMaster->task_title=NULL; }
            if(isset($request->due_date) && $request->due_date!="") { $TaskMaster->task_due_on=date('Y-m-d',strtotime($request->due_date)); }else { $TaskMaster->task_due_on= "9999-12-30";}
            if(isset($request->status)) { $TaskMaster->case_status=$request->case_status; }
            if(isset($request->event_frequency)) { $TaskMaster->task_priority=$request->event_frequency; }else{$TaskMaster->task_priority=NULL;}
            if(isset($request->description)) { $TaskMaster->description=$request->description; }else{ $TaskMaster->description=NULL; }
            if(isset($request->time_tracking_enabled)) { $TaskMaster->time_tracking_enabled='yes'; }else{ $TaskMaster->time_tracking_enabled='no'; }
            $TaskMaster->created_by=Auth::User()->id; 
            $TaskMaster->save();

            $this->saveTaskReminder($request->all(),$TaskMaster->id); 
            $this->saveLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveNonLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveTaskChecklist($request->all(),$TaskMaster->id); 

            $taskHistory=[];
            $taskHistory['task_id']=$TaskMaster->id;
            $taskHistory['task_action']='Created task';
            $taskHistory['created_by']=Auth::User()->id;
            $taskHistory['created_at']=date('Y-m-d H:i:s');
            $this->taskHistory($taskHistory);
            
            $caseHistory=[];
            $caseHistory['acrtivity_title']='added';
            $caseHistory['activity_by']=Auth::User()->id;
            $caseHistory['for_lead']=($TaskMaster->lead_id)??NULL;
            $caseHistory['type']="1";
            $caseHistory['task_id']=$TaskMaster->id;
            $caseHistory['case_id']=NULL;
            $caseHistory['created_by']=Auth::User()->id;
            $caseHistory['created_at']=date('Y-m-d H:i:s');
            $this->saveCaseActivity($caseHistory);
            

            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }

            public function saveTaskReminder($request,$task_id)
            {
                CaseTaskReminder::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();

            for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
                $CaseTaskReminder = new CaseTaskReminder;
                $CaseTaskReminder->task_id=$task_id; 
                $CaseTaskReminder->reminder_type=$request['reminder_type'][$i];
                $CaseTaskReminder->reminer_number=$request['reminder_number'][$i];
                $CaseTaskReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
                $CaseTaskReminder->reminder_user_type=$request['reminder_user_type'][$i];
                $CaseTaskReminder->created_by=Auth::user()->id; 
                $CaseTaskReminder->save();
            }
        }

        public function saveNonLinkedStaffToTask($request,$task_id)
        {
            if(isset($request['share_checkbox_nonlinked'])){
                for($i=0;$i<count($request['share_checkbox_nonlinked']);$i++){
                        $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                        $CaseTaskLinkedStaff->task_id=$task_id; 
                        $CaseTaskLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                        if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                            $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$request['share_checkbox_nonlinked'][$i]];
                        }else{
                            $CaseTaskLinkedStaff->time_estimate_total="0";
                        }

                        $CaseTaskLinkedStaff->linked_or_not_with_case="no";
                        $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                        $CaseTaskLinkedStaff->save();
                    }
                }
        }
        public function saveLinkedStaffToTask($request,$task_id)
        {
            CaseTaskLinkedStaff::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
            if(isset($request['linked_staff_checked_attend'])){
                for($i=0;$i<count($request['linked_staff_checked_attend']);$i++){
                        $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                        $CaseTaskLinkedStaff->task_id=$task_id; 
                        $CaseTaskLinkedStaff->user_id=$request['linked_staff_checked_attend'][$i];
                        // $CaseTaskLinkedStaff->time_estimate_total="0";
                        if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                            $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$request['linked_staff_checked_attend'][$i]];
                        }else{
                            $CaseTaskLinkedStaff->time_estimate_total="0";
                        }
                        $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                    
                        $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                        $CaseTaskLinkedStaff->save();
                    }
                }
        }

        public function saveTaskChecklist($request,$task_id)
        {
                TaskChecklist::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
                $orderValue=1;
                if(isset($request['checklist-item-name'])){
                        for($i=0;$i<count($request['checklist-item-name'])-1;$i++){
                        $TaskChecklist = new TaskChecklist;
                        $TaskChecklist->task_id=$task_id; 
                        $TaskChecklist->checklist_order=$orderValue; 
                        $TaskChecklist->status="0"; 
                        $TaskChecklist->title=$request['checklist-item-name'][$i];
                        $TaskChecklist->created_by=Auth::user()->id; 
                        if($request['checklist-item-name'][$i]!=''){
                            $TaskChecklist->save(); //Could not store empty checklist
                        }
                        $orderValue++;
                    }
                }
        }
        public function taskHistory($historyData)
        {
              $TaskHistory = new TaskHistory; 
              $TaskHistory->task_id=$historyData['task_id'];
              $TaskHistory->task_action= $historyData['task_action'];
              $TaskHistory->created_by=$historyData['created_by'];
              $TaskHistory->created_at=$historyData['created_at'];
              $TaskHistory->save();
              return true;
        }

    public function markAsCompleted(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'task_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            if($request->type=="incomplete"){
                Task::where('id',$request->task_id)->update(['status'=>'0','task_completed_date'=>NULL,'task_completed_by'=>NULL]);
            }else{
                Task::where('id',$request->task_id)->update(['status'=>'1','task_completed_date'=>date('Y-m-d h:i:s'),'task_completed_by'=>Auth::User()->id]);
            }
            
            return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
            exit;   
        } 
    }

    
    public function loadEditTaskPopup(Request $request)
    {
        $task_id=$request->task_id;
        $Task = Task::find($request->task_id);
        $TaskChecklist = TaskChecklist::select("*")->where("task_id",$task_id)->orderBy('checklist_order','ASC')->get();
        $taskReminderData = CaseTaskReminder::select("*")->where("task_id",$task_id)->get();
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        $country = Countries::get();
        $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
        $currentDateTime=$this->getCurrentDateAndTime();
    
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();
         $from_view="no";
         if(isset($request->from_view) && $request->from_view=='yes'){
             $from_view="yes";
         }
         return view('lead.tasks.loadEditTaskPopup',compact('caseLeadList','CaseMasterData','task_id','Task','TaskChecklist','taskReminderData','from_view'));          
    }

    public function saveEditTaskPopup(Request $request)
    {
     
    //    print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'task_name' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskMaster =Task::find($request->task_id);
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $TaskMaster->case_id=$request->text_case_id; 
                        $TaskMaster->lead_id=NULL; 
                    }else if($request->text_lead_id!=''){
                        $TaskMaster->case_id=NULL; 
                        $TaskMaster->lead_id=$request->text_lead_id; 
                    }    
                } 
                $TaskMaster->no_case_link="yes";
            }else{
                $TaskMaster->no_case_link="no";
            }
            
            if(isset($request->task_name)) { $TaskMaster->task_title=$request->task_name; }else{ $TaskMaster->task_title=NULL; }
            if(isset($request->due_date) && $request->due_date!="") { $TaskMaster->task_due_on=date('Y-m-d',strtotime($request->due_date)); }else { $TaskMaster->task_due_on= "9999-12-30";}
            if(isset($request->status)) { $TaskMaster->case_status=$request->case_status; }
            if(isset($request->event_frequency)) { $TaskMaster->task_priority=$request->event_frequency; }else{$TaskMaster->task_priority=NULL;}
            if(isset($request->description)) { $TaskMaster->description=$request->description; }else{ $TaskMaster->description=NULL; }
            if(isset($request->time_tracking_enabled) && $request->time_tracking_enabled=="on") { $TaskMaster->time_tracking_enabled='yes'; }else{ $TaskMaster->time_tracking_enabled='no'; }
            $TaskMaster->updated_by=Auth::User()->id; 
            $TaskMaster->save();

            $taskHistory=[];
            $taskHistory['task_id']=$TaskMaster->id;
            $taskHistory['task_action']='Updated task';
            $taskHistory['created_by']=Auth::User()->id;
            $taskHistory['created_at']=date('Y-m-d H:i:s');
            $this->taskHistory($taskHistory);

            $this->saveEditTaskReminder($request->all(),$TaskMaster->id); 
            $this->saveEditLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveNonLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveEditTaskChecklist($request->all(),$TaskMaster->id); 
            if($request->from_view=="yes"){
                Session::put('task_id', $request->task_id);
            }
            
            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }
    public function saveEditTaskChecklist($request,$task_id)
    {
          $orderValue=1;
          $finalDataList=array();
          if(isset($request['checklist-item-name'])){
                foreach($request['checklist-item-name'] as $k=>$v){
                $TaskChecklist =TaskChecklist::where("id",$k)->where("task_id", $task_id)->count();
                if($TaskChecklist=="0"){
                    $TaskChecklist = new TaskChecklist;
                    $TaskChecklist->task_id=$task_id; 
                    $TaskChecklist->checklist_order=$orderValue; 
                    $TaskChecklist->status="0"; 
                    $TaskChecklist->title=$request['checklist-item-name'][$k];
                    $TaskChecklist->created_by=Auth::user()->id; 
                    if($request['checklist-item-name'][$k]!=''){
                        $TaskChecklist->save(); //Could not store empty checklist
                        $finalDataList[]=$TaskChecklist->id;    
                    }
                }else{
                    $TaskChecklist = TaskChecklist::find($k);
                    $TaskChecklist->checklist_order=$orderValue; 
                    $TaskChecklist->title=$request['checklist-item-name'][$k];
                    $TaskChecklist->updated_by=Auth::user()->id; 
                    if($request['checklist-item-name'][$k]!=''){
                        $TaskChecklist->save(); //Could not store empty checklist
                        $finalDataList[]=$TaskChecklist->id;    
                    }  
                }
                $orderValue++;
            }
            $ids=TaskChecklist::select("*")->whereIn("id",$finalDataList)->get()->pluck('id');
            TaskChecklist::where("task_id", $task_id)->whereNotIn("id",$ids)->delete();
        }
   }
    public function saveEditTaskReminder($request,$task_id)
    {
      
        CaseTaskReminder::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
        for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
           $CaseTaskReminder = new CaseTaskReminder;
           $CaseTaskReminder->task_id=$task_id; 
           $CaseTaskReminder->reminder_type=$request['reminder_type'][$i];
           $CaseTaskReminder->reminer_number=$request['reminder_number'][$i];
           $CaseTaskReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
           $CaseTaskReminder->reminder_user_type=$request['reminder_user_type'][$i];
           $CaseTaskReminder->created_by=Auth::user()->id; 
           $CaseTaskReminder->save();
       }
   }
   public function saveEditLinkedStaffToTask($request,$task_id)
   {
        $orderValue=1;
        $finalDataList=array();
        if(isset($request['linked_staff_checked_attend'])){
            foreach($request['linked_staff_checked_attend'] as $k=>$v){
                $CaseTaskLinkedStaff =CaseTaskLinkedStaff::where("user_id",$v)->where("task_id", $task_id)->count();
                if($CaseTaskLinkedStaff=="0"){
                    $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                    $CaseTaskLinkedStaff->task_id=$task_id; 
                    $CaseTaskLinkedStaff->user_id=$v; 
                    if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                        $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$v];
                    }else{
                        $CaseTaskLinkedStaff->time_estimate_total="0";
                    }
                    $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                    $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                    $CaseTaskLinkedStaff->save();
                    $finalDataList[]=$CaseTaskLinkedStaff->id;
                }else{
                    $CaseTaskLinkedStaffCheck =CaseTaskLinkedStaff::select("*")->where("user_id",$v)->where("task_id", $task_id)->first();
                    if(!empty($CaseTaskLinkedStaffCheck)){
                        $CaseTaskLinkedStaff = CaseTaskLinkedStaff::find($CaseTaskLinkedStaffCheck->id);
                        $CaseTaskLinkedStaff->task_id=$task_id; 
                        $CaseTaskLinkedStaff->user_id=$v;
                        if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                            $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$v];
                        }else{
                            $CaseTaskLinkedStaff->time_estimate_total="0";
                        }
                        $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                        $CaseTaskLinkedStaff->updated_by=Auth::user()->id; 
                        $CaseTaskLinkedStaff->save();
                        $finalDataList[]=$CaseTaskLinkedStaffCheck->id;
                    }
                  
                }
            }
        }
        $pluckIds =CaseTaskLinkedStaff::select("*")->where("task_id", $task_id)->whereIn("id",$finalDataList)->get()->pluck("id");
        CaseTaskLinkedStaff::where("task_id", $task_id)->whereNotIn("id",$pluckIds)->delete();
   }
   public function loadTaskDetailPage(Request $request)
   {    
       //Delete duplicate records   
        $delete="DELETE t1 FROM task_linked_staff t1 INNER JOIN task_linked_staff t2 WHERE t1.id < t2.id AND t1.task_id =".$request->task_id." AND t1.user_id = t2.user_id";
        DB::delete($delete); 
        
     $TaskActivity=TaskActivity::where('status','1')->get();
     $TaskData=Task::find($request->task_id);
 
     $TaskCreatedBy = Task::join("users","task.created_by","=","users.id")
         ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title")->where('task.id',$request->task_id)->first();
 
     $TaskAssignedTo = CaseTaskLinkedStaff::leftJoin("users","task_linked_staff.user_id","=","users.id")
         ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title","task_linked_staff.time_estimate_total")
         ->where('task_linked_staff.task_id',$request->task_id)
        // ->where('task_linked_staff.linked_or_not_with_case','yes')
         ->get();
       
    $CaseMasterData='';
     if($TaskData->case_id!=''){
         $CaseMasterData = CaseMaster::find($TaskData->case_id);
     }
 
     $TaskReminders=CaseTaskReminder::leftJoin("users","task_reminder.created_by","=","users.id")
     ->select("task_reminder.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))
     ->where("task_id", $request->task_id)->get();
 
     $TaskChecklist = TaskChecklist::select("*")->where("task_id", $request->task_id)->orderBy('checklist_order','ASC')->get();
     $TaskChecklistCompleted = TaskChecklist::select("*")->where("task_id", $request->task_id)->where('status','1')->count();
 

     return view('lead.tasks.taskView',compact('TaskData','CaseMasterData','TaskCreatedBy','TaskAssignedTo','TaskReminders','TaskChecklist','TaskChecklistCompleted'));     
     exit;   
   }
    /*****************LEAD TASKS**********************/


    /*****************LEAD DETAILS**********************/

    public function leadIno(Request $request)
    {
        $referBy=$notesData=$assignedToData=$CaseNotesData=$allEvents=$totalForm=$totalInvoiceData='';
        $user_id=$request->id;
        // $leadMasterData=LeadAdditionalInfo::find($user_id);
        // print_r($leadMasterData);exit;
    
        $LeadData = User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id');
        $LeadData = $LeadData->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
        $LeadData = $LeadData->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
        $LeadData = $LeadData->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
        $LeadData = $LeadData->leftJoin('countries','users.country','=','countries.id');
        $LeadData = $LeadData->select('countries.name as country_name','lead_additional_info.id as lead_additional_info_id','users.created_by as user_created_by','lead_status.title as lead_status_title','referal_resource.title as referal_resource_title','lead_additional_info.lead_detail',"users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,lead_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"lead_additional_info.*")
        ->where("users.id",$user_id)
        ->first();

        $createdByAndDate = User::find($LeadData['user_created_by']);
       
      

        if(\Route::current()->getName()=="lead_details/info"){
            if($LeadData['refered_by']!=NULL){
                $referBy = User::find($LeadData['refered_by']);
            }
        }
        if(\Route::current()->getName()=="lead_details/notes"){
            $notesData = LeadNotes::leftJoin('lead_notes_activity','lead_notes.note_activity','=','lead_notes_activity.id');
            $notesData = $notesData->leftJoin('users','lead_notes.created_by','=','users.id');
            $notesData = $notesData->select("lead_notes.id as lead_notes_id",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdByName'),"users.user_title","users.id as user_id","lead_notes.updated_at as lead_notes_created_at","lead_notes.*",'lead_notes_activity.*')
            ->where("lead_notes.notes_for",$user_id)
            ->orderBy("lead_notes.created_at","DESC")
            ->get();
        }
        if(\Route::current()->getName()=="lead_details/activity"){
            $notesData = LeadNotes::leftJoin('lead_notes_activity','lead_notes.note_activity','=','lead_notes_activity.id');
            $notesData = $notesData->leftJoin('users','lead_notes.created_by','=','users.id');
            $notesData = $notesData->select("lead_notes.id as lead_notes_id",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdByName'),"users.user_title","users.id as user_id","lead_notes.updated_at as lead_notes_created_at","lead_notes.*",'lead_notes_activity.*')
            ->where("lead_notes.notes_for",$user_id)
            ->orderBy("lead_notes.created_at","DESC")
            ->get();
        }

        if(\Route::current()->getName()=="case_details/info"){
        
            $LeadData = User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id');
            $LeadData = $LeadData->leftJoin('referal_resource','lead_additional_info.referal_source','=','referal_resource.id');
            $LeadData = $LeadData->leftJoin('lead_status','lead_additional_info.lead_status','=','lead_status.id');
            $LeadData = $LeadData->leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id');
            $LeadData = $LeadData->leftJoin('countries','users.country','=','countries.id');
            $LeadData = $LeadData->select('case_practice_area.title as case_practice_area_title','countries.name as country_name','lead_additional_info.id as lead_additional_info_id','users.created_by as user_created_by','lead_status.title as lead_status_title','referal_resource.title as referal_resource_title','lead_additional_info.lead_detail',"users.*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as leadname'),DB::raw('CONCAT_WS(",",users.street,lead_additional_info.address2,users.apt_unit,users.city,users.state,users.postal_code) as full_address'),"lead_additional_info.*")
            ->where("users.id",$user_id)
            ->first();

            if($LeadData['assigned_to']!=NULL){
                $assignedToData=User::select("id","user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as assigned_to_name'))
                ->where("id",$LeadData['assigned_to'])
                ->first();
            }
        }
        if(\Route::current()->getName()=="case_details/activity"){
            $notesData = LeadNotes::leftJoin('lead_notes_activity','lead_notes.note_activity','=','lead_notes_activity.id');
            $notesData = $notesData->leftJoin('users','lead_notes.created_by','=','users.id');
            $notesData = $notesData->select("lead_notes.id as lead_notes_id",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdByName'),"users.user_title","users.id as user_id","lead_notes.updated_at as lead_notes_created_at","lead_notes.*",'lead_notes_activity.*')
            ->where("lead_notes.notes_for",$user_id)
            ->orderBy("lead_notes.created_at","DESC")
            ->get();
        }
        if(\Route::current()->getName()=="case_details/tasks"){
            $notesData = LeadNotes::leftJoin('lead_notes_activity','lead_notes.note_activity','=','lead_notes_activity.id');
            $notesData = $notesData->leftJoin('users','lead_notes.created_by','=','users.id');
            $notesData = $notesData->select("lead_notes.id as lead_notes_id",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdByName'),"users.user_title","users.id as user_id","lead_notes.updated_at as lead_notes_created_at","lead_notes.*",'lead_notes_activity.*')
            ->where("lead_notes.notes_for",$user_id)
            ->orderBy("lead_notes.created_at","DESC")
            ->get();
        }
        if(\Route::current()->getName()=="case_details/notes"){
            $CaseNotesData = CaseNotes::leftJoin('lead_notes_activity','case_notes.note_activity','=','lead_notes_activity.id');
            $CaseNotesData = $CaseNotesData->leftJoin('users','case_notes.created_by','=','users.id');
            $CaseNotesData = $CaseNotesData->select("case_notes.id as lead_notes_id",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdByName'),"users.user_title","users.id as user_id","case_notes.updated_at as lead_notes_created_at","case_notes.*",'lead_notes_activity.*')
            ->where("case_notes.notes_for",$user_id)
            ->orderBy("case_notes.created_at","DESC")
            ->get();
        }
        if(\Route::current()->getName()=="case_details/calendars"){
            //Load only upcoming events
            if(isset($_GET['upcoming_events'])){
              
                //Get all event by 
                $allEvents = CaseEvent::select("*")->where("lead_id",$user_id)->where("start_date",">=",date('Y-m-d'))->orderBy('start_date','ASC')->orderBy('start_time','ASC')->get()
                ->groupBy(function($val) {
                    return Carbon::parse($val->start_date)->format('Y');
                });
            }else{

                //Get all event by 
                $allEvents = CaseEvent::select("*")->where("lead_id",$user_id)->orderBy('start_date','ASC')->orderBy('start_time','ASC')->get()
                ->groupBy(function($val) {
                    return Carbon::parse($val->start_date)->format('Y');
                });
            }
        }

        if(\Route::current()->getName()=="case_details/intake_forms"){
            // $notesData = LeadNotes::leftJoin('lead_notes_activity','lead_notes.note_activity','=','lead_notes_activity.id');
            // $notesData = $notesData->leftJoin('users','lead_notes.created_by','=','users.id');
            // $notesData = $notesData->select("lead_notes.id as lead_notes_id",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdByName'),"users.user_title","users.id as user_id","lead_notes.updated_at as lead_notes_created_at","lead_notes.*",'lead_notes_activity.*')
            // ->where("lead_notes.notes_for",$user_id)
            // ->orderBy("lead_notes.created_at","DESC")
            // ->get();

            $allForms = CaseIntakeForm::leftJoin('intake_form','intake_form.id','=','case_intake_form.intake_form_id');
            $allForms = $allForms->select("intake_form.id as intake_form_id","case_intake_form.created_at as case_intake_form_created_at","intake_form.*","case_intake_form.*");        
            $totalForm=$allForms->count();

        }

        if(\Route::current()->getName()=="case_details/invoices"){
            $PotentialCaseInvoice = PotentialCaseInvoice::select("potential_case_invoice.*");      
            $PotentialCaseInvoice = $PotentialCaseInvoice->where("potential_case_invoice.lead_id",$user_id);        
            $totalInvoiceData=$PotentialCaseInvoice->count();
        }

        //Communication tab [LEADS]
        $totalCalls=$getAllFirmUser='';
        if(\Route::current()->getName()=="communications/text_messages"){

        }
        if(\Route::current()->getName()=="communications/calls"){

            $Calls = Calls::select("calls.*",DB::raw('CONCAT(u1.first_name, " ",u1.last_name) as created_name'),DB::raw('CONCAT(u2.first_name, " ",u2.last_name) as caller_full_name'),DB::raw('CONCAT(u3.first_name, " ",u3.last_name) as call_for_name'));
            $Calls = $Calls->leftJoin('users as u1','calls.created_by','=','u1.id');        
            $Calls = $Calls->leftJoin('users as u2','calls.caller_name','=','u2.id');        
            $Calls = $Calls->leftJoin('users as u3','calls.call_for','=','u3.id');        
            $totalCalls=$Calls->count();
            
            $getAllFirmUser=$this->getAllFirmUser();
            
            $getAllFirmUser =  Calls::select("calls.id as cid","u1.id","u1.first_name","u1.last_name","calls.call_for");
            $getAllFirmUser = $getAllFirmUser->leftJoin('users as u1','calls.call_for','=','u1.id')->groupBy("call_for")->get();
        }

        $CaseMaster = CaseMaster::join('users','users.id','=','case_master.created_by')->select("*","case_master.id as case_id","users.id","users.first_name","users.last_name","users.user_level","users.email","case_master.created_at as case_created_date","case_master.created_by as case_created_by")->where("users.id",$user_id)->first();

        return view('lead.details.index',compact('LeadData','createdByAndDate','user_id','referBy','notesData','LeadData','assignedToData','CaseNotesData','allEvents','CaseMaster','totalForm','totalInvoiceData','totalCalls','getAllFirmUser'));
    }
    public function reactivateLead(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            LeadAdditionalInfo::where('id',$request->user_id)->update(['user_status'=>"1",'do_not_hire_reason'=>NULL,'do_not_hire_on'=>NULL]);
            return response()->json(['errors'=>'','id'=>$request->user_id]);
            exit;
        }
        
    }

    public function doNotHireFromDetail(Request $request)
    {
        $id=$request->id;
        $HireReason=NotHireReasons::where("firm_id",Auth::User()->firm_name)->get();
        return view('lead.details.doNotHire',compact('HireReason','id'));
    }
    public function editLeadFromDetail(Request $request)
    {
        $id=$request->id;
        $UserMaster=User::find($id);
        $LeadAdditionalInfo=LeadAdditionalInfo::where("user_id",$id)->first();
        $country = Countries::get();
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $LeadStatus=LeadStatus::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        
        $getChildUsers=$this->getParentAndChildUserIds();
        $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
        $CaseMasterClient = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        
        $firmStaff = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',3)->where("parent_user",Auth::user()->id)->orWhere("id",Auth::user()->id)->get();
       
        return view('lead.details.editLead',compact('UserMaster','LeadAdditionalInfo','country','ReferalResource','LeadStatus','CasePracticeArea','CaseMasterClient','CaseMasterCompany','firmStaff'));
    }


    public function addLeadPopup(Request $request)
    {
        $id=$request->id;
        $userData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdForName'))->find($id);
        $LeadActivity=LeadNotesActivity::get();
        return view('lead.details.addNote',compact('userData','id','LeadActivity'));
    }

    public function saveNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'notes' => 'required'
        ],[
            'note_date.required' => 'Date is a required field',
            'notes.required' => 'Notes is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            $LeadNotes = new LeadNotes; 
            $LeadNotes->notes_for=$request->user_id;
            $LeadNotes->note_date=date('Y-m-d',strtotime($request->note_date));
            $LeadNotes->note_subject=$request->note_subject;
            $LeadNotes->note_activity=$request->note_activity;
            $LeadNotes->notes=$request->notes;
            $LeadNotes->status="1";
            $LeadNotes->created_by=Auth::User()->id;
            $LeadNotes->created_at=date('Y-m-d H:i:s');
            $LeadNotes->save();

            $noteHistory=[];
            $noteHistory['acrtivity_title']='added';
            $noteHistory['activity_by']=Auth::User()->id;
            $noteHistory['for_lead']=$request->user_id;
            $this->noteActivity($noteHistory);

            session(['popup_success' => 'Note has been created.']);
            return response()->json(['errors'=>'','id'=>$request->user_id]);
            exit;
        }
        
    }
    public function editLeadPopup(Request $request)
    {
        $lead_id=$request->id;
        $LeadNotes=LeadNotes::find($lead_id);
        $userData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdForName'))->find($LeadNotes['notes_for']);
        $LeadActivity=LeadNotesActivity::get();
        return view('lead.details.editNote',compact('userData','lead_id','LeadActivity','LeadNotes'));
    }

    public function updateNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'notes' => 'required'
        ],[
            'note_date.required' => 'Date is a required field',
            'notes.required' => 'Notes is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            $LeadNotes = LeadNotes::find($request->lead_id); 
            $LeadNotes->note_date=date('Y-m-d',strtotime($request->note_date));
            $LeadNotes->note_subject=$request->note_subject;
            $LeadNotes->notes=$request->notes;
            $LeadNotes->note_activity=$request->note_activity;
            $LeadNotes->updated_by=Auth::User()->id;
            $LeadNotes->updated_at=date('Y-m-d H:i:s');
            $LeadNotes->save();

            $noteHistory=[];
            $noteHistory['acrtivity_title']='edited';
            $noteHistory['activity_by']=Auth::User()->id;
            $noteHistory['for_lead']=$request->user_id;
            $this->noteActivity($noteHistory);

            session(['popup_success' => 'Note has been updated.']);
            return response()->json(['errors'=>'','id'=>$LeadNotes->id]);
            exit;
        }
        
    }
    public function deleteNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $LeadNotes=LeadNotes::find($request->note_id);
            $noteHistory=[];
            $noteHistory['acrtivity_title']='deleted';
            $noteHistory['activity_by']=Auth::User()->id;
            $noteHistory['for_lead']=$LeadNotes['notes_for'];
            $this->noteActivity($noteHistory);

            LeadNotes::where('id',$request->note_id)->delete();
            session(['popup_success' => 'Notes has been removed.']);
            return response()->json(['errors'=>'','id'=>$request->note_id]);
            exit;
        }
        
    }

    public function noteActivity($historyData)
    {
        $LeadNotesActivityHistory = new LeadNotesActivityHistory; 
        $LeadNotesActivityHistory->acrtivity_title=$historyData['acrtivity_title'];
        $LeadNotesActivityHistory->activity_by=$historyData['activity_by'];
        $LeadNotesActivityHistory->for_lead =$historyData['for_lead'];
        $LeadNotesActivityHistory->created_by=Auth::User()->id;
        $LeadNotesActivityHistory->created_at=date('Y-m-d H:i:s');
        $LeadNotesActivityHistory->save();
    }

    public function leadActivity()
    {   

        $columns = array('users.id', 'referal_resource.title');
        $requestData= $_REQUEST;
        
        $allLeads = LeadNotesActivityHistory::leftJoin('users','lead_notes_activity_history.activity_by','=','users.id');
        $allLeads = $allLeads->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),"lead_notes_activity_history.*","lead_notes_activity_history.created_at as note_created_at"); 
        $allLeads = $allLeads->where("lead_notes_activity_history.for_lead",$requestData['user_id']);       
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('lead_notes_activity_history.created_at','DESC');
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }
    
    /*****************LEAD DETAILS**********************/


    /*****************CASE DETAILS**********************/


    public function editPotentailCase(Request $request)
    {
        $id=$request->id;
        $UserMaster=User::find($id);
        $LeadAdditionalInfo=LeadAdditionalInfo::where("user_id",$id)->first();
        $country = Countries::get();
        $ReferalResource=ReferalResource::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        $LeadStatus=LeadStatus::select('*')->where('firm_id',Auth::User()->firm_name)->get();
        
        $getChildUsers=$this->getParentAndChildUserIds();
        $CasePracticeArea = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
        $CaseMasterClient = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        
        $firmStaff = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',3)->where("parent_user",Auth::user()->id)->orWhere("id",Auth::user()->id)->get();
        return view('lead.details.case_detail.editPotenatialCase',compact('UserMaster','LeadAdditionalInfo','country','ReferalResource','LeadStatus','CasePracticeArea','CaseMasterClient','CaseMasterCompany','firmStaff'));
    }

    public function savePotentailCase(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'potential_case_title' => 'required|max:2000',
            'date_added' => 'required'
            ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $LeadAdditionalInfoMaster =LeadAdditionalInfo::firstOrNew(array('id' => $request->user_id));
            $LeadAdditionalInfoMaster->potential_case_title=$request->potential_case_title;
            $LeadAdditionalInfoMaster->date_added=($request->date_added) ? date('Y-m-d',strtotime($request->date_added)) : NULL;
            $LeadAdditionalInfoMaster->lead_status=$request->lead_status;
            $LeadAdditionalInfoMaster->practice_area=$request->practice_area;
            $LeadAdditionalInfoMaster->potential_case_value=str_replace(",","",$request->potential_case_value);
            $LeadAdditionalInfoMaster->assigned_to=$request->assigned_to;
            $LeadAdditionalInfoMaster->office="1";
            $LeadAdditionalInfoMaster->potential_case_description=$request->potential_case_description;
            $LeadAdditionalInfoMaster->conflict_check=($request->conflict_check)?'yes':'no';
            if($LeadAdditionalInfoMaster->conflict_check=="no"){
                $LeadAdditionalInfoMaster->conflict_check_at=NULL;
            }else{
                $LeadAdditionalInfoMaster->conflict_check_at=date('Y-m-d h:i:s');
            }
            $LeadAdditionalInfoMaster->conflict_check_description=$request->conflict_check_description;
            $LeadAdditionalInfoMaster->save();

            $caseHistory=[];
            $caseHistory['acrtivity_title']='updated';
            $caseHistory['activity_by']=Auth::User()->id;
            $caseHistory['for_lead']=($LeadAdditionalInfoMaster->user_id)??NULL;
            $caseHistory['type']="2";
            $caseHistory['task_id']=NULL;
            $caseHistory['case_id']=$LeadAdditionalInfoMaster->id;
            $caseHistory['created_by']=Auth::User()->id;
            $caseHistory['created_at']=date('Y-m-d H:i:s');
            $this->saveCaseActivity($caseHistory);


            session(['popup_success' => 'Your potential case has been updated']);
        }
        return response()->json(['errors'=>'','user_id'=>$LeadAdditionalInfoMaster->id]);
        exit;
    }
    public function caseActivityHistory()
    {   
        $requestData= $_REQUEST;
        $allLeads = LeadCaseActivityHistory::leftJoin('users','lead_case_activity_history.activity_by','=','users.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),"lead_case_activity_history.*","lead_case_activity_history.created_at as note_created_at");        
        $allLeads = $allLeads->where("lead_case_activity_history.for_lead",$requestData['user_id']);    
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('lead_case_activity_history.created_at','DESC');
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }

    public function saveCaseActivity($historyData)
    {
        $LeadCaseActivityHistory = new LeadCaseActivityHistory; 
        $LeadCaseActivityHistory->acrtivity_title=$historyData['acrtivity_title'];
        $LeadCaseActivityHistory->activity_by=$historyData['activity_by'];
        $LeadCaseActivityHistory->for_lead =$historyData['for_lead'];
        $LeadCaseActivityHistory->type =$historyData['type'];
        $LeadCaseActivityHistory->task_id =$historyData['task_id'];
        $LeadCaseActivityHistory->case_id =$historyData['case_id'];
        $LeadCaseActivityHistory->created_by=Auth::User()->id;
        $LeadCaseActivityHistory->created_at=date('Y-m-d H:i:s');
        $LeadCaseActivityHistory->save();
    }

    public function addLoadSingleTask(Request $request)
    {
        $user_id=$request->user_id;
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        $country = Countries::get();
        $eventLocation = CaseEventLocation::get();
        $currentDateTime=$this->getCurrentDateAndTime();
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();
         return view('lead.details.case_detail.loadAddTaskPopup',compact('caseLeadList','CaseMasterData','country','currentDateTime','eventLocation','allEventType','user_id'));          
    }

    public function loadAllTaskByLead(){
        
        $requestData= $_REQUEST;
        $task = Task::join("users","task.lead_id","=","users.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        $task = $task->where("users.firm_name",Auth::user()->firm_name);
        $task = $task->where("users.user_type","5");
        $task = $task->where("task.lead_id",$requestData['id']);
        if($requestData['show']=='all'){
            $task = $task->whereIn("task.status",['0','1']);
        }else{
            $task = $task->where("task.status","0");
        }
        $totalData=$task->count();
        $totalFiltered = $totalData; 
        $task = $task->offset($requestData['start'])->limit($requestData['length']);
        $task = $task->orderBy('task.created_at','DESC');
        $task = $task->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $task 
        );
        echo json_encode($json_data);  
    }

    public function addCaseNotePopup(Request $request)
    {
        $id=$request->id;
        $userData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdForName'))->find($id);
        $LeadActivity=LeadNotesActivity::get();
        $LeadAdditionalInfo=LeadAdditionalInfo::select('potential_case_title')->where("user_id",$id)->first();
        $lead_id=$LeadAdditionalInfo->id;
        return view('lead.details.case_detail.addCaseNote',compact('userData','id','LeadActivity','LeadAdditionalInfo','lead_id'));
    }
    public function saveCaseNotePopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'notes' => 'required',
            'note_subject'=> 'max:512'
        ],[
            'note_date.required' => 'Date is a required field',
            'notes.required' => 'Notes is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            $CaseNotes = new CaseNotes; 
            $CaseNotes->notes_for=$request->user_id;
            $CaseNotes->note_date=date('Y-m-d',strtotime($request->note_date));
            $CaseNotes->note_subject=$request->note_subject;
            $CaseNotes->note_activity=$request->note_activity;
            $CaseNotes->notes=$request->notes;
            $CaseNotes->status="1";
            $CaseNotes->created_by=Auth::User()->id;
            $CaseNotes->created_at=date('Y-m-d H:i:s');
            $CaseNotes->save();
            session(['popup_success' => 'Note has been created.']);
            return response()->json(['errors'=>'','id'=>$request->user_id]);
            exit;
        }
        
    }
    public function editCaseNotePopup(Request $request)
    {
        $id=$request->id;
        $LeadNotes=CaseNotes::find($id);
        $userData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as createdForName'))->find($LeadNotes['notes_for']);
        $LeadActivity=LeadNotesActivity::get();
        $LeadAdditionalInfo=LeadAdditionalInfo::select('potential_case_title')->where("user_id",$LeadNotes['notes_for'])->first();

        return view('lead.details.case_detail.editCaseNote',compact('userData','id','LeadActivity','LeadNotes','LeadAdditionalInfo'));
    }

    public function updateCaseNotePopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'notes' => 'required'
        ],[
            'note_date.required' => 'Date is a required field',
            'notes.required' => 'Notes is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            $LeadNotes = CaseNotes::find($request->id); 
            $LeadNotes->note_date=date('Y-m-d',strtotime($request->note_date));
            $LeadNotes->note_subject=$request->note_subject;
            $LeadNotes->notes=$request->notes;
            $LeadNotes->note_activity=$request->note_activity;
            $LeadNotes->updated_by=Auth::User()->id;
            $LeadNotes->updated_at=date('Y-m-d H:i:s');
            $LeadNotes->save();

            session(['popup_success' => 'Note has been updated.']);
            return response()->json(['errors'=>'','id'=>$LeadNotes->id]);
            exit;
        }
        
    }
    public function deleteCaseNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'case_note_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            CaseNotes::where('id',$request->case_note_id)->delete();
            session(['popup_success' => 'Notes has been removed.']);
            return response()->json(['errors'=>'','id'=>$request->case_note_id]);
            exit;
        }
        
    }
    

    public function loadAddEventPage(Request $request)
    {
      $lead_id="";
      if(isset($request->lead_id)){
          $lead_id=$request->lead_id;
      }
      $case_id=$request->case_id;
      $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();

      if(Auth::user()->parent_user==0){
          $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
          $getChildUsers[]=Auth::user()->id;
          $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
      }else{
          $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
          $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
      }

      $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();


      $country = Countries::get();
      $eventLocation = CaseEventLocation::get();
      $currentDateTime=$this->getCurrentDateAndTime();
       //Get event type 
       $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();
      return view('lead.details.case_detail.loadAddEvent',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id','caseLeadList','lead_id'));          
   }


   public function loadEventRightSection(Request $request)
    {       
        $caseLinkeSaved=array();
        $caseLinkeSavedAttending=array();
        $case_id=$request->case_id;
        $task_id=$request->task_id;
        $nonLinkedSaved=[];
        $from=$request->from;
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id")->where("case_client_selection.case_id",$case_id)->get();
        
        $caseNoneLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');
        $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereNotIn('id',$caseNoneLinkedStaffList)->get();

        if(isset($request->task_id) && $request->task_id!=''){
             $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","no")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
        }
       
        
        $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
      
        if(isset($task_id) && $task_id!=''){
          $caseLinkeSaved = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$task_id)->get()->pluck('user_id');
          $caseLinkeSaved= $caseLinkeSaved->toArray();

          $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$task_id)->get()->pluck('user_id');
          $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
        }
       
        return view('lead.details.case_detail.loadCaseRightSection',compact('caseCllientSelection','loadFirmUser','caseLinkeSavedAttending','from','task_id','nonLinkedSaved','caseLinkedStaffList','caseLinkeSaved'));     
        exit;    
   }  
   public function loadAllCaseStaffMember(Request $request)
   {
        $SavedStaff=$from=$alreadySelected=$isAttending='';
        if($request->event_id){
            $alreadySelected = CaseEventLinkedStaff::select("user_id")->where("case_event_linked_staff.event_id",$request->event_id)->pluck("user_id")->toArray();
            $isAttending= CaseEventLinkedStaff::select("user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where("case_event_linked_staff.attending",'yes')->pluck("user_id")->toArray();
            $from="edit";
        }
        $staffData = User::select("first_name","last_name","id","user_level")->where('user_level',3)->where("firm_name",Auth::user()->firm_name)->get();
        return view('lead.details.case_detail.firmStaff',compact('SavedStaff','from','staffData','alreadySelected','isAttending'));     
        exit;    
    }

    public function loadEditEventPage(Request $request)
    {

          $evnt_id=$request->evnt_id;
          $evetData=CaseEvent::find($evnt_id);
          $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();

          $case_id=$evetData->case_id;
          $lead_id=$evetData->lead_id;
          $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
          // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
          if(Auth::user()->parent_user==0){
              $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
              $getChildUsers[]=Auth::user()->id;
              $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
          }else{
              $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
              $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
          }

          $country = Countries::get();
          $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
          $currentDateTime=$this->getCurrentDateAndTime();
      
          //Get event type 
          $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();

          //Event created By user name
          $userData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->created_by)->first();
      
          $updatedEvenByUserData='';
          if($evetData->updated_by!=NULL){
              //Event updated By user name
              $updatedEvenByUserData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->updated_by)->first();
          }
          $eventLocationAdded=[];
          if($evetData->event_location_id!=""){
              $eventLocationAdded = CaseEventLocation::where("id",$evetData->event_location_id)->first();
          }
      
          $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type)->pluck('color_code');

          $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();

          return view('lead.details.case_detail.loadEditEvent',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode','eventLocationAdded','caseLeadList','lead_id'));          
   }
   public function loadSingleEditEventPage(Request $request)
   {

         $evnt_id=$request->evnt_id;
         $evetData=CaseEvent::find($evnt_id);
         $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();

         $case_id=$evetData->case_id;   
         $lead_id=$evetData->lead_id;
         $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
      //    $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
      if(Auth::user()->parent_user==0){
          $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
          $getChildUsers[]=Auth::user()->id;
          $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
      }else{
          $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
          $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
      }

      $country = Countries::get();
         $eventLocation = CaseEventLocation::get();
         $currentDateTime=$this->getCurrentDateAndTime();
     
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();

         //Event created By user name
         $userData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->created_by)->first();
     
         $updatedEvenByUserData='';
         if($evetData->updated_by!=NULL){
             //Event updated By user name
             $updatedEvenByUserData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->updated_by)->first();
         }
     
         $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type)->pluck('color_code');
         $eventLocationAdded=[];
         if($evetData->event_location_id!=""){
            
             $eventLocationAdded = CaseEventLocation::where("id",$evetData->event_location_id)->first();
           
         }


         $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
          
         return view('lead.details.case_detail.loadSingleEditEvent',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode','eventLocationAdded','caseLeadList','lead_id'));          
  }


  public function saveEditEventPage(Request $request)
  {
    $validator = \Validator::make($request->all(), [
        'linked_staff_checked_share' => 'required'
    ]);
    if($validator->fails())
    {
        return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
    }
    
    if($request->delete_event_type=='SINGLE_EVENT'){
        $CaseEvent=CaseEvent::find($request->event_id);

        $start_date = date("Y-m-d",  strtotime($request->start_date));
        $start_time = date("H:i:s", strtotime($request->start_time));
        $end_date = date("Y-m-d",  strtotime($request->end_date));
        $end_time = date("H:i:s", strtotime($request->end_time));
       
        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
        // if(!isset($request->no_case_link)){
        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
        // }else{
        //     $CaseEvent->case_id=NULL; 
        // }

        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $CaseEvent->case_id=$request->text_case_id; 
                }    
                if($request->text_lead_id!=''){
                    $CaseEvent->lead_id=$request->text_lead_id; 
                }    
            } 
        }
        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
        if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
        if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
        if(isset($request->all_day)) { $CaseEvent->all_day="yes";
            $CaseEvent->start_time=NULL;
            $CaseEvent->end_time=NULL;
         }else{ $CaseEvent->all_day="no";} 
        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
        $CaseEvent->recuring_event="no"; 
        if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }

        //If new location is creating.
        if($request->location_name!=''){
            $CaseEventLocation = new CaseEventLocation;
            $CaseEventLocation->location_name=$request->location_name;
            $CaseEventLocation->address1=$request->address;
            $CaseEventLocation->address2=$request->address2;
            $CaseEventLocation->city=$request->city;
            $CaseEventLocation->state=$request->state;
            $CaseEventLocation->postal_code=$request->postal_code;
            $CaseEventLocation->country=$request->country;
            $CaseEventLocation->location_future_use=($request->location_future_use)?'yes':'no';
            $CaseEventLocation->created_by=Auth::user()->id; 
            $CaseEventLocation->save();
            $CaseEvent->event_location_id =$CaseEventLocation->id;
        }
       if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
        $CaseEvent->parent_evnt_id ='0';
        $CaseEvent->updated_by=Auth::user()->id; 
        $CaseEvent->save();
        $this->saveEventReminder($request->all(),$CaseEvent->id); 
        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
        $this->saveEventHistory($CaseEvent->id);
        

    }elseif($request->delete_event_type=='THIS_AND_FOLLOWING_EVENTS'){
        $CaseEvent=CaseEvent::find($request->event_id);
       
        if(!isset($request->recuring_event)){
            CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id)->where('id',"!=",$request->event_id)->delete();
            $start_date = date("Y-m-d", strtotime($request->start_date));
            $start_time = date("H:i:s", strtotime($request->start_time));
            $end_date = date("Y-m-d", strtotime($request->end_date));
            $end_time = date("H:i:s", strtotime($request->end_time));
            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
            // if(!isset($request->no_case_link)){
            //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
            // }
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $CaseEvent->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $CaseEvent->lead_id=$request->text_lead_id; 
                    }    
                } 
            }
            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
            if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
            if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
            $CaseEvent->recuring_event="no"; 
            $CaseEvent->event_frequency=NULL; 
            $CaseEvent->event_interval_day=NULL; 
            $CaseEvent->daily_weekname=NULL; 
            $CaseEvent->end_on=NULL; 
            if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }

            //If new location is creating.
            if( $request->location_name!=''){ //$request->case_location_list=="0" &&
                $CaseEventLocation = new CaseEventLocation;
                $CaseEventLocation->location_name=$request->location_name;
                $CaseEventLocation->address1=$request->address;
                $CaseEventLocation->address2=$request->address2;
                $CaseEventLocation->city=$request->city;
                $CaseEventLocation->state=$request->state;
                $CaseEventLocation->postal_code=$request->postal_code;
                $CaseEventLocation->country=$request->country;
                $CaseEventLocation->location_future_use=($request->location_future_use)?'yes':'no';
                $CaseEventLocation->created_by=Auth::user()->id; 
                $CaseEventLocation->save();
                $CaseEvent->event_location_id =$CaseEventLocation->id;
            }
            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
            $CaseEvent->parent_evnt_id ='0';
            $CaseEvent->updated_by=Auth::user()->id; 
            $CaseEvent->save();
        }else{
            $startTime = strtotime($request->start_date);
            $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
            if($request->end_on!=''){
                $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
            if($request->event_frequency=='DAILY')
            {
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->delete();
              
                $i=0;
                $event_interval_day=$request->event_interval_day;
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                    $start_date = date("Y-m-d",$startTime);
                    $start_time = date("H:i:s",strtotime($request->start_time));
                    $end_date = date("Y-m-d",$startTime);
                    $end_time = date("H:i:s",strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    // if(!isset($request->no_case_link)){
                    //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    // }
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date;} 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                        $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_day=$request->event_interval_day;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->updated_by=Auth::user()->id; 
                    $CaseEvent->created_by=$OldCaseEvent->created_by;
                    $CaseEvent->created_at=$OldCaseEvent->created_at; 
                    $CaseEvent->parent_evnt_id =  $OldCaseEvent->parent_evnt_id; 
                    $CaseEvent->save();
                   
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id);   
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveEventHistory($CaseEvent->id);

                    $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                    $i++;
                } while ($startTime <= $endTime);

               
            }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
            { 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->delete();
               //If new location is creating.
               if($request->location_name!=''){
                $locationID= $this->saveLocationOnce($request);
            }
            
                do {
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );            
                    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                    }else {
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        // if(!isset($request->no_case_link)){
                        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        // }
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                       
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->parent_evnt_id =  $OldCaseEvent->id;                             
                        $CaseEvent->save();
                       
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                    }
                    $i++;
                    $startTime = strtotime('+1 day',$startTime); 
                    } while ($startTime <= $endTime);
                   
            }else if($request->event_frequency=='WEEKLY')
            {
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->delete();
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );       
                    if ($weekday==date("l")) { 
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        // if(!isset($request->no_case_link)){
                        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        // }
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                       
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->parent_evnt_id = $OldCaseEvent->parent_evnt_id;

                        $CaseEvent->save();
                       
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                    }  $startTime = strtotime('+1 day',$startTime); 
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='CUSTOM')
            { 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->delete();

                $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                $start = new DateTime($weekFirstDay);
                $startClone = new DateTime($weekFirstDay);

                
            
                if($request->end_on!=''){
                    $end=new DateTime($request->end_on);
                }else{
                    $end=$startClone->add(new DateInterval('P365D'));
                }
                //$end = new DateTime( '2021-09-28 23:59:59');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
                $weekInterval = $request->daily_weekname;
                $fakeWeek = 0;
                $currentWeek = $start->format('W');
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                foreach ($period as $date) {
                    if ($date->format('W') !== $currentWeek) {
                        $currentWeek = $date->format('W');
                        $fakeWeek++;
                    }
                
                    if ($fakeWeek % $weekInterval !== 0) {
                        continue;
                    }
                
                    $dayOfWeek = $date->format('l');
                    if(in_array($dayOfWeek,$request->custom)){

                        $start_date = $date->format('Y-m-d');
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date =$date->format('Y-m-d');
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        // if(!isset($request->no_case_link)){
                        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        // }
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                       
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->parent_evnt_id=$OldCaseEvent->parent_evnt_id; 
                        $CaseEvent->save();
                       
                        $i++;
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);


                    }
                }
            
            }else if($request->event_frequency=='MONTHLY')
            { 
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->delete();
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }

                do {
                
                    $monthly_frequency=$request->monthly_frequency;
                    $event_interval_month=$request->event_interval_month;
                    if($monthly_frequency=='MONTHLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($monthly_frequency=='MONTHLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $fourthDay);
                    }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $lastDay);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    // if(!isset($request->no_case_link)){
                    //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    // }
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                        $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    $CaseEvent->event_interval_month=$request->event_interval_month;
                    $CaseEvent->monthly_frequency=$request->monthly_frequency;
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->updated_by=Auth::user()->id; 
                    $CaseEvent->created_by=$OldCaseEvent->created_by;
                    $CaseEvent->created_at=$OldCaseEvent->created_at; 
                    $CaseEvent->parent_evnt_id=$OldCaseEvent->parent_evnt_id; 
                    $CaseEvent->save();
                    
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveEventHistory($CaseEvent->id);

                    $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='YEARLY'){ 
                $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                $yearly_frequency=$request->yearly_frequency;
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->delete();
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }

                do {
                    $event_interval_year=$request->event_interval_year;
                    if($yearly_frequency=='YEARLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                    //    echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // echo date('Y-m-d', $startTime);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    // if(!isset($request->no_case_link)){
                    //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    // }
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                        $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_year=$request->event_interval_year;
                    $CaseEvent->yearly_frequency=$request->yearly_frequency;

                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->updated_by=Auth::user()->id; 
                    $CaseEvent->created_by=$OldCaseEvent->created_by;
                    $CaseEvent->created_at=$OldCaseEvent->created_at; 
                    $CaseEvent->parent_evnt_id=$OldCaseEvent->parent_evnt_id; 
                    $CaseEvent->save();
                   
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveEventHistory($CaseEvent->id);

                    
                    $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }
        }

    }elseif($request->delete_event_type=='ALL_EVENTS'){
        $CaseEvent=CaseEvent::find($request->event_id);
       
        if(!isset($request->recuring_event)){
            CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id)->where('id',"!=",$request->event_id)->delete();
            $start_date = date("Y-m-d", strtotime($request->start_date));
            $start_time = date("H:i:s", strtotime($request->start_time));
            $end_date = date("Y-m-d", strtotime($request->end_date));
            $end_time = date("H:i:s", strtotime($request->end_time));
            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
            // if(!isset($request->no_case_link)){
            //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
            // }
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $CaseEvent->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $CaseEvent->lead_id=$request->text_lead_id; 
                    }    
                } 
            }
            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
            if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
            if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
            $CaseEvent->recuring_event="no"; 
            $CaseEvent->event_frequency=NULL; 
            $CaseEvent->event_interval_day=NULL; 
            $CaseEvent->daily_weekname=NULL; 
            $CaseEvent->end_on=NULL; 
            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                $CaseEvent->event_location_id=$request->case_location_list; 
            }else{  
                $CaseEvent->event_location_id=($locationID)??NULL;
            }   
            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
            $CaseEvent->parent_evnt_id ='0';
            $CaseEvent->updated_by=Auth::user()->id; 
            $CaseEvent->save();
        }else{
            $startTime = strtotime(date('Y-m-d'));
            $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
            if($request->end_on!=''){
                $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
            if($request->event_frequency=='DAILY')
            {
                $OldCaseEvent=CaseEvent::find($request->event_id);
                $OldCaseEventForDate=CaseEvent::where("id",$OldCaseEvent->parent_evnt_id)->first();
                $startTime = strtotime($OldCaseEventForDate->start_date);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->delete();
               
                $i=0;
                $event_interval_day=$request->event_interval_day;
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                    $start_date = date("Y-m-d",$startTime);
                    $start_time = date("H:i:s",strtotime($request->start_time));
                    $end_date = $start_date;
                    $end_time = date("H:i:s",strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    // if(!isset($request->no_case_link)){
                    //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    // }
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date;} 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                        $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_day=$request->event_interval_day;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->updated_by=Auth::user()->id; 
                    $CaseEvent->created_by=$OldCaseEvent->created_by;
                    $CaseEvent->created_at=$OldCaseEvent->created_at; 
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id);                               
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                     $this->saveEventHistory($CaseEvent->id);

                    $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                    $i++;
                } while ($startTime <= $endTime);
            }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
            { 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->delete();
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );            
                    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                    }else {
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        // if(!isset($request->no_case_link)){
                        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        // }
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at;                             
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                    }
                    $i++;
                    $startTime = strtotime('+1 day',$startTime); 
                    } while ($startTime <= $endTime);
                   
            }else if($request->event_frequency=='WEEKLY')
            {
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->delete();
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );       
                    if ($weekday==date("l")) { 
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        // if(!isset($request->no_case_link)){
                        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        // }

                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                       
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at;    
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                    }  $startTime = strtotime('+1 day',$startTime); 
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='CUSTOM')
            { 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->delete();
                $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                $start = new DateTime($weekFirstDay);
                $startClone = new DateTime($weekFirstDay);

                if($request->end_on!=''){
                    $end=new DateTime($request->end_on);
                }else{
                    $end=$startClone->add(new DateInterval('P365D'));
                }
                //$end = new DateTime( '2021-09-28 23:59:59');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
                $weekInterval = $request->daily_weekname;
                $fakeWeek = 0;
                $currentWeek = $start->format('W');
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                foreach ($period as $date) {
                    if ($date->format('W') !== $currentWeek) {
                        $currentWeek = $date->format('W');
                        $fakeWeek++;
                    }
                
                    if ($fakeWeek % $weekInterval !== 0) {
                        continue;
                    }
                
                    $dayOfWeek = $date->format('l');
                    if(in_array($dayOfWeek,$request->custom)){

                        $start_date = $date->format('Y-m-d');
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date =$date->format('Y-m-d');
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        // if(!isset($request->no_case_link)){
                        //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        // }
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        }
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                       
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $i++;
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);
                    }
                }
            
            }else if($request->event_frequency=='MONTHLY')
            { 
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->delete();
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                
                    $monthly_frequency=$request->monthly_frequency;
                    $event_interval_month=$request->event_interval_month;
                    if($monthly_frequency=='MONTHLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($monthly_frequency=='MONTHLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $fourthDay);
                    }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $lastDay);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    // if(!isset($request->no_case_link)){
                    //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    // }
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes";  $CaseEvent->start_time=NULL;
                        $CaseEvent->end_time=NULL;}else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    $CaseEvent->event_interval_month=$request->event_interval_month;
                    $CaseEvent->monthly_frequency=$request->monthly_frequency;
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->updated_by=Auth::user()->id; 
                    $CaseEvent->created_by=$OldCaseEvent->created_by;
                    $CaseEvent->created_at=$OldCaseEvent->created_at; 
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveEventHistory($CaseEvent->id);

                    
                    $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='YEARLY'){ 
                $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                $yearly_frequency=$request->yearly_frequency;
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                $OldCaseEvent=CaseEvent::find($request->event_id);
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->delete();
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                
                do {
                    $event_interval_year=$request->event_interval_year;
                    if($yearly_frequency=='YEARLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                    //    echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // echo date('Y-m-d', $startTime);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    // if(!isset($request->no_case_link)){
                    //     if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    // }
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes";  $CaseEvent->start_time=NULL;
                        $CaseEvent->end_time=NULL;}else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_year=$request->event_interval_year;
                    $CaseEvent->yearly_frequency=$request->yearly_frequency;

                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                  if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->updated_by=Auth::user()->id; 
                    $CaseEvent->created_by=$OldCaseEvent->created_by;
                    $CaseEvent->created_at=$OldCaseEvent->created_at; 
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveEventHistory($CaseEvent->id);
                    
                    $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }
        }
    }
    session(['popup_success' => 'Event was updated.']);
    return response()->json(['errors'=>'']);
    exit;
  }


 
        /*****************CASE DETAILS**********************/


        /***********************LEAD EVENT***************************** */
    public function saveCaseEvent(Request $request)
    {
        $CommonController= new CommonController();
        $validator = \Validator::make($request->all(), [
            'linked_staff_checked_share' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
        }
        //Single event
        if(!isset($request->recuring_event)){
            $startTime = strtotime(date("Y-m-d", strtotime($request->start_date)));
            $endTime = strtotime(date("Y-m-d",strtotime($request->end_date)));
        }else{
            //recurring event
            $startTime = strtotime(date("Y-m-d",  strtotime($request->start_date)));
             $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
             if($request->end_on!=''){
                $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
        }
        if(!isset($request->recuring_event)){
           
            //If new location is creating.
            if($request->location_name!=''){
                $locationID= $this->saveLocationOnce($request);
              }

            $start_date = date("Y-m-d", $startTime);
            $start_time = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)),Auth::User()->user_timezone)));
            $end_date = date("Y-m-d", $endTime);
            $end_time = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)),Auth::User()->user_timezone)));
            $CaseEvent = new CaseEvent;
            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $CaseEvent->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $CaseEvent->lead_id=$request->text_lead_id; 
                    }    
                } 
            }
            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
            if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
            if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
            $CaseEvent->recuring_event="no"; 
            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                $CaseEvent->event_location_id=$request->case_location_list; 
            }else{  
                $CaseEvent->event_location_id=($locationID)??NULL;
                
            }
            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
            $CaseEvent->parent_evnt_id ='0';

            $CaseEvent->created_by=Auth::user()->id; 
            $CaseEvent->save();
            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            // $this->saveEventHistory($CaseEvent->id);
              
              
        }else{
            if($request->event_frequency=='DAILY')
            {
                $i=0;
                $event_interval_day=$request->event_interval_day;

               
                 //If new location is creating.
                 if($request->location_name!=''){
                   $locationID= $this->saveLocationOnce($request);
                 }

                do {
                    
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_day=$request->event_interval_day;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                        
                    } 
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->created_by=Auth::user()->id; 
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    // $this->saveEventHistory($CaseEvent->id);
                    
                    $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                    $i++;
                } while ($startTime < $endTime);
            }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
            { 
                $i=0;
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                do {
                   
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );            
                    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                    }else {
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                      
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        } 
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        // $this->saveEventHistory($CaseEvent->id);
                    }
                    $i++;
                    $startTime = strtotime('+1 day',$startTime); 
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='WEEKLY')
            {
                $i=0;
 
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                 do {
                
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );       
                    if ($weekday==date("l")) { 
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        // $this->saveEventHistory($CaseEvent->id);

                    }  $startTime = strtotime('+1 day',$startTime); 
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='CUSTOM')
            { 
                $i=0;
                $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                $start = new DateTime($weekFirstDay);
                $startClone = new DateTime($weekFirstDay);

                
               
                if($request->end_on!=''){
                    $end=new DateTime($request->end_on);
                }else{
                    $end=$startClone->add(new DateInterval('P365D'));
                }
                //$end = new DateTime( '2021-09-28 23:59:59');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
                $weekInterval = $request->daily_weekname;
                $fakeWeek = 0;
                $currentWeek = $start->format('W');
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                foreach ($period as $date) {
                    if ($date->format('W') !== $currentWeek) {
                        $currentWeek = $date->format('W');
                        $fakeWeek++;
                    }
                
                    if ($fakeWeek % $weekInterval !== 0) {
                        continue;
                    }
                
                    $dayOfWeek = $date->format('l');
                    if(in_array($dayOfWeek,$request->custom)){

                        $start_date = $date->format('Y-m-d');
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date =$date->format('Y-m-d');
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $i++;
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        // $this->saveEventHistory($CaseEvent->id);
                    }
                }
               
            }else if($request->event_frequency=='MONTHLY')
            { 
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                 do {
                
                    $monthly_frequency=$request->monthly_frequency;
                    $event_interval_month=$request->event_interval_month;
                    if($monthly_frequency=='MONTHLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($monthly_frequency=='MONTHLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $fourthDay);
                    }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $lastDay);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    $CaseEvent->event_interval_month=$request->event_interval_month;
                    $CaseEvent->monthly_frequency=$request->monthly_frequency;
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->created_by=Auth::user()->id; 
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    //  $this->saveEventHistory($CaseEvent->id);
                    $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='YEARLY'){ 
                $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                $yearly_frequency=$request->yearly_frequency;
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                do {
                    $event_interval_year=$request->event_interval_year;
                    if($yearly_frequency=='YEARLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                    //    echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // echo date('Y-m-d', $startTime);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_year=$request->event_interval_year;
                    $CaseEvent->yearly_frequency=$request->yearly_frequency;

                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->created_by=Auth::user()->id; 
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    // $this->saveEventHistory($CaseEvent->id);

                    
                    $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }
        }
       
        session(['popup_success' => 'Event was added.']);
        return response()->json(['errors'=>''   ]);
        exit;
      }


      public function saveEventData($request){
        //If new location is creating.
        if($request->location_name!=''){
            $locationID= $this->saveLocationOnce($request);
        }

        $start_date = date("Y-m-d", $startTime);
        $start_time = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)),Auth::User()->user_timezone)));
        $end_date = date("Y-m-d", $endTime);
        $end_time = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)),Auth::User()->user_timezone)));
        $CaseEvent = new CaseEvent;
        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
        }
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $CaseEvent->case_id=$request->text_case_id; 
                }    
                if($request->text_lead_id!=''){
                    $CaseEvent->lead_id=$request->text_lead_id; 
                }    
            } 
        }
        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
        if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
        if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
        $CaseEvent->recuring_event="no"; 
        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
            $CaseEvent->event_location_id=$request->case_location_list; 
        }else{  
            $CaseEvent->event_location_id=($locationID)??NULL;
            
        }
        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
        $CaseEvent->parent_evnt_id ='0';

        $CaseEvent->created_by=Auth::user()->id; 
        $CaseEvent->save();
        $this->saveEventReminder($request->all(),$CaseEvent->id); 
        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
      }

      public function saveLocationOnce($locationdata){
      
        $CaseEventLocation = new CaseEventLocation;
        $CaseEventLocation->location_name=$locationdata['location_name'];
        $CaseEventLocation->address1=$locationdata['address'];
        $CaseEventLocation->address2=$locationdata['address2'];
        $CaseEventLocation->city=$locationdata['city'];
        $CaseEventLocation->state=$locationdata['state'];
        $CaseEventLocation->postal_code=$locationdata['postal_code'];
        $CaseEventLocation->country=$locationdata['country'];
        $CaseEventLocation->location_future_use=($locationdata['location_future_use'])?'yes':'no';
        $CaseEventLocation->created_by=Auth::user()->id; 
        $CaseEventLocation->save();
        return $CaseEventLocation->id;
    }
    public function saveEventHistory($request)
    {
        $CaseEventComment =new CaseEventComment;
        $CaseEventComment->event_id=$request;
        $CaseEventComment->comment=NULL;
        $CaseEventComment->created_by=Auth::user()->id; 
        $CaseEventComment->action_type="1";
        $CaseEventComment->save();
    }
    public function saveEventReminder($request,$event_id)
     {
        CaseEventReminder::where("event_id", $event_id)->where("created_by", Auth::user()->id)->delete();

        for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
            $CaseEventReminder = new CaseEventReminder;
            $CaseEventReminder->event_id=$event_id; 
            $CaseEventReminder->reminder_type=$request['reminder_type'][$i];
            $CaseEventReminder->reminer_number=$request['reminder_number'][$i];
            $CaseEventReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
            $CaseEventReminder->reminder_user_type=$request['reminder_user_type'][$i];
            $CaseEventReminder->created_by=Auth::user()->id; 
            $CaseEventReminder->save();
        }
    }
    public function saveLinkedStaffToEvent($request,$event_id)
    {
       
        CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","yes")->delete();
        if(isset($request['linked_staff_checked_share'])){
            $alreadyAdded=[];
            for($i=0;$i<count($request['linked_staff_checked_share']);$i++){
                $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                $CaseEventLinkedStaff->event_id=$event_id; 
                $CaseEventLinkedStaff->user_id=$request['linked_staff_checked_share'][$i];
                if(isset($request['linked_staff_checked_attend'][$i])){
                    $attend="yes";
                }else{
                    $attend="no";
                }
                $CaseEventLinkedStaff->is_linked='yes';
                $CaseEventLinkedStaff->attending=$attend;
                $CaseEventLinkedStaff->created_by=Auth::user()->id; 
                if(!in_array($request['linked_staff_checked_share'][$i],$alreadyAdded)){
                    $CaseEventLinkedStaff->save();
                }
                $alreadyAdded[]=$request['linked_staff_checked_share'][$i];
            }
        }
   }
   public function saveNonLinkedStaffToEvent($request,$event_id)
    {
       
        CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","no")->delete();
        if(isset($request['share_checkbox_nonlinked'])){
            $alreadyAdded=[];
            for($i=0;$i<count(array_unique($request['share_checkbox_nonlinked']));$i++){
                $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                $CaseEventLinkedStaff->event_id=$event_id; 
                $CaseEventLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                if(isset($request['share_checkbox_nonlinked'][$i])){
                    $attend="yes";
                }else{
                    $attend="no";
                }
                
                $CaseEventLinkedStaff->is_linked='no';
                $CaseEventLinkedStaff->attending=$attend;
                $CaseEventLinkedStaff->created_by=Auth::user()->id; 
                if(!in_array($request['share_checkbox_nonlinked'][$i],$alreadyAdded)){
                    $CaseEventLinkedStaff->save();
                }
                $alreadyAdded[]=$request['share_checkbox_nonlinked'][$i];
            }
        }
   }
        /***********************LEAD EVENT***************************** */


    /***********************INTAKE FORMS***************************** */

        public function loadIntakeForms()
        {   
            $requestData= $_REQUEST;
            $allForms = CaseIntakeForm::leftJoin('intake_form','intake_form.id','=','case_intake_form.intake_form_id');
            $allForms = $allForms->select("intake_form.id as intake_form_id","case_intake_form.created_at as case_intake_form_created_at","intake_form.*","case_intake_form.*");      
            $allForms = $allForms->where("lead_id",$requestData['id']);  
            $totalData=$allForms->count();
            $totalFiltered = $totalData; 
         
            $allForms = $allForms->offset($requestData['start'])->limit($requestData['length']);
            $allForms = $allForms->orderBy('case_intake_form.created_at','DESC');
            $allForms = $allForms->get();
            $json_data = array(
                "draw"            => intval( $requestData['draw'] ),   
                "recordsTotal"    => intval( $totalData ),  
                "recordsFiltered" => intval( $totalFiltered ), 
                "data"            => $allForms 
            );
            echo json_encode($json_data);  
        }

        public function deleteIntakeFormFromList(Request $request)
        {
            $intakeForm=IntakeForm::where("id",$request->id)->first();
            $primary_id=$request->primary_id;
            return view('lead.details.case_detail.deleteIntakeform',compact('intakeForm','primary_id'));
        } 

        public function saveDeleteIntakeFormFromList(Request $request)
        {
            $validator = \Validator::make($request->all(), [
                'id' => 'required'
            ]);
            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()->all()]);
            }else{
                    
                $id=$request->id;
                CaseIntakeForm::where("id", $id)->delete();
                session(['popup_success' => 'Successfully deleted intake form.']);
                return response()->json(['errors'=>'']);
                exit;   
            }
        }   
        public function CaseFormSent(Request $request)
        {
            $formId=$request->id;
            $caseIntakeForm=CaseIntakeForm::where("form_unique_id",$formId)->first();
            if(empty($caseIntakeForm)){
                return view('pages.404');
            }else{
                $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
                $intakeFormFields=IntakeFormFields::where("intake_form_id",$intakeForm['id'])->orderBy("sort_order","ASC")->get();
                $firmData=Firm::find($caseIntakeForm['firm_id']);
                $country = Countries::get();
                $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm['id'])->first();
                
                return view('intake_forms.formSent',compact('intakeForm','intakeFormFields','firmData','country','alreadyFilldedData','caseIntakeForm','formId'));
            }
                
        } 
        public function contact_us(Request $request)
        {
            $formId=$request->id;
            $caseIntakeForm=IntakeForm::where("form_unique_id",$formId)->first();
            if(empty($caseIntakeForm)){
                return view('pages.404');
            }else{
                $intakeForm=$caseIntakeForm;
                $intakeFormFields=IntakeFormFields::where("intake_form_id",$intakeForm['id'])->orderBy("sort_order","ASC")->get();
                $firmData=Firm::find($caseIntakeForm['firm_name']);
                $country = Countries::get();
                $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm['id'])->first();
                
                return view('intake_forms.contact_us_sent',compact('intakeForm','intakeFormFields','firmData','country','alreadyFilldedData','caseIntakeForm'));
            }
                
        } 
        public function popupOpenSendEmailIntakeFormFromList(Request $request)
        {
            $formId=$request->form_id;
            $caseIntakeForm=CaseIntakeForm::where("intake_form_id",$formId)->where("lead_id",$request->lead_id)->first();
            $intakeForm=IntakeForm::where("id",$formId)->first();
           
            $firmData=Firm::find(Auth::User()->firm_name);
    
            $leadData=User::find($request->lead_id);           
            return view('lead.details.case_detail.emailIntakeForm',compact('intakeForm','formId','firmData','caseIntakeForm','leadData'));

        }

        public function sendEmailIntakeFormPC(Request $request)
        {
           
            $validator = \Validator::make($request->all(), [
                'email_address' => 'required|email'
            ]);
            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()->all()]);
            }else{
                    
                $form_id=$request->form_id;
                $CaseIntakeForm =CaseIntakeForm::find($form_id);
                $CaseIntakeForm->status="0";
                $CaseIntakeForm->save();
                $CaseIntakeForm=CaseIntakeForm::where("id",$form_id)->first();

                $getTemplateData = EmailTemplate::find(7);
                $fullName=$request->first_name. ' ' .$request->last_name;
                $email=$request->email;
                $token=url('cform', $CaseIntakeForm->form_unique_id);
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{message}', $request->email_message, $mail_body);
                $mail_body = str_replace('{email}', $email,$mail_body);
                $mail_body = str_replace('{token}', $token,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', REGARDS, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        
    
                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => FROM_EMAIL_TITLE,
                    "subject" => $request->email_suubject,
                    "to" => $request->email_address,
                    "full_name" => "",
                    "mail_body" => $mail_body
                ];
                $sendEmail = $this->sendMail($user);
                session(['popup_success' => 'Email sent successfully.']);
                return response()->json(['errors'=>'']);
                exit;   
         }
    }

    public function addIntakeForm(Request $request)
    {
        $lead_id=$request->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $leadInfo=User::select('email')->Where('id',$lead_id)->first();
        $IntakeForm=IntakeForm::where("firm_name",Auth::User()->firm_name)->get();
        return view('lead.details.case_detail.addIntakeForm',compact('IntakeForm','firmData','lead_id','leadInfo'));

    }
    public function saveIntakeForm(Request $request)
    {
        // print_r($request->all());
        // exit;
        // return response()->json(['errors'=>'']);
        //     exit;  
        $validator = \Validator::make($request->all(), [
            'intake_form' => 'required',
            'email_address' => 'required|email'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $CaseIntakeForm = new CaseIntakeForm;
            $CaseIntakeForm->intake_form_id=$request->intake_form; 
            $CaseIntakeForm->form_unique_id=md5(time());
            if($request->current_submit=="savenow"){
                $CaseIntakeForm->status="0";
            }else{
                $CaseIntakeForm->status="3";
            }
          
            $CaseIntakeForm->submited_at=date('Y-m-d h:i:s');
            $CaseIntakeForm->firm_id=Auth::user()->firm_name;
            $CaseIntakeForm->submited_to=$request->email_address;
            $CaseIntakeForm->lead_id=$request->lead_id;
            $CaseIntakeForm->unique_token=$this->generateUniqueToken();
            $CaseIntakeForm->created_by=Auth::user()->id; 
            $CaseIntakeForm->save();

            $form_id=$request->intake_form;
            $intakeForm=IntakeForm::where("id",$form_id)->first();
            
            $getTemplateData = EmailTemplate::find(7);
            $email=$request->email;
            $token=url('cform', $CaseIntakeForm->form_unique_id);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', $request->email_message, $mail_body);
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{token}', $token,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', REGARDS, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        

            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $request->email_subject,
                "to" => $request->email_address,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            if($request->current_submit=="savenow"){
                $sendEmail = $this->sendMail($user);
            }
            session(['popup_success' => 'Added intake form.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   
    public function collectFormData(Request $request)
    {
        $intakeForm=IntakeFormFields::where("intake_form_id",$request->form_id)->get();
        $requiredArray=[];
        foreach($intakeForm as $k=>$v){
            //This valdiation for contact fields only.
            if($v->form_field=="name" && $v->is_required=="yes"){
                $requiredArray=array("first_name"=>"required|min:1","last_name"=>"required");
            }
            if($v->form_field=="email" && $v->is_required=="yes"){
                $requiredArray=array("email"=>"required|email");
            }
            if($v->form_field=="address" && $v->is_required=="yes"){
                $requiredArray=array("email"=>"required");
            }
            if($v->form_field=="home_phone" && $v->is_required=="yes"){
                $requiredArray=array("home_phone"=>"required|numeric");
            }
            if($v->form_field=="cell_phone" && $v->is_required=="yes"){
                $requiredArray=array("cell_phone"=>"required|numeric");
            }
            if($v->form_field=="work_phone" && $v->is_required=="yes"){
                $requiredArray=array("work_phone"=>"required|numeric");
            }
            if($v->form_field=="birthday" && $v->is_required=="yes"){
                $requiredArray=array("birthday"=>"required");
            }
            if($v->form_field=="driver_license" && $v->is_required=="yes"){
                $requiredArray=array("driver_license_number"=>"required","driver_license_state"=>"required");
            }

            if($v->form_field=="long_text" && $v->is_required=="yes"){
                $requiredArray=array("long_text"=>"required");
                if($v->client_friendly_lable!=NULL){
                    $f=$v->client_friendly_lable;
                }else{
                    $f="Long Text";
                }
                $customMessages = [
                    'long_text.required' => 'The '.$f.' field is required.'
                ];
            }
        }

        $validator = \Validator::make($request->all(),$requiredArray);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseIntakeFormFieldsData=CaseIntakeFormFieldsData::where("intake_form_id",$request->form_id)->first();
            if(!empty($CaseIntakeFormFieldsData)){
                $CaseIntakeFormFieldsData = CaseIntakeFormFieldsData::find($CaseIntakeFormFieldsData['id']);
            }else{
                $CaseIntakeFormFieldsData = new CaseIntakeFormFieldsData;
            }
            $CaseIntakeFormFieldsData->intake_form_id=$request->form_id;
            $CaseIntakeFormFieldsData->form_value=json_encode($request->all());
            if(isset(Auth::user()->id)){
                $CaseIntakeFormFieldsData->created_by=Auth::user()->id; 
            }

            $CaseIntakeFormFieldsData->case_intake_form_token=$request->case_intake_form_token;
            $CaseIntakeFormFieldsData->save();

            if($request->current_submit=="saveform"){
                CaseIntakeForm::where('intake_form_id',$request->form_id)->update(['is_filled'=>'yes','status'=>'2','submited_at'=>date('Y-m-d h:i:s')]);
                return response()->json(['errors'=>'','process'=>'done']);
            }
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   
    
    public function collectContactUSFormData(Request $request)
    {
        // $firmData=IntakeForm::where("form_unique_id",$request->form_unique_id)->first();
        // $intakeForm=IntakeForm::where("form_unique_id",$request->form_unique_id)->first();
       
        // print_r($request->all());exit;
        $post = [
            'secret' => '6LfC0JQaAAAAABP1teNxor8FJ4CDTcNsvQgzTPEl',
            'response' => $_REQUEST['g-recaptcha-response'],
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);
        $serverCode=json_decode($server_output,true);
    
        if($serverCode['success']=="1"){
            $firmData=IntakeForm::where("form_unique_id",$request->form_unique_id)->first();
            $intakeForm=IntakeFormFields::where("intake_form_id",$request->form_id)->get();
            $requiredArray=$customMessages=[];
            foreach($intakeForm as $k=>$v){
                //This valdiation for contact fields only.
                if($v->form_field=="name" && $v->is_required=="yes"){
                    $requiredArray=array("first_name"=>"required|min:1","last_name"=>"required");
                }
                if($v->form_field=="email" && $v->is_required=="yes"){
                    $requiredArray=array("email"=>'required|email|unique:users,email,NULL,id,firm_name,'.$firmData['firm_name']);
                }else{
                    $requiredArray=array("email"=>'email|unique:users,email,NULL,id,firm_name,'.$firmData['firm_name']);

                }
                if($v->form_field=="address" && $v->is_required=="yes"){
                    $requiredArray=array("email"=>"required");
                }
                if($v->form_field=="home_phone" && $v->is_required=="yes"){
                    $requiredArray=array("home_phone"=>"required|numeric");
                }
                if($v->form_field=="cell_phone" && $v->is_required=="yes"){
                    $requiredArray=array("cell_phone"=>"required|numeric");
                }
                if($v->form_field=="work_phone" && $v->is_required=="yes"){
                    $requiredArray=array("work_phone"=>"required|numeric");
                }
                if($v->form_field=="birthday" && $v->is_required=="yes"){
                    $requiredArray=array("birthday"=>"required");
                }
                if($v->form_field=="driver_license" && $v->is_required=="yes"){
                    $requiredArray=array("driver_license_number"=>"required","driver_license_state"=>"required");
                }
                
                if($v->form_field=="long_text" && $v->is_required=="yes"){
                    $requiredArray=array("long_text"=>"required");
                    if($v->client_friendly_lable!=NULL){
                        $f=$v->client_friendly_lable;
                    }else{
                        $f="Long Text";
                    }
                    $customMessages = [
                        'long_text.required' => 'The '.$f.' field is required.'
                    ];
                }
                if($v->form_field=="short_text" && $v->is_required=="yes"){
                    $requiredArray=array("short_text"=>"required");
                    if($v->client_friendly_lable!=NULL){
                        $f=$v->client_friendly_lable;
                    }else{
                        $f="Short Text";
                    }
                    $customMessages = [
                        'long_text.required' => 'The '.$f.' field is required.'
                    ];
                }

              
            }
            
            $validator = \Validator::make($request->all(),$requiredArray,$customMessages);
            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()->all()]);
            }else{
                $intakeForm=IntakeForm::where("form_unique_id",$request->form_unique_id)->first();

                // $CaseIntakeFormFieldsData=CaseIntakeFormFieldsData::where("intake_form_id",$request->form_id)->first();
                // if(!empty($CaseIntakeFormFieldsData)){
                //     $CaseIntakeFormFieldsData = CaseIntakeFormFieldsData::find($CaseIntakeFormFieldsData['id']);
                // }else{
                //     $CaseIntakeFormFieldsData = new CaseIntakeFormFieldsData;
                // }
                $CaseIntakeFormFieldsData = new CaseIntakeFormFieldsData;
                $CaseIntakeFormFieldsData->intake_form_id=$request->form_id;
                $CaseIntakeFormFieldsData->firm_id=$intakeForm['firm_name'];
                $CaseIntakeFormFieldsData->form_value=json_encode($request->all());
                $CaseIntakeFormFieldsData->form_type="contact";
                if(isset(Auth::user()->id)){
                    $CaseIntakeFormFieldsData->created_by=Auth::user()->id; 
                }
               
                $CaseIntakeFormFieldsData->save();

                $OnlineLeadSubmit = new OnlineLeadSubmit;
                $OnlineLeadSubmit->intake_form_id=$intakeForm['id'];
                $OnlineLeadSubmit->firm_id=$intakeForm['firm_name'];
                $OnlineLeadSubmit->first_name=$request->first_name;
                $OnlineLeadSubmit->last_name=$request->last_name;
                $OnlineLeadSubmit->middle_name=$request->middle_name;
                $OnlineLeadSubmit->email=$request->email;
                $CommonController= new CommonController();
                $OnlineLeadSubmit->unique_token=$CommonController->getUniqueToken();
                $OnlineLeadSubmit->case_intake_form_fields_data_id=$CaseIntakeFormFieldsData->id;
                $OnlineLeadSubmit->created_by=0; 
                $OnlineLeadSubmit->save();
                
                $CaseIntakeFormFieldsData->online_lead_id=$OnlineLeadSubmit->id; 
                $CaseIntakeFormFieldsData->save();

                $getTemplateData = EmailTemplate::find(18);
                $firmData=Firm::find($intakeForm['firm_name']);
                $email=$request->email;
                $token=url('form', $intakeForm->form_unique_id);
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{email}', $email,$mail_body);
                $mail_body = str_replace('{firmname}', $firmData->firm_name,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', REGARDS, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        

                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => DO_NOT_REPLAY_FROM_EMAIL_TITLE,
                    "subject" => $getTemplateData->subject,
                    "to" => $email,
                    "full_name" => "",
                    "mail_body" => $mail_body
                ];
                if($email!=""){
                    $sendEmail = $this->sendMail($user);
                }

                //Send email to lawyer
                $getTemplateData = EmailTemplate::find(19);
                $firmData=Firm::find($intakeForm['firm_name']);
                $firmOWnertData=User::find($firmData['parent_user_id']);
                
                $email=$request->email;
                $receiver=$firmOWnertData['first_name'].' '.$firmOWnertData['last_name'];
                $token=BASE_URL.'leads/onlineleads/';
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{email}', $email,$mail_body);
                $mail_body = str_replace('{receiver}', $receiver,$mail_body);
                $mail_body = str_replace('{url}', $token,$mail_body);
                $mail_body = str_replace('{firmname}', $firmData->firm_name,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', REGARDS, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        

                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => FROM_EMAIL_TITLE,
                    "subject" => $getTemplateData->subject,
                    "to" => $firmOWnertData['email'],
                    "full_name" => $receiver,
                    "mail_body" => $mail_body
                ];
                if($email!=""){
                    $sendEmail = $this->sendMail($user);
                }
                session(['form_success' => 'Success! Thank you for submitting your information.']);

                return response()->json(['errors'=>'','success'=>'Success! Thank you for submitting your information.']);
                exit;   
            }
        }else{
            return response()->json(['errors'=>['Please validate the captcha first.']]);
            exit;  
        }
    }   
    public function downloadIntakeFormOLD(Request $request)
    {
        $id=$request->id;
        $caseIntakeForm=CaseIntakeForm::where("id",$id)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$caseIntakeForm['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm->id)->first();
 
        $headerHtml="asdas";
        return view('lead.details.case_detail.intakeFormPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));

        $pdf = PDF::loadView('lead.details.case_detail.intakeFormPDF',array('caseIntakeForm' => $caseIntakeForm,'firmData'=>$firmData,'intakeForm'=>$intakeForm,'intakeFormFields'=>$intakeFormFields,'country'=>$country,'alreadyFilldedData'=>$alreadyFilldedData))->setOptions(['footer-center'=> 'Page [page]']);
        // $pdf->setOptions(['defaultFont' => 'sans-serif']);
        // $pdf->setOptions(['isPhpEnabled' => true]);//->setOptions(['header-html'=> $headerHtml]);
        return $pdf->stream();
    }

    public function inlineViewIntakeForm($id)
    {
        $caseIntakeForm=CaseIntakeForm::where("unique_token",$id)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$caseIntakeForm['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm->id)->first();

        $search=array(' ',':');
        $filename=str_replace($search,"_",$intakeForm['form_name'])."_".time().'.pdf';
        $PDFData=view('lead.details.case_detail.intakeFormPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
        $pdf->saveAs(public_path("download_intakeform/pdf/".$filename));
        $path = public_path("download_intakeform/pdf/".$filename);
        // return response()->download($path);
        // exit;
        $pdf->addPage($PDFData);
        if (!$pdf->send()) {
            $error = $pdf->getError();
        }

        // return response()->json([ 'success' => true, "url"=>url('public/download_intakeform/pdf/'.$filename),"file_name"=>$filename], 200);
        // exit;
    }

    public function inlineViewOnlineLeadForm($id)
    {

        $OnlineLeadSubmit=OnlineLeadSubmit::where("unique_token",$id)->first();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("id",$OnlineLeadSubmit['case_intake_form_fields_data_id'])->first();
        $intakeForm=IntakeForm::where("id",$alreadyFilldedData['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$alreadyFilldedData['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $country = Countries::get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $filename="online_lead_".time().'.pdf';
        $PDFData=view('lead.onlineLeadPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));


        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        // $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
        $pdf->saveAs(public_path("download_intakeform/pdf/".$filename));
        $path = public_path("download_intakeform/pdf/".$filename);
        // return response()->download($path);
        // exit;
        $pdf->addPage($PDFData);
        if (!$pdf->send()) {
            $error = $pdf->getError();
        }

        // return response()->json([ 'success' => true, "url"=>url('public/download_intakeform/pdf/'.$filename),"file_name"=>$filename], 200);
        // exit;
    }
    public function printLead(Request $request)
    {
        if($request->section=="leads/statuses")
        {
            $LeadStatus=LeadStatus::where("firm_id",Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
            $allLEadByGroup=[];
            $extraInfo=[];
            $ld=$pa=$ol=$at='';
            foreach($LeadStatus as $k=>$v){
                $allLeads = User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id');
                $allLeads = $allLeads->select("users.*","lead_additional_info.*");
                $allLeads = $allLeads->where("users.user_type","5");
                $allLeads = $allLeads->where("users.user_level","5");
                $allLeads = $allLeads->where("lead_additional_info.is_converted","no");
                $allLeads = $allLeads->where("users.firm_name",Auth::User()->firm_name);
                $allLeads = $allLeads->where("lead_additional_info.lead_status",$v->id);
                $allLeads = $allLeads->where("lead_additional_info.deleted_at",NULL);
                $allLeads = $allLeads->where("lead_additional_info.do_not_hire_reason",NULL);
                
                if(isset($request->ld) && $request->ld!=''){
                    $allLeads = $allLeads->where("lead_additional_info.user_id",$request->ld);
                    $ld=$request->ld;
                }
                if(isset($request->pa) && $request->pa!=''){
                    $allLeads = $allLeads->where("lead_additional_info.practice_area",$request->pa);
                    $pa=$request->pa;
                }
                if(isset($request->ol) && $request->ol!=''){
                    $allLeads = $allLeads->where("lead_additional_info.office",$request->ol);
                    $ol=$request->ol;
                }
                if(isset($request->at) && $request->at!=''){
                    if($request->at=='unassigned'){
                        $allLeads = $allLeads->where("lead_additional_info.assigned_to",NULL);
                    }elseif($request->at=='me'){
                        $allLeads = $allLeads->where("lead_additional_info.assigned_to",Auth::User()->id);
                    } 
                    $at=$request->at;
                }
                $allLeads = $allLeads->orderBy("lead_additional_info.sort_order",'ASC');
                $allLeads = $allLeads->get();
               
                $allLEadByGroup[$v->id]=$allLeads;
                $extraInfo[$v->id]['sum']=$allLeads->sum('potential_case_value'); 
                $extraInfo[$v->id]['totalLeads']=$allLeads->count(); 
            }

            $allLeadsDropdown = LeadAdditionalInfo::leftJoin('users','lead_additional_info.user_id','=','users.id')
            ->select("users.*")
            ->where("users.user_type","5")
            ->where("users.user_level","5")
            ->where("users.firm_name",Auth::User()->firm_name)
            ->groupBy("lead_additional_info.user_id")
            ->get();

          
            $allPracticeAreaDropdown = LeadAdditionalInfo::leftJoin('case_practice_area','lead_additional_info.practice_area','=','case_practice_area.id')
            ->select("case_practice_area.*","lead_additional_info.practice_area")
            ->groupBy("lead_additional_info.practice_area")
            ->get();

            $filename="status_lead_".time().'.pdf';
            $PDFData=view('lead.printStatusLeadPdf',compact('allLEadByGroup','extraInfo','LeadStatus','allLeadsDropdown','allPracticeAreaDropdown','ld','pa','ol','at'));

        }else if($request->section=="leads/onlineleads")
        {
            $OnlineLeadSubmit=OnlineLeadSubmit::where("firm_id",Auth::User()->firm_name)->get();
            $filename="online_lead_".time().'.pdf';
            $PDFData=view('lead.printOnlineLeadPdf',compact('OnlineLeadSubmit'));
        }   
        
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = WKHTMLTOPDF_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        $pdf->saveAs(public_path("download_intakeform/pdf/".$filename));
        $path = public_path("download_intakeform/pdf/".$filename);
        $pdf->addPage($PDFData);
        $pdf->addPage($PDFData);
        // if (!$pdf->send()) {
        //     $error = $pdf->getError();
        // }

       return response()->json([ 'success' => true, "url"=>url('public/download_intakeform/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
    }
    public function downloadIntakeForm(Request $request)
    {
        $id=$request->id;
        $caseIntakeForm=CaseIntakeForm::where("id",$id)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$caseIntakeForm['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm->id)->first();

        $search=array(' ',':');
        $filename=str_replace($search,"_",$intakeForm['form_name'])."_".time().'.pdf';
        $PDFData=view('lead.details.case_detail.intakeFormPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
        $pdf->saveAs(public_path("download_intakeform/pdf/".$filename));
        $path = public_path("download_intakeform/pdf/".$filename);
        // return response()->download($path);
        // exit;

        return response()->json([ 'success' => true, "url"=>url('public/download_intakeform/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
    }
    /*********************** INTAKE FORMS ***************************** */


    /*********************** INVOICE ***************************** */

    public function loadInvoices()
    {   
        $requestData= $_REQUEST;
        $PotentialCaseInvoice = PotentialCaseInvoice::select("potential_case_invoice.*");      
        $PotentialCaseInvoice = $PotentialCaseInvoice->where("potential_case_invoice.lead_id",$requestData['user_id']);        
        $totalData=$PotentialCaseInvoice->count();
        $totalFiltered = $totalData; 
     
        $PotentialCaseInvoice = $PotentialCaseInvoice->offset($requestData['start'])->limit($requestData['length']);
        $PotentialCaseInvoice = $PotentialCaseInvoice->orderBy('created_at','DESC');
        $PotentialCaseInvoice = $PotentialCaseInvoice->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $PotentialCaseInvoice 
        );
        echo json_encode($json_data);  
    }

    public function addNewInvoices(Request $request)
    {
        $lead_id=$request->user_id;
        $userData=User::find($request->user_id);
        $maxNumber=PotentialCaseInvoice::where("lead_id",$lead_id)->max('invoice_number');
        return view('lead.details.case_detail.invoices.addInvoice',compact('userData','lead_id','maxNumber'));

    }

    public function saveInvoices(Request $request)
    {
        // print_r($request->all());
        // // return response()->json(['errors'=>'']);
        //     exit;  
        $validator = \Validator::make($request->all(), [
            'invoice_date' => 'required',
            'invoice_number' => 'required|min:1|max:128|unique:potential_case_invoice,invoice_number,NULL,id,lead_id,'.$request->lead_id,
            'due_date' => 'required',
            'total_amount' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $PotentialCaseInvoice = new PotentialCaseInvoice;
            $PotentialCaseInvoice->lead_id=$request->lead_id; 
            $PotentialCaseInvoice->invoice_unique_id=md5(uniqid(rand(), true).time()); 
            $PotentialCaseInvoice->invoice_number=$request->invoice_number;
            $PotentialCaseInvoice->invoice_amount=str_replace(",","",$request->total_amount);
            $PotentialCaseInvoice->amount_paid="0.00";
            $PotentialCaseInvoice->due_date=date('Y-m-d',strtotime($request->due_date));
            $PotentialCaseInvoice->invoice_date=date('Y-m-d',strtotime($request->invoice_date));
            $PotentialCaseInvoice->description=$request->description;
            $PotentialCaseInvoice->status="2";
            $PotentialCaseInvoice->created_by=Auth::user()->id; 
            $PotentialCaseInvoice->save();
            // session(['popup_success' => 'Invoice successfully created.']);
            return response()->json(['errors'=>'','invoice_id'=>$PotentialCaseInvoice->id]);
            exit;   
        }
    }   

    public function editInvoice(Request $request)
    {
        $invoice_id=$request->id;
        $PotentialCaseInvoice=PotentialCaseInvoice::find($invoice_id);
        $userData=User::find($PotentialCaseInvoice->lead_id);
        return view('lead.details.case_detail.invoices.editInvoice',compact('userData','PotentialCaseInvoice','invoice_id'));

    }
    public function updateInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'invoice_date' => 'required',
            'invoice_number' => 'required|min:1|max:128|unique:potential_case_invoice,invoice_number,'.$request->invoice_id.',id,lead_id,'.$request->lead_id,
            'due_date' => 'required',
            'total_amount' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $PotentialCaseInvoice = PotentialCaseInvoice::find($request->invoice_id);
            $PotentialCaseInvoice->invoice_number=$request->invoice_number;
            $PotentialCaseInvoice->invoice_amount=str_replace(",","",$request->total_amount);
            // $PotentialCaseInvoice->amount_paid=str_replace(",","",$request->amount_paid);
            $PotentialCaseInvoice->due_date=date('Y-m-d',strtotime($request->due_date));
            $PotentialCaseInvoice->invoice_date=date('Y-m-d',strtotime($request->invoice_date));
            $PotentialCaseInvoice->description=$request->description;
            $PotentialCaseInvoice->updated_by=Auth::user()->id; 
            $PotentialCaseInvoice->save();
            session(['popup_success' => 'Invoice successfully updated.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   
    public function deleteInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'invoice_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            PotentialCaseInvoice::where('id',$request->invoice_id)->delete();
            return response()->json(['errors'=>'','id'=>$request->invoice_id]);
            exit;
        }
        
    }
    public function openSendInvoicePopup(Request $request)
    {
        $invoice_id=$request->invoice_id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $userData=User::find($request->user_id);
        return view('lead.details.case_detail.invoices.sendInvoice',compact('userData','firmData','invoice_id'));
    }
    public function sendInvoice(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email_address' => 'required|email'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $invoice_id=$request->invoice_id;
            $PotentialCaseInvoice=PotentialCaseInvoice::where("id",$invoice_id)->first();
            $firmData=Firm::find(Auth::User()->firm_name);
            if($request->sent_by=="email"){
                $getTemplateData = EmailTemplate::find(8);
                $token=url('bills/invoice', $PotentialCaseInvoice->invoice_unique_id);
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{message}', $request->email_message, $mail_body);
                $mail_body = str_replace('{token}', $token,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
                $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        
    
                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => FROM_EMAIL_TITLE,
                    "subject" => $request->email_subject,
                    "to" => $request->email_address,
                    "full_name" => "",
                    "mail_body" => $mail_body
                ];
                $sendEmail = $this->sendMail($user);
                $saveData=PotentialCaseInvoice::find($invoice_id);
                $saveData->status="1";
                $saveData->save();
                session(['popup_success' => 'Email sent successfully.']);
            }else{
            }
            return response()->json(['errors'=>'']);
            exit;   
        }
    }
    public function viewInvoice(Request $request)
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

    public function viewInvoiceForPdf(Request $request)
    {
        $invoice_id=$request->id;
        $PotentialCaseInvoice=PotentialCaseInvoice::where("invoice_unique_id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$PotentialCaseInvoice['lead_id'])->first();
       
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        
        $PotentialCaseInvoicePayment=PotentialCaseInvoicePayment::select("potential_case_invoice_payment.*","users.id","users.first_name","users.last_name","users.user_title")->leftJoin('users','users.id',"=","potential_case_invoice_payment.created_by")->where("invoice_id",$PotentialCaseInvoice['id'])->get();

        if(empty($PotentialCaseInvoice)){
            return view('pages.404');
        }else{
            return view('lead.details.case_detail.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','PotentialCaseInvoice','firmAddress','PotentialCaseInvoicePayment'));
        }
    }
    public function downloadInvoiceForm(Request $request)
    {
        $invoice_id=$request->id;
        $PotentialCaseInvoice=PotentialCaseInvoice::where("id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$PotentialCaseInvoice['lead_id'])->first();
       
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        
        $firmData=Firm::find($userData['firm_name']);
        $PotentialCaseInvoicePayment=PotentialCaseInvoicePayment::select("potential_case_invoice_payment.*","users.id","users.first_name","users.last_name","users.user_title")->leftJoin('users','users.id',"=","potential_case_invoice_payment.created_by")->where("invoice_id",$PotentialCaseInvoice['id'])->get();
        $filename="Invoice_".$PotentialCaseInvoice['id'].'.pdf';
        $PDFData=view('lead.details.case_detail.invoices.viewInvoicePdf',compact('userData','firmData','invoice_id','PotentialCaseInvoice','firmAddress','PotentialCaseInvoicePayment'));
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        // $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
        $pdf->saveAs(public_path("download/pdf/".$filename));
        $path = public_path("download/pdf/".$filename);
        // return response()->download($path);
        // exit;
        return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
    }

    public function payInvoice(Request $request)
    {
        $invoice_id=$request->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $userData=User::find($request->user_id);

        $PotentialCaseInvoice=PotentialCaseInvoice::where("id",$invoice_id)->first();
        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$PotentialCaseInvoice['lead_id'])->first();

       
        return view('lead.details.case_detail.invoices.payInvoice',compact('userData','firmData','invoice_id','PotentialCaseInvoice'));
    }
    public function savePayment(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);
        $PotentialCaseInvoice=PotentialCaseInvoice::find($request->invoice_id);
        $paid=$PotentialCaseInvoice['amount_paid'];
        $invoice=$PotentialCaseInvoice['invoice_amount'];
        $finalAmt=$invoice-$paid;

        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required',
            'amount' => 'required|numeric|max:'.$finalAmt,
            'deposit_into' => 'required',
            'invoice_id' => 'required|numeric'
        ],[
            'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $invoice_id=$request->invoice_id;
            $PotentialCaseInvoicePayment=new PotentialCaseInvoicePayment;
            $PotentialCaseInvoicePayment->invoice_id=$request->invoice_id;
            $PotentialCaseInvoicePayment->payment_method=$request->payment_method;
            $PotentialCaseInvoicePayment->amount_paid=$request->amount;
            $PotentialCaseInvoicePayment->payment_date=date('Y-m-d',strtotime($request->payment_date));
            $PotentialCaseInvoicePayment->deposit_into=$request->deposit_into;
            $PotentialCaseInvoicePayment->notes=$request->notes;
            $PotentialCaseInvoicePayment->created_by=Auth::user()->id; 
            $PotentialCaseInvoicePayment->save();

            $PotentialCaseInvoice=PotentialCaseInvoice::find($invoice_id);
            $PotentialCaseInvoice->amount_paid=PotentialCaseInvoicePayment::where("invoice_id",$invoice_id)->get()->sum("amount_paid");
            $PotentialCaseInvoice->save();
            $firmData=Firm::find(Auth::User()->firm_name);
            $msg="Thank you. Your payment of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }
    /*********************** INVOICE ***************************** */


    
    /*********************** COMMUNICATION TAB ***************************** */
    public function addCall(Request $request)
    {
        
        //Get client and lead list
        $ClientAndLead = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',5)->orWhere('user_level',2)->where("parent_user",Auth::user()->id)->get();

        //Get potential case list
        $potentialCase = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("users.*","lead_additional_info.*")->where("users.user_type","5")->where("users.user_level","5")->where("lead_additional_info.is_converted","no")->where("users.firm_name",Auth::User()->firm_name)->where("lead_additional_info.user_status","1")->get();

        //Get Actual case list
        $CaseMasterData = CaseMaster::select("*");
         //If Parent user logged in then show all child case to parent
         if(Auth::user()->parent_user==0){
            $getChildUsers =$this->getParentAndChildUserIds();
            $CaseMasterData = $CaseMasterData->whereIn("case_master.created_by",$getChildUsers);
        }else{
            $CaseMasterData = $CaseMasterData->where("case_master.id",Auth::user()->id);
        }
        $CaseMasterData=$CaseMasterData->where('is_entry_done',"1")->get();
        
         $getAllFirmUser=$this->getAllFirmUser();
         $case_id='';
         if(isset($request->case_id)){
            $case_id=$request->case_id;
            return view('case.view.timebilling.addCall',compact('ClientAndLead','potentialCase','CaseMasterData','getAllFirmUser','case_id'));
         }else{
            return view('lead.details.communication.addCall',compact('ClientAndLead','potentialCase','CaseMasterData','getAllFirmUser'));
         }
    }
    public function getMobileNumber(Request $request)
    {
        $mobileNumber = User::find($request->user_id);
        return response()->json(['errors'=>'','mobile_number'=>($mobileNumber['mobile_number'])??'']);
        exit;  
    }

    public function saveCall(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'call_date' => 'required',
            'caller_name' => 'required',
            'phone_number' => 'required',
            'call_for' => 'required',
            'message' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CommonController= new CommonController();
            $startDateTime = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->call_date.' '.$request->call_time)),Auth::User()->user_timezone)));

            $callSave = new Calls;
            $callSave->call_date=date("Y-m-d",strtotime($request->call_date)) ; 
            $callSave->call_time=date("H:i:s",strtotime($startDateTime)); 
            $ClientAndLeadExist = User::select("first_name","last_name","id","user_level","user_title")->where("id",$request->caller_name)->first();
            if(empty($ClientAndLeadExist)){
                $callSave->caller_name=NULL;
                $callSave->caller_name_text=$request->caller_name; 
            }else{
                $callSave->caller_name=$request->caller_name; 
            }
            $callSave->phone_number=$request->phone_number; 
            $callSave->case_id=$request->case; 
            $callSave->call_for=$request->call_for; 
            $callSave->message=$request->message; 
            if($request->call_resolved=="on"){
                $callSave->call_resolved="yes"; 
            }else{
                $callSave->call_resolved="no"; 
            }

            if($request->outgoing=="yes"){
                $callSave->call_type="1"; 
            }

            $callSave->call_duration=$request->timer_value;
            $callSave->created_by=Auth::user()->id; 
            $callSave->save();
            session(['popup_success' => 'Your call has been recorded.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   
    public function loadCalls()
    {   
        $requestData= $_REQUEST;
        $Calls = Calls::select("calls.*",DB::raw('CONCAT(u1.first_name, " ",u1.last_name) as created_name'),DB::raw('CONCAT(u2.first_name, " ",u2.last_name) as caller_full_name'),DB::raw('CONCAT(u3.first_name, " ",u3.last_name) as call_for_name'));
        $Calls = $Calls->leftJoin('users as u1','calls.created_by','=','u1.id');        
        $Calls = $Calls->leftJoin('users as u2','calls.caller_name','=','u2.id');        
        $Calls = $Calls->leftJoin('users as u3','calls.call_for','=','u3.id');   

      
        $totalData=$Calls->count();
        $totalFiltered = $totalData; 
       
        if(isset($requestData['callfor']) && $requestData['callfor']!=''){
            $Calls = $Calls->where("calls.call_for",$requestData['callfor']);
        }
        if(isset($requestData['status']) && $requestData['status']!=''){
            $Calls = $Calls->where("calls.call_resolved",$requestData['status']);
        }
        if(isset($requestData['type']) && $requestData['type']!=''){
            $Calls = $Calls->where("calls.call_type",$requestData['type']);
        }
        if(isset($requestData['case_id']) && $requestData['case_id']!=''){
            $Calls = $Calls->where("calls.case_id",$requestData['case_id']);
        }
        
        $Calls = $Calls->offset($requestData['start'])->limit($requestData['length']);
        $Calls = $Calls->orderBy('created_at','DESC');
        $Calls = $Calls->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $Calls 
        );
        echo json_encode($json_data);  
    }

    public function deleteCallLog(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'call_id' => 'required|min:1'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            Calls::where('id',$request->call_id)->delete();
            session(['popup_success' => 'Call log has been removed.']);
            return response()->json(['errors'=>'','id'=>$request->call_id]);
            exit;
        }
        
    }

    public function editCall(Request $request)
    {
        $call_id=$request->id;
        //Get client and lead list
        $ClientAndLead = User::select("first_name","last_name","id","user_level","user_title")->where('user_level',5)->orWhere('user_level',2)->where("parent_user",Auth::user()->id)->get();

        //Get potential case list
        $potentialCase = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("users.*","lead_additional_info.*")->where("users.user_type","5")->where("users.user_level","5")->where("lead_additional_info.is_converted","no")->where("users.firm_name",Auth::User()->firm_name)->where("lead_additional_info.user_status","1")->get();

        //Get Actual case list
        $CaseMasterData = CaseMaster::select("*");
         //If Parent user logged in then show all child case to parent
         if(Auth::user()->parent_user==0){
            $getChildUsers =$this->getParentAndChildUserIds();
            $CaseMasterData = $CaseMasterData->whereIn("case_master.created_by",$getChildUsers);
        }else{
            $CaseMasterData = $CaseMasterData->where("case_master.id",Auth::user()->id);
        }
        $CaseMasterData=$CaseMasterData->where('is_entry_done',"1")->get();
        
        $getAllFirmUser=$this->getAllFirmUser();
        $Calls=Calls::find($call_id);
        return view('lead.details.communication.editCall',compact('ClientAndLead','potentialCase','CaseMasterData','getAllFirmUser','Calls'));
    }

    public function updateCall(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'call_date' => 'required',
            'caller_name' => 'required',
            'phone_number' => 'required',
            'call_for' => 'required',
            'message' => 'required',
            'call_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CommonController= new CommonController();
            $startDateTime = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->call_date.' '.$request->call_time)),Auth::User()->user_timezone)));

            $callSave = Calls::find($request->call_id);
            $callSave->call_date=date("Y-m-d",strtotime($request->call_date)) ; 
            $callSave->call_time=date("H:i:s",strtotime($startDateTime)); 
            $ClientAndLeadExist = User::select("first_name","last_name","id","user_level","user_title")->where("id",$request->caller_name)->first();
            if(empty($ClientAndLeadExist)){
                $callSave->caller_name=NULL;
                $callSave->caller_name_text=$request->caller_name; 
            }else{
                $callSave->caller_name=$request->caller_name; 
            }
            $callSave->phone_number=$request->phone_number; 
            $callSave->case_id=$request->case; 
            $callSave->call_for=$request->call_for; 
            $callSave->message=$request->message; 
            if($request->call_resolved=="on"){
                $callSave->call_resolved="yes"; 
            }else{
                $callSave->call_resolved="no"; 
            }
            $callSave->updated_by=Auth::user()->id; 
            $callSave->call_type=$request->call_type; 
            $callSave->save();
            session(['popup_success' => 'Your call has been recorded.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   

    public function changeCallType(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $callSave = Calls::find($request->id);
            if($callSave['call_resolved']=="yes"){
                $callSave->call_resolved="no"; 
            }else{
                $callSave->call_resolved="yes"; 
            } 
            $callSave->save();
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   

    public function loadAddTaskPopupFromLog(Request $request)
    {
        $lead_id=$request->user_id;
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        $country = Countries::get();
        $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
        $currentDateTime=$this->getCurrentDateAndTime();
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->get();
         
         $case_id='';
         if(isset($request->case_id)){
             $case_id=$request->case_id;
         }

         return view('lead.tasks.loadAddTaskPopup',compact('lead_id','caseLeadList','CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id')); 
         
         
    }
    /***********************COMMUNICATION TAB******************************/



}
  