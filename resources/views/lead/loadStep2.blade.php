
<form class="createStep2" id="createStep2" name="createStep2" method="POST">
    <div id="showError2" class="showError" style="display:none"></div>
    @csrf    
    <input class="form-control" value="{{($UserMaster->id)??''}}" id="id" maxlength="250" name="id" type="hidden">
    <input class="form-control" value="{{($LeadAdditionalInfo->id)??''}}" id="id" maxlength="250" name="user_id" type="hidden">
    {{-- <input type="text" name="case_id"  id="case_id" value="{{@$case_id}}"> --}}

    <div class=" col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case name</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="case_name" value="Potential Case: {{$UserMaster->first_name}} {{$UserMaster->last_name}}" name="case_name" type="text"
                    placeholder="E.g. John Smith - Divorce">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case number</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="case_number" value="" name="case_number" type="text"
                    placeholder="Enter case number">
                <small>A unique identifier for this case.</small>
            </div>
        </div>
        <div class="form-group row" id="area_dropdown">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
            <div class="col-md-6 form-group mb-3">
                <select id="practice_area" name="practice_area" class="form-control custom-select col">
                    <option value="-1"></option>
                    <?php 
                        foreach($practiceAreaList as $k=>$v){?>
                    <option <?php if($LeadAdditionalInfo->practice_area==$v->id){ echo "selected=selected"; }?> value="{{$v->id}}">{{$v->title}}</option>
                    <?php } ?>

                </select>
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();" href="javascript:;">Add
                    new practice area</a></label>
        </div>
        <div class="form-group row" id="area_text">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
            <div class="col-md-6 form-group mb-3">
                <input class="form-control" id="practice_area_text" value="" name="practice_area_text" type="text"
                    placeholder="Enter new practice area">
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showDropdown();"
                    href="javascript:;">Cancel</a></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case stage
            </label>
            <div class="col-md-10 form-group mb-3">
                <select id="case_status" name="case_status" class="form-control custom-select col">
                    <option value="0"></option>
                    <?php 
                    foreach($caseStageList as $kcs=>$vcs){?>
                    <option value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Date opened</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control datepicker" id="case_open_date" value="{{date('m/d/Y')}}" name="case_open_date" type="text"
                placeholder="mm/dd/yyyy">

            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Office
            </label>
            <div class="col-md-10 form-group mb-3">
                <select id="case_office" name="case_office" class="form-control custom-select col">
                    <option value="1">Primary</option>
                    
                </select>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="case_description" class="form-control" rows="5"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Statute of Limitations</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control datepicker" id="case_statute" value="" name="case_statute" type="text"
                placeholder="mm/dd/yyyy">

            </div>
        </div>
        <div class="form-group row" id="addMoreReminder">
            <label for="sol_reminders" class="col-sm-2 col-form-label">SOL Reminders</label>
            <div class="col">
                <div class="test-sol-reminders fieldGroup">
                    
                    <div>
                        <button type="button" class="btn btn-link pl-0 add-more">Add a reminder</button>
                    </div>
                </div>
            </div>
        </div>
       <div class="fieldGroupCopy copy hide" style="display: none;">
            <div class="col-md-2 form-group mb-3">
                <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  "><option value="email">email</option><option value="popup">popup</option></select>

            </div>
            <div class="col-md-2 form-group mb-3">
                <input class="form-control" id="reminder_days" value="1" name="reminder_days[]" type="number" > 
            </div> <span class="pt-2">Days</span>
            <div class="col-md-2 form-group mb-3">   
                <button class="btn remove" type="button"><i class="fa fa-trash"
                aria-hidden="true"></i>
                </button>
            </div>
        </div>
      
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check</label>
            <div class="col-md-10 form-group mb-3">
                <label class="switch pr-5 switch-success mr-3"><span>Completed</span>
                    <input type="checkbox" name="conflict_check" id="conflict_check"><span class="slider"></span>
                </label>

            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check Notes</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="conflict_check_description" class="form-control" rows="5"></textarea>
            </div>
        </div>
        <a class="ml-2" href="javascript:void(0);"  onclick="goBack()" rel="noopener noreferrer">Go back</a>
        <div class="form-group row float-right">
            <button class="btn btn-primary ladda-button example-button m-1">
                <span class="ladda-label">Continue to Billing</span>
            </button>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader1" style="display: none;"></div>
            </div>
        </div>
     
    </div>
</form>

<script type="text/javascript">
    
    $(document).ready(function () {
        
        //$(".datepicker" ).datepicker();
       
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            "orientation": "bottom",
            'todayHighlight': true
        });

        //  $('.datepicker').datepicker({
            
        //     onSelect: function(dateText, inst) { 
        //        $("#addMoreReminder").show();
        //     },  
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function () {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         // If "Clear" gets clicked, then really clear it
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //             $("#addMoreReminder").hide();
        //         }
        //     }
        // });
        $("#addMoreReminder").hide();
        $("#innerLoader1").css('display', 'none');
        $("#area_text").css('display', 'none');

        $("#createStep2").validate({
            rules: {
                case_name:{
                    required:true
                }
            },
            messages: {
                
                case_name: {
                    required: "Case name is a required field"
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
        $(".add-more").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
        });
        $('#createStep2').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });

        $('#createStep2').submit(function (e) {
            e.preventDefault();
            $("#innerLoader1").css('display', 'block');
            if (!$('#createStep2').valid()) {
                $("#innerLoader1").css('display', 'none');
                return false;
            }

            var dataString = $("#createStep2").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/saveStep2", // json datasource
                data: dataString,
                success: function (res) {
                    $("#innerLoader1").css('display', 'block');
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
                        $("#innerLoader1").css('display', 'none');
                        $("#AddCaseModel").scrollTop(0);
                        return false;
                    } else {
                        $('.showError').html('');
                        localStorage.setItem("case_id",res.case_id);
                        loadStep3(res);
                    }
                }
            });
        });
    });

    function loadStep3(res) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadStep3", // json datasource
            data: {
                "case_id": res.case_id,
                "id": res.id,
                "user_id": res.user_id
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

    function showText() {

        $("#area_text").show();
        $("#area_dropdown").hide();
        return flase;
    }

    function showDropdown() {

        $("#area_text").hide();
        $("#area_dropdown").show()
        return flase;
    }
    function goBack(){
        $('.showError').html('');
        $("#case_id").val( localStorage.getItem("case_id"));
        $('#smartwizard').smartWizard("prev");
        return false;
    }
    $("#case_name").focus();

    $("#case_id").val( localStorage.getItem("case_id"));
</script>

