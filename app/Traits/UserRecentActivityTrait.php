<?php
 
namespace App\Traits;

use App\User,App\NotificationSetting,App\CaseStage;
use Illuminate\Support\Facades\Log;
use DB;

trait UserRecentActivityTrait {
 
    public function bulkInsertUserActivity($user_id) {
 
        $user = User::whereId($user_id)->first();
        $notifications = NotificationSetting::pluck("topic", "id")->toArray();
        
        $finalArray = [];
        foreach ($notifications as $key => $item) {
            $finalArray[$key] = [
                'for_email' => "yes",
                'for_feed' => "yes"
            ];
        }
        $user->userNotificationSetting()->sync($finalArray);

        DB::table('user_notification_interval')->updateOrInsert(['user_id' => $user_id],['user_id' => $user_id, 'notification_email_interval' => 1440]);
    }
    
}