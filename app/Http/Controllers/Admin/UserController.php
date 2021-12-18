<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User,App\CaseStaff,App\CaseTaskLinkedStaff,App\UsersAdditionalInfo,App\CaseMaster,App\DeactivatedUser;
use DB;
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
        $userData = $case = [];
        $userProfile = User::where('user_level','3')->where('email', $email)->with('firmDetail')->first();
        // select * from users where firm_name = 10 and user_level in ('3')
        // select * from case_master where created_by = 31
        // select * from case_master where created_by = 31
        if(!empty($userProfile)){
            $userData = DB::select("select count(*) as staffCount, (select count(*) from case_master where created_by = ".$userProfile->parent_user.") as firmCaseCount from users where firm_name = ".$userProfile->firm_name." and user_level in ('3','1')");
            $case = CaseMaster::join("users","case_master.created_by","=","users.id")->where('firm_id',$userProfile->firm_name)->where("case_master.is_entry_done","1")->count();
        }
        // dd($userData);
        return view('admin_panel.staff.loadallstaffdata',compact('userProfile','userData','case'));        
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
        ->addColumn('user_status', function ($user){
            if($user->user_status==1){
                return 'Active';
            }else{
                return 'In-active';
            }
        })      
        ->rawColumns(['action','fullname','default_rate','status'])
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



    
}