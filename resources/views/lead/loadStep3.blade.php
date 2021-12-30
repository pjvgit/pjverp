
<form class="createStep3" id="createStep3" name="createStep3" method="POST">
    @csrf
    <input type="hidden" name="case_id" value="{{$case_id}}"">
    <input class="form-control" value="{{($UserMaster->id)??''}}" id="id" maxlength="250" name="id" type="hidden">
    <input class="form-control" value="{{($LeadAdditionalInfo->id)??''}}" id="id" maxlength="250" name="user_id" type="hidden">
    <div class=" col-md-12" bladeFile="resources/views/lead/loadStep3.blade.php">
        
    <div id="showError3" style="display:none;"></div>      
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Billing Contact</label>
            <div class="col-md-10 form-group mb-3">
                <select id="billing_contact" name="billing_contact" class="form-control custom-select col">
                    <option value=""></option>
                    <?php 
                        foreach($selectdUSerList as $ksul=>$vsul){?>
                    <option value="{{$vsul->id}}" selected="selected" >{{$vsul->first_name}} {{$vsul->last_name}}</option>
                    <?php } ?>
                </select>
                <small>Choosing a billing contact allows you to batch bill this case.</small>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Billing Method</label>
            <div class="col-md-10 form-group mb-3">
                <select onchange="selectMethod();" id="billingMethod" name="billingMethod"
                    class="form-control custom-select col">
                    <option value=""></option>
                    <option value="hourly">Hourly</option>
                    <option value="contingency">Contingency</option>
                    <option value="flat">Flat Fee</option>
                    <option value="mixed">Mix of Flat Fee and Hourly</option>
                    <option value="pro_bono">Pro Bono</option>
                </select>
            </div>
        </div>
        <div class="form-group row" id="billing_rate_text">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Flat fee Amount</label>
            <div class="input-group mb-3 col-sm-5">
                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                <input class="form-control case_rate number" name="default_rate" maxlength="10" type="text"
                    aria-label="Amount (to the nearest dollar)">
            </div>
        </div>
        <a class="ml-2" href="javascript:void(0);"  onclick="goBack()" rel="noopener noreferrer">Go back</a>

        <div class="form-group row float-right">
            <button class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
                <span class="ladda-label">Continue to staff</span>
            </button>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader3").css('display', 'none');
        $("#billing_rate_text").hide();
        // $('input.number').keyup(function(event) {
        //     // skip for arrow keys
        //     if(event.which >= 37 && event.which <= 40) return;
        //     // format number
        //     $(this).val(function(index, value) {
        //         return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        //     });
        // });

        // $('.number').mask("#,##0.00", {reverse: true});

        $('input.number').keyup(function(event) {
            // skip for arrow keys
            if(event.which >= 37 && event.which <= 40) return;
            // format number
            $(this).val(function(index, value) {
                if(value.split('.').length>2) 
                    return value =value.replace(/\.+$/,"");
                return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        });
    });

    $('#createStep3').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader3").css('display', 'block');
        e.preventDefault();

        var dataString = $("#createStep3").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveStep3", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader3").css('display', 'block');
                if (res.errors != '') {
                    $('#showError3').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError3').append(errotHtml);
                    $('#showError3').show();
                    $("#innerLoader3").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    $("#AddCaseModel").scrollTop(0);
                    return false;
                } else {
                    $('#showError3').html('');
                    loadStep4(res);
                }
            }
        });
    });

    function loadStep4(res) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadStep4", // json datasource
            data: {
                "case_id": res.case_id
            },
            success: function (res) {
                $('#smartwizard').smartWizard("next");
                $("#innerLoader3").css('display', 'none');
                $("#step-4").html(res);
                $("#preloader").hide();
            }
        })
        return false;
    }
    function selectMethod() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#billingMethod option:selected").val();
        if (selectdValue == 'mixed' || selectdValue == 'flat') {
            $("#billing_rate_text").show();
        } else {
            $("#billing_rate_text").hide();
        }
    }
    function goBack(){
        $("#case_id").val( localStorage.getItem("case_id"));
        $('#smartwizard').smartWizard("prev");
        return false;
    }
</script>
