<?php

namespace App\Http\Controllers\ClientPortal;

use App\AllHistory;
use App\CaseEvent;
use App\Http\Controllers\Controller;
use App\Invoices;
use Carbon\Carbon;

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
                            $query->where('users.id', $userId);
                        })->whereDate('start_date', '>=', Carbon::now())->orderBy('start_date', 'asc')->take(3)->get();

        $recentActivity = AllHistory::where("action", "share")->where("client_id", $userId)
                        ->orderBy("created_at", "desc")->with(["createdByUser"])->take(3)->get();

        return view("client_portal.home", compact('totalInvoice', 'upcomingEvents', 'recentActivity'));
    }
}