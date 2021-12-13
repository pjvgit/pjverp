<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use DB;
class UserController extends Controller {

    /**
     * Get admin dashboard
     */
    public function index(Request $request)
    {
        return view('admin_panel.users.list');
    }

    public function userInfo(Request $request){
        return view('admin_panel.users.view');
    }
}