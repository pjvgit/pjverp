@if((count($task->taskLinkedStaff) || count($task->taskLinkedContact)))
    @php
        $totalUser = count($task->taskLinkedStaff) + count($task->taskLinkedContact);    
    @endphp
    @if($totalUser > 1)
        @php
        $userListHtml = "<table><tbody>";
        foreach($task->taskLinkedStaff as $linkuserValue){
            $userListHtml.="<tr><td>
            <span> 
                <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                <a href=".url('contacts/attorneys/'.$linkuserValue->decode_id)."> ".$linkuserValue->full_name."</a>
            </span>
            </td></tr>";
        }
        if(count($task->taskLinkedContact)) {
            foreach($task->taskLinkedContact as $linkuserValue){
                $userListHtml.="<tr><td>
                <span> 
                    <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                    <a href=".(($linkuserValue->user_level == 4) ? route('contacts/companies/view', $linkuserValue->id) : route('contacts/clients/view', $linkuserValue->id))."> ".$linkuserValue->full_name."</a>
                </span>
                </td></tr>";
            }
        }
        $userListHtml .= "</tbody></table>";
        @endphp
        <a class="mt-3 event-name d-flex align-items-center pop" tabindex="0" role="button"
        href="javascript:;" data-toggle="popover" data-trigger="focus" title=""
        data-content="{{$userListHtml}}" data-html="true" {{-- data-original-title="Staff" --}}
        style="float:left;"><i class="fas fa-user-friends"></i>&nbsp;{{ $totalUser ?? 0 }} Users</a>
    @else
        <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
        href="{{ route('contacts/attorneys/info', base64_encode(@$task->taskLinkedStaff[0]->id)) }}">{{ @$task->taskLinkedStaff[0]->full_name}}</a>
    @endif
@else
    <span><i class="fas fa-user-friends"></i> 0 Users</span>
@endif