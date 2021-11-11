<div class="sharing-table clients-table"  bladename="case\event\loadLeadRightSection.blade.php">
    <div class="table-responsive">
        <table class="table table-lg" id="CaseClientSection">
            <tr style="background-color:#FBFBFC;">
                <td><b>Contact & Leads </b></td>
                <td>Invite<br>
                    <span tabindex="0" data-toggle="popover" data-trigger="focus" data-placement="bottom" title="Invite Contacts and Leads" data-content='<div class="popover-body"><strong>Contacts:</strong> When you invite a contact to an event, they will receive an email with event details and it will be shared on their client portal.<br><br><strong>Leads:</strong> When you invite a lead to an event, they will receive an email with event details.</div>'>
                        <i class="fas fa-question-circle"></i>
                    </span>
                </td>
                <td>Attend</td>
            </tr>
            <tr>
                <td><b>Select All</b></td>
                <td><input name="client-share-all" id="SelectAllLeadShare" type="checkbox" class="load-default-reminder" <?php if(count($caseCllientSelection)==count($caseLinkeSavedInviteLead)){?> checked="checked" <?php } ?>></td>
                <td><input name="client-attend-all" id="SelectAllLeadAttend" type="checkbox" <?php if(count($caseCllientSelection)==count($caseLinkeSavedAttendingLead)){?> checked="checked" <?php } ?>></td>
            </tr>
            <?php 
                    foreach($caseCllientSelection as $key=>$val){?>
            <tr class="sharing-user">
                <td class="d-flex  no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    <a class="event-name d-flex align-items-center" tabindex="0" role="button" href="#"
                        data-toggle="popover" title="" data-trigger="hover"
                        data-content="<?php if($val->mobile_number==''){?> <span> No cell phone number.
                        </span><br><?php } ?> <?php if($val->email==''){?> No Email.</span> <br> <?php } ?> <a href='{{ route('contacts/clients/view', $val->id) }}'>Edit Info</a>"
                        data-html="true">
                        <?php if($val->mobile_number==''){?> <i class="texting-off-icon"></i> <?php } ?>
                        <?php if($val->email==''){?> <i class="no-email-icon"></i> <?php } ?>
                    </a>
                    {{-- <?php if($val->client_portal_enable == '0'){?> 
                    <i class="tooltip-alert" id="err_popover_{{ $val->id }}" data-toggle="popover" data-trigger="hover"  data-placement= "bottom"  title="" 
                        data-content='This user is not yet enabled for the Client Portal. Click the box next to their near to invite them and share this item.' 
                        data-html="true" data-original-title="" style="display: none;"></i>
                    <?php } ?> --}}
                </td>
                <td>
                    <label class="mb-0">
                        <input data-email-present="false" name="LeadInviteClientCheckbox[]" value="{{$val->id}}" id="cleintUSER_{{$val->id}}" <?php if(in_array($val->id,$caseLinkeSavedInviteLead)){ ?> checked="checked" <?php } ?> 
                            onclick="loadGrantAccessModal({{$val->id}});" {{-- data-client_portal_enable="{{$val->client_portal_enable}}" --}} type="checkbox"
                            class="lead_client_share_all_users client-login-not-enabled handler-attached load-default-reminder">
                    </label>
                </td>
                <td>
                    <label class="mb-0">
                        <input  class="lead_client_attend_all_users {{-- {{ ($val->client_portal_enable == '0') ? 'not-enable-portal' : '' }} --}}"  id="attend_user_{{$val->id}}" 
                        name="LeadAttendClientCheckbox[]" type="checkbox" value="{{$val->id}}"  
                        <?php if(in_array($val->id,$caseLinkeSavedAttendingLead)){ ?> checked="checked" <?php } ?> {{ (in_array($val->id,$caseLinkeSavedInviteLead)) ? "" : "disabled" }}>
                    </label>
                </td>

            </tr>
            <?php } ?>
        </table>
    </div>
</div>

<div class="sharing-table staff-table">
    <div class="table-responsive">
        <table class="table table-lg" id="CaseLinkedStaffSection">
            <tr class="no-border" style="background-color:#FBFBFC;">
                <th class="sharing-list-header no-border w-75">Staff</th>
                <th class="no-border">Share</th>
                <th class="no-border">Attend</th>
            </tr>

            <tr>
                <td><b>Select All</b></td>
                <td>
                    <?php if(isset($from) && $from=="edit"){?>
                        <input name="client_share_all" id="client_share_all"
                        type="checkbox">
                    
                    <?php }else{ ?>
                    <input name="client_share_all" id="client_share_all"  type="checkbox" {{ (count($loadFirmUser) == 1) ? "checked" : "" }}>
                    <?php } ?>
                </td>
                <td>
                    <?php if(isset($from) && $from=="edit"){?>
                        <input name="client-attend-all" id="client_attend_all"
                        type="checkbox">
                    
                    <?php }else{ ?>
                        <input name="client-attend-all" id="client_attend_all" type="checkbox" {{ (count($loadFirmUser) == 1) ? "checked" : "" }}>                    
                    <?php } ?>
                    
                </td>
            </tr>
            <?php  foreach($loadFirmUser as $key=>$val){?>
            <tr class="sharing-user">
                <td class=" no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    <div style="text-indent: 0px;">
                        <div id="communication-19798317"><i class="texting-off-icon"></i><i class="no-email-icon"></i>
                        </div>
                    </div><small class="client-portal-alert float-right" style="visibility: hidden;"><span
                            style="position: relative;" class="tooltip-wrapper"><span><i
                                    class="tooltip-alert"></i></span></span></small>
                </td>
                <td>
                    <label class="mb-0">
                        <?php 
                        if(isset($from) && $from=="edit"){?>
                        <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                            rowVal="{{$val->id}}" value="{{$val->id}}" <?php if(in_array($val->id,$caseLinkeSaved)){ ?>
                            checked="checked" <?php } ?> type="checkbox"
                            class="client-login-not-enabled handler-attached client_share_all_users">
                        <?php } else {  ?>
                        <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                            rowVal="{{$val->id}}" value="{{$val->id}}" <?php if($val->id==Auth::User()->id){ ?>
                                checked="checked" <?php } ?>  type="checkbox"
                            class="client-login-not-enabled handler-attached client_share_all_users">
                        <?php } ?>
                    </label>
                </td>
                <td>
                    <label class="mb-0">
                        <?php 
                        if(isset($from) && $from=="edit"){?>
                        <input name="linked_staff_checked_attend[]"
                            <?php if(in_array($val->id,$caseLinkeSavedAttending)){ ?> checked="checked" <?php } ?>
                            value="{{$val->id}}" class="client_attend_all_users"
                            id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">

                        <?php } else {  ?>
                        <input name="linked_staff_checked_attend[]" value="{{$val->id}}"   <?php if($val->id==Auth::User()->id){ ?>
                            checked="checked" <?php } ?>   class="client_attend_all_users"
                            id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">
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
        $("#client_attend_all").click(function () {
            if ($("#client_share_all").prop('checked')) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
            }
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

            $(".client_share_all_users").prop('checked', $(this).prop('checked'));
            $(".client_attend_all_users").prop('disabled', !$(this).prop('checked'));
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
        $(".client_share_all_users").click(function () {
            var id = $(this).attr('rowVal');
            $("#linked_staff_checked_attend_" + id).prop('disabled', !$(this).prop('checked'));
            if ($(this).prop('checked') == false) {
                $("#linked_staff_checked_attend_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
        });
        $("#HideShowNonlink").on('click', function () {
            $(".staff-table-nonlinked").toggle();
        });

        $("#SelectAllLeadShare").click(function () {
            var multi = $('.lead_client_share_all_users');
            /* var winners_array = [];
            $.each(multi, function (index, item) {
                if($(item).attr('data-client_portal_enable') == 0){
                    winners_array.push( {name: $(item).val(), value: $(item).attr('data-client_portal_enable')} );  
                    $("#err_popover_"+item.value).show();
                }
            }); */

            $(".lead_client_share_all_users").prop('checked', $(this).prop('checked'));
            /* if(winners_array.length > 0){
                $.each(winners_array, function (index, item) {
                    // $(".tooltip-alert").show();
                    $("#cleintUSER_"+item.name).prop('checked',false);
                });
                $("#SelectAllLeadAttend").prop('checked', false);
            } */
            $(".lead_client_attend_all_users").prop('disabled', !$(this).prop('checked'));
            $(".not-enable-portal").prop('disabled', true);
            if(!$(this).is(":checked")) {
                $(".lead_client_attend_all_users").prop('checked', $(this).prop('checked'));
                $("#SelectAllLeadAttend").prop('checked', $(this).prop('checked'));
                $(".not-enable-portal").prop('checked', false);
                $(".not-enable-portal").prop('disabled', true);
            }  
        });
        $(".lead_client_share_all_users ").click(function () {
            var userId = $(this).val();
            if ($('.lead_client_share_all_users:checked').length == $('.lead_client_share_all_users').length) {
                $("#SelectAllLeadShare").prop('checked', true);
            } else {
                $("#SelectAllLeadShare").prop('checked', false);
            }
            if($(this).is(":checked")){
                $("#attend_user_"+userId).prop('disabled', false);
            }else{
                $("#attend_user_"+userId).prop('disabled', true);
                $("#attend_user_"+userId).prop('checked', false);
                $("#SelectAllLeadAttend").prop('checked', false);
            }
        });
        $("#SelectAllLeadAttend").click(function () {
            // $(".lead_client_attend_all_users").prop('checked', $(this).prop('checked'));
            var checkVal = $(this).prop('checked');
            $(".lead_client_attend_all_users").each(function() {
                if(!$(this).is(":disabled")) {
                    $(this).prop('checked', checkVal);
                }
            });
            $(".not-enable-portal").prop('checked', false);
        });
        $(".lead_client_attend_all_users ").click(function () {
            if ($('.lead_client_attend_all_users:checked').length == $('.lead_client_attend_all_users').length) {
                $("#SelectAllLeadAttend").prop('checked', "checked")
            } else {
                $("#SelectAllLeadAttend").prop('checked', false)
            }
        });
    });

    function loadTimeEstimationUsersList(SU) {
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

    function getCheckedUser() {
        var array = [];
        $('input[name="linked_staff_checked_share[]"]:checked').each(function (i) {
            array.push($(this).val());
        });
        $('input[name="share_checkbox_nonlinked[]"]:checked').each(function (i) {
            array.push($(this).val());
        });

        if ($('.client_share_all_users:checked').length == $('.client_share_all_users').length) {
            $("#client_share_all").prop('checked', "checked")
        } else {
            $("#client_share_all").prop('checked', false)
        }
        return array;
    }

</script>
