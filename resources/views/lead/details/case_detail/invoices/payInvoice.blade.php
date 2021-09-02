
<?php
$paymentMethod = unserialize(PAYMENT_METHOD);
$paid=$PotentialCaseInvoice['amount_paid'];
$invoice=$PotentialCaseInvoice['invoice_amount'];
$finalAmt=$invoice-$paid;
?>
<div class="row">
    <div class="col-md-6 selenium-invoice-number">Invoice Number: # {{$PotentialCaseInvoice['id']}}</div>
    {{-- {{sprintf("%05d", $PotentialCaseInvoice['id'])}} --}}
    <div class="col-md-4 text-right">Invoice Amount:</div>
    <div class="col-md-2 text-right selenium-total-amount">${{number_format($invoice,2)}}</div>
</div>
<div class="row">
    <div class="col-md-6 selenium-case-name">
        Potential Case: {{ucfirst(substr($userData['first_name'],0,50))}}
        {{ucfirst(substr($userData['middle_name'],0,50))}} {{ucfirst(substr($userData['last_name'],0,50))}}</div>
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
        <a class="nav-link" id="home-basic-tab" data-toggle="tab" href="#homeBasic" role="tab"
            aria-controls="homeBasic" aria-selected="false">Online Payment</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  active show" id="profile-basic-tab" data-toggle="tab" href="#profileBasic" role="tab"
            aria-controls="profileBasic" aria-selected="true">Offline Payment</a>
    </li>

</ul>
<div class="tab-content" id="myTabContent">
    <div class="showError" style="display:none"></div>
    <div class="tab-pane fade " id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
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
                    <a class="btn btn-primary"
                        href="http://info.mycase.com/payments-c-short-lp.html?sd=MC-BNR-APP-042018-InvoiceEditCreate&amp;campaign=70180000001B2eU&amp;ms=converted&amp;utm_medium=banner&amp;utm_campaign=MC-BNR-APP-042018-InvoiceEditCreate&amp;utm_source=in-app&amp;utm_content=in-app-invoice-edit-create"
                        target="_blank" rel="noopener noreferrer">Get Started Now!</a>
                </div>
            </div>
        </form>
    </div>
    <div class="tab-pane fade  active show" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
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
                <div class="col-md-6 form-group mb-3">
                    <label for="firstName1">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input class="form-control number" style="width:50%; " maxlength="20" name="amount" id="amount"
                            value="" type="text" aria-label="Amount (to the nearest dollar)">

                        <small>&nbsp;</small>
                        <div class="input-group col-sm-9" id="TypeError"></div>
                        <span id="amt"></span>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label for="firstName1">&nbsp;</label>
                    <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" id="payfull" value="{{number_format($finalAmt,2)}}" name="payfull"><span>Pay in full</span><span class="checkmark"></span>
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="firstName1">Notes</label>
                    <input class="form-control" value="" id="notes" name="notes" type="text">
                </div>
                <div class="col-md-6 form-group">
                    <label for="firstName1">Deposit Into </label>
                    <select class="form-control caller_name" id="deposit_into" name="deposit_into" style="width: 100%;"
                        placeholder="Select a bank account">
                        <option></option>
                        <option value="Operating Account">Operating Account</option>
                    </select>
                    <span id="depositin"></span>
                </div>

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
    </div>
    </form>
</div>

</div>

<script type="text/javascript">

    $(document).ready(function () {
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });

        $("#payment_method").select2({
            placeholder: "Select method",
            theme: "classic",

        });
        $("#deposit_into").select2({
            placeholder: "Select a bank account",
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
                },
                deposit_into: {
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
                deposit_into: {
                    required: "Deposit Account is required",
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#payment_method')) {
                    error.appendTo('#ptype');
                } else if (element.is('#amount')) {
                    error.appendTo('#amt');
                    depositin
                } else if (element.is('#deposit_into')) {
                    error.appendTo('#depositin');
                } else {
                    element.after(error);
                }
            }
        });



        $('#payfull').change(function() {
            if($(this).is(":checked")) {
               $("#amount").val($(this).val());
            }else{
                $("#amount").val("");
            }
                 
        });
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
    //Payment Successful!
    //Thank you. Your payment of $3.00 has been sent to Testing10.

    function didPayment() {
        var currentAmt=$('#amount').val();
        swal({
            title: 'Confirm the payment amount of $'+currentAmt+'?',
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            cancelButtonText: 'Close',
            confirmButtonText: 'Confirm Payment',
            confirmButtonClass: 'btn btn-success mr-5',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false
        }).then(function () {
            beforeLoader();
            var dataString = '';
            dataString = $("#PayInvoiceForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/savePayment", // json datasource
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
                        swal('Payment Successful!', res.msg, 'success');
                        afterLoader();
                        setTimeout(function(){ $("#payInvoice").modal("hide") }, 3000);
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
</script>
