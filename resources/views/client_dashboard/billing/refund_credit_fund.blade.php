<div class="tab-content" id="myTabContent">
    <div class="showError" style="display:none"></div>
    <div class="tab-pane fade  active show" id="tba2" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="refundForm" id="refundForm" name="refundForm" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" id="client_id" value="{{$userData->id}}" name="client_id">
            <input type="hidden" id="transaction_id" value="{{$creditHistory->id}}" name="transaction_id">
            <div class="row form-group">
                <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Refund Amount:</label>
                <div class="col-12 col-sm-9">
                    <div>
                        <div class="">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input class="form-control number" style="width:50%; " maxlength="20" name="amount"
                                    id="amount" value="{{ $creditHistory->deposit_amount }}" readonly type="text" aria-label="Amount (to the nearest dollar)">
                                <small>&nbsp;</small>
                                <div class="input-group col-sm-9" id="TypeError"></div>
                                <span id="amt"></span>
                            </div>
                        </div>
                    </div>
                    <br>
                    <label class="checkbox checkbox-outline-primary">
                        <input type="checkbox" id="full_refund" checked="checked" name="payfull" data-total-amount="{{ $creditHistory->deposit_amount }}"><span>Refund entire payment</span><span class="checkmark"></span>
                    </label>

                </div>
            </div>

            <div class="row form-group">
                <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Date</label>
                <div class="col-12 col-sm-9">
                    <div>
                        <div class="">
                            <div class="input-group">
                                <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="payment_date" maxlength="250" name="payment_date" type="text">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Notes</label>
                <div class="col-12 col-sm-9">
                    <div>
                        <div class="">
                            <div class="input-group">
                                <textarea id="notes" name="notes" class="form-control " placeholder="" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            @if(in_array($creditHistory->payment_method, ["oxxo cash","spei"]))
            <div class="mt-3">
                <strong>Nota:</strong> Los reembolsos para pagos hechos en efectivo o transferencia se reflejan en el saldo en el sistema, sin embargo, el dinero debe reembolsarle al cliente de manera externa/manual. Es decir, es necesario que haga el reembolso de los fondos directamente, ya sea en efectivo, transferencia o cualquier otro método que usted prefiera. En estos casos, la comisión por el pago recibido no se le reembolsará a usted.
            </div>
            @endif
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                </a>
                <button class="btn btn-primary m-1 submit" id="submitButton" type="submit">Refund</button>
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
            'todayHighlight': true
        });

        afterLoader();
        $("#refundForm").validate({
            rules: {
                amount: {
                    required: true,
                }
            },
            messages: {
                amount: {
                    required: "Amount is required",
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#amount')) {
                    error.appendTo('#amt');
                } else {
                    element.after(error);
                }
            }
        });
    });
    $('#refundForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#refundForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#refundForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/credit/saveRefundPopup", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                beforeLoader();
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });
    $('#collapsed2').click(function () {
        $("#collapsed2").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
            'fa-sort-down align-text-top');
    });
    $('#full_refund').change(function () {
        if ($(this).is(":checked")) {
            $("#amount").val($(this).attr("data-total-amount"));
            $("#amount").attr('readonly', true);
        } else {
            // $("#amount").removeAttr('readonly');
            $("#amount").attr('readonly', false);
        }
    });

</script>
