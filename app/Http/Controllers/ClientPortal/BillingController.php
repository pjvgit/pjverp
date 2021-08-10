<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Invoices;

class BillingController extends Controller 
{
    /**
     * Get client portal billing
     */
    public function index()
    {
        $invoices = Invoices::whereHas('invoiceShared', function($query) {
                        $query->where("user_id", auth()->id());
                    })->orderBy('created_at', 'desc')->get();
        return view("client_portal.billing.index", ['invoices' => $invoices]);
    }

    /**
     * Show invoice detail
     */
    public function show($id)
    {
        $invoiceId = base64_decode($id);
        $invoice = Invoices::where("id",$invoiceId)->first();
        return view("client_portal.billing.detail", ["invoice" => $invoice]);
    }
}