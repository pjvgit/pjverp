<tr>
    <td>Select All</td>
    <td><input name="client-share-all" type="checkbox"></td>
    <td><input name="client-attend-all" type="checkbox"></td>
</tr>
<?php 
        foreach($caseCllientSelection as $key=>$val){?>
<tr class="sharing-user">
    <td class="d-flex  no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span>
        <a class="event-name d-flex align-items-center" tabindex="0" role="button" href="#" data-toggle="popover"
            title=""
            data-content="<?php if($val->mobile_number==''){?> <span> No cell phone number.
            </span><br><?php } ?> <?php if($val->email==''){?> No Email.</span> <br> <?php } ?> <a href='{{BASE_URL}}contacts/client/{{base64_encode($val->user_id)}}'>Edit Info</a>"
            data-html="true">
            <?php if($val->mobile_number==''){?> <i class="texting-off-icon"></i> <?php } ?>
            <?php if($val->email==''){?> <i class="no-email-icon"></i> <?php } ?>
        </a>
    </td>
    <td>
        <label class="mb-0">
            <?php 
            if($val->client_portal_enable=="0"){
            ?>
            <input data-email-present="false" name="clientCheckbox[]" id="cleintUSER_{{$val->id}}" onclick="loadGrantAccessModal({{$val->id}});"
               type="checkbox" class="lead_client_attend_all_users client-login-not-enabled handler-attached">
            <?php
            }else{
                ?>
            <input data-email-present="false" name="clientCheckbox[]" id="cleintUSER_{{$val->id}}" onclick="loadGrantAccessModal({{$val->id}});" type="checkbox"
                class="lead_client_attend_all_users client-login-not-enabled handler-attached">
            <?php
            }?>

        </label>
    </td>
    <td>
        <label class="mb-0">
            <input disabled="" name="attend-checkbox" type="checkbox">
        </label>
    </td>
    
</tr>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("[data-toggle=popover]").popover({
            html: true
        });
    });
</script>
