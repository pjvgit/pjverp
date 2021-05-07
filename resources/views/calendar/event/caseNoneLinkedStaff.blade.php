<?php
foreach($loadFirmUser as $key=>$val){?>
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
            <input data-email-present="false" rowVal="{{$val->id}}" value="{{$val->id}}" name="share_checkbox_nonlinked[]" id="share_checkbox_nonlinked_{{$val->id}}" type="checkbox"
                class="client-login-not-enabled handler-attached share_checkbox_nonlinked"></label>
    </td>
    <td>
        <label class="mb-0"><input name="attend_checkbox_nonlinked[]" disabled="disabled" id="attend_checkbox_nonlinked_{{$val->id}}" type="checkbox"></label>
    </td>
</tr>
<?php } 

if($loadFirmUser->isEmpty()){ ?>
   <tr class="sharing-user">
    <td class=" no-border" colspan="3"> Non linked staff member not available</td></tr>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {

        $(".share_checkbox_nonlinked").click(function () {
            var id=$(this).attr('rowVal');
            $("#attend_checkbox_nonlinked_"+id).prop('disabled', !$(this).prop('checked'));
        });

        $("#client_attend_all").click(function () {
            if ($("#client_share_all").prop('checked')) {
                $(".client_attend_all_users").prop('checked', $(this).prop('checked'));
            }

        });

        $(".share_checkbox_nonlinked").click(function () {
            var id=$(this).attr('rowVal');
           
            if($(this).prop('checked')==false){
                $("#attend_checkbox_nonlinked_"+id).prop('checked', $(this).prop('checked'));

            }
        });

    });

</script>
