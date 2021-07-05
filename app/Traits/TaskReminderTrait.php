<?php
 
namespace App\Traits;

use App\User;

trait TaskReminderTrait {
 
    public function getTaskLinkedUser($item, $notifyType) {
        // return $notifyType;
        $taskLinkedUser = [];
        if(!empty($item->task->taskLinkedStaff)) {
            $taskLinkedUser = $item->task->taskLinkedStaff->pluck('id');
        }
        if($item->task->case_id) {
            $caseLinkedUser = $item->task->case->caseStaffAll->pluck('user_id');
        } else if(!empty($item->task->lead_id)) {
            // if(!empty($item->task->lead->userLeadAdditionalInfo)) {
                $caseLinkedUser = @$item->task->lead->userLeadAdditionalInfo->pluck("assigned_to");
            // }
        } else {
            $caseLinkedUser = [];
        }
        $users = User::where(function($query) use($taskLinkedUser, $caseLinkedUser) {
            $query->whereIn("id", $taskLinkedUser)->orWhereIn("id", $caseLinkedUser);
        });
        if($item->reminder_user_type == "attorney") {
            $users = $users->where("user_type", "1");
        } else if($item->reminder_user_type == "staff") {
            $users = $users->where("user_type", "3");
        } else if($item->reminder_user_type == "paralegal") {
            $users = $users->where("user_type", "2");
        } else {
            $users = User::whereId($item->created_by);
        }
        if($notifyType == "popup") {
            $users = $users->whereId(auth()->id());
        }
        return $users = $users->get();
    }
 
}
 