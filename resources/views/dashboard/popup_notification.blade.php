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
    <select class="snooze-time btn btn-secondary">
        <option value="5" data-snooze-type="min">Snooze 5 mins</option>
        <option value="10" data-snooze-type="min">Snooze 10 mins</option>
        <option value="15" data-snooze-type="min">Snooze 15 mins</option>
        <option value="30" data-snooze-type="min">Snooze 30 mins</option>
        <option value="1" data-snooze-type="hour">Snooze 1 hour</option>
        <option value="2" data-snooze-type="hour">Snooze 2 hours</option>
        <option value="1" data-snooze-type="day">Snooze 1 day</option>
        <option value="1" data-snooze-type="week">Snooze 1 week</option>
    </select>
    <select class="dismiss-notification btn btn-primary">
        <option value="dismiss">Dismiss</option>
        <option value="dismiss-all">Dismiss All</option>
    </select>
</div>
            