<?php if(count($selectdUSerList)>0){?>
<div>
    <p class="font-weight-bold mt-2">Contacts added to this case</p>
    <div class="added-contacts-table">
        <?php
        foreach($selectdUSerList as $k=>$v){
       ?>
        <div class="added-contact-row align-items-center mx-0  row bordergray">
            <div class="test-contact-name col-3">{{$v->first_name}} {{$v->last_name}}</div>
            <div class="test-contact-group col-2"><?php
            if($v->user_level==2){
                echo "Client";

            }else{
                echo "Company";
            }
            ?>
            </div>
            <div class="col-2 offset-5"><button type="button" onclick="removeUser({{$v->id}})"
                    class="btn btn-link">Remove</button></div>
        </div>
        <?php
        }
        ?>
    </div>
    
</div>
<?php } else{ ?>
    <div class="m-2 empty-state text-center text-center-also">
        <p class="font-weight-bold">Start creating your case by adding a new or existing contact.</p>
        <div>All cases need at least one client to bill.</div><a href="#" rel="noopener noreferrer" target="_blank">Learn more about adding a case and a contact at the same time.</a>
    </div>
<?php } ?>