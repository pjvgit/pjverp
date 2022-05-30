<?php
 
namespace App\Traits;

use App\EventRecurring;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait UserCaseSharingTrait {

    /**
     * Link firm user/staff to case events
     */
    public function shareEventToUser($caseId, $staffUserId, $isEventRead = 'no')
    {
        // Link user with events
        $eventRecurrings = EventRecurring::whereHas('event', function($query) use($caseId) {
            $query->where('case_id', $caseId)->where('is_event_private', 'no');
        })->get();
        if($eventRecurrings) {
            foreach($eventRecurrings as $key => $item) {
                $decodeStaff = encodeDecodeJson($item->event_linked_staff);
                $checkUserExist = $decodeStaff->where("user_id", (string)$staffUserId)->where('is_linked', 'no')->first();
                if($checkUserExist) {
                    $newArray = [];
                    foreach($decodeStaff as $skey => $sitem) {
                        if($sitem->user_id == $staffUserId) {
                            $sitem->is_linked = 'yes';
                            $sitem->is_read = $isEventRead;
                        }
                        $newArray[] = $sitem;
                    }
                    $item->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();
                } else {
                    $eventLinkedStaff = [
                        'event_id' => $item->event_id,
                        'user_id' => (string)$staffUserId,
                        'is_linked' => 'yes',
                        'attending' => "no",
                        'comment_read_at' => Carbon::now(),
                        'created_by' => auth()->id(),
                        'is_read' => $isEventRead,
                    ];
                    $decodeStaff->push($eventLinkedStaff);
                    $item->fill(['event_linked_staff' => encodeDecodeJson($decodeStaff, 'encode')])->save();
                }
            }
        }
    }

    /**
     * Unlink staff/firm user from event
     */
    public function eventUnlinkUser($caseId, $staffUserId)
    {
        $CaseEventData = EventRecurring::whereHas('event', function($query) use($caseId) {
            $query->where('case_id', $caseId);
        })->whereJsonContains('event_linked_staff', ['user_id' => (string)$staffUserId])->get();
        if(count($CaseEventData) > 0) {
            foreach ($CaseEventData as $key => $item) {
                $decodeStaff = encodeDecodeJson($item->event_linked_staff);
                $newArray = [];
                foreach($decodeStaff as $skey => $sitem) {
                    if($sitem->user_id == $staffUserId) {
                    } else {
                        $newArray[] = $sitem;
                    }
                }
                $item->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();
            }
        }
    }

    /**
     * Link firm client to case events
     */
    public function shareEventToClient($caseId, $clientId, $isEventRead = 'no')
    {
        // Link user with events
        $eventRecurrings = EventRecurring::whereHas('event', function($query) use($caseId) {
            $query->where('case_id', $caseId)->where('is_event_private', 'no');
        })->get();
        if($eventRecurrings) {
            foreach($eventRecurrings as $key => $item) {
                $decodeContact = encodeDecodeJson($item->event_linked_contact_lead);
                $eventLinkedContact = [
                    'event_id' => $item->id,
                    'user_type' => 'contact',
                    'contact_id' => $clientId,
                    'attending' => 'no',
                    'invite' => 'yes',
                    'is_view' => $isEventRead,
                    'created_by' => auth()->id(),
                ];
                $decodeContact->push($eventLinkedContact);
                $item->fill(['event_linked_contact_lead' => encodeDecodeJson($decodeContact, 'encode')])->save();
            }
        }
    }

    /**
     * Unlink client from event
     */
    public function eventUnlinkClient($caseId, $clientId)
    {
        $CaseEventData = EventRecurring::whereHas('event', function($query) use($caseId) {
            $query->where('case_id',$caseId);
        })->whereJsonContains('event_linked_contact_lead', ['contact_id' => (string)$clientId])->get();
        if(count($CaseEventData) > 0) {
            foreach ($CaseEventData as $key => $item) {
                $decodeStaff = encodeDecodeJson($item->event_linked_contact_lead)->where('user_type', 'contact');
                $newArray = [];
                foreach($decodeStaff as $skey => $sitem) {
                    if($sitem->contact_id == $clientId) {
                    } else {
                        $newArray[] = $sitem;
                    }
                }
                $item->fill(['event_linked_contact_lead' => encodeDecodeJson($newArray, 'encode')])->save();
            }
        }
    }

    /**
     * Re-assign user events to new user when firm user deactivate
     */
    public function reAssignEventToNewUser($oldUserId, $newUserId)
    {
        $eventRecurrings = EventRecurring::whereJsonContains('event_linked_staff', ["user_id" => (string)$oldUserId])->has('event')->get();
        if($eventRecurrings) {
            Log::info("event recurrings found");
            foreach($eventRecurrings as $ekey => $item) {
                $decodeStaff = encodeDecodeJson($item->event_linked_staff);
                // $checkUserExist = $decodeStaff->where("user_id", $oldUserId)->first();
                // if($checkUserExist) {
                    Log::info("user found in linked list");
                    $newArray = [];
                    foreach($decodeStaff as $skey => $sitem) {
                        if($sitem->user_id == $oldUserId) {
                            $sitem->user_id = $newUserId;
                            $sitem->is_read = 'no';
                        }
                        $newArray[] = $sitem;
                    }
                    $item->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();
                // }
            }
        }
    }
}