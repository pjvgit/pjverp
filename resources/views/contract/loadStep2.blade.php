<div id="showError" style="display:none"></div>
<h4 class="border-bottom border-gray pb-2">Link to Cases</h4>

<div class="alert alert-success"><b>You've added {{$user->first_name}} to your firm.</b> We have sent a welcome email
    to
    {{$user->email}} .</div>
<form class="createStep2" id="createStep2" name="createStep2" method="POST">
    @csrf
    <input type="hidden" name="user_id" value="{{$user->id}}">
    <input type="hidden" name="case_id" value="{{$case_id}}">
             
    <div class=" col-md-12">
    <h6><b>Now, grant access to the cases you want this user to have access to.</b></h6>

    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label">Link user to</label>
        <div class="col-sm-10">
            <label class="radio radio-outline-success">
                <input type="radio" name="link_to" <?php echo ($case_id == 0 || $case_id == NULL) ? 'checked' : ''; ?> value="1"><span>No cases</span><span
                    class="checkmark caseMark"></span>
            </label>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-10">

            <label class="radio radio-outline-success">
                <input type="radio" name="link_to" value="2"><span>All active cases</span><span
                    class="checkmark caseMark"></span>
            </label>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-10">
            <label class="radio radio-outline-success">
                <input type="radio" name="link_to" id="specificcase" value="3" <?php echo ($case_id > 0) ? 'checked' : ''; ?>><span>A specific case</span><span
                    class="checkmark caseMark"></span>
            </label>
        </div>

    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-10">
            <select class="form-control case_list" id="case_list" name="case_list" data-placeholder="Select Case">
                <option value="">Select Case</option>                
                <?php foreach($CaseMaster as $k=>$v){?>
                    <option value="{{$v->id}}" <?php echo ($case_id == $v->id) ? ' selected' : ''; ?> >{{$v->case_title}}</option>
                <?php } ?>
            </select>
            <small>You can link this user to additional cases later by clicking on "Firm Users" from the Settings
                section of {{config('app.name')}}. Then, click the user's name from the list and open the "Case Link"
                tab.</small>
            <br>
            <span id="CaseListError"></span>
        </div>
    </div>
    <hr>

    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label">Sharing Settings
        </label>
        <div class="col-md-10 form-group mb-3">
            <label class="switch pr-5 switch-success mr-3"><span>Add all case events to user's calendar</span>
                <input type="checkbox" name="sharing_setting_1" <?php echo ($case_id == 0 || $case_id == NULL) ? 'disabled' : ''; ?>><span class="slider"></span>
            </label>

        </div>

    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
        <div class="col-md-10 form-group mb-3">
            <label class="switch pr-5 switch-success mr-3"><span> Share all open and completed case tasks with this
                    user</span>
                <input type="checkbox" name="sharing_setting_2" <?php echo ($case_id == 0 || $case_id == NULL) ? 'disabled' : ''; ?> ><span class="slider"></span>
            </label>

        </div>

    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label">
        </label>
        <div class="col-md-10 form-group mb-3">
            <label class="switch pr-5 switch-success mr-3" ><span> Mark all items as read (only available when a
                    specific case is selected)</span>
                <input type="checkbox" name="sharing_setting_3" id="sharing_setting_3" <?php echo ($case_id == 0 || $case_id == NULL) ? 'disabled' : ''; ?>><span class="slider"></span>
            </label>

        </div>

    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label">Case Rate</label>
        <div class="col-sm-10">
            <label class="radio radio-outline-success">
                <input type="radio" name="case_rate" class="case_rate" checked="checked" value="0" <?php echo ($case_id == 0 || $case_id == NULL) ? 'disabled' : ''; ?>><span>Use lawyer
                    default rate</span><span class="checkmark"></span>
            </label>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-4">
            <label class="radio radio-outline-success">
                <input type="radio" name="case_rate" class="case_rate" value="1" <?php echo ($case_id == 0 || $case_id == NULL) ? 'disabled' : ''; ?> ><span>Specify a default rate for this
                    case</span><span class="checkmark"></span>
            </label>
        </div>
        <div class="input-group mb-3 col-sm-5">
            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
            <input class="form-control case_rate" name="default_rate" type="text" aria-label="Amount (to the nearest dollar)" <?php echo ($case_id == 0 || $case_id == NULL) ? 'disabled' : ''; ?>>
            <div class="input-group-append"><span class="input-group-text">/hr</span></div>
        </div>
    </div>
    <div class="form-group row float-right">
        <button class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
            <span class="ladda-label">Next</span>
        </button>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary" id="innerLoader1" style="display: none;"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
    </div>
    </div>
</form>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function () {
        $("#innerLoader1").css('display', 'none');
        $("#createStep2").validate({

            rules: {
                case_list: {
                    required: {
                        depends: function (element) {
                            var status = false;
                            if ($("#specificcase:checked").val() !== undefined) {
                                var status = true;
                            }
                            return status;
                        }
                    }
                }
            },
            messages: {
                case_list: {
                  
                    required: "Please select case from the list."
                
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_list')) {
                    error.appendTo('#CaseListError');
                } else {
                    element.after(error);
                }
            }
        });
        <?php if($case_id == 0 || $case_id == NULL) { ?>
        $(".case_list").attr('disabled', true);
        $(".case_rate").attr('disabled', true);
        $("#sharing_setting_3").attr('disabled', true);
        <?php } ?>
        $("input[name='link_to']").on("change", function () {
            var radioValue = $("input[name='link_to']:checked").val();
            if (radioValue == "3") {
                $("input[name='sharing_setting_1']").attr('disabled', false);
                $("input[name='sharing_setting_2']").attr('disabled', false);
                $("input[name='sharing_setting_3']").attr('disabled', false);
                $('.case_rate').attr('disabled', false);
                $(".case_list").attr('disabled', false);
            } else if (radioValue == "2") {
                $("input[name='sharing_setting_3']").prop('checked', false);
                $("input[name='sharing_setting_1']").attr('disabled', false);
                $("input[name='sharing_setting_2']").attr('disabled', false);
                $("input[name='sharing_setting_3']").attr('disabled', true);
                $(".case_rate").attr('disabled', true);
                $(".case_list").attr('disabled', true);
            } else {
                $("input[name='sharing_setting_1']").prop('checked', false);
                $("input[name='sharing_setting_2']").prop('checked', false);
                $("input[name='sharing_setting_3']").prop('checked', false);
                $("input[name='sharing_setting_1']").attr('disabled', true);
                $("input[name='sharing_setting_2']").attr('disabled', true);
                $("input[name='sharing_setting_3']").attr('disabled', true);
                $(".case_rate").attr('disabled', true);
                $(".case_list").attr('disabled', true);
            }
        });
    });

    $('#createStep2').submit(function (e) {
        $("#innerLoader1").css('display', 'block');
        e.preventDefault();

        if (!$('#createStep2').valid()) {
            $("#innerLoader1").css('display', 'none');
            return false;
        }

        var dataString = $("#createStep2").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveStep2", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader1").css('display', 'block');
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
                    $("#innerLoader1").css('display', 'none');
                    $('#DeleteModal').animate({ scrollTop: 0 }, 'slow');

                    return false;
                } else {
                    loadStep3(res);
                }
            }
        });
    });

    function loadStep3(res) {

        console.log(res);
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/loadStep3", // json datasource
            data: {
                "user_id": res.user_id,
                "case_id" : {{$case_id ?? 0}}
            },
            success: function (res) {
                $('#smartwizard').smartWizard("next");
                $("#innerLoader1").css('display', 'none');
                $("#step-3").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    }

</script>
