<div class="modal-body">
    <table class="table" id="popup_reminder_table">
        <thead>
            <th>Date/Time</th>
            <th>Created By</th>
            <th>Type</th>
            <th>Name</th>
            <th>Case/Lead</th>
            <th>Location</th>
            <th>Priority</th>
            <th></th>
            <th></th>
        </thead>
        <tbody>
            @forelse ($result as $key => $item)
                <tr data-reminder-id="{{ $item['reminder_id'] }}" data-event-recurring-id="{{ $item['event_recurring_id'] }}" data-reminder-type="{{ $item['type']}}">
                    <td class="align-middle">{{ $item['date_time'] }}</td>
                    <td class="align-middle">{{ $item['created_by'] }}</td>
                    <td class="align-middle">{{ ucfirst($item['type']) }}</td>
                    <td class="align-middle">{!! $item['name'] !!}</td>
                    <td class="align-middle">
                        @if ($item['case_id'])
                            <a href="{{ route('info', $item['case_unique_number']) }}" >{{ $item['case_lead'] }}</a>
                        @elseif($item['lead_id'])
                            <a href="{{ route('case_details/info', $item['lead_id']) }}" >{{ $item['case_lead'] }}</a>
                        @else
                            {{ $item['case_lead'] }}
                        @endif
                    </td>
                    <td class="align-middle">{{ $item['location'] }}</td>
                    <td class="align-middle">{{ $item['priority'] }}</td>
                    <td class="align-middle"><button type="button" class="snooze-button text-nowrap btn btn-link" data-snooze-type="min" data-reminder-id="{{ $item['reminder_id'] }}" data-reminder-type="{{ $item['type']}}" >Snooze 10min</button></td>
                    <td class="align-middle"><button type="button" class="dismiss-button btn btn-link" data-reminder-id="{{ $item['reminder_id'] }}" data-event-recurring-id="{{ $item['event_recurring_id'] }}" data-reminder-type="{{ $item['type']}}" >Dismiss</button></td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <div role="group" class="btn-group">
        <button type="button" value="10" data-snooze-type="min" class="btn btn-secondary ladda-button example-button snooze-time">
            Snooze All 10 mins
        </button>
        <div class="btn-group">
            <button type="button" id="snoozeDropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-secondary" data-toggle="dropdown">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right ">
                <button type="button" tabindex="0" value="5" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 5 mins</button>
                <button type="button" tabindex="0" value="10" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 10 mins</button>
                <button type="button" tabindex="0" value="15" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 15 mins</button>
                <button type="button" tabindex="0" value="30" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 30 mins</button>
                <button type="button" tabindex="0" value="1" data-snooze-type="hour" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 1 hour</button>
                <button type="button" tabindex="0" value="2" data-snooze-type="hour" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 2 hour</button>
                <button type="button" tabindex="0" value="1" data-snooze-type="day" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 1 day</button>
                <button type="button" tabindex="0" value="1" data-snooze-type="week" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze All 1 week</button>
            </div>
        </div>
    </div>
    <div role="group" class="btn-group">
        <button type="button" value="dismiss-all"  class="btn btn-primary ladda-button example-button dismiss-notification">
            Dismiss All
        </button>
    </div>
</div>
<script>
$("#snoozeDropdown").trigger("click");
</script>