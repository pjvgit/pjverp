@if (count($allEvents))
    @forelse ($allEvents as $key => $vv)
        @if(isset($oDate) && date('Y', strtotime($oDate)) != date('Y', strtotime($vv->start_date)))
        <tr>
            <th colspan="6">
                <h2 class="mb-2 mt-4 font-weight-bold text-dark">{{ date('Y',strtotime($vv->start_date)) }}</h2>
            </th>
        </tr>
        <tr>
            <th width="5%">Date</th>
            <th width="20%">Time</th>
            <th width="35%">Title</th>
            <th width="15%">Type</th>
            <th width="15%">Users</th>
            <th width="13%"></th>
        </tr>
        @endif
        <tr class="event-row false ">
            <td class="event-date-and-time  c-pointer" style="width: 50px;">
                @if(isset($oDate) && $vv->start_date==$oDate)
                @else
                    @php
                        $dateandMonth= date('d',strtotime($vv->start_date_time));
                        $dateOfEvent=date('M',strtotime($vv->start_date_time)); 
                        $oDate=$vv->start_date;
                    @endphp
                    <div class="d-flex">
                        <div style="width: 45px;">
                            <div
                                class="col-12 p-0 text-center text-white bg-dark font-weight-bold rounded-top">
                                <?php echo $dateOfEvent; ?></div>
                            <div class="col-12 p-0 text-center rounded-bottom"
                                style="background-color: rgb(237, 237, 235); color: rgb(70, 74, 76);">
                                <h4 class="py-1 m-0 font-weight-bold">
                                    {{$dateandMonth}}
                                </h4>
                            </div>
                        </div>
                    </div>
                @endif
            </td>
            <td class="border-left c-pointer">
                <div class="ml-2 mt-3">
                @php
                if($vv->start_date==NULL || $vv->end_time==NULL){
                    echo "All Day";
                }else{

                    // $start_time = date("H:i:s", strtotime(convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($vv->start_date.' '.$vv->start_time)),Auth::User()->user_timezone)));

                    // $end_time = date("H:i:s", strtotime(convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($vv->end_date.' '.$vv->end_time)),Auth::User()->user_timezone)));

                    echo date('h:i A',strtotime($vv->start_date_time));
                    echo "-";
                    echo date('h:i A',strtotime($vv->end_date_time));

                    // echo date('h:i A',strtotime($vv['start_time']));
                    // echo "-";
                    // echo date('h:i A',strtotime($vv['end_time']));
                }
                @endphp
                </div>
            </td>
            <td class="c-pointer">
                <div class="mt-3 event-name d-flex align-items-center">
                    <span><span class="">{{($vv->event_title)??'<No Title>'}}</span></span>
                        @if($vv->is_event_private=='yes')
                            <span class="text-danger"> &nbsp;[Private]</span>
                        @endif 
                </div>
            </td>
            <td class="c-pointer">
                @if(!empty($vv->eventType))
                <div class="d-flex align-items-center mt-3">
                    <div class="mr-1"
                        style="width: 15px; height: 15px; border-radius: 30%; background-color: {{ @$vv->eventType->color_code }}">
                    </div><span>{{ @$vv->eventType->title }}</span>
                </div>
                @else
                <i class="table-cell-placeholder mt-3"></i>
                @endif
            </td>
            <td class="event-users">
                    @if(!empty($vv->eventLinkedStaff))
                    @if(count($vv->eventLinkedStaff) > 1)
                        @php
                        $userListHtml="";
                        foreach($vv->eventLinkedStaff as $linkuserValue){
                            $userListHtml.="<span> <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i><a href=".url('contacts/attorneys/'.$linkuserValue->decode_id)."> ".substr($linkuserValue->first_name,0,15) . " ". substr($linkuserValue->last_name,0,15)."</a></span><br>";
                        }
                        @endphp
                        <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                        href="javascript:;" data-toggle="popover" data-trigger="focus" title=""
                        data-content="{{$userListHtml}}" data-html="true" data-original-title="Staff"
                        style="float:left;">{{count($vv->eventLinkedStaff)}} People</a>
                    @else
                        <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                        href="{{ route('contacts/attorneys/info', base64_encode(@$vv->eventLinkedStaff[0]->id)) }}">{{ @$vv->eventLinkedStaff[0]->full_name}}</a>
                    @endif
                @else
                    <i class="table-cell-placeholder mt-3"></i>
                @endif
            </td>
            <td class="event-users">
                <?php if($vv->is_event_private=='no'){?>
                <div class="mt-3 float-right">

                    <a class="align-items-center" data-toggle="modal" data-target="#loadReminderPopupIndex"
                    data-placement="bottom" href="javascript:;"
                    onclick="loadReminderPopupIndex({{$vv->id}});">
                    <i class="fas fa-bell pr-2 align-middle"></i>
                    </a>


                    <a class="align-items-center" data-toggle="modal" data-target="#loadCommentPopup"
                    data-placement="bottom" href="javascript:;"
                    onclick="loadEventComment({{$vv->id}});">
                    <i class="fas fa-comment pr-2 align-middle"></i>
                    @php
                        $commentCount = 0;
                        if(count($vv->eventLinkedStaff)) {
                            $lastReadAt = $vv->eventLinkedStaff()->wherePivot('user_id', auth()->id())->first();
                            if($lastReadAt)
                                $commentCount = $vv->eventComments->where("created_at", ">=", $lastReadAt->pivot->comment_read_at)->count();
                        }
                    @endphp
                    @if($commentCount)
                    <span class="badge badge-danger" style="right: 4px; top: -11px;">{{ $commentCount }}</span>
                    @endif
                    </a>
                    <?php 
                    if($vv->parent_evnt_id=="0"){
                        ?>
                    <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                    data-placement="bottom" href="javascript:;"
                    onclick="editSingleEventFunction({{$vv->id}});">
                    <i class="fas fa-pen pr-2  align-middle"></i> </a>
                    <?php }else{?>
                        <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                        data-placement="bottom" href="javascript:;"
                        onclick="editEventFunction({{$vv->id}});">
                        <i class="fas fa-pen pr-2  align-middle"></i> </a>
                    <?php } ?>
                
                    <?php 
                    if($vv->parent_evnt_id=="0"){
                        ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEvent"
                        data-placement="bottom" href="javascript:;"
                        onclick="deleteEventFunction({{$vv->id}},'single');">
                        <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php
                    }else{?>
                    <a class="align-items-center" data-toggle="modal" data-target="#deleteEvent"
                        data-placement="bottom" href="javascript:;"
                        onclick="deleteEventFunction({{$vv->id}},'multiple');">
                        <i class="fas fa-trash pr-2  align-middle"></i> </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </td>
        </tr>
    @empty
    @endforelse
    
    <tr><td colspan="6" style="text-align: center;">
        <input type="hidden" class="event-last-page" value="{{ $allEvents->lastPage() }}">
        {!! $allEvents->render() !!}
        <div class="loader-bubble loader-bubble-primary load-more-loader" style="display: none; margin-bottom: 30px;"></div>
    </td></tr>
@endif