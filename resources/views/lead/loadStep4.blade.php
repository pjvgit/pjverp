<form class="createStep4" id="createStep4" name="createStep4" method="POST">
    <div id="showError4" style="display:none"></div>
    <input type="hidden" name="case_id" value="{{$case_id}}"">
    @csrf
             
    <div class=" col-md-12">
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label">Lead Attorney</label>
        <div class="col-md-9 form-group mb-3">
            <select id="lead_attorney" name="lead_attorney" onclick="selectLeadAttorney();" class="form-control custom-select col">
                <option value=""></option>
                <?php foreach($loadFirmUser as $key=>$user){?>
                <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                <?php } ?>
            </select>
            <small>The user you select will automatically be checked in the table below.</small>
        </div>

    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label">Originating Attorney</label>
        <div class="col-md-9 form-group mb-3">
            <select onchange="selectAttorney();" id="originating_attorney" name="originating_attorney"
                class="form-control custom-select col">
                <option value=""></option>
                <?php foreach($loadFirmUser as $key=>$user){?>
                <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                <?php } ?>
            </select>
            <small>The user you select will automatically be checked in the table below.</small>
        </div>
    </div>
    <div class="form-group row" id="billing_rate_text">
        <label for="inputEmail3" class="col-sm-12 col-form-label">Who from your firm should have access to this
            case?</label>
    </div>
    <table style="table-layout: auto;" class="firm-users-table table table-sm">
        <colgroup>
            <col style="width: 8%;">
            <col>
            <col>
            <col>
            <col style="width: 20%;">
            <col style="width: 15%;">
        </colgroup>
        <thead>
            <tr>
                <th><input class="test-all-users-checkbox" id="select-all" type="checkbox"></th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>User Title</th>
                <th>Billing Rate</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($loadFirmUser as $key=>$user){?>
            <tr>
                <td><input class="test-all-users-checkbox" <?php if($user->id==Auth::User()->id){ echo "checked=checked"; } ?> type="checkbox" id="{{$user->id}}" name="selectedUSer[{{$user->id}}]"></td>
                <td>{{$user->first_name}}</td>
                <td>{{$user->last_name}}</td>
                <td>{{$user->user_title}}</td>
                <td>
                    <select  onchange="selectRate({{$user->id}});" name="rate_type[{{$user->id}}]" id="cc{{$user->id}}"
                        class="rate test-billing-rate-dropdown form-control mr-1" >
                        <option value="Default_Rate">Default Rate</option>
                        <option value="Case_Rate">Case Rate</option>
                    </select>
                </td>
                <td id="default_{{$user->id}}">
                    <?php if($user->default_rate){
                        echo "$".$user->default_rate;
                     } ?>
                </td>
                <td id="custome_{{$user->id}}" style="display:none;">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input class="form-control case_rate number" name="new_rate[{{$user->id}}]" maxlength="20"  type="text"
                            aria-label="Amount (to the nearest dollar)">
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <a class="ml-2" href="javascript:void(0);"  onclick="goBack()" rel="noopener noreferrer">Go back</a>

    <div class="form-group row float-right">
        <button class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
            <span class="ladda-label">Save & Finish</span>
        </button>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary" id="innerLoader4" style="display: none;"></div>
        </div>
    </div>
    
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader4").css('display', 'none');
        // var dataTable = $('#example').DataTable();
        $('#select-all').click(function () {
            var checked = this.checked;
            $('input[type="checkbox"]').each(function () {
                this.checked = checked;
            });
        });
    });
    $('input.number').keyup(function (event) {
        // skip for arrow keys
        if (event.which >= 37 && event.which <= 40) return;
        // format number
        $(this).val(function (index, value) {
            if (value.split('.').length > 2)
                return value = value.replace(/\.+$/, "");
            return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });
    $('#createStep4').submit(function (e) {
        $("#innerLoader4").css('display', 'block');
        e.preventDefault();
        var dataString = $("#createStep4").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveStep4", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader4").css('display', 'block');
                if (res.errors != '') {
                    $('#showError4').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        errotHtml += '<li>' + res.errors + '</li>';
                    errotHtml += '</ul></div>';
                    $('#showError4').append(errotHtml);
                    $('#showError4').show();
                    $("#innerLoader4").css('display', 'none');
                    $("#AddCaseModel").scrollTop(0);

                    return false;
                } else {

                    window.location.href=baseUrl+'/court_cases/'+res.case_unique_number+'/info';
                    // // window.location.reload();
                    // // $("#innerLoader4").css('display', 'none');
                    // // $('#AddCaseModel').modal('hide');
                    // loadStep5();
                }
            }
        });
    });

    

    function selectRate(id) {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#cc"+id+" option:selected").val();
        // alert(selectdValue); 
        if (selectdValue == 'Default_Rate') {
            $("#default_"+id).show();
            $("#custome_"+id).hide();
        } else {
            $("#custome_"+id).show();
            $("#default_"+id).hide();
        }
    }
    

    function selectAttorney() {
        var selectdValue = $("#originating_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
    function selectLeadAttorney() {
        var selectdValue = $("#lead_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
    
    function goBack(){
        $('#showError4').html('');
        $("#case_id").val( localStorage.getItem("case_id"));
        $('#smartwizard').smartWizard("prev");
        return false;
    }
    $("#case_name").focus();

    $("#case_id").val( localStorage.getItem("case_id"));
</script>
