<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User,App\CaseStaff,App\CaseTaskLinkedStaff,App\UsersAdditionalInfo,App\CaseMaster,App\DeactivatedUser,App\Admin;
use DB, Validator, File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\Datatables\Datatables;

class UserController extends Controller {

    /**
     * Get admin dashboard
     */
    public function index(Request $request)
    {
        return view('admin_panel.users.index');
    }

    public function loadallstaffdata(Request $request)
    {
        $email = $request->email;
        $userData = $case = $userPermissions = $userProfile = [];
        $userProfiles = User::where('user_level','3')->where('email', $email)->with('firmDetail','caseStaff')->get();
        if(count($userProfiles) > 1){
            return view('admin_panel.staff.checkstaffList',compact('userProfiles'));        
        }else{            
            if(count($userProfiles) == 1){
                $userData = DB::select("select count(*) as staffCount, (select count(*) from case_master where firm_id = ".$userProfiles[0]->firm_name." and is_entry_done = '1' and deleted_at IS NULL) as firmCaseCount from users where firm_name = ".$userProfiles[0]->firm_name." and user_level in ('3','1')");
                $userPermissions = $userProfiles[0]->getPermissionNames()->toArray();
                $userProfile = $userProfiles[0];
            }
            // dd($userData);
            return view('admin_panel.staff.loadallstaffdata',compact('userProfile','userData','case','userPermissions'));            
        }
    }

    public function checkStaffDetails(Request $request)
    {
        $staff_id = $request->staff_id;
        $userData = $case = $userPermissions = [];
        $userProfile = User::where('user_level','3')->where('id', $staff_id)->with(['firmDetail','caseStaff','staffCases' => function($query) {
            $query->orWhereNotNull('case_master.case_close_date');
        }])->first();
        // select * from users where firm_name = 10 and user_level in ('3')
        // select * from case_master where created_by = 31
        // select * from case_master where created_by = 31
        if(!empty($userProfile)){
            $userData = DB::select("select count(*) as staffCount,(select count(*) from case_master where firm_id = ".$userProfile->firm_name." and is_entry_done = '1' and deleted_at IS NULL) as firmCaseCount from users where firm_name = ".$userProfile->firm_name." and user_level in ('3','1')");
            $userPermissions = $userProfile->getPermissionNames()->toArray();                
        }
        // dd($userData);
        return view('admin_panel.staff.loadallstaffdata',compact('userProfile','userData','userPermissions'));        
    }

    public function userList(Request $request)
    {
        return view('admin_panel.users.list');
    }

    public function staffList(Request $request)
    {
        return view('admin_panel.staff.list');
    }

    public function userInfo(Request $request){
        if(!isset($request->id)){
            redirect('/userlist');
        }else{
            $userProfile = User::find(base64_decode($request->id));
            if(!empty($userProfile) || $userProfile != null){
                $UsersAdditionalInfo =  UsersAdditionalInfo::where('user_id', $userProfile->id)->first();
                $userProfileCreatedBy = User::where("users.id",$userProfile->parent_user)->get();             
                return view('admin_panel.users.view', compact('userProfile','UsersAdditionalInfo','userProfileCreatedBy'));
            }else{
                redirect('/userlist');
            }
        }     
    }

    public function loadUsers(Request $request)
    {           
        if(isset($request->type) && $request->type != ''){
            $data = User::where('user_level',$request->type)->orderBy("created_at", "desc")->with('firmDetail');
        }else{
            $data = User::whereIn('user_level',['2','4'])->orderBy("created_at", "desc")->with('firmDetail');
        }
        
        return Datatables::of($data)
            ->addColumn('action', function ($data){
                $action = '<a class="name" href="'.route("admin/userlist/info", $data->decode_id).'">View</a>';
                return '<div class="text-center">'.$action.'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>';
            })
            ->addColumn('status', function ($data){
                switch ($data->user_status) {
                    case '1':
                        return 'Active';
                        break;
                    case '2':
                        return 'Inactive';
                        break;
                    case '3':
                        return 'Suspended';
                        break;
                    case '4':
                        return 'Archive';
                        break;
                    default:
                        # code... 1 : Active 2: Inactive 3:Suspended, 4:Archive
                        break;
                }
                return $data->user_status;
            })
            ->addColumn('type', function ($data){
                switch ($data->user_level) {
                    case '2':
                        return 'Contact';
                        break;
                    case '4':
                        return 'Company';
                        break;
                    case '5':
                        return 'Lead';
                        break;
                    default:
                        # code... 1 : Active 2: Inactive 3:Suspended, 4:Archive
                        break;
                }
                return $data->user_status;
            })
            ->addColumn('firmName', function ($data){
                return $data->firmDetail->firm_name;
            })            
            ->addColumn('created_at', function ($data){
                return date("Y-m-d H:i:s", strtotime($data->created_at));
            })            
            ->rawColumns(['action','status','type','firmName'])
            ->make(true);
    }

    public function staffInfo(Request $request){
        if(!isset($request->id)){
            redirect('/stafflist');
        }else{
            $userProfile = User::find(base64_decode($request->id));
            if(!empty($userProfile) || $userProfile != null){
                if(\Route::current()->getName() === 'admin/stafflist/cases')
                {
                    return view('admin_panel.staff.cases', compact('userProfile'));    
                }
                $userProfileCreatedBy = '';
                if($userProfile->parent_user > 0){
                    $userProfileCreatedBy = User::where("users.id",$userProfile->parent_user)->first();             
                }
                return view('admin_panel.staff.view', compact('userProfile','userProfileCreatedBy'));
            }else{
                redirect('/stafflist');
            }
        }     
    }

    public function loadStaff(Request $request)
    {   
        if(isset($request->type) && $request->type != ''){
            $data = User::whereIn('user_level',['3'])->where('user_type',$request->type)->orderBy("created_at", "desc")->with('firmDetail');
        }else{
            $data = User::whereIn('user_level',['3'])->orderBy("created_at", "desc")->with('firmDetail');
        }
        return Datatables::of($data)
            ->addColumn('action', function ($data){
                $action = '<a class="name" href="'.route("admin/stafflist/info", $data->decode_id).'">View</a>';
                return '<div class="text-center">'.$action.'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>';
            })    
            ->addColumn('firmName', function ($data){
                return $data->firmDetail->firm_name;
            }) 
            ->addColumn('type', function ($data){
                switch ($data->user_type) {
                    case '1':
                        return 'Attorney';
                        break;
                    case '2':
                        return 'Paralegal';
                        break;
                    case '3':
                        return 'Staff';
                        break;
                    default:
                        # code... 1:Attorney 2: Paralegal 3:Staff 4: None 5:Lead
                        break;
                }
                return $data->user_status;
            })
            ->rawColumns(['action','firmName','type'])
            ->make(true);
    }

    public function staffCaseList(Request $request)
    {   
        $case = CaseStaff::leftJoin('case_master','case_master.id',"=","case_staff.case_id")
        ->leftjoin("users","case_staff.user_id","=","users.id")
        ->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole",'case_staff.rate_amount',"case_staff.id as case_staff_id","case_staff.id as case_staff_id","users.default_rate as user_default_rate","case_staff.rate_type as case_staff_rate_type","users.user_title");
        $case = $case->where("case_staff.user_id",$request['staff_id']);
        // dd($case);
        return Datatables::of($case)
        ->addColumn('action', function ($case){
            $action = '';
            return '<div class="text-center">'.$action.'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>';
        })
        ->addColumn('case_name', function ($case){
            return $case->case_title;
        })
        ->addColumn('user_title', function ($case){
            return $case->user_title;
        })
        ->addColumn('status', function ($case){
            if($case->case_close_date==null){
                return '<div class="text-left">Active</div>';
            }else{
                return '<div class="text-left">Closed</div>';
            }
        })   
        ->addColumn('hourly_rate', function ($case){
            if($case->case_staff_rate_type=="1" && $case->rate_amount!=null){
                return '<div class="text-left">$<span class="amount">'.$case->rate_amount.'</span></div>';
            }else if($case->case_staff_rate_type=="0" && $case->user_default_rate!=null){
                return '<div class="text-left">$<span class="amount">'.$case->user_default_rate.'</span></div>';
            }else{
                return '<div class="text-left">Not Specified</div>';
            }  
        })          
        ->rawColumns(['action','case_name','user_title','status','hourly_rate'])
        ->make(true);
    }

    public function firmStaffList(Request $request){
        if(!isset($request->id)){
            redirect('/users');
        }else{
            $userProfile = User::find(base64_decode($request->id));
            if(!empty($userProfile) || $userProfile != null){
                return view('admin_panel.staff.stafflist', compact('userProfile'));
            }else{
                redirect('/users');
            }
        } 
    }

    public function loadFirmStaffList(Request $request)
    {   
        $user = User::select('*',DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as name'));
        $user = $user->where("firm_name",$request->firm_name); //Logged in user not visible in grid
        $user = $user->whereIn("user_level",['1','3']); //Show firm staff only
        $user = $user->with('caseStaff');
        
        // dd($case);
        return Datatables::of($user)
        ->addColumn('action', function ($user){
            if($user->user_status==1){
                $action='<button class="btn btn-outline-danger btn-rounded text-nowrap deactivate-user" staff_id="'.$user->id.'" type="button">De-activate</button>'
                .'<br><button class="btn btn-outline-primary btn-rounded text-nowrap">Entrar como usuario</button>';
            }else{
                $action='<button class="btn btn-outline-danger btn-rounded text-nowrap reactivate-user" staff_id="'.$user->id.'" type="button">Reactivate</button>';
            }
            return '<div class="text-center">'.$action.'</div>';
        })
        ->addColumn('fullname', function ($user){
            return $user->fullname;
        })
        ->addColumn('default_rate', function ($user){
            return $user->default_rate === null ? '$0.00' : '$'.$user->default_rate;
        })
        ->addColumn('active_cases', function ($user){
            return count($user->caseStaff);
        })
        ->addColumn('user_status', function ($user){
            if($user->user_status==1){
                return 'Active';
            }else{
                return 'In-active';
            }
        })      
        ->rawColumns(['action','fullname','default_rate','status','active_cases'])
        ->make(true);
    }
    
    public function reactivateStaff(Request $request){  
        $user_id = $request->user_id;
        $user =User::find($user_id);
        $user->user_status="1";
        $user->save();
        // assing all case to current staff
        $userDeactivate = DeactivatedUser::where('user_id', $user_id)->withTrashed()->first();
        if(!empty($userDeactivate)){
            // CaseStaff::where('user_id',$user_id)->update(['deleted_at'=> null]);
            // CaseTaskLinkedStaff::where('user_id',$user_id)->update(['deleted_at'=> null]);
            $userDeactivate->delete();
        }
        session(['popup_success' => 'User has been re-activated.']);            
        return response()->json(['errors'=>'']);
        exit;        
    } 

    public function loadDeactivateUser(Request $request){ 
        $contractUserID=$request->user_id;
        $user = User::select("users.*")->where("users.id",$contractUserID)->first();
        // $allUser = User::select("*")->where("users.parent_user",$user->parent_user)->where("users.id","!=",$user->id)->get();

        $allUser = User::select('*');
        $allUser = $allUser->where("firm_name",$user->firm_name); //Logged in user not visible in grid
        $allUser = $allUser->whereIn("user_level",['1','3']); //Show firm staff only
        $allUser = $allUser->where("user_status",1); // Check user is deactivated or not
        $allUser = $allUser->where("users.id","!=",$user->id);
        $allUser = $allUser->get();
        return view('admin_panel.staff.loadDeactivateUser',compact('user','allUser'));
    }

    public function deactivateStaff(Request $request){        
        $user_id = $request->user_id;
        $user =User::find($user_id);
        $user->user_status="3";
        $user->save();

        $userDeactivate =new DeactivatedUser;
        $userDeactivate->user_id= $request->user_id;
        if(isset($request->reason)) { $userDeactivate->reason=$request->reason; }
        if(isset($request->other_reason)) { $userDeactivate->other_reason=$request->other_reason; }
        if(isset($request->assign_to)) { $userDeactivate->assigned_to=$request->assign_to; }
        $userDeactivate->created_by = $user->firm_name;
        $userDeactivate->save();

        // assing all case to new staff
        if(isset($request->assign_to)) {
            $caseStaffData =  CaseStaff::where('user_id',$request->user_id)->get();
            if(count($caseStaffData) > 0){
                foreach($caseStaffData as $k =>$v){
                    CaseStaff::updateOrCreate(['case_id' => $v->case_id, 'user_id' => $request->user_id], ['case_id' => $v->case_id, 'user_id' => $request->assign_to]);
                }
            }
            $CaseTaskLinkedStaffData =  CaseTaskLinkedStaff::where('user_id',$request->user_id)->get();
            if(count($CaseTaskLinkedStaffData) > 0){
                foreach($CaseTaskLinkedStaffData as $k =>$v){
                    CaseTaskLinkedStaff::updateOrCreate(['task_id' => $v->task_id, 'user_id' => $request->user_id], ['task_id' => $v->task_id, 'user_id' => $request->assign_to]);
                }
            }
                
            // CaseStaff::where('user_id',$request->user_id)->delete();
            // CaseTaskLinkedStaff::where('user_id',$request->user_id)->delete();
        }
        session(['popup_success' => 'User has been de-activated.']);            
        return response()->json(['errors'=>'']);
        exit;        
    } 

    public function loadProfile(){
        $userProfile = Admin::find(Auth::User()->id);
        return view('admin_panel.profile.view', compact('userProfile'));
    }    

    public function saveProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:1|max:255',
            'last_name' => 'required|min:1|max:255',
        ]);
        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
            $isSuccess = false;
            return redirect()->back()->withErrors($validator)->withInput();
        }else{
            $user = Admin::find(Auth::User()->id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->timezone = $request->timezone;
            $user->save();
            return redirect()->route('admin/loadProfile')->with('success','Profile has been updated successfully.');
        }
    }


    public function savePassword(Request $request)
    {
        $id=Auth::user()->id;
        $input = $request->all();
        $user = Admin::find($id);
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
                return redirect()->route('admin/loadProfile')->with('success',SUCCESS_SAVE_PROFILE);
            }else{
                $request->session()->flash('page', 'password');
                return redirect()->back()->withErrors(ERROR_INCORRECT_PASSWORD)->withInput();
            }
        
        }
    }

    public function exportAllStaff(){
        $fileDestination = 'export/'.date('Y-m-d');
        $folderPath = public_path($fileDestination);

        File::deleteDirectory($folderPath);
        if(!is_dir($folderPath)) {
            File::makeDirectory($folderPath, $mode = 0777, true, true);
        }    
        
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        
        $user = User::select("users.id","users.email","users.firm_name","users.created_at",DB::raw('(select count(*) from case_master where firm_id = users.firm_name and is_entry_done = "1" and deleted_at IS NULL) as firmCaseCount'));
        $user = $user->whereIn("user_level",['1','3']); //Show firm staff only
        $user = $user->groupBy('users.id','users.firm_name');
        $user = $user->with('firmDetail','caseStaff');
        $user = $user->get();
        
        // dd($user);

        $casesCsvData[]="Email|Sign up|Staff Cases|Firm Name|Firm's cases";
                
        foreach($user as $k =>$v){
            $casesCsvData[]=$v->email."|".$v->created_date_new."|".count($v->caseStaff)."|".(($v->firmDetail != null) ? $v->firmDetail->firm_name : '')."|".$v->firmCaseCount;
        }

        $file_path =  $folderPath.'/admin_allstaff_reports.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
        }   
        fclose($file); 

        $Path= asset($fileDestination.'/admin_allstaff_reports.csv');

        return response()->json(['errors'=>'','url'=>$Path,'msg'=>"Building File... it will downloading automaticaly"]);
        exit;
    }
}