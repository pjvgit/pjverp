<?php
 
namespace App\Traits;

use App\User;
use Illuminate\Support\Facades\Log;

trait EventReminderTrait {
 
    public function getEventLinkedUser($item, $notifyType) {
        // return $notifyType;
        if($item->reminder_user_type == "attorney" || $item->reminder_user_type == "staff" || $item->reminder_user_type == "paralegal") {
            $eventLinkedUser = $item->event->eventLinkedStaff->pluck('id');
            if($item->event->case) {
                $caseLinkedUser = $item->event->case->caseStaffAll->pluck('user_id');
            }
            $userType = ($item->reminder_user_type == "attorney") ? 1 : (($item->reminder_user_type == "staff") ? 3 : 2);
            $users = User::whereIn("id", $eventLinkedUser)->orWhereIn("id", $caseLinkedUser ?? [])->where("user_type", $userType)->withoutAppends()->get();
            $attendEvent = $item->event->eventLinkedStaff->pluck("pivot.attending", 'id')->toArray();

        } else if($item->reminder_user_type == "client-lead") {
            $eventLinkContactIds = $item->event->eventLinkedContact->pluck('id');
            $eventLinkedLeadIds = $item->event->eventLinkedLead->pluck('user_id');
            $users = User::whereIn("id", $eventLinkContactIds)->orWhereIn("id", $eventLinkedLeadIds)->withoutAppends()->get();
            if(count($eventLinkContactIds)) {
                $attendEvent = $item->event->eventLinkedContact->pluck("pivot.attending", 'id')->toArray();
            } else {
                $attendEvent = $item->event->eventLinkedLead->pluck("pivot.attending", 'id')->toArray();
            }
        } else {
            $users = User::whereId($item->created_by)->withoutAppends()->get();
            $attendEvent = [$item->created_by => "yes"];
        }
        if($notifyType == "popup") {
            $users = $users->where("id", auth()->id());
            return $users;
        }
        // Log::info("users id:". $users);
        return ['users' => $users, 'attendEvent' => $attendEvent];
    }
 
}
 