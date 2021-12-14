<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
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
}