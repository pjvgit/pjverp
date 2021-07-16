<form class="updateBillingInfoForm" id="updateBillingInfoForm" name="updateBillingInfoForm" method="POST">
    @csrf
    <input class="form-control" value="{{$case_id}}" id="case_id" name="case_id" type="hidden">
    <div id="showError" style="display:none"></div>
    <div class="col-md-12">
        <div class="form-group row">
            <p class="col-sm-7">
                Which contact will be the billing point of contact for this case?
            </p>
            <div class="col-7 form-group mb-3">
                <select onchange="selectStaff();" id="staff_user_id" name="staff_user_id"
                    class="form-control custom-select col">
                    <option value="">- Select a Billing Contact -</option>
                    <?php foreach($caseCllientSelection as $ksul=>$vsul){?>
                    <option <?php if(isset($caseMasterDefaultBiller['selected_user']) &&  $caseMasterDefaultBiller['selected_user'] == $vsul->selected_user){ echo "selected=selected"; }?> value="{{$vsul->selected_user}}"> {{$vsul->first_name}} {{$vsul->last_name}}</option>
                    <?php } ?>
                </select>
            </div>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
        <div class="alert alert-info my-2 pb-0">
            @if(!$caseMasterDefaultBiller)
            <p class="court-case-wizard-billing-user-help">This case must have one linked contact to create invoices. Link a contact from the <a href="{{ route("info", $caseDefaultBiller->case_unique_number) }}" >case details page.</a></p>
            @else
            <p class="court-case-wizard-billing-user-help">Tip: To include this case when batch creating invoices, you must select a billing contact. You can change this selection later.</p>
            @endif
        </div>
        <div class="form-group row">
            <p class="col-sm-7">
                How should this case be billed?
            </p>
            <div class="col-7 form-group mb-3">
                <select onchange="selectMethod();" id="billingMethod" name="billingMethod"
                    class="form-control custom-select col">
                    <option value=""></option>
                    <option <?php if($caseDefaultBiller['billing_method']=='hourly'){ echo "selected=selected"; }?> value="hourly">Hourly</option>
                    <option <?php if($caseDefaultBiller['billing_method']=='contingency'){ echo "selected=selected"; }?> value="contingency">Contingency</option>
                    <option <?php if($caseDefaultBiller['billing_method']=='flat'){ echo "selected=selected"; }?> value="flat">Flat Fee</option>
                    <option <?php if($caseDefaultBiller['billing_method']=='mixed'){ echo "selected=selected"; }?> value="mixed">Mix of Flat Fee and Hourly</option>
                    <option <?php if($caseDefaultBiller['billing_method']=='pro_bono'){ echo "selected=selected"; }?> value="pro_bono">Pro Bono</option>
                </select>
            </div>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
        <div class="form-group row" id="billing_rate_text">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Flat fee Amount</label>
            <div class="input-group mb-3 col-sm-5">
                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                <input class="form-control case_rate" name="default_rate" maxlength="10" id="default_rate" type="text"
                    aria-label="Amount (to the nearest dollar)" value="{{($caseDefaultBiller['billing_amount'])??'0.00'}}">
            </div>
            <span id="TypeError"></span>
        </div>
    </div>
    <div class="justify-content-between modal-footer">
        <div>
            <div>
                <?php if(isset($caseCllientUpdateCreated) && $caseCllientUpdateCreated!=''){?>
                <div><b>Originally Created: </b>{{date('l, F jS Y',strtotime($caseCllientUpdateCreated['created_at']))}} by
                    {{substr($caseCllientUpdateCreated['first_name'],0,15)}} {{substr($caseCllientUpdateCreated['last_name'],0,15)}}</div>
                <?php } ?>
                <?php if(isset($caseCllientUpdateUpdated) && $caseCllientUpdateUpdated!=''){?>
                <div><b>Last Modified: </b>{{date('l, F jS Y',strtotime($caseCllientUpdateUpdated['updated_at']))}} by
                    {{substr($caseCllientUpdateUpdated['first_name'],0,15)}}
                    {{substr($caseCllientUpdateUpdated['last_name'],0,15)}}</div>
                <?php } ?>
            </div>
        </div>
        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
        <div>
            <button type="submit" name="save" value="s" class="btn btn-primary submitbutton">Update Billing</button>
            
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#UserTypeError").css('display', 'none');

        $("#submit_with_user").attr("disabled", true);
        $("#updateBillingInfoForm").validate({
            rules: {
                staff_user_id: {
                    required: false
                },
                default_rate: {
                    number: true
                }

            },
            messages: {
                staff_user_id: {
                    required: "Please select atleast one staff member"
                },
                default_rate: {
                    
                    number: "Default rate is not a number"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                } else {
                    element.after(error);
                }
            }
        });

        $('#updateBillingInfoForm').submit(function (e) {
            e.preventDefault();
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#updateBillingInfoForm').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("#updateBillingInfoForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/overview/saveBillingContactPopup", // json datasource
                data: dataString,
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    console.log(res.errors);
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });

    });

    function selectMethod() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#billingMethod option:selected").val();
        if (selectdValue == 'mixed' || selectdValue == 'flat') {
            $("#billing_rate_text").show();
        } else {
            $("#billing_rate_text").hide();
        }
        $("#innerLoader").css('display', 'none');
    }

    <?php if(isset($caseDefaultBiller['billing_method']) && $caseDefaultBiller['billing_method']!=NULL) {
        if ($caseDefaultBiller['billing_method'] == 'mixed' || $caseDefaultBiller['billing_method'] == 'flat') {?>
            $("#billing_rate_text").show();
       <?php  } else { ?>
            $("#billing_rate_text").hide();
        <?php } ?>
    <?php } else { ?>
            $("#billing_rate_text").hide();
        <?php } ?>
</script>
