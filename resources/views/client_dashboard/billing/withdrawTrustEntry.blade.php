<div class="tab-content" id="myTabContent">
    <div class="showError" style="display:none"></div>
    <div class="tab-pane fade  active show" id="tba2" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="addWithdrawForm" id="addWithdrawForm" name="addWithdrawForm" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" id="client_id" value="{{$userData->id}}" name="client_id">
            <div class="row form-group">
                <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Trust Account</label>
                <div class="col-12 col-sm-9">
                    <div>
                        <div class="">
                            <div class="input-group">
                                <select class="form-control caller_name select2" id="trust_account" name="trust_account" style="width: 100%;" placeholder="Select user's account...">
                                    <option></option>
                                    <optgroup label="Withdraw from a case">
                                        @forelse ($userCases as $item)
                                            <option value="{{ $item->id }}" data-amount={{ $item->total_allocated_trust_balance }}>{{ ucfirst($item->case_title) }} (Balance ${{ number_format($item->total_allocated_trust_balance, 2) }})
                                        @empty
                                        @endforelse
                                    </optgroup>
                                    <optgroup label="Withdraw from unallocated">
                                        <option data-amount={{ $UsersAdditionalInfo->unallocate_trust_balance }}>Trust Account (Balance ${{number_format(($UsersAdditionalInfo->unallocate_trust_balance),2)}})</option>
                                    </optgroup>
                                </select>
                                <input type="hidden" name="case_id" id="case_id" >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row form-group">
                <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Date</label>
                <div class="col-12 col-sm-9">
                    <div>
                        <div class="">
                            <div class="input-group">
                                <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="payment_date" maxlength="250"
                        name="payment_date" type="text">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row form-group">
                <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Amount</label>
                <div class="col-12 col-sm-9">
                    <div>
                        <div class="">
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
            <br>
            <div class="">
                <a class="collapsed2" id="collapsed2" data-toggle="collapse" href="javascript:void(0);"
                    data-target="#accordion-item-icons-4" aria-expanded="false">
                    Transfer to Operating Account  <i class="fas fa-sort-down align-text-top"></i>
                </a>
            </div>
            <br>
            <div class="collapse" id="accordion-item-icons-4" style="">
                <div class="row form-group">
                    <label for="notes" class="text-sm-right pr-0 col-sm-3 col-form-label">Trust Account</label>
                    <div class="col-12 col-sm-9">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <select class="form-control caller_name select2" id="select_account" name="select_account"
                                style="width: 100%;" placeholder="Select a bank account...">
                                <option></option>
                                <option value="Operating Account">Operating Account </option>
                            </select>
                                </div>
                            </div>
                        </div>
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
                <button class="btn btn-primary m-1 submit" id="submitButton" type="submit"  >Withdraw</button>
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

        $("#trust_account").select2({
            placeholder: "Select user's account",
            theme: "classic",

        });
        $("#select_account").select2({
            placeholder: "Select a bank account",
            theme: "classic",

        });

        $("#trust_account").on("change", function() {
            var label=$('#trust_account :selected').parent().attr('label');
            if(label == "Withdraw from a case") {
                $("#case_id").val($(this).val());
            } else {
                $("#case_id").val("");
            }
            $("#amount").attr("data-max-amount", $('#trust_account :selected').attr("data-amount"));
        });
        
        afterLoader();
        $("#addWithdrawForm").validate({
            rules: {
                trust_account: {
                    required: true,
                },
                amount: {
                    required: true,
                    maxamount: true,
                }
            },
            messages: {
                trust_account: {
                    required: "Account Is Required",
                },
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
    $('#addWithdrawForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#addWithdrawForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#addWithdrawForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/saveWithdrawFromTrust", // json datasource
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
        $("#collapsed2").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top');
    });
</script>
