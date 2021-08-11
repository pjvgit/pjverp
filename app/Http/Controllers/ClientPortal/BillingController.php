<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Invoices;
use App\TimeEntryForInvoice;

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
        $invoice = Invoices::where("id",$invoiceId)->with('case', 'case.caseBillingClient', 'invoiceTimeEntry', 'invoiceFlatFeeEntry', 
                    'invoiceExpenseEntry', 'invoiceTimeEntry.taskActivity', 'invoiceExpenseEntry.expenseActivity', 'invoiceAdjustmentEntry', 
                    'forwardedInvoices', 'invoicePaymentHistory')->first();
        
        return view("client_portal.billing.detail", ["invoice" => $invoice]);
    }
}