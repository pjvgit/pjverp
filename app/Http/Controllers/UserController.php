<?php

namespace App\Http\Controllers;

use App\User,App\EmailTemplate,App\PlanHistory,App\Countries;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Firm,App\CaseStage,App\CasePracticeArea;
use Carbon\Carbon;
use App\UserPreferanceReminder;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    public function __construct()
    {
        // $this->middleware("auth");
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }
    protected function authenticated($request, $user) { auth()->logoutOtherDevices(request('password')); }


    public function login(Request $request)
    {
        $input=$request->all();
        $validator = Validator::make($input, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
        	$isSuccess = false;
            return redirect('login')->withErrors($validator)->withInput();
        }else{
            $email=$request->email;
            $password=$request->password;
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                Auth::logoutOtherDevices($password);

                $userStatus = Auth::User()->user_status;
                $user = User::find(Auth::User()->id);
                $user->last_login=date('Y-m-d h:i:s');
                $user->save();
                if($userStatus=='1') { //User status active then able to login
                    session(['layout' => 'horizontal']);
                    $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                    $user->save();
                    return redirect()->intended('dashboard')->with('success','Login Successfully');
                }else{
                    Auth::logout();
                    Session::flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
            }else{
                return redirect('login')->with('error', ERROR_LOGIN_MESSAGE)->withInput();
            }
        }
    }
    //Singup user (Create fresh account)
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,NULL,id,deleted_at,NULL',
            'mobile_number' => 'required',
            'firm_name' => 'required|unique:firm,firm_name,NULL,id,deleted_at,NULL'
        ]);
        $user = new User;
        $user->first_name=$request->first_name;
        $user->last_name=$request->last_name;
        $user->email=$request->email;
        $user->mobile_number=$request->mobile_number;
        $user->employee_no=$request->employee_no;
        $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
        $user->password='';
        $user->firm_name=$request->firm_name;
        $user->token  = str_random(40);
        $user->user_title='Attorney';
        $user->user_timezone='';
        $user->parent_user ="0";
        $user->save();

        $firm =  new Firm;
        $firm->parent_user_id=$user->id;
        $firm->firm_name=$request->firm_name;
        $firm->save();
        
        $user->firm_name=$firm->id;
        $user->save();

        // //Create default plan for the user type is client or end user
        // $start_date = date('Y-m-d h:i:s');
        // $end_date= date('Y-m-d h:i:s',strtotime("+1 week", strtotime($start_date)));
        // $userPlan =  PlanHistory::firstOrNew(["user_id" => $user->id]);
        // $userPlan->user_id=$user->id;
        // $userPlan->start_date=$start_date;
        // $userPlan->end_date=$end_date;
        // $userPlan->plan_type='0';
        // $userPlan->status='1';
        // $userPlan->save();


        $getTemplateData = EmailTemplate::find(5);
        $fullName=$request->first_name. ' ' .$request->last_name;
        $email=$request->email;
        $token=url('user/verify', $user->token);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{name}', $fullName, $mail_body);
        $mail_body = str_replace('{email}', $email,$mail_body);
        $mail_body = str_replace('{token}', $token,$mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        


        $user = [
            "from" => FROM_EMAIL,
            "from_title" => FROM_EMAIL_TITLE,
            "subject" => $getTemplateData->subject,
            "to" => $request->email,
            "full_name" => $fullName,
            "mail_body" => $mail_body
            ];
        $sendEmail = $this->sendMail($user);
        return redirect('/login')->with('status', SENT_LINK);
    }

    //Verify user once click on link shared by email.
    public function verifyUser($token)
    {
        $verifyUser = User::where('token', $token)->first();
        
        if(isset($verifyUser) ){
            if($verifyUser->user_status==1){
                return redirect('login')->with('warning', EMAIL_ALREADY_VERIFIED);
            }else{
                //Insert default case stage 
                $data = array(
                    array("stage_order"=>"1","title"=>"Discovery","created_by"=>$verifyUser->id,'stage_color'=>'#661051'),
                    array("stage_order"=>"2","title"=>"In Trial","created_by"=>$verifyUser->id,'stage_color'=>'#BFD8E1'),
                    array("stage_order"=>"3","title"=>"On Hold","created_by"=>$verifyUser->id,'stage_color'=>'#69B6D6')
                );
                CaseStage::insert($data);

                $case_practice_area = array(
                    array('title' => 'Bankruptcy','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Business','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Civil','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Criminal Defense','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Divorce/Separation','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'DUI/DWI','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Employment','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Estate Planning','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Family','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Foreclosure','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Immigration','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Landlord/Tenant','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Personal Injury','status' => '1',"created_by"=>$verifyUser->id),
                    array('title' => 'Real Estate','status' => '1',"created_by"=>$verifyUser->id)
                  );
                  CasePracticeArea::insert($case_practice_area);
                $status = EMAIL_VERIFIED;
                return redirect('setupprofile/'.$token);
            }
        }else{
            return redirect('login')->with('warning', EMAIL_NOT_IDENTIFIED);
        }
    }
   //Forgot password page shown
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
    //Submit form and sent reset password link via email
    public function sendResetLinkEmail(Request $request)
    {
        //Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
        	$isSuccess = false;
            return redirect('password/reset')->withErrors($validator)->withInput();
        }else{

            $user = DB::table('users')->where('email', '=', $request->email)->first();//Check if the user exists
            if (empty($user)) {
                return redirect()->back()->withErrors(['email' => trans(ERROR_USER_NOTEXIST)])->withInput();
            }
            //Create Password Reset Token
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => str_random(60),
                'created_at' => date('Y-m-d h:i:s')
            ]);
            //Get the token just created above
            $tokenData = DB::table('password_resets')->where('email', $request->email)->first();
            if ($this->sendResetEmail($request->email, $tokenData->token)) {
                return redirect()->back()->with('status', trans(SUCCESS_EMAIL_SENT));
            } else {
                return redirect()->back()->with(['warning' => trans(ERROR_NETWORK_ERROR)]);
            }
        }
    }
    //Sent email to use with reset passoword link.[Child function]
    private function sendResetEmail($email, $token)
    {
        //Retrieve the user from the database
        $user = DB::table('users')->where('email', $email)->select('first_name', 'last_name','email')->first();//Generate, the password reset link. The token generated is embedded in the link
        $link =  $token ;
        try {
          
             //Sent email to user with reset link.
             $getTemplateData = EmailTemplate::find(1);
             $changePwdUrl = route('password.reset.token',['token' => $token]);          
             $fullName = $user->first_name . ' ' . $user->last_name;

             $mail_body = $getTemplateData->content;
             $mail_body = str_replace('{name}', $fullName, $mail_body);
             $mail_body = str_replace('{1}', $changePwdUrl, $mail_body);
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

            return true;
        } catch (\Exception $e) {
            echo $e;
            exit;
            return false;
        }
    }
    
    public function resetPassword(Request $request)
    {
        //Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:6',
            'token' => 'required' ]);

        //check if payload is valid before moving on
        if ($validator->fails()) {
            $errors = $validator->errors();
        	$code = 404;
        	$isSuccess = false;
            return redirect()->back()->withErrors($validator)->withInput();
        }else{

            $password = $request->password;// Validate the token
            $tokenData = DB::table('password_resets')
            ->where('token', $request->token)->first();// Redirect the user back to the password reset request form if the token is invalid
            if (!$tokenData) return view('auth.passwords.email');

            $user = User::where('email', $tokenData->email)->first();
        // Redirect the user back if the email is invalid
            if (!$user) return redirect()->back()->withErrors(['email' => ERROR_EMAIL_NOTFOUND]);//Hash and update the new password
            $user->password = \Hash::make($password);
            $user->update(); //or $user->save();

            //Delete the token
            DB::table('password_resets')->where('email', $user->email)->delete();

            //Send Email Reset Success Email
            if ($this->sendSuccessEmail($tokenData->email)) {
                return redirect('login')->with('status',SUCCESS_PASSWORD_CHANGE);
            } else {
                return redirect()->back()->withErrors(['email' => trans(ERROR_NETWORK_ERROR)]);
            }
        }

    }
    //Once password reset notify to user via email
    private function sendSuccessEmail($email)
    {
        //Retrieve the user from the database
        $user = DB::table('users')->where('email', $email)->select('first_name','last_name' ,'email')->first();//Generate, the password reset link. The token generated is embedded in the link
        try {
             //Sent email to user with reset link.
             $getTemplateData = EmailTemplate::find(2);
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

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function showPasswordRestPage(Request $request)
    {
        $token=$request->token;
        $tokenData = DB::table('password_resets')->where('token', $request->token)->first();
        // Redirect the user back to the password reset request form if the token is invalid
       if(empty($tokenData)){
            return redirect('password/reset')->with('error',ERROR_INVALID_TOKEN);
       }else{
          return view('auth.passwords.reset',['token'=>$token,'email'=>$tokenData->email]);
       }
    }

    public function profile_load()
    {
        $id=Auth::user()->id;
        $user = User::find($id);
        $country = Countries::get();
        if(!empty($user)){
            return view('user.profile', compact('user','country'));
        }else{
            return redirect('login');
        }
    }
    public function preferences()
    {
        $id=Auth::user()->id;
        $user = User::find($id);
        $country = Countries::get();
        $UserPreferanceEventReminder=UserPreferanceReminder::where("user_id",Auth::User()->id)->where("type","event")->get();
        $UserPreferanceTaskReminder=UserPreferanceReminder::where("user_id",Auth::User()->id)->where("type","task")->get();
        if(!empty($user)){
            return view('user.preferences', compact('user','country','UserPreferanceEventReminder','UserPreferanceTaskReminder'));
        }else{
            return redirect('login');
        }
    }
    public function savePreferences(Request $request)
    {
        $id=Auth::user()->id;
        $user = User::find($id);
        $user->user_timezone=trim($request->timeZone); 
        if(isset($request->auto_logout_enabled) && $request->auto_logout_enabled=="true"){
            $user->auto_logout="on"; 
            $user->sessionTime=$request->logout_minutes;
        }else{
            $user->auto_logout="off"; 
            $user->sessionTime=NULL;
        }
        if(isset($request->timer_running_logout) && $request->timer_running_logout==1){
            $user->dont_logout_while_timer_runnig="on"; 
        }else{
            $user->dont_logout_while_timer_runnig="off"; 

        }
        if(isset($request->tooltips) && $request->tooltips=="on"){
            $user->started_tips="on"; 
        }else{
            $user->started_tips="off";
        }
        $user->save();


        UserPreferanceReminder::where("user_id",Auth::User()->id)->where("type","event")->delete();
        for($i=0;$i<count($request['event_reminder_type'])-1;$i++){
            $UserPreferanceReminder = new UserPreferanceReminder;
            $UserPreferanceReminder->user_id=Auth::User()->id; 
            $UserPreferanceReminder->reminder_type=$request['event_reminder_type'][$i];
            $UserPreferanceReminder->reminer_number=$request['event_reminder_number'][$i];
            $UserPreferanceReminder->reminder_frequncy=$request['event_reminder_time_unit'][$i];
            $UserPreferanceReminder->type="event";
            $UserPreferanceReminder->created_by=Auth::user()->id; 
            $UserPreferanceReminder->save();
        }

        UserPreferanceReminder::where("user_id",Auth::User()->id)->where("type","task")->delete();
        for($i=0;$i<count($request['task_reminder_type'])-1;$i++){
            $UserPreferanceReminder = new UserPreferanceReminder;
            $UserPreferanceReminder->user_id=Auth::User()->id; 
            $UserPreferanceReminder->reminder_type=$request['task_reminder_type'][$i];
            $UserPreferanceReminder->reminer_number=$request['task_reminder_number'][$i];
            $UserPreferanceReminder->reminder_frequncy=$request['task_reminder_time_unit'][$i];
            $UserPreferanceReminder->type="task";
            $UserPreferanceReminder->created_by=Auth::user()->id; 
            $UserPreferanceReminder->save();
        }
        return redirect()->route('account/preferences')->with('success','Preferences updated');
   
    }
    public function saveBasicInfo(Request $request)
    {
        $id=Auth::user()->id;
        $input = $request->all();
        $user = User::find($id);
        $validator = Validator::make($input, [
            'first_name' => 'required|min:1|max:255',
            'last_name' => 'required|min:1|max:255',
            'home_phone'=>'nullable|numeric',
            'work_phone'=>'nullable|numeric',
            'cell_phone'=>'nullable|numeric',
        ]);

        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
            $isSuccess = false;
            $request->session()->flash('page', 'infopage');
             return redirect()->back()->withErrors($validator)->withInput();
        }else{
            if(isset($request->first_name)){ $user->first_name=trim($request->first_name); }
            if(isset($request->middle_name)){ $user->middle_name=trim($request->middle_name); }
            if(isset($request->last_name)){ $user->last_name=trim($request->last_name); }
            if(isset($request->street)) { $user->street=trim($request->street); }else{ $user->street=NULL;}
            if(isset($request->apt_unit)) { $user->apt_unit=trim($request->apt_unit); }else{ $user->apt_unit=NULL;}
            if(isset($request->city)) { $user->city=trim($request->city); }else{ $user->city=NULL;}
            if(isset($request->state)) { $user->state=trim($request->state); }else{ $user->state=NULL;}
            if(isset($request->postal_code)) { $user->postal_code=trim($request->postal_code); }else{ $user->postal_code=NULL;}
            if(isset($request->country)) { $user->country=$request->country; }else{ $user->country=NULL;}
            if(isset($request->home_phone)) { $user->home_phone=trim($request->home_phone); }else{ $user->home_phone=NULL;}
            if(isset($request->work_phone)) { $user->work_phone=trim($request->work_phone); }else{ $user->work_phone=NULL;}
            if(isset($request->cell_phone)) { $user->mobile_number=trim($request->cell_phone); }else{ $user->mobile_number=NULL;}
            $user->save();
            return redirect()->route('load_profile')->with('success',SUCCESS_SAVE_PROFILE);
   
        }
    }
    public function saveEmail(Request $request)
    {
        $id=Auth::user()->id;
        $input = $request->all();
        $user = User::find($id);
        $validator = Validator::make($input, [
            'email' => 'required|email|unique:users,email,'.$id,
            'current_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
            $isSuccess = false;
            $request->session()->flash('page', 'email');
             return redirect()->back()->withErrors($validator)->withInput();
        }else{
          
            if($user->email==$input['email']){
                $request->session()->flash('page', 'email');
                return redirect()->back()->withErrors(ERROR_SAME_EMAIL)->withInput();
            }else{
                if (Auth::attempt(array('email' => $user->email, 'password' => $input['current_password']))){
                    if(isset($request->email)){ $user->email=trim($request->email); }
                    $user->save();
                    return redirect()->route('load_profile')->with('success',SUCCESS_SAVE_PROFILE);
                }else{
                    $request->session()->flash('page', 'email');
                    return redirect()->back()->withErrors('Password is incorrect')->withInput();
                }
            }
        }
    }
    public function savePassword(Request $request)
    {
        $id=Auth::user()->id;
        $input = $request->all();
        $user = User::find($id);
        $validator = Validator::make($input, [
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
            $isSuccess = false;
            $request->session()->flash('page', 'password');
             return redirect()->back()->withErrors($validator)->withInput();
        }else{
            if (Auth::attempt(array('email' => $user->email, 'password' => $input['current_password']))){
                if(isset($request->current_password)){ $user->password=\Hash::make($input['confirm_password']); }
                $user->save();
                return redirect()->route('load_profile')->with('success',SUCCESS_SAVE_PROFILE);
            }else{
                $request->session()->flash('page', 'password');
                return redirect()->back()->withErrors(ERROR_INCORRECT_PASSWORD)->withInput();
            }
        
        }
    }
    public function saveProfileimage(Request $request)
    {
        $id=Auth::user()->id;
        $input = $request->all();
        $user = User::find($id);
        $validator = Validator::make($input, [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ],[
            'profile_image.required' => 'Please select a file to upload',
        ]);
        
        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
            $isSuccess = false;
            $request->session()->flash('page', 'image');

             return redirect()->back()->withErrors($validator)->withInput();
        }else{
           
            if ($request->hasFile('profile_image')) {
              
                $destinationPath = public_path('/images/users/');
               
                if(isset($user->profile_image) && $user->profile_image){
                    $storeImageFullPath=$destinationPath . '/' . $user->profile_image;
                    // unlink($storeImageFullPath);
                }
               
                $image = $request->file('profile_image');
                // print_r($request->all());exit;
                 $image_name = Str::slug($user->id)."_".date('Ymdhis').'.'.$image->getClientOriginalExtension();
            
                $resize_image = Image::make($image->getRealPath())->save($destinationPath . '/' . $image_name);
                // $resize_image->resize(160, 160, function($constraint){
                // })->save($destinationPath . '/' . $image_name);
                $user->profile_image = $image_name;
                $user->is_published="no";
                $user->save();
            }

        //     if ($request->hasFile('profile_image')) {
        //         if(isset($user->profile_image) && $user->profile_image){
        //             $info = pathinfo($user->profile_image);
        //             $this->removeImage(USER_IMAGE_FOLDER_PATH . $info['basename']);
        //             $this->removeImage(USER_IMAGE_FOLDER_PATH ."thumb/". $info['basename']);
        //         }

        //         $imgTime=date('Ymdhis');
        //         $saveImageName =str_slug($user->id).'_' .$imgTime.'.'.$request->profile_image->getClientOriginalExtension();
        //         $imageName = USER_IMAGE_FOLDER_PATH . str_slug($user->id).'_' .$imgTime.'.'.$request->profile_image->getClientOriginalExtension();
        //         $image = $request->file('profile_image');
        //         $imageUrl = $this->uploadImage($imageName, $image);
        //         $user->profile_image = $saveImageName;
        //         $user->save();


        //         //Store image in localy
        //         $image_name = str_slug($user->id)."_".$imgTime.'.'.$image->getClientOriginalExtension();
        //         $destinationPath = public_path('/images/users');
        //         $resize_image = Image::make($image->getRealPath());
        //         $resize_image->resize(215, 215, function($constraint){
        //       //  $constraint->aspectRatio();
        //         })->save($destinationPath . '/' . $image_name);

        //         $storeImageFullPath=$destinationPath . '/' . $image_name;
        //         $imageNameThumb = USER_IMAGE_FOLDER_PATH .'thumb/'. str_slug($user->id).'_' .$imgTime.'.'.$request->profile_image->getClientOriginalExtension();
        //         $imageUrl = $this->uploadImage($imageNameThumb, $storeImageFullPath);
        //         unlink($storeImageFullPath);

        // }
        return redirect()->route('load_profile')->with('success');
           
        
        }
    }
    public function removeProfileImage(Request $request)
    {
       // print_r($request->all());exit;
       $User= User::find(Auth::User()->id);
       
       $User->profile_image=NULL;
       $User->is_published="no";
       $User->save();
       return response()->json(['errors'=>'','contact_id'=>""]);
       exit;
    }
    public function saveCropedProfileimage(Request $request)
    {
        // print_r($request->all());exit;
        $User= User::find(Auth::User()->id);
        $path = BASE_URL."images/users/".$User->profile_image;
        if(file_exists($path)){
            unlink($path);
        }
        $image = $request->imageCode;
        $img = explode(',', $request->imageCode);
        $ini =substr($img[0], 11);
        $type = explode(';', $ini);
        $img = str_replace('data:image/'.$type[0].';base64,', '', $image);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file = Auth::User()->id."_profile" . "." . $type[0];
        $destinationPath = public_path('/images/users/'); 
        $success = file_put_contents($destinationPath."/".$file, $data);

        $User->profile_image=$file;
        $User->is_published="yes";
        $User->save();
        return redirect()->route('load_profile')->with('success',SUCCESS_SAVE_PROFILE);

        exit;
    }
    public function email_template(Request $request)
     {
        echo "<hr>";
        echo "<h3 style='text-align: center;'>Rest Password OTP</h3>";
        $getTemplateData = EmailTemplate::find(3);
        $fullName = $request->first_name . ' ' . $request->last_name;
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{0}', $fullName, $mail_body);
        $mail_body = str_replace('{1}', "######", $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);        

        echo  $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);


        echo "<hr>";
        echo "<h3 style='ext-align: center;'>Account Activate</h3>";
        $getTemplateData = EmailTemplate::find(5);
        $fullName = "Divyesh" . ' ' . "Patoriya";
        $email="test@mail.com";
        $token=url('user/verify', str_random(40));
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{name}', $fullName, $mail_body);
        $mail_body = str_replace('{email}', $email,$mail_body);
        $mail_body = str_replace('{token}', $token,$mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);        
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

        echo "<hr>";
        echo "<h3 style='text-align: center;'>Forgot Password</h3>";
        $token=str_random(40);
        $changePwdUrl = route('password.reset.token',['token' => $token]);
        $getTemplateData = EmailTemplate::find(1);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{name}', $fullName, $mail_body);
        $mail_body = str_replace('{1}', $changePwdUrl, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);


        echo "<hr>";
        echo "<h3 style='text-align: center;'>Password Chagned</h3>";
        $getTemplateData = EmailTemplate::find(2);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{name}', $fullName, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

        echo "<hr>";
        echo "<h3 style='text-align: center;'>Welcome User</h3>";
        $getTemplateData = EmailTemplate::find(4);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{name}', $fullName, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

        echo "<hr>";
        echo "<h3 style='text-align: center;'> Invited User to Join Legalcase</h3>";
        $getTemplateData = EmailTemplate::find(6);
        $token=url('user/verify', str_random(40));
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{name}', $fullName, $mail_body);
        $mail_body = str_replace('{email}', $email,$mail_body);
        $mail_body = str_replace('{token}', $token,$mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
        $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
        $mail_body = str_replace('{refuser}', TITLE, $mail_body);                          
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);


        echo "<hr>";
        echo "<h3 style='text-align: center;'> Intake Form</h3>";
        $getTemplateData = EmailTemplate::find(7);
        $token=url('user/verify', str_random(40));
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{message}', $fullName, $mail_body);
        $mail_body = str_replace('{email}', $email,$mail_body);
        $mail_body = str_replace('{token}', $token,$mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
        $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
        $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
        $mail_body = str_replace('{refuser}', TITLE, $mail_body);                          
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

        echo "<hr>";
        echo "<h3 style='text-align: center;'> Request fund</h3>";
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(10);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{message}', "Legalcase is all-in-one legal practice management software for case and matter management. ", $mail_body);
        $mail_body = str_replace('{amount}', "11.00", $mail_body);
        $mail_body = str_replace('{duedate}', date('m/d/Y'), $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

        echo "<hr>";
        echo "<h3 style='text-align: center;'> Send Message</h3>";
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(11);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{sender}', "Legalcase", $mail_body);
        $mail_body = str_replace('{subject}', "subject", $mail_body);
        $mail_body = str_replace('{loginurl}', BASE_URL, $mail_body);
        $mail_body = str_replace('{url}', BASE_URL, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

        echo "<hr>";
        echo "<h3 style='text-align: center;'> Intake Form Submit</h3>";
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(19);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{sender}', "Legalcase", $mail_body);
        $mail_body = str_replace('{subject}', "subject", $mail_body);
        $mail_body = str_replace('{receiver}', "DES", $mail_body);
        $mail_body = str_replace('{url}', BASE_URL, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

         $user = [
            "from" => FROM_EMAIL,
            "from_title" => FROM_EMAIL_TITLE,
            "subject" => $getTemplateData->subject,
            "to" => "divyesh.patoriya@plutustec.com",
            "full_name" => $fullName,
            "mail_body" => $mail_body
            ];
        // $sendEmail = $this->sendMail($user);
        // if($sendEmail==1){ 
        //     echo "Test Email Sent ";
        // }
    }

    public function testmail(Request $request)
     {
        echo "<h3 style='text-align: center;'> Send Message</h3>";
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(11);
        $mail_body = $getTemplateData->content;
        $mail_body = str_replace('{sender}', "Legalcase", $mail_body);
        $mail_body = str_replace('{subject}', "subject", $mail_body);
        $mail_body = str_replace('{loginurl}', BASE_URL, $mail_body);
        $mail_body = str_replace('{url}', BASE_URL, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        
        echo $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);

         $user = [
            "from" => FROM_EMAIL,
            "from_title" => FROM_EMAIL_TITLE,
            "subject" => $getTemplateData->subject,
            "to" => "testing.testuser6@gmail.com",
            "full_name" => "Divyesh",
            "mail_body" => $mail_body
            ];
      echo  $sendEmail = $this->sendMail($user);
        
    }
     //open set password popup when verify email
     public function setupprofile($token)
     {
        $verifyUser = User::where('token', $token)->first();
        return view('auth.setupprofile',['verifyUser'=>$verifyUser]);

     }   
     
     //open set password popup when verify email
     public function setupsave(Request $request)
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
     public function autoLogout(Request $request) {
        Auth::logout();
        return redirect('/login');
      }
}
