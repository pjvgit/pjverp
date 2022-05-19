<?php

namespace App\Http\Controllers\ClientPortal;

use App\AccountActivity;
use App\CaseClientSelection;
use App\CaseMaster;
use App\DepositIntoCreditHistory;
use App\Firm;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\InvoiceHistory;
use App\InvoiceOnlinePayment;
use App\FirmOnlinePaymentSetting;
use App\InvoicePayment;
use App\InvoicePaymentPlan;
use App\Invoices;
use App\Jobs\OnlinePaymentEmailJob;
use App\LeadAdditionalInfo;
use App\RequestedFund;
use App\RequestedFundOnlinePayment;
use App\SharedInvoice;
use App\Traits\CreditAccountTrait;
use App\Traits\OnlinePaymentTrait;
use App\Traits\TrustAccountTrait;
use App\TrustHistory;
use App\User;
use App\UserOnlinePaymentCustomerDetail;
use App\UsersAdditionalInfo;
use App\UserTrustCreditFundOnlinePayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mikehaertl\wkhtmlto\Pdf;

class BillingController extends Controller 
{
    use CreditAccountTrait, TrustAccountTrait, OnlinePaymentTrait;

    public function __construct()
    {
        // \Conekta\Conekta::setApiKey("key_pRsoTgnsyUULMb76SDXA6w"); 
    }

    /**
     * Get client portal billing
     */
    public function index()
    {
        $invoices = Invoices::whereHas('invoiceShared', function($query) {
                        $query->where("user_id", auth()->id())->where("is_shared", "yes");
                    })->whereNotIn('status', ['Forwarded', 'Paid'])->orderBy('created_at', 'desc')
                    ->with(["invoiceShared" => function($query) {
                        $query->where("user_id", auth()->id())->where("is_shared", "yes");
                    }, "invoiceInstallment"])->get();
        $forwardedInvoices = Invoices::whereHas('invoiceShared', function($query) {
                                $query->where("user_id", auth()->id())->where("is_shared", "yes");
                            })->whereIn('status', ['Forwarded', 'Paid'])->orderBy('created_at', 'desc')
                            ->with(["invoiceForwardedToInvoice", "invoiceLastPayment", "invoiceShared" => function($query) {
                                $query->where("user_id", auth()->id())->where("is_shared", "yes");
                            }])->get();
        $onlinePaymentSetting = FirmOnlinePaymentSetting::where('firm_id', auth()->user()->firm_name)->first();
        $requestFunds = RequestedFund::where('client_id', auth()->id())->where('amount_due', '>', '0.00')->orderBy('created_at', 'desc')->get();
        $requestFundsHistory = RequestedFund::where('client_id', auth()->id())->where('amount_due', '0.00')->orderBy('created_at', 'desc')->get();
        return view("client_portal.billing.index", compact('invoices', 'forwardedInvoices', 'requestFunds', 'requestFundsHistory', 'onlinePaymentSetting'));
    }

    /**
     * Show invoice detail
     */
    public function show($id)
    {
        // return encodeDecodeId('20', 'encode');
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
            $onlinePaymentSetting = FirmOnlinePaymentSetting::where('firm_id', auth()->user()->firm_name)->first();
            return view("client_portal.billing.invoice_detail", compact("invoice", "onlinePaymentSetting"));
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
            if($fundRequest->is_viewed == 'no') {
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
        } else {
            return redirect()->route("client/bills");
        }
    }

    /**
     * Get invoice payment detail screen
     */
    public function paymentDetail($type, $id, $client_id, Request $request)
    {
        $payableRecordId = encodeDecodeId($id, 'decode');
        $clientId = encodeDecodeId($client_id, 'decode');
        $invoice = ''; $fundRequest = '';
        if($type == 'invoice') {
            $invoice = Invoices::whereId($payableRecordId)->whereHas('invoiceShared', function($query) use($clientId) {
                $query->where("user_id", $clientId)->where("is_shared", "yes");
            })->whereNotIn('status', ['Paid','Forwarded'])->with('invoiceFirstInstallment')->first();
        } elseif($type == 'fundrequest') {
            $fundRequest = RequestedFund::whereId($payableRecordId)->where('status', '!=', 'paid')->first();
        } else { }
        $client = User::whereId($clientId)->whereId(auth()->id())->first();
        if((!empty($invoice) || !empty($fundRequest)) && !empty($client)) {
            $month = $request->month;
            if($fundRequest) {
                $payableAmount = str_replace( ',', '', $fundRequest->amount_due );
            } else {
                /* $payableAmount = $invoice->due_amount;
                if($invoice->invoiceFirstInstallment) {
                    $payableAmount = ($invoice->invoiceFirstInstallment->adjustment > 0) ? $invoice->invoiceFirstInstallment->adjustment : $invoice->invoiceFirstInstallment->installment_amount;
                } */
                $payableAmount = str_replace( ',', '', $invoice->getInstallmentDueAmount() );
            }
            $onlinePaymentSetting = getFirmOnlinePaymentSetting();
            return view('client_portal.billing.invoice_payment', compact('invoice', 'clientId', 'month', 'payableAmount', 'client', 'fundRequest', 'payableRecordId', 'type', 'onlinePaymentSetting'));
        } else {
            return abort(403);
        }
    }

    /**
     * Get card payment options and redirect to card detail screen
     */
    public function getCardPaymentOption(Request $request)
    {
        return redirect()->route('client/bills/payment', ['type'=>$request->type, 'id'=>$request->payable_record_id, 'client_id'=>$request->client_id, 'month'=>$request->payment_option]);
    }

    /**
     * Get credit/debit card detail and do payment 
     */
    public function cardPayment(Request $request)
    {   
        // return $request->all();
        DB::beginTransaction();
        try {
            $firmOnlinePaymentSetting = getFirmOnlinePaymentSetting();
            \Conekta\Conekta::setApiKey($firmOnlinePaymentSetting->private_key);
            $client = User::whereId($request->client_id)->with('onlinePaymentCustomerDetail')->first();
            if($client) {
                $customerId = '';
                $customerId = $this->checkUserExistOnConekta($client);
                if($customerId == '' || $customerId == 'error code') {
                    $customer = \Conekta\Customer::create([
                        "name"=> $client->full_name,
                        "email"=> $client->email,
                        "phone"=> $client->mobile_number ?? $request->phone_number,
                    ]);
                    UserOnlinePaymentCustomerDetail::create([
                        'client_id' => $client->id,
                        'conekta_customer_id' => $customer->id,
                        'created_by' => auth()->id(),
                    ]);
                    $customerId = $customer->id;
                }
                // $customerId = $client->conekta_customer_id;
                $payableAmount = $request->payable_amount;
                $validOrderWithCharge = [
                    'line_items' => [
                        [
                            'name' => ucfirst($request->type).' number '.$request->payable_record_id,
                            'unit_price' => (int)$payableAmount * 100,
                            'quantity' => 1,
                        ]
                    ],
                    'customer_info' => array(
                        'customer_id' => $customerId
                    ),
                    'currency'    => 'MXN',
                    'metadata'    => array('payment' => 'Invoice/FundRequest cash payment')
                ];
                if($request->emi_month == 0) {
                    $validOrderWithCharge['charges'] = [
                        [
                            'payment_method' => [
                                'type'       => 'card',
                                'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000",
                                'token_id' => $request->conekta_token_id,
                            ],
                            'amount' => (int)$payableAmount * 100,
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
                            'amount' => (int)$payableAmount * 100,
                        ]
                    ];
                }
                if($request->type == 'fundrequest') {
                    $fundRequest = RequestedFund::whereId($request->payable_record_id)->where('status', '!=', 'paid')->first();
                    if($fundRequest && $fundRequest->status != 'paid') {
                        $order = \Conekta\Order::create($validOrderWithCharge);
                        if($order->payment_status == 'paid') {
                            $requestOnlinePayment = RequestedFundOnlinePayment::create([
                                'fund_request_id' => $fundRequest->id,
                                'user_id' => $client->id,
                                'payment_method' => 'card',
                                'amount' => $payableAmount,
                                'card_emi_month' => $request->emi_month ?? 0,
                                'conekta_order_id' => $order->id,
                                'conekta_charge_id' => $order->charges[0]->id ?? Null,
                                'conekta_customer_id' => $customerId,
                                'conekta_payment_status' => $order->payment_status,
                                'created_by' => auth()->id(),
                                'firm_id' => $client->firm_name,
                                'conekta_order_object' => $order,
                                'status' => 'deposit',
                            ]);

                            // Update fund request paid/due amount and status
                            $remainAmt = $fundRequest->amount_due - $payableAmount;
                            $fundRequest->fill([
                                'amount_due' => $remainAmt,
                                'amount_paid' => ($fundRequest->amount_paid + $payableAmount),
                                'payment_date' => date('Y-m-d'),
                                'status' => ($remainAmt == 0) ? 'paid' : 'partial',
                                'online_payment_status' => 'paid',
                            ])->save();

                            //Deposit into trust account
                            $userAdditionalInfo = UsersAdditionalInfo::select("trust_account_balance", "credit_account_balance")->where("user_id", $client->id)->first();
                            if($fundRequest->deposit_into_type == "trust") {
                                UsersAdditionalInfo::where("user_id", $client->id)->increment('trust_account_balance', $payableAmount);
                                $trustHistory = TrustHistory::create([
                                    'client_id' => $client->id,
                                    'payment_method' => 'card',
                                    'amount_paid' => $payableAmount,
                                    'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
                                    'payment_date' => date('Y-m-d'),
                                    'fund_type' => 'diposit',
                                    'related_to_fund_request_id' => $fundRequest->id,
                                    'allocated_to_case_id' => $fundRequest->allocated_to_case_id,
                                    'created_by' => $client->id,
                                    'online_payment_status' => $order->payment_status,
                                ]);
                                $requestOnlinePayment->fill(['trust_history_id' => $trustHistory->id])->save();

                                // For allocated case trust balance
                                if($fundRequest->allocated_to_case_id != '') {
                                    CaseMaster::where('id', $fundRequest->allocated_to_case_id)->increment('total_allocated_trust_balance', $payableAmount);
                                    CaseClientSelection::where('case_id', $fundRequest->allocated_to_case_id)->where('selected_user', $client->id)->increment('allocated_trust_balance', $payableAmount);
                                }
                                // For update next/previous trust balance
                                $this->updateNextPreviousTrustBalance($trustHistory->client_id);
                                
                                // Add fund request account activity
                                $AccountActivityData = AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
                                AccountActivity::create([
                                    'user_id' => $client->id,
                                    'case_id'=> $fundRequest->allocated_to_case_id ?? Null,
                                    'credit_amount' => $payableAmount ?? 0.00,
                                    'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                                    'entry_date' => date('Y-m-d'),
                                    'payment_method' => "Card",
                                    'payment_type' => "deposit",
                                    'pay_type' => "trust",
                                    'from_pay' => "online",
                                    'trust_history_id' => $trustHistory->id ?? Null,
                                    'firm_id' => $client->firm_name,
                                    'section' => "request",
                                    'related_to' => $fundRequest->id,
                                    'created_by'=>$client->id,
                                ]);

                            } else {
                                // Deposit into credit account
                                UsersAdditionalInfo::where("user_id", $client->id)->increment('credit_account_balance', $payableAmount);
                                $creditHistory = DepositIntoCreditHistory::create([
                                    'user_id' => $client->id,
                                    'deposit_amount' => $payableAmount,
                                    'payment_method' => "card",
                                    'payment_date' => date("Y-m-d"),
                                    'total_balance' => @$userAdditionalInfo->credit_account_balance,
                                    'payment_type' => "deposit",
                                    'firm_id' => $client->firm_name,
                                    'related_to_fund_request_id' => $fundRequest->id,
                                    'created_by' => $client->id,
                                    'online_payment_status' => $order->payment_status,
                                ]);
                                $requestOnlinePayment->fill(['credit_history_id' => $creditHistory->id])->save();

                                // For update next/previous credit balance
                                $this->updateNextPreviousCreditBalance($client->id);

                                // Add fund request account activity
                                $AccountActivityData=AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                                AccountActivity::create([
                                    'user_id' => $client->id,
                                    'credit_amount' => $payableAmount ?? 0.00,
                                    'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                                    'entry_date' => date('Y-m-d'),
                                    'payment_method' => 'Card',
                                    'payment_type' => "deposit",
                                    'trust_history_id' => $creditHistory->id ?? Null,
                                    'status' => "unsent",
                                    'pay_type' => "client",
                                    'from_pay' => "online",
                                    'firm_id' => $client->firm_name,
                                    'section' => "request",
                                    'related_to' => $fundRequest->id,
                                    'created_by' => $client->id,
                                ]);
                            }
                            
                            $data=[];
                            $data['user_id'] = $client->id;
                            $data['client_id'] = $client->id;
                            $data['deposit_for'] = $client->id;
                            $data['deposit_id']=$fundRequest->id;
                            $data['activity']="pay a payment of $".number_format($payableAmount, 2)." (Card) for deposit request";
                            $data['type']='fundrequest';
                            $data['action']='pay';
                            $CommonController= new CommonController();
                            $CommonController->addMultipleHistory($data);

                            // For client activity
                            $data['activity'] = 'pay a payment of $'.number_format($payableAmount,2).' (Card) for fund request';
                            $data['is_for_client'] = 'yes';
                            $CommonController->addMultipleHistory($data);

                            // Send confirm email to client
                            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $client, $emailTemplateId = 30, $requestOnlinePayment, 'client', 'fundrequest'));

                            // Send confirm email to lawyer/invoice created user
                            $user = User::whereId($fundRequest->created_by)->first();
                            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $user, $emailTemplateId = 31, $requestOnlinePayment, 'user', 'fundrequest'));
                            
                            // Send confirm email to firm owner/lead attorney
                            $firmOwner = User::where('firm_name', $client->firm_name)->where('parent_user', 0)->first();
                            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $firmOwner, $emailTemplateId = 31, $requestOnlinePayment, 'user', 'fundrequest'));

                            DB::commit();
                            return redirect()->route('client/bills/payments/confirmation', ['fundrequest', encodeDecodeId($requestOnlinePayment->id, 'encode')]);
                        }
                    }
                } else {
                    $invoice = Invoices::whereId($request->payable_record_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
                    if($invoice && !in_array($invoice->status, ['Paid','Forwarded'])) {                    
                        $order = \Conekta\Order::create($validOrderWithCharge);
                        if($order->payment_status == 'paid') {
                            $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                                'invoice_id' => $invoice->id,
                                'user_id' => $client->id,
                                'payment_method' => 'card',
                                'amount' => $payableAmount,
                                'card_emi_month' => $request->emi_month ?? 0,
                                'conekta_order_id' => $order->id,
                                'conekta_charge_id' => $order->charges[0]->id ?? Null,
                                'conekta_customer_id' => $customerId,
                                'conekta_payment_status' => $order->payment_status,
                                'created_by' => auth()->id(),
                                'firm_id' => $client->firm_name,
                                'conekta_order_object' => $order,
                            ]);

                            //Insert invoice payment record.
                            $InvoicePayment=InvoicePayment::create([
                                'invoice_id' => $invoice->id,
                                'payment_from' => 'client',
                                'amount_paid' => $payableAmount,
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
                            $getInstallMentIfOn=InvoicePaymentPlan::where("invoice_id",$invoice->id)->first();
                            if(!empty($getInstallMentIfOn)){
                                $this->updateInvoiceInstallmentAmount($payableAmount,$invoice->id, $onlinePaymentStatus = 'paid');
                            }

                            // Update invoice online payment status
                            $invoice->fill(['online_payment_status' => $order->payment_status])->save();

                            // Update invoice paid/due amount and status
                            $this->updateInvoiceAmount($invoice->id);

                            $invoiceHistory = InvoiceHistory::create([
                                'invoice_id' => $invoice->id,
                                'acrtivity_title' => 'Payment Received',
                                'pay_method' => 'Card',
                                'amount' => $payableAmount,
                                'responsible_user' => $client->id,
                                'payment_from' => 'client_online',
                                'deposit_into_id' => $client->id,
                                'invoice_payment_id' => $InvoicePayment->id,
                                'status' => "1",
                                'online_payment_status' => $order->payment_status,
                                'created_by' => $client->id,
                                'created_at' => Carbon::now(),
                            ]);
                            $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();

                            $AccountActivityData=AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
                            AccountActivity::create([
                                'user_id' => $client->id,
                                'case_id' => $invoice->case_id,
                                'credit_amount' => $payableAmount ?? 0.00,
                                'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                                'entry_date' => date('Y-m-d'),
                                'payment_method' => 'Card',
                                'payment_type' => "payment",
                                'invoice_history_id' => $invoiceHistory->id ?? Null,
                                'status' => "unsent",
                                'pay_type' => "client",
                                'from_pay' => "online",
                                'firm_id' => $client->firm_name,
                                'section' => "invoice",
                                'related_to' => $invoice->id,
                                'created_by' => $client->id,
                            ]);
                                
                            //Add Invoice activity
                            $data=[];
                            $data['case_id'] = $invoice->case_id;
                            $data['user_id'] = $invoice->user_id;
                            $data['activity']='accepted a payment of $'.number_format($payableAmount,2).' (Card)';
                            $data['activity_for']=$invoice->id;
                            $data['type']='invoices';
                            $data['action']='pay';
                            $CommonController= new CommonController();
                            $CommonController->addMultipleHistory($data);

                            // For client activity
                            $data['client_id'] = $client->id;
                            $data['activity'] = 'pay a payment of $'.number_format($payableAmount,2).' (Card) for invoice';
                            $data['is_for_client'] = 'yes';
                            $CommonController->addMultipleHistory($data);

                            // Send confirm email to client
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 30, $invoiceOnlinePayment, 'client', 'invoice'));

                            // Send confirm email to lawyer/invoice created user
                            $user = User::whereId($invoice->created_by)->first();
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $user, $emailTemplateId = 31, $invoiceOnlinePayment, 'user', 'invoice'));
                            
                            // Send confirm email to firm owner/lead attorney
                            $firmOwner = User::where('firm_name', $client->firm_name)->where('parent_user', 0)->first();
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $firmOwner, $emailTemplateId = 31, $invoiceOnlinePayment, 'user', 'invoice'));

                            DB::commit();
                            return redirect()->route('client/bills/payments/confirmation', ['invoice', encodeDecodeId($invoiceOnlinePayment->id, 'encode')]);
                        }
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

    /**
     * Get cash payment detail and do payment
     */
    public function cashPayment(Request $request)
    {
        // return $request->all();
        DB::beginTransaction();
        try {
            $firmOnlinePaymentSetting = getFirmOnlinePaymentSetting();
            \Conekta\Conekta::setApiKey($firmOnlinePaymentSetting->private_key);
            $client = User::whereId(auth()->id())->with('onlinePaymentCustomerDetail')->first();
            if($client) {
                $customerId = '';
                $customerId = $this->checkUserExistOnConekta($client);
                if($customerId == '' || $customerId == 'error code') {
                    $customer = \Conekta\Customer::create([
                        "name"=> $client->full_name,
                        "email"=> $client->email,
                        "phone"=> $request->phone_number ?? $client->mobile_number,
                    ]);
                    UserOnlinePaymentCustomerDetail::create([
                        'client_id' => $client->id,
                        'conekta_customer_id' => $customer->id,
                        'created_by' => auth()->id(),
                    ]);
                    $customerId = $customer->id;
                }
            /* if($client) {
                if(empty($client->conekta_customer_id)) {
                    $customer = \Conekta\Customer::create([
                                    "name"=> $request->name ?? $client->full_name,
                                    "email"=> $client->email,
                                    "phone"=> $request->phone_number ?? $client->mobile_number,
                                ]);
                    $client->fill(['conekta_customer_id' => $customer->id])->save();
                    $client->refresh();
                }
                $customerId = $client->conekta_customer_id; */
                
                $amount = $request->payable_amount;
                $validOrderWithCharge = [
                    'line_items' => [
                        [
                            'name' => ucfirst($request->type).' number '.$request->payable_record_id,
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
                    'metadata'    => array('payment' => 'Invoice/FundRequest cash payment')
                ];
                if($request->type == 'fundrequest') {
                    $fundRequest = RequestedFund::whereId($request->payable_record_id)->where('status', '!=', 'paid')->first();
                    if($fundRequest && $fundRequest->status != 'paid') {
                        $order = \Conekta\Order::create($validOrderWithCharge);
                        if($order->payment_status == 'pending_payment') {
                            $requestOnlinePayment = RequestedFundOnlinePayment::create([
                                'fund_request_id' => $fundRequest->id,
                                'user_id' => $client->id,
                                'payment_method' => 'cash',
                                'amount' => $amount,
                                'card_emi_month' => $request->emi_month ?? 0,
                                'conekta_order_id' => $order->id,
                                'conekta_charge_id' => $order->charges[0]->id ?? Null,
                                'conekta_payment_reference_id' => $order->charges[0]->payment_method->reference ?? Null,
                                'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                                'conekta_customer_id' => $customerId,
                                'conekta_payment_status' => $order->payment_status,
                                'created_by' => auth()->id(),
                                'firm_id' => $client->firm_name,
                                'conekta_order_object' => $order,
                                'status' => 'deposit',
                            ]);

                            // Update fund request paid/due amount and status
                            $fundRequest->fill([
                                'online_payment_status' => 'pending',
                            ])->save();

                            // Cash payment reference email to client
                            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $client, $emailTemplateId = 32, $requestOnlinePayment, 'cash_reference_client', 'fundrequest'));

                            DB::commit();
                            return redirect()->route('client/bills/payments/confirmation', ['fundrequest', encodeDecodeId($requestOnlinePayment->id, 'encode')]);
                        }
                    }
                }
                else {
                    $invoice = Invoices::whereId($request->payable_record_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
                    if($invoice && !in_array($invoice->status, ['Paid','Forwarded'])) {
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
                                'firm_id' => $client->firm_name,
                                'conekta_order_object' => $order,
                            ]);

                            $invoice->fill(['online_payment_status' => 'pending'])->save();

                            $invoiceHistory=[];
                            $invoiceHistory['deposit_into'] = $request->deposit_into;
                            $request->request->add(['payment_type' => 'payment']);

                            //Insert invoice payment record.
                            $InvoicePayment=InvoicePayment::create([
                                'invoice_id' => $invoice->id,
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

                            $invoiceHistory = InvoiceHistory::create([
                                'invoice_id' => $invoice->id,
                                'acrtivity_title' => 'Payment Pending',
                                'pay_method' => 'Oxxo Cash',
                                'amount' => $amount,
                                'responsible_user' => $client->id,
                                'payment_from' => 'client_online',
                                'deposit_into_id' => $client->id,
                                'invoice_payment_id' => $InvoicePayment->id,
                                'status' => "0",
                                'online_payment_status' => 'pending',
                                'created_by' => $client->id,
                                'created_at' => Carbon::now(),
                            ]);

                            $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();
                                
                            // Cash payment reference email to client
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 32, $invoiceOnlinePayment, 'cash_reference_client', 'invoice'));

                            DB::commit();
                            return redirect()->route('client/bills/payments/confirmation', ['invoice', encodeDecodeId($invoiceOnlinePayment->id, 'encode')]);
                        }
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

    /**
     * Get bank transfer payment detail and do payment
     */
    public function bankPayment(Request $request)
    {
        // return $request->all();
        DB::beginTransaction();
        try {
            $firmOnlinePaymentSetting = getFirmOnlinePaymentSetting();
            \Conekta\Conekta::setApiKey($firmOnlinePaymentSetting->private_key);
            $client = User::whereId(auth()->id())->with('onlinePaymentCustomerDetail')->first();
            if($client) {
                $customerId = '';
                $customerId = $this->checkUserExistOnConekta($client);
                if($customerId == '' || $customerId == 'error code') {
                    $customer = \Conekta\Customer::create([
                        "name"=> $client->full_name,
                        "email"=> $client->email,
                        "phone"=> $client->mobile_number ?? $request->bt_phone_number,
                    ]);
                    UserOnlinePaymentCustomerDetail::create([
                        'client_id' => $client->id,
                        'conekta_customer_id' => $customer->id,
                        'created_by' => auth()->id(),
                    ]);
                    $customerId = $customer->id;
                }
            /* if($client) {
                if(empty($client->conekta_customer_id)) {
                    $customer = \Conekta\Customer::create([
                                    "name"=> $client->full_name ?? $request->bt_name,
                                    "email"=> $client->email,
                                    "phone"=> $client->mobile_number ?? $request->bt_phone_number,
                                ]);
                    $client->fill(['conekta_customer_id' => $customer->id])->save();
                    $client->refresh();
                }
                $customerId = $client->conekta_customer_id; */
                $amount = $request->payable_amount;
                $validOrderWithCharge = [
                    'line_items' => [
                        [
                            'name' => ucfirst($request->type).' number '.$request->payable_record_id,
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
                                'type'       => 'spei',
                                'expires_at' => strtotime(Carbon::now()->addDays(7)),
                            ],
                            'amount' => (int)$amount * 100,
                        ]
                    ],
                    'currency'    => 'MXN',
                    'metadata'    => array('payment' => 'Invoice/FundRequest bank payment')
                ];
                if($request->type == 'fundrequest') {
                    $fundRequest = RequestedFund::whereId($request->payable_record_id)->where('status', '!=', 'paid')->first();
                    if($fundRequest && $fundRequest->status != 'paid') {
                        $order = \Conekta\Order::create($validOrderWithCharge);
                        if($order->payment_status == 'pending_payment') {
                            $requestOnlinePayment = RequestedFundOnlinePayment::create([
                                'fund_request_id' => $fundRequest->id,
                                'user_id' => $client->id,
                                'payment_method' => 'bank transfer',
                                'amount' => $amount,
                                'card_emi_month' => $request->emi_month ?? 0,
                                'conekta_order_id' => $order->id,
                                'conekta_charge_id' => $order->charges[0]->id ?? Null,
                                'conekta_payment_reference_id' => $order->charges[0]->payment_method->clabe ?? Null, // CLABE number for bank transfer
                                'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                                'conekta_customer_id' => $customerId,
                                'conekta_payment_status' => $order->payment_status,
                                'created_by' => auth()->id(),
                                'firm_id' => $client->firm_name,
                                'conekta_order_object' => $order,
                                'status' => 'deposit',
                            ]);

                            // Update fund request paid/due amount and status
                            $fundRequest->fill([
                                'online_payment_status' => 'pending',
                            ])->save();

                            // Bank payment reference email to client
                            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $client, $emailTemplateId = 35, $requestOnlinePayment, 'bank_reference_client', 'fundrequest'));

                            DB::commit();
                            return redirect()->route('client/bills/payments/confirmation', ['fundrequest', encodeDecodeId($requestOnlinePayment->id, 'encode')]);
                        }
                    }
                }
                else {
                    $invoice = Invoices::whereId($request->payable_record_id)->whereNotIn('status', ['Paid','Forwarded'])->first();
                    if($invoice && !in_array($invoice->status, ['Paid','Forwarded'])) {
                        $order = \Conekta\Order::create($validOrderWithCharge);
                        if($order->payment_status == 'pending_payment') {
                            $invoiceOnlinePayment = InvoiceOnlinePayment::create([
                                'invoice_id' => $invoice->id,
                                'user_id' => $client->id,
                                'payment_method' => 'bank transfer',
                                'amount' => $amount,
                                'conekta_order_id' => $order->id,
                                'conekta_charge_id' => $order->charges[0]->id ?? Null,
                                'conekta_payment_reference_id' => $order->charges[0]->payment_method->clabe ?? Null, // CLABE number for bank transfer
                                'conekta_reference_expires_at' => Carbon::createFromTimestamp($order->charges[0]->payment_method->expires_at)->toDateTimeString() ?? Null,
                                'conekta_customer_id' => $customerId,
                                'conekta_payment_status' => $order->payment_status,
                                'created_by' => auth()->id(),
                                'firm_id' => $client->firm_name,
                                'conekta_order_object' => $order,
                            ]);

                            $invoice->fill(['online_payment_status' => 'pending'])->save();

                            //Insert invoice payment record.
                            $InvoicePayment=InvoicePayment::create([
                                'invoice_id' => $invoice->id,
                                'payment_from' => 'client',
                                'amount_paid' => $amount,
                                'payment_method' => 'SPEI',
                                'payment_date'=>convertDateToUTCzone(date("Y-m-d"), auth()->user()->user_timezone),
                                'status'=>"2",
                                'entry_type'=>"2",
                                'payment_from_id' => $client->id,
                                'firm_id' => $client->firm_name,
                                'created_at' => date('Y-m-d H:i:s'),
                                'created_by' => $client->id 
                            ]);

                            $invoiceHistory = InvoiceHistory::create([
                                'invoice_id' => $invoice->id,
                                'acrtivity_title' => 'Payment Pending',
                                'pay_method' => 'SPEI',
                                'amount' => $amount,
                                'responsible_user' => $client->id,
                                'payment_from' => 'client_online',
                                'deposit_into_id' => $client->id,
                                'invoice_payment_id' => $InvoicePayment->id,
                                'status' => "0",
                                'online_payment_status' => 'pending',
                                'created_by' => $client->id,
                                'created_at' => Carbon::now(),
                            ]);

                            $invoiceOnlinePayment->fill(['invoice_history_id' => $invoiceHistory->id])->save();

                            // Bank payment reference email to client
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 35, $invoiceOnlinePayment, 'bank_reference_client', 'invoice'));

                            DB::commit();
                            return redirect()->route('client/bills/payments/confirmation', ['invoice', encodeDecodeId($invoiceOnlinePayment->id, 'encode')]);
                        }
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

    /**
     * Get invoice payment confirmation page
     */
    public function paymentConfirmation($payableType, $online_payment_id)
    {
        $onlinePaymentId = encodeDecodeId($online_payment_id, 'decode');
        $fundRequest = ''; $invoice = '';
        if($payableType == 'fundrequest') {
            $paymentDetail = RequestedFundOnlinePayment::whereId($onlinePaymentId)->with('firmDetail')->first();
            $fundRequest = RequestedFund::whereId($paymentDetail->fund_request_id)->first();
        } else {
            $paymentDetail = InvoiceOnlinePayment::whereId($onlinePaymentId)->with('firmDetail')->first();
            $invoice = Invoices::where("id", $paymentDetail->invoice_id)->first();
        }
        return view('client_portal.billing.invoice_payment_confirmation', compact('invoice', 'paymentDetail', 'fundRequest', 'payableType'));
    }

    /**
     * To check cash/bank payment confirmation and expires
     */
    public function paymentWebhook()
    {
        Log::info("webhook function enter");
        try {
            dbStart();
            $body = @file_get_contents('php://input');
            $data = json_decode($body);
            http_response_code(200); // Return 200 OK 
            Log::info("webhook response: ". json_encode(@$data->data->object));
            Log::info("webhook called type: ". $data->type);
            switch ($data->type) {
                case 'order.paid':
                    Log::info("conekta order paid called");
                    $this->orderPaidConfirm($data);
                    break;
                case 'order.expired':
                    Log::info("conekta order expired called");
                    $this->conektaReferenceExpired($data);
                    break;
                case 'order.partially_refunded':
                    Log::info("conekta order refunded called");
                    $this->conektaOrderRefund($data);
                    break;
                default:
                    Log::info("conekta default called");
                    break;
            }
            dbCommit();
        } catch (Exception $e) {
            dbEnd();
            Log::info('Payment webhook failed: '. $e->getMessage());
        }
    }

    /**
     * Cash/bank payment webhook confirmation
     */
    public function orderPaidConfirm($data)
    {
        Log::info("charge paid function enter");
        try {
            dbStart();
            Log::info("conekta object order id: ". @$data->data->object->id);
            $response = $data->data;
            $paymentDetail = InvoiceOnlinePayment::where("conekta_order_id", $response->object->id)->where('conekta_payment_status', 'pending_payment')->first();
            if($paymentDetail) {
                $this->invoiceOrderConfirm($paymentDetail, $response);
            } else {
                $paymentDetail = RequestedFundOnlinePayment::where("conekta_order_id", $response->object->id)->where('conekta_payment_status', 'pending_payment')->first();
                Log::info("Fundrequest online payment detail: ". @$paymentDetail);
                if($paymentDetail) {
                    $this->fundRequestOrderConfirm($paymentDetail, $response);
                } else {
                    $paymentDetail = UserTrustCreditFundOnlinePayment::where("conekta_order_id", $response->object->id)->where('conekta_payment_status', 'pending_payment')->first();
                    Log::info("User trust/credit fund online payment detail: ". @$paymentDetail);
                    if($paymentDetail) {
                        $this->fundOrderConfirm($paymentDetail, $response);
                    }
                }
            }
            dbCommit();
            Log::info('payment webhook successfull');
        } catch (Exception $e) {
            dbEnd();
            Log::info('Cash Payment webhook failed: '. $e->getMessage());
        }
    }
    /**
     * Cash/bank order expired webhook
     */
    public function conektaReferenceExpired($data)
    {
        Log::info("reference expired function enter");
        try {
            dbStart();
            Log::info("conekta order id: ". $data->data->object->id);
            $response = $data->data;
            $paymentDetail = InvoiceOnlinePayment::where("conekta_order_id", $response->object->id)->where('conekta_payment_status', 'pending_payment')->first();
            if($paymentDetail) {
                DB::table("invoice_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)
                        ->update(['conekta_payment_status' => 'expired'/* , 'conekta_order_object' => json_encode($data) */]);

                $invoice = Invoices::whereId($paymentDetail->invoice_id)->first();
                $invoiceHistory = InvoiceHistory::whereId($paymentDetail->invoice_history_id)->first();
                if($invoice && $invoiceHistory) {
                    // Update invoice payment status
                    DB::table("invoice_payment")->whereId($invoiceHistory->invoice_payment_id)->update(['status' => '0']);

                    // Update invoice history status
                    DB::table("invoice_history")->whereId($paymentDetail->invoice_history_id)->update(['online_payment_status' => 'expired']);
                    
                    // Send reference expired email to client
                    $client = User::whereId($paymentDetail->user_id)->first();
                    if($paymentDetail->payment_method == 'cash') {
                        $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 40, $paymentDetail, 'cash_reference_expired_client', 'invoice'));
                        Log::info('cash reference expired webhook successfull');
                    } else if($paymentDetail->payment_method == 'bank transfer') {
                        $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 42, $paymentDetail, 'bank_reference_expired_client', 'invoice'));
                        Log::info('bank reference expired webhook successfull');
                    } else {
                    }
                }
            } else {
                $paymentDetail = RequestedFundOnlinePayment::where("conekta_order_id", $response->object->id)->where('conekta_payment_status', 'pending_payment')->first();
                Log::info("Fundrequest online payment detail: ". @$paymentDetail);
                if($paymentDetail) {
                    DB::table("requested_fund_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)
                            ->update(['conekta_payment_status' => 'expired'/* , 'conekta_order_object' => json_encode($data) */]);

                    $invoice = Invoices::whereId($paymentDetail->invoice_id)->first();
                    $invoiceHistory = InvoiceHistory::whereId($paymentDetail->invoice_history_id)->first();
                    if($invoice && $invoiceHistory) {
                        // Update invoice payment status
                        DB::table("invoice_payment")->whereId($invoiceHistory->invoice_payment_id)->update(['status' => '0']);

                        // Update invoice history status
                        DB::table("invoice_history")->whereId($paymentDetail->invoice_history_id)->update(['online_payment_status' => 'expired']);
                        
                        // Send reference expired email to client
                        $client = User::whereId($paymentDetail->user_id)->first();
                        if($paymentDetail->payment_method == 'cash') {
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 40, $paymentDetail, 'cash_reference_expired_client', 'invoice'));
                            Log::info('cash reference expired webhook successfull');
                        } else if($paymentDetail->payment_method == 'bank transfer') {
                            $this->dispatch(new OnlinePaymentEmailJob($invoice, $client, $emailTemplateId = 42, $paymentDetail, 'bank_reference_expired_client', 'invoice'));
                            Log::info('bank reference expired webhook successfull');
                        } else { }
                    }
                }
            }
            Log::info('reference expired webhook end');
        } catch (Exception $e) {
            dbEnd();
            Log::info('Reference expired webhook failed: '. $e->getMessage());
        }
    }

    /**
     * Card payment refund webhook
     */
    public function conektaOrderRefund($data)
    {
        Log::info("Conekta order refund response: ". json_encode($data));
    }

    /**
     * Invoice cash/bank payment, order confirmed
     */
    public function invoiceOrderConfirm($paymentDetail, $response)
    {
        Log::info("Invoice online payment detail: ". @$paymentDetail);
        Log::info("invoice cash payment");
        DB::table("invoice_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)
                ->update(['conekta_payment_status' => 'paid', 'paid_at' => Carbon::now()/* , 'conekta_order_object' => json_encode($data) */]);

        $invoice = Invoices::where("id", $paymentDetail->invoice_id)->first();
        $invoiceHistory = DB::table("invoice_history")->where("id", $paymentDetail->invoice_history_id)->first();
        if($invoiceHistory) {
            Log::info("oxxo cash invoice status: ". $invoice->status);
            if(!empty($invoice) && in_array($invoice->status, ["Paid", "Forwarded"])) {
                // Update invoice history status
                DB::table("invoice_history")->whereId($paymentDetail->invoice_history_id)->update(['acrtivity_title' => 'Payment Received', 'status' => '5', 'online_payment_status' => 'paid']);

                // Update invoice online payment status
                $anyPending = InvoiceHistory::where('invoice_id', $invoice->id)->where('online_payment_status', 'pending')->first();
                if(empty($anyPending)) {
                    DB::table('invoices')->where('id', $invoice->id)->update(["online_payment_status" => 'paid']);
                }

                /* $caseId = ($invoice->case_id != 0 && $invoice->is_lead_invoice == 'no') ? $invoice->case_id : Null; 
                $leadCaseId = ($invoice->case_id == Null || $invoice->is_lead_invoice == 'yes') ? $invoice->user_id : Null;

                // Get user additional info
                $userAdditionalInfo = UsersAdditionalInfo::select("trust_account_balance", "credit_account_balance")->where("user_id", $paymentDetail->user_id)->first();
                $paymentMethod = ($paymentDetail->payment_method == 'cash') ? 'Oxxo Cash' : (($paymentDetail->payment_method == 'bank transfer') ? 'SPEI' : '');
                DB::table('users_additional_info')->where("user_id", $paymentDetail->user_id)->increment('trust_account_balance', $paymentDetail->amount);
                Log::info("oxxo cash move payment to trust account");
                $trustHistoryId = DB::table('trust_history')->insertGetId([
                    'client_id' => $paymentDetail->user_id,
                    'payment_method' => $paymentMethod,
                    'amount_paid' => $paymentDetail->amount,
                    'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
                    'payment_date' => date('Y-m-d'),
                    'fund_type' => 'diposit',
                    'online_payment_status' => 'paid',
                    'related_to_invoice_id' => $invoice->id,
                    "allocated_to_case_id" => $caseId ?? Null,
                    "allocated_to_lead_case_id" => $leadCaseId ?? Null,
                    'created_by' => $paymentDetail->user_id,
                    'created_at' => Carbon::now(),
                ]);
                if(isset($caseId)) {
                    DB::table('case_master')->where('id', $caseId)->increment("total_allocated_trust_balance", $paymentDetail->amount);
                    // CaseMaster::where('id', $caseId)->increment('total_allocated_trust_balance', $paymentDetail->amount);
                    DB::table("case_client_selection")->where("case_id", $caseId)->where("selected_user", $paymentDetail->user_id)->increment('allocated_trust_balance', $paymentDetail->amount);
                    // CaseClientSelection::where('case_id', $caseId)->where('selected_user', $paymentDetail->user_id)->increment('allocated_trust_balance', $paymentDetail->amount);
                }
                if(isset($leadCaseId)) {
                    DB::table("lead_additional_info")->where("user_id", $leadCaseId)->increment("allocated_trust_balance", $paymentDetail->amount);
                    // LeadAdditionalInfo::where("user_id", $leadCaseId)->increment('allocated_trust_balance', $paymentDetail->amount);
                }
                $paymentDetail->fill(['trust_history_id' => $trustHistoryId])->save();
                // For update next/previous trust balance
                $this->updateNextPreviousTrustBalance($paymentDetail->user_id); */
                $this->savePaymentToTrustFund($paymentDetail, $invoice, $paymentDetail->amount);
            } else {
                Log::info("receive oxxo cash payment");
                // Update invoice payment status
                DB::table("invoice_payment")->whereId($invoiceHistory->invoice_payment_id)->update(['status' => '0']);

                // Update invoice history status
                DB::table("invoice_history")->whereId($paymentDetail->invoice_history_id)->update(['acrtivity_title' => 'Payment Received', 'status' => '1', 'online_payment_status' => 'paid']);

                if($invoice->due_amount < $paymentDetail->amount) {
                    $dueAmt = $invoice->due_amount;
                    // Extra amount will move to trust allocated fund
                    $extraAmt = $paymentDetail->amount - $invoice->due_amount;
                    $this->savePaymentToTrustFund($paymentDetail, $invoice, $extraAmt);
                } else {
                    $dueAmt = $paymentDetail->amount;
                }
                // Update invoice status and amount
                DB::table("invoices")->whereId($paymentDetail->invoice_id)->update(['online_payment_status' => 'paid']);

                // Update invoice/invoice installment amount
                $getInstallMentIfOn = InvoicePaymentPlan::where("invoice_id",$invoice->id)->first();
                if(!empty($getInstallMentIfOn)){
                    $this->updateInvoiceInstallmentAmount($dueAmt, $invoice->id, $onlinePaymentStatus = 'paid');
                }
                // $this->updateInvoiceAmount($invoice->id);
                $invDueAmt = $invoice->due_amount - $dueAmt;
                $invoice->fill([
                    'paid_amount'=> $invoice->paid_amount + $dueAmt,
                    'due_amount'=> $invDueAmt,
                    'status' => ($invDueAmt == 0) ? "Paid" : $invoice->status,
                ])->save();

                $paymentMethod = ($paymentDetail->payment_method == 'cash') ? 'Oxxo Cash' : (($paymentDetail->payment_method == 'bank transfer') ? 'SPEI' : '');
                // For lawyer activity
                DB::table("all_history")->insert([
                    'user_id' => $paymentDetail->user_id,
                    'case_id' => $invoice->case_id,
                    'activity_for' => $invoice->id,
                    'activity' => "pay a payment of $".number_format($paymentDetail->amount,2)." (".$paymentMethod.") for invoice",
                    'type' => 'invoices',
                    'action' => 'pay',
                    'created_by' => $paymentDetail->user_id,
                    'created_at' => Carbon::now(),
                ]);

                // For client activity
                DB::table("all_history")->insert([
                    'case_id' => $invoice->case_id,
                    'user_id' => $paymentDetail->user_id,
                    'client_id' => $paymentDetail->user_id,
                    'activity_for' => $invoice->id,
                    'activity' => "pay a payment of $".number_format($paymentDetail->amount,2)." (".$paymentMethod.") for invoice",
                    'type' => 'invoices',
                    'action' => 'pay',
                    'is_for_client' => 'yes',
                    'created_by' => $paymentDetail->user_id,
                    'created_at' => Carbon::now(),
                ]);
                if($paymentDetail->payment_method == 'cash') {
                    // Send confirmation email to client
                    $client = User::whereId($paymentDetail->user_id)->first();
                    $this->dispatch(new OnlinePaymentEmailJob(null, $client, $emailTemplateId = 33, $paymentDetail, 'cash_confirm_client', 'invoice'));

                    // Send confirmation email to invoice created user
                    $user = User::whereId($invoice->created_by)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $user, $emailTemplateId = 34, $paymentDetail, 'cash_confirm_user', 'invoice'));

                    // Send confirm email to firm owner/lead attorney
                    $firmOwner = User::where('firm_name', $paymentDetail->firm_id)->where('parent_user', 0)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $firmOwner, $emailTemplateId = 34, $paymentDetail, 'cash_confirm_user', 'invoice'));

                    Log::info('invoice cash payment webhook successfull');

                } else if($paymentDetail->payment_method == 'bank transfer') {
                    // Send confirmation email to client
                    $client = User::whereId($paymentDetail->user_id)->first();
                    $this->dispatch(new OnlinePaymentEmailJob(null, $client, $emailTemplateId = 36, $paymentDetail, 'bank_confirm_client', 'invoice'));

                    // Send confirmation email to invoice created user
                    $user = User::whereId($invoice->created_by)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $user, $emailTemplateId = 37, $paymentDetail, 'bank_confirm_user', 'invoice'));

                    // Send confirm email to firm owner/lead attorney
                    $firmOwner = User::where('firm_name', $paymentDetail->firm_id)->where('parent_user', 0)->first();
                    $this->dispatch(new OnlinePaymentEmailJob($invoice, $firmOwner, $emailTemplateId = 37, $paymentDetail, 'bank_confirm_user', 'invoice'));

                    Log::info('invoice bank payment webhook successfull');
                } else {
                }
            }
        } else {
            Log::info("cash invoice & invoice history not found");
        }
    }

    /**
     * Fund request cash/bank payment, order confirmed
     */
    public function fundRequestOrderConfirm($paymentDetail, $response)
    {
        DB::table("requested_fund_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)
            ->update(['conekta_payment_status' => 'paid', 'paid_at' => Carbon::now()/* , 'conekta_order_object' => json_encode($data) */]);

        $fundRequest = RequestedFund::whereId($paymentDetail->fund_request_id)->first();
        // Get user additional info
        $userAdditionalInfo = UsersAdditionalInfo::select("trust_account_balance", "credit_account_balance")->where("user_id", $paymentDetail->user_id)->first();
        $paymentMethod = ($paymentDetail->payment_method == 'cash') ? 'Oxxo Cash' : (($paymentDetail->payment_method == 'bank transfer') ? 'SPEI' : '');
        if(!empty($fundRequest) || $fundRequest->status == "paid") {
                DB::table('users_additional_info')->where("user_id", $paymentDetail->user_id)->increment('trust_account_balance', $paymentDetail->amount);
                $trustHistoryId = DB::table('trust_history')->insertGetId([
                    'client_id' => $paymentDetail->user_id,
                    'payment_method' => $paymentMethod,
                    'amount_paid' => $paymentDetail->amount,
                    'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
                    'payment_date' => date('Y-m-d'),
                    'fund_type' => 'diposit',
                    'online_payment_status' => 'paid',
                    'related_to_fund_request_id' => $paymentDetail->fund_request_id,
                    'created_by' => $paymentDetail->user_id,
                    'created_at' => Carbon::now(),
                ]);
                $paymentDetail->fill(['trust_history_id' => $trustHistoryId])->save();
                // For update next/previous trust balance
                $this->updateNextPreviousTrustBalance($paymentDetail->user_id);
        } else {
            // Update fund request paid/due amount and status
            $remainAmt = $fundRequest->amount_due - $paymentDetail->amount;
            DB::table("requested_fund")->where("id", $paymentDetail->fund_request_id)
                ->update([
                    'amount_due' => $remainAmt,
                    'amount_paid' => ($fundRequest->amount_paid + $paymentDetail->amount),
                    'payment_date' => date('Y-m-d'),
                    'status' => ($remainAmt == 0) ? 'paid' : 'partial',
                    'online_payment_status' => 'paid',
                    'updated_at' => Carbon::now()
                ]);                        
            //Deposit into trust account
            if($fundRequest->deposit_into_type == "trust") {
                DB::table('users_additional_info')->where("user_id", $paymentDetail->user_id)->increment('trust_account_balance', $paymentDetail->amount);
                $trustHistoryId = DB::table('trust_history')->insertGetId([
                    'client_id' => $paymentDetail->user_id,
                    'payment_method' => $paymentMethod,
                    'amount_paid' => $paymentDetail->amount,
                    'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
                    'payment_date' => date('Y-m-d'),
                    'fund_type' => 'diposit',
                    'related_to_fund_request_id' => $fundRequest->id,
                    'allocated_to_case_id' => $fundRequest->allocated_to_case_id,
                    'allocated_to_lead_case_id' => $fundRequest->allocated_to_lead_case_id,
                    'created_by' => $paymentDetail->user_id,
                    'online_payment_status' => 'paid',
                    'created_by' => $paymentDetail->user_id,
                    'created_at' => Carbon::now(),
                ]);
                $paymentDetail->fill(['trust_history_id' => $trustHistoryId])->save();

                // For allocated case trust balance
                if($fundRequest->allocated_to_case_id != '') {
                    DB::table('case_master')->where('id', $fundRequest->allocated_to_case_id)->increment('total_allocated_trust_balance', $paymentDetail->amount);
                    DB::table('case_client_selection')->where('case_id', $fundRequest->allocated_to_case_id)->where('selected_user', $paymentDetail->user_id)->increment('allocated_trust_balance', $paymentDetail->amount);
                }
                // For allocated lead case trust balance
                if($fundRequest->allocated_to_lead_case_id != '') {
                    LeadAdditionalInfo::where('user_id', $fundRequest->allocated_to_lead_case_id)->increment('allocated_trust_balance', $paymentDetail->amount);
                }

                // For update next/previous trust balance
                $this->updateNextPreviousTrustBalance($paymentDetail->user_id);
            } else {
                // Deposit into credit account
                DB::table('users_additional_info')->where("user_id", $paymentDetail->user_id)->increment('credit_account_balance', $paymentDetail->amount);
                $creditHistoryId = DB::table('deposit_into_credit_history')->insertGetId([
                    'user_id' => $paymentDetail->user_id,
                    'deposit_amount' => $paymentDetail->amount,
                    'payment_method' => strtolower($paymentMethod),
                    'payment_date' => date("Y-m-d"),
                    'total_balance' => @$userAdditionalInfo->credit_account_balance,
                    'payment_type' => "deposit",
                    'firm_id' => $paymentDetail->firm_id,
                    'related_to_fund_request_id' => $fundRequest->id,
                    'created_by' => $paymentDetail->user_id,
                    'online_payment_status' => 'paid',
                    'created_by' => $paymentDetail->user_id,
                    'created_at' => Carbon::now(),
                ]);
                $paymentDetail->fill(['credit_history_id' => $creditHistoryId])->save();

                // For update next/previous credit balance
                $this->updateNextPreviousCreditBalance($paymentDetail->user_id);
            }
        }

        // For lawyer/firm staff activity 
        DB::table("all_history")->insert([
            'user_id' => $paymentDetail->user_id,
            'client_id' => $paymentDetail->user_id,
            'deposit_for' => $paymentDetail->user_id,
            'deposit_id' => $fundRequest->id,
            'activity' => "pay a payment of $".number_format($paymentDetail->amount, 2)." (".$paymentMethod.") for deposit request",
            'type' => 'fundrequest',
            'action' => 'pay',
            'created_by' => $paymentDetail->user_id,
            'created_at' => Carbon::now(),
        ]);

        // For client activity
        DB::table("all_history")->insert([
            'user_id' => $paymentDetail->user_id,
            'client_id' => $paymentDetail->user_id,
            'deposit_for' => $paymentDetail->user_id,
            'deposit_id' => $fundRequest->id,
            'activity' => "pay a payment of $".number_format($paymentDetail->amount,2)." (".$paymentMethod.") for deposit request",
            'type' => 'fundrequest',
            'action' => 'pay',
            'is_for_client' => 'yes',
            'created_by' => $paymentDetail->user_id,
            'created_at' => Carbon::now(),
        ]);

        if($paymentDetail->payment_method == 'cash') {
            // Send confirmation email to client
            $client = User::whereId($paymentDetail->user_id)->first();
            $this->dispatch(new OnlinePaymentEmailJob(null, $client, $emailTemplateId = 33, $paymentDetail, 'cash_confirm_client', 'fundrequest'));

            // Send confirmation email to fundRequest created user
            $user = User::whereId($fundRequest->created_by)->first();
            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $user, $emailTemplateId = 34, $paymentDetail, 'cash_confirm_user', 'fundrequest'));

            // Send confirm email to firm owner/lead attorney
            $firmOwner = User::where('firm_name', $paymentDetail->firm_id)->where('parent_user', 0)->first();
            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $firmOwner, $emailTemplateId = 34, $paymentDetail, 'cash_confirm_user', 'fundrequest'));

            Log::info('fundRequest cash payment webhook successfull');

        } else if($paymentDetail && $paymentDetail->payment_method == 'bank transfer') { 
            // Send confirmation email to client
            $client = User::whereId($paymentDetail->user_id)->first();
            $this->dispatch(new OnlinePaymentEmailJob(null, $client, $emailTemplateId = 33, $paymentDetail, 'bank_confirm_client', 'fundrequest'));

            // Send confirmation email to fundRequest created user
            $user = User::whereId($fundRequest->created_by)->first();
            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $user, $emailTemplateId = 34, $paymentDetail, 'bank_confirm_user', 'fundrequest'));

            // Send confirm email to firm owner/lead attorney
            $firmOwner = User::where('firm_name', $paymentDetail->firm_id)->where('parent_user', 0)->first();
            $this->dispatch(new OnlinePaymentEmailJob($fundRequest, $firmOwner, $emailTemplateId = 34, $paymentDetail, 'bank_confirm_user', 'fundrequest'));
            
            Log::info('fundRequest bank payment webhook successfull');
        } else {
            Log::info("No email sent for request:". @$fundRequest->id);
        }
    }

    /**
     * User trust/credit fund  cash/bank payment, order confirmed
     */
    public function fundOrderConfirm($paymentDetail, $response)
    {
        DB::table("user_trust_credit_fund_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)
            ->update(['conekta_payment_status' => 'paid', 'paid_at' => Carbon::now()/* , 'conekta_order_object' => json_encode($data) */]);

        $client = User::whereId($paymentDetail->user_id)->first();
        // Get user additional info
        $userAdditionalInfo = UsersAdditionalInfo::select("trust_account_balance", "credit_account_balance")->where("user_id", $paymentDetail->user_id)->first();
        $paymentMethod = ($paymentDetail->payment_method == 'cash') ? 'Oxxo Cash' : (($paymentDetail->payment_method == 'bank transfer') ? 'SPEI' : '');
        $payableAmount = $paymentDetail->amount;
        if($paymentDetail->fund_type == "trust") {
            DB::table('users_additional_info')->where("user_id", $client->id)->increment('trust_account_balance', $payableAmount);

            $trustHistoryId = DB::table('trust_history')->insertGetId([
                'client_id' => $paymentDetail->user_id,
                'payment_method' => $paymentMethod,
                'amount_paid' => $payableAmount,
                'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
                'payment_date' => date('Y-m-d'),
                'fund_type' => 'diposit',
                'allocated_to_case_id' => $paymentDetail->allocated_to_case_id,
                'created_by' => $paymentDetail->user_id,
                'online_payment_status' => 'paid',
                'created_at' => Carbon::now(),
            ]);
            DB::table("user_trust_credit_fund_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)->update(['trust_history_id' => $trustHistoryId]);

            // For allocated case trust balance
            if($paymentDetail->allocated_to_case_id != '') {
                DB::table('case_master')->where('id', $paymentDetail->allocated_to_case_id)->increment('total_allocated_trust_balance', $payableAmount);
                DB::table('case_client_selection')->where('case_id', $paymentDetail->allocated_to_case_id)->where('selected_user', $paymentDetail->user_id)->increment('allocated_trust_balance', $payableAmount);
            }
            // For update next/previous trust balance
            $this->updateNextPreviousTrustBalance($paymentDetail->user_id);
            
            // Add fund account activity
            $AccountActivityData = AccountActivity::select("*")->where("firm_id",$client->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
            DB::table('account_activity')->insert([
                'user_id' => $client->id,
                'case_id'=> $paymentDetail->allocated_to_case_id ?? Null,
                'credit_amount' => $payableAmount ?? 0.00,
                'total_amount' => ($AccountActivityData) ? $AccountActivityData['total_amount'] + $payableAmount : $payableAmount,
                'entry_date' => date('Y-m-d'),
                'payment_method' => $paymentMethod,
                'payment_type' => "deposit",
                'pay_type' => "trust",
                'from_pay' => "online",
                'trust_history_id' => $trustHistoryId ?? Null,
                'firm_id' => $client->firm_name,
                'section' => "other",
                'created_by'=> $client->id,
                'created_at' => Carbon::now(),
            ]);

            // FOr acitivity
            DB::table("all_history")->insert([
                'user_id' => $paymentDetail->user_id,
                'client_id' => $paymentDetail->user_id,
                'deposit_for' => $paymentDetail->user_id,
                'deposit_id' => $trustHistoryId,
                'activity' => "deposit into trust of $".number_format($payableAmount,2)." (".$paymentMethod.") for ",
                'type' => 'deposit',
                'action' => 'add',
                'created_by' => $paymentDetail->user_id,
                'created_at' => Carbon::now(),
            ]);
        } else {
            // Deposit into credit account
            DB::table('users_additional_info')->where("user_id", $paymentDetail->user_id)->increment('credit_account_balance', $payableAmount);
            $creditHistoryId = DB::table('deposit_into_credit_history')->insertGetId([
                'user_id' => $paymentDetail->user_id,
                'deposit_amount' => $payableAmount,
                'payment_method' => strtolower($paymentMethod),
                'payment_date' => date("Y-m-d"),
                'total_balance' => @$userAdditionalInfo->credit_account_balance,
                'payment_type' => "deposit",
                'firm_id' => $paymentDetail->firm_id,
                'created_by' => $paymentDetail->user_id,
                'online_payment_status' => 'paid',
                'created_at' => Carbon::now(),
            ]);
            DB::table("user_trust_credit_fund_online_payments")->where("conekta_order_id", $paymentDetail->conekta_order_id)->update(['credit_history_id' => $creditHistoryId]);

            // For update next/previous credit balance
            $this->updateNextPreviousCreditBalance($paymentDetail->user_id);

            // For recent activity
            DB::table("all_history")->insert([
                'user_id' => $paymentDetail->user_id,
                'client_id' => $paymentDetail->user_id,
                'deposit_for' => $paymentDetail->user_id,
                'deposit_id' => $creditHistoryId,
                'activity' => "deposit into credit of $".number_format($payableAmount,2)." (".$paymentMethod.") for ",
                'type' => 'credit',
                'action' => 'add',
                'created_by' => $paymentDetail->user_id,
                'created_at' => Carbon::now(),
            ]);
        }

        if($paymentDetail->payment_method == 'cash') {
            // Send confirmation email to client
            $this->dispatch(new OnlinePaymentEmailJob(null, $client, $emailTemplateId = 33, $paymentDetail, 'cash_confirm_client', 'fund'));

            // Send confirmation email to fundRequest created user
            $user = User::whereId($paymentDetail->created_by)->first();
            $this->dispatch(new OnlinePaymentEmailJob($client, $user, $emailTemplateId = 34, $paymentDetail, 'cash_confirm_user', 'fund'));

            // Send confirm email to firm owner/lead attorney
            $firmOwner = User::where('firm_name', $paymentDetail->firm_id)->where('parent_user', 0)->first();
            $this->dispatch(new OnlinePaymentEmailJob($client, $firmOwner, $emailTemplateId = 34, $paymentDetail, 'cash_confirm_user', 'fund'));

            Log::info('fund cash payment webhook successfull');

        } else if($paymentDetail && $paymentDetail->payment_method == 'bank transfer') { 
            // Send confirmation email to client
            $client = User::whereId($paymentDetail->user_id)->first();
            $this->dispatch(new OnlinePaymentEmailJob(null, $client, $emailTemplateId = 33, $paymentDetail, 'bank_confirm_client', 'fund'));

            // Send confirmation email to fundRequest created user
            $user = User::whereId($paymentDetail->created_by)->first();
            $this->dispatch(new OnlinePaymentEmailJob($client, $user, $emailTemplateId = 34, $paymentDetail, 'bank_confirm_user', 'fund'));

            // Send confirm email to firm owner/lead attorney
            $firmOwner = User::where('firm_name', $paymentDetail->firm_id)->where('parent_user', 0)->first();
            $this->dispatch(new OnlinePaymentEmailJob($client, $firmOwner, $emailTemplateId = 34, $paymentDetail, 'bank_confirm_user', 'fund'));
            
            Log::info('fund bank payment webhook successfull');
        } else {
            Log::info("No email sent for fund user:". @$client->id);
        }
    }
}