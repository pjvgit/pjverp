@if(count($events))
<table class="table-bordered">
    <thead class="fc-head">
        <tr>

        </tr>
    </thead>
    <tbody class="fc-body">
        @forelse ($events as $item)
            <tr>
                <td>{{ date('D, M d', strtotime($item->start_date)) }}</td>
                <td>{{ date('h:i A',strtotime($item->st)) }} - {{ date('h:i A',strtotime($item->et)) }}</td>
                <td>{{ $item->event_title }}</td>
                <td>
                    @if($item->case_id)
                    <a href="{{ route('caseview', $item->case->case_unique_number) }}">{{$item->case->case_title}}</a>
                    @elseif($item->lead_id)
                    <a href="{{ route('case_details/info', $item->lead_id) }}">{{$item->leadUser->full_name}}</a>
                    @else
                        -
                    @endif
                </td>
                <td><a href="{{ route('contacts/attorneys/info', base64_encode($item->created_by)) }}" class="d-flex align-items-center user-link">
                    {{ $item->eventCreatedByUser->full_name }}
                </td>
                <td class="event-users">
                    @if($item->is_event_private=='no')
                    <div class="mt-3 float-right">

                        <a class="align-items-center" data-toggle="modal" data-target="#loadReminderPopupIndex"
                        data-placement="bottom" href="javascript:;"
                        onclick="loadReminderPopupIndex({{$item->id}});">
                        <i class="fas fa-bell pr-2 align-middle"></i>
                        </a>


                        <a class="align-items-center" data-toggle="modal" data-target="#loadCommentPopup"
                        data-placement="bottom" href="javascript:;"
                        onclick="loadEventComment({{$item->id}});">
                        <i class="fas fa-comment pr-2 align-middle"></i>
                        </a>
                        <?php 
                        if($item->parent_evnt_id=="0"){
                            ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                        data-placement="bottom" href="javascript:;"
                        onclick="editSingleEventFunction({{$item->id}});">
                        <i class="fas fa-pen pr-2  align-middle"></i> </a>
                        <?php }else{?>
                            <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                            data-placement="bottom" href="javascript:;"
                            onclick="editEventFunction({{$item->id}});">
                            <i class="fas fa-pen pr-2  align-middle"></i> </a>
                        <?php } ?>
                        
                        <?php 
                        if($item->parent_evnt_id=="0"){
                            ?>
                                <a class="align-items-center" data-toggle="modal" data-target="#deleteEvent"
                                data-placement="bottom" href="javascript:;"
                                onclick="deleteEventFunction({{$item->id}},'single');">
                                <i class="fas fa-trash pr-2  align-middle"></i> </a>
                            <?php
                        }else{?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEvent"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$item->id}},'multiple');">
                            <i class="fas fa-trash pr-2  align-middle"></i> </a>
                        <?php } ?>
                    </div>
                    @endif
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
@endif
