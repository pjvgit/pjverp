<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB,Schema;

class MysqlController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    //Alter table query for execution.
    public function executeQuery()
    {
        
        DB::statement("ALTER TABLE `users` ADD `mobile_number` INT(12) NOT NULL AFTER `password`, ADD `employee_no` INT(5) NOT NULL DEFAULT '0' COMMENT 'Number of Firm Employees' AFTER `mobile_number`");
        
        // if (!Schema::hasColumn('users', 'user_type'))
        // {
        //     DB::statement("ALTER TABLE `users` ADD `user_type` ENUM('1','2','3') NOT NULL DEFAULT '3' COMMENT '1 : Admin 2:Client 3:User' AFTER `remember_token`");
        // }

    }   
}
