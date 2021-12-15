<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use DB;
class DashboardController extends Controller {

    /**
     * Get admin dashboard
     */
    public function index(Request $request)
    {
        if(isset($_GET['date_range']) && $_GET['date_range']!=""){
            $dates=explode("-",$_GET['date_range']);
            $start_date = date('Y-m-d',strtotime($dates[0]));
            $end_date = date('Y-m-d',strtotime($dates[1]));
            $date_range = $request->date_range ?? '';
        }else{
            $start_date = date('Y-m-1');
            $end_date = date('Y-m-t');
            $date_range = date('m/1/Y').' - '.date('m/t/Y');
        }       
        
        $signupChartUsers = [];
        $signupUsers = 0;
        
        $signupUsersData = User::where('user_level', '3')
            ->whereBetween('created_at',[$start_date, $end_date])->select(
            DB::raw("count(*) as total_users"),
            DB::raw("(DATE_FORMAT(created_at, '%d/%m/%Y')) as created_date")
            )
            ->orderBy('created_at')
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"))
            ->get();
        //SELECT * FROM `users` WHERE `user_level` = '3' AND `created_at` >= '2021-12-01' AND `created_at` <= '2021-12-15' LIMIT 50
        foreach ($signupUsersData as $k => $v) {
            $signupUsers = $signupUsers + $v->total_users;
            $signupChartUsers[(string) $v->created_date] = $v->total_users;  
        }
        return view('admin_panel.dashboard', compact('signupUsers','signupChartUsers','date_range'));
    }

    function searchUsers(Request $request){
        $arrData = User::where('email','LIKE', "%".$request->st."%")->where('user_level', '3')->get();
        return json_encode($arrData);
    }
}