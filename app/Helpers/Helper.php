<?php

use App\LeadAdditionalInfo;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Get lead list for add task
 */
function userLeadList()
{
    $authUser = auth()->user();
    $leades = User::where("firm_name", $authUser->firm_name)->where("user_type", 5)->where("user_level","5")->whereHas("userLeadAdditionalInfo", function($query) {
        $query->where("is_converted", "no")->where("user_status", 1);
    });
    if($authUser->parent_user != 0) {
        $leades = $leades->where("parent_user", $authUser->id);
    }
    return $leades->get()->pluck("full_name", "id");
    // LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->where("lead_additional_info.user_status", 1)->get();
}

/**
 * Get firm user/owner clients
 */
function userClientList()
{
    $authUser = auth()->user();
    $clients = User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where("firm_name", $authUser->firm_name)
                ->where('user_level', 2)->whereIn("user_status", [1,2]);
    if($authUser->parent_user != 0) {
        $clients = $clients->where("parent_user", $authUser->id);
    }
    return $clients->pluck("name", "id");
    // User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
}

/**
 * Get firm user/owner company
 */
function userCompanyList()
{
    $authUser = auth()->user();
    $company = User::where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4);
    if($authUser->parent_user != 0) {
        $company = $company->where("parent_user", $authUser->id);
    }
    return $company->get()->pluck("full_name", "id");
    // User::select("email","first_name","last_name","id","user_level")->whereIn("user_status",[1,2])->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
}

/**
 * Get firm user list
 */
function firmUserList()
{
    return User::select("first_name","last_name","id","user_level","user_title","default_rate")->where("firm_name", auth()->user()->firm_name)
                ->where("user_level","3")->get();
    /* $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
    $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
    $getChildUsers[]=Auth::user()->id;
    $getChildUsers[]="0"; //This 0 mean default category need to load in each user
    $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get(); */
}

/*
* Begine Transaction.                  
*/
function dbStart()
{
    return DB::beginTransaction();
}

/*
* Commit Transaction.     
*/
function dbCommit()
{
    return DB::commit();
}

/**
 * RollBack Transaction.                    
 */
function dbEnd()
{
    return DB::rollback();
}