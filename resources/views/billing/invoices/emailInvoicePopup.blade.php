<form class="ConfirmAccessFormPopup" id="ConfirmAccessFormPopup" name="ConfirmAccessFormPopup" method="POST">
    @csrf
    <input type="hidden" value="{{$Invoices->id}}" name="invoice_id">
    <div class="row">
        <div class="col-md-12" id="confirmAccess" bladefile="resources/views/billing/invoices/emailInvoicePopup.blade.php">
            {{-- <div>
                <div class="alert alert-info fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="w-100">
                            <p class="send-email-clients-already-shared mb-0"><strong>Note</strong>: You have already
                                shared this invoice via the Client Portal with<span class="send-emails-client-names">
                                    Client5 l and Client3 l</span>.</p>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="row pt-2 pb-0 send-to-client-list">
                <div class="col-2 px-3 text-left">Send To:</div>
                <div class="col-10 pl-0">
                    <ul class="list-unstyled mb-0">
                        <?php foreach($getAllClientForSharing as $k=>$v){ 
                            if($v->user_level==2){ ?>
                            <li class="court-case-users-row mb-2 pb-1">
                                <table class="col-12">
                                    <tbody>                                    
                                        <tr>
                                            <td>
                                                <label class="mb-0">
                                                    <input type="checkbox" class="mr-2 mb-1 checkMail" name="client[]"
                                                        id="send-email-{{$v->user_id ?? $v->id}}" value="{{$v->user_id ?? $v->id}}" data-email="{{$v->email}}"></label></td>
                                            <td class="pl-0 col-12"> {{substr($v->unm ?? ($v->first_name.' '.$v->last_name),0,100)}} (Client) </td>
                                        </tr>
                                        <tr id="mailOpen_{{$v->user_id ?? $v->id}}" style="display: none;"><td></td><td>Please enter an email address for this contact:<input id="new-email-{{$v->user_id ?? $v->id}}" class="col-12 form-control" name="new_email-{{$v->user_id ?? $v->id}}" placeholder="Enter email" value="{{$v->email}}"></td></tr>
                                    
                                    </tbody>
                                </table>
                            </li>
                        <?php } 
                        } ?>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row mt-3 pt-2 pl-3 pr-4">
                <div class="col-2 pl-0 text-left">Subject:</div>
                <div class="pl-0 col-10 ">
                    <p>New Invoice from {{$firmAddress['firm_name']}}</p>
                </div>
            </div>
            <div class="row pl-3 pr-4 pb-1">
                <div class="col-2 pl-0 text-left">Message:</div>
                <div class="pl-0 col-10 text-left"><textarea name="message" maxlength="160"
                        class="mb-2 send-email-form-custom-message form-control">Please review your attached invoice.</textarea>
                    <div class="send-email-form-helper-text helper-text mt-1 text-right text-muted mb-3"></div>
                </div>
            </div>
            <div class="row pl-3 pr-4">
                <div class="col-2"></div>
                <div class="col-10 pl-0 text-left">
                    <p class="mb-2">Thanks,</p>
                    <p class="mb-4">{{$firmAddress['firm_name']}}</p>
                </div>
            </div>
            <div class="row align-middle px-3">
                <div class="col-2 pl-0 text-left">
                    <p class="pt-3">Attached:</p>
                </div>
                <div class="col-10 pl-0 text-left">
                    <table>
                        <tbody>
                            <tr>
                                <td class="pr-2"> <i class="paylink-pdf-preview"></i> </td>
                                <td class="pt-3 pdf-name-row">
                                    <a href="{{ route('bills/invoices/invoiceInlineView', $Invoices['invoice_token']) }}?disposition=inline" target="_blank"
                                        rel="noopener noreferrer">
                                        <p>{{"Invoice_".$Invoices['id'].".pdf"}}</p>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mt-3 pay-now-disabled-note">
                <div class="mx-3 p-4">
                    <div class=" row payment-disabled-ad py-3">
                        <div class="col-5 text-center">
                            <p class="h6 my-auto font-weight-bold">Get Paid Faster with {{config('app.name')}} Payments
                            </p>
                        </div>
                        <div class="col-7 payment-disabled-ad-message">
                            <p class="ml-2 my-auto">Provide your clients with the simplest way to pay online - no login
                                required.&nbsp;<a href="#" target="_blank" rel="noopener noreferrer">Learn More</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="modal-footer pb-1 mt-0 send-email-form-footer">
        <div class="loader-bubble loader-bubble-primary innerLoader mr-5 mb-4" id="innerLoader" style="display: none;">
        </div>
        <div>
            <a href="#" target="_blank" rel="noopener noreferrer">What will my client see?</a>
        </div>
        <div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button type="submit" class="btn btn-primary ladda-button example-button m-1 submit">Send</button>
        </div>
    </div>

</form>
<style>
    i.paylink-pdf-preview {
        background-image: url('{{BASE_URL}}svg/paylink_pdf_preview.svg');
        height: 45px;
        width: 36px;
    }
    .pay-now-disabled-note {
        background-color: #F5F5F5;
        font-size: 14px;
        margin-left: -15px;
        margin-right: -15px;
    }
    .payment-disabled-ad {
        border-bottom: 1px solid #000;
        border-top: 1px solid #000;
        color: #000;
    }
    .payment-disabled-ad-message {
        border-left: 1px solid #000;
        font-size: 13px;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {

        $('#ConfirmAccessFormPopup').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            var dataString = $("#ConfirmAccessFormPopup").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/SendEmailInvoice", // json datasource
                data: dataString,
                success: function (res) {
                    afterLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
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

        $(".checkMail").on("change", function (e) {
            var userID = $(this).val();
            if ($(this).is(":checked") && $(this).attr("data-email") == '') {
                $("#mailOpen_"+userID).show();
            }else{
                $("#mailOpen_"+userID).hide();
            }
        });
    });

</script>
