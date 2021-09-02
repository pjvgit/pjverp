
<form class="CreateTask" id="CreateTask" name="CreateTask" method="POST">
    @csrf
    <input class="form-control" id="id" value="{{ $Task->id}}" name="task_id" type="hidden">

    <input class="form-control" id="id" value="{{$from_view}}" name="from_view" type="hidden">
    <div class="row">
        <div class="col-8">
            <div id="showError" style="display:none"></div>
           <div class="form-group row">
                <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
            </div>

            <input type="hidden" id="text_case_id" value="" name="text_case_id">
            <input type="hidden" id="text_lead_id" value="" name="text_lead_id">
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Case or Lead</label>
                <div class="col-8 form-group mb-3">
                    <select onChange="changeCaseUser()" class="form-control case_or_lead" id="case_or_lead" name="case_or_lead"
                        data-placeholder="Search for an existing contact or company">
                        <option value="">Search for an existing Case or Lead</option>
                        <optgroup label="Court Cases">
                            <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                            <option uType="case"
                                value="{{$Caseval->id}}">{{$Caseval->case_title}} <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?></option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Leads">
                            {{-- <?php foreach($caseLeadList as $caseLeadListKey=>$caseLeadListVal){ ?>
                            <option uType="lead" <?php if($Task->lead_id==$caseLeadListVal->id) { echo "selected=selected"; } ?> value="{{$caseLeadListVal->id}}">{{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                            <?php } ?> --}}
                            @forelse ($caseLeadList as $key => $item)
                                <option uType="lead" value="{{ $key }}" {{ ($key == $Task->lead_id) ? "selected" : "" }}>{{ $item }}</option>
                            @empty
                            @endforelse
                        </optgroup>
                    </select>

                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-8 form-group mb-3">
                    <label class="form-check-label">
                        <input class="mr-2 no_case_link" type="checkbox" <?php if($Task->no_case_link=="no"){ echo "checked=checked"; } ?> id="no_case_link" name="no_case_link">
                        <span>This task is not linked to a case or lead</span>
                    </label>
                    <span id="CaseListError"></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Task Name</label>
                <div class="col-md-8 form-group mb-3">
                    <input class="form-control" id="task_name" value="{{$Task->task_title}}" name="task_name" type="text" maxlength="512">
                </div>
                
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Due</label>
                <div class="col-md-3 form-group mb-3">
                    <?php 
                    $dueDate='';
                    if($Task->task_due_on!=NULL && $Task->task_due_on!='9999-12-30'){
                        $dueDate=date('m/d/Y',strtotime($Task->task_due_on));
                    }
                    ?>
                    <input class="form-control datepicker" autocomplete="off" id="due_date" value="{{$dueDate}}" name="due_date" type="text"
                        placeholder="mm/dd/yyyy">
                </div>
            </div>

            <div class="form-group row">
                <label for="checklist" class="col-sm-2 col-form-label">Checklist</label>
                <div class="col" >
                    <div class="checklist-field form-group col-sm-10" id="sortable">
                        <?php
                        foreach($TaskChecklist as $keyTaskChecklist=>$valTaskChecklist){
                        ?><div class="row form-group fieldGroupChecklist">
                          
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="fas fa-bars fa-lg input-group-text"></span></div>
                                <textarea type="text" name="checklist-item-name[{{$valTaskChecklist->id}}]" class="checklist-item-name form-control" rows="1" style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 33.6px;">{{$valTaskChecklist->title}}</textarea>
                                <div class="input-group-append">
                                    <button class="btn removeChecklist" type="button">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                      
                        </div>  <?php } ?>
                        <div class="fieldGroupChecklist"></div>
                        <button type="button" class="btn btn-link p-0 test-add-new-checklist add-more-checklist">Add a checklist item</button>
                    </div>
                </div>
            </div>
            <div class="fieldGroupCopyChecklist copyChecklist hide" style="display: none;">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="fas fa-bars fa-lg input-group-text"></span></div>
                    <textarea type="text" name="checklist-item-name[]" class="checklist-item-name form-control"
                        rows="1"
                        style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 33.6px;"></textarea>
                    <div class="input-group-append">
                        <button class="btn removeChecklist" type="button">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

           
            <div class="form-group row" >
                <label for="inputEmail3" class="col-sm-2 col-form-label">Priority</label>
                <div class="col-md-3 form-group mb-3">
                    <select  id="event-frequency" name="event_frequency"
                        class="form-control custom-select  ">
                        <option value="" selected="selected">No Priority</option>
                        <option <?php if($Task->task_priority=="1"){ echo "selected=selected"; } ?> value="1" >Low</option>
                        <option <?php if($Task->task_priority=="2"){ echo "selected=selected"; } ?> value="2">Medium</option>
                        <option <?php if($Task->task_priority=="3"){ echo "selected=selected"; } ?> value="3">High</option>
                    </select>
                </div>
                
            </div>
            
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                <div class="col-md-8 form-group mb-3">
                    <textarea id="description" name="description" class="form-control " placeholder="" rows="5"
                        style="max-height: 600px; overflow: hidden; overflow-wrap: break-word; resize: none; height: 111.6px;"
                        spellcheck="false" data-gramm="false">{{$Task->description}}</textarea>
                    <p class="form-text text-muted mb-1">This description will be viewable by anyone this task is shared with.
                    </p>
                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-3 col-form-label">Enter Time Estimate</label>
                <div class="col-md-9 form-group mb-3">
                    <label class="switch pr-5 switch-success mr-3 mt-2"><span></span>
                        <input type="checkbox" name="time_tracking_enabled"   id="time_tracking_enabled"><span class="slider"></span>
                    </label>
    
                </div>
    
            </div>
            <div id="dynamicUSerTimes"></div>
           
            <div class="form-group row">
                <label for="reminders" class="col-sm-2 col-form-label">Reminders</label>
                <div class="col">
                    <div>
                        <?php
                            foreach($taskReminderData as $rkey=>$rval){
                            ?>
                            <div class="row form-group fieldGroup">
                                <div class="">
                                    <div class="d-flex col-10 pl-0 align-items-center">
                                        <div class="pl-0 col-2">
                                            <div>
                                                <div class="">
                                                    <select id="reminder_user_type" name="reminder_user_type[]" class="form-control custom-select  ">
                                                        <option <?php if($rval->reminder_user_type=="me"){ echo "selected=selected"; } ?>  value="me">Me</option>
                                                        <option <?php if($rval->reminder_user_type=="attorney"){ echo "selected=selected"; } ?> value="attorney">Attorneys</option>
                                                        <option <?php if($rval->reminder_user_type=="paralegal"){ echo "selected=selected"; } ?>  value="paralegal">Paralegals</option>
                                                        <option <?php if($rval->reminder_user_type=="staff"){ echo "selected=selected"; } ?>  value="staff">Staff</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pl-0 col-2">
                                            <div>
                                                <div class="">
                                                    <select id="reminder_type" name="reminder_type[]" class="form-control custom-select valid" aria-invalid="false">
                                                        <option <?php if($rval->reminder_type=="popup"){ echo "selected=selected"; } ?> value="popup">popup</option>
                                                        <option <?php if($rval->reminder_type=="email"){ echo "selected=selected"; } ?> value="email">email</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div><input name="reminder_number[]" class="form-control col-2 reminder-number" value="{{$rval->reminer_number}}">
                                        <div class="col-3">
                                            <div>
                                                <div class="">
                                                    <select id="reminder_time_unit" name="reminder_time_unit[]" class="form-control custom-select  ">
                                                         <option <?php if($rval->reminder_frequncy=="day"){ echo "selected=selected"; } ?> value="day">days</option>
                                                        <option <?php if($rval->reminder_frequncy=="week"){ echo "selected=selected"; } ?> value="week">weeks</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>  before task &nbsp;
                                        <button class="btn remove" type="button">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        <div class="fieldGroup">
                        </div>
                        <div><button type="button" class="btn btn-link p-0 test-add-new-reminder add-more">Add a reminder</button></div>
                    </div>
                </div>
            </div>  
        
            
            <div class="fieldGroupCopy copy hide" style="display: none;">
                <div class="">
                    <div class="d-flex col-12 pl-0 align-items-center">
                        <div class="pl-0 col-2">
                            <div>
                                <div class="">
                                    <select id="reminder_user_type" name="reminder_user_type[]"
                                        class="form-control custom-select  ">
                                        <option value="me">Me</option>
                                        <option value="attorney">Attorneys</option>
                                        <option value="paralegal">Paralegals</option>
                                        <option value="staff">Staff</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="pl-0 col-2">
                            <div>
                                <div class="">
                                    <select id="reminder_type" name="reminder_type[]"
                                        class="form-control custom-select  ">
                                        <option value="popup">popup</option>
                                        <option value="email">email</option>
                                    </select>
                                </div>
                            </div>
                        </div><input name="reminder_number[]" type="number" min="0" class="form-control col-2 reminder-number" value="1">
                        <div class="col-3">
                            <div>
                                <div class="">
                                    <select id="reminder_time_unit" name="reminder_time_unit[]"
                                        class="form-control custom-select  ">
                                        {{-- <option value="minute">minutes</option>
                                        <option value="hour">hours</option> --}}
                                        <option value="day">days</option>
                                        <option value="week">weeks</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        before task &nbsp;
                        <button class="btn remove" type="button">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="loadUserAjax"></div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
            </div>
        </div>
        <div class="col-4">
            <?php 
            if(Auth::User()->add_task_guide=="0"){?>
                <div class="client-task-tip" id="guiderArea">
                    <div class="alert alert-info">
                        <a class="close closeGuider">×</a>
                        <div class="tooltip-message"><b>Note :</b> Assign a task to your client and they will receive a link
                            to view and complete it via the client portal. <a href="#" rel="noopener noreferrer"
                                target="_blank"><u>What will my client see?</u></a>
                        </div>
                        <div></div>
                    </div>
                </div>
            <?php } ?>
            <section class="sharing-list" id="loadTaskSection">

            </section>
        </div>
    </div>
  
    <div class="justify-content-between modal-footer">
        <div></div>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display:none;"></div>
        <div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>
            <button class="btn btn-primary example-button m-1" id="submit"  type="submit"
            data-style="expand-left">Save Task </button>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $(".case_or_lead").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#editTask"),
        });
        
        $( "#sortable" ).sortable();;
       //$(".datepicker" ).datepicker();
    //    $('.datepicker').datepicker({
    //         onSelect: function(dateText, inst) { 
    //            $("#addMoreReminder").show();
    //         },  
    //         showOn: 'focus',
    //         showButtonPanel: true,
    //         closeText: 'Clear', // Text to show for "close" button
    //         onClose: function () {
    //             var event = arguments.callee.caller.caller.arguments[0];
    //             // If "Clear" gets clicked, then really clear it
    //             if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
    //                 $(this).val('');
    //                 $("#addMoreReminder").hide();
    //             }
    //         }
    //     });
    $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
        });

        $("#HideShowNonlink").on('click', function () {
            $(".staff-table-nonlinked").toggle();
        });
       
        $("#innerLoader").css('display', 'none');
        $(".innerLoader").css('display', 'none');
     
        // $(".hide").hide();
        $(".add-more").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
        });
        $('#CreateTask').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });


        // $(".hide").hide();
        $(".add-more-checklist").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroupChecklist">' + $(".fieldGroupCopyChecklist").html() +
                '</div>';
            $('body').find('.fieldGroupChecklist:last').before(fieldHTML);
            $(".checklist-item-name").focus();
        });
        $('#CreateTask').on('click', '.removeChecklist', function () {
            var $row = $(this).parents('.fieldGroupChecklist').remove();
        });
        $("#CreateTask").validate({
            rules: {
                case_or_lead: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#no_case_link:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    }
                },
                end_on: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#no_end_date_checkbox:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    }
                },
                task_name:{
                    required:true
                }
            },
            messages: {
                case_or_lead: {
                    required: "Select a court case or check not linked to a case"

                }, 
                end_on: {
                    required: "Please provide an end date or select no end date."

                },
                task_name: {
                    required: "Task Name can't be blank."

                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_or_lead')) {
                    $($('.select2-container--classic .select2-selection--single')[2]).addClass("input-border-error");
                    error.appendTo('#CaseListError');
                }else if (element.is('#end_on')) {
                    error.appendTo('#EndOnListError');
                } else {
                    element.after(error);
                }
            }
        });

        $('#case_or_lead').on('select2:select', function (e) { 
            $($('.select2-container--classic .select2-selection--single')[2]).removeClass("input-border-error");
            $('#CaseListError').text('');
        });

        $('#CreateTask').submit(function (e) {
            e.preventDefault();
         //   $(this).find(":submit").prop("disabled", true);
            $("#innerLoader").css('display', 'block');
            $(".innerLoader").css('display', 'block');
            if (!$('#CreateTask').valid()) {
                $("#innerLoader").css('display', 'none');
                $(".innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("#CreateTask").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/saveEditTaskPopup", // json datasource
                data: dataString,
                success: function (res) {
                    $(this).find(":submit").prop("disabled", true);
                    $("#innerLoader").css('display', 'block');
                    $(".innerLoader").css('display', 'block');
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
                        $("#innerLoader").css('display', 'none');
                        $(".innerLoader").css('display', 'none');

                        $('#submit').removeAttr("disabled");
                        $('#editTask').animate({ scrollTop: 0 }, 'slow');

                        return false;
                    } else {
                       window.location.reload();
                        $("#innerLoader").css('display', 'none');
                        $(".innerLoader").css('display', 'none');


                    }
                }
            });
        });
        $("input:checkbox.all_day").click(function () {
            if ($(this).is(":checked")) {
                $("#start_time").attr("readonly", true);
                $("#end_time").attr("readonly", true);

            } else {
                $('#start_time').removeAttr("readonly");
                $('#end_time').removeAttr("readonly");

            }
        });
        $("input:checkbox.no_case_link").click(function () {
            if ($(this).is(":checked")) {
                $("#case_or_lead").attr("disabled", true);
            } else {
                $('#case_or_lead').removeAttr("disabled");
                $("#dynamicUSerTimes").html('');
            }
        });

        $("input:checkbox#no_end_date_checkbox").click(function () {
            if ($(this).is(":checked")) {
                $("#end_on").attr("disabled", true);
            } else {
                $('#end_on').removeAttr("disabled");
            }
        });

        $("input:checkbox.recuring_event").click(function () {
            if ($(this).is(":checked")) {
                $("#repeat_dropdown").show();
                $("#endondiv").show();

            } else {
                $("#endondiv").hide();

                $('#repeat_dropdown').hide();
            }
        });


    });
    function loadRightSection(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadRightSection",
            data: {"case_id": case_id},
            success: function (res) {
                $("#loadTaskSection").html(res);
            }
        })
    }

    function firmStaff() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadAllStaffMember",
            data: {"edit":"edit","task_id":{{$Task->id}}},
            success: function (res) {
                $("#loadTaskSection").html(res);
              
            }
        })
    }
    function changeCaseUser1() {
        var selectdValue = $("#case_or_lead option:selected").val() // or
     
        if(selectdValue!=''){
            $("#HideShowNonlink").show();
            loadCaseClient(selectdValue);
            loadCaseLinkedStaff(selectdValue);
            loadCaseNoneLinkedStaff(selectdValue);
            <?php  if($Task->time_tracking_enabled=="yes"){  ?>
                $('input:checkbox#time_tracking_enabled').trigger('click');
           <?php } ?>
            
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersLinkedStaffList1();
            }
         
        }else{
            $("#CaseNoneLinkedStaffSection").html('');
            $("#CaseLinkedStaffSection").html('');
            $("#CaseClientSection").html('');
            $("#HideShowNonlink").hide();
            
        }
    }

    function changeCaseUser() {
        $("#dynamicUSerTimes").html('');

        $("#text_lead_id").val('');
        $("#text_case_id").val('');
        var uType=$("#case_or_lead option:selected").attr('uType');
        var selectdValue = $("#case_or_lead option:selected").val() 
       
        if(selectdValue!=''){
            $("#time_tracking_enabled").prop('checked',false);
            if(uType=="case"){
                $("#text_case_id").val(selectdValue);
                $("#text_lead_id").val('');

                $("#HideShowNonlink").show();
                loadRightSection(selectdValue);
            }else{
                $("#text_lead_id").val(selectdValue);
                $("#text_case_id").val('');

                firmStaff();
            }
            <?php  if($Task->time_tracking_enabled=="yes"){  ?>
                $('input:checkbox#time_tracking_enabled').trigger('click');
           <?php } ?>
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersLinkedStaffList1();
            }
        }else{
            $("#loadTaskSection").html('');
            $("#HideShowNonlink").hide();
            loadDefaultContent();
        }
    }
    function loadDefaultContent(){
        $("#CaseClientSection").html('<br><label for="inputEmail3" class="col-sm-12 col-form-label">Select a case or check “This task is not linked to a case” to assign this task.</label>');
        
    }

    $("input:checkbox#no_case_link").click(function () {
        if ($(this).is(":checked")) {
            $("#time_tracking_enabled").prop('checked',false)

            $('#case_or_lead').prop('selectedIndex',0);
            $("#HideShowNonlink").hide();
            firmStaff();
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersListMain();
            }
            $("#loadTaskSection").html('');
        } else {
            $("#CaseLinkedStaffSection").html('');
            loadDefaultContent();
        }
    });
    $(".closeGuider").click(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/hideTaskGuide",
            data: "",
            success: function (res) {
                $("#guiderArea").html('');
            }
        })
    });
    $("input:checkbox#time_tracking_enabled").click(function () {
        var uType=$("#case_or_lead option:selected").attr('uType');
        if ($(this).is(":checked")) {
            if($("input:checkbox#no_case_link").is(":checked")){
                loadTimeEstimationUsersListMain();
            }else{
                if(uType=="case"){
                    loadTimeEstimationUsersLinkedStaffList1();
                }else{
                    loadTimeEstimationUsersListMain();

                }
            }
        } else {
            $("#dynamicUSerTimes").html('');
        }
    });
    function loadTimeEstimationUsersListMain() {
        $("#dynamicUSerTimes").html('');
        var array = [];
        $('input[name="linked_staff_checked_attend[]"]:checked').each(function (i) {
            array.push($(this).val());
        });
        SU=array;
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTimeEstimationUsersList",
            data: {
                "userList": JSON.stringify(SU)
            },
            success: function (res) {
                $("#dynamicUSerTimes").html(res);
            }
        })
    }
    function loadTimeEstimationUsersLinkedStaffList1() {
        $("#preloader").show();
        setTimeout(function () {
            var selectdValue = $("#case_or_lead option:selected").val() // or
            var SU=getCheckedUser();
        
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/loadTimeEstimationCaseWiseUsersList",
                // data: {"case_id":selectdValue,"edit":"edit"},  //"userList" : JSON.stringify(SU),
                data: {"userList" : JSON.stringify(SU),"case_id":selectdValue,"edit":"edit","task_id":{{$Task->id}}},
                success: function (res) {
                    $("#dynamicUSerTimes").html(res);
                    $("#preloader").hide();

                }
            })  
        },1000);           
    }
    function getCheckedUser(){
        var array = [];
        $('input[name="linked_staff_checked_share[]"]:checked').each(function(i){
            array.push($(this).val());
        });
        $('input[name="share_checkbox_nonlinked[]"]:checked').each(function(i){
            array.push($(this).val());
        });
        return array;
    }

    $(".onlyNumber").keypress(function (e) {
        if (String.fromCharCode(e.keyCode).match(/[^0-9]/g)) return false;
    });

    <?php  if($Task->no_case_link=="no"){  ?>
            firmStaff();
    <?php }  ?>
  
    changeCaseUser();
</script>
