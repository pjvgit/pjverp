<?php

namespace App\Http\Controllers\ClientPortal;

use App\AllHistory;
use App\CaseEvent;
use App\EventRecurring;
use App\Http\Controllers\Controller;
use App\Invoices, App\Messages, App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller 
{
    /**
     * Get client portal dashboard
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $totalInvoice = Invoices::whereHas('invoiceShared', function($query) use($userId) {
                            $query->where("user_id", $userId)->where("is_shared", "yes");
                        })->whereNotIn('status', ['Paid','Forwarded']);
        if(isset($request->case_id) && $request->case_id != ''){
            $totalInvoice = $totalInvoice->where("case_id",$request->case_id);
        }                
        $totalInvoice = $totalInvoice->count();

        /* $upcomingEvents = CaseEvent::whereHas("eventLinkedContact", function($query) use($userId) {
                            $query->where('users.id', $userId)->select("case_event_linked_contact_lead.is_view");
                        })->whereDate('start_date', '>=', Carbon::now())
                        ->orderBy('start_date', 'asc');
        if(isset($request->case_id) && $request->case_id != ''){
            $upcomingEvents = $upcomingEvents->where("case_events.case_id",$request->case_id);
        }                
        $upcomingEvents = $upcomingEvents->take(3)->get(); */
        $authUserId = (string) auth()->id();
        $upcomingEvents = EventRecurring::whereJsonContains('event_linked_contact_lead', ["contact_id" => $authUserId])
                        ->whereDate('start_date', '>=', Carbon::now())->has('event');
        if(isset($request->case_id) && $request->case_id != ''){
            $upcomingEvents = $upcomingEvents->whereHas('event', function($query) use($request) {
                $query->where("case_id",$request->case_id);
            });
        }
        $upcomingEvents = $upcomingEvents->orderBy('start_date', 'asc')->with("event")->take(3)->get();

        $recentActivity = AllHistory::where("is_for_client", "yes")->where("client_id", $userId)
                        ->orderBy("created_at", "desc")->with(["createdByUser", "task"/*  => function($query) {
                            $query->select("task_title")->withoutAppends();
                        } */]);
        $recentActivity = $recentActivity->take(3)->get();
        
        $totalMessages = Messages::leftJoin("users","users.id","=","messages.created_by")
        ->leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.id')
        ->where("messages.is_read",1);
        // ->where("messages.user_id",'like', '%'.Auth::User()->id.'%')
        $totalMessages = $totalMessages->where(function($totalMessages){
            $totalMessages = $totalMessages->orWhere("messages.user_id",'like', '%'.Auth::User()->id.'%');
            $totalMessages = $totalMessages->orWhere("messages.created_by",Auth::user()->id);
        });
        if(isset($request->case_id) && $request->case_id != ''){
            $totalMessages = $totalMessages->where("messages.case_id",$request->case_id);
        }
        $totalMessages = $totalMessages->where("messages.firm_id",Auth::User()->firm_name)->count();

        $caseList = User::where('id', Auth::User()->id)->select('id')->with('clientCases')->first();

        return view("client_portal.home", compact('totalInvoice', 'upcomingEvents', 'recentActivity', 'totalMessages','caseList',  'request'));
    }

    /**
     * Show all recent activities
     */
    public function allNotification()
    {
        $userId = auth()->id();
        $recentActivity = AllHistory::where("is_for_client", "yes")->where("client_id", $userId)
                        ->orderBy("created_at", "desc")->with(["createdByUser", "task"/*  => function($query) {
                            $query->select("task_title")->withoutAppends();
                        } */])->get();

        return view("client_portal.all_notification", compact('recentActivity'));
    }
}