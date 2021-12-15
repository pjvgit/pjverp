<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User,App\CaseStaff;
use DB;
use Yajra\Datatables\Datatables;

class UserController extends Controller {

    /**
     * Get admin dashboard
     */
    public function index(Request $request)
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
                return view('admin_panel.users.view', compact('userProfile'));
            }else{
                redirect('/userlist');
            }
        }     
    }

    public function loadUsers(Request $request)
    {           
        $data = User::whereIn('user_level',['2','4'])->orderBy("created_at", "desc")->with('firmDetail');
        return Datatables::of($data)
            ->addColumn('action', function ($data){
                $action = '<a class="name" href="'.route("admin/userinfo", $data->decode_id).'">View</a>';
                return '<div class="text-center">'.$action.'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>';
            })
            ->rawColumns(['action'])
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
                return view('admin_panel.staff.view', compact('userProfile'));
            }else{
                redirect('/stafflist');
            }
        }     
    }

    public function loadStaff(Request $request)
    {           
        $data = User::whereIn('user_level',['3'])->orderBy("created_at", "desc")->with('firmDetail');
        return Datatables::of($data)
            ->addColumn('action', function ($data){
                $action = '<a class="name" href="'.route("admin/stafflist/info", $data->decode_id).'">View</a>';
                return '<div class="text-center">'.$action.'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>';
            })
            ->rawColumns(['action'])
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