<div id="showError" style="display:none"></div>
<form class="Editnvoice m-3" id="Editnvoice" name="Editnvoice" method="POST">
    <span id="response"></span>
    @csrf
    <input class="form-control" value="{{$userData->id}}"  id="dateadded" maxlength="250" name="lead_id" type="hidden">

    <input class="form-control" value="{{$invoice_id}}"  id="dateadded" maxlength="250" name="invoice_id" type="hidden">
    <div class="row">
        <div class="col-md-6 form-group mb-3">
            <label for="firstName1">Lead</label>
            <input class="form-control" value="{{$userData->first_name}} {{$userData->last_name}}" disabled id="dateadded" maxlength="250" name="lead_name"
                type="text">
        </div>
        <div class="col-md-6 form-group mb-3">
            <label for="firstName1">Invoice Date</label>
            <input class="form-control datepicker" value="{{date('m/d/Y',strtotime($FindInvoice->invoice_date))}}" id="invoiceDate" maxlength="250"
                name="invoice_date" type="text">
        </div>
        <div class="col-md-6 form-group mb-3">
            <label for="firstName1">Invoice #</label>
            <input class="form-control" value="{{str_pad($FindInvoice->id, 6, '0', STR_PAD_LEFT)}}" disabled id="dateadded" maxlength="250" name="invoice_number" type="text">
        </div>
        <div class="col-md-6 form-group mb-3">
            <label for="firstName1">Due Date</label>
            <input class="form-control datepicker" value="{{date('m/d/Y',strtotime($FindInvoice->due_date))}}" id="dueDate" maxlength="250"
                name="due_date" type="text">
        </div>
        <div class="col-6 mb-3">
            <div class=""><label for="total_amount" class="">Amount</label>
                <div class="px-0 ">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div><input
                            id="total_amount" name="total_amount" class="form-control " value="{{$FindInvoice->total_amount}}">
                    </div>
                </div>
                <span id="afterShowError"></span>
            </div>
        </div>

        <div class="col-md-12 form-group mb-3">
            <label for="firstName1">Description</label>
            <textarea rows="10" class="form-control" name="description"
                placeholder="Notes">{{$FindInvoice->notes}}</textarea>
            <small>This description will appear in your invoice</small>
        </div>
    </div>
    <div class="mt-4 row payment-disabled-ad py-3">
        <div class="col-5 text-center">
            <p class="h6 my-auto font-weight-bold">Get Paid Faster with {{config('app.name')}} Payments</p>
        </div>
        <div class="col-7 payment-disabled-ad-message">
            <p class="ml-2 my-auto">Provide your clients with the simplest way to pay online - no login
                required.&nbsp;
                <a href="#" target="_blank" rel="noopener noreferrer">Learn More</a></p>
        </div>
    </div>
    <div class="justify-content-between modal-footer">
        <div><a href="#" target="_blank">&nbsp;</a></div>

        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
        <div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button type="submit" name="savenow" id="submit" value="payment"
                class="btn btn-secondary ladda-button example-button submit">
                Record Payment
            </button>
            <button type="submit" name="savenow" id="submit" value="sendnow"
                class="btn btn-secondary ladda-button example-button submit">
                Send Now
            </button>
            <button type="submit" name="savenow" id="submit" value="savenow"
                class="btn btn-primary ladda-button example-button submit ">
                Save
            </button>
        </div>
    </div>
    <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden">
</form>
<style>
    .payment-disabled-ad {
        border-bottom: 1px solid black;
        border-top: 1px solid black;
        color: black;
    }

    .payment-disabled-ad-message {
        border-left: 1px solid black;
        font-size: 13px;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $(".dropdown-toggle").trigger("click");

        $('#invoiceDate,#dueDate').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
             'todayHighlight': true
        });

        // $('#invoiceDate,#dueDate').datepicker({
        //     onSelect: function (dateText, inst) {},
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function (selectedDate) {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //         }
        //     }
        // });


        $(".innerLoader").css('display', 'none');
        $(".innerLoader").hide();
        $("#Editnvoice").validate({
            rules: {
                invoice_date: {
                    required: true,
                    date: true
                },
                invoice_number: {
                    required: true,
                    number:true
                },
                due_date: {
                    required: true,
                    date: true
                },
                total_amount: {
                    required: true,
                    min:{{$FindInvoice->paid_amount}} 
                }
            },
            messages: {
                invoice_date: {
                    required: "Invoice date is required.",
                },
                invoice_number: {
                    required: "Invoice number is required.",
                },
                due_date: {
                    required: "Due date is required.",
                },
                total_amount: {
                    required: "Amount is required.",
                    min: "You cannot lower the amount of this invoice below ${{ $FindInvoice->paid_amount }} </br> because payments have already been received for that amount."
                },
            },errorPlacement: function (error, element) {
                if (element.is('#total_amount')) {
                    error.appendTo('#afterShowError');
                }else {
                    element.after(error);
                }
            }
        });
    });
    $(document).on("click", ":submit", function(e){
        $("#current_submit").val($(this).val());
    });
    $('#Editnvoice').submit(function (e) {
        $(".submit").attr("disabled", true);
        $(".innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#Editnvoice').valid()) {
            $(".innerLoader").css('display', 'none');
            $('.submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#Editnvoice").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/updateInvoice", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $(".innerLoader").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    $(".innerLoader").css('display', 'none');
                    $('.submit').removeAttr("disabled");
                    // $("#Editnvoice").scrollTop(0);
                    $('#editInvoice').animate({scrollTop: 0}, 'slow');
                    return false;
                } else {
                    $("#editInvoice").modal("hide");
                    setTimeout(() => {
                    if($("#current_submit").val()=="sendnow"){                        
                        toastr.success('Invoice successfully updated.', "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });                        
                        $("#sendInvoice").modal("show");
                        sendInvoice(res.invoice_id);
                    }else if($("#current_submit").val()=="payment"){
                        toastr.success('Invoice successfully updated.', "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        $("#payInvoice").modal("show");
                        payinvoice(res.invoice_id);
                    }else{
                        toastr.success('Invoice successfully updated.', "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        window.location.reload(); 
                    }                        
                    }, 1000);
                }
            }
        });
    });
   
    $("#first_name").focus();

</script>
