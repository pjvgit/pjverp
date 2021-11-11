<?php

namespace App\Http\Controllers\ClientPortal;

use App\CaseEvent;
use App\CaseEventComment;
use App\CaseEventLinkedContactLead;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Invoices;
use App\Jobs\EventCommentEmailJob;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventController extends Controller 
{
    /**
     * Get client portal billing
     */
    public function index()
    {
        $events = CaseEvent::whereHas("eventLinkedContact", function($query) {
                        $query->where('users.id', auth()->id());
                    })->whereDate('start_date', '>=', Carbon::now())->orderBy('start_date', 'asc')
                    ->with(["eventLinkedContact" => function($query) {
                        $query->where('users.id', auth()->id())->select("case_event_linked_contact_lead.is_view");
                    }])->get();
        return view("client_portal.events.index", compact('events'));
    }

    /**
     * Show invoice detail
     */
    public function show($id)
    {
        $eventId = base64_decode($id);
        $event = CaseEvent::where("id",$eventId)->whereHas("eventLinkedContact", function($query) {
                    $query->where('users.id', auth()->id());
                })->with('case', 'eventLocation', 'leadUser', 'clientReminder')->first();
        if($event) {
            if($event->parent_evnt_id > 0) {
                $eventRelatedIds = CaseEvent::where("parent_evnt_id", $event->parent_evnt_id)
                                    ->whereHas("eventLinkedContact", function($query) {
                                        $query->where('users.id', auth()->id())->where("case_event_linked_contact_lead.is_view", 'no');
                                    })->pluck("id")->toArray();
            } else {
                $eventRelatedIds = [$event->id];
            }
            if(count($eventRelatedIds)) {
                CaseEventLinkedContactLead::whereIn("event_id", $eventRelatedIds)->where('contact_id', auth()->id())->update(['is_view' => 'yes']);
                
                $data=[];
                $data['event_id'] = $eventId;
                $data['case_id'] = $event->case_id;
                $data['user_id'] = auth()->id();
                $data['activity']='has viewed event';
                $data['type']='event';
                $data['action']='view';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            }
            $event->refresh();
            return view("client_portal.events.detail", compact('event'));
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
        $comment = CaseEventComment::create([
            'event_id' => $request->event_id,
            'comment' => $request->message,
            'action_type' => 0,
            'created_by' => $authUser->id,
        ]);
        $caseEvent = CaseEvent::whereId($request->event_id)->with('eventLinkedContact')->first();
        $data=[];
        $data['event_for_case']=$caseEvent->case_id;
        $data['event_id'] = $caseEvent->id;
        $data['event_name']=$caseEvent->event_title;
        $data['user_id'] = auth()->id();
        $data['activity']='commented on event';
        $data['type']='event';
        $data['action']='comment';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        // For client recent activity
        if($caseEvent->eventLinkedContact) {
            foreach($caseEvent->eventLinkedContact as $key => $item) {
                $data['user_id'] = $item->id;
                $data['client_id'] = $item->id;
                $data['activity']='commented event';
                $data['is_for_client'] = 'yes';
                $CommonController->addMultipleHistory($data);
            }
        }

        dispatch(new EventCommentEmailJob($request->event_id, $authUser->firm_name, $comment->id, $authUser->id));

        return response()->json(['success'=> true, 'message' => "Comment added"]);
    }

    /**
     * Get event comment history
     */
    public function eventCommentHistory(Request $request)
    {
        $commentData = CaseEventComment::where("event_id", $request->event_id)->where("action_type", 0)->orderBy('created_at')->with("createdByUser")->get();
        $view = view('client_portal.events.load_comment_history',compact('commentData'))->render();
        return response()->json(['totalComment' => $commentData->count(), 'view' => $view]);
    }  
}