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
        </thead>
        <tbody>
            @forelse ($result as $key => $item)
                <tr data-reminder-id="{{ $item['reminder_id'] }}" data-reminder-type="{{ $item['type']}}">
                    <td>{{ $item['date_time'] }}</td>
                    <td>{{ $item['created_by'] }}</td>
                    <td>{{ ucfirst($item['type']) }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['case_lead'] }}</td>
                    <td>{{ $item['location'] }}</td>
                    <td>{{ $item['priority'] }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
<div class="modal-footer">
    {{-- <select class="snooze-time btn btn-secondary">
        <option value="5" data-snooze-type="min">Snooze 5 mins</option>
        <option value="10" data-snooze-type="min">Snooze 10 mins</option>
        <option value="15" data-snooze-type="min">Snooze 15 mins</option>
        <option value="30" data-snooze-type="min">Snooze 30 mins</option>
        <option value="1" data-snooze-type="hour">Snooze 1 hour</option>
        <option value="2" data-snooze-type="hour">Snooze 2 hours</option>
        <option value="1" data-snooze-type="day">Snooze 1 day</option>
        <option value="1" data-snooze-type="week">Snooze 1 week</option>
    </select> --}}
    <div role="group" class="btn-group">
        <button type="button" value="5" data-snooze-type="min" class="btn btn-secondary ladda-button example-button snooze-time">
            Snooze 5 mins
        </button>
        <div class="btn-group">
            <button type="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-secondary" data-toggle="dropdown">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right ">
                <button type="button" tabindex="0" value="10" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 10 mins</button>
                <button type="button" tabindex="0" value="15" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 15 mins</button>
                <button type="button" tabindex="0" value="30" data-snooze-type="min" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 30 mins</button>
                <button type="button" tabindex="0" value="1" data-snooze-type="hour" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 1 hour</button>
                <button type="button" tabindex="0" value="2" data-snooze-type="hour" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 2 hour</button>
                <button type="button" tabindex="0" value="1" data-snooze-type="day" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 1 day</button>
                <button type="button" tabindex="0" value="1" data-snooze-type="week" role="menuitem" class="dropdown-item cursor-pointer snooze-time">Snooze 1 week</button>
            </div>
        </div>
    </div>
    {{-- <select class="dismiss-notification btn btn-primary">
        <option value="dismiss">Dismiss</option>
        <option value="dismiss-all">Dismiss All</option>
    </select> --}}
    <div role="group" class="btn-group">
        <button type="button" value="dismiss" class="btn btn-primary ladda-button example-button dismiss-notification">
            Dismiss
        </button>
        <div class="btn-group">
            <button type="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-primary" data-toggle="dropdown">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right ">
                <button type="button" tabindex="0" value="dismiss-all" role="menuitem" class="dropdown-item cursor-pointer dismiss-notification">Dismiss All</button>
            </div>
        </div>
    </div>
</div>
            