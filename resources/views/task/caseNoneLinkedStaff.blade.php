<thead>
    <tr class="no-border">
        <th class="sharing-list-header no-border w-75">Staff (Non-Linked)</th>
        <th class="no-border">Assign</th>
    </tr>
</thead>
<tbody class="no-border">
    <?php
  
foreach($loadFirmUser as $key=>$val){
  
    ?>
    <tr class="sharing-user">
        <td class=" no-border ">
            <span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
            <div style="text-indent: 0px;">
                <div id="communication-19798317"><i class="texting-off-icon"></i><i class="no-email-icon"></i></div>
            </div>
            <small class="client-portal-alert float-right" style="visibility: hidden;">
                <span style="position: relative;" class="tooltip-wrapper"><span>
                        <i class="tooltip-alert"></i>
                    </span>
                </span>
            </small>
        </td>
        <td>
            <label class="mb-0">
                <?php 
                if($from=="edit"){?>
                    <input name="share_checkbox_nonlinked[]" <?php if(in_array($val->id,$caseLinkeSavedAttending)){ ?>
                        checked="checked" <?php } ?> value="{{$val->id}}" class="linked_staff share_checkbox_nonlinked"
                        id="share_checkbox_nonlinked{{$val->id}}" type="checkbox">
    
                    <?php } else {  ?>
                <input data-email-present="false" rowVal="{{$val->id}}" name="share_checkbox_nonlinked[]"
                    id="share_checkbox_nonlinked_{{$val->id}}" <?php if($val->parent_user=="0"){ ?>
                        checked="checked" <?php } ?> value="{{$val->id}}" type="checkbox"
                    class=" client-login-not-enabled handler-attached linked_staff share_checkbox_nonlinked">
                    <?php } ?>
                </label>
        </td>

    </tr>
    <?php } 

if($loadFirmUser->isEmpty()){ ?>
    <tr class="sharing-user">
        <td class=" no-border" colspan="3"> Non linked staff member not available</td>
    </tr>
    <?php } ?>
</tbody>
<script type="text/javascript">
    $(document).ready(function () {

        $(".share_checkbox_nonlinked").click(function () {
            var id = $(this).attr('rowVal');
            $("#attend_checkbox_nonlinked_" + id).prop('disabled', !$(this).prop('checked'));
        });

        $("#client_attend_all").click(function () {
            if ($("#client_share_all").prop('checked')) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
            }

        });

        $(".share_checkbox_nonlinked").click(function () {
            var array = [];
            var id = $(this).attr('rowVal');

            if ($(this).prop('checked') == false) {
                $("#attend_checkbox_nonlinked_" + id).prop('checked', $(this).prop('checked'));

            }
            $('input:checkbox.linked_staff1').each(function () {
                var sThisVal = (this.checked ?   array.push($(this).val()) : "");
            });
           
            $('input:checkbox.share_checkbox_nonlinked').each(function () {
                var sThisVal12 = (this.checked ?   array.push($(this).val()) : "");
            });
          
            loadTimeEstimationUsersLinkedStaffList3(array);
        });
       
    });
    function loadTimeEstimationUsersLinkedStaffList3(SU) {
        var selectdValue = $("#case_or_lead option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTimeEstimationCaseWiseUsersList",
            data: {"userList" : JSON.stringify(SU),"case_id":selectdValue,"edit":"{{$from}}","task_id":"{{$task_id}}" },
            success: function (res) {
                $("#dynamicUSerTimes").html(res);
            }
        })
    }
</script>
