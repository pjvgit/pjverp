<?php

use App\LeadAdditionalInfo;
use App\User;

/**
 * Get lead list for add task
 */
function userLeadList()
{
    return User::where("user_type", 5)->where("user_level","5")->whereHas("userLeads", function($query) {
        $query->where("parent_user", auth()->id())->where("is_converted", "no")->where("user_status", 1);
    })->get()->pluck("full_name", "id");
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