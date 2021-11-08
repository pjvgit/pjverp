@if($item->type == "invoices")
<li class="list-row">
    <a href="{{ route('bills/invoices/view',base64_encode($item->activity_for)) }}"><i class="fas fa-dollar-sign list-row__icon"></i>
        <div class="list-row__body">
            <span class="list-row__wrappable-content">{{ @$item->createdByUser->full_name}} {{ $item->activity }} 
                <span class="u-color-primary">
                    @if ($item->deleteInvoice == NULL)
                        <a href="{{ route('bills/invoices/view',base64_encode($item->activity_for)) }}"> #{{sprintf('%06d', $item->activity_for)}} </a>
                    @else
                        #{{sprintf('%06d', $item->activity_for)}}
                    @endif 
                </span>
            </span><br>
            <span class="list-row__header-detail">{{ date('M d, Y h:i A', strtotime($item->formated_created_at)) }}</span>
        </div>
    </a>
</li>
@elseif($item->type == "task")
<li class="list-row">
    <a href="{{ route('client/tasks/detail', encodeDecodeId($item->task_id, 'encode')) }}"><i class="fas fa-sticky-note list-row__icon"></i>
        <div class="list-row__body">
            <span class="list-row__wrappable-content">{{ @$item->createdByUser->full_name}} {{ $item->activity }}
                <span class="u-color-primary">
                    @if ($item->task)
                    <a href="{{ route('client/tasks/detail', encodeDecodeId($item->task_id, 'encode')) }}">{{ $item->task->task_title }}</a>
                    @endif
                </span>
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
@endif