<div class="sharing-table clients-table" bladeFile="resources/views/task/firmStaff.blade.php">
    <div class="table-responsive">
        <table class="table table-lg" id="CaseClientSection">
            <thead>
                <tr class="no-border" style="background-color:#FBFBFC;">
                    <th class="sharing-list-header no-border w-75">Staff</th>
                    <th class="no-border">Assign</th>
                </tr>
            </thead>
            <tbody class="no-border">
                <tr>
                    <td><b>Select All</b></td>
                    <td>
                        <?php if(isset($from) && $from=="edit"){?>
                        <input name="client_share_all" id="client_share_all"
                            <?php if(count($SavedStaff)==count($loadFirmStaff)){?> checked="checked" <?php } ?>
                            type="checkbox">
                        <?php }else{ ?>
                        <input name="client_share_all" id="client_share_all"  type="checkbox">
                        <?php } ?>
                    </td>

                </tr>
                <?php 
                    foreach($loadFirmStaff as $key=>$val){?>
                <tr class="sharing-user">
                    <td class=" no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
                        {{-- <div style="text-indent: 0px;">
                            <div id="communication-19798317"><i class="texting-off-icon"></i><i
                                    class="no-email-icon"></i></div>
                        </div><small class="client-portal-alert float-right" style="visibility: hidden;"><span
                                style="position: relative;" class="tooltip-wrapper"><span><i
                                        class="tooltip-alert"></i></span></span></small> --}}
                    </td>

                    <td>
                        <label class="mb-0">
                            <?php if(isset($from) && $from=="edit"){?>
                            <input name="linked_staff_checked_attend[]" <?php if(in_array($val->id,$SavedStaff)){ ?>
                                checked="checked" <?php } ?> value="{{$val->id}}" class="linked_staff"
                                id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">

                            <?php } else {  ?>
                            <input name="linked_staff_checked_attend[]" value="{{$val->id}}" class="linked_staff"
                                id="linked_staff_checked_attend_{{$val->id}}" <?php if($val->id==Auth::User()->id){ ?>
                                checked="checked" <?php } ?> <?php if($val->id == Auth::User()->id){ ?> defaultreminder="yes" <?php } ?>
                                type="checkbox">
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
        $(".task-fieldGroup").empty();
        $('#client_share_all').on('change', function () {
            $('.linked_staff').prop('checked', $(this).prop("checked"));
            if ($("input:checkbox#time_tracking_enabled").is(":checked")) {
                var SU = getCheckedUser();
                loadTimeEstimationUsersList(SU);
            }
            $(".linked_staff").each(function (i) {
                if($(this).val() == '{{auth::user()->id}}'){
                    $('.reminder_user_type').each(function (j) {
                        if($(this).val() == 'me'){
                            $(this).parents('.task-fieldGroup').remove();
                        }
                    });
                    if ($(this).prop('checked') == true) {
                        loadDefaultTaskReminder();
                    }
                }
            });

        });
        //deselect "checked all", if one of the listed checkbox product is unchecked amd select "checked all" if all of the listed checkbox product is checked
        $('.linked_staff').change(function () { //".checkbox" change 
            if ($('.linked_staff:checked').length == $('.linked_staff').length) {
                $('#client_share_all').prop('checked', true);
            } else {
                $('#client_share_all').prop('checked', false);
            }
            if($(this).val() == '{{auth::user()->id}}'){
                $('.reminder_user_type').each(function (j) {
                    if($(this).val() == 'me'){
                        $(this).parents('.task-fieldGroup').remove();
                    }
                });
                if ($(this).prop('checked') == true) {
                    loadDefaultTaskReminder();
                }
            }
        });


        $('.linked_staff').on('change', function () {
            $(this).prop('checked', $(this).prop("checked"));
            if ($(this).prop("checked") == true) {
                var SU = getCheckedUser();
            } else {
                var SU = getCheckedUser();
            }
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersList(SU);
            }
        });
        $("input:checkbox#time_tracking_enabled").click(function () {
            if ($(this).prop("checked") == true) {
                var SU = getCheckedUser();
            } else {
                var SU = getCheckedUser();
            }
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersList(SU);
            }
        });

        // check if login user is checked or not for showing default reminder
        <?php if(isset($from) && $from != "edit"){?>
        $('input[name="linked_staff_checked_attend[]"]:checked').each(function (i) {
            if($(this).attr("defaultreminder") == 'yes'){
                loadDefaultTaskReminder();
            }
        });
        <?php } ?>  

    });

    function getCheckedUser() {
        var array = [];
        $("input[class=linked_staff]:checked").each(function (i) {
            array.push($(this).val());
        });
        return array;
    }

    function loadTimeEstimationUsersList(SU) {
        var arrayList = [];

        $(".userwiseHours").each(function(){
            arrayList.push({'hour':$(this).val(),'id':$(this).attr('ownid')});
        });
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTimeEstimationUsersList",
            data: {
                "userList": JSON.stringify(SU),
                "edit":"{{$from}}",
                "task_id":"{{$task_id}}",
                "arrayList": JSON.stringify(arrayList)
            },
            success: function (res) {
                $("#dynamicUSerTimes").html(res);
            }
        })
    }

</script>
