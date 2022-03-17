@if(count($events))
<table class="table-bordered agenda-table">
    <thead class="fc-head">
        <tr>

        </tr>
    </thead>
    <tbody class="fc-body">
        @php
            $userTimezone = auth()->user()->user_timezone ?? 'UTC';
            $authUser = auth()->user();
        @endphp
        @forelse ($events as $item)
            <tr>
                <td>
                    @if(isset($oDate) && $item->start_date==$oDate)
                    @else
                        @php
                            $oDate=$item->start_date;
                        @endphp
                        {{ date('D, M d', strtotime($item->start_date_time)) }}
                    @endif
                </td>
                <td>
                    @if($item->is_SOL == 'yes' || $item->is_task == 'yes')
                        all day
                    @else
                    {{ date('h:i A',strtotime($item->start_date_time)) }} - {{ date('h:i A',strtotime($item->end_date_time)) }}</td>
                    @endif
                <td>
                    @if($item->is_SOL == 'yes')
                        @if($item->sol_satisfied == "yes")
                        <span class="mr-2 badge badge-success">SOL</span>
                        @else 
                        <span class="mr-2 badge badge-danger">SOL</span>
                        @endif
                        <i class="sol-gavel mr-1"></i>
                        {{ $item->event_title }}
                        <br><p class="agenda-sol-satisfied">Satisfied</p>
                    @else
                        {{ $item->event_title }}
                    @endif
                </td>
                <td>
                    @if($item->case_id)
                    <a href="{{ route('caseview', @$item->case_unique_number) }}">{{$item->case_title}}</a>
                    @elseif($item->lead_id)
                    <a href="{{ route('case_details/info', $item->lead_id) }}">{{$item->lead_user_name}}</a>
                    @else
                        -
                    @endif
                </td>
                <td class="event-users">
                    @if($item->is_SOL == 'no')
                    @php
                        $isAuthUserLinked = $item->event_linked_staff->where('user_id', $authUser->id)->first();
                    @endphp
                    @if(!empty($item->event_linked_staff) && ($isAuthUserLinked || $authUser->parent_user == 0))
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
                    @else
                    @endif
                </td>
                @if($item->is_SOL=='yes')
                <td class="event-users">
                    <a class="align-items-center" data-toggle="modal" data-target="#addCaseReminderPopup" 
                    data-placement="bottom" href="javascript:;" 
                    onclick="addCaseReminder({{$item->case_id}});"> 
                    <i class="fas fa-bell pr-2 align-middle"></i>
                    </a>
                </td>
                @else
                <td class="event-users">
                    @if($item->is_event_private=='no')
                    <div class="">
                        @if (auth()->id() == $item->created_by)
                        <a class="align-items-center" data-toggle="modal" data-target="#loadEventReminderPopup" data-placement="bottom" href="javascript:;" onclick="loadEventReminderPopup({{$item->event_id}}, {{$item->event_recurring_id}});"> <i class="fas fa-bell pr-2 align-middle"></i> </a>
                        @endif
                        <a class="align-items-center" data-toggle="modal" data-target="#loadCommentPopup" data-placement="bottom" href="javascript:;" onclick="loadEventComment({{$item->event_id}}, {{$item->event_recurring_id}});"> <i class="fas fa-comment pr-2 align-middle"></i> </a>
                        @can('event_add_edit')
                        <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup" data-placement="bottom" href="javascript:;" onclick="editEventFunction({{$item->event_id}}, {{$item->event_recurring_id}});">
                            <i class="fas fa-pen pr-2  align-middle"></i> 
                        </a>
                        @endcan
                        @can(['event_add_edit','delete_items'])
                        <?php 
                        if(empty($item->parent_event_id)  && $item->is_recurring == "no"){
                            ?>
                            <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->event_recurring_id}}, {{$item->event_id}},'single');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php }else if($item->edit_recurring_pattern == "single event" && $item->is_recurring == "yes"){ ?>
                            <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->event_recurring_id}}, {{$item->event_id}},'single');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php }else{ ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->event_recurring_id}}, {{$item->event_id}},'multiple');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php } ?>
                        @endcan
                    </div>
                    @endif
                </td>
                @endif
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
@endif
