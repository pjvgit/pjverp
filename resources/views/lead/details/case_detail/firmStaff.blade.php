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
                        <input class="client_share_all" name="client_share_all" id="client_share_all"
                            <?php if(count($staffData)==count($alreadySelected)){?> checked="checked" <?php } ?> type="checkbox">
                        <?php }else{ ?>
                        <input class="client_share_all" name="client_share_all" id="client_share_all" type="checkbox">
                        <?php } ?>
                    </td>
                    <td>
                        <input class="client_attend_all" name="client-attend-all" id="client_attend_all" type="checkbox">
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
                                class="client-login-not-enabled handler-attached client_share_all_users">
                            <?php } else {  ?>
                            <input name="linked_staff_checked_share[]" id="linked_staff_checked_share_{{$val->id}}"
                                rowVal="{{$val->id}}" value="{{$val->id}}" <?php if($val->id==Auth::User()->id){ ?> checked="checked"
                                <?php } ?> type="checkbox" <?php if($val->id == Auth::User()->id){ ?> defaultreminder="yes" <?php } ?> class="client-login-not-enabled handler-attached client_share_all_users">
                            <?php } ?>
                        </label>
                    </td>
                    <td>
                        <label class="mb-0">
                            <?php 
                            if(isset($from) && $from=="edit"){?>
                            <input name="linked_staff_checked_attend[]" <?php if(in_array($val->id,$isAttending)){ ?> checked="checked"
                                <?php } ?> value="{{$val->id}}" class="client_attend_all_users"
                                id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">
                
                            <?php } else {  ?>
                            <input name="linked_staff_checked_attend[]" value="{{$val->id}}" class="client_attend_all_users"
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
        $(".client_share_all").click(function () {
            $(".client_share_all_users").prop('checked', $(this).prop('checked'));
            $(".client_attend_all_users").prop('disabled', !$(this).prop('checked'));
            $(".client_attend_all_users").prop('checked', false);
            $('.client_attend_all').prop('checked', false);

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
        $(".client_attend_all").click(function () {
            if ($("#client_share_all").prop('checked')) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
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
            if ($('.client_share_all_users:checked').length == $('.client_share_all_users').length) {
                $('.client_share_all').prop('checked', true);
            } else {
                $('.client_share_all').prop('checked', false);
            }
        });

        /**********************/
        $(".client_attend_all").click(function () {
            if ($(this).is(':checked')) {
                $(".client_share_all_users").each(function () {
                    var id = $(this).attr('rowVal');
                    if ($(this).is(':checked')) {
                    
                        if ($("#linked_staff_checked_share_" + id).prop('checked') == true) {
                            $("#linked_staff_checked_attend_" + id).prop('checked', $(this).prop(
                                'checked'));
                        }
                    }else{
                        $("#linked_staff_checked_attend_" + id).prop('checked',false);
                    }
                })
             }else{
                $(".client_attend_all_users").prop('checked', false);
            }
        });

        $(".client_attend_all_users").click(function () {
            var id = $(this).attr('rowVal');
            $("#linked_staff_checked_share_" + id).prop('disabled', !$(this).prop('checked'));
            if ($(this).prop('checked') == false) {
                $("#linked_staff_checked_attend_" + id).prop('checked', $(this).prop('checked'));
            }
            if ($('.client_attend_all_users:checked').length == $('.client_attend_all_users').length) {
                $('.client_attend_all').prop('checked', true);
            } else {
                $('.client_attend_all').prop('checked', false);
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

</script>
