<?php
$paymentMethod = unserialize(PAYMENT_METHOD);
?>
<div class="row">
    <div class="col-md-6">Contact: {{$userData->cname}} (Company)</div>
</div>
<div class="row">
    <div class="col-md-12" data-testid="deposit-into-account-balance">
        <strong>Current Balance: ${{number_format($UsersAdditionalInfo->trust_account_balance,2)}}</strong>
    </div>
</div>
<br>
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link  active show" id="profile-basic-tab" data-toggle="tab" href="#tba2" role="tab"
            aria-controls="tba2" aria-selected="true">Offline Payment</a>
    </li>

</ul>
<div class="tab-content" id="myTabContent">
    <div class="showError" style="display:none"></div>
    <div class="tab-pane fade  active show" id="tba2" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="addDepostForm" id="addDepostForm" name="addDepostForm" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" id="client_id" value="{{$userData->id}}" name="client_id">
            <?php
            if(!$clientList->isEmpty()){?>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Apply to Request</label>
                    <select class="form-control caller_name select2" id="applied_to" name="applied_to"
                        style="width: 100%;" placeholder="Applied To">
                        <option value="0"> Do not apply to a retainer request</option>
                        <?php foreach($clientList as $key=>$val){?>
                            <option value="{{$val->id}}">R-{{ sprintf('%06d', $val->id)}} (${{number_format($val->amount_due,2)}})</option>
                        <?php } ?>
                    </select>
                    <span id="papply"></span>
                </div>
              
            </div>
            <?php } ?>
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
                    <input class="form-control input-date" value="{{convertUTCToUserTimeZone('dateOnly')}}" id="payment_date" maxlength="250"
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
                    <label for="firstName1">Notes</label>
                    <input class="form-control" value="" id="notes" name="notes" type="text">
                </div>
            </div>
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary m-1 submit" id="submitButton" type="button"
                    onclick="paymentConfitmation()">Deposit Funds</button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'endDate': "dateToday",
            'todayHighlight': true
        });

        $("#payment_method").select2({
            placeholder: "Select method",
            theme: "classic",

        });
        afterLoader();
        $("#addDepostForm").validate({
            rules: {
                payment_method: {
                    required: true,
                },
                amount: {
                    required: true,
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
                } else {
                    element.after(error);
                }
            }
        });
    });

    function paymentConfitmation() {
        if (!$('#addDepostForm').valid()) {
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
        var currentAmt = $('#amount').val();
        swal({
            title: 'Confirm the deposit amount of $' + currentAmt + '?',
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            cancelButtonText: 'Close',
            confirmButtonText: 'Confirm Deposit',
            confirmButtonClass: 'btn btn-success mr-5',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false
        }).then(function () {
            beforeLoader();
            var dataString = '';
            dataString = $("#addDepostForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/saveTrustEntry", // json datasource
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
                        swal('Deposit Successful!', res.msg, 'success');
                        afterLoader();
                        setTimeout(function () {
                            $("#depositAmountPopup").modal("hide")
                        }, 3000);
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
    $("#depostifundtitle").html("Deposit Trust Funds for {{$userData->cname}}");

</script>
