<option value="">Select case</option>
<?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
<option  <?php if($case_id==$Caseval->id){ echo "selected=selected"; }?> value="{{$Caseval->id}}">{{$Caseval->case_title}}
    <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?></option>
<?php } ?>