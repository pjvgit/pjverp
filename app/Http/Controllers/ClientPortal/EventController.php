<?php

namespace App\Http\Controllers\ClientPortal;

use App\CaseEvent;
use App\CaseEventComment;
use App\CaseEventLinkedContactLead;
use App\Event;
use App\EventRecurring;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Invoices;
use App\Jobs\EventCommentEmailJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller 
{
    /**
     * Get client portal billing
     */
    public function index()
    {
        /* $events = CaseEvent::whereHas("eventLinkedContact", function($query) {
                        $query->where('users.id', auth()->id());
                    })->whereDate('start_date', '>=', Carbon::now())->orderBy('start_date', 'asc')
                    ->with(["eventLinkedContact" => function($query) {
                        $query->where('users.id', auth()->id())->select("case_event_linked_contact_lead.is_view");
                    }])->get(); */
        $authUser = auth()->user();
        $authUserId = (string) auth()->id();
        $currentDate = convertUTCToUserDate(Carbon::now()->format('Y-m-d'), $authUser->user_timezone)->format('Y-m-d');
        $events = EventRecurring::whereJsonContains('event_linked_contact_lead', ["contact_id" => $authUserId])
                    ->whereDate('start_date', '>=', $currentDate)
                    ->whereHas('event', function($query) {
                        $query->where('is_SOL', 'no');
                    })
                    ->orderBy('start_date', 'asc')->with("event")->get();
        return view("client_portal.events.index", compact('events', 'authUser'));
    }

    /**
     * Show invoice detail
     */
    public function show($id)
    {
        $eventRecurringId = base64_decode($id);
        $authUser = auth()->user();
        $authUserId = (string) $authUser->id;
        /* $event = CaseEvent::where("id",$eventId)->whereHas("eventLinkedContact", function($query) {
                    $query->where('users.id', auth()->id());
                })->with('case', 'eventLocation', 'leadUser', 'clientReminder')->first(); */
        $eventRecurring = EventRecurring::where("id", $eventRecurringId)->whereJsonContains('event_linked_contact_lead', ["contact_id" => $authUserId])->has('event')->first();
        if($eventRecurring) {
            $event = Event::where("id", $eventRecurring->event_id)->with('case', 'eventLocation', 'leadUser')->first();
            /* if($event->parent_evnt_id > 0) {
                $eventRelatedIds = CaseEvent::where("parent_evnt_id", $event->parent_evnt_id)
                                    ->whereHas("eventLinkedContact", function($query) {
                                        $query->where('users.id', auth()->id())->where("case_event_linked_contact_lead.is_view", 'no');
                                    })->pluck("id")->toArray();
            } else {
                $eventRelatedIds = [$event->id];
            }
            if(count($eventRelatedIds)) {
                CaseEventLinkedContactLead::whereIn("event_id", $eventRelatedIds)->where('contact_id', auth()->id())->update(['is_view' => 'yes']); */
                $allRecurringEvents = EventRecurring::where("event_id", $event->id)->get();
                foreach($allRecurringEvents as $rkey => $ritem) {
                    $updatedLinkedContact = [];
                    $linkedContact = encodeDecodeJson($ritem->event_linked_contact_lead);
                    foreach($linkedContact as $key => $item) {
                        if($item->contact_id == $authUserId) {
                            $item->is_view = 'yes';
                        }
                        $updatedLinkedContact[] = $item;
                    }
                    $ritem->fill(['event_linked_contact_lead' => encodeDecodeJson($updatedLinkedContact, 'encode')])->save();
                }

                $data=[];
                $data['event_id'] = $eventRecurringId;
                $data['case_id'] = $event->case_id;
                $data['event_name'] = $eventevent_title ?? "<No Title>";
                $data['user_id'] = $authUser->id;
                $data['activity']='has viewed event';
                $data['type']='event';
                $data['action']='view';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            // }
            $event->refresh();
            return view("client_portal.events.detail", compact('event', 'eventRecurring', 'authUser'));
        } else {
            return redirect()->route("client/events");
        }
    }

    /**
     * Save event comment
     */
    public function saveComment(Request $request)
    {
        $authUser = auth()->user();
        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        if($eventRecurring) {
            $eventComment = [
                'event_id' => $request->event_id,
                'comment' => $request->message,
                'action_type' => 0,
                'created_by' => $authUser->id,
                'created_at' => Carbon::now(),
            ];
            $decodeJson = encodeDecodeJson($eventRecurring->event_comments);
            $decodeJson->push($eventComment);
            $eventRecurring->fill(['event_comments' => encodeDecodeJson($decodeJson)])->save();

            $event = Event::whereId($request->event_id)->first(); 
            $data=[];
            $data['event_for_case'] = $event->case_id;
            $data['event_id'] = $event->id;
            $data['event_recurring_id'] = $eventRecurring->id;
            $data['event_name'] = $event->event_title;
            $data['user_id'] = $authUser->id;
            $data['activity']='commented on event';
            $data['type']='event';
            $data['action']='comment';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            // For client recent activity
            if($eventRecurring->event_linked_contact_lead) {
                $decodeContacts = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
                foreach($decodeContacts as $key => $item) {
                    $data['user_id'] = $item->contact_id;
                    $data['client_id'] = $item->contact_id;
                    $data['activity']='commented event';
                    $data['is_for_client'] = 'yes';
                    $CommonController->addMultipleHistory($data);
                }
            }
            Log::info("client comment email job dispatched");
            dispatch(new EventCommentEmailJob($request->event_id, $authUser->firm_name, $eventComment, $authUser->id, $request->event_recurring_id));
        }
        return response()->json(['success'=> true, 'message' => "Comment added"]);

    }

    /**
     * Get event comment history
     */
    public function eventCommentHistory(Request $request)
    {
        $eventRecurring = EventRecurring::where("id", $request->event_recurring_id)->where("event_id", $request->event_id)->first();
        $commentData = encodeDecodeJson($eventRecurring->event_comments)->where('action_type', "0");
        $authUser = auth()->user();
        $view = view('client_portal.events.load_comment_history',compact('commentData', 'authUser'))->render();
        return response()->json(['totalComment' => $commentData->count(), 'view' => $view]);
    }  
}