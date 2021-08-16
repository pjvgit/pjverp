<?php

namespace App\Http\Controllers\ClientPortal;

use App\Firm;
use App\Http\Controllers\Controller;
use App\Invoices;
use App\User;
use Illuminate\Http\Request;
use mikehaertl\wkhtmlto\Pdf;

class BillingController extends Controller 
{
    /**
     * Get client portal billing
     */
    public function index()
    {
        $invoices = Invoices::whereHas('invoiceShared', function($query) {
                        $query->where("user_id", auth()->id());
                    })->whereNotIn('status', ['Forwarded', 'Paid'])->orderBy('created_at', 'desc')
                    ->get();
        $forwardedInvoices = Invoices::whereHas('invoiceShared', function($query) {
                                $query->where("user_id", auth()->id());
                            })->whereIn('status', ['Forwarded', 'Paid'])->orderBy('created_at', 'desc')
                            ->with("invoiceForwardedToInvoice", "invoiceLastPayment")->get();
        return view("client_portal.billing.index", ['invoices' => $invoices, 'forwardedInvoices' => $forwardedInvoices]);
    }

    /**
     * Show invoice detail
     */
    public function show($id)
    {
        $invoiceId = base64_decode($id);
        $invoice = Invoices::where("id",$invoiceId)->with('case', 'case.caseBillingClient', 'invoiceTimeEntry', 'invoiceFlatFeeEntry', 
                    'invoiceExpenseEntry', 'invoiceTimeEntry.taskActivity', 'invoiceExpenseEntry.expenseActivity', 'invoiceAdjustmentEntry', 
                    'forwardedInvoices', 'invoicePaymentHistory', 'invoiceInstallment', 'invoiceForwardedToInvoice', 'invoiceFirstInstallment')->first();
        $invoice->fill(['is_viewed' => 'yes'])->save();
        $invoice->refresh();
        return view("client_portal.billing.detail", ["invoice" => $invoice]);
    }

    /**
     * Download invoice pdf
     */
    public function downloaInvoivePdf($id)
    {
        $invoiceId = base64_decode($id);
        $invoice = Invoices::where("id",$invoiceId)->with('case', 'case.caseBillingClient', 'invoiceTimeEntry', 'invoiceFlatFeeEntry', 
                    'invoiceExpenseEntry', 'invoiceTimeEntry.taskActivity', 'invoiceExpenseEntry.expenseActivity', 'invoiceAdjustmentEntry', 
                    'forwardedInvoices', 'invoicePaymentHistory', 'invoiceInstallment')->first();

        $userData = User::select("users.*","countries.name as countryname")->leftJoin('lead_additional_info','users.id',"=","lead_additional_info.user_id")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$invoice->user_id)->first();
       
        //Getting firm related data
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();        
        $firmData=Firm::find($userData['firm_name']);

        // return view('client_portal.billing.pdf_view', ['userData' => $userData, 'firmData' => $firmData, 'invoice' => $invoice, 'firmAddress' => $firmAddress]);
        
        $filename="Invoice_".$invoiceId.'.pdf';
        $PDFData=view('client_portal.billing.pdf_view',compact('userData','firmData','invoice','firmAddress'));
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = EXE_PATH;
        }
        $pdf->addPage($PDFData);
        if (!$pdf->send($filename)) {
            return redirect()->back()->with('error', $pdf->getError());
        }
    }
}