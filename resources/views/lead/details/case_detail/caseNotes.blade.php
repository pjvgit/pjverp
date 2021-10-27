<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<div class="lead-info-tab">
    <div class="row ">
        <div class="col">

        </div>
        <div class="col">
            <div class="float-right">
                <a data-toggle="modal" data-target="#addCaseNote" data-placement="bottom" href="javascript:;">
                    <button class="btn btn-primary btn-rounded m-1 px-5" type="button"
                        onclick="addCaseNoteFunction({{$user_id}});">Add a Note</button>
                </a>
            </div>
        </div>
    </div>
    <div class="row ">

    </div>
    <?php
     if(!$CaseNotesData->isEmpty()){?>
    <div class="m-3 notes-container">
        <?php foreach($CaseNotesData as $k=>$v){?>
        <div class="note-row pt-3 pr-2 pb-3 pl-2  ">
            <div class="note-created-by col-3">
                <div class="d-flex flex-row"><img class="rounded-circle mr-2 small-circle-picture"
                        src="{{BASE_URL}}svg/default_avatar_32.svg">
                    <div>
                        <div class="d-flex flex-row"><a
                                href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}"
                                class="d-flex align-items-center user-link"
                                title="{{$v->user_title}}">{{$v->createdByName}}</a></div>
                        <p id="user-link-level">{{$v->user_title}}</p>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div>
                    <h6 class="note-subject"><b>{{$v->note_subject}}</b></h6>
                    <p class="note-note"><span>{{$v->notes}}<br></span></p>
                    <div><span class="note-minor-text note-activity">Activity: {{$v->acrtivity_title}}</span><br></div>
                    <?php 
                        $OwnDate=$CommonController->convertUTCToUserTime($v->lead_notes_created_at,Auth::User()->user_timezone);
                        ?>
                    <span class="note-minor-text">Last updated at {{date('F jS Y, h:i:s a',strtotime($OwnDate))}}</span>
                </div>
            </div>
            <div class="note-date col-2">
                <p> {{ $v->note_date }} </p>
            </div>
            <div class="col-1">
                <div>
                    <a data-toggle="modal" data-target="#editCaseNote" data-placement="bottom" href="javascript:;"
                        onclick="editCaseNoteFunction({{$v->lead_notes_id}});">
                        <i class="fas fa-pen align-middle"></i>
                    </a>
                    <a data-toggle="modal" data-target="#deleteCaseNote" data-placement="bottom" href="javascript:;"
                        onclick="deleteCaseNoteFunction({{$v->lead_notes_id}});">
                        <i class="fas fa-fw fa-trash ml-1"></i>
                    </a>
                </div>
            </div>
        </div><?php
            }
            ?>
    </div>
    <?php }else{ ?>
    <div class="m-3 notes-container text-center p-2">No notes available
    </div>
    <?php } ?>
</div>
