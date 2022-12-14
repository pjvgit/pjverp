<div data-testid="retainer-request-modal">
    <form class="addRequestForm" id="addRequestForm" name="addRequestForm" method="POST">
        <span class="showError"></span>
        @csrf
        <div class="modal-body retainer-requests-popup-content">
            <div class="row">
                <div class="col-7">
                    <div class="form-group row">
                        <label for="contact" class="col-4 pt-2 ">Contact</label>
                        <div class="contact-autocomplete-wrapper col-8">
                            <select class="form-control caller_name select2" id="contact" onchange="reloadClientAmount()" name="contact"
                                style="width: 100%;" placeholder="Search for an existing contact or company">
                                <option></option>
                                <optgroup label="Client">
                                    <?php foreach($ClientList as $key=>$val){ 
                                        if($val->email==NULL){
                                            $isEmail="no";
                                        }else{
                                            $isEmail="yes";
                                        }?>
                                    <option uType="client" isemail="{{$isEmail}}" value="{{$val->id}}" <?php if($val->id==$client_id){ echo "selected=selected";} ?> >{{substr($val->name,0,200)}} (Client)
                                    </option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="Company">
                                    <?php foreach($CompanyList as $CompanyListKey=>$CompanyListVal){ ?>
                                    <option uType="company"  isemail={{$val->email}} value="{{$CompanyListVal->id}}">
                                        {{substr($CompanyListVal->first_name,0,200)}} (Company)</option>
                                    <?php } ?>
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
                                    <input class="form-control input-date" value="{{date('m/d/Y')}}" id="due_date"
                                        maxlength="250" name="due_date" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" data-testid="user-bank-account-autocomplete"><label for="bank-account"
                            class="d-block col-4 pt-2 ">Deposit Into</label>
                        <div class="deposit-account-select-wrapper col-8">
                            <div class="Select has-value is-searchable Select--single">
                                <select class="form-control caller_name select2" id="deposit_into" name="deposit_into"
                                    style="width: 100%;" placeholder="Select user's account...">
                                    <option></option>
                                    <option selected="selected">Trust Account (${{number_format($UsersAdditionalInfo->trust_account_balance,2)}})</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row"><label for="retainer-request-email-notes-el"
                            class="col-4 pt-2 d-block ">Email Message</label>
                        <div class="email-notes-wrapper col-8">
                            <textarea name="message"  id="retainer-request-email-notes-el" class="retainer-request-email-message form-control"
                                placeholder="Type your personal message here..."></textarea>
                            <div data-testid="retainer-request-character-counter-container"
                                id="retainer-request-character-counter-container"
                                class="helper-text mt-1 text-right text-muted">
                                
                              </div>
                        </div>
                    </div>
                    <div class="row form-group"><span class="col-4 pt-2">Online Payments</span>
                        <div data-testid="online-payments-text"
                            class="col-8 form-control-plaintext text-danger selenium-online-payments-text">Disabled
                        </div>
                    </div>
                    <div class="">
                        <div data-testid="payment-section">
                            <div class="row form-group">
                                <div class="col-12 pt-2">
                                    <div class="payment-ads-borders">
                                        <div data-testid="retainer-request-get-paid-now-ad"
                                            class="row retainer-request-get-paid-now-ad pt-3 pb-3">
                                            <div class="col-6 text-center payment-ads-border-right">
                                                <div>
                                                    <h6 class="pt-1 pb-2 font-weight-bold text-muted">Get Paid Faster
                                                        with {{config('app.name')}} Payments</h6>
                                                </div><a class="btn btn-outline-secondary m-1 btn-rounded " href=""
                                                    target="_blank" rel="noopener noreferrer">Learn More</a>
                                            </div>
                                            <div class="col-6 text-center text-muted">
                                                <div class="pb-3">Provide your clients with the simplest way to pay
                                                    online ??? no login required.</div>
                                                <div>No monthly fee or setup fee.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </span>
                <div class="col-5">
                    <div data-testid="contact-balance">
                        <div class="pb-2 text-muted">
                            <div class="row selenium-balance-value-amount">
                                <div class="col-9">Current Trust Balance:</div>
                                <div class="col-3" data-testid="current-balance">
                                    $<span id="current-balance">
                                       {{number_format($UsersAdditionalInfo->trust_account_balance,2)}}
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-9">Minimum Trust Amount Required:</div>
                                <div class="col-3" data-testid="minimum-trust-balance">
                                    $<span id="minimum-trust-balance">
                                    {{number_format($UsersAdditionalInfo->minimum_trust_balance,2)}}
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
        afterLoader();
        $("#addRequestForm").validate({
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
                } else {
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
                            $("#addRequestFund").modal("hide")
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
                        $("#minimum-trust-balance").html(res.minimum_trust_balance);
                        $("#current-balance-list-down").text(res.minimum_trust_balance);
                        $('#deposit_into').html('<option selected="selected" value="'+res.freshData.user_id+'">Trust Account  ($'+res.trust_account_balance+')</option>'); 
                        $(".text-muted").show();
                        $('#disabledArea').removeClass('retainer-request-opaque');
                        $('.submit').removeAttr("disabled");  
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
                $('#deposit_into').html('<option selected="selected" value="'+res.freshData.user_id+'">Trust Account  ($'+res.trust_account_balance+')</option>'); 
            }
        })
    }
</script>
