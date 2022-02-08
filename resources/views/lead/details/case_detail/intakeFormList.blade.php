<?php
$CommonController= new App\Http\Controllers\CommonController();

?>
<div class="row">
    <div class="col"></div>
    <div class="d-flex">
        <div class="float-right">
            <a href="#" target="_blank" class="mr-4"><i class="fas fa-question-circle" aria-hidden="true"></i> Learn
                about Intake Forms</a>

            <a data-toggle="modal" data-target="#addCaseNote" data-placement="bottom" href="javascript:;">
                <button class="btn btn-outline-secondary  btn-rounded m-1 px-3" type="button">Tell us what you
                    think</button>
            </a>

            <a data-toggle="modal" data-target="#addIntakeFormFromPC" data-placement="bottom" href="javascript:;">
                <button class="btn btn-primary btn-rounded m-1 px-3" type="button" onclick="addIntakeFormFromPC({{$user_id}});">Add
                    Intake Form</button>
            </a>
        </div>
    </div>
</div>
<?php
if($totalForm<=0){
?>
<br>
<div class="text-center">
    <i class="fas fa-edit fa-2x m-3"></i>
    <h5><strong>Send an Intake Form to a Lead on this Case</strong></h5>
    <p>Gather important case-related information by sending your lead&nbsp;a link to your Intake Form via email.<br><a
            href="/form_templates" target="_blank">Build a new Intake Form</a> in settings or <a href="#">add an
            existing form</a> to this case.</p>
    <a href="#" target="_blank">Learn more about Intake Forms</a>
</div>
<?php
}else{?>
<div class="table-responsive">
    <table class="display table table-striped table-bordered" id="intakeFormList" style="width:100%">
        <thead>
            <tr>
                <th width="60%">INTAKE FORM</th>
                <th width="15%">CREATED</th>
                <th width="15%">STATUS</th>
                <th width="10%"></th>
            </tr>
        </thead>

    </table>
</div>
<?php }
?>
