<?php
 
namespace App\Traits;

use App\CasePracticeArea;
use App\User,App\NotificationSetting,App\CaseStage,App\ClientGroup;
use Illuminate\Support\Facades\Log;
use DB;

trait FirmDefaultSettingTrait {
 
    public function bulkInsertUserActivity($user_id) {
 
        $user = User::whereId($user_id)->first();
        $notifications = NotificationSetting::pluck("topic", "id")->toArray();
        
        $finalArray = [];
        foreach ($notifications as $key => $item) {
            $finalArray[$key] = [
                'for_email' => "yes",
                'for_feed' => "yes",
                'for_app' => "no",
            ];
        }
        $user->userNotificationSetting()->sync($finalArray);

        $notificationSetting = NotificationSetting::whereIn("type", ["calendars","tasks"])->whereIn("action", ["add","update"])->get();
        foreach($notificationSetting as $key => $item){
            if($item->type == "calendars" && $item->action == "update"){
                DB::table('user_notification_settings')->updateOrInsert(['user_id' => $user_id,"notification_id"=>$item->id],['user_id' => $user_id, 'for_app' => 'no']);    
            }else{
                DB::table('user_notification_settings')->updateOrInsert(['user_id' => $user_id,"notification_id"=>$item->id],['user_id' => $user_id, 'for_app' => 'yes']);    
            }
        }
        DB::table('user_notification_interval')->updateOrInsert(['user_id' => $user_id],['user_id' => $user_id, 'notification_email_interval' => 1440]);
    }
    
    /**
     * Insert firm's default case stages
     */
    public function saveFirmDefaultCaseStages($user)
    {
        $CaseStage =  CaseStage::where('firm_id', $user->firm_name)->get();
        if(count($CaseStage) == 0){
            $data = array(
                array('stage_order' => 1, 'title'=>'Discovery', 'stage_color'=>'#FF0000','firm_id' => $user->firm_name,'created_by' => $user->id, 'created_at' => date('Y-m-d')),
                array('stage_order' => 2, 'title'=>'In Trial', 'stage_color'=>'#00FF00','firm_id' => $user->firm_name,'created_by' => $user->id, 'created_at' => date('Y-m-d')),
                array('stage_order' => 3, 'title'=>'On Hold', 'stage_color'=>'#0000FF','firm_id' => $user->firm_name,'created_by' => $user->id, 'created_at' => date('Y-m-d')),
            );        
            CaseStage::insert($data);
        }
    }

    /**
     * Insert firm's default case practice areas
     */
    public function saveFirmDefaultPracticeArea($user)
    {
        $CasePracticeArea = array(
            array('title'=>'Bankruptcy','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Business','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Civil Party','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Criminal Defense','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Divorce/Separation','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'DUI/DWI','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Employment','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Estate Planning','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Family','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Foreclosure','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Immigration','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Landlord/Tenant','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Personal Injury','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Real Estate','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
            array('title'=>'Tax','status' => '1','firm_id' => $user->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => $user->id),
        );
        CasePracticeArea::insert($CasePracticeArea);
    }

    /**
     * Insert firm's default client group
     */
    public function saveFirmClientGroup($user)
    {
        $ClientGroup = ClientGroup::where('firm_id', $user->firm_name)->get();
        if(count($ClientGroup) == 0){
            $data = array(
                array("group_name" => 'Client', "status" => 1, "firm_id" => $user->firm_name, "is_default" => 1, "created_by" => $user->id, "created_at" => date('Y-m-d')),
                array("group_name" => 'Unassigned', "status" => 1, "firm_id" => $user->firm_name, "is_default" => 1, "created_by" => $user->id, "created_at" => date('Y-m-d')),
            );        
            ClientGroup::insert($data);
        }
    }
}