<thead>
    <tr class="no-border">
        <th class="sharing-list-header no-border w-75">Staff</th>
        <th class="no-border">Assign</th>
    </tr>
</thead>
<tbody class="no-border">
    <tr>
        <td>Select All</td>
        <td>

            <?php if($from=="edit"){?>
            <input name="client_share_all" id="client_share_all"
                <?php if(count($caseLinkeSaved)==count($caseLinkedStaffList)){?> checked="checked" <?php } ?>
                type="checkbox">
            <?php }else{ ?>
            <input name="client_share_all" id="client_share_all" checked="checked" type="checkbox">
            <?php } ?>
        </td>
       
    </tr>
    <?php 
        foreach($caseLinkedStaffList as $key=>$val){?>
    <tr class="sharing-user">
        <td class=" no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
            <div style="text-indent: 0px;">
                <div id="communication-19798317"><i class="texting-off-icon"></i><i class="no-email-icon"></i></div>
            </div><small class="client-portal-alert float-right" style="visibility: hidden;"><span
                    style="position: relative;" class="tooltip-wrapper"><span><i
                            class="tooltip-alert"></i></span></span></small>
        </td>
       
        <td>
            <label class="mb-0">
                <?php 
            if($from=="edit"){?>
                <input name="linked_staff_checked_attend[]" <?php if(in_array($val->id,$caseLinkeSavedAttending)){ ?>
                    checked="checked" <?php } ?> value="{{$val->id}}" class="linked_staff1"
                    id="linked_staff_checked_attend_{{$val->id}}" type="checkbox">

                <?php } else {  ?>
                <input name="linked_staff_checked_attend[]" value="{{$val->id}}" class="linked_staff1"
                    id="linked_staff_checked_attend_{{$val->id}}" checked="checked" type="checkbox">
                <?php } ?>

            </label>
        </td>
    </tr>
    <?php } ?>
</tbody>
<script type="text/javascript">
    $(document).ready(function () {

       
        $('#client_share_all').on('change', function() {     
            $('.linked_staff1').prop('checked', $(this).prop("checked"));   
            var SU=getCheckedUser2();
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersLinkedStaffList2(SU);
            }
        });
        //deselect "checked all", if one of the listed checkbox product is unchecked amd select "checked all" if all of the listed checkbox product is checked
        $('.linked_staff1').change(function(){ //".checkbox" change 
            if($('.linked_staff1:checked').length == $('.linked_staff1').length){
                $('#client_share_all').prop('checked',true);
                var SU=getCheckedUser2();
            }else{
                $('#client_share_all').prop('checked',false);
                var SU=getCheckedUser2();
            }
            if($("input:checkbox#time_tracking_enabled").is(":checked")){
                loadTimeEstimationUsersLinkedStaffList2(SU);
            }
        });
    });
    function getCheckedUser2(){
        var array = [];
        $("input[class=linked_staff1]:checked").each(function(i){
            array.push($(this).val());
        });
        return array;
    }
    function loadTimeEstimationUsersLinkedStaffList2(SU) {
        var selectdValue = $("#case_or_lead option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTimeEstimationCaseWiseUsersList",
            data: {"userList" : JSON.stringify(SU),"case_id":selectdValue},
            success: function (res) {
                $("#dynamicUSerTimes").html(res);
            }
        })
    }
</script>
