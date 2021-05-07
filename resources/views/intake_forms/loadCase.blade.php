<option value="">Select...</option>
<?php if($caseList!=''){?>
<?php foreach($caseList as $k=>$v){ ?>
    <option value="{{$v->id}}">{{$v->case_title}}</option>
<?php 
}
}?>
