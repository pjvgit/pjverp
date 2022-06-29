<div class="sharing-table clients-table"  bladename="resources/views/lead/details/case_detail/firmStaff.blade.php">
    <div class="table-responsive">
        <table class="table table-lg" id="CaseClientSection">
            <thead>
                <tr class="no-border"  style="background-color:#FBFBFC;">
                    <th class="sharing-list-header no-border w-75">Staff</th>
                    <th class="no-border">Share</th>
                    <th class="no-border">Attend</th>
                </tr>
            </thead>
            <tbody class="no-border">
                <tr>
                    <td>Select All</td>
                    <td>
                        <?php 
                        if(isset($from) && $from=="edit"){?>
                        <input class="staff_share_all" name="staff_share_all" id="staff_share_all"
                            <?php if(count($staffData)==count($alreadySelected)){?> checked="checked" <?php } ?> type="checkbox">
                        <?php }else{ ?>
                        <input class="staff_share_all" name="client_share_all" id="staff_share_all" type="checkbox">
                        <?php } ?>
                    </td>
                    <td>
                        @if(isset($from) && $from=="edit")
                        <input class="staff_attend_all" name="client-attend-all" id="staff_attend_all" type="checkbox" {{ (count($staffData) == count($isAttending)) ? 'checked' : '' }}>
                        @else
                        <input class="staff_attend_all" name="client-attend-all" id="staff_attend_all" type="checkbox">
                        @endif
                    </td>
                </tr>
                <?php foreach($staffData as $key=>$val){?>
                <tr class="sharing-user">
                    <td class=" no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                    </td>
                    <td>
                        <label class="mb-0">
                            <?php 
                            if(isset($from) && $from=="edit"){?>
                            <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                                rowVal="{{$val->id}}" value="{{$val->id}}" <?php if(in_array($val->id,$alreadySelected)){ ?>
                                checked="checked" <?php } ?> type="checkbox"
                                class="client-login-not-enabled handler-attached staff_share_all_users">
                            <?php } else {  ?>
                            <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                                rowVal="{{$val->id}}" value="{{$val->id}}" <?php if($val->id==Auth::User()->id){ ?> checked="checked"
                                <?php } ?> type="checkbox" <?php if($val->id == Auth::User()->id){ ?> defaultreminder="yes" <?php } ?> class="client-login-not-enabled handler-attached staff_share_all_users">
                            <?php } ?>
                        </label>
                    </td>
                    <td>
                        <label class="mb-0">
                            <?php 
                            if(isset($from) && $from=="edit"){?>
                            <input name="linked_staff_checked_attend[]" <?php if(in_array($val->id,$isAttending)){ ?> checked="checked"
                                <?php } ?> value="{{$val->id}}" class="staff_attend_all_users"
                                id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">
                
                            <?php } else {  ?>
                            <input name="linked_staff_checked_attend[]" value="{{$val->id}}" class="staff_attend_all_users"
                                id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">
                            <?php } ?>
                
                        </label>
                    </td>
                </tr>
                <?php } ?>
                
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // check if login user is checked or not for showing default reminder
        <?php if(isset($from) && $from != "edit"){?>   
        $(".fieldGroup").empty();
        <?php } ?>   
        $(".staff_share_all").click(function () {
            var modalId = $(this).parents('div.modal').attr("id");
            $("#"+modalId+" .staff_share_all_users").prop('checked', $(this).prop('checked'));
            $("#"+modalId+" .staff_attend_all_users").prop('disabled', !$(this).prop('checked'));
            $("#"+modalId+" .staff_attend_all_users").prop('checked', false);
            $("#"+modalId+" .staff_attend_all").prop('checked', false);

            // check if login user is checked or not for showing default reminder
            <?php if(isset($from) && $from != "edit"){?>
            $("#"+modalId+" .staff_share_all_users").each(function (i) {
                if($(this).val() == '{{auth::user()->id}}'){
                    $("#"+modalId+" .reminder_user_type").each(function (j) {
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
        $(".staff_attend_all").click(function () {
            var modalId = $(this).parents('div.modal').attr("id");
            if ($("#"+modalId+" .staff_share_all").prop('checked')) {
                $("#"+modalId+" .staff_attend_all_users").prop('checked', $(this).prop('checked'));
            }
        });
        $(".staff_share_all_users").click(function () {
            var modalId = $(this).parents('div.modal').attr("id");
            var id = $(this).attr('rowVal');
            // check if login user is checked or not for showing default reminder
            <?php if(isset($from) && $from != "edit"){?>
            if(id == '{{auth::user()->id}}'){
                $("#"+modalId+" .reminder_user_type").each(function (j) {
                    if($(this).val() == 'me'){
                        $(this).parents('.fieldGroup').remove();
                    }
                });
                if ($(this).prop('checked') == true) {
                    loadDefaultEventReminder();
                }
            }
            <?php } ?>   
            $("#"+modalId+" #linked_staff_checked_attend_" + id).prop('disabled', !$(this).prop('checked'));
            if ($(this).prop('checked') == false) {
                $("#"+modalId+" #linked_staff_checked_attend_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($("#"+modalId+" .staff_share_all_users:checked").length == $("#"+modalId+" .staff_share_all_users").length) {
                $("#"+modalId+" .staff_share_all").prop('checked', true);
            } else {
                $("#"+modalId+" .staff_share_all").prop('checked', false);
                $("#"+modalId+" .staff_attend_all").prop('checked', false);
            }
        });

        /**********************/
        $(".staff_attend_all").click(function () {
            var modalId = $(this).parents('div.modal').attr("id");
            if ($(this).is(':checked')) {
                $("#"+modalId+" .staff_share_all_users").each(function () {
                    var id = $(this).attr('rowVal');
                    if ($(this).is(':checked')) {
                    
                        if ($("#"+modalId+" #linked_staff_checked_share_" + id).prop('checked') == true) {
                            $("#"+modalId+" #linked_staff_checked_attend_" + id).prop('checked', $(this).prop(
                                'checked'));
                        }
                    }else{
                        $("#"+modalId+" #linked_staff_checked_attend_" + id).prop('checked',false);
                    }
                })
             }else{
                $("#"+modalId+" .staff_attend_all_users").prop('checked', false);
            }
        });

        $(".staff_attend_all_users").click(function () {
            var modalId = $(this).parents('div.modal').attr("id");
            var id = $(this).attr('rowVal');
            $("#"+modalId+" #linked_staff_checked_share_" + id).prop('disabled', !$(this).prop('checked'));
            if ($(this).prop('checked') == false) {
                $("#"+modalId+" #linked_staff_checked_attend_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($("#"+modalId+" .staff_attend_all_users:checked").length == $("#"+modalId+" .staff_attend_all_users").length) {
                $("#"+modalId+" .staff_attend_all").prop('checked', true);
            } else {
                $("#"+modalId+" .staff_attend_all").prop('checked', false);
            }
        });

        // check if login user is checked or not for showing default reminder
        <?php if(isset($from) && $from != "edit"){?>
        $('input[name="linked_staff_checked_share[]"]:checked').each(function (i) {
            if($(this).attr("defaultreminder") == 'yes'){
                loadDefaultEventReminder();
            }
        });
        <?php } ?>       
    });

</script>
