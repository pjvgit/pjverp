<?php
 $CommonController= new App\Http\Controllers\CommonController();
?> 
<div class="tab-content" id="myTabContent">
    <span id="showError" class="showError" style="display: none;"></span>
    <div class="tab-pane fade show active" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
        <form class="savenewTimeEntry" id="savenewTimeEntry" name="savenewTimeEntry" method="POST">
            @csrf
            <input type="hidden" name="entry_id" value="{{$TaskTimeEntry['id']}}">

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Case</label>
                <div class="col-6 form-group mb-3">
                    <select class="form-control case_or_lead dropdownSelect" id="case_or_lead" name="case_or_lead"
                        data-placeholder="Select case">
                        <option value="">Select case</option>
                        <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                        <option  <?php if($Caseval->id==$TaskTimeEntry['case_id']){ echo "selected=selected"; } ?> value="{{$Caseval->id}}">{{$Caseval->case_title}}
                            <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?></option>
                        <?php } ?>

                    </select>
                    <span id="cnl"></span>
                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">User</label>
                <div class="col-6 form-group mb-3">
                    <select class="form-control staff_user dropdownSelect" id="staff_user" name="staff_user">
                        <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                        <option <?php if($CasevloadFirmStaffvalal->id==$TaskTimeEntry['user_id']){ echo "selected=selected"; } ?> value="{{$CasevloadFirmStaffvalal->id}}">{{$CasevloadFirmStaffvalal->first_name}}
                            {{$CasevloadFirmStaffvalal->last_name}}  <?php if($CasevloadFirmStaffvalal->user_title){ echo "(".$CasevloadFirmStaffvalal->user_title.")"; } ?></option>
                        <?php } ?>
                    </select>

                </div>
            </div>

            <div class="form-group row" id="Edit_area_dropdown">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Activity</label>
                <div class="col-md-6 form-group mb-3">
                    <select id="Editactivity" name="activity" class="form-control custom-select col dropdown_activity">
                        <option value="">Search activity</option>
                        <?php foreach($TaskActivity as $k=>$v){ ?>
                        <option <?php if($v->id==$TaskTimeEntry['activity_id']){ echo "selected=selected"; } ?> value="{{$v->id}}">{{$v->title}}</option>
                        <?php } ?>
                    </select>
                    <span id="act"></span>

                </div>
                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();"
                        href="javascript:;">Add
                        new activity</a></label>
            </div>
            <div class="form-group row" id="Edit_area_text">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Activity</label>
                <div class="col-md-6 form-group mb-3">
                    <input class="form-control" id="activity_text" value="" maxlength="255" name="activity_text"
                        type="text" placeholder="Enter new activity">
                </div>
                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showDropdown();"
                        href="javascript:;">Cancel</a></label>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-10 form-group mb-3">
                    <label class="switch pr-5 switch-success mr-3 mt-2"><span></span>
                        <input type="checkbox" name="time_tracking_enabled" <?php if($TaskTimeEntry['time_entry_billable']=="yes"){ echo "checked=checked"; } ?>  
                            id="time_tracking_enabled"><span class="slider"></span>
                            This expense is billable.</label>
                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                <div class="col-md-10 form-group mb-3">
                    <textarea name="case_description" class="form-control"
                        rows="5">{{$TaskTimeEntry['description']}}</textarea>
                    <small>This description will appear on invoices.</small>
                </div>
            </div>
            <div class="">
                <a class="collapsed" id="collapsed" data-toggle="collapse" href="javascript:void(0);"
                    data-target="#accordion-item-icons-4" aria-expanded="false">
                    Custom Fields <i class="fas fa-sort-down align-text-top"></i>
                </a>
            </div>
            <div class="collapse" id="accordion-item-icons-4" style="">
                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-md-9 form-group mb-3">
                        <div>
                            Have more information you want to add? You can create custom fields for expense by going to "Settings" and clicking "Custom Fields".
                            <a class="ml-2" href="#" target="_blank" rel="noopener noreferrer">
                                Learn More
                            </a>
                        </div>
            
                    </div>
                </div>
            </div>
            <div class="my-2 px-3 py-4 date-rate-duration-block container">
                <div class="mb-2 row ">
                    <div class="col-4"><label class="mb-0" for="date-field-id">Date</label></div>
                    <div class="col-4"><label class="mb-0" for="rate-field-id">Cost</label></div>
                    <div class="pl-4 col-4"><label class="mb-0" for="duration-field-id">Quantity</label></div>
                </div>
                <div class="row ">
                    <div class="col-4">
                        <input class="form-control datepicker" id="datepicker" value="{{date('m/d/Y',strtotime($TaskTimeEntry['entry_date']))}}"
                            name="start_date" type="text" placeholder="mm/dd/yyyy">

                    </div>
                    <div class="col-4">
                        <div class="">
                            <div class="px-0 undefined">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div><input
                                        id="rate_field_id" name="rate_field_id" maxlength="15"
                                        class="form-control number" value="{{$TaskTimeEntry['cost']}}">
                                </div>
                                <span id="rfi"></span>
                            </div>
                        </div>
                    </div>
                   
                    <div class="pl-4 col-4">
                        <div class=""><input id="duration-field" maxlength="15" name="duration_field"
                                class="form-control text-right" value="{{$TaskTimeEntry['duration']}}"></div>
                    </div>
                </div>
            </div>


            <div class="justify-content-between modal-footer">
                <div>
                    <?php
                     $createdDate= $CommonController->convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($TaskTimeEntry['created_at'])),Auth::User()->user_timezone);

                     ?>
                    <div class="m-0 test-created-at"><strong>Originally Created:</strong> {{date('M d Y, h:i a',strtotime($createdDate))}}  by {{$createdBy['created_by_name']}}.
                    </div>
                    <?php 
                    if(!empty($updatedBy) && $updatedBy['updated_by_name']){
                        
                     $updatedDate= $CommonController->convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($TaskTimeEntry['updated_at'])),Auth::User()->user_timezone);

            ?>
                    <div class="m-0 test-last-modified-at"><strong>Last Modified:</strong> {{date('M d Y, h:i a',strtotime($updatedDate))}} by {{$updatedBy['updated_by_name']}}.</div>
                    <?php } ?>
                </div>
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
                <div>
                    <button type="button" class="mr-1 btn btn-outline-danger" onclick="deleteExpenseEntryCommon({{$TaskTimeEntry['id']}})">Delete Expense</button>
                    <button type="submit" class="btn btn-primary submit">Save</button></div>
            </div>

        </form>
    </div>
   
</div>
<style>
    .hide {
        display: none;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $('#collapsed').click(function () {
            $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top');
        });
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $(".case_or_lead").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadEditExpenseEntryPopup"),
        });
        $(".staff_user").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadEditExpenseEntryPopup"),
        });
        $("#Editactivity").select2({
            placeholder: "Select activity",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadEditExpenseEntryPopup"),
        });

        $("#savenewTimeEntry").validate({
            rules: {
                case_or_lead: {
                    required: true
                },
                staff_user: {
                    required: true
                },
                activity: {
                    required: true
                },
                rate_field_id: {
                    required: true,
                    number: true
                },
                duration_field: {
                    required: true,
                    number: true
                }
            },

            messages: {
                case_or_lead: {
                    required: "Case can't be blank"
                },
                staff_user: {
                    required: "User can't be blank"
                },
                activity: {
                    required: "Activity can't be blank"
                },
                rate_field_id: {
                    required: "Cost can't be blank", 
                    number: "Cost is invalid"

                },
                duration_field: {
                    required: "Quantity can't be blank",
                    number: "Quantity is invalid"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_or_lead')) {
                    error.appendTo('#cnl');
                } else if (element.is('#activity')) {
                    error.appendTo('#act');
                } else if (element.is('#staff_user')) {
                    error.appendTo('#usr');
                }else if (element.is('#rate_field_id')) {
                    error.appendTo('#rfi');
                } else {
                    element.after(error);
                }
            }

            
        });
        
        $('#savenewTimeEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#savenewTimeEntry').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#savenewTimeEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/updateEditExpenseEntry", // json datasource
                data: dataString,
                success: function (res) {
                    afterLoader();
                    $("#innerLoader").css('display', 'block');
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
                        afterLoader();
                        $('#loadEditExpenseEntryPopup').animate({ scrollTop: 0 }, 'slow');
                        return false;
                    } else {
                        toastr.success('Your expense entry has been updated', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        window.location.reload();

                    }
                }, error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#loadEditExpenseEntryPopup').animate({ scrollTop: 0 }, 'slow');
                    afterLoader();
                }
            });
        });

        $(document).on('change', ".case_or_lead", function () {
            var f = $(this).attr("dvid");
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/getAndCheckDefaultCaseRate",
                data: {
                    'case_id': $(this).attr('value')
                },
                success: function (res) {
                    console.log(f);
                    $("#replaceAmt" + f).text("Billable - Rate :" + res.data);
                    console.log("#replaceAmt" + f);
                }
            })
        });

    });
    showDropdown();

    function showText() {
        $("#Edit_area_text").show();
        $("#Edit_area_dropdown").hide();
        return false;
    }

    function showDropdown() {
        $("#Edit_area_text").hide();
        $("#Edit_area_dropdown").show()
        return false;
    }

    function loadTaskActivity(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/task/loadTaskActivity", // json datasource
            data: '',
            success: function (res) {

                $("#TaskActivityDown").html(res);
                $("#preloader").hide();
            }
        })
    }
    
</script>
