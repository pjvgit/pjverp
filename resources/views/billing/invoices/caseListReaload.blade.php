<option value=""></option>
<option value="none">None</option>

<?php foreach($caseListByClient as $key=>$val){ ?>
<option value="{{$val->case_id}}">{{substr($val->case_title,0,200)}}</option>
<?php } ?>
