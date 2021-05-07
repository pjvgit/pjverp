<?php if(!$TaskChecklist->isEmpty()){?>
<div class="mb-3">
    <strong>Subtasks: </strong>
    <span>{{$TaskChecklistCompleted}} / {{count($TaskChecklist)}} completed</span>
    <?php  $findComletedPErcent=($TaskChecklistCompleted/count($TaskChecklist) * 100); ?>
    <div style="height: 10px;" class="my-2 progress">
        <div class="progress-bar" style="width:{{$findComletedPErcent}}%;" role="progressbar"
            aria-valuenow="{{$findComletedPErcent}}" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <ul class="list-group" id="checklistReloadArea">
        <?php
            foreach($TaskChecklist as $ckkey=>$ckval){
                if($ckval->status=="1"){?>
         <a href="javascript:void(0);" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}});" > <li class="c-pointer list-group-item" ><i
                class="fas fa-check fa-lg text-success mr-5"></i>
            {{$ckval->title}}
        </li>
         </a>
        <?php }else{?>
            <a href="javascript:void(0);" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}});">
                 <li class="c-pointer list-group-item" ><i
                class="fas fa-check fa-lg text-muted opacity-50 mr-5"></i> {{$ckval->title}}
        </li>
            </a>
            <?php } ?>
            <?php } ?>
    </ul>
</div>
<?php } ?>
