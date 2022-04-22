<?php
 
namespace App\Traits;

use App\User;
use Illuminate\Support\Facades\Log;

trait EventReminderTrait {
 
    public function getEventLinkedUser($item, $notifyType, $event, $eventRecurring) {
        // return $notifyType;
        $decodeStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
        if($item->reminder_user_type == "attorney" || $item->reminder_user_type == "staff" || $item->reminder_user_type == "paralegal") {
            $eventLinkedUser = $decodeStaff->pluck('user_id')->toArray();
            $caseLinkedUser = [];
            if($event->case) {
                $caseLinkedUser = $event->case->caseStaffAll->pluck('user_id')->toArray();
            }
            $userType = ($item->reminder_user_type == "attorney") ? 1 : (($item->reminder_user_type == "staff") ? 3 : 2);
            $users = User::where(function ($qry) use ($eventLinkedUser, $caseLinkedUser){
                        $qry->whereIn("id", $eventLinkedUser)->orWhereIn("id", $caseLinkedUser);
                    })->where("user_type", $userType)->withoutAppends()->get();
            $attendEvent = $decodeStaff->pluck("attending", 'user_id')->toArray();
        } else if($item->reminder_user_type == "client-lead") {
            $decodeContacts = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
            $eventLinkContactIds = $decodeContacts->pluck('contact_id')->toArray();
            $eventLinkedLeadIds = $decodeContacts->pluck('lead_id')->toArray();
            $users = User::where(function ($qry) use ($eventLinkContactIds, $eventLinkedLeadIds) {
                    $qry->whereIn("id", $eventLinkContactIds)->orWhereIn("id", $eventLinkedLeadIds);
                })->withoutAppends()->get();
            if(count($eventLinkContactIds)) {
                $attendEvent = $decodeContacts->pluck("attending", 'contact_id')->toArray();
            } else {
                $attendEvent = $decodeContacts->pluck("attending", 'lead_id')->toArray();
            }
        } else {
            $users = User::whereId($item->created_by)->withoutAppends()->get();
            $attendEvent = [$item->created_by => $item->attending ?? 'no'];
        }
        if($notifyType == "popup") {
            $users = $users->where("id", auth()->id());
            return $users;
        }
        // Log::info("users id:". $users);
        return ['users' => $users, 'attendEvent' => $attendEvent];
    } 
}
 