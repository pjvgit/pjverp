
<form class="AddCaseIntakeForm" id="AddCaseIntakeForm" name="AddCaseIntakeForm" method="POST">
    <span id="response"></span>
    @csrf
    <input type="hidden" name="case_id" value="{{$case_id}}">
    <div class="showError" style="display:none"></div>
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Intake Form:</label>
            <div class="col-sm-9">
                <select class="form-control intake_form select2" id="intake_form" name="intake_form"
                data-placeholder="Search form">
                <option></option>
                    <?php foreach($IntakeForm as $IntakeFormKey=>$IntakeFormVal){ ?>
                    <option value="{{$IntakeFormVal->id}}">{{substr($IntakeFormVal->form_name,0,40)}}</option>
                    <?php } ?>
            </select>
            <span id="afterShowError"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Contact:</label>
            <div class="col-sm-9">
                <select class="form-control intake_form select2" id="contact" name="contact"
                data-placeholder="Select contact">
                    <option>Select contact</option>
                    <?php foreach($clientList as $clientListKey=>$clientListVal){ ?>
                    <option value="{{$clientListVal->id}}" cmail="{{$clientListVal->email}}">{{substr($clientListVal->first_name,0,40)}}</option>
                    <?php } ?>
            </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Send To:</label>
            <div class="col-sm-9">
                <input class="form-control" value="" id="email_address" maxlength="250" name="email_address" type="text"
                    placeholder="example@email.com">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject:</label>
            <div class="col-sm-9">
                <input class="form-control" value="Intake Form from {{$firmData->firm_name}}" id="email_subject"
                    maxlength="250" name="email_subject" type="text" placeholder="">
                <p class="form-text text-muted mb-1">This is the subject of the email that will be sent to the client
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Message:</label>
            <div class="col-sm-9">
                <textarea class="form-control" id="email_message" maxlength="250"
                    name="email_message">Please click the button below and fill out this intake form at your earliest convenience.</textarea>
                <p class="form-text text-muted mb-1">Your email will include a button to access the Intake Form.</p>
            </div>
        </div>

        <div class="justify-content-between modal-footer">
            <div><a href="#" target="_blank">What will my client see?</a></div>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
            <div>
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                </a>
                <div role="group" class="btn-group">
                    <button type="submit" name="savenow" id="submit" value="savenow" class="btn btn-primary ladda-button example-button submit">
                        Save & Send
                    </button>
                    <div class="btn-group">
                        <button type="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-primary" data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right ">
                            <button type="submit" id="save-and-close-button" tabindex="0" name="savelater" value="savelater" role="menuitem" class="dropdown-item cursor-pointer">Save &amp; Send Later</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden"
    placeholder="example@email.com">
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown(); 

        $("#intake_form").select2({
            allowClear:true,
            theme: "classic",
            dropdownParent: $("#addIntakeFormFromCase"),
        });
        $(".innerLoader").css('display', 'none');
        $(".innerLoader").hide();
        $("#AddCaseIntakeForm").validate({
            rules: {
                intake_form:{
                    required: true,
                }, 
                email_address: {
                    required: true,
                    email: true
                }
            },
            messages: {
                intake_form:{
                    required: "You must select an intake form.",
                }, 
                email_address: {
                    required: "You must provide a valid email address.",
                    email: "A valid email address is required."
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#intake_form')) {
                    error.appendTo('#afterShowError');
                }else {
                    element.after(error);
                }
            }
        });
        $(document).on("click", ":submit", function(e){
            $("#current_submit").val($(this).val());
        });
        $('#AddCaseIntakeForm').submit(function (e) {
            // $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#AddCaseIntakeForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#AddCaseIntakeForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveIntakeForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
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
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });
        });

        $("select#contact").change(function() {
           $("#email_address").val($(this).find('option:selected').attr('cmail'))
        })
    });

    

</script>
