<?php
 
namespace App\Traits;

use App\User;
use Illuminate\Support\Facades\Log;

trait TaskReminderTrait {
 
    public function getTaskLinkedUser($item, $notifyType) {
        // return $notifyType;
        $taskLinkedUser = []; $caseLinkedUser = [];
        if(!empty($item->task->taskLinkedStaff)) {
            $taskLinkedUser = $item->task->taskLinkedStaff->pluck('id')->toArray();
        }
        if($item->task->case_id && $item->task->case) {
            if(!empty($item->task->case->caseStaffAll)) {
                $caseLinkedUser = $item->task->case->caseStaffAll->pluck('user_id')->toArray();
            }
        } else if(!empty($item->task->lead_id) && $item->task->leadAdditionalInfo) {
            $caseLinkedUser = @$item->task->leadAdditionalInfo->pluck("assigned_to")->toArray();
        } else {
            $caseLinkedUser = [];
        }
        // Log::info("Task linked user:". $taskLinkedUser);
        // Log::info("Task assigned user:". $caseLinkedUser);
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
 