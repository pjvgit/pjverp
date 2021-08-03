<div class="sharing-table clients-table" bladeFile="resources/views/task/loadTaskRightSection.blade.php">
    <div class="table-responsive">
        <?php 
        if(!$caseCllientSelection->isEmpty()){?>
        <table class="table table-lg" id="CaseClientSection">
            <tr  style="background-color:#FBFBFC;">
                <th class="w-75">Contacts &amp; Leads</th>
                <th class="no-border">Assign <i id="help-bubble-4" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary cursor-pointer" tabindex="0" role="button" href="javascript:;" data-toggle="popover"   title="Assigning to Clients" data-content='<div class="popover-body">When you add clients and contacts to a task, they will receive an email with a link to view it in their portal. <a href="#" rel="noopener noreferrer" target="_blank"><u>What will my client see?</u></a></div>' data-html="true" data-original-title="" style="float:revert;"></i>
                    </th>
            </tr>
            <tr>
                <td><b>Select All</b></td>
                <td><input name="client-share-all" id="client_share_all" type="checkbox"></td>
                {{-- <td><input name="client-attend-all" type="checkbox"></td> --}}
            </tr>
            <?php 
                    foreach($caseCllientSelection as $key=>$val){?>
            <tr class="sharing-user">
                <td class="d-flex  no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    <a class="event-name d-flex align-items-center" tabindex="0" role="button" href="#"
                        data-toggle="popover" title=""
                        data-content="<?php if($val->mobile_number==''){?> <span> No cell phone number.
                        </span><br><?php } ?> <?php if($val->email==''){?> No Email.</span> <br> <?php } ?> <a href='{{BASE_URL}}contacts/clients/{{$val->user_id}}'>Edit Info</a>"
                        data-html="true">
                        <?php if($val->mobile_number==''){?> <i class="texting-off-icon"></i> <?php } ?>
                        <?php if($val->email==''){?> <i class="no-email-icon"></i> <?php } ?>
                    </a>
                </td>
                <?php /* <td>
                    <label class="mb-0">
                        <?php 
                        if($val->client_portal_enable=="0"){
                        ?>
                        <input data-email-present="false" name="clientCheckbox[]" id="cleintUSER_{{$val->id}}"
                            onclick="loadGrantAccessModal({{$val->id}});" type="checkbox"
                            class="lead_client_attend_all_users client-login-not-enabled handler-attached">
                        <?php
                        }else{
                            ?>
                        <input data-email-present="false" name="clientCheckbox[]" id="cleintUSER_{{$val->id}}"
                            onclick="loadGrantAccessModal({{$val->id}});" type="checkbox"
                            class="lead_client_attend_all_users client-login-not-enabled handler-attached">
                        <?php
                        }?>

                    </label>
                </td> */ ?>
                <td>
                    <label class="mb-0">
                        <input name="attend-checkbox" type="checkbox" class="client_share_all_users">
                    </label>
                </td>

            </tr>
            <?php } ?>
        </table>
    <?php } ?>
    </div>
</div>
<button type="button" class="btn btn-link" bladeName="resources/views/task/loadTaskRightSection.blade.php" id="HideShowNonlink">Include staff member not linked to this case</button>
<div class="sharing-table staff-table-nonlinked" @if(count($caseNonLinkedAssigned) == 0) style="display:none;" @endif>
    <div class="table-responsive">
        <table class="table table-lg" id="CaseNoneLinkedStaffSection">
            <tr class="no-border"   style="background-color:#FBFBFC;">
                <th class="sharing-list-header no-border w-75">Staff (Non-Linked)</th>
                <th class="no-border">Assign</th>
            </tr>

            <?php foreach($loadFirmUser as $key=>$val){?>
            <tr class="sharing-user">
                <td class=" no-border ">
                    <span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    {{-- <div style="text-indent: 0px;">
                        <div id="communication-19798317"><i class="texting-off-icon"></i><i class="no-email-icon"></i>
                        </div>
                    </div>
                    <small class="client-portal-alert float-right" style="visibility: hidden;">
                        <span style="position: relative;" class="tooltip-wrapper"><span>
                                <i class="tooltip-alert"></i>
                            </span>
                        </span>
                    </small> --}}
                </td>
                <td>
                    <label class="mb-0">
                        <input data-email-present="false" rowVal="{{$val->id}}" value="{{$val->id}}"
                            <?php if(in_array($val->id,$caseNonLinkedAssigned)){ ?> checked="checked" <?php } ?>
                            name="share_checkbox_nonlinked[]" id="share_checkbox_nonlinked_{{$val->id}}" type="checkbox"
                            class="client-login-not-enabled handler-attached share_checkbox_nonlinked"></label>
                </td>
                <?php /* <td>
                    <label class="mb-0"><input name="attend_checkbox_nonlinked[]" disabled="disabled"
                            id="attend_checkbox_nonlinked_{{$val->id}}" type="checkbox"></label>
                </td> */ ?>
            </tr>
            <?php } 
            if($loadFirmUser->isEmpty()){ ?>
            <tr class="sharing-user">
                <td class=" no-border" colspan="3"> Non linked staff member not available</td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<div class="sharing-table staff-table">
    <div class="table-responsive">
        <table class="table table-lg" id="CaseLinkedStaffSection">
            <tr class="no-border"   style="background-color:#FBFBFC;">
                <th class="sharing-list-header no-border w-75">Staff</th>
                <th class="no-border">Assign</th>
            </tr>

            <tr>
                <td><b>Select All</b></td>
                <td>
                    <?php 
                    if(isset($from) && $from=="edit"){?>
                    <input name="client_attend_all" id="client_attend_all"
                        <?php if(count($caseLinkedStaffList)==count($caseLinkedSavedAssigned)){?> checked="checked" <?php } ?>
                        type="checkbox">
                    <?php }else{ ?>
                    <input name="client_attend_all" id="client_attend_all" checked="checked" type="checkbox">
                    <?php } ?>
                </td>
                <?php /*<td>
                    <input name="client-attend-all" id="client_attend_all" type="checkbox">
                </td>*/ ?>
            </tr>
            <?php  foreach($caseLinkedStaffList as $key=>$val){?>
            <tr class="sharing-user">
                <td class=" no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    {{-- <div style="text-indent: 0px;">
                        <div id="communication-19798317"><i class="texting-off-icon"></i><i class="no-email-icon"></i>
                        </div>
                    </div><small class="client-portal-alert float-right" style="visibility: hidden;"><span
                            style="position: relative;" class="tooltip-wrapper"><span><i
                                    class="tooltip-alert"></i></span></span></small> --}}
                </td>
                <?php /*   <td>
                    <label class="mb-0">
                        <?php 
                        if(isset($from) && $from=="edit"){?>
                        <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                            rowVal="{{$val->id}}" value="{{$val->id}}" <?php if(in_array($val->id,$caseLinkeSavedAssign)){ ?>
                            checked="checked" <?php } ?> type="checkbox"
                            class="client-login-not-enabled handler-attached client_share_all_users">
                        <?php } else {  ?>
                        <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                            rowVal="{{$val->id}}" value="{{$val->id}}" checked="checked" type="checkbox"
                            class="client-login-not-enabled handler-attached client_share_all_users">
                        <?php } ?>
                    </label>
                </td>
               */?>
                <td>
                    <label class="mb-0">
                        <?php 
                        if(isset($from) && $from=="edit"){?>
                        <input name="linked_staff_checked_attend[]"
                            <?php if(in_array($val->id,$caseLinkedSavedAssigned)){ ?> checked="checked" <?php } ?>
                            value="{{$val->id}}" class="client_attend_all_users"
                            id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">

                        <?php } else {  ?>
                        <input name="linked_staff_checked_attend[]" value="{{$val->id}}" class="client_attend_all_users"
                            id="linked_staff_checked_attend_{{$val->id}}" checked="checked" type="checkbox">
                        <?php } ?>

                    </label>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("[data-toggle=popover]").popover({
            html: true
        });

        $(".share_checkbox_nonlinked").click(function () {
            var id = $(this).attr('rowVal');
            $("#attend_checkbox_nonlinked_" + id).prop('disabled', !$(this).prop('checked'));
        });
        $("#client_share_all").click(function () {
            $(".client_share_all_users").prop('checked', $(this).prop('checked'));
        });
        $("#client_attend_all").click(function () {
            $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
        });

        $(".share_checkbox_nonlinked").click(function () {
            var id = $(this).attr('rowVal');
            if ($(this).prop('checked') == false) {
                $("#attend_checkbox_nonlinked_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
        });
        $("#client_share_all").click(function () {

            // $(".client_share_all_users").prop('checked', $(this).prop('checked'));
            // $(".client_attend_all_users").prop('disabled', !$(this).prop('checked'));
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }

        });
        $("#client_attend_all").click(function () {
            if ($("#client_share_all").prop('checked')) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
            }
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
        });
        $(".client_attend_all_users").click(function () {
            var id = $(this).attr('rowVal');
          
            if ($(this).prop('checked') == false) {
                $("#linked_staff_checked_attend_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($('.client_attend_all_users:checked').length == $('.client_attend_all_users').length) {
                $('#client_attend_all').prop('checked', true);
            } else {
                $('#client_attend_all').prop('checked', false);
            }
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
        });
        $("#HideShowNonlink").on('click', function () {
            $(".staff-table-nonlinked").toggle();
        });
    });

    function loadTimeEstimationUsersListbkp(SU) {
        var arrayList = [];

        $(".userwiseHours").each(function(){
            arrayList.push({'hour':$(this).val(),'id':$(this).attr('ownid')});
           
        });
        console.log(arrayList);
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTimeEstimationUsersList",
            data: {
                "userList": JSON.stringify(SU),
                "arrayList": JSON.stringify(arrayList)
            },
            success: function (res) {
                $("#dynamicUSerTimes").html(res);
            }
        })
    }

    function getCheckedUser() {
        var array = [];
        $('input[name="linked_staff_checked_attend[]"]:checked').each(function (i) {
            array.push($(this).val());
        });
        $('input[name="share_checkbox_nonlinked[]"]:checked').each(function (i) {
            array.push($(this).val());
        });

        if ($('.client_attend_all_users:checked').length == $('.client_attend_all_users').length) {
            $("#client_attend_all").prop('checked', "checked")
        } else {
            $("#client_attend_all").prop('checked', false)
        }
        return array;
    }

</script>
