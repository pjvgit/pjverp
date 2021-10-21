<?php
$paymentMethod = unserialize(PAYMENT_METHOD);
$paid=$invoiceData['paid_amount'];
$invoice=$invoiceData['total_amount'];
$finalAmt=$invoice-$paid;
?>
<div class="blade" bladefile="resources/views/billing/invoices/payInvoice.blade.php"></div>
<div class="row">
    <div class="col-md-6 selenium-invoice-number">Invoice Number: #{{sprintf("%05d", $invoiceData['id'])}}</div>
    <div class="col-md-4 text-right">Invoice Amount:</div>
    <div class="col-md-2 text-right selenium-total-amount">${{number_format($invoice,2)}}</div>
</div>
<div class="row">
    <div class="col-md-6 selenium-case-name"> 
    <?php 
        if($invoiceData['is_lead_invoice'] == 'yes'){
            echo "Potential Case: ".$userData['user_name'];
        }else{
            echo (($caseMaster) ? ucfirst(substr($caseMaster['case_title'],0,50)) : 'None');
        }
    ?>
    <input type="hidden" name="is_lead_invoice" id="is_lead_invoice" value="{{$invoiceData['is_lead_invoice']}}">
    </div>
    <div class="col-md-4 text-right">Amount Paid:</div>
    <div class="col-md-2 text-right selenium-paid-amount">${{number_format($paid,2)}}</div>
</div>
<div class="row">
    <div class="col-md-10 text-right"><strong>Outstanding Amount:</strong></div>
    <div class="col-md-2 text-right selenium-outstanding-amount"><strong>${{number_format($finalAmt,2)}}</strong></div>
</div>
<br>
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link" id="online-payment-tab" data-toggle="tab" href="#online-payment-div" role="tab" aria-controls="online-payment-div"
            aria-selected="false">Online Payment
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link  active show" id="offline-payment-tab" data-toggle="tab" href="#offline_payment_tab" role="tab"
            aria-controls="offline_payment_tab" aria-selected="true">Offline Payment
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="trust-account-tab" data-toggle="tab" href="#fromTrustccount" role="tab"
            aria-controls="fromTrustccount" aria-selected="true">From Trust Account
        </a>
    </li>
    @if(getInvoiceSetting() && getInvoiceSetting()->is_non_trust_retainers_credit_account == "yes")
    <li class="nav-item">
        <a class="nav-link " id="credit-account-tab" data-toggle="tab" href="#fromCreditccount" role="tab"
            aria-controls="fromCreditccount" aria-selected="true">From Credit Account
        </a>
    </li>
    @endif
</ul>
<div class="tab-content" id="myTabContent">

    <div class="tab-pane fade " id="online-payment-div" role="tabpanel" aria-labelledby="online-payment-tab">
        <form class="AddIncomingCall" id="AddIncomingCall" name="AddIncomingCall" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" id="invoice_id" value="{{$invoice_id}}" name="invoice_id">
            <div class="col-md-12">
                <div class="payment-confirmation-container">
                    <div>
                        <div class="row">
                            <div class="col-3 pl-4 pt-4">
                                <span class="money-graph"></span>
                            </div>
                            <div class="col-9">
                                <div data-testid="payments-platform-promo-header"
                                    class="payments-platform-promo-header">
                                    <h3>Start Accepting Online Payments!</h3>
                                </div>
                                <br>
                                <ul class="invoice-payments-platform-promo-list clearfix" style="list-style-type:none;">
                                    <li data-testid="payments-platform-promo-list-item"
                                        class="payments-platform-promo-list-item clearfix"><i
                                            class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                                        <span class="payments-platform-promo-list-item-text">Get paid faster by
                                            accepting credit card payments in office</span></li>
                                    <li data-testid="payments-platform-promo-list-item"
                                        class="payments-platform-promo-list-item clearfix"><i
                                            class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                                        <span class="payments-platform-promo-list-item-text">Get paid faster by
                                            letting your clients pay online</span></li>
                                    <li data-testid="payments-platform-promo-list-item"
                                        class="payments-platform-promo-list-item clearfix"><i
                                            class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                                        <span class="payments-platform-promo-list-item-text">Access from your MyCase
                                            account, no 3rd party</span></li>
                                    <li data-testid="payments-platform-promo-list-item"
                                        class="payments-platform-promo-list-item clearfix"><i
                                            class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                                        <span class="payments-platform-promo-list-item-text">Save money with free
                                            eCheck payments and competitive credit card fees</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="modal-footer"></div>
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime"
                    style="display: none;"></div>
                <div class="form-group row float-right">
                    <a class="btn btn-primary" href="#" target="_blank" rel="noopener noreferrer">Get Started Now!</a>
                </div>
            </div>
        </form>
    </div>
    <div class="tab-pane fade  active show" id="offline_payment_tab" role="tabpanel" aria-labelledby="offline-payment-tab">
        <form class="PayInvoiceForm" id="PayInvoiceForm" name="PayInvoiceForm" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" id="invoice_id" value="{{$invoice_id}}" name="invoice_id">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="firstName1">Payment Method</label>
                    <select class="form-control caller_name select2" id="payment_method" name="payment_method"
                        style="width: 100%;" placeholder="Select or enter a name...">
                        <option></option>
                        <?php foreach($paymentMethod as $key=>$val){?>
                        <option value="{{$val}}"> {{$val}}</option>
                        <?php } ?>
                    </select>
                    <span id="ptype"></span>
                </div>
                <div class="col-md-6 form-group">
                    <label for="firstName1">Date</label>
                    <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="payment_date" maxlength="250"
                        name="payment_date" type="text">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="firstName1">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input class="form-control amountFirst" style="width:50%; " maxlength="20" name="amount"
                            id="amountFirst" value="" type="text" aria-label="Amount (to the nearest dollar)" data-payable-amount="{{ $finalAmt }}">

                        <small>&nbsp;</small>
                        <div class="input-group col-sm-9" id="TypeError"></div>
                        <span id="amt"></span>
                    </div>
                </div>
                <div class="col-md-2 form-group">
                    <label for="firstName1">&nbsp;</label>
                    <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" class="payfullFirst" id="payfull" value="{{$finalAmt}}"
                            name="payfull"><span>Pay in full</span><span class="checkmark"></span>
                    </label>
                </div>
                <div class="col-md-6 form-group">
                    <label for="firstName1">Notes</label>
                    <input class="form-control" value="" id="notes" name="notes" type="text">
                </div>
                <div class="col-md-12 form-group">
                    <label for="contact-field">Choose contact</label>
                    <select class="form-control caller_name" id="contact_id" name="contact_id" style="width: 100%;"
                        placeholder="Select a contact">
                        <option></option>
                        <?php                        
                        if(count($trustAccounts) > 0) {
                            foreach($trustAccounts as $key=>$val){?>
                            <option value="{{$val->uid}}"> {{$val->user_name}} (<?php if($val->user_level=="2") { echo "Client"; } else{ echo "Company" ;}  ?>)</option>
                        <?php } }else{ ?>
                            <option value="{{$userData['uid']}}"> {{$userData['user_name']}} </option>
                        <?php }  ?>
                        @if (!empty($invoiceUserNotInCase))
                            <option value="{{ $invoiceUserNotInCase->user_id }}">{{ @$invoiceUserNotInCase->user->full_name }} ({{ @$invoiceUserNotInCase->user->user_type_text }}) Not in case</option>
                        @endif
                    </select>  
                    <span id="contactin"></span>
                </div>                
                <div class="col-md-12 form-group">
                    <label for="firstName1">Deposit Into </label>
                    <select class="form-control caller_name" id="deposit_into" name="deposit_into" style="width: 100%;"
                        placeholder="Select a bank account" disabled>
                        <option></option>
                        <option value="Operating Account">Operating Account</option>
                        <?php if($invoiceData['is_lead_invoice'] == 'no'){
                            echo '<option value="Trust Account">Trust Account</option>';
                        } ?>
                    </select>
                    <span id="depositin"></span>                    
                </div>  
                <?php if($invoiceData['is_lead_invoice'] == 'no'){ ?>              
                <div class="col-md-12 form-group" id="chkDeposit">
                    <div class="col-form-label d-flex align-items-center h-100 credit-payment-field form-check">
                        <input id="credit-payment-field" name="credit_payment" type="checkbox" class="my-0 form-check-input">
                        <label for="credit-payment-field" class="my-0 form-check-label ">Mark as Credit Payment</label>
                    </div>
                </div>
                <div class="col-md-12 form-group" id="ca">
                    <label for="firstName1">Credit Account</label>
                    <select class="form-control" id="from_credit_account" name="credit_account" style="width: 100%;">
                        <option></option>
                        <?php 
                            if(count($trustAccounts) > 0) {
                            foreach($trustAccounts as $key=>$val){?>
                            <option value="{{$val->uid}}"> {{$val->user_name}}  (Balance {{number_format($val->credit_account_balance,2)}})</option>
                        <?php } }else{ ?>
                            <option value="{{$userData['uid']}}"> {{$userData['user_name']}} (Balance
                                ${{number_format($userData['credit_account_balance'],2)}})
                            </option>
                        <?php } ?>
                    </select>
                    <span id="caacount"></span>
                </div>
                <div class="col-md-12 form-group" id="ta">
                    <label for="firstName1">Trust Account</label>
                    <select class="form-control caller_name trust_account" id="trust_account" name="trust_account" style="width: 100%;"
                        placeholder="Select an account">
                        <option></option>
                    </select>
                    <span id="taacount"></span>
                    <input type="hidden" name="is_case" class="is_case">
                </div>

                <?php } ?>
            </div>
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="button"
                    onclick="paymentConfitmation()">Make Payment</button>
            </div>
        </form>
    </div>
    <div class="tab-pane fade" id="fromTrustccount" role="tabpanel" aria-labelledby="trust-account-tab">
        <form class="PayInvoiceFromTrustForm" id="PayInvoiceFromTrustForm" name="PayInvoiceFromTrustForm" method="POST">
            @csrf
            <input type="hidden" id="invoice_id" value="{{$invoice_id}}" name="invoice_id">
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Select Contact</label>
                    <select class="form-control caller_name" id="trust_contact_id" name="contact_id"  style="width: 100%;">
                        <option></option>
                        <?php
                            if(count($trustAccounts) > 0) {
                            foreach($trustAccounts as $key=>$val){?>
                            <option value="{{$val->uid}}"> {{$val->user_name}} (<?php if($val->user_level=="2") { echo "Client"; } else{ echo "Company" ;}  ?>)</option>
                        <?php } }else{ ?>
                            <option value="{{$userData['uid']}}"> {{$userData['user_name']}} </option>
                        <?php } ?>
                        <!-- <option value="{{$userData['uid']}}"> {{$userData['user_name']}} (Balance ${{number_format($userData['trust_account_balance'],2)}}) - Trust(Trust Account) </option> -->
                        
                        @if (!empty($invoiceUserNotInCase))
                            <option value="{{ $invoiceUserNotInCase->user_id }}">{{ @$invoiceUserNotInCase->user->full_name }} ({{ @$invoiceUserNotInCase->user->user_type_text }}) Not in case</option>
                        @endif
                    </select>
                    <span id="ttcaccount"></span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Select Bank Account</label>
                    <select class="form-control caller_name " id="bank_account" name="bank_account" style="width: 100%;"
                        placeholder="Select a bank account" disabled>
                        <option></option>
                        <option value="trust">Trust Account</option>
                    </select>
                    <span id="bankacount"></span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Trust Account</label>
                    <select class="form-control caller_name trust_account bank_trust_account" id="trust_account" name="trust_account" style="width: 100%;"
                        placeholder="Select an account" disabled>
                        <option></option>
                    </select>
                    <span id="taacount"></span>
                    <input type="hidden" name="is_case" class="is_case">
                </div>
            </div>
            <div id="allocation-alert-section" data-testid="allocation-alert-section" class="row">
                <div class="col-md-12">
                    <div class="alert alert-primary fade show" role="alert">
                        <div class="d-flex align-items-start">
                            <div class="w-100">
                                <span data-testid="note">Note: <span id="selectContact"></span> has $<span id="selectContactUnallocatedAmount">0.00</span> in unallocated trust funds</span><br>
                                <a href="javascript:;" data-toggle="modal" data-target="#trust_allocation_modal" class="balance-allocation-link btn-link" data-case-id="{{$invoiceData['case_id']}}"  data-user-id="" data-page="invoice_payment">
                                    Transfer unallocated trust funds
                                </a>
                                to this case
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="firstName1">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input class="form-control amountTrust" style="width:50%; " maxlength="20" name="amount"
                            id="amount" value="" type="text" aria-label="Amount (to the nearest dollar)">
                        <small>&nbsp;</small>
                        <div class="input-group col-sm-9" id="TypeError"></div>
                        <span id="amt"></span>
                    </div>
                </div>
                <div class="col-md-2 form-group">
                    <label for="firstName1">&nbsp;</label>
                    <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" id="payfull" class="payfullTrust" value="{{number_format($finalAmt,2)}}"
                            name="payfull"><span>Pay in full</span><span class="checkmark"></span>
                    </label>
                </div>

                <div class="col-md-6 form-group">
                    <label for="firstName1">Date</label>
                    <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="payment_date" maxlength="250"
                        name="payment_date" type="text">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Notes</label>
                    <textarea class="form-control" value="" id="notes" name="notes"></textarea>
                </div>
            </div>
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton"  onclick="trustPaymentConfitmation()" type="button">
                    Make Payment
                </button>
            </div>
            
        </form>
    </div>
    <div class="tab-pane fade" id="fromCreditccount" role="tabpanel" aria-labelledby="credit-account-tab">
        <form class="PayInvoiceFromCreditForm" id="PayInvoiceFromCreditForm" name="PayInvoiceFromCreditForm" method="POST">
            @csrf
            <input type="hidden" id="invoice_id" value="{{$invoice_id}}" name="invoice_id">
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Select User Account</label>
                    <select class="form-control" id="from_credit_account" name="credit_account" style="width: 100%;">
                        <option></option>
                        <?php 
                        if(count($trustAccounts) > 0) {
                            foreach($trustAccounts as $key=>$val){?>
                            <option value="{{$val->uid}}"> {{$val->user_name}}  (Balance {{number_format($val->credit_account_balance,2)}}) - Operating (Operating Account)</option>
                        <?php } }else{ ?>
                            <option value="{{$userData['uid']}}"> {{$userData['user_name']}} (Balance
                                ${{number_format($userData['credit_account_balance'],2)}}) - Operating (Operating Account)
                            </option>
                        <?php } ?>
                        <!-- <option value="{{$userData['uid']}}"> {{$userData['user_name']}} (Balance
                            ${{number_format($userData['credit_account_balance'],2)}}) - Operating (Operating Account)
                        </option> -->
                        @if (!empty($invoiceUserNotInCase))
                            <option value="{{ $invoiceUserNotInCase->user_id }}">{{ @$invoiceUserNotInCase->user->full_name }} (Balance {{number_format($invoiceUserNotInCase->credit_account_balance ?? 0,2)}})  - Operating (Operating Account) Not in case</option>
                        @endif
                    </select>
                    <span id="ccaccount"></span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="firstName1">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input class="form-control number amountCredit" style="width:50%; " maxlength="20" name="amount" id="credit_amount" value="" type="text" max="{{number_format($finalAmt,2)}}" aria-label="Amount (to the nearest dollar)">
                        <small>&nbsp;</small>
                        <div class="input-group col-sm-9" id="TypeError"></div>
                        <span id="cre_amt"></span>
                    </div>
                </div>
                <div class="col-md-2 form-group">
                    <label for="firstName1">&nbsp;</label>
                    <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" class="payfullCredit" value="{{number_format($finalAmt,2)}}" name="payfull">
                        <span>Pay in full</span><span class="checkmark"></span>
                    </label>
                </div>

                <div class="col-md-6 form-group">
                    <label for="firstName1">Date</label>
                    <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" maxlength="15" name="payment_date" type="text">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Notes</label>
                    <textarea class="form-control" value="" id="notes" name="notes"></textarea>
                </div>
            </div>
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary ladda-button example-button m-1 submit" onclick="creditPaymentConfirmation()" type="button">
                    Make Payment
                </button>
            </div>

        </form>
    </div>
</div>
<script src="{{ asset('assets\js\custom\client\trustallocation.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });

        $("#from_trust_account").select2({
            placeholder: "Select a user's account",
            theme: "classic",
            allowClear: true,
        });
        $("#payment_method").select2({
            placeholder: "Select method",
            theme: "classic",

        });
        $("#contact_id").select2({
            placeholder: "Select contact",
            theme: "classic",

        });
        $("#trust_contact_id").select2({
            placeholder: "Select contact",
            theme: "classic",

        });
        $("#deposit_into").select2({
            placeholder: "Select a bank account",
            theme: "classic",

        });
        $("#trust_account").select2({
            placeholder: "Select an account",
            theme: "classic",

        });
        
        afterLoader();
        $("#PayInvoiceForm").validate({
            rules: {
                payment_method: {
                    required: true,
                },
                amount: {
                    required: true,
                    // max:{{$finalAmt}}
                    validAmount: true,
                },
                contact_id: {
                    required: true,
                },
                deposit_into: {
                    required: true,
                },
                trust_account:{
                    required: true,
                }
            },
            messages: {
                payment_method: {
                    required: "Payment type is required",
                },
                amount: {
                    required: "Amount is required",
                    // max:"Amount exceeds requested balance of ${{number_format($finalAmt,2)}}" 
                },
                contact_id: {
                    required: "Contact is required",
                },
                deposit_into: {
                    required: "Deposit Account is required",
                },
                trust_account: {
                    required: "Trust Account is required",
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#payment_method')) {
                    error.appendTo('#ptype');
                } else if (element.is('.amountFirst')) {
                    error.appendTo('#amt');
                } else if (element.is('#contact_id')) {
                    error.appendTo('#contactin');    
                } else if (element.is('#deposit_into')) {
                    error.appendTo('#depositin');
                } else if (element.is('#trust_account')) {
                    error.appendTo('#taacount');
                } else {
                    element.after(error);
                }
            }
        });
        $("#PayInvoiceFromTrustForm").validate({
            rules: {
                contact_id: {
                    required: true,
                },
                bank_account: {
                    required: true,
                },
                trust_account: {
                    required: true,
                },
                amount: {
                    required: true
                }
            },
            messages: {
                contact_id: {
                    required: "Contact is required",
                },
                bank_account: {
                    required: "Bank Account is required",
                },
                trust_account: {
                    required: "Account is required",
                },
                amount: {
                    required: "Amount is required"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#trust_contact_id')) {
                    error.appendTo('#ttcaccount');
                }else {
                    element.after(error);
                }
            }
        });

        $('.payfullTrust').change(function () {
            if ($(this).is(":checked")) {
                $(".amountTrust").val($(this).val());
                $(".amountTrust").attr("readonly",true);
            } else {
                $(".amountTrust").val("");
                $(".amountTrust").removeAttr("readonly");
            }
        });

        $('.payfullFirst').change(function () {
            if ($(this).is(":checked")) {
                $(".amountFirst").val($(this).val());
                $(".amountFirst").attr("readonly",true);
            } else {
                $(".amountFirst").val("");
                $(".amountFirst").removeAttr("readonly");
            }
        });
        $('#deposit_into').change(function () {            
            if ($(this).val()=="Trust Account") {
                $("#ta").show();
                $("#chkDeposit").hide();
                $("#ca").hide();
            } else {
                $("#ta").hide();
                $("#chkDeposit").show();
            }
        });
        $('#credit-payment-field').on('change', function () {
            if ($(this).is(":checked")) {
                $("#ca").show();
            } else {
                $("#ca").hide();
            }
        });
        $("#ta").hide();
        $("#chkDeposit").hide();
        $("#ca").hide();
        $("#allocation-alert-section").hide();
    });

    function paymentConfitmation() {
        if (!$('#PayInvoiceForm').valid()) {
            afterLoader();
            return false;
        } else {
            didPayment();
            return false;
        }
    }

    function didPayment() {
        console.log("Payinvoice > didPayment > submit form");
        var amtVal= $('#amountFirst').val();
        var currentAmt = $.number(amtVal,2);
        swal({
            title: 'Confirm the payment amount of $' + currentAmt + '?',
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            cancelButtonText: 'Close',
            confirmButtonText: 'Confirm Payment',
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger  mr-2',
            buttonsStyling: false,
            reverseButtons: true

        }).then(function () {
            beforeLoader();
            var dataString = '';
            dataString = $("#PayInvoiceForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/saveInvoicePayment", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        if($("#is_lead_invoice").val() != 'yes') {                        
                            swal('Payment Successful!', res.msg, 'success');
                            afterLoader();
                            setTimeout(function () {
                                $("#payInvoice").modal("hide");                           
                            }, 1000);
                            updateInvoiceDetail();
                        }else{
                            swal('Payment Successful!', res.msg, 'success').then(function(){
                                window.location.reload();
                            });
                            afterLoader();
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    afterLoader();
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });

        });
    }

    function trustPaymentConfitmation() {
        if (!$('#PayInvoiceFromTrustForm').valid()) {
            afterLoader();
            return false;
        } else {
            didTrustPayment();
            return false;
        }
    }

    function didTrustPayment() {
        var currentAmt = $.number($('.amountTrust').val(),2);
        swal({
            title: 'Confirm the payment amount of $' + currentAmt + '?',
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            confirmButtonText: 'Confirm Payment',
            cancelButtonText: 'Close',
            confirmButtonClass: 'btn btn-success ml-3',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false,
            reverseButtons: true
        }).then(function () {
            // $('.formater').number(true, 2);

            beforeLoader();
            var dataString = '';
            dataString = $("#PayInvoiceFromTrustForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/saveTrustInvoicePayment", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $('#payInvoice').animate({ scrollTop: 0 }, 'slow');
                        afterLoader();
                        return false;
                    } else {
                        if($("#is_lead_invoice").val() != 'yes') {                        
                            swal('Payment Successful!', res.msg, 'success');
                            afterLoader();
                            setTimeout(function () {
                                $("#payInvoice").modal("hide")
                            }, 1000);
                            $('#billing_invoice_table').DataTable().ajax.reload(null, false);
                            $('#invoiceGrid').DataTable().ajax.reload(null, false);
                            updateInvoiceDetail();
                        }else{
                            swal('Payment Successful!', res.msg, 'success').then(function(){
                                window.location.reload();
                            });
                            afterLoader();                            
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    afterLoader();
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });

        });
    }

// For payable amount validation
jQuery.validator.addMethod("validAmount", function(value, element) {
    value = value.replace(/,/g, '');
    return (parseFloat(value) <= parseFloat($('#amountFirst').attr("data-payable-amount")));
}, "Amount exceeds requested balance of ${{number_format($finalAmt,2)}}");

// Validate credit payment form
$("#PayInvoiceFromCreditForm").validate({
    rules: {
        credit_account: {
            required: true,
        },
        amount: {
            required: true
        }
    },
    messages: {
        credit_account: {
            required: "Account is required",
        },
        amount: {
            required: "Amount is required"
        }
    },
    errorPlacement: function (error, element) {
        if (element.is('#from_credit_account')) {
            error.appendTo('#ccaccount');
        } else {
            element.after(error);
        }
    }
});

$('.payfullCredit').change(function () {
    if ($(this).is(":checked")) {
        $(".amountCredit").val($(this).val());
        $(".amountCredit").attr("readonly", true);
    } else {
        $(".amountCredit").val("");
        $(".amountCredit").removeAttr("readonly");
    }
});

// For credit payment
function creditPaymentConfirmation() {
    if (!$('#PayInvoiceFromCreditForm').valid()) {
        afterLoader();
        return false;
    } else {
        didCreditPayment();
        return false;
    }
}

// Credit payment
function didCreditPayment() {
    var f= $.number($('.amountCredit ').val(),2);
    var currentAmt = f;
    // var currentAmt = $.number($('#amountTrust').val(),2);
    swal({
        title: 'Confirm the payment amount of $' + currentAmt + '?',
        text: "",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0CC27E',
        cancelButtonColor: '#FF586B',
        confirmButtonText: 'Confirm Payment',
        cancelButtonText: 'Close',
        confirmButtonClass: 'btn btn-success ml-3',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false,
        reverseButtons: true
    }).then(function () {
        beforeLoader();
        var dataString = '';
        dataString = $("#PayInvoiceFromCreditForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/save/credit/payment", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#payInvoice').animate({
                        scrollTop: 0
                    }, 'slow');
                    afterLoader();
                    return false;
                } else {
                    if($("#is_lead_invoice").val() != 'yes') {
                        swal('Payment Successful!', res.msg, 'success');
                        afterLoader();
                        setTimeout(function () {
                            $("#payInvoice").modal("hide")
                        }, 1000);
                        $('#billing_invoice_table').DataTable().ajax.reload(null, false);
                        $('#invoiceGrid').DataTable().ajax.reload(null, false);
                        updateInvoiceDetail();
                    }else{
                        swal('Payment Successful!', res.msg, 'success').then(function(){
                            window.location.reload();
                        });
                        afterLoader();                        
                    }
                }
            },
            error: function (jqXHR, exception) {
                afterLoader();
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
            },
        });

    });
}
$('#contact_id').on('select2:select', function (e) {
    var data = e.params.data;
    getClientCases(data.id);
    // localStorage.setItem("selectedContact", data.id);
    $("#deposit_into").removeAttr('disabled');
    $("#from_credit_account").val(data.id);
    $('#from_credit_account').find('option').not('[value='+data.id+']').hide();
});

$('#trust_contact_id').on('select2:select', function (e) {
    var data = e.params.data;
    getClientCases(data.id);
    // localStorage.setItem("selectedContact", data.id);
    // $("#bank_account").attr("disabled",false);
    $("#bank_account").removeAttr('disabled');
    $(".balance-allocation-link").attr("data-user-id",data.id)
});

$('#bank_account').on('change', function (e) {
    $(".bank_trust_account").removeAttr('disabled');
    if( parseFloat($("#selectContactUnallocatedAmount").html()) >  0.10){
        $("#allocation-alert-section").show();
    }
});

$('#offline-payment-tab').on('click', function (e) {
    $("#PayInvoiceForm").trigger("reset");
    $("#payment_method").select2({
        placeholder: "Select method",
        theme: "classic",
        allowClear: true,
    });
    $("#contact_id").select2({
        placeholder: "Select contact",
        theme: "classic",
        allowClear: true,
    });
    $("#deposit_into").select2({
        placeholder: "Select a bank account",
        theme: "classic",
        allowClear: true,
    });
    $("#trust_account").select2({
        placeholder: "Select an account",
        theme: "classic",
        allowClear: true,
    });
    return true;
});
$('#trust-account-tab').on('click', function (e) {
    $("#PayInvoiceFromTrustForm").trigger("reset");
    $("#trust_contact_id").select2({
        placeholder: "Select contact",
        theme: "classic",
        allowClear: true,
    });
    $("#bank_account").select2({
        placeholder: "Select a bank account",
        theme: "classic",
        allowClear: true,
    });
    $("#trust_account").select2({
        placeholder: "Select an account",
        theme: "classic",
        allowClear: true,
    });
    return true;
});
$('#credit-account-tab').on('click', function (e) {
    $("#PayInvoiceFromCreditForm").trigger("reset");
    return true;
});

function reallocateContact(){
    $("body").addClass('modal-open');
    var client_id = $("#trust_contact_id").val();
    getClientCases(client_id);
}

function getClientCases(clientId) {
    var case_id = "{{$invoiceData['case_id']}}";
    var optgroup = '';
    $("#preloader").show();
    $.ajax({
        url: baseUrl+"/bills/dashboard/depositIntoTrust/clientCases",
        type: 'POST',
        data: {user_id: clientId, case_id: case_id},
        success: function(data) {            
            $('.trust_account').html('');
            $('.trust_account').html('<option value=""></option>');
            if(data.result.length > 0) {
                optgroup += "<optgroup label='Allocate to case'>";
                if(data.is_lead_case == 'yes') {
                    $.each(data.result, function(ind, item) {
                        optgroup += "<option value='" + item.user_id + "'>" + item.potential_case_title +"(Balance $"+item.allocated_trust_balance.toFixed(2)+")" + "</option>";
                    });      
                } else {
                    $.each(data.result, function(ind, item) {
                        // if(case_id == item.id || case_id == 0){
                            optgroup += "<option value='" + item.id + "'>" + item.case_title +"(Balance $"+item.allocated_trust_balance.toFixed(2)+")" + "</option>";
                        // }
                    });     
                }           
                optgroup += "</optgroup>"
            }
            
            if(data.user) {
                $("#selectContact").html(data.user.full_name);
                $("#selectContactUnallocatedAmount").html(data.userAddInfo.unallocate_trust_balance.toFixed(2));
                
                if( parseFloat($("#selectContactUnallocatedAmount").html()) <  0.01){
                    $("#allocation-alert-section").hide();
                }
                optgroup += "<optgroup label='Unallocated'>";
                optgroup += "<option value='" + data.user.id + "'>" + data.user.full_name +" ("+data.user.user_type_text+") (Balance $"+data.userAddInfo.unallocate_trust_balance.toFixed(2)+")" + "</option>";
                optgroup += "</optgroup>";
            }
            if(case_id != 0 && data.result.length <= 0) {
                $("#allocation-alert-section").hide();
            }
            
            $('.trust_account').append(optgroup);
            $(".select2-option").trigger('chosen:updated');
            $("#preloader").hide();
        }
    });
}

$(".trust_account").on("change", function() {
    var label = $(this.options[this.selectedIndex]).closest('optgroup').prop('label');
    var selectedTab = $("#payInvoice ul#myTab li a.active").attr('aria-controls');
    if(label == 'Allocate to case') {
        $("#"+selectedTab).find(".is_case").val("yes");
    } else {
        $("#"+selectedTab).find(".is_case").val("");
    }
})
</script>
