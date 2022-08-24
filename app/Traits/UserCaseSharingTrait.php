<?php
 
namespace App\Traits;

use App\CaseTaskLinkedStaff;
use App\EventRecurring;
use App\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait UserCaseSharingTrait {

    /**
     * Link firm user/staff to case events
     */
    public function shareEventToUser($caseId = null, $staffUserId, $isEventRead = 'no')
    {
        // Link user with events
        $authUser = auth()->user();
        $eventRecurrings = EventRecurring::whereHas('event', function($query) use($caseId, $authUser) {
            $query->where("firm_id", $authUser->firm_name)->where('is_event_private', 'no')->whereNotNull('case_id');
            if($caseId) {
                $query->where('case_id', $caseId);
            }
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
            foreach($eventRecurrings as $ekey => $item) {
                $decodeStaff = encodeDecodeJson($item->event_linked_staff);
                    $newArray = [];
                    foreach($decodeStaff as $skey => $sitem) {
                        if($sitem->user_id == $oldUserId) {
                            $sitem->is_deactivate_reassign = 'yes';
                            $checkExist = $decodeStaff->where('user_id', $newUserId)->first();
                            if(empty($checkExist)) {
                                $newArray[] = [
                                    'event_id' => $sitem->event_id,
                                    'user_id' => $newUserId,
                                    'is_linked' => 'yes',
                                    'attending' => $sitem->attending,
                                    'comment_read_at' => $sitem->comment_read_at,
                                    'created_by' => auth()->id(),
                                    'is_read' => 'no',
                                ];
                            }
                        }
                        $newArray[] = $sitem;
                    }
                    $item->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();
            }
        }
    }

    /**
     * Link firm user/staff to case task
     */
    public function shareTaskToUser($caseId = null, $staffUserId, $isEventRead = 'no')
    {
        // Link user with events
        $authUser = auth()->user();
        $allTask = Task::where("firm_id", $authUser->firm_name)->whereNotNull('case_id');
        if($caseId) {
            $allTask = $allTask->where('case_id', $caseId);
        }
        $allTask = $allTask->get();
        if($allTask) {
            foreach($allTask as $key => $item) {

                CaseTaskLinkedStaff::create([
                    'task_id' => $item->id,
                    'user_id' => $staffUserId,
                    'linked_or_not_with_case' => 'yes',
                    'is_assign' => "yes",
                    'is_read' => $isEventRead,
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }
}