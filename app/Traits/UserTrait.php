<?php
 
namespace App\Traits;

use App\Firm;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait UserTrait {

    /**
     * Update same email user detail
     */
    public function updateSameEmailUserDetail($user)
    {
        $existVerifiedUser = User::whereEmail($user->email)->whereVerified(1)->whereNotNull('password')->first();
        if($existVerifiedUser) {
            $user->user_status = 1;
            $user->verified = 1;
            $user->password = $existVerifiedUser->password;
            $user->user_timezone = $existVerifiedUser->user_timezone;
            $user->save();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get login user's firms
     */
    public function getUserFirms($user)
    {
        return Firm::join("users", function($query) use($user) {
                $query->on('firm.id', '=', 'users.firm_name');
                $query->where('users.email', $user->email)->whereIn("users.user_level", ['2','3']);
                $query->leftjoin("users_additional_info", function($join) {
                    $join->on("users.id", '=', 'users_additional_info.user_id')->where("users_additional_info.client_portal_enable", '1');
                });
            })->select("firm.*", "users.id as user_id", "users_additional_info.client_portal_enable", "users.is_primary_account", "users.user_level")->get();
    }
}
 