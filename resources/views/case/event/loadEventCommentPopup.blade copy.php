<div class="modal-header">
    <div class="header-left-group">
        <h5 class="mb-0">
            <?php 
            if($evetData->event_title!=''){
                echo $evetData->event_title;
            }else{
                ?> <span class="modal-title">&lt;no-title&gt;</span><?php
            }?>
        </h5>
        <h6 class="modal-subtitle mt-2 mb-0">{{date('D, M jS Y, h:ia',strtotime($evetData->start_date_time))}} —
            {{date('D, M jS Y, h:ia',strtotime($evetData->end_date_time))}}</h6>
    </div>
    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body" style="max-height: 500px;overflow: auto;">
    <div>
        <div class="container-fluid event-detail-modal-body ml-0">
            <div class="row row ">
                <div class="event-detail-column col-6">
                    <div class="event-column-container">
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Event Type</b></div>
                            <div class="detail-info  col-9">
                                <span class="event-type-badge badge badge-secondary" style="background-color: {{ @$evetData->eventType->color_code }}; font-size: 12px; height: 20px;">{{ @$evetData->eventType->title}}</span>
                            </div>
                        </div>
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Location</b></div>
                            <div class="detail-info event-location-section col-9">

                                {{-- <?php 
                                if(empty($eventLocation)){?>
                                <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                                <?php }else{ ?>
                                <?=$eventLocation->location_name?><br>
                                <?=$eventLocation->address1?><br>
                                <?=$eventLocation->address2?><br>
                                <?=$eventLocation->city?> &nbsp;
                                <?=$eventLocation->state?>&nbsp;
                                <?=$eventLocation->postal_code?><br>
                                <?=$eventLocation->name?>
                                <?php } ?> --}}
                                @if($evetData->event_location_id)
                                    {{ $evetData->eventLocation->full_address }}
                                @else
                                    <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2 row ">
                            <div class="col-3"><b>Repeats</b></div>
                            <?php if(isset($evetData) && $evetData->event_frequency!=NULL){?>
                            <?php if($evetData->event_frequency=='DAILY'){?>
                            <div class="detail-info recurring-rule-text col-9">Daily</div>
                            <?php }else if($evetData->event_frequency=='EVERY_BUSINESS_DAY'){?>
                            <div class="detail-info recurring-rule-text col-9">Weekly on Weekdays</div>
                            <?php }else if($evetData->event_frequency=='CUSTOM'){ ?>
                            <div class="detail-info recurring-rule-text col-9">Weekly on
                                {{date("l",strtotime($evetData->start_date))}}</div>
                            <?php }else if($evetData->event_frequency=='WEEKLY'){ ?>
                            <div class="detail-info recurring-rule-text col-9">Weekly</div>
                            <?php }else if($evetData->event_frequency=='MONTHLY'){ ?>
                            <div class="detail-info recurring-rule-text col-9">Monthly</div>
                            <?php }else if($evetData->event_frequency=='YEARLY'){ ?>
                            <div class="detail-info recurring-rule-text col-9">Yearly</div>
                            <?php } ?>
                            <?php }else { ?><div class="detail-info recurring-rule-text col-9">Never</div><?php } ?>
                        </div>
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Case</b></div>
                            <div class="detail-info  col-9">
                                <?php 
                                if(!empty($CaseMasterData)){?>
                                <a
                                    href="{{ route('info', $CaseMasterData->case_unique_number) }}">{{$CaseMasterData->case_title}}</a>
                                <?php } else  { ?>
                                Not specified
                                <?php } ?>

                            </div>

                        </div>
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Lead</b></div>
                            <div class="detail-info  col-9">
                                <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                            </div>
                        </div>
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Description</b></div>
                            <div class="detail-info  col-9">
                                <?php 
                                if($evetData->event_description!=''){?>
                                <p class="d-inline" style="opacity: 0.7;">{{$evetData->event_description}}</p>
                                <?php }else{?>
                                <p class="d-inline" style="opacity: 0.7;">Not specified</p>

                                <?php } ?>
                            </div>
                        </div>
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Reminders</b></div>
                            <div class="detail-info  col-9">
                                <div>
                                    <ul id="reminder_list" class="list-unstyled">
                                       
                                    </ul>

                                    <a class="align-items-center" data-toggle="modal" data-target="#loadReminderPopup"
                                        data-placement="bottom" onclick="loadReminderPopup({{$evetData->id}})" href="javascript:;">
                                        Edit
                                        Reminders</a>
                                  
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div>
                            <div class="event-sharing-list">
                                <div class="mb-2"><b>Shared / Attending</b></div>
                                <div>
                                    <div class="mb-2 sharing-user">
                                        <div class="row ">
                                            @if(count($evetData->eventLinkedStaff))
                                                @forelse ($evetData->eventLinkedStaff as $key => $item)
                                                <div class="col-8">
                                                    <div class="d-flex flex-row">
                                                        <a href="{{ route('contacts/attorneys/info', base64_encode($item->id)) }}" class="d-flex align-items-center user-link" title="{{ userTypeList()[$item->user_type] }}">
                                                            {{substr($item->full_name,0,15)}} ({{userTypeList()[$item->user_type]}})</a>
                                                    </div>
                                                </div>
                                                <div class="col-4"><b style="color: rgb(99, 108, 114);"><?php if($item->pivot->attending=='yes'){ echo "Attending"; } ?></b></div>
                                                @empty
                                                @endforelse
                                            @endif
                                        </div>
                                        <div class="row ">
                                            @if(count($evetData->eventLinkedContact))
                                                @forelse ($evetData->eventLinkedContact as $key => $item)
                                                <div class="col-8">
                                                    <div class="d-flex flex-row">
                                                        <a href="{{ route('contacts/clients/view', $item->id) }}" class="d-flex align-items-center user-link">
                                                            {{substr($item->full_name,0,15)}} ({{ userLevelList()[$item->user_level] }})</a>
                                                    </div>
                                                </div>
                                                <div class="col-4"><b style="color: rgb(99, 108, 114);"><?php if($item->pivot->attending=='yes'){ echo "Attending"; } ?></b></div>
                                                @empty
                                                @endforelse
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="event-detail-history col-6">
                    <div>
                        <div>
                            <div id="editorArea" class="mt-3 mb-3"  style="display: none;">
                                <form class="addComment" id="addComment" name="addComment" method="POST">
                                    @csrf
                                    <input class="form-control" id="id" value="{{ $evetData->id}}" name="event_id" type="hidden">
                                    <div id="editor">
                                   
                                    </div>
                                    <div class="row ">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary mt-3 mb-3  float-right">Post Comment</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @can('commenting_add_edit')
                            <div class="mt-3 mb-3" id="linkArea" >
                                <a id="addcomment"  onclick="toggelComment()" href="#">Add a comment...</a>
                            </div>
                            @else
                            <div class="mt-3 mb-3"><p id="cannot-post-comment">You cannot post comments on this item.</p></div>
                            @endcan
                            <hr>
                            <div class="detail-label">History</div>
                            <div class="history-contents container-fluid" id="commentHistory">

                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
<div class="form-group row">
        <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"></div>
        </div>
    </div>
    <div class="action-buttons">
        <div>
            @can('delete_items')
            <?php if($evetData->parent_evnt_id=="0"){ ?>
                    <a class="align-items-center" data-toggle="modal" data-target="#deleteFromCommentBox"
                        data-placement="bottom" href="javascript:;"
                        onclick="deleteEventFromCommentFunction({{$evetData->id}},'single');">
                        <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button> 
                    </a>
            <?php }else{ ?>
                    <a class="align-items-center" data-toggle="modal" data-target="#deleteFromCommentBox"
                    data-placement="bottom" href="javascript:;"
                    onclick="deleteEventFromCommentFunction({{$evetData->id}},'multiple');">
                    <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button>
                    </a>
            <?php } ?>
            @endcan
            <?php if(!empty($CaseMasterData)){?>
                <a data-toggle="modal" data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;">
                    <button class="btn  btn-outline-primary m-1" type="button" id="button" onclick="loadTimeEntryPopupByCaseWithoutRefresh('{{$CaseMasterData->id}}');">
                    Add Time Entry
                    </button>
                </a>
                <?php } ?>
            @can('event_add_edit')
            <?php if($evetData->parent_evnt_id=="0"){ ?>
                <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                data-placement="bottom" href="javascript:;"
                onclick="editSingleEventFunction({{$evetData->id}});">
                <button type="button" class="btn btn-primary  pendo-exp2-add-event m-1 btn btn-cta-primary">Edit</button> </a>
            <?php }else{?>
                    <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                    data-placement="bottom" href="javascript:;"
                    onclick="editEventFunction({{$evetData->id}});">
                    <button type="button" class="btn btn-primary  pendo-exp2-add-event m-1 btn btn-cta-primary">Edit</button> </a>
            <?php } ?>
            @endcan
        </div>
    </div>
</div>


<style>
body > 
#editor {
      margin: 50px auto;
      max-width: 720px;
    }
    #editor {
      height: 200px;
      background-color: white;
    }
</style>


<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'], // toggled buttons
            ['blockquote', 'code-block'],
            [{
                'header': 1
            }, {
                'header': 2
            }], // custom button values
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }],
            
            [{
                'size': ['small', false, 'large', 'huge']
            }], // custom dropdown
            [{
                'header': [1, 2, 3, 4, 5, 6, false]
            }],

            [{
                'color': []
            }, {
                'background': []
            }], // dropdown with defaults from theme
            [{
                'font': []
            }],
            [{
                'align': []
            }],

            ['clean'] // remove formatting button
        ];

        var quill = new Quill('#editor', {
            modules: {
                toolbar: toolbarOptions
            },
            theme: 'snow'
        });

        afterLoader();

        $('#addComment').submit(function (e) {
            beforeLoader();

            e.preventDefault();
            var delta =quill.root.innerHTML;
            if(delta=='<p><br></p>'){
                toastr.error('Unable to post a blank comment', "", {
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                })
                afterLoader();

            }else{
                var dataString = $("#addComment").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/court_cases/saveEventComment", // json datasource
                    data: dataString + '&delta=' + delta,
                    success: function (res) {
                        $(this).find(":submit").prop("disabled", true);
                        $("#innerLoader").css('display', 'block');
                        if (res.errors != '') {
                            afterLoader();

                            return false;
                        } else {
                            toastr.success('Your comment was posted', "", {
                                positionClass: "toast-top-full-width",
                                containerId: "toast-top-full-width"
                            });
                            loadCommentHistory({{$evetData->id}});
                            quill.root.innerHTML='';
                            afterLoader();

                        }
                    }
                });
            }
        });
    });
    loadCommentHistory({{$evetData->id}});
    function loadCommentHistory(event_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCommentHistory",
            data: {
                "event_id": event_id
            },
            success: function (res) {
                $("#commentHistory").html(res);
            }
        })
    }
    loadReminderHistory({{$evetData->id}});
    function loadReminderHistory(event_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadReminderHistory",
            data: {
                "event_id": event_id
            },
            success: function (res) {
                $("#reminder_list").html(res);
            }
        })
    }
    function toggelComment(){
        $("#linkArea").hide();
        $("#editorArea").show();
    }
    function deleteEventFromCommentFunction(id,types) {
  
      if(types=='single'){
            $("#deleteSingleEvent").text('Delete Event');
      }else{
            $("#deleteSingleEvent").text('Delete Recurring Event');
      }
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/deleteEventFromCommentPopup", 
                data: {
                    "event_id": id
                },
                success: function (res) {
                    $("#deleteFromComment").html('');
                    $("#deleteFromComment").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

</script>
