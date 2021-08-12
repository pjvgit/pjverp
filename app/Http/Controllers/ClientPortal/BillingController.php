<?php

namespace App\Http\Controllers\ClientPortal;

use App\ExpenseForInvoice;
use App\Firm;
use App\Http\Controllers\Controller;
use App\InvoiceHistory;
use App\InvoiceInstallment;
use App\Invoices;
use App\TimeEntryForInvoice;
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
                    'forwardedInvoices', 'invoicePaymentHistory', 'invoiceInstallment')->first();
        
        return view("client_portal.billing.detail", ["invoice" => $invoice]);
    }

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

        $InvoiceHistory = InvoiceHistory::where("invoice_id",$invoiceId)->orderBy("id","DESC")->get();

        $InvoiceInstallment=InvoiceInstallment::Where("invoice_id",$invoiceId)->get();
        // return $invoice;
        return view('client_portal.billing.pdf_view', ['userData' => $userData, 'firmData' => $firmData, 'invoice' => $invoice, 'firmAddress' => $firmAddress, 'InvoiceHistory' => $InvoiceHistory, 'InvoiceInstallment' => $InvoiceInstallment]);
        
        $filename="Invoice_".$invoiceId.'.pdf';
        $PDFData=view('client_portal.billing.pdf_view',compact('userData','firmData','invoice','firmAddress','InvoiceHistory','InvoiceInstallment'));
        /* $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = EXE_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->saveAs(public_path("download/pdf/".$filename));
        $path = public_path("download/pdf/".$filename); */
        $pdfUrl = $this->generateInvoicePdf($PDFData, $filename);
        // return response()->download($path);
        // exit;

        // return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename], 200);
        return response()->json([ 'success' => true, "url" => $pdfUrl,"file_name"=>$filename], 200);
        exit;
    }

    /**
     * Generate invoice pdf and store it
     */
    public function generateInvoicePdf($pdfData, $filename)
    {
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        }
        $pdf->addPage($pdfData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        // $pdf->saveAs(Storage::path('download/pdf/'.$filename));

		if (!File::isDirectory('download')) {
			File::makeDirectory('download', 0755, true, true);
		}		
		$subDirectory = Storage::path("download/pdf");
		if (!File::isDirectory($subDirectory)) {
			File::makeDirectory($subDirectory, 0755, true, true);
		}
        if (!$pdf->saveAs($subDirectory.'/'.$filename)) {
            Log::info("Generate pdf error: ". $pdf->getError());
        }
        return asset(Storage::url("download/pdf/".$filename));
    }
}