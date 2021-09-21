
<form class="depositForm" id="depositForm" name="depositForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12" id="contact_div"
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="inputEmail3" class="col-form-label">Select Contact</label>
                <select class="form-control contact select2" id="contact" name="contact">
                    <option></option>
                    <optgroup label="Client">
                        @forelse (firmClientList() as $key => $item)
                            <option value="{{$item->id}}">{{$item->name}} 
                                ({{ getUserTypeText()[$item->user_level] }})
                            </option>
                        @empty
                        @endforelse
                    </optgroup>
                    <optgroup label="Comapny">
                        @forelse (firmCompanyList() as $key => $item)
                            <option value="{{$item->id}}">{{$item->name}} 
                                ({{ getUserTypeText()[$item->user_level] }})
                            </option>
                        @empty
                        @endforelse
                    </optgroup>
                </select>
                <span class="contact_error" ></span>
            </div>
        </div>
    </div>
    <div id="allocate-funds-container" style="display: none;">
        <div class="col-md-12">
            <div class="row form-group">
                <div class="col-md-12">
                    <label for="user-bank-account-select-field" class="col-form-label">Select Account</label>
                    <select class="form-control" id="user_account" name="user_account">
                        <option value="">Select a user's account</option>
                        <option value="trust">Trust Account</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row form-group">
                <div class="col-md-12">
                    <label class="col-form-label">Allocate Funds 
                        <span id="allocate-funds-tooltip" data-toggle="tooltip" data-html="true" title="<b>Now you can earmark trust funds by case:</b> <br> - Keep each case's trust balance seperate <br> - Prevent improperly applying trust funds <br><b>Note:</b> select client name to keep trust funds on the client level (Unallocated option)"><i class="fas fa-info-circle ml-1"></i></span>
                    </label>
                    <select class="form-control select2-option" id="allocate_fund" name="allocate_fund" style="width: 100%;">
                        <option value=""></option>
                    </select> 
                    <span class="allocate_fund_error" ></span>
                </div>
            </div>
        </div>
        <hr>
        <div class="col-md-12">
            <div class="row form-group">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary" style="float: right;" id="next_btn">Next</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $(".select2-option").select2({
            theme: "classic",
        });
        localStorage.setItem("selectedContact", null);
        afterLoader();
        $("#contact").select2({
            allowClear: true,
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            dropdownParent: $("#depositIntoTrust"),
        });
        $('#contact').on('select2:select', function (e) {
            var data = e.params.data;
            getClientCases(data.id);
            localStorage.setItem("selectedContact", data.id);
            // $("#depositIntoTrust").modal("hide");
            // depositIntoTrustPopup(localStorage.getItem("selectedContact"));
            // $("#depositIntoTrustAccount").modal("show");
        });

        /* $("#depositForm").validate({
            rules: {
                contact: "required",
                allocate_fund: "required",
                user_account: "required",
            },
            errorPlacement: function (error, element) {
                if (element.attr("name") == "allocate_fund")
                error.insertAfter(".allocate_fund_error");
                else if (element.attr("name") == "contact")
                error.insertAfter(".contact_error");
                else
                error.insertAfter(element);
            },
        }); */
    });

function getClientCases(clientId) {
    $.ajax({
        url: baseUrl+"/bills/dashboard/depositIntoTrust/clientCases",
        type: 'POST',
        data: {user_id: clientId},
        success: function(data) {
            $("#allocate-funds-container").show();
            if(data.result.length > 0) {
                var optgroup = "<optgroup label='Allocate to case'>";
                $.each(data.result, function(ind, item) {
                    optgroup += "<option value='" + item.id + "'>" + item.case_title +"(Balance $"+item.total_allocated_trust_balance+")" + "</option>";
                });
                
                optgroup += "</optgroup>"
                $('#allocate_fund').append(optgroup);
            }
            var optgroup = "<optgroup label='Unallocated'>";
            if(data.user) {
                optgroup += "<option value='" + data.user.id + "'>" + data.user.full_name +" ("+data.user.user_type_text+") (Balance $"+data.user.user_additional_info.trust_account_balance+")" + "</option>";
            }
            optgroup += "</optgroup>"
            $('#allocate_fund').append(optgroup);
            $(".select2-option").trigger('chosen:updated');;
        }
    });
}

$("#next_btn").on("click", function() {
    if($("#depositForm").valid()) {
        $("#depositIntoTrust").modal("hide");
        var caseId = '';
        var label = $('#allocate_fund :selected').parent().attr('label');
        if(label != "Unallocated") {
            caseId = $('#allocate_fund :selected').val();
        }
        depositIntoTrustPopup(localStorage.getItem("selectedContact"), caseId);
        $("#depositIntoTrustAccount").modal("show");
    }
})
</script>
