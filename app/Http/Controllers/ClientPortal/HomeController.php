<?php

namespace App\Http\Controllers\ClientPortal;

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
        $totalInvoice = Invoices::whereHas('invoiceShared', function($query) {
                            $query->where("user_id", auth()->id());
                        })->where('status', '!=', 'Paid')->count();
        $upcomingEvents = CaseEvent::whereHas("eventLinkedContact", function($query) {
                            $query->where('users.id', auth()->id());
                        })->whereDate('start_date', '>=', Carbon::now())->orderBy('start_date', 'asc')->take(3)->get();
        return view("client_portal.home", compact('totalInvoice', 'upcomingEvents'));
    }
}