<div data-testid="retainer-request-modal">
    <form class="addRequestForm" id="addRequestForm" name="addRequestForm" method="POST">
        <span class="showError"></span>
        @csrf
        <div class="modal-body retainer-requests-popup-content">
            <div class="row" bladefile="resources/views/client_dashboard/billing/addFundRequestEnrty.blade.php">
                <div class="col-7">
                    <div class="form-group row">
                        <label for="contact" class="col-4 pt-2 ">Contact</label>
                        <div class="contact-autocomplete-wrapper col-8">
                            <select class="form-control caller_name select2" id="contact" onchange="reloadClientAmount()" name="contact"
                                style="width: 100%;" placeholder="Search for an existing contact or company">
                                <option></option>
                                <optgroup label="Client">
                                    @forelse ($ClientList as $key => $item)
                                        <option uType="client" value="{{$item->id}}" {{ ($item->id == $client_id) ? "selected" : "" }} isemail="{{ ($item->email) ? 'yes' : 'no' }}">{{$item->name}} 
                                            ({{ getUserTypeText()[$item->user_level] }})
                                        </option>
                                    @empty
                                    @endforelse
                                </optgroup>
                                <optgroup label="Comapny">
                                    @forelse ($CompanyList as $key => $item)
                                        <option uType="company" value="{{$item->id}}" {{ ($item->id == $client_id) ? "selected" : "" }} isemail="{{ ($item->email) ? 'yes' : 'no' }}" >{{$item->name}} 
                                            ({{ getUserTypeText()[$item->user_level] }})
                                        </option>
                                    @empty
                                    @endforelse
                                </optgroup>
                                <optgroup label="Lead">
                                    @forelse ($LeadList as $key => $item)
                                        <option uType="lead" value="{{$item->id}}" {{ ($item->id == $client_id) ? "selected" : "" }} isemail="{{ ($item->email) ? 'yes' : 'no' }}" >{{$item->name}}                                             ({{ getUserTypeText()[$item->user_level] }})
                                        </option>
                                    @empty
                                    @endforelse
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <span id="disabledArea">
                    <div class="form-group row"><label for="amount" class="col-4 pt-2 ">Amount</label>
                        <div class="amount-holder col-8">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span></div>
                                <input id="amount" name="amount" class="form-control number" value="" maxlength="50">
                            </div>
                            <span id="amterror"></span>
                        </div>
                    </div>
                    <div class="form-group row"><label for="due-date" class="col-4 pt-2">Due Date</label>
                        <div class="date-input-wrapper col-8">
                            <div class="">
                                <div>
                                    <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="due_date"
                                        maxlength="250" name="due_date" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" data-testid="user-bank-account-autocomplete">
                        <label for="bank-account" class="d-block col-4 pt-2 ">Deposit Into</label>
                        <div class="col-8">
                            <select class="form-control caller_name" id="deposit_into" name="deposit_into" style="width: 100%;" placeholder="Select user's account...">
                                <option></option>
                                <option value="trust">Trust Account</option>
                                @if(getInvoiceSetting() && getInvoiceSetting()->is_non_trust_retainers_credit_account == "yes" && $isFromTrustAllocation == 'no')
                                <option value="credit">Credit Account</option>
                                @endif
                            </select>
                            <span class="deposit_into_error" ></span>
                        </div>
                    </div>
                    <div class="form-group row" id="allocate_fund_div">
                        <label class="d-block col-4 pt-2">Allocate Funds
                            <span id="allocate-funds-tooltip" data-toggle="tooltip" data-html="true" title="<b>Now you can earmark trust funds by case:</b> <br> - Keep each case's trust balance seperate <br> - Prevent improperly applying trust funds <br><b>Note:</b> select client name to keep trust funds on the client level (Unallocated option)"><i class="fas fa-info-circle ml-1"></i></span>
                        </label>
                        <div class="col-8">
                            <select class="form-control select2-option" id="allocate_fund" name="allocate_fund" style="width: 100%;" disabled>
                                <option value=""></option>
                            </select> 
                            <span class="allocate_fund_error" ></span>
                        </div>
                        <input type="hidden" name="case_id" id="case_id">
                    </div>
                    <div class="form-group row"><label for="retainer-request-email-notes-el"
                            class="col-4 pt-2 d-block ">Email Message</label>
                        <div class="email-notes-wrapper col-8">
                            <textarea name="message"  id="retainer-request-email-notes-el" class="retainer-request-email-message form-control"
                                placeholder="Type your personal message here...">{{ @getInvoiceSetting()->request_funds_preferences_default_msg }}</textarea>
                            <div data-testid="retainer-request-character-counter-container"
                                id="retainer-request-character-counter-container"
                                class="helper-text mt-1 text-right text-muted">
                                
                              </div>
                        </div>
                    </div>
                    <div class="row form-group"><span class="col-4 pt-2">Online Payments</span>
                        @if(getFirmOnlinePaymentSetting() && getFirmOnlinePaymentSetting()->is_accept_online_payment == 'yes')
                            <span class="col-8 text-success">Enabled</span>
                        @else
                            <span class="col-8 text-danger">Disabled</span>
                        @endif
                    </div>
                    <div class="border-top"></div>
                </div>
            </span>
                <div class="col-5">
                    <div data-testid="contact-balance">
                        <div class="pb-2 text-muted">
                            {{-- <div class="row selenium-balance-value-amount">
                                <div class="col-9">Current Trust Balance:</div>
                                <div class="col-3" data-testid="current-balance">
                                    $<span id="current-balance">
                                       {{number_format($UsersAdditionalInfo->trust_account_balance ?? 0,2)}}
                                    </span>
                                </div>
                            </div> --}}
                            <div class="row">
                                <div class="col-9">Minimum Trust Amount Required:</div>
                                <div class="col-3" data-testid="minimum-trust-balance">
                                    $<span id="minimum-trust-balance">
                                    {{number_format($UsersAdditionalInfo->minimum_trust_balance ?? 0,2)}}
                                </span>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-2">
                        <h6>Preview Email</h6>
                        <div class="helper-text">Clients will receive a request email.</div>
                        <div>
                            <a href="#" target="_blank" rel="noopener noreferrer">What will my client see?</a>
                        </div>
                        <div class="pt-2"><i class="sample-request-funds-email"></i></div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary m-1 submit" id="submitButton" type="submit">Send</button>
            </div>
    </form>
</div>

<style>
    .payment-ads-border-right {
        border-right: 2px solid #CCC;
    }
    .payment-ads-borders {
        border-bottom: 2px solid #CCC;
        border-top: 2px solid #CCC;
    }
    .retainer-request-opaque {
        opacity: .2;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        reloadClientAmount();
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'startDate': "dateToday",
            'todayHighlight': true
        });

        $("#trust_account1").select2({
            theme: "classic",
            placeholder: "Search user's account",
        });

        $("#contact").select2({
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#addRequestFund"),
        });
        $('#addEmailToClient .close, #addEmailToClient .close-email-modal').on('click', function () {
            $("#contact").val("").trigger('change');
        });
        afterLoader();
        $("#addRequestForm").validate({
            rules: {
                payment_method: {
                    required: true,
                },
                amount: {
                    required: true,
                    minStrict: true,
                },
                deposit_into: {
                    required: true,
                },
                allocate_fund: {
                    required: true,
                }
            },
            messages: {
                payment_method: {
                    required: "Payment type is required",
                },
                amount: {
                    required: "Invalid amount",
                },
                deposit_into: {
                    required: "Deposit Account is required",
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#payment_method')) {
                    error.appendTo('#ptype');
                } else if (element.is('#amount')) {
                    error.appendTo('#amterror');
                } else if (element.attr("name") == "allocate_fund")
                    error.insertAfter(".allocate_fund_error");
                else if (element.attr("name") == "deposit_into")
                    error.insertAfter(".deposit_into_error");
                else {
                    element.after(error);
                }
            }
        });

        $('#addRequestForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#addRequestForm').valid()) {
                afterLoader();
                return false;
            }
            
            if($("#contact option:selected").attr('isemail')=="no"){
                var contactSelectd=$("#contact option:selected").val();
                afterLoader();
                $("#preloader").hide();
                $('.submit').prop("disabled", false);
                $('#disabledArea').removeClass('retainer-request-opaque');
                $("#addEmailToClient").modal("show");
                $("#client_id_for_email").val(contactSelectd);
                return false;
            }
            var caseId = '';
            var label = $('#allocate_fund :selected').parent().attr('label');
            if(label != "Unallocated") {
                caseId = $('#allocate_fund :selected').val();
            }
            $("#case_id").val(caseId);
            var dataString = '';
            dataString = $("#addRequestForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/saveRequestFundPopup", // json datasource
                data: dataString ,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        // $.each(res.errors, function (key, value) {
                        //     errotHtml += '<li>' + value + '</li>';
                        // });
                        errotHtml += '<li>' + res.errors + '</li>';
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        swal('Success!', res.msg, 'success');
                        setTimeout(function () {
                            $("#addRequestFund").modal("hide");
                            window.location.reload();
                        }, 2000);
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
        
        $(document).on('keypress , paste', '.number', function (e) {
            if (/^-?\d*[,.]?(\d{0,3},)*(\d{3},)?\d{0,3}$/.test(e.key)) {
                $('.number').on('input', function () {
                    e.target.value = numberSeparator(e.target.value);
                });
            } else {
                e.preventDefault();
                return false;
            }
        });

        $("#contact").on("select2:unselecting", function(e) {
            $('#disabledArea').addClass('retainer-request-opaque');
        });

        $(".select2-option").select2({
            theme: "classic",
            placeholder: "Select funds allocation",
            allowClear: true,
            minimumResultsForSearch: Infinity
        });
        $("#deposit_into").select2({
            theme: "classic",
            placeholder: "Select a bank account",
            allowClear: true,
            minimumResultsForSearch: Infinity
        });
    });

    function countChar(val) {
        var len = val.value.length;
        if (len > 160) {
          val.value = val.value.substring(0, 160);
        } else {
          $('#charNum').text(len);
        }
    }

    function reloadClientAmount() {
        $("#preloader").show();
        var contactSelectd=$("#contact option:selected").val();
        // $("#addEmailToClient").modal("show");
        // $("#client_id_for_email").val(contactSelectd);
        if(contactSelectd==''){
            $('#disabledArea').addClass('retainer-request-opaque');
            $(".text-muted").hide();
            $("#preloader").hide();
            $('.submit').prop("disabled", true);
        }else{
            if($("#contact option:selected").attr('isemail')=="no"){
                $("#addEmailtouser")[0].reset();
                $("#showErrorOver").hide();
                $("#email").val('');
                $("#preloader").hide();
                $('.submit').prop("disabled", false);
                $('#disabledArea').removeClass('retainer-request-opaque');
                $("#addEmailToClient").modal("show");
                $("#client_id_for_email").val(contactSelectd);
            }else{
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/clients/reloadAmount", 
                    data: {"user_id": contactSelectd},
                    success: function (res) {
                        $("#preloader").hide();

                        $("#current-balance").html(res.trust_account_balance);
                        $('#current-balance').number(true, 2);

                        $("#minimum-trust-balance").html(res.minimum_trust_balance);
                        $('#minimum-trust-balance').number(true, 2);

                        $("#current-balance-list-down").text(res.minimum_trust_balance);
                        // $('#deposit_into').html('<option value="trust">Trust Account</option>');
                        if(res.is_non_trust_retainer == "yes" && $("#deposit_into option[value='credit']").length < 0) {
                            $('#deposit_into').append('<option value="credit">Credit Account</option>'); 
                        }
                        $(".text-muted").show();
                        $('#disabledArea').removeClass('retainer-request-opaque');
                        $('.submit').removeAttr("disabled"); 
                        if($("#deposit_into").val() != '') { 
                            $('#allocate_fund').html("");
                            if(res.clientCases.length > 0) {
                                if($("#deposit_into").val() == "trust") {
                                    var optgroup = "<optgroup label='Allocate to case'>";
                                    $.each(res.clientCases, function(ind, item) {
                                        optgroup += "<option value='" + item.case_id + "' data-minimum-trust-balance='"+item.minimum_trust_balance+"'>" + item.case.case_title +"(Balance $"+item.allocated_trust_balance+")" + "</option>";
                                    });
                                    optgroup += "</optgroup>"
                                    $('#allocate_fund').append(optgroup);
                                }
                            }
                            var leadAllocateAmount = 0;
                            if(res.leadCases.length > 0) {
                                if($("#deposit_into").val() == "trust") {
                                    var optgroup = "<optgroup label='Allocate to case'>";
                                    $.each(res.leadCases, function(ind, item) {
                                        optgroup += "<option value='" + item.user_id + "'>" + item.potential_case_title +"(Balance $"+item.allocated_trust_balance ?? 0.00+")" + "</option>";
                                        leadAllocateAmount += item.allocated_trust_balance ?? 0.00;
                                    });
                                    optgroup += "</optgroup>"
                                    $('#allocate_fund').append(optgroup);
                                }
                            }
                            var optgroup = "<optgroup label='Unallocated'>";
                            if(res.freshData) {
                                if($("#deposit_into").val() == "credit") {
                                    optgroup += "<option value='" + res.freshData.user_id + "'>" + res.freshData.user.full_name +" ("+res.freshData.user.user_type_text+") (Balance $"+(res.freshData.credit_account_balance)+")" + "</option>";
                                } else {
                                    optgroup += "<option value='" + res.freshData.user_id + "' data-minimum-trust-balance='"+res.freshData.minimum_trust_balance+"'>" + res.freshData.user.full_name +" ("+res.freshData.user.user_type_text+") (Balance $"+(res.freshData.unallocate_trust_balance - leadAllocateAmount)+")" + "</option>";
                                }
                            }
                            optgroup += "</optgroup>"
                            $('#allocate_fund').append(optgroup);
                            $(".select2-option").trigger('chosen:updated');
                            $('#allocate_fund').trigger("change");
                        }
                    }
                })
            }
        }
    }

    function refreshDetail() {
        var contactSelectd=$("#contact option:selected").val();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/reloadAmount", 
            data: {"user_id": contactSelectd},
            success: function (res) {
                $("#preloader").hide();
                $('.submit').prop("disabled", false);
                $("#contact option:selected").attr('isemail','yes');
                $("#current-balance").html(res.trust_account_balance);
                $("#minimum-trust-balance").html(res.minimum_trust_balance);
                $("#current-balance-list-down").text(res.minimum_trust_balance);
                // $('#deposit_into').html('<option value="'+res.freshData.user_id+'">Trust Account  ($'+res.trust_account_balance+')</option>'); 
                if(res.is_non_trust_retainer == "yes" && $("#deposit_into option[value='credit']").length < 0) {
                    $('#deposit_into').append('<option value="credit">Credit Account ($'+res.credit_account_balance+')</option>');
                }
            }
        })
    }

$("#deposit_into").on("change", function() {
    if($(this).val() != '') {
        $('#allocate_fund').prop("disabled", false);
    } else {
        $('#allocate_fund').prop("disabled", true);
    }
    reloadClientAmount();
    if($(this).val() == 'trust') {
        $("#allocate-funds-tooltip").show();
        $("#allocate_fund_div").show();
    } else {
        $("#allocate-funds-tooltip").hide();
        $("#allocate_fund_div").hide();
    }
});

$(document).on("change", "#allocate_fund", function() {
    var minimumBal = $(this).find(':selected').attr("data-minimum-trust-balance");
    if(minimumBal != '') {
        $('#minimum-trust-balance').text(minimumBal);
    } else {
        $('#minimum-trust-balance').text("0.00");
    }
});
</script>
