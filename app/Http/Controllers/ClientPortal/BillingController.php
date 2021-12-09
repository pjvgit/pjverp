<?php

namespace App\Http\Controllers\ClientPortal;

use App\Firm;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\InvoiceHistory;
use App\InvoiceOnlinePayment;
use App\Invoices;
use App\RequestedFund;
use App\SharedInvoice;
use App\Traits\CreditAccountTrait;
use App\User;
use Illuminate\Http\Request;
use mikehaertl\wkhtmlto\Pdf;

class BillingController extends Controller 
{
    use CreditAccountTrait;
    /**
     * Get client portal billing
     */
    public function index()
    {
        $invoices = Invoices::whereHas('invoiceShared', function($query) {
                        $query->where("user_id", auth()->id())->where("is_shared", "yes");
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
        $invoice = Invoices::where("id",$invoiceId)->whereHas('invoiceShared', function($query) {
                        $query->where("user_id", auth()->id())->where("is_shared", "yes");
                    })->with('case', 'case.caseBillingClient', 'invoiceTimeEntry', 'invoiceFlatFeeEntry', 
                    'invoiceExpenseEntry', 'invoiceTimeEntry.taskActivity', 'invoiceExpenseEntry.expenseActivity', 'invoiceAdjustmentEntry', 
                    'forwardedInvoices', 'invoicePaymentHistory', 'invoiceInstallment', 'invoiceForwardedToInvoice', 'invoiceFirstInstallment')->first();
        // $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
        // $dueDate = $invoice->due_date;
        // $currentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $currentDate);
        // $dueDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dueDate);
        // return $diffDays = $dueDate->diffInDays($currentDate);
        if($invoice) {
            $sharedInv = SharedInvoice::where("user_id", auth()->id())->where("invoice_id", $invoiceId)->first();
            if($sharedInv && !$sharedInv->last_viewed_at) {
                $sharedInv->fill([
                    'last_viewed_at' => date('Y-m-d h:i:s'),
                    'is_viewed' => 'yes',
                ])->save();
                $authUserId = auth()->id();
                InvoiceHistory::create([
                    "invoice_id" => $invoiceId,
                    "acrtivity_title" => "Invoice Viewed",
                    "responsible_user" => $authUserId,
                    "created_by" => $authUserId
                ]);

                $data=[];
                $data['activity_for'] = $invoiceId;
                $data['case_id'] = $invoice->case_id;
                $data['user_id'] = $authUserId;
                $data['activity']='has viewed invoice';
                $data['type']='invoices';
                $data['action']='view';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            }
            return view("client_portal.billing.invoice_detail", ["invoice" => $invoice]);
        } else {
            return redirect()->route("client/bills");
        }
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
            $data['type']='fundrequest';
            $data['action']='view';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
        }
        return view("client_portal.billing.fund_request_detail", compact("fundRequest"));
    }

    /**
     * Get invoice payment detail screen
     */
    public function paymentDetail($invoiceId, $clientId, Request $request)
    {
        $invoiceId = encodeDecodeId($invoiceId, 'decode');
        $clientId = encodeDecodeId($clientId, 'decode');
        $invoice = Invoices::where("id",$invoiceId)->whereHas('invoiceShared', function($query) use($clientId) {
                        $query->where("user_id", $clientId)->where("is_shared", "yes");
                    })->whereNotIn('status', ['Paid','Forwarded'])->first();
        if(!empty($invoice)) {
            // if(\Route::current()->getName() == "client/bills/payment/card/detail") {
                $month = $request->month;
            // }

            return view('client_portal.billing.invoice_payment', compact('invoice', 'clientId', 'month'));
        } else {
            return abort(403);
        }
    }

    /**
     * Get card payment options and redirect to card detail screen
     */
    public function getCardPaymentOption(Request $request)
    {
        return redirect()->route('client/bills/payment', ['invoice_id'=>$request->invoice_id, 'client_id'=>$request->client_id, 'month'=>$request->payment_option]);
    }

    /**
     * Load form to get credit/debit card detail
     */
    public function cardPayment(Request $request)
    {   
        // return $request->all();
        try {
            \Conekta\Conekta::setApiKey("key_pRsoTgnsyUULMb76SDXA6w");
            $invoice = Invoices::whereId($request->invoice_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
            $client = User::whereId($request->client_id)->first();
            if($invoice && $client) {
                if(empty($client->conekta_customer_id)) {
                    $customer = \Conekta\Customer::create([
                                    "name"=> $client->full_name,
                                    "email"=> $client->email,
                                    "phone"=> $client->mobile_number ?? $request->phone_number,
                                ]);
                    $client->fill(['conekta_customer_id' => $customer->id])->save();
                    $client->refresh();
                }
                $customerId = $client->conekta_customer_id;
                $invoice->refresh();
                // return (int)$invoice->due_amount;
                if(!in_array($invoice->status, ['Paid','Forwarded'])) {
                    $validOrderWithCheckout = [
                        /* 'checkout' => array(
                            'type' => 'Integration',
                            'allowed_payment_methods' => ["card"],
                            'monthly_installments_enabled' => true,
                            'monthly_installments_options' => [$request->emi_month],
                        ), */
                        'line_items' => [
                            [
                                'name' => 'Invoice number '.$invoice->id,
                                'unit_price' => (int)$invoice->due_amount,
                                'quantity' => 1,
                            ]
                        ],
                        'customer_info' => array(
                            'customer_id' => $customerId
                        ),
                        'currency'    => 'MXN',
                        'charges'  => [
                            [
                                'payment_method' => [
                                    'type'       => 'card',
                                    'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000",
                                    'token_id' => $request->conekta_token_id,
                                ],
                                'amount' => (int)$invoice->due_amount,
                            ]
                        ],
                        'metadata'    => array('test' => 'extra info')
                    ];
                    $order = \Conekta\Order::create($validOrderWithCheckout);
                    if($order->payment_status == 'paid') {
                        $invoice->fill(['status' => 'Paid', 'paid_amount' => ($invoice->due_amount + $invoice->paid_amount), 'due_amount' => 0])->save();

                        InvoiceOnlinePayment::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => $client->id,
                            'payment_method' => 'card',
                            'card_emi_month' => 0,
                            'conekta_order_id' => $order->id,
                            // 'conekta_charge_id' => $order->charges->data[0]->id,
                            'conekta_customer_id' => $customerId,
                            'conekta_payment_status' => $order->payment_status,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }
            return redirect()->route('client/bills')->with('success', 'Invoice payment successfull');
        } catch (\Conekta\ProcessingError $e){
            echo $e->getMessage();
        } catch (\Conekta\ParameterValidationError $e){
            echo $e->getMessage();
        } catch (\Conekta\Handler $e){
            echo $e->getMessage();
        }
        
    }

    public function casePayment()
    {
        # code...
    }

    public function bankPayment()
    {
        # code...
    }
}