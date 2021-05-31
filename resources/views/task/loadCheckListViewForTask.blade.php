<?php
$controllerLoad = new App\Http\Controllers\CommonController();
?>
<tbody>
            {{-- As discussed with Divyeshbhai, below tr tag commented --}}
                {{-- <tr class="border-bottom-0 task-checklist-background-incomplete <?php if($TaskData->status=="0"){?>  table-info <?php } else{  ?>table-success<?php } ?>">
                    <td class="align-middle border-bottom-0 border-right-0 text-center task-checkbox-column">
                        <div class="task-completed" data-task-completion-status="complete"></div>
                        <input type="checkbox" <?php if($TaskData->status=="1"){ echo "checked=checked";}?> name="complete_19667270" id="complete_19667270" onclick="markAsCompleteTask({{$TaskData->id}});" class="form-control cursor-pointer" style="width:20px;" value="1" >
                    </td>

                    <td class="border-bottom-0 border-left-0">
                    <?php if($TaskData->status=="1"){?>
                        <div id="task_details_complete" class="align-middle lead">Completed on {{date('M d,Y',strtotime($TaskCompletedBy['task_completed_date']))}} by <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($TaskCompletedBy['uid'])}}"> {{$TaskCompletedBy['completed_by_name']}} ({{$controllerLoad->getUserLevelText($TaskCompletedBy['user_type'])}})</a></div>
                    <?php }else{ ?>
                        <div id="task_details_complete"  class="align-middle lead">Mark As Complete</div>
                    <?php } ?>
                    </td>
                </tr> --}}
                <tr class="border-top-0 task-checklist-background-incomplete <?php if($TaskData->status=="0"){?>  table-info <?php } else{  ?>table-success<?php } ?>">
                    <td class="text-center border-top-0 border-right-0 task-checkbox-column float-left">
                    <?php $findComletedPErcent = ($TaskChecklistCompleted / count($TaskChecklist) * 100);?>

                        <span class="checklist-completion-percentage-details checklist-details-completion-percentage-19667270">{{number_format($findComletedPErcent)}}%</span>
                    </td>

                    <td class="border-top-0 border-left-0">
                        <div class="checklist-details-progress-bar-19667270 ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="ui-progressbar-value ui-widget-header ui-corner-left" style="width:{{$findComletedPErcent}}%;background-color:#5c9ccc;"></div></div>
                    </td>
                </tr>
    <?php if (!$TaskChecklist->isEmpty()) {?>
        <div class="mb-3">
            <ul class="list-group" id="checklistReloadArea">
                <?php foreach ($TaskChecklist as $ckkey => $ckval) {
                    if ($ckval->status == "1") {?>
                        <tr class="checklist-item-details">
                            <td class="text-center task-checkbox-column">
                                <input type="checkbox"  checked="checked" class="cursor-pointer form-control" style="width:20px;" name="complete_checklist_item_18768293" id="complete_checklist_item_18768293" value="1" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}},{{$task_id}});"  style="">
                            </td>
                            <td class="checklist-item-name-details">
                                <a href="javascript:void(0);" >
                                    {{$ckval->title}}
                                </a>
                                <div>
                                <small id="checklist_details_completed_by_18768388">Completed on {{date('M d,Y',strtotime($ckval->updated_at))}}  by <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($ckval->uid)}}">{{$ckval->first_name}} {{$ckval->last_name}} ({{$controllerLoad->getUserLevelText($ckval->user_type)}})</a></small>
                                </div>
                            </td>
                        </tr>
                    <?php } else {?>
                        <tr class="checklist-item-details">
                            <td class="text-center task-checkbox-column">
                                <input type="checkbox"  class="cursor-pointer form-control" style="width:20px;" name="complete_checklist_item_18768293" id="complete_checklist_item_18768293" value="1" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}},{{$task_id}});"  style="">
                            </td>
                            <td class="checklist-item-name-details">
                                <a href="javascript:void(0);" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}},{{$task_id}});" >
                                    {{$ckval->title}}
                                </a>
                                <div>
                                    <small id="checklist_details_completed_by_18768293"></small>
                                </div>
                            </td>
                        </tr>
                    <?php }?>
            <?php }?>
            </ul>
        </div>
    <?php }?>
</tbody>