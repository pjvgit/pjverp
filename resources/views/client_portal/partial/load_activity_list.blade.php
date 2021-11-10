@if($item->type == "invoices")
<li class="list-row">
    <a href="{{ route('bills/invoices/view',base64_encode($item->activity_for)) }}"><i class="fas fa-dollar-sign list-row__icon"></i>
        <div class="list-row__body">
            <span class="list-row__wrappable-content">{{ @$item->createdByUser->full_name}} {{ $item->activity }} 
                <span class="u-color-primary">#{{sprintf('%06d', $item->activity_for)}}</span>
            </span><br>
            <span class="list-row__header-detail">{{ date('M d, Y h:i A', strtotime($item->formated_created_at)) }}</span>
        </div>
    </a>
</li>
@elseif($item->type == "task")
<li class="list-row">
    <a href="{{ route('client/tasks/detail', encodeDecodeId($item->task_id, 'encode')) }}">
        <i class="far fa-calendar-minus list-row__icon"></i>
        <div class="list-row__body">
            <span class="list-row__wrappable-content">{{ @$item->createdByUser->full_name}} {{ $item->activity }}
                <span class="u-color-primary">{{ $item->task->task_title }}</span>
                @if ($item->action == 'complete')
                    as completed
                @elseif($item->action == 'incomplete')
                    as incomplete
                @else
                @endif
            </span><br>
            <span class="list-row__header-detail">{{ date('M d, Y h:i A', strtotime($item->formated_created_at)) }}</span>
        </div>
    </a>
</li>
@elseif($item->type == "event")
<li class="list-row">
    <a href="{{ route('client/events/detail', encodeDecodeId($item->event_id, 'encode')) }}">
        <i class="far fa-calendar-minus list-row__icon"></i>
        <div class="list-row__body">
            <span class="list-row__wrappable-content">{{ @$item->createdByUser->full_name}} {{ $item->activity }}
                <span class="u-color-primary">{{ $item->event_name }}</span>
            </span><br>
            <span class="list-row__header-detail">{{ date('M d, Y h:i A', strtotime($item->formated_created_at)) }}</span>
        </div>
    </a>
</li>
@endif