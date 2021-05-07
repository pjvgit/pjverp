<?php
if(!$caseCllientSelection->isEmpty()){
    ?>
    <thead>
    <tr class="no-border">
        <th class="border-top-none sharing-list-header no-border w-75">Contacts &amp; Leads</th>
        <th class="border-top-none no-border">
          
                <a class="" tabindex="0" role="button" href="#"
                data-toggle="popover"  title="Assigning to Clients" 
                data-content='<div class="popover-inner" role="tooltip"><h3 class="popover-header"></h3><div class="popover-body">When you add clients and contacts to a task, they will receive an email with a link to view it in their portal. <a href="#" rel="noopener noreferrer" target="_blank"><u>What will my client see?</u></a></div></div>' data-html="true">Assign <i id="help-bubble-6" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary"></i
            </a>
       
        </th>
    </tr>
</thead>
<tbody class="no-border">
    <tr>
        <td>Select All</td>
        <td><input name="client_attend_all" id="client_attend_all" type="checkbox"></td>
    </tr>
    <?php 
        foreach($caseCllientSelection as $key=>$val){?>
    <tr class="sharing-user">
        <td class="d-flex  no-border "><span class="mr-2">{{$val->first_name}} {{$val->last_name}}</span></td>
        <td>
            <label class="mb-0">
                <input data-email-present="false" class="assign_client_lead" value="{{$val->id}}"
                    name="assign_client_lead[]" type="checkbox" class="client-login-not-enabled handler-attached">
            </label>
        </td>
    </tr>
</tbody>
<?php }
} ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("[data-toggle=popover]").popover({html:true});

        $("#client_attend_all").click(function () {
            $(".assign_client_lead").prop('checked', $(this).prop('checked'));
        });
        //deselect "checked all", if one of the listed checkbox product is unchecked amd select "checked all" if all of the listed checkbox product is checked
        $('.assign_client_lead').change(function () { //".checkbox" change 
            if ($('.assign_client_lead:checked').length == $('.assign_client_lead').length) {
                $('#client_attend_all').prop('checked', true);
            } else {
                $('#client_attend_all').prop('checked', false);
            }
        });
    });

</script>
