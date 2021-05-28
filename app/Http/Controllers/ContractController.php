<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\ContractUserCase,App\CaseMaster,App\ContractUserPermission,App\ContractAccessPermission,App\Firm;
use App\DeactivatedUser,App\ClientGroup,App\UsersAdditionalInfo,App\CaseClientSelection,App\CaseStaff,App\TempUserSelection,App\UserRole;
use App\CasePracticeArea,App\CaseStage;
use Illuminate\Support\Str;
class ContractController extends BaseController
{
    public function __construct()
    {
        // $this->middleware("auth");
    }
    public function index()
    {
        $user = User::latest()->get();
        $country = Countries::get();
        return view('contract.index',compact('user','country'));
    }

    public function loadUser()
    {   
        $columns = array('id','first_name','user_title', 'email', 'email', 'user_title','user_status','last_login','created_at');
        $requestData= $_REQUEST;
        
        $user = User::select('*',DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as name'));
        $user = $user->where("firm_name",Auth::user()->firm_name); //Logged in user not visible in grid
        $user = $user->whereIn("user_level",['1','3']); //Show firm staff only
        $user = $user->doesntHave("deactivateUserDetail"); // Check user is deactivated or not
        $totalData=$user->count();
        $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.
        if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $user = $user->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('email', 'like', "%".$requestData['search']['value']."%" );
                });
            });
            $totalFiltered = $user->count(); 
        }
       
        $user = $user->offset($requestData['start'])->limit($requestData['length']);
        $user = $user->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $user = $user->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $user   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }

    public function loadStep1(Request $request)
    {
        $case_id=($request->case_id)??NULL;
        $country = Countries::get();
        return view('contract.loadStep1',compact('country','case_id'));
    }
    public function saveStep1(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,NULL,id,deleted_at,NULL',
            'user_type' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user = new User;
            $user->first_name=$request->first_name;
            $user->last_name=$request->last_name;
            $user->email=$request->email;
            if(isset($request->middle_name)){ $user->middle_name=trim($request->middle_name); }
            if(isset($request->street)) { $user->street=trim($request->street); }
            if(isset($request->apt_unit)) { $user->apt_unit=trim($request->apt_unit); }
            if(isset($request->city)) { $user->city=trim($request->city); }
            if(isset($request->state)) { $user->state=trim($request->state); }
            if(isset($request->postal_code)) { $user->postal_code=trim($request->postal_code); }
            if(isset($request->country)) { $user->country=trim($request->country); }
            if(isset($request->home_phone)) { $user->home_phone=trim($request->home_phone); }
            if(isset($request->work_phone)) { $user->work_phone=trim($request->work_phone); }
            if(isset($request->cell_phone)) { $user->mobile_number=trim($request->cell_phone); }
            if($request->user_title==''){
                if($request->user_type=="1") { 
                    $user->user_title='Attorney';
                 }else if($request->user_type=="2") 
                 { 
                    $user->user_title='Paralegal'; 
                }else{
                    $user->user_title='Staff';
                }
                
            }else{
                $user->user_title=$request->user_title;
            }
            if(isset($request->default_rate)) { $user->default_rate=trim(str_replace(",","",$request->default_rate)); }
            if(isset($request->user_type)) { $user->user_type=trim($request->user_type); }
            $user->firm_name=Auth::User()->firm_name;
            $user->token  = Str::random(40);
            $user->parent_user =Auth::User()->id;
            $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
            $user->created_by =Auth::User()->id;
            // print_r($user);exit;
            $user->save();

            //If case id is exist then only save in to staff table.
            if(isset($request->case_id) && $request->case_id!=''){
                $CaseStaff = new CaseStaff;
                $CaseStaff->case_id=$request->case_id; 
                $CaseStaff->user_id=$user->id; 
                $CaseStaff->created_by=Auth::user()->id; 
                $CaseStaff->lead_attorney=NULL;
                $CaseStaff->originating_attorney=NULL;
                $CaseStaff->rate_type="0";
                $CaseStaff->rate_amount="0.00";
                $CaseStaff->save();
            }
            
            $getTemplateData = EmailTemplate::find(6);
            $fullName=$request->first_name. ' ' .$request->last_name;
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
        //   $sendEmail = $this->sendMail($userEmail);
        //   if($sendEmail=="1"){
        //     $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
        //     $user->save();
        //   }
            return response()->json(['errors'=>'','user_id'=>$user->id]);
          exit;
        }
    }
    // Load step 2 when click from step 1
    public function loadStep2(Request $request)
    {
        $user = User::where("id",$request->user_id)->get();
        $country = Countries::get();
        $CaseMaster = CaseMaster::where("case_status","1")->get();
        
        return view('contract.loadStep2',compact('user','country','CaseMaster'));
    }
    // Save step 2 data to database.
    public function saveStep2(Request $request)
    {
        $user = User::find($request->user_id);
        $user->link_user_to=$request->link_to;
        if(isset($request->case_rate)) { $user->case_rate=$request->case_rate; }
        if($user->link_user_to=='3'){ 
            $ContractUserCase = new ContractUserCase;
            $ContractUserCase->case_id=$request->case_list;
            $ContractUserCase->user_id=$request->user_id;
            $ContractUserCase->save();
        }else if($user->link_user_to=='2'){
            ContractUserCase::where("user_id",$request->user_id)->delete();
            $CaseMaster = CaseMaster::where("case_status","1")->get();
            foreach($CaseMaster as $k=>$v){
                $ContractUserCase = new ContractUserCase;
                $ContractUserCase->case_id=$v->id;
                $ContractUserCase->user_id=$request->user_id;
                $ContractUserCase->save();
            }
        }
        if(isset($request->sharing_setting_1)) { $user->sharing_setting_1="1"; }else{ $user->sharing_setting_1="0"; }
        if(isset($request->sharing_setting_2)) { $user->sharing_setting_2="1"; }else{ $user->sharing_setting_2="0"; }
        if(isset($request->sharing_setting_3)) { $user->sharing_setting_3="1"; }else{ $user->sharing_setting_3="0"; }
        if($request->case_rate=="1") 
        { 
            $user->rate_amount=trim(str_replace(",","",$request->default_rate));
        }
        $user->save();
        return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
    }
    //Load step 3 when click next button in step 2
    public function loadStep3(Request $request)
    {
        $user = User::where("id",$request->user_id)->get();
        $country = Countries::get();
        $CaseMaster = CaseMaster::where("case_status","1")->get();
        return view('contract.loadStep3',compact('user','country','CaseMaster'));
    }
    //Save step 3 data to database.
    public function saveStep3(Request $request)
    {
        $userPermission = new ContractUserPermission;
        
        if(isset($request->user_id)) { $userPermission->user_id=$request->user_id; }
        if(isset($request->access_case)) { $userPermission->access_case=$request->access_case; }
        if(isset($request->add_new)) { $userPermission->add_new=$request->add_new; }
        if(isset($request->edit_permisssion)) { $userPermission->edit_permisssion=$request->edit_permisssion; }
        if(isset($request->delete_item)) { $userPermission->delete_item=$request->delete_item; }
        if(isset($request->import_export)) { $userPermission->import_export=$request->import_export; }
        if(isset($request->custome_fields)) { $userPermission->custome_fields=$request->custome_fields; }
        if(isset($request->manage_firm)) { $userPermission->manage_firm=$request->manage_firm; }
        $userPermission->save();
        return response()->json(['errors'=>'','user_id'=>$request->user_id]);
        exit;
    }
    public function loadStep4(Request $request)
    {
        $user = User::where("id",$request->user_id)->get();
        $country = Countries::get();
        $CaseMaster = CaseMaster::where("case_status","1")->get();
        return view('contract.loadStep4',compact('user','country','CaseMaster'));
    }

    public function saveStep4(Request $request)
    {
        $userPermission = new ContractAccessPermission;
        if(isset($request->user_id)) { $userPermission->user_id=$request->user_id; }
        if(isset($request->clientsPermission)) { $userPermission->clientsPermission=$request->clientsPermission; }
        if(isset($request->leadsPermission)) { $userPermission->leadsPermission=$request->leadsPermission; }
        if(isset($request->casesPermission)) { $userPermission->casesPermission=$request->casesPermission; }
        if(isset($request->eventsPermission)) { $userPermission->eventsPermission=$request->eventsPermission; }
        if(isset($request->documentsPermission)) { $userPermission->documentsPermission=$request->documentsPermission; }
        if(isset($request->commentingPermission)) { $userPermission->commentingPermission=$request->commentingPermission; }
        if(isset($request->textMessagingPermission)) { $userPermission->textMessagingPermission=$request->textMessagingPermission; }
        if(isset($request->messagesPermission)) { $userPermission->messagesPermission=$request->messagesPermission; }
        if(isset($request->billingPermission)) { $userPermission->billingPermission=$request->billingPermission; }
        if(isset($request->reportingPermission)) { $userPermission->reportingPermission=$request->reportingPermission; }

        if(isset($request->allMessagesFirmwide)) { $userPermission->allMessagesFirmwide="1"; }else { $userPermission->allMessagesFirmwide="0"; }
        if(isset($request->restrictBilling)) { $userPermission->restrictBilling="1"; }else { $userPermission->restrictBilling="0"; }
        if(isset($request->financialInsightsPermission)) { $userPermission->financialInsightsPermission="1"; }else { $userPermission->financialInsightsPermission="0"; }

        $userPermission->save();
        return response()->json(['errors'=>'','user_id'=>$request->user_id]);
        exit;
    }
    public function loadFinishStep(Request $request)
    {
        return view('contract.loadFinal');
    }
    public function loadColorPicker(Request $request)
    {        
        $user = User::where("id",$request->user_id)->get();
        return view('contract.loadColorPicker',compact('user'));
    }
    public function saveColorCode(Request $request)
    {
        $user = User::find($request->user_id);
        $user->default_color="#".$request->colorcode;
        $user->save();
        session(['popup_success' => 'Calender color code has been saved.']);
        return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
    }
    public function loadRateBox(Request $request)
    {        
        $user = User::where("id",$request->user_id)->get();
        return view('contract.loadRateBox',compact('user'));
    }
    public function saveRate(Request $request)
    {
        $user = User::find($request->user_id);
        $user->default_rate=(str_replace(",","",$request->default_rate))??"0.0";
        $user->save();
        session(['popup_success' => 'Default hourly rate has been saved.']);
        return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
    }

    public function loadPermissionModel(Request $request)
    {        
        $ContractUser = User::where("id",$request->user_id)->get();
        $ContractUserPermission = ContractUserPermission::where("user_id",$request->user_id)->get();
        $ContractAccessPermission = ContractAccessPermission::where("user_id",$request->user_id)->get();
        return view('contract.loadPermissionModel',compact('ContractUser','ContractUserPermission','ContractAccessPermission'));
    }
    public function savePermissionModel(Request $request)
    {  
        $userPermission = ContractAccessPermission::firstOrNew(array('user_id' => $request->user_id));
        if(isset($request->user_id)) { $userPermission->user_id=$request->user_id; }
        if(isset($request->clientsPermission)) { $userPermission->clientsPermission=$request->clientsPermission; }
        if(isset($request->leadsPermission)) { $userPermission->leadsPermission=$request->leadsPermission; }
        if(isset($request->casesPermission)) { $userPermission->casesPermission=$request->casesPermission; }
        if(isset($request->eventsPermission)) { $userPermission->eventsPermission=$request->eventsPermission; }
        if(isset($request->documentsPermission)) { $userPermission->documentsPermission=$request->documentsPermission; }
        if(isset($request->commentingPermission)) { $userPermission->commentingPermission=$request->commentingPermission; }
        if(isset($request->textMessagingPermission)) { $userPermission->textMessagingPermission=$request->textMessagingPermission; }
        if(isset($request->messagesPermission)) { $userPermission->messagesPermission=$request->messagesPermission; }
        if(isset($request->billingPermission)) { $userPermission->billingPermission=$request->billingPermission; }
        if(isset($request->reportingPermission)) { $userPermission->reportingPermission=$request->reportingPermission; }
        if(isset($request->allMessagesFirmwide)) { $userPermission->allMessagesFirmwide="1"; }else { $userPermission->allMessagesFirmwide="0"; }
        if(isset($request->restrictBilling)) { $userPermission->restrictBilling="1"; }else { $userPermission->restrictBilling="0"; }
        if(isset($request->financialInsightsPermission)) { $userPermission->financialInsightsPermission="1"; }else { $userPermission->financialInsightsPermission="0"; }
        $userPermission->updated_by =Auth::User()->id;

        $userPermission->save();
        $CurrentUserPermission = ContractUserPermission::firstOrNew(array('user_id' => $request->user_id));
        if(isset($request->user_id)) { $CurrentUserPermission->user_id=$request->user_id; }
        if(isset($request->access_case)) { $CurrentUserPermission->access_case=$request->access_case; }
        if(isset($request->add_new)) { $CurrentUserPermission->add_new=$request->add_new; }
        if(isset($request->edit_permisssion)) { $CurrentUserPermission->edit_permisssion=$request->edit_permisssion; }
        if(isset($request->delete_item)) { $CurrentUserPermission->delete_item=$request->delete_item; }
        if(isset($request->import_export)) { $CurrentUserPermission->import_export=$request->import_export; }
        if(isset($request->custome_fields)) { $CurrentUserPermission->custome_fields=$request->custome_fields; }
        if(isset($request->manage_firm)) { $CurrentUserPermission->manage_firm=$request->manage_firm; }

        $CurrentUserPermission->updated_by =Auth::User()->id;
        $CurrentUserPermission->save();
        session(['popup_success' => 'Permission rate has been saved.']);

        return response()->json(['errors'=>'','user_id'=>$request->user_id]);
        exit;
    }

    //Verify user once click on link shared by email.
    public function verifyUser($token)
    {
        $verifyUser = User::where('token', $token)->first();
        if(isset($verifyUser) ){
            if($verifyUser->user_status==1){
                return redirect('login')->with('warning', EMAIL_ALREADY_VERIFIED);
            }else{
                $status = EMAIL_VERIFIED;
                return redirect('setupuserpprofile/'.$token);
            }
        }else{
            return redirect('login')->with('warning', EMAIL_NOT_IDENTIFIED);
        }
    }

     //open set password popup when verify email
     public function setupuserpprofile($token)
     {
        $verifyUser = User::where('token', $token)->first();
        return view('contract.setupprofile',['verifyUser'=>$verifyUser]);

     }   
     
     //open set password popup when verify email
     public function setupusersave(Request $request)
     {
        $request->validate([
            'password' => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
            'user_timezone' => 'required',
        ]);

        $verifyUser =  User::where(["token" => $request->utoken])->first();
    
        if(isset($verifyUser) ){
            $user = $verifyUser;
            User::where('id',$user->id)->update(['password'=>Hash::make(trim($request->password)),
            'user_timezone'=>$request->user_timezone,
            'verified'=>"1",
            'user_status'=>"1"
            ]);

             //Sent welcome email to user.
             $getTemplateData = EmailTemplate::find(4);
             $fullName = $user->first_name . ' ' . $user->last_name;

             $mail_body = $getTemplateData->content;
             $mail_body = str_replace('{name}', $fullName, $mail_body);
             $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
             $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
             $mail_body = str_replace('{regards}', REGARDS, $mail_body);
             $mail_body = str_replace('{year}', date('Y'), $mail_body);     
             $user = [
                 "from" => FROM_EMAIL,
                 "from_title" => FROM_EMAIL_TITLE,
                 "subject" => $getTemplateData->subject,
                 "to" => $user->email,
                 "full_name" => $fullName,
                 "mail_body" => $mail_body
             ];
             $sendEmail = $this->sendMail($user);   
            if (Auth::attempt(['email' => $verifyUser->email, 'password' => $request->password])) {
                $userStatus = Auth::User()->user_status;
                if($userStatus=='1') { 
                    session(['layout' => 'horizontal']);
                    return redirect()->intended('dashboard')->with('success','Login Successfully');
                }else{
                    Auth::logout();
                    Session::flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
            }
        }else{
            Auth::logout();
            Session::flush();
            return redirect('login')->with('warning', INACTIVE_ACCOUNT);
        }
     }

     //Send welcome email to user as many times want to send.
     public function SendWelcomeEmail(Request $request)
     {
            $user =  User::where(["id" => $request->user_id])->first();
            $getTemplateData = EmailTemplate::find(6);
             $fullName=$user->first_name. ' ' .$user->last_name;
             $email=$user->email;
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
 
             $userEmail = [
                 "from" => FROM_EMAIL,
                 "from_title" => FROM_EMAIL_TITLE,
                 "subject" => $getTemplateData->subject,
                 "to" => $user->email,
                 "full_name" => $fullName,
                 "mail_body" => $mail_body
                 ];
           $sendEmail = $this->sendMail($userEmail);
           if($sendEmail=="1"){
             $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
             $user->save();
                session(['popup_success' => 'Success! Welcome email sent!']);
           }else{
                session(['popup_error' => 'Error! Unable to send email!']);
           }
            return response()->json(['errors'=>'','user_id'=>$user->id]);
           exit;
        }

     public function dashboard()
     {
        $lastLoginUsers = User::where("parent_user",Auth::User()->id)->orderBy('last_login','desc')->limit(5)->get();
        return view('contract.dashboard',['lastLoginUsers'=>$lastLoginUsers]);

     }
     public function attorneysView(Request $request,$id)
     {
         $contractUserID=base64_decode($id);
        $userProfile = User::select("users.*","countries.name as countryname")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->first();
            if(!empty($userProfile)){
                //if parent user then data load using user id itself other wise load using parent user
                if($userProfile->parent_user==0){
                    $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'))->where("users.id",$contractUserID)->get();
                }else{
                    $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'))->where("users.id",$userProfile->parent_user)->get();             
                }
            }
            $CaseMasterData=$CaseMasterClient=$practiceAreaList=$caseStageList=$selectdUSerList=$loadFirmUser=$CaseMasterCompany =[];
            if(\Route::current()->getName()=="contacts/attorneys/cases"){
                $getChildUsers=$this->getParentAndChildUserIds();
                $childUSersCase = CaseStaff::select("case_id")->where('user_id',$contractUserID)->get()->pluck('case_id');
                $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
                $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
                $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
                $practiceAreaList = CasePracticeArea::where("status","1")->whereIn("created_by",$getChildUsers)->get();  
                $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  
                $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
                $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
                $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
                $getChildUsers[]=Auth::user()->id;
                $getChildUsers[]="0"; //This 0 mean default category need to load in each user
                $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
          
            }

            $case = CaseStaff::leftJoin('case_master','case_master.id',"=","case_staff.case_id")->leftjoin("users","case_staff.user_id","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole",'case_staff.rate_amount',"case_staff.id as case_staff_id","case_staff.id as case_staff_id","users.default_rate as user_default_rate","case_staff.rate_type as case_staff_rate_type")->where("case_staff.user_id",$contractUserID)->where("firm_name",Auth::user()->firm_name)->where("case_master.is_entry_done","1")->get();
            
            
            return view('contract.attorneysView',compact('userProfile','userProfileCreatedBy','id','CaseMasterClient','CaseMasterCompany','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','case'));
     }   
        

     public function loadProfile(Request $request)
     {
        $contractUserID=base64_decode($request->user_id);
         $country = Countries::get();
         $userProfile = User::select("users.*","countries.name as countryname")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->first();
         if(!empty($userProfile)){
             $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'))->where("users.id",$userProfile->parent_user)->get();
         }
         return view('contract.loadProfile',compact('userProfile','country','userProfileCreatedBy'));
     }
     public function saveProfile(Request $request)
     {
         $validator = \Validator::make($request->all(), [
             'first_name' => 'required',
             'last_name' => 'required',
             'email' => 'required|unique:users,email,'.base64_decode($request->uid).',id,deleted_at,NULL',
             'user_type' => 'required',
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
             $user =User::firstOrNew(array('id' => base64_decode($request->uid)));
           
             $user->first_name=trim($request->first_name); 
             $user->middle_name=trim($request->middle_name); 
             $user->last_name=trim($request->last_name); 
             $user->street=trim($request->street); 
             $user->apt_unit=trim($request->apt_unit); 
             $user->city=trim($request->city); 
             $user->state=trim($request->state); 
             $user->postal_code=trim($request->postal_code); 
             $user->country=trim($request->country); 
             $user->home_phone=trim($request->home_phone); 
             $user->work_phone=trim($request->work_phone); 
             $user->mobile_number=trim($request->cell_phone); 
             $user->user_title=trim($request->user_title); 
             $user->default_rate=trim(str_replace(",","",$request->default_rate)); 
             $user->user_type=$request->user_type; 
             $user->updated_by =Auth::User()->id;
             $user->save();

             if($user->email!=$request->email){
                $user->email=$request->email;
                $user->token  = Str::random(40);
                $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
                $getTemplateData = EmailTemplate::find(6);
                $fullName=$request->first_name. ' ' .$request->last_name;
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
             }
            session(['popup_success' => 'Profile data has been updated.']);
            return response()->json(['errors'=>'','user_id'=>$user->id]);
            exit;
         }
     }

     public function loadDeactivateUser(Request $request)
     {
        $contractUserID=base64_decode($request->user_id);
        $user = User::select("users.*")->where("users.id",$contractUserID)->first();
        $allUser = User::select("*")->where("users.parent_user",$user->parent_user)->where("users.id","!=",$user->id)->get();
        return view('contract.loadDeactivateUser',compact('user','allUser'));
     }

     public function saveDeactivate(Request $request)
     {
         $validator = \Validator::make($request->all(), [
             'reason' => 'required'
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
             $user =User::find($request->user_id);
             if(isset($request->user_id)) { $user->user_status="3"; }
             $user->updated_by =Auth::User()->id;
             $user->save();

             $userDeactivate =new DeactivatedUser;
             $userDeactivate->user_id= $request->user_id;
             if(isset($request->reason)) { $userDeactivate->reason=$request->reason; }
             if(isset($request->other_reason)) { $userDeactivate->other_reason=$request->other_reason; }
             if(isset($request->assign_to)) { $userDeactivate-> assigned_to=$request->assign_to; }
             $userDeactivate->created_by =Auth::User()->id;
             $userDeactivate->save();
             session(['popup_success' => 'Profile data has been updated.']);

            return response()->json(['errors'=>'']);
            exit;
         }
     }


     //Client
     public function clientIndex()
    {
        $user = User::latest()->where("user_level","2")->get(); //Level 2= Client
        $ClientGroup=ClientGroup::where("firm_id",Auth::User()->firm_name)->orWhere("created_by",0)->get();
        $country = Countries::get();
        return view('client.index',compact('user','country','ClientGroup'));
    }

    public function loadClient()
    {   
        $columns = array('users.id','users.id','first_name','last_name', 'email', 'email', 'user_title','users.user_status','last_login','users.created_at');
        $requestData= $_REQUEST;
        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->leftJoin('client_group','client_group.id','=','users_additional_info.contact_group_id')->select('users.*',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'),'users_additional_info.contact_group_id','client_group.group_name',"users.id as id");
        $user = $user->where("user_level","2"); //Load all client 

        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $user = $user->whereIn("parent_user",$getChildUsers);
        }else{
            $user = $user->where("parent_user",Auth::user()->id); //Logged in user not visible in grid
        }
        if($requestData['tab']=="active"){
            $user = $user->whereIn("users.user_status",[1,2]);
        }else{
            $user = $user->where("users.user_status","4"); 
        }
        if($requestData['filter_on']!=""){
            $user = $user->where("users_additional_info.contact_group_id",$requestData['filter_on']);
        }

        $totalData=$user->count();
        $totalFiltered = $totalData;  
        if( !empty($requestData['search']['value']) ) {   
           
            $user = $user->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('email', 'like', "%".$requestData['search']['value']."%" );
                    // $select->orWhere('created_at', 'like', "%".$requestData['search']['value']."%" );
                   
                });
            });
            
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $user->count(); 
        }
        $user = $user->offset($requestData['start'])->limit($requestData['length']);
        $user = $user->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $user = $user->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $user   // total data array
        );
        echo json_encode($json_data);  // send data as json format
    }
    public function loadAddContact(Request $request)
    {
       DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
       $contractUserID=base64_decode($request->user_id);
       $ClientGroup=ClientGroup::where("status","1");
       $getChildUsers=$this->getParentAndChildUserIds();
       $ClientGroup = $ClientGroup->whereIn("created_by",$getChildUsers)->orWhere('created_by',"0")->get();          

       $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->get();
       $country = Countries::get();
       $case_id='';
       if(isset($request->case_id)){
           $case_id=$request->case_id;
       }
       $client_portal_access=Firm::find(Auth::User()->firm_name);
       return view('client.addClient',compact("country",'ClientGroup','CompanyList','case_id','client_portal_access'));
    }
    public function loadAddContactFromInvoice(Request $request)
    {
       DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
       $contractUserID=base64_decode($request->user_id);
       $ClientGroup=ClientGroup::where("status","1");
       $getChildUsers=$this->getParentAndChildUserIds();
       $ClientGroup = $ClientGroup->whereIn("created_by",$getChildUsers)->orWhere('created_by',"0")->get();          

       $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->get();
       $country = Countries::get();
       $case_id='';
       if(isset($request->case_id)){
           $case_id=$request->case_id;
       }
       $client_portal_access=Firm::find(Auth::User()->firm_name);
       return view('client.addClientFromInvoice',compact("country",'ClientGroup','CompanyList','case_id','client_portal_access'));
    }
    public function loadAddContactFromCompany(Request $request)
    {
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
       $contractUserID=base64_decode($request->user_id);
       $ClientGroup=ClientGroup::where("status","1");
       $getChildUsers=$this->getParentAndChildUserIds();
       $ClientGroup = $ClientGroup->whereIn("created_by",$getChildUsers)->orWhere('created_by',"0")->get();          
       $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->get();
       $country = Countries::get();
       $company_id=$request->company_id;
       $client_portal_access=Firm::find(Auth::User()->firm_name);

       return view('client.addClientFromCompany',compact("country",'ClientGroup','CompanyList','company_id','client_portal_access'));
    }
    public function loadAddContactFromCase(Request $request)
    {
       $contractUserID=base64_decode($request->user_id);
       $ClientGroup=ClientGroup::where("status","1");
       $getChildUsers=$this->getParentAndChildUserIds();
       $ClientGroup = $ClientGroup->whereIn("created_by",$getChildUsers)->orWhere('created_by',"0")->get();          

       $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->get();
       $country = Countries::get();
       $case_id='';
       if(isset($request->case_id)){
           $case_id=$request->case_id;
       }
       $client_portal_access=Firm::find(Auth::User()->firm_name);

       return view('client.addClientFromCase',compact("country",'ClientGroup','CompanyList','case_id','client_portal_access'));
    }
    public function saveAddContact(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:250',
            'last_name' => 'required|max:250',
            'email' => 'nullable|unique:users,email',

            // 'email' => 'required_if:client_portal_enable,on|unique:users,email',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user = new User;
            if(isset($request->first_name)) { $user->first_name=$request->first_name; }
            if(isset($request->middle_name)) { $user->middle_name=$request->middle_name; }
            if(isset($request->last_name)) { $user->last_name=$request->last_name; }
            if(isset($request->email)) { $user->email=$request->email; }
            if(isset($request->home_phone)) { $user->home_phone=$request->home_phone; }
            if(isset($request->work_phone)) { $user->work_phone=$request->work_phone; }
            if(isset($request->cell_phone)) { $user->mobile_number=$request->cell_phone; }
            if(isset($request->address)) { $user->street=$request->address; }
            if(isset($request->city)) { $user->city=$request->city; }
            if(isset($request->state)) { $user->state=$request->state; }
            if(isset($request->postal_code)) { $user->postal_code=$request->postal_code; }
            if(isset($request->country)) { $user->country=$request->country; }
            if(isset(Auth::User()->firm_name)) { $user->firm_name=Auth::User()->firm_name; }
            

            $user->token  = Str::random(40);
            $user->parent_user =Auth::User()->id;
            $user->user_status  = "1";  // Default status is active for client.
            $user->user_level  = "2";  // Default status is inactive once verified account it will activated.
            $user->created_by =Auth::User()->id;
            $user->save();
            session(['clientId' => $user->id]);


            $UsersAdditionalInfo= new UsersAdditionalInfo;
            $UsersAdditionalInfo->user_id=$user->id; 
            if(isset($request->contact_group)) { $UsersAdditionalInfo->contact_group_id=$request->contact_group; }
            if(isset($request->contact_group_text)) { 
                $ClientGroup=new ClientGroup;
                $ClientGroup->group_name=$request->contact_group_text; 
                $ClientGroup->status="1";
                $ClientGroup->email="";
                $ClientGroup->created_by=Auth::User()->id;
                $ClientGroup->save();
                $UsersAdditionalInfo->contact_group_id=$ClientGroup->id;
            }
            
            // if(isset($request->company_name)) { $UsersAdditionalInfo->company_id=$request->company_name; }
            if(isset($request->company_name)) { $UsersAdditionalInfo->multiple_compnay_id=implode(",",$request->company_name); }

            if(isset($request->company_name_text)) { 
                $companyUser=new User;
                $companyUser->first_name=$request->company_name_text; 
                $companyUser->created_by =Auth::User()->id;
                $companyUser->user_level="4";
                $companyUser->user_title="";
                $companyUser->parent_user=Auth::User()->id;
                $companyUser->save();
                $UsersAdditionalInfo->company_id=$companyUser->id;
            }

            if(isset($request->address2)) { $UsersAdditionalInfo->address2=$request->address2; }
            if(isset($request->fax_number)) { $UsersAdditionalInfo->fax_number=$request->fax_number; }
            if(isset($request->job_title)) { $UsersAdditionalInfo->job_title=$request->job_title; }
            if(isset($request->driver_license)) { $UsersAdditionalInfo->driver_license=$request->driver_license; }
            if(isset($request->driver_state)) { $UsersAdditionalInfo->license_state=$request->driver_state; }
            if(isset($request->website)) { $UsersAdditionalInfo->website=$request->website; }
            if(isset($request->case_name)) { $UsersAdditionalInfo->case_name=$request->case_name; }
            if(isset($request->notes)) { $UsersAdditionalInfo->notes=$request->notes; }
            if(isset($request->dob)) { $UsersAdditionalInfo->dob=date('Y-m-d',strtotime($request->dob)); }
            if(isset($request->client_portal_enable)) { $UsersAdditionalInfo->client_portal_enable="1"; }
            $UsersAdditionalInfo->created_by =Auth::User()->id;
            $UsersAdditionalInfo->save();


            if(isset($request->case_id)) {
                $CaseClientSelection = new CaseClientSelection;
                $CaseClientSelection->case_id=$request->case_id; 
                $CaseClientSelection->selected_user=$user->id; 
                $CaseClientSelection->created_by=Auth::user()->id; 
                $CaseClientSelection->save();

            }


            //if click on save and add case button then
            if(isset($request->saveandaddcase) && $request->saveandaddcase=='yes'){
                $TempUserSelection = new TempUserSelection;
                $TempUserSelection->selected_user=$user->id;
                $TempUserSelection->user_id=Auth::user()->id;
                $TempUserSelection->save();
            }
            
            if($UsersAdditionalInfo->client_portal_enable=="1"){
                    $firmData=Firm::find(Auth::User()->firm_name);
                    $getTemplateData = EmailTemplate::find(21);
                    $fullName=$request->first_name. ' ' .$request->last_name;
                    $email=$request->email;
                    $token=url('firmclient/verify', $user->token);
                    $mail_body = $getTemplateData->content;
                    $mail_body = str_replace('{name}', $fullName, $mail_body);
                    $mail_body = str_replace('{firm}', $firmData['firm_name'], $mail_body);
                    $mail_body = str_replace('{email}', $email,$mail_body);
                    $mail_body = str_replace('{token}', $token,$mail_body);
                    $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                    $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
                    $mail_body = str_replace('{regards}', $firmData['firm_name'], $mail_body);  
                    $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
                    $mail_body = str_replace('{refuser}', $firmData['firm_name'], $mail_body);                          
                    $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                    $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);       
                    $mail_body = str_replace('{cell}', CELL, $mail_body);       
                    $userEmail = [
                        "from" => FROM_EMAIL,
                        "from_title" => FROM_EMAIL_TITLE,
                        "subject" => $getTemplateData->subject. " ". $firmData['firm_name'],
                        "to" => $request->email,
                        "full_name" => $fullName,
                        "mail_body" => $mail_body
                        ];
                    $sendEmail = $this->sendMail($userEmail);
                }
           
            
           $ClientActivityHistory=[];
           $ClientActivityHistory['acrtivity_title']='added contact';
           $ClientActivityHistory['activity_by']=Auth::User()->id;
           $ClientActivityHistory['activity_for']=($user->id)??NULL;
           $ClientActivityHistory['type']="2";
           $ClientActivityHistory['task_id']=NULL;
           $ClientActivityHistory['case_id']=NULL;
           $ClientActivityHistory['created_by']=Auth::User()->id;
           $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
           $this->saveClientActivity($ClientActivityHistory);
           

        //    $data=[];
        //     $data['user_id']=$user->id;
        //     $data['client_id']=$user->id;
        //     $data['activity']='added contact';
        //     $data['type']='contact';
        //     $data['action']='add';
        //     $CommonController= new CommonController();
        //     $CommonController->addMultipleHistory($data);


           if(!isset($request->fromCase)){ 
            session(['popup_success' => 'Your client has been created.']); 
           }
            //if click on save and add case button then
            if(isset($request->saveandaddcase) && $request->saveandaddcase=='yes'){
                session(['popup_success' => '']); 
            }
           
           return response()->json(['errors'=>'','user_id'=>$user->id]);
            exit;
        }
    }

    public function loadEditContact(Request $request)
    {
        $user_id=$request->user_id;
      
        $ClientGroup=ClientGroup::where("status","1");
        $getChildUsers=$this->getParentAndChildUserIds();
        $ClientGroup = $ClientGroup->whereIn("created_by",$getChildUsers)->orWhere('created_by',"0")->get();   
       
       $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->get();
       $country = Countries::get();
       $userData=User::find($user_id);
       $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$user_id)->first();
       return view('client.editClient',compact("country",'ClientGroup','CompanyList','userData','UsersAdditionalInfo'));
    }
    public function saveEditContact(Request $request)
    {
        $user_id=$request->user_id;
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|max:250',
            'last_name' => 'required|max:250',
            'email' => 'nullable|unique:users,email,'.$user_id,
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user = User::find($user_id);
             $user->first_name=$request->first_name; 
             $user->middle_name=$request->middle_name; 
             $user->last_name=$request->last_name; 
             $user->email=$request->email;
             $user->home_phone=$request->home_phone; 
             $user->work_phone=$request->work_phone; 
            $user->mobile_number=$request->cell_phone; 
             $user->street=$request->address; 
             $user->city=$request->city; 
             $user->state=$request->state; 
             $user->postal_code=$request->postal_code; 
             $user->country=$request->country; 
            $user->updated_by =Auth::User()->id;
            $user->save();
            
            $UsersAdditionalInfo= UsersAdditionalInfo::updateOrCreate(array("user_id"=>$user_id));
           
            if(isset($request->contact_group)) { $UsersAdditionalInfo->contact_group_id=$request->contact_group; }
            if(isset($request->contact_group_text)) { 
                $ClientGroup=ClientGroup::updateOrCreate(array("id"=> $request->company_contact_groupname));
                $ClientGroup->group_name=$request->contact_group_text; 
                $ClientGroup->status="1";
                $ClientGroup->created_by =Auth::User()->id;
                $ClientGroup->save();
                $UsersAdditionalInfo->contact_group_id=$ClientGroup->id;
            }
            // if(isset($request->company_name)) { $UsersAdditionalInfo->company_id=$request->company_name; }
            if(isset($request->company_name)) { $UsersAdditionalInfo->multiple_compnay_id=implode(",",$request->company_name); }

            if(isset($request->company_name_text)) { 
                // $companyUser=User::updateOrCreate(array("id"=>$request->company_name));
                $companyUser=new User;
                $companyUser->first_name=$request->company_name_text; 
                $companyUser->created_by =Auth::User()->id;
                $companyUser->user_level="4";
                $companyUser->user_title="";
                $companyUser->parent_user =Auth::User()->id;
                $companyUser->save();
                $UsersAdditionalInfo->company_id=$companyUser->id;
            }
            if(isset($request->address2)) { $UsersAdditionalInfo->address2=$request->address2; }
            if(isset($request->fax_number)) { $UsersAdditionalInfo->fax_number=$request->fax_number; }
            if(isset($request->job_title)) { $UsersAdditionalInfo->job_title=$request->job_title; }
            if(isset($request->driver_license)) { $UsersAdditionalInfo->driver_license=$request->driver_license; }
            if(isset($request->driver_state)) { $UsersAdditionalInfo->license_state=$request->driver_state; }
            if(isset($request->website)) { $UsersAdditionalInfo->website=$request->website; }
            if(isset($request->case_name)) { $UsersAdditionalInfo->case_name=$request->case_name; }
            if(isset($request->notes)) { $UsersAdditionalInfo->notes=$request->notes; }
            if(isset($request->dob)) { $UsersAdditionalInfo->dob=date('Y-m-d',strtotime($request->dob)); }else{$UsersAdditionalInfo->dob=NULL;}
            if(isset($request->client_portal_enable)) { $UsersAdditionalInfo->client_portal_enable="1"; }else{$UsersAdditionalInfo->client_portal_enable="0";}
            $UsersAdditionalInfo->created_by =Auth::User()->id;
            $UsersAdditionalInfo->save();

            $ClientActivityHistory=[];
            $ClientActivityHistory['acrtivity_title']='update contact';
            $ClientActivityHistory['activity_by']=Auth::User()->id;
            $ClientActivityHistory['activity_for']=($user->id)??NULL;
            $ClientActivityHistory['type']="2";
            $ClientActivityHistory['task_id']=NULL;
            $ClientActivityHistory['case_id']=NULL;
            $ClientActivityHistory['created_by']=Auth::User()->id;
            $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
            $this->saveClientActivity($ClientActivityHistory);

            $data=[];
            $data['user_id']=$user->id;
            $data['client_id']=$user_id;
            $data['activity']='Update Contact';
            $data['type']='contact';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Your client has been updated.']);
            return response()->json(['errors'=>'','user_id'=>$user->id]);
            exit;
        }
    }
   

    
     //Client Group
     public function clientgroupIndex()
    {
        $user = User::latest()->get();
        $country = Countries::get();
        return view('client_group.index',compact('user','country'));
    }

    public function loadClientgroup()
    {   
        $columns = array('id','group_name','status');
        $requestData= $_REQUEST;
        $ClientGroup = ClientGroup::leftJoin("users","client_group.created_by","=","users.id")
        ->select('client_group.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
       
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $getChildUsers[]="0"; //This 0 mean default category need to load in each user
            $ClientGroup = $ClientGroup->where("status","1")->whereIn("client_group.created_by",$getChildUsers);
        }else{
            $getChildUsers=array();
            $getChildUsers[]=Auth::user()->id;
            $getChildUsers[]="0";
            $ClientGroup = $ClientGroup->whereIn("client_group.created_by",$getChildUsers)->where("status","1");

        }


        $ClientGroup = $ClientGroup; 
        $totalData=$ClientGroup->count();
        $totalFiltered = $totalData;  
        if( !empty($requestData['search']['value']) ) {  
            $ClientGroup = $ClientGroup->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere('group_name', 'like', "%".$requestData['search']['value']."%");
                });
            });            
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $ClientGroup->count(); 
        }
        $ClientGroup = $ClientGroup->offset($requestData['start'])->limit($requestData['length']);
        $ClientGroup = $ClientGroup->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $ClientGroup = $ClientGroup->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ), 
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $ClientGroup   
        );
        echo json_encode($json_data);  // send data as json format
    }
    //Client Group
    public function loadAddContactGroup()
    {
        return view('client_group.addClientGroup');
    }

    public function saveAddContactGroup(Request $request)
    {
        $user_id=$request->user_id;
        $validator = \Validator::make($request->all(), [
            'group_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $getChildUsers = User::select("id")->where('firm_name',Auth::user()->firm_name)->get()->pluck('id');
            $ClientGroupCheck=ClientGroup::select('*')->whereIn('created_by',$getChildUsers)->where('group_name',$request->group_name)->get()->count();
            if($ClientGroupCheck<="0"){
                $ClientGroup=new ClientGroup;
                $ClientGroup->group_name=$request->group_name; 
                $ClientGroup->status="1";
                $ClientGroup->created_by =Auth::User()->id;
                $ClientGroup->save();
                session(['popup_success' => 'Your contact group has been created.']);
                return response()->json(['errors'=>'','group_id'=>$ClientGroup->id]);
                exit;
            }else{
                return response()->json(['errors'=>['Name already exists']]);
                exit;
            }
        }
    }
    public function deleteClientGroup(Request $request)
    {
        $group_id=$request->group_id;
        ClientGroup::where("id", $group_id)->delete();
        //When delete the client group assinged to default group to each client which hase assigned this group.
        UsersAdditionalInfo::where('contact_group_id',$group_id)->update(['contact_group_id'=>"1"]);
        session(['popup_success' => 'Contact group was deleted']);

        return response()->json(['errors'=>'','group_id'=>$group_id]);
        exit;
    }
    public function loadEditClientGroup(Request $request)
    {
        $id=$request->id;
       $ClientGroup=ClientGroup::find($id);    
       return view('client_group.editClientGroup',compact("ClientGroup"));
    }
    public function saveEditClientGroup(Request $request)
    {
        $id=$request->id;
        $validator = \Validator::make($request->all(), [
            'group_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $ClientGroup=ClientGroup::find($id);
            $ClientGroup->group_name=$request->group_name; 
            $ClientGroup->save();
            session(['popup_success' => 'Your contact group has been updated.']);

            return response()->json(['errors'=>'','group_id'=>$ClientGroup->id]);
            exit;
        }
    }


      //Company
      public function companyIndex()
      {
          return view('company.index');
      }
  
      public function loadCompany()
      {   
            $columns = array('id','first_name','user_title', 'email', 'email', 'user_title','user_status','last_login','created_at');
            $requestData= $_REQUEST;
            
            $user = User::select('*',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'));
            $user = $user->where("user_level","4");  //4=Company
          
            if(Auth::user()->parent_user==0){
                $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
                $getChildUsers[]=Auth::user()->id;
                $user = $user->whereIn("parent_user",$getChildUsers);              
            }else{
                $user = $user->where("parent_user",Auth::user()->id); //Logged in user not visible in grid
            }
            if($requestData['tab']=="active"){
                $user = $user->whereIn("users.user_status",["1","2"]);
            }else{
                $user = $user->where("users.user_status","4"); 
            }
            $totalData=$user->count();
            $totalFiltered = $totalData;
            if( !empty($requestData['search']['value']) ) {   
                $user = $user->where( function($q) use ($requestData){
                    $q->where( function($select) use ($requestData){
                        $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                    });
                });
            }
            if( !empty($requestData['search']['value']) ) { 
                $totalFiltered = $user->count(); 
            }

            $user = $user->offset($requestData['start'])->limit($requestData['length']);
            $user = $user->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
            $user = $user->get();
            $json_data = array(
                "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                "recordsTotal"    => intval( $totalData ),  // total number of records
                "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data"            => $user   // total data array
            );
            echo json_encode($json_data);  // send data as json format
      }

    public function loadAddCompany(Request  $request)
    {
        $case_id='';
        if(isset($request->case_id)){
            $case_id=$request->case_id;
        }
        $country = Countries::get();
        return view('company.addCompany',compact('country','case_id'));
    }

    public function saveAddCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'company_name' => 'required|min:1|max:255|unique:users,first_name,NULL,id,firm_name,'.Auth::User()->firm_name,
            'main_phone'=>'nullable|numeric',
            'fax_number'=>'nullable|numeric',
            'email' => 'required|email|unique:users,email',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
          
            $user = new User;
            if(isset($request->company_name)) { $user->first_name=$request->company_name; }
            if(isset($request->email)) { $user->email=$request->email; }
            if(isset($request->main_phone)) { $user->mobile_number=$request->main_phone; }
            if(isset($request->address)) { $user->street=$request->address; }
            if(isset($request->city)) { $user->city=$request->city; }
            if(isset($request->state)) { $user->state=$request->state; }
            if(isset($request->postal_code)) { $user->postal_code=$request->postal_code; }
            if(isset($request->country)) { $user->country=$request->country; }
            if(isset(Auth::User()->firm_name)) { $user->firm_name=Auth::User()->firm_name; }

            $user->token  = Str::random(40);
            $user->parent_user =Auth::User()->id;
            $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
            $user->user_level  = "4"; //4-company  
            $user->user_type  = "4"; //4-none  
            $user->created_by =Auth::User()->id;
            $user->save();

            $UsersAdditionalInfo= new UsersAdditionalInfo;
            $UsersAdditionalInfo->user_id=$user->id; 
            if(isset($request->address2)) { $UsersAdditionalInfo->address2=$request->address2; }
            if(isset($request->website)) { $UsersAdditionalInfo->website=$request->website; }
            if(isset($request->notes)) { $UsersAdditionalInfo->notes=$request->notes; }
            if(isset($request->fax_number)) { $UsersAdditionalInfo->fax_number=$request->fax_number; }
            $UsersAdditionalInfo->created_by =Auth::User()->id;
            $UsersAdditionalInfo->save();

            if(isset($request->case_id)) {
                $CaseClientSelection = new CaseClientSelection;
                $CaseClientSelection->case_id=$request->case_id; 
                $CaseClientSelection->selected_user=$user->id; 
                $CaseClientSelection->created_by=Auth::user()->id; 
                $CaseClientSelection->save();
            }
            
            session(['popup_success' => 'Your company has been created.']);

            return response()->json(['errors'=>'','user_id'=>$user->user_id]);
            exit;
        }
    }
    public function changeRolePopup(Request  $request)
    {
        $user_id=$request->user_id;
        $case_id=$request->case_id;
        
        $UserRole=UserRole::where("firm_id",Auth::User()->firm_name)->get();

        $user = CaseClientSelection::select("case_client_selection.*")->where("case_client_selection.selected_user",$user_id)->where("case_client_selection.case_id",$case_id)->first();
            
        return view('contract.loadRolePopup',compact('user_id','case_id','UserRole','user'));
    }
    public function saveRolePopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_role' => 'nullable',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user_id=$request->user_id;
            $case_id=$request->case_id;
            $user = CaseClientSelection::select("case_client_selection.*")->where("case_client_selection.selected_user",$user_id)->where("case_client_selection.case_id",$case_id)->first();
            if(!empty($user))
            {
                $user = CaseClientSelection::find($user['id']);
                $user->user_role  = $request->user_role;
                $user->save(); 
            } 
            return response()->json(['errors'=>'']);
            exit;
        }
    }
    public function loadEditCompany(Request $request)
    {
        $id=$request->id;
        $company=User::find($id); 
        $country = Countries::get();
        $companyAdditionalInfo=UsersAdditionalInfo::where("user_id",$id)->first();
        return view('company.editCompany',compact('company','country','companyAdditionalInfo'));   
    }
    public function saveEditCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            //'company_name' => 'required|max:255',
            'company_name' => 'required|min:3|max:255|unique:users,first_name,'.$request->id.',id,firm_name,'.Auth::User()->firm_name,
            'main_phone'=>'nullable|numeric',
            'fax_number'=>'nullable|numeric',
            'email' => 'nullable|email|unique:users,email,'.$request->id,
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
          
            $user = User::find($request->id);
            if(isset($request->company_name)) { $user->first_name=$request->company_name; }else{ $user->first_name=NULL; }
            if(isset($request->email)) { $user->email=$request->email; }else{ $user->email=NULL; }
            if(isset($request->main_phone)) { $user->mobile_number=$request->main_phone; }else{ $user->mobile_number=NULL; }
            if(isset($request->address)) { $user->street=$request->address; }else{ $user->street=NULL; }
            if(isset($request->city)) { $user->city=$request->city; }else{ $user->city=NULL; }
            if(isset($request->state)) { $user->state=$request->state; }else{ $user->state=NULL; }
            if(isset($request->postal_code)) { $user->postal_code=$request->postal_code; }else{ $user->postal_code=NULL; }
            if(isset($request->country)) { $user->country=$request->country; }else{ $user->country=NULL; }
            $user->updated_by =Auth::User()->id;
            $user->save();

            $UsersAdditionalInfo= UsersAdditionalInfo::updateOrCreate(array("user_id"=>$request->id));
            if(isset($request->address2)) { $UsersAdditionalInfo->address2=$request->address2; }else{ $UsersAdditionalInfo->address2=NULL; }
            if(isset($request->website)) { $UsersAdditionalInfo->website=$request->website; }else{ $UsersAdditionalInfo->website=NULL; }
            if(isset($request->notes)) { $UsersAdditionalInfo->notes=$request->notes; }else{ $UsersAdditionalInfo->notes=NULL; }
            if(isset($request->fax_number)) { $UsersAdditionalInfo->fax_number=$request->fax_number; }else{ $UsersAdditionalInfo->fax_number=NULL; }
            $UsersAdditionalInfo->created_by =Auth::User()->id;
            $UsersAdditionalInfo->save();
            session(['popup_success' => 'Your company has been updated.']);

            return response()->json(['errors'=>'','user_id'=>$user->user_id]);
            exit;
        }
    }

    public function createCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'company_name_text' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $existStatus=User::select('id')->where('first_name',$request->company_name_text)->where("firm_name",Auth::User()->firm_name)->count();
            if($existStatus=="0"){
                $companyUser=new User;
                $companyUser->first_name=$request->company_name_text; 
                $companyUser->created_by =Auth::User()->id;
                $companyUser->user_level="4";
                $companyUser->user_title="";
                $companyUser->parent_user=Auth::User()->id;
                $companyUser->firm_name=Auth::User()->firm_name;
                $companyUser->save();

                $firstCheck=TempUserSelection::where("selected_user",$companyUser->id)->where("user_id",Auth::user()->id)->get();
        
                if($firstCheck->isEmpty()){
                    $TempUserSelection = new TempUserSelection;
                    $TempUserSelection->selected_user=$companyUser->id;
                    $TempUserSelection->user_id=Auth::user()->id;
                    $TempUserSelection->save();
                }
            }else{
                $CustomError[]='Compnay name already exists';
                return response()->json(['errors'=>$CustomError]);
                exit;
            }
            return response()->json(['errors'=>'','user_id'=>$companyUser->id]);
            exit;
        }
    }

    public function realoadCompanySelection(Request $request)
    {
        $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->get();
        // $selectdCompany=User::select('id')->where("firm_name",Auth::User()->firm_name)->where("created_by",Auth::User()->id)->pluck("id")->toArray();    
        $selectdCompany=TempUserSelection::select("selected_user")->where("user_id",Auth::user()->id)->pluck("selected_user")->toArray();  
        if(isset($request->client_id)){
            $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$request->client_id)->pluck("multiple_compnay_id")->toArray();
            $selectdCompany=array_merge($selectdCompany,$UsersAdditionalInfo);
        }

        return view('contract.realoadCompanySelection',compact('selectdCompany','CompanyList'));   

    }
    public function removeCompany(Request $request)
    {
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->whereNotIn("selected_user",$request->unselected_value)->delete();

    }

    public function staffCaseList()
    {   
        $requestData= $_REQUEST;    
        //Removed deleted case staff from the child table if exist.
        $caseStaff = CaseStaff::select("*")->where("case_staff.user_id",base64_decode($requestData['user_id']))->get();
        foreach($caseStaff as $kk=>$vv){
            $count=CaseMaster::where("id",$vv->case_id)->count();
            if($count<=0){
              CaseStaff::where("case_staff.user_id",$vv->user_id)->where("case_staff.case_id",$vv->case_id)->delete();
            }
        }

        $columns = array('id', 'case_title', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $case = CaseStaff::leftJoin('case_master','case_master.id',"=","case_staff.case_id")
        ->leftjoin("users","case_staff.user_id","=","users.id")
        ->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole",'case_staff.rate_amount',"case_staff.id as case_staff_id","case_staff.id as case_staff_id","users.default_rate as user_default_rate","case_staff.rate_type as case_staff_rate_type");
        $case = $case->where("case_staff.user_id",base64_decode($requestData['user_id']));
        $case = $case->where("firm_name",Auth::user()->firm_name); //Logged in user not visible in grid
        $case = $case->where("case_master.is_entry_done","1");
        $totalData=$case->count();
        $totalFiltered = $totalData; 
        $case = $case->groupBy('case_staff.case_id');
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
    public function updateCaseRateForStaff(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required',
            'case_rate' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseStaff=CaseStaff::find($request->user_id);
            $CaseStaff->rate_amount=str_replace(",","",$request->case_rate);
            $CaseStaff->rate_type="1";
            $CaseStaff->save();
            return response()->json(['errors'=>'','id'=>$CaseStaff->id]);
            exit;
        }
    }
    public function updateDefaultRateForStaff(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required',
            'case_rate' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseStaff=User::find($request->user_id);
            $CaseStaff->default_rate=str_replace(",","",$request->case_rate);
            $CaseStaff->save();
            return response()->json(['errors'=>'','id'=>$CaseStaff->id]);
            exit;
        }
    }
    public function unlinkStaffFromCase(Request $request)
    {
      
        $validator = \Validator::make($request->all(), [
            'id' => 'required|numeric',
            'user_delete_contact_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            CaseStaff::where('case_id',$request->id)->where('user_id',$request->user_delete_contact_id)->delete();
            return response()->json(['errors'=>'','user_id'=>$request->user_delete_contact_id]);
            exit;
        }
    }

    public function linkMultipleCaseToStaff(Request $request)
    {
      
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user_id=base64_decode($request->user_id);
            $CaseMasterList=CaseMaster::select("id")->where("firm_id",Auth::User()->firm_name)->get()->pluck("id");
            foreach($CaseMasterList as $v){
               $CheckExist=CaseStaff::where("case_id",$v)->where("user_id",$user_id)->count();
                if($CheckExist<=0){
                    $CaseStaff = new CaseStaff;
                    $CaseStaff->case_id=$v; 
                    $CaseStaff->user_id=$user_id; 
                    $CaseStaff->created_by=Auth::user()->id;
                    $CaseStaff->save();

                    //Activity tab
                    $datauser=[];
                    $datauser['activity_title']='linked staff';
                    $datauser['case_id']=$v;
                    $datauser['staff_id']=$user_id;
                    $this->caseActivity($datauser);
                }
            }
            return response()->json(['errors'=>'','user_id'=>$user_id]);
            exit;
        }
    }

    public function loadAllClient(Request $request)
    {
        $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
        //Get all company related to firm
        $CompanyList = User::select("email","first_name","last_name","id","user_level")->where('user_level',4)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
    
        return view('contract.loadClient',compact('ClientList','CompanyList'));   

    }
}
  
