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
            $user->user_status = '1';
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

    /**
     * Save parent user default permissions
     */
    public function saveUserDefaultPermission($user, $userType = null)
    {
        $permissions = [
            'client_add_edit',
            'lead_add_edit',
            'case_add_edit',
            'event_add_edit',
            'document_add_edit',
            'commenting_add_edit',
            'text_messaging_add_edit',
            'messaging_add_edit',
            'billing_add_edit',
            'billing_access_financial_insight',
            'reporting_entire_firm',

            'access_all_cases',
            'add_firm_user',
            ($userType == 'firmowner') ? 'edit_firm_user_permission' : '',
            'delete_items',
            ($userType == 'firmowner') ? 'empty_trash_permission' : '',
            'edit_import_export_settings',
            'edit_custom_fields_settings',
            ($userType == 'firmowner') ? 'manage_firm_and_billing_settings' : '',
        ];
        $user->syncPermissions($permissions);
    }
}
 