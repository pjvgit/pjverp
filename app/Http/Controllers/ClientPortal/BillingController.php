<?php

namespace App\Http\Controllers\ClientPortal;

use App\Firm;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\InvoiceHistory;
use App\Invoices;
use App\RequestedFund;
use App\SharedInvoice;
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
        
        $requestFunds = RequestedFund::where('client_id', auth()->id())->where('amount_due', '>', '0.00')->orderBy('created_at', 'desc')->get();
        $requestFundsHistory = RequestedFund::where('client_id', auth()->id())->where('amount_due', '0.00')->orderBy('created_at', 'desc')->get();
        return view("client_portal.billing.index", compact('invoices', 'forwardedInvoices', 'requestFunds', 'requestFundsHistory'));
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
        $sharedInv = SharedInvoice::where("user_id", auth()->id())->where("invoice_id", $invoiceId)->first();
        if($sharedInv && !$sharedInv->last_viewed_at) {
            $sharedInv->fill([
                'last_viewed_at' => date('Y-m-d h:i:s'),
                'is_viewed' => 'yes',
            ])->save();

            InvoiceHistory::create([
                "invoice_id" => $invoiceId,
                "acrtivity_title" => "Invoice Viewed",
                "responsible_user" => auth()->id(),
                "created_by" => auth()->id()
            ]);
        }
        return view("client_portal.billing.invoice_detail", ["invoice" => $invoice]);
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

    /**
     * Show fund request detail
     */
    public function showFundRequest($id)
    {
        $fundId = base64_decode($id);
        $fundRequest = RequestedFund::where("id",$fundId)->first();
        if($fundRequest) {
            $fundRequest->fill([
                'is_viewed' => 'yes',
            ])->save();

            $data=[];
            $data['deposit_id']=$fundRequest->id;
            $data['deposit_for']=$fundRequest->client_id;
            $data['user_id']=$fundRequest->client_id;
            $data['client_id']=$fundRequest->client_id;
            $data['activity']='has viewed deposit request';
            $data['type']='deposit';
            $data['action']='view';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
        }
        return view("client_portal.billing.fund_request_detail", compact("fundRequest"));
    }
}