<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User,App\CaseStaff,App\UsersAdditionalInfo,App\CaseMaster;
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
            $userData = DB::select("select count(*) as staffCount, (select count(*) from case_master where created_by = ".$userProfile->parent_user.") as firmCaseCount from users where firm_name = 10 and user_level in ('3')");
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
                $action = '<a class="name" href="'.route("admin/userlist/info", $data->decode_id).'">View</a> | <a class="name" href="#">Login</a>';
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
}