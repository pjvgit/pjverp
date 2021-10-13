<?php $CommonController= new App\Http\Controllers\CommonController();
$paymentMethod = unserialize(PAYMENT_METHOD);
?>
<div class="row">
    <div class="col-md-12 selenium-invoice-number">Contact: {{$userData['user_name']}}  (<?php echo $CommonController->getUserTypeText($userData['user_level']); ?>)</div>
    <div class="col-md-12 selenium-invoice-number">
        @if($case)
        <strong>{{ $case->case_title }} Current Balance: ${{number_format($case->total_allocated_trust_balance, 2)}}</strong>
        @else
        <strong>Current Balance: ${{number_format($userData['trust_account_balance'],2)}}</strong>
        @endif
    </div>
</div>
<br>
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link  active show" id="profile-basic-tab" data-toggle="tab" href="#profileBasic" role="tab"
            aria-controls="profileBasic" aria-selected="true">Offline Payment
        </a>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade  active show" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="DepositTrustFund" id="DepositTrustFund" name="DepositTrustFund" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" id="trust_account_id" name="trust_account" value="{{$userData['uid']}}">
            <input type="hidden" id="case_id" name="case_id" value="{{ @$case->id }}">
            <?php
            if(!$clientList->isEmpty()){?>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="firstName1">Apply to Request</label>
                    <select class="form-control caller_name select2" id="applied_to" name="applied_to"
                        style="width: 100%;" placeholder="Applied To">
                        <option value="0"> Do not apply to a retainer request</option>
                        <?php foreach($clientList as $key=>$val){?>
                            <option value="{{$val->id}}"  <?php echo ($val->id == $request->request_id) ? 'selected':''; ?> >R-{{ sprintf('%06d', $val->id)}} (${{number_format($val->amount_due,2)}})</option>
                        <?php } ?>
                    </select>
                    <span id="papply"></span>
                </div>
              
            </div>
            <?php } ?>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="firstName1">Payment Method</label>
                    <select class="form-control caller_name select2" id="payment_method1" name="payment_method"
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
                        <input class="form-control amountFirst" style="width:50%; " maxlength="20" name="amount"
                            id="amountFirst" value="" type="text" aria-label="Amount (to the nearest dollar)">

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
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="button"
                    onclick="trustPaymentConfitmation()">Deposit Funds</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dynTitle").html("{{$userData['user_name']}}");
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        
        $("#applied_to").select2({
            theme: "classic",
            dropdownParent: $("#depositIntoTrustAccount"),
        });
        $("#from_trust_account").select2({
            placeholder: "Select a user's account",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#depositIntoTrustAccount"),
        });
        $("#payment_method1").select2({
            placeholder: "Select method",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#depositIntoTrustAccount"),
        }); 
        afterLoader();
        $("#DepositTrustFund").validate({
            rules: {
                payment_method: {
                    required: true,
                },
                amount: {
                    required: true
                }
            },
            messages: {
                payment_method: {
                    required: "Payment type is required",
                },
                amount: {
                    required: "Amount is required"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#payment_method1')) {
                    error.appendTo('#ptype');
                } else if (element.is('#amountFirst')) {
                    error.appendTo('#amt');
                }else if (element.is('#applied_to')) {
                    error.appendTo('#papply');
                } else {
                    element.after(error);
                }
                
            }
        });
    });

    
    function trustPaymentConfitmation() {
        if (!$('#DepositTrustFund').valid()) {
            afterLoader();
            return false;
        } else {
            didTrustPayment();
            return false;
        }
    }
    function didTrustPayment() {
        var f = $.number($('#amountFirst').val(), 2);
        var currentAmt = f;
        swal({
            title: 'Confirm the deposit amount of $' + currentAmt + '?',
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            confirmButtonText: 'Deposit Confirmation',
            cancelButtonText: 'Close',
            confirmButtonClass: 'btn btn-success ml-3',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false,
            reverseButtons: true
        }).then(function () {
            beforeLoader();
            var dataString = '';
            dataString = $("#DepositTrustFund").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/dashboard/saveDepositIntoTrustPopup", // json datasource
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
                        $('#depositIntoTrustAccount').animate({
                            scrollTop: 0
                        }, 'slow');
                        afterLoader();
                        return false;
                    } else {
                        afterLoader();
                        setTimeout(function () {
                            $("#depositIntoTrustAccount").modal("hide")
                        }, 100);
                        swal('Deposit Successful!', res.msg, 'success');
                        if($("#invoiceListw").length > 0) {
                            setTimeout(function () {
                            window.location.reload();
                            }, 1000);
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

</script>
