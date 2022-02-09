<?php 
$array=json_decode($alreadySelected);
if($typpe=="contact_field"){?>
<optgroup label="Standard Fields">
    <?php if(!in_array("name",$array)){?>
    <option value="name">Name</option>
    <?php } ?>
    <?php if(!in_array("email",$array)){?>
    <option value="email">Email</option>
    <?php } ?>
    <?php if(!in_array("work_phone",$array)){?>
    <option value="work_phone">Work Phone</option>
    <?php } ?>
    <?php if(!in_array("home_phone",$array)){?>
    <option value="home_phone">Home Phone</option>
    <?php } ?>
    <?php if(!in_array("cell_phone",$array)){?>
    <option value="cell_phone">Cell Phone</option>
    <?php } ?>
    <?php if(!in_array("birthday",$array)){?>
    <option value="birthday">Birthday</option>
    <?php } ?>
    <?php if(!in_array("driver_license",$array)){?>
    <option value="driver_license">Driver license</option>
    <?php } ?>
    <?php if(!in_array("address",$array)){?>
    <option value="address">Address</option>
    <?php } ?>

</optgroup>
<optgroup label="Custom Fields">
    <option value="custom_field" disabled> + <a href="">Create a custom field</a></option>
</optgroup>
<?php } ?>

<?php 
if($typpe=="unmapped_field"){?>
<option value="">Select...</option>
<option value="short_text">Short Text</option>
<option value="long_text">Long Text</option>
<option value="yesno">Yes/No</option>
<option value="number">Number</option>
<option value="currency">Currency</option>
<option value="date">Date</option>
<option value="multiple_choice">Multiple Choice</option>
<option value="checkboxes">Checkboxes</option>
<?php } ?>
