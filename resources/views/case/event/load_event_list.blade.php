@forelse ($allEvents as $key => $item)
    @if(isset($oDate) && date('Y', strtotime($oDate)) != date('Y', strtotime($item->start_date)))
    <tr>
        <th colspan="6">
            <h2 class="mb-2 mt-4 font-weight-bold text-dark">{{ date('Y',strtotime($item->start_date)) }}</h2>
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
    @php
        $eventLinkedStaff = encodeDecodeJson($item->event_linked_staff);
        $isAuthUserLinked = $eventLinkedStaff->where('user_id', $authUser->id)->first();
    @endphp
    <tr class="{{ ($item->is_read == 'no' && $isAuthUserLinked) ? 'font-weight-bold' : '' }}">
        <td class="event-date-and-time  c-pointer" style="width: 50px;">
            @if(isset($oDate) && $item->start_date==$oDate)
            @else
                @php
                    $dateandMonth= date('d',strtotime($item->user_start_date));
                    $dateOfEvent=date('M',strtotime($item->user_start_date)); 
                    $oDate=$item->start_date;
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
        <td>
            <div class="ml-2 mt-3">
            @php
            if($item->event->start_time==NULL || $item->event->end_time==NULL && $item->event->is_full_day == "yes"){
                echo "All Day";
            }else{                        
                echo $item->event->user_start_time;
                echo " - ";
                echo (strtotime($item->start_date) != strtotime($item->end_date)) ? $item->user_end_date->format("M d").", " : "";
                echo $item->event->user_end_time;
            }
            @endphp
            </div>
        </td>
        <td>
            @if($item->event->is_SOL == 'yes')
                @if($item->event->case && $item->event->case->sol_satisfied == "yes")
                <span class="mr-2 badge badge-success">SOL</span>
                @else 
                <span class="mr-2 badge badge-danger">SOL</span>
                @endif
                {{ $item->event->event_title ?? "<no title>" }}
            @else
                @if($item->event->is_event_private=='yes' && !$isAuthUserLinked)
                    Private Event
                @else
                    {{ $item->event->event_title ?? "<no title>" }} @if($item->event->is_event_private == 'yes') <span class="agenda-shared-private-event"> [Private]</span> @endif
                @endif
            @endif
        </td>
        <td class="c-pointer">
            @if(!empty($item->event->eventType) && ($isAuthUserLinked || $authUser->parent_user == 0 || auth()->user()->hasPermissionTo('access_all_cases')))
            <div class="d-flex align-items-center mt-3">
                <div class="mr-1"
                    style="width: 15px; height: 15px; border-radius: 30%; background-color: {{ @$item->event->eventType->color_code }}">
                </div><span>{{ @$item->event->eventType->title }}</span>
            </div>
            @else
            <i class="table-cell-placeholder mt-3"></i>
            @endif
        </td>
        <td class="event-users">
            @if(!empty($item->event_linked_staff) && ($isAuthUserLinked || $authUser->parent_user == 0 || auth()->user()->hasPermissionTo('access_all_cases')))
                @php    
                    $linkedUser = [];
                    $linkedStaff = encodeDecodeJson($item->event_linked_staff);
                    if(count($linkedStaff)) {
                        foreach($linkedStaff as $skey => $sitem) {
                            $user = getUserDetail($sitem->user_id);
                            $linkedUser[] = [
                                'user_id' => $sitem->user_id,
                                'full_name' => $user->full_name,
                                'user_type' => $user->user_type_text,
                                'attending' => $sitem->attending,
                                'utype' => 'staff',
                                'user_level' => $user->user_level,
                            ];
                        }
                    }
                    $linkedClient = [];
                    $linkedContact = encodeDecodeJson($item->event_linked_contact_lead);
                    if(count($linkedContact)) {
                        foreach($linkedContact as $ckey => $citem) {
                            $user = getUserDetail(($citem->user_type == 'lead') ? $citem->lead_id : $citem->contact_id);
                            $linkedClient[] = [
                                'user_id' => ($citem->user_type == 'lead') ? $citem->lead_id : $citem->contact_id,
                                'full_name' => $user->full_name,
                                'user_type' => $user->user_type_text,
                                'attending' => $citem->attending,
                                'utype' => $citem->user_type,
                                'user_level' => $user->user_level,
                            ];
                        }
                    }
                    $totalUser = count($linkedUser) + count($linkedClient);
                    if($totalUser > 1) {
                        $userListHtml = "<table><tbody>";
                        if(count($linkedUser)) {
                            $userListHtml.="<tr><td colspan='2'><b>Staff</b></td></tr>";
                            foreach($linkedUser as $linkuserValue){
                                $userListHtml.="<tr><td>
                                <span> 
                                    <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                                    <a href=".route('contacts/attorneys/info', base64_encode($linkuserValue['user_id']))."> ".$linkuserValue['full_name']."</a>
                                </span>
                                </td>";
                                if($linkuserValue['attending'] == "yes") {
                                    $userListHtml .= "<td>Attending</td></tr>";
                                } else {
                                    $userListHtml .= "<td></td></tr>";
                                }
                            }
                        }
                        if(count($linkedClient)) {
                            $userListHtml.="<tr><td colspan='2'><b>Contacts/Leads</b></td></tr>";
                            foreach($linkedClient as $linkuserValue){
                                $userListHtml.="<tr><td>
                                <span> 
                                    <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                                    <a href=".(($linkuserValue['user_level'] == 4) ? route('contacts/companies/view', $linkuserValue['user_id']) : route('contacts/clients/view', $linkuserValue['user_id']))."> ".$linkuserValue['full_name']."</a>
                                </span>
                                </td>";
                                if($linkuserValue['attending'] == "yes") {
                                    $userListHtml .= "<td>Attending</td></tr>";
                                } else {
                                    $userListHtml .= "<td></td></tr>";
                                }
                            }
                        }
                        $userListHtml .= "</tbody></table>";
                    }
                @endphp
                @if($totalUser > 1)
                    <a class="mt-3 event-name d-flex align-items-center pop" tabindex="0" role="button"
                    href="javascript:;" data-toggle="popover" title="" data-content="{{$userListHtml}}" data-html="true"
                    style="float:left;">{{ $totalUser ?? 0 }} People</a>
                @else
                    <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                    href="{{ route('contacts/attorneys/info', base64_encode(@$linkedUser[0]['user_id'])) }}">{{ @$linkedUser[0]['full_name']}}</a>
                @endif
            @else
                <i class="table-cell-placeholder mt-3"></i>
            @endif
        </td>
        @if($item->event->is_SOL=='yes')
        <td class="event-users">
            <a class="align-items-center" data-toggle="modal" data-target="#addCaseReminderPopup" 
            data-placement="bottom" href="javascript:;" 
            onclick="addCaseReminder({{$item->event->case_id}});"> 
            <i class="fas fa-bell pr-2 align-middle"></i>
            </a>
        </td>
        @else
        <td class="event-users">
            @if($item->event->is_event_private=='yes' && !$isAuthUserLinked)
            @else
            <div class="mt-3 float-right">
                @if($isAuthUserLinked || $authUser->parent_user == 0 || auth()->user()->hasPermissionTo('access_all_cases'))
                    @if($isAuthUserLinked || $authUser->parent_user == 0)
                        <a class="align-items-center" data-toggle="modal" data-target="#loadEventReminderPopup"
                            data-placement="bottom" href="javascript:;"
                            onclick="loadEventReminderPopup({{$item->event_id}}, {{$item->id}});">
                            <i class="fas fa-bell pr-2 align-middle"></i>
                        </a>
                    @endif
                    @canany(['commenting_add_edit', 'commenting_view'])
                        <a class="align-items-center" data-toggle="modal" data-target="#loadCommentPopup"
                            data-placement="bottom" href="javascript:;"
                            onclick="loadEventComment({{$item->event_id}}, {{$item->id}});">
                            @php
                                $commentCount = 0;
                                if(count($eventLinkedStaff)) {
                                    $lastReadAt = $eventLinkedStaff->where('user_id', $authUser->id)->first();
                                    $comments = encodeDecodeJson($item->event_comments);
                                    if($lastReadAt)
                                        $commentCount = $comments->where("action_type", "0")->where("created_at", ">=", $lastReadAt->comment_read_at)->count();
                                }
                            @endphp
                            <i class="fas fa-comment-alt @if(!$commentCount) pr-2 @endif"></i>
                            @if($commentCount)
                            <span class="badge badge-danger comment-count comment-count-{{ $item->id }}">{{ $commentCount }}</span>
                            @endif
                        </a>
                    @endcanany
                    @can('event_add_edit')
                        @if(Route::currentRouteName() == 'calendars')
                            @can('case_add_edit')
                                <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                                    data-placement="bottom" href="javascript:;"
                                    onclick="editEventFunction({{$item->event_id}}, {{$item->id}});">
                                    <i class="fas fa-pen pr-2  align-middle"></i> 
                                </a>
                            @endcan
                        @else
                            @canany(['lead_add_edit', 'lead_view'])
                                <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                                    data-placement="bottom" href="javascript:;"
                                    onclick="editEventFunction({{$item->event_id}}, {{$item->id}});">
                                    <i class="fas fa-pen pr-2  align-middle"></i> 
                                </a>
                            @endcanany
                        @endif
                    @endcan
                    @can(['event_add_edit','delete_items'])
                        <?php 
                        if(empty($item->event->parent_event_id)  && $item->event->is_recurring == "no"){
                            ?>
                            <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->id}}, {{$item->event_id}},'single');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php }else if($item->event->edit_recurring_pattern == "single event" && $item->event->is_recurring == "yes"){ ?>
                            <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->id}}, {{$item->event_id}},'single');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php }else{ ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->id}}, {{$item->event_id}},'multiple');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php } ?>
                    @endcan
                @endif
            </div>
            @endif
        </td>
        @endif
    </tr>
@empty
@endforelse