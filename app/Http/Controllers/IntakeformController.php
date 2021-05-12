<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Firm,App\CaseStage,App\CasePracticeArea;
use App\ReferalResource,App\LeadStatus,App\NotHireReasons;
use App\Http\Controllers\CommonController;
use App\CaseEvent,App\IntakeForm,App\IntakeFormFields,App\Countries,App\LeadAdditionalInfo,App\CaseStaff,App\CaseMaster,App\User;
use App\CaseClientSelection,App\EmailTemplate;
use App\IntakeFormDomain,App\CaseIntakeFormFieldsData,App\CaseIntakeForm;
class IntakeformController extends BaseController
{
    public function __construct()
    {
        // $this->middleware('auth');
    }
    public function form_templates()
    {
        $IntakeForm=IntakeForm::where("firm_name",Auth::User()->firm_name)->where("form_type","0")->orderBy("created_at","DESC")->get();
        $ContactUSIntakeForm=IntakeForm::where("firm_name",Auth::User()->firm_name)->where("form_type","1")->count();
        if($ContactUSIntakeForm <=0){
            $IntakeForm=new IntakeForm;
            $IntakeForm->form_name="Contact Us"; 
            $IntakeForm->form_introduction=NULL;
            $IntakeForm->form_unique_id=md5(time());
            $IntakeForm->background_color="FFFFFF";
            $IntakeForm->button_color='0070C0';
            $IntakeForm->form_font_color='000000';
            $IntakeForm->button_font_color='FFFFFF';
            $IntakeForm->form_font='helvetica';
            $IntakeForm->button_font='helvetica';
            $IntakeForm->form_type='1';
            $IntakeForm->firm_name=Auth::User()->firm_name;
            $IntakeForm->created_by =Auth::User()->id;
            $IntakeForm->save();


            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$IntakeForm->id; 
            $IntakeFormFields->form_category='contact_field';
            $IntakeFormFields->form_field='name';
            $IntakeFormFields->client_friendly_lable='Name';
            $IntakeFormFields->is_required="yes";
            $IntakeFormFields->sort_order=1;
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();

            
            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$IntakeForm->id; 
            $IntakeFormFields->form_category='contact_field';
            $IntakeFormFields->form_field='email';
            $IntakeFormFields->client_friendly_lable='Email';
            $IntakeFormFields->is_required="no";
            $IntakeFormFields->sort_order=1;
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();

            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$IntakeForm->id; 
            $IntakeFormFields->form_category='contact_field';
            $IntakeFormFields->form_field='cell_phone';
            $IntakeFormFields->client_friendly_lable='Cell Phone';
            $IntakeFormFields->is_required="no";
            $IntakeFormFields->sort_order=1;
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();

            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$IntakeForm->id; 
            $IntakeFormFields->form_category='unmapped_field';
            $IntakeFormFields->form_field='long_text';
            $IntakeFormFields->client_friendly_lable='How may we be of service?';
            $IntakeFormFields->is_required="no";
            $IntakeFormFields->sort_order=1;
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();
        }
        $ContactUSIntakeForm=IntakeForm::where("firm_name",Auth::User()->firm_name)->where("form_type","1")->orderBy("created_at","DESC")->get();
         return view('intake_forms.index',compact('IntakeForm','ContactUSIntakeForm'));
    }

    public function load_form_templates()
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
    public function newForm()
    {
         return view('intake_forms.newForm');
    }
    public function loadFeilds(Request $request)
    {
        // print_r($request->all());exit;
        $typpe=$request->typpe;
        $alreadySelected=$request->alreadySelected;
        if($request->typpe=="contact_field"){
            return view('intake_forms.loadFields',compact('typpe','alreadySelected'));
        }
        if($request->typpe=="case_field"){
            return view('intake_forms.loadFields',compact('typpe','alreadySelected'));
        }
        if($request->typpe=="unmapped_field"){
            return view('intake_forms.loadFields',compact('typpe','alreadySelected'));
        }
    }

    public function loadFieldsSelected(Request $request)
    {
        $typpe=$request->typpe;
        $selectedRow=$request->selectedRow;
        $alreadySelected=$request->alreadySelected;

        return view('intake_forms.loadFieldsSelected',compact('typpe','selectedRow','alreadySelected'));
    }

    public function saveIntakeForm(Request $request)
    {
        // print_r($request->all());exit;
        // $counter=array_count_values($request->form_field);
        // foreach($counter as $k=>$v){
        //     if($v>1){
        //         return response()->json(['errors'=>[str_replace('_',' ',ucwords($k,'_')) ." field is the already selected, Please remove it."]]);
        //     }
        // }
        $validator = \Validator::make($request->all(), [
            'form_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $IntakeForm=new IntakeForm;
            $IntakeForm->form_name=$request->form_name; 
            $IntakeForm->form_introduction=($request->form_introduction)??NULL;
            $IntakeForm->form_unique_id=md5(time());
            $IntakeForm->background_color=$request->background_color_code;
            $IntakeForm->button_color=$request->button_color_code;
            $IntakeForm->form_font_color=$request->form_font_color_code;
            $IntakeForm->button_font_color=$request->button_font_color_code;
            $IntakeForm->form_font=$request->form_font_style;
            $IntakeForm->button_font=$request->form_button_font_style;
            $IntakeForm->firm_name=Auth::User()->firm_name;
            $IntakeForm->created_by =Auth::User()->id;
            $IntakeForm->save();

            $this->saveAllFields($request,$IntakeForm->id);

            if($request->pressButton=="s"){
                return response()->json(['errors'=>'','url'=>$IntakeForm->form_unique_id]);
                exit; 
                // return redirect()->route('form_templates/{id}',[$IntakeForm->form_unique_id]);
                // exit;
            }else{
                return response()->json(['errors'=>'','url'=>'']);
                exit; 
                // return redirect()->route('form_templates');
                // exit;
    
            }
        }
    }
    public function saveAllFields($request,$formId)
    {
        $j=1;
        foreach($request->category as $i=>$v){
            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$formId; 
            if(in_array($request->category[$i],['contact_field','case_field'])){
                $IntakeFormFields->form_category=$request->category[$i];
                $IntakeFormFields->form_field=$request->form_field[$i];
                $IntakeFormFields->client_friendly_lable=$request->user_friendly_label[$i];
                if(isset($request->requiredCheckbox[$i])){
                    $IntakeFormFields->is_required="yes";
                }else{
                    $IntakeFormFields->is_required="no";
                }
            }else if(in_array($request->category[$i],['unmapped_field'])){
                $IntakeFormFields->form_category=$request->category[$i];
                $IntakeFormFields->form_field=$request->form_field[$i];
                $IntakeFormFields->client_friendly_lable=$request->user_friendly_label[$i];
                if(isset($request->requiredCheckbox[$i])){
                    $IntakeFormFields->is_required="yes";
                }else{
                    $IntakeFormFields->is_required="no";
                }
                if(isset($request->currentRow[$i])){
                    $IntakeFormFields->extra_value=json_encode($request->currentRow[$i]);
                }
            }else{
                $IntakeFormFields->form_category=NULL;
                $IntakeFormFields->header_text=$request->category[$i];
                $IntakeFormFields->is_required="no";
            }
          
            $IntakeFormFields->sort_order=$j++;
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();
        }
        
    }
    public function saveUpdateIntakeForm(Request $request)
    {
        // print_r($request->all());exit;
        // $counter=array_count_values($request->form_field);
        // foreach($counter as $k=>$v){
        //     if($v>1){
        //         return response()->json(['errors'=>[str_replace('_',' ',ucwords($k,'_')) ." field is the already selected, Please remove it."]]);
        //     }
        // }

        
        $validator = \Validator::make($request->all(), [
            'form_name' => 'required|max:255',
            'domain_name.*' => 'url'
        ],[
        'domain_name.*.url'=> "Please enter a valid HTTPS domain."]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $IntakeForm=IntakeForm::find($request->form_id);
            $IntakeForm->form_name=$request->form_name; 
            $IntakeForm->form_introduction=($request->form_introduction)??NULL;
            $IntakeForm->background_color=$request->background_color_code;
            $IntakeForm->button_color=$request->button_color_code;
            $IntakeForm->form_font_color=$request->form_font_color_code;
            $IntakeForm->button_font_color=$request->button_font_color_code;
            $IntakeForm->form_font=$request->form_font_style;
            $IntakeForm->button_font=$request->form_button_font_style;
            if(isset($request->automatic_email) && $request->automatic_email=="on" ) { $ST="yes"; }else { $ST="no"; }
            $IntakeForm->send_confimation_mail=$ST;
            $IntakeForm->updated_by=Auth::User()->id;

            if(isset($request->authorised_domain) && $request->authorised_domain=="on"){
                $IntakeForm->authorised_domain="yes";
            }else{
                $IntakeForm->authorised_domain="no";
            }
            $IntakeForm->save();

            if(!empty($request->domain_name)){
                IntakeFormDomain::where("form_id",$IntakeForm->id)->delete();
                foreach($request->domain_name as $k=>$v){
                    if($v!=''){
                        $IntakeFormDomain=new IntakeFormDomain;
                        $IntakeFormDomain->form_id=$IntakeForm->id; 
                        $IntakeFormDomain->domain_url=$v;
                        $IntakeFormDomain->created_by=Auth::User()->id;
                        $IntakeFormDomain->save();
                    }
                }
            }

            $this->updatedAllFields($request,$IntakeForm->id);
            if($request->pressButton=="s"){
                return response()->json(['errors'=>'','url'=>'/form_templates/'.$IntakeForm->form_unique_id]);
                exit; 
                // return redirect()->route('form_templates/{id}',[$IntakeForm->form_unique_id]);
                // exit;
            }else{
                return response()->json(['errors'=>'','url'=>'/form_templates']);
                exit; 
                // return redirect()->route('form_templates');
                // exit;
            }
        }
    }

    
    public function updatedAllFields($request,$formId)
    {                
        IntakeFormFields::where("intake_form_id", $formId)->delete();
        $j=1;
        foreach($request->category as $i=>$v){
            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$formId; 
            if(in_array($request->category[$i],['contact_field','case_field'])){
                $IntakeFormFields->form_category=$request->category[$i];
                $IntakeFormFields->form_field=$request->form_field[$i];
                $IntakeFormFields->client_friendly_lable=$request->user_friendly_label[$i];
                if(isset($request->requiredCheckbox[$i])){
                    $IntakeFormFields->is_required="yes";
                }else{
                    $IntakeFormFields->is_required="no";
                }
            }else if(in_array($request->category[$i],['unmapped_field'])){
                $IntakeFormFields->form_category=$request->category[$i];
                $IntakeFormFields->form_field=$request->form_field[$i];
                $IntakeFormFields->client_friendly_lable=$request->user_friendly_label[$i];
                if(isset($request->requiredCheckbox[$i])){
                    $IntakeFormFields->is_required="yes";
                }else{
                    $IntakeFormFields->is_required="no";
                }
                if(isset($request->currentRow[$i])){
                    $IntakeFormFields->extra_value=json_encode($request->currentRow[$i]);
                }
            }else{
                $IntakeFormFields->form_category=NULL;
                $IntakeFormFields->header_text=$request->category[$i];
                $IntakeFormFields->is_required="no";
            }
            $IntakeFormFields->sort_order=$j++;
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();
        }
        
    }
    public function updateIntakeForm(Request $request)
    {
        $formId=$request->id;
        $intakeForm=IntakeForm::where("form_unique_id",$formId)->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$intakeForm->id)->orderBy("sort_order","ASC")->get();
        $IntakeFormDomain=IntakeFormDomain::where("form_id",$intakeForm['id'])->get();
        $firmData=Firm::find($intakeForm->firm_name);
        return view('intake_forms.updateIntakeForm',compact('intakeForm','intakeFormFields','IntakeFormDomain','firmData'));
    }

    public function formPreview(Request $request)
    {
       
        $formId=$request->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();

        $intakeForm=IntakeForm::where("form_unique_id",$formId)->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$intakeForm->id)->orderBy("sort_order","ASC")->get();

        if($intakeForm->form_type=="1"){ //1:contact us form
            return view('intake_forms.contactUsFormPreview',compact('intakeForm','intakeFormFields','firmData','country'));
        }else{
            return view('intake_forms.formPreview',compact('intakeForm','intakeFormFields','firmData','country'));
        }
    }
    public function formSent(Request $request)
    {
        $formId=$request->id;
        $country = Countries::get();
        $caseIntakeForm=CaseIntakeForm::select("*")->where("form_unique_id",$formId)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $firmData=Firm::find($intakeForm['firm_name']);
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$intakeForm['id'])->orderBy("sort_order","ASC")->get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm['id'])->first();
        return view('intake_forms.formSent',compact('intakeForm','intakeFormFields','firmData','country','alreadyFilldedData','formId','caseIntakeForm'));
    }
    public function deleteIntakeForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'form_id' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $form_id=$request->form_id;
            $intakeForm=IntakeForm::where("id",$form_id)->first();
            IntakeForm::where("id", $form_id)->delete();
            IntakeFormFields::where("intake_form_id", $form_id)->delete();
            session(['popup_success' => 'Successfully deleted '.$intakeForm->form_name.'.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }    
    
    public function cloneIntakeForm(Request $request)
    {
       
        $formId=$request->form_id;
        $intakeForm=IntakeForm::where("id",$formId)->first();
        return view('intake_forms.cloneIntakeForm',compact('intakeForm','formId'));
    }
    public function cloneSaveIntakeForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'form_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $intakeFormExisting=IntakeForm::where("id",$request->form_id)->first();

            $IntakeForm=new IntakeForm;
            $IntakeForm->form_name=$request->form_name; 
            $IntakeForm->form_introduction=$intakeFormExisting['form_introduction'];
            $IntakeForm->form_unique_id=md5(time());
            $IntakeForm->background_color=$intakeFormExisting['background_color'];
            $IntakeForm->button_color=$intakeFormExisting['button_color'];
            $IntakeForm->form_font_color=$intakeFormExisting['form_font_color'];
            $IntakeForm->button_font_color=$intakeFormExisting['button_font_color'];
            $IntakeForm->form_font=$intakeFormExisting['form_font'];
            $IntakeForm->button_font=$intakeFormExisting['button_font'];
            $IntakeForm->firm_name=Auth::User()->firm_name;
            $IntakeForm->created_by =Auth::User()->id;
            $IntakeForm->save();

            $this->saveCloneAllFields($request->form_id,$IntakeForm->id);
            return response()->json(['errors'=>'']);
            exit;  
        }
    }
    public function saveCloneAllFields($form_id,$new_form_id)
    {
        $intakeFormFieldsExisting=IntakeFormFields::where("intake_form_id",$form_id)->get();
        foreach($intakeFormFieldsExisting as $k=>$v){
            $IntakeFormFields=new IntakeFormFields;
            $IntakeFormFields->intake_form_id=$new_form_id;

            $IntakeFormFields->form_category=$v['form_category'];
            $IntakeFormFields->form_field=$v['form_field'];
            $IntakeFormFields->client_friendly_lable=$v['client_friendly_lable'];
            $IntakeFormFields->is_required=$v['is_required'];
            $IntakeFormFields->header_text=$v['header_text'];
            $IntakeFormFields->is_required=$v['is_required'];
            $IntakeFormFields->sort_order=$v['sort_order'];
            $IntakeFormFields->created_by =Auth::User()->id;
            $IntakeFormFields->save();
        }
    }

    public function emailIntakeForm(Request $request)
    {
       
        $formId=$request->form_id;
        $intakeForm=IntakeForm::where("id",$formId)->first();
      
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        
        if(Auth::user()->parent_user==0){
            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("firm_name",Auth::user()->firm_name)->get();

        $firmData=Firm::find(Auth::User()->firm_name);

       
        return view('intake_forms.emailIntakeForm',compact('intakeForm','formId','caseLeadList','CaseMasterData','CaseMasterClient','firmData'));
    }

    public function loadClientCase(Request $request)
    {
        $id=$request->id;
        $CaseClientSelection=CaseClientSelection::select("case_id")->where("selected_user",$id)->pluck("case_id")->toArray();
        $caseList='';
        if(!empty($CaseClientSelection)){
            $caseList = CaseMaster::whereIn("case_master.id",$CaseClientSelection)->where('is_entry_done',"1")->get();
        
        }
        return view('intake_forms.loadCase',compact('caseList'));
    }
    public function loadLeadCase(Request $request)
    {
        $id=$request->id;
        $caseList=LeadAdditionalInfo::select("*","potential_case_title as case_title")->where("user_id",$id)->get();
        return view('intake_forms.loadCase',compact('caseList'));
    }
    public function sentEmailIntakeForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'contact_or_lead' => 'required',
            'case_id' => 'required',
            'email_address' => 'required|email'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $form_id=$request->form_id;
            $intakeForm=IntakeForm::where("id",$form_id)->first();
            
            $CaseIntakeForm = new CaseIntakeForm;
            $CaseIntakeForm->intake_form_id=$intakeForm['id']; 
            $CaseIntakeForm->form_unique_id=md5(time());
            $CaseIntakeForm->status="0";
            $CaseIntakeForm->submited_at=date('Y-m-d h:i:s');
            $CaseIntakeForm->firm_id=Auth::user()->firm_name;
            $CaseIntakeForm->submited_to=$request->email_address;
            if(isset($request->text_lead_id)){
                $CaseIntakeForm->lead_id=$request->text_lead_id;
            }
            if(isset($request->text_contact_id)){
                $CaseIntakeForm->client_id=$request->text_contact_id;
                $CaseIntakeForm->case_id=$request->case_id;
            }
            $CaseIntakeForm->unique_token=$this->generateUniqueToken();
            $CaseIntakeForm->created_by=Auth::user()->id; 
            $CaseIntakeForm->save();


            $getTemplateData = EmailTemplate::find(7);
            $fullName=$request->first_name. ' ' .$request->last_name;
            $email=$request->email;
            $token=url('form', $CaseIntakeForm->form_unique_id);
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

    public function dismissContactUs(Request $request)
    {
        $user = User::find(Auth::User()->id);
        $user->contact_us_widget='no';
        $user->save();
    }
}
