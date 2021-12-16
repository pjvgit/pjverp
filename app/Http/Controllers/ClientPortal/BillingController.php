<?php

namespace App\Http\Controllers\ClientPortal;

use App\Firm;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\InvoiceHistory;
use App\InvoiceOnlinePayment;
use App\InvoicePayment;
use App\InvoicePaymentPlan;
use App\Invoices;
use App\Jobs\InvoicePaymentEmailJob;
use App\RequestedFund;
use App\SharedInvoice;
use App\Traits\CreditAccountTrait;
use App\User;
use Carbon\Carbon;
use Conekta\Conekta;
use Conekta\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mikehaertl\wkhtmlto\Pdf;

class BillingController extends Controller 
{
    use CreditAccountTrait;

    public function __construct()
    {
        \Conekta\Conekta::setApiKey("key_pRsoTgnsyUULMb76SDXA6w");   
    }

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
                    })->whereNotIn('status', ['Paid','Forwarded'])->with('invoiceFirstInstallment')->first();
        if(!empty($invoice)) {
            $month = $request->month;
            $payableAmount = $invoice->due_amount;
            if($invoice->invoiceFirstInstallment) {
                $payableAmount = ($invoice->invoiceFirstInstallment->adjustment > 0) ? $invoice->invoiceFirstInstallment->adjustment : $invoice->invoiceFirstInstallment->installment_amount;
            }
            return view('client_portal.billing.invoice_payment', compact('invoice', 'clientId', 'month', 'payableAmount'));
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
     * Get credit/debit card detail and do payment 
     */
    public function cardPayment(Request $request)
    {   
        // return $request->all();
        DB::beginTransaction();
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

                if(!in_array($invoice->status, ['Paid','Forwarded'])) {
                    $amount = $request->payable_amount;
                    $validOrderWithCharge = [
                        'line_items' => [
                            [
                                'name' => 'Invoice number '.$invoice->id,
                                'unit_price' => (int)$amount * 100,
                                'quantity' => 1,
                            ]
                        ],
                        'customer_info' => array(
                            'customer_id' => $customerId
                        ),
                        'currency'    => 'MXN',
                        'metadata'    => array('test' => 'extra info')
                    ];
                    if($request->emi_month == 0) {
                        $validOrderWithCharge['charges'] = [
                            [
                                'payment_method' => [
                                    'type'       => 'card',
                                    'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000",
                                    'token_id' => $request->conekta_token_id,
                                ],
                                'amount' => (int)$amount * 100,
                            ]
                        ];
                    } else {
                        $validOrderWithCharge['charges'] = [
                            [
                                'payment_method' => [
                                    'type'       => 'card',
                                    'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000",
                                    'token_id' => $request->conekta_token_id,
                                    'monthly_installments' => (int)$request->emi_month
                                ],
                                'amount' => (int)$amount * 100,
                            ]
                        ];
                    }
                    $order = \Conekta\Order::create($validOrderWithCharge);
                    if($order->payment_status == 'paid') {
                        $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => $client->id,
                            'payment_method' => 'card',
                            'amount' => $amount,
                            'card_emi_month' => 0,
                            'conekta_order_id' => $order->id,
                            // 'conekta_charge_id' => $order->charges->data[0]->id,
                            'conekta_customer_id' => $customerId,
                            'conekta_payment_status' => $order->payment_status,
                            'created_by' => auth()->id(),
                        ]);

                        $invoiceHistory=[];
                        $invoiceHistory['deposit_into'] = $request->deposit_into;
                        $request->request->add(['payment_type' => 'payment']);

                        //Insert invoice payment record.
                        $InvoicePayment=InvoicePayment::create([
                            'invoice_id' => $request->invoice_id,
                            'payment_from' => 'client',
                            'amount_paid' => $amount,
                            'payment_method' => 'Card',
                            'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                            'status'=>"0",
                            'entry_type'=>"2",
                            'payment_from_id' => $client->id,
                            'firm_id' => $client->firm_name,
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => $client->id 
                        ]);

                        //Code For installment amount
                        $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$request->invoice_id)->first();
                        if(!empty($getInstallMentIfOn)){
                            $this->installmentManagement($amount,$request->invoice_id);
                        }

                        // Update invoice paid/due amount and status
                        $this->updateInvoiceAmount($request->invoice_id);

                        $invoiceHistory = InvoiceHistory::create([
                            'invoice_id' => $request->invoice_id,
                            'acrtivity_title' => 'Payment Received',
                            'pay_method' => 'Card',
                            'amount' => $amount,
                            'responsible_user' => $client->id,
                            'payment_from' => 'online',
                            'invoice_payment_id' => $InvoicePayment->id,
                            'status' => "1",
                            'online_payment_status' => $order->payment_status,
                            'created_by' => $client->id,
                            'created_at' => Carbon::now(),
                        ]);

                        $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();
                            
                        //Add Invoice activity
                        $data=[];
                        $data['case_id'] = $invoice->case_id;
                        $data['user_id'] = $invoice->user_id;
                        $data['activity']='accepted a payment of $'.number_format($amount,2).' (Card)';
                        $data['activity_for']=$invoice->id;
                        $data['type']='invoices';
                        $data['action']='pay';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                        // For client activity
                        $data['client_id'] = $client->id;
                        $data['activity'] = 'pay a payment of $'.number_format($amount,2).' (Card) for invoice';
                        $data['is_for_client'] = 'yes';
                        $CommonController->addMultipleHistory($data);

                        // Send confirm email to client
                        $this->dispatch(new InvoicePaymentEmailJob($invoice, $client, $emailTemplateId = 29, $invoiceOnlinePayment->id, 'client'));

                        // Send confirm email to lawyer
                        $user = User::whereId($invoice->created_by)->first();
                        $this->dispatch(new InvoicePaymentEmailJob($invoice, $user, $emailTemplateId = 30, $invoiceOnlinePayment->id, 'user'));

                        DB::commit();
                        return redirect()->route('client/bills/payment/card/confirmation', encodeDecodeId($invoiceOnlinePayment->id, 'encode'));
                    }
                }
            }
        } catch (\Conekta\AuthenticationError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\ApiError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\ProcessingError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\ParameterValidationError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\Handler $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (Exception $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage ());
        }
    }

    public function cashPayment(Request $request)
    {
        // return $request->all();
        DB::beginTransaction();
        try {
            $invoice = Invoices::whereId($request->invoice_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
            $client = User::whereId(auth()->id())->first();
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
                
                if(!in_array($invoice->status, ['Paid','Forwarded'])) {
                    $amount = $request->payable_amount;
                    $validOrderWithCharge = [
                        'line_items' => [
                            [
                                'name' => 'Invoice number '.$invoice->id,
                                'unit_price' => (int)$amount * 100,
                                'quantity' => 1,
                            ]
                        ],
                        'customer_info' => array(
                            'customer_id' => $customerId
                        ),
                        'charges' => [
                            [
                                'payment_method' => [
                                    'type'       => 'oxxo_cash',
                                    'expires_at' => strtotime(Carbon::now()->addDays(7)),
                                ],
                                'amount' => (int)$amount * 100,
                            ]
                        ],
                        'currency'    => 'MXN',
                        'metadata'    => array('test' => 'extra info')
                    ];
                    $order = \Conekta\Order::create($validOrderWithCharge);
                    if($order->payment_status == 'pending_payment') {
                        $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => $client->id,
                            'payment_method' => 'cash',
                            'amount' => $amount,
                            'conekta_order_id' => $order->id,
                            'conekta_charge_id' => $order->charges[0]->id ?? Null,
                            'conekta_payment_reference_id' => $order->charges[0]->payment_method->reference ?? Null,
                            'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                            'conekta_customer_id' => $customerId,
                            'conekta_payment_status' => $order->payment_status,
                            'created_by' => auth()->id(),
                        ]);

                        $invoiceHistory=[];
                        $invoiceHistory['deposit_into'] = $request->deposit_into;
                        $request->request->add(['payment_type' => 'payment']);

                        //Insert invoice payment record.
                        $InvoicePayment=InvoicePayment::create([
                            'invoice_id' => $request->invoice_id,
                            'payment_from' => 'client',
                            'amount_paid' => $amount,
                            'payment_method' => 'Oxxo Cash',
                            'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                            'status'=>"2",
                            'entry_type'=>"2",
                            'payment_from_id' => $client->id,
                            'firm_id' => $client->firm_name,
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => $client->id 
                        ]);

                        //Code For installment amount
                        $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$request->invoice_id)->first();
                        if(!empty($getInstallMentIfOn)){
                            $this->installmentManagement($amount,$request->invoice_id);
                        }

                        // Update invoice paid/due amount and status
                        // $this->updateInvoiceAmount($request->invoice_id);

                        $invoiceHistory = InvoiceHistory::create([
                            'invoice_id' => $request->invoice_id,
                            'acrtivity_title' => 'Payment Received',
                            'pay_method' => 'Oxxo Cash',
                            'amount' => $amount,
                            'responsible_user' => $client->id,
                            'payment_from' => 'online',
                            'invoice_payment_id' => $InvoicePayment->id,
                            'status' => "0",
                            'online_payment_status' => $order->payment_status,
                            'created_by' => $client->id,
                            'created_at' => Carbon::now(),
                        ]);

                        $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();
                            
                        //Add Invoice activity
                        $data=[];
                        $data['case_id'] = $invoice->case_id;
                        $data['user_id'] = $invoice->user_id;
                        $data['activity']='accepted a payment of $'.number_format($amount,2).' (Card)';
                        $data['activity_for']=$invoice->id;
                        $data['type']='invoices';
                        $data['action']='pay';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                        // For client activity
                        $data['client_id'] = $client->id;
                        $data['activity'] = 'pay a payment of $'.number_format($amount,2).' (Card) for invoice';
                        $data['is_for_client'] = 'yes';
                        $CommonController->addMultipleHistory($data);

                        // Send confirm email to client
                        // $this->dispatch(new InvoicePaymentEmailJob($invoice, $client, $emailTemplateId = 29, $invoiceOnlinePayment->id, 'client'));

                        // Send confirm email to lawyer
                        // $user = User::whereId($invoice->created_by)->first();
                        // $this->dispatch(new InvoicePaymentEmailJob($invoice, $user, $emailTemplateId = 30, $invoiceOnlinePayment->id, 'user'));

                        DB::commit();
                        return redirect()->route('client/bills/payment/card/confirmation', encodeDecodeId($invoiceOnlinePayment->id, 'encode'));
                    }
                }
            }
        } catch (\Conekta\AuthenticationError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\ApiError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\ProcessingError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\ParameterValidationError $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (\Conekta\Handler $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage());
        } catch (Exception $e){
            DB::rollback();
            return redirect()->back()->with('error_alert', $e->getMessage ());
        }
    }

    public function bankPayment()
    {
        # code...
    }

    /**
     * Get invoice payment confirmation page
     */
    public function paymentConfirmation($onlinePaymentId)
    {
        $onlinePaymentId = encodeDecodeId($onlinePaymentId, 'decode');
        $paymentDetail = InvoiceOnlinePayment::whereId($onlinePaymentId)->first();
        $invoice = Invoices::where("id", $paymentDetail->invoice_id)
                    /* ->whereHas('invoiceShared', function($query) use($clientId) {
                        $query->where("user_id", $clientId)->where("is_shared", "yes");
                    }) */
                    ->first();
        $order = '';
        if($paymentDetail->payment_method == 'cash') {
            $order = \Conekta\Order::find($paymentDetail->conekta_order_id);
        }
        return view('client_portal.billing.invoice_payment_confirmation', compact('invoice', 'paymentDetail', 'order'));
    }
}