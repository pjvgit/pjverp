<div class="modal-header">
    <div class="header-left-group">
        <a href="{{ route('events/') }}">Back to Calendar</a>
        <h5 class="mb-0">
            <span class="modal-title">{{ ($evetData->event_title) ? $evetData->event_title : "&lt;no-title&gt" }}</span>
        </h5>
        <h6 class="modal-subtitle mt-2 mb-0">{{date('D, M jS Y, h:ia',strtotime($evetData->start_date_time))}} —
            {{date('D, M jS Y, h:ia',strtotime($evetData->end_date_time))}}</h6>
    </div>
    <div class="action-buttons">
        <div>
            @can(['event_add_edit','delete_items'])
            <?php 
                if($evetData->parent_evnt_id=="0"){
                    ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteFromCommentBox"
                        data-placement="bottom" href="javascript:;"
                        onclick="deleteEventFromCommentFunction({{$evetData->id}},'single');">
                        <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button> </a>
                    <?php
                }else{?>
                <a class="align-items-center" data-toggle="modal" data-target="#deleteFromCommentBox"
                    data-placement="bottom" href="javascript:;"
                    onclick="deleteEventFromCommentFunction({{$evetData->id}},'multiple');">
                    <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button></a>
                <?php } ?>
            @endcan
            @can('event_add_edit')
                <?php 
                if($evetData->parent_evnt_id=="0"){
                    ?>
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
<div class="modal-body" style="max-height: 500px;overflow: auto;">
    <div>
        <div class="container-fluid event-detail-modal-body ml-0">
            <div class="row row ">
                <input type="hidden" value="{{ $evetData->id }}" id="event_id">
                <div class="event-detail-column col-6">
                    <div class="event-column-container">
                        <div class="mb-2 row ">
                            <div class="col-3"><b>Location</b></div>
                            <div class="detail-info event-location-section col-9">                                 
                                @if(empty($evetData->eventLocation))
                                <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                                @else
                                {{ @$evetData->eventLocation->location_name }}<br>
                                {{ @$evetData->eventLocation->address1 }}<br>
                                {{ @$evetData->eventLocation->address2 }}<br>
                                {{ @$evetData->eventLocation->city }} &nbsp;
                                {{ @$evetData->eventLocation->state }}&nbsp;
                                {{ @$evetData->eventLocation->postal_code }}<br>
                                {{ @$evetData->eventLocation->name }}
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
                                @if(!empty($evetData->case))
                                <a
                                    href="{{ route('info', $evetData->case->case_unique_number) }}">{{ $evetData->case->case_title }}</a>
                                @else
                                Not specified
                                @endif
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
                                            @php    
                                            $userTypes = unserialize(USER_TYPE);
                                            @endphp                                          
                                                @if(!empty($evetData->eventLinkedStaff))
                                                    @foreach($evetData->eventLinkedStaff as $kstaff=>$vstaff)
                                                        <div class="col-8">
                                                            <div class="d-flex flex-row">
                                                                <a href="{{ route('contacts/attorneys/info', base64_encode($vstaff->id)) }}"
                                                                    class="d-flex align-items-center user-link"
                                                                    title="{{$userTypes[$vstaff->user_type]}}">{{substr($vstaff->first_name,0,15)}}
                                                                    {{substr($vstaff->last_name,0,15)}}
                                                                    ({{$userTypes[$vstaff->user_type]}})</a>
                                                            </div>
                                                        </div>
                                                        <div class="col-4"><b
                                                                style="color: rgb(99, 108, 114);"><?php if($vstaff->attending=='yes'){ echo "Attending"; } ?></b>
                                                        </div>
                                                    @endforeach
                                                @endif
                                        </div>
                                        <div class="row ">
                                            <?php                                              
                                                if(!$CaseEventLinkedContactLead->isEmpty()){
                                                    foreach($CaseEventLinkedContactLead as $kstaff=>$vstaff){?>
                                                        <div class="col-8">
                                                            <div class="d-flex flex-row">
                                                                <a href="{{ route('contacts/clients/view', $vstaff->contact_id) }}"
                                                                    class="d-flex align-items-center user-link"
                                                                    title="{{@$userTypes[$vstaff->user_type]}}">{{substr($vstaff->first_name,0,15)}}
                                                                    {{substr($vstaff->last_name,0,15)}}
                                                                    (Client)</a>
                                                            </div>
                                                        </div>
                                                        <div class="col-4"><b
                                                                style="color: rgb(99, 108, 114);"><?php if($vstaff->attending=='yes'){ echo "Attending"; } ?></b>
                                                        </div>
                                                <?php } 
                                                }?>
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
                                            <button type="submit" class="submit btn btn-primary mt-3 mb-3  float-right">Post Comment</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="mt-3 mb-3" id="linkArea" >
                                <a id="addcomment"  onclick="toggelComment()" href="#">Add a comment...</a>
                            </div>
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
