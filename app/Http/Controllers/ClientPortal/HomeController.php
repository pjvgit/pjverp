<?php

namespace App\Http\Controllers\ClientPortal;

use App\AllHistory;
use App\CaseEvent;
use App\Http\Controllers\Controller;
use App\Invoices, App\Messages;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller 
{
    /**
     * Get client portal dashboard
     */
    public function index()
    {
        $userId = auth()->id();
        $totalInvoice = Invoices::whereHas('invoiceShared', function($query) use($userId) {
                            $query->where("user_id", $userId)->where("is_shared", "yes");
                        })->where('status', '!=', 'Paid')->count();
        $upcomingEvents = CaseEvent::whereHas("eventLinkedContact", function($query) use($userId) {
                            $query->where('users.id', $userId)->select("case_event_linked_contact_lead.is_view");
                        })->whereDate('start_date', '>=', Carbon::now())->orderBy('start_date', 'asc')->take(3)->get();

        $recentActivity = AllHistory::where("is_for_client", "yes")->where("client_id", $userId)
                        ->orderBy("created_at", "desc")->with(["createdByUser", "task"/*  => function($query) {
                            $query->select("task_title")->withoutAppends();
                        } */])->take(3)->get();
        
        $totalMessages = Messages::leftJoin("users","users.id","=","messages.created_by")
        ->leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.id')
        ->where("messages.is_read",1);
        // ->where("messages.user_id",'like', '%'.Auth::User()->id.'%')
        $totalMessages = $totalMessages->where(function($totalMessages){
            $totalMessages = $totalMessages->orWhere("messages.user_id",'like', '%'.Auth::User()->id.'%');
            $totalMessages = $totalMessages->orWhere("messages.created_by",Auth::user()->id);
        });
        $totalMessages = $totalMessages->where("messages.firm_id",Auth::User()->firm_name)->count();

        
        return view("client_portal.home", compact('totalInvoice', 'upcomingEvents', 'recentActivity', 'totalMessages'));
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