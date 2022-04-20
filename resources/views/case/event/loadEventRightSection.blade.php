<div class="sharing-table clients-table" bladename="resources/views/case/event/loadEventRightSection.blade.php">
    <div class="table-responsive">
        <?php 
        if(!empty($caseCllientSelection)){?>
        <table class="table table-lg" id="CaseClientSection">
            <tr style="background-color:#FBFBFC;">
                <td><b>Contact & Leads </b></td>
                <td>Invite <br><i id="help-bubble-4" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary cursor-pointer" tabindex="0" role="button" href="javascript:;" data-toggle="popover"  data-placement= "bottom"  title="Invite Contacts and Leads" data-content='<div class="popover-body"><strong>Contacts:</strong> When you invite a contact to an event, they will receive an email with event details and it will be shared on their client portal.<br><br><strong>Leads:</strong> When you invite a lead to an event, they will receive an email with event details.</div>' data-html="true" data-original-title="" ></i></td>
                <td>Attend </td>
            </tr>
            <tr>
                @php
                    $enabledClientPortalUser = $caseCllientSelection->whereIn('id', $caseLinkeSavedInviteContact)->where('client_portal_enable', '1')->pluck('id')->toArray();
                    $enabledClientPortalUserAttending = $caseCllientSelection->whereIn('id', $caseLinkeSavedAttendingContact)->where('client_portal_enable', '1')->pluck('id')->toArray();
                    $linkedUserPortalDisabled = $caseCllientSelection->whereIn('id', $caseLinkeSavedInviteContact)->where('client_portal_enable', '0')->pluck('id')->toArray();
                @endphp
                <td><b>Select All</b></td>
                <td><input name="client-share-all" id="SelectAllLeadShare" type="checkbox" class="load-default-reminder-all" <?php if(count($caseCllientSelection)==count($caseLinkeSavedInviteContact)){?> checked="checked" <?php } ?>></td>
                <td><input name="client-attend-all" id="SelectAllLeadAttend" type="checkbox" <?php if(count($caseCllientSelection)==count($caseLinkeSavedAttendingContact)){?> checked="checked" <?php } ?>></td>
            </tr>
            <?php 
            foreach($caseCllientSelection as $key=>$val){?>
            @if($val->user_level == '4')
            <tr class="sharing-user sharing-company"><td colspan="3">{{$val->first_name}} {{$val->last_name}}</td></tr>
            @else
            <tr class="sharing-user">
                <td class="d-flex no-border @if($val->is_company_contact == 'yes') indent @endif">
                    <span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    <a class="event-name d-flex align-items-center pop" tabindex="0" role="button" href="javascript:;" data-toggle="popover" title=""
                        data-content="<?php if($val->mobile_number==''){?> <span> No cell phone number. </span><br><?php } ?> <?php if($val->email==''){?> No Email.</span> <br> <?php } ?> <a href='{{ route('contacts/clients/view', $val->user_id) }}'>Edit Info</a>" data-html="true">
                        <?php if($val->mobile_number==''){?> <i class="texting-off-icon"></i> <?php } ?>
                        <?php if($val->email==''){?> <i class="no-email-icon"></i> <?php } ?>
                    </a>
                    <?php if($val->client_portal_enable=='0'){?> 
                    <i class="tooltip-alert" id="err_popover_{{ $val->id }}" data-toggle="popover"  data-placement= "bottom" data-trigger="hover"  title="" 
                        data-content='This user is not yet enabled for the Client Portal. Click the box next to their near to invite them and share this item.' 
                        data-html="true" data-original-title="" style="display: none;"></i>
                    <?php } ?>
                    <?php if(in_array($val->id, $linkedUserPortalDisabled)){?> 
                    <i class="tooltip-alert" id="war_popover_{{ $val->id }}" data-toggle="popover"  data-placement= "bottom" data-trigger="hover"  title="" 
                        data-content='Client Portal must be enabled for this user' 
                        data-html="true" data-original-title=""></i>
                    <?php } ?>
                </td>
                
                <td>
                    <label class="mb-0 loadEventRightSection">
                        <input data-email-present="false" name="ContactInviteClientCheckbox[]" <?php if(in_array($val->id,$enabledClientPortalUser)){ ?> checked="checked" <?php } ?> value="{{$val->id}}" id="cleintUSER_{{$val->id}}"
                        onclick="loadGrantAccessModal({{$val->id}});" data-client_portal_enable="{{$val->client_portal_enable}}" type="checkbox"
                        class="lead_client_share_all_users client-login-not-enabled handler-attached load-default-reminder">
                    </label>
                </td>
                <td>
                    <label class="mb-0">
                        <input {{ (in_array($val->id,$caseLinkeSavedInviteContact)) ? "" : "disabled" }} class="lead_client_attend_all_users {{ ($val->client_portal_enable == '0') ? 'not-enable-portal' : '' }}" id="attend_user_{{$val->id}}" name="ContactAttendClientCheckbox[]" 
                        <?php if(in_array($val->id,$enabledClientPortalUserAttending)){ ?> checked="checked" <?php } ?> value="{{$val->id}}" 
                        type="checkbox">
                    </label>
                </td>
            </tr>
            @endif
            <?php } ?>
        </table>
    <?php } ?>
    </div>
</div>

<button type="button" class="btn btn-link" bladeName="resources/views/case/event/loadEventRightSection.blade.php" id="HideShowNonlink">Include staff member not linked to this case</button>
<div class="sharing-table staff-table-nonlinked" @if(count($caseNonLinkeSaved) == 0) style="display:none;" @endif>
    <div class="table-responsive">
        <table class="table table-lg" id="CaseNoneLinkedStaffSection">
            <tr class="no-border" style="background-color:#FBFBFC;">
                <th class="sharing-list-header no-border w-75">Staff (Non-Linked)</th>
                <th class="no-border">Share</th>
                <th class="no-border">Attend</th>
            </tr>
            <?php foreach($loadFirmUser as $key=>$val){?>
                <tr class="sharing-user">
                    <td class=" no-border ">
                        <span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                        
                    </td>
                    <td>
                        <label class="mb-0">
                            <input data-email-present="false" rowVal="{{$val->id}}" value="{{$val->id}}"
                            <?php if(in_array($val->id,$caseNonLinkeSaved)){ ?> checked="checked" <?php } ?>
                                name="share_checkbox_nonlinked[]" id="share_checkbox_nonlinked_{{$val->id}}" type="checkbox"
                                class="client-login-not-enabled handler-attached share_checkbox_nonlinked"></label>
                    </td>
                    <td>
                        <label class="mb-0"><input name="attend_checkbox_nonlinked[]"  value="{{$val->id}}"
                            <?php if(in_array($val->id,$caseNonLinkeSavedAttending)){ ?> checked="checked" <?php } else {  if(!in_array($val->id,$caseNonLinkeSaved)) { ?> disabled="disabled" <?php } } ?>
                                id="attend_checkbox_nonlinked_{{$val->id}}" type="checkbox"></label>
                    </td>
                </tr>
            <?php } 
            if($loadFirmUser->isEmpty()){ ?>
                <tr class="sharing-user">
                    <td class=" no-border" colspan="3"> Non linked staff member not available</td>
                    <td></td>
                    <td></td>
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
                <td sr-count="{{ count(collect($caseLinkeSaved)->where('is_linked', 'yes')) .' = '. count($caseLinkedStaffList) }}">
                    <?php if(isset($from) && $from=="edit"){?>
                        <input name="client_share_all" id="client_share_all"
                        <?php if(count($caseLinkeSaved)==count($caseLinkedStaffList)){?> checked="checked" <?php } ?>
                        type="checkbox">
                    <?php }else{ ?>
                    <input name="client_share_all" id="client_share_all" checked="checked" type="checkbox">
                    <?php } ?>
                </td>
                <td sr-count="{{ count($caseLinkedStaffList) .' = '. count($caseLinkeSavedAttending) }}">
                    <?php if(isset($from) && $from=="edit"){?>
                        <input name="client-attend-all" id="client_attend_all"
                        <?php if(count($caseLinkedStaffList)==count($caseLinkeSavedAttending)){?> checked="checked" <?php } ?>
                        type="checkbox">
                    
                    <?php }else{ ?>
                        <input name="client-attend-all" id="client_attend_all" type="checkbox">                    
                    <?php } ?>
                    
                </td>
            </tr>
            <?php  foreach($caseLinkedStaffList as $key=>$val){?>
            <tr class="sharing-user">
                <td class=" no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                   
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
                            rowVal="{{$val->id}}" value="{{$val->id}}" checked="checked" type="checkbox"
                            <?php if($val->id == Auth::User()->id){ ?> defaultreminder="yes" <?php } ?>
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
                            id="linked_staff_checked_attend_{{$val->id}}" type="checkbox"
                            <?php if(!in_array($val->id,$caseLinkeSaved)){ ?> disabled="disabled" <?php } ?>
                            >

                        <?php } else {  ?>
                        <input name="linked_staff_checked_attend[]" value="{{$val->id}}" class="client_attend_all_users"
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
        // check if login user is checked or not for showing default reminder
        <?php if(isset($from) && $from != "edit"){?>
        $(".fieldGroup").empty();
        <?php } ?>   
        /* $("[data-toggle=popover]").popover({
            html: true
        }); */
        // For popover
        $(".pop, [data-toggle=popover]").popover({ trigger: "manual", html: true, animation: false })
            .on("mouseenter", function() {
                var _this = this;
                $(this).popover("show");
                $(".popover").on("mouseleave", function() {
                    $(_this).popover('hide');
                });
            }).on("mouseleave", function() {
                var _this = this;
                setTimeout(function() {
                    if (!$(".popover:hover").length) {
                        $(_this).popover("hide");
                    }
                }, 300);
            });

        $(".share_checkbox_nonlinked").click(function () {
            var id = $(this).attr('rowVal');
            $("#attend_checkbox_nonlinked_" + id).prop('disabled', !$(this).prop('checked'));
        });
        $("#client_attend_all").click(function () {
            if ($("#client_share_all").prop('checked')) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
            }
            $(".client_attend_all_users").each(function(element, index, set) {
                if($(this).prop("disabled") === false){
                    $(this).prop('checked', $("#client_attend_all").prop('checked'));
                }
            });
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
            if(!$(this).is(":checked")) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
                $("#client_attend_all").prop('checked', $(this).prop('checked'));
                $(".client_attend_all_users").prop("disabled", true)
            } else {
                $(".client_attend_all_users").prop('disabled', !$(this).prop('checked'));
            }
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
            // check if login user is checked or not for showing default reminder
            <?php if(isset($from) && $from != "edit"){?>
            $(".client_share_all_users").each(function (i) {
                if($(this).val() == '{{auth::user()->id}}'){
                    $('.reminder_user_type').each(function (j) {
                        if($(this).val() == 'me'){
                            $(this).parents('.fieldGroup').remove();
                        }
                    });
                    if ($(this).prop('checked') == true) {
                        loadDefaultEventReminder();
                    }
                }
            });
            <?php } ?>   

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
            // check if login user is checked or not for showing default reminder
            <?php if(isset($from) && $from != "edit"){?>
            if(id == '{{auth::user()->id}}'){
                $('.reminder_user_type').each(function (j) {
                    if($(this).val() == 'me'){
                        $(this).parents('.fieldGroup').remove();
                    }
                });
                if ($(this).prop('checked') == true) {
                    loadDefaultEventReminder();
                }
            }
            <?php } ?>   
            $("#linked_staff_checked_attend_" + id).prop('disabled', !$(this).prop('checked'));
            if ($(this).prop('checked') == false) {
                $("#linked_staff_checked_attend_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
        // });

        // $(".client_share_all_users").click(function () {
            if ($('.client_share_all_users:checked').length == $('.client_share_all_users').length) {
                $("#client_share_all").prop('checked', "checked")
            } else {
                $("#client_share_all").prop('checked', false)
            }
        });
        $(".client_attend_all_users").click(function () {
            if ($('.client_attend_all_users:checked').length == $('.client_attend_all_users').length) {
                $("#client_attend_all").prop('checked', "checked")
            } else {
                $("#client_attend_all").prop('checked', false)
            }
        });

        $("#HideShowNonlink").on('click', function () {
            $(".staff-table-nonlinked").toggle();
        });

        $(document).on("click", "#SelectAllLeadShare", function () {
        // $("#SelectAllLeadShare").click(function () {
            var multi = $('.lead_client_share_all_users');
            var winners_array = [];
            $.each(multi, function (index, item) {
                if($(item).attr('data-client_portal_enable') == 0){
                    winners_array.push( {name: $(item).val(), value: $(item).attr('data-client_portal_enable')} );  
                    $("#err_popover_"+item.value).show();
                }
            });

            $(".lead_client_share_all_users").prop('checked', $(this).prop('checked'));
            if(winners_array.length > 0){
                $.each(winners_array, function (index, item) {
                    // $(".tooltip-alert").show();
                    $("#cleintUSER_"+item.name).prop('checked',false);
                });
                $("#SelectAllLeadAttend").prop('checked', false);
            }
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
            if($('.lead_client_share_all_users:checked').length) {
                $(".reminder_user_type").children("option[value='client-lead']").show();
            } else {
                $(".reminder_user_type").children("option[value='client-lead']").hide();
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

        // check if login user is checked or not for showing default reminder
        <?php if(isset($from) && $from != "edit"){?>
        $('input[name="linked_staff_checked_share[]"]:checked').each(function (i) {
            console.log($(this).attr("defaultreminder"));
            if($(this).attr("defaultreminder") == 'yes'){
                loadDefaultEventReminder();
            }
        });
        <?php } ?>       
    });

    function loadTimeEstimationUsersList(SU) {
        console.log("resources/views/case/event/loadEventRightSection.blade.php > loadTimeEstimationUsersList > 359");
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

function MarkAsChanged(){
    $(this).addClass("changed");
}
$(":checkbox").change(MarkAsChanged).change(MarkAsChanged);
</script>
