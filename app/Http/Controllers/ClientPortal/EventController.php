<?php

namespace App\Http\Controllers\ClientPortal;

use App\CaseEvent;
use App\CaseEventComment;
use App\Http\Controllers\Controller;
use App\Invoices;
use App\Jobs\CommentEmail;
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
                    })->whereDate('start_date', '>=', Carbon::now())->orderBy('start_date', 'asc')->get();
        return view("client_portal.events.index", compact('events'));
    }

    /**
     * Show invoice detail
     */
    public function show($id)
    {
        $eventId = base64_decode($id);
        $event = CaseEvent::where("id",$eventId)->with('case', 'eventLocation', 'leadUser', 'clientReminder')->first();
        $event->fill(['event_read' => 'yes'])->save();
        $event->refresh();
        return view("client_portal.events.detail", compact('event'));
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

        dispatch(new CommentEmail($request->event_id, $authUser->firm_name, $comment->id, $authUser->id));

        return response()->json(['errors'=>'', 'message' => "Comment added"]);
    }

    /**
     * Get event comment history
     */
    public function eventCommentHistory(Request $request)
    {
        $evnt_id=$request->event_id;
        $evetData=CaseEvent::find($evnt_id);

        
        //Event created By user name
        $eventCreatedBy = '';
        if(!empty($evetData) && $evetData->created_by != NULL){
            $eventCreatedBy = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->created_by)->first();
        }
         

        //Event comment data
        $commentData = CaseEventComment::join('users','users.id','=','case_event_comment.created_by')
        ->select("users.id","users.first_name","users.last_name","case_event_comment.comment","user_type","case_event_comment.created_at","case_event_comment.action_type")->where("case_event_comment.event_id",$evnt_id)->orderBy('case_event_comment.created_at','DESC')->get();
            
        return view('case.event.loadEventHistory',compact('evetData','eventCreatedBy','updatedEvenByUserData','commentData'));     
        exit;    
    }  
}