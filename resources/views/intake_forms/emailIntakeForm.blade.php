<form class="EmailForm" id="EmailForm" name="EmailForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="showError" style="display:none"></div>

    <input type="hidden" id="text_contact_id" value="" name="text_contact_id">
    <input type="hidden" id="text_lead_id" value="" name="text_lead_id">
    <input type="hidden" id="form_id" value="{{$intakeForm['id']}}" name="form_id">
    <div class="col-md-12" bladefile="resources/views/intake_forms/emailIntakeForm.blade.php">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Intake Form:</label>
            <div class="col-sm-9">
                <label for="inputEmail3" class="col-sm-3 col-form-label">{{$intakeForm['form_name']}}</label>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Search for contact/lead:</label>
            <div class="col-sm-9">
                <select onChange="changeCaseUser()" class="form-control contact_or_lead" id="contact_or_lead" name="contact_or_lead"
                data-placeholder="Search for an existing contact or lead">
                <option value="">Select Contact/Lead..</option>
                <optgroup label="Contacts">
                    @forelse (userClientList() as $key => $item)
                    <option uType="contact" value="{{ $item->id }}" >{{ $item->name }}</option>
                    @empty
                    @endforelse
                </optgroup>
                <optgroup label="Leads">
                    @forelse (userLeadList() as $key => $item)
                    <option uType="lead" value="{{ $key }}" >{{ $item }}</option>
                    @empty
                    @endforelse
                </optgroup>
            </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Case:</label>
            <div class="col-sm-9">
                <select class="form-control case_id" id="case_id" disabled name="case_id"
                data-placeholder="Search for an existing case">
                <option value="">Select Case..</option>
            </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Email address:</label>
            <div class="col-sm-9">
                <input class="form-control" value="" id="email_address" maxlength="250" name="email_address" type="text" placeholder="">            
                
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject:</label>
            <div class="col-sm-9">
            <input class="form-control" value="Intake Form from {{$firmData->firm_name}}" id="email_suubject" maxlength="250" name="email_suubject" type="text" placeholder="">            
                <p class="form-text text-muted mb-1">This is the subject of the email that will be sent to the client</p>
            </div>

        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Message:</label>
            <div class="col-sm-9">
            <textarea class="form-control" id="email_message" maxlength="250" name="email_message">Please click the button below and fill out this intake form at your earliest convenience.</textarea>            
                <p class="form-text text-muted mb-1">Your email will include a button to access the Intake Form.</p>
            </div>

        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Send</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $(".innerLoader").css('display', 'none');
        $(".innerLoader").hide();
        $("#EmailForm").validate({
            rules: {
                contact_or_lead: {
                    required: true,
                },
                case_id: {
                    required: true,
                },
                email_address: {
                    required: true,
                    email:true
                }
            },
            messages: {
                contact_or_lead: {
                    required: "A recipient is required.",
                },
                case_id: {
                    required: "A case is required.",
                },
                email_address: {
                    required: "A valid email address is required.",
                    email:"A valid email address is required."
                },
            }
        });
        $('#EmailForm').submit(function (e) {
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#EmailForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#EmailForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/sentEmailIntakeForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
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
                        $(".innerLoader").css('display', 'none');
                        $('.submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (jqXHR, exception) {
                    $(".innerLoader").css('display', 'none');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });
        });

    });
    function changeCaseUser() {
        $("#text_lead_id").val('');
        $("#text_contact_id").val('');
        var uType=$("#contact_or_lead option:selected").attr('uType');
        var selectdValue = $("#contact_or_lead option:selected").val() 
       
        if(selectdValue!=''){
            
            $("#case_id").removeAttr("disabled");
            if(uType=="contact"){
                $("#text_contact_id").val(selectdValue);
                loadClientCase(selectdValue);
                loadClientLeadDetail(selectdValue, 'client');
            }else{
                $("#text_lead_id").val(selectdValue);
                loadLeadCase(selectdValue);
                loadClientLeadDetail(selectdValue, 'lead');
            }
        }else{
            $("#case_id").attr("disabled",true);
            
        }
    }

    function loadClientCase(id) {
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/intake_form/loadClientCase", // json datasource
                data: {'id':id},
                success: function (res) {
                $("#case_id").html(res);
                    $("#preloader").hide();
                }
            })
        }) 
    }
    function loadLeadCase(id) {
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/intake_form/loadLeadCase", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#case_id").html(res);
                     // $("#emailFormArea").html(res);
                    $("#preloader").hide();
                }
            })
        }) 
    }
    function loadClientLeadDetail(id, uType) {
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/intake_form/getClientLeadDetail", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#emailIntakeForm #email_address").val(res.email);
                    $("#preloader").hide();
                }
            })
        }) 
    }
</script>
