<div class="modal-body">
    <table class="table">
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
                <tr>
                    <td>{{ $item['date_time'] }}</td>
                    <td>{{ $item['created_by'] }}</td>
                    <td>{{ ucfirst($item['type']) }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['case_lead'] }}</td>
                    <td>{{ $item['location'] }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary">Snooze</button>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="snooze_button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Snooze
        </button>
        <div class="dropdown-menu" aria-labelledby="snooze_button">
          <a class="dropdown-item" href="#">Snooze 5 mins</a>
          <a class="dropdown-item" href="#">Snooze 10 mins</a>
          <a class="dropdown-item" href="#">Snooze 15 mins</a>
          <a class="dropdown-item" href="#">Snooze 30 mins</a>
          <a class="dropdown-item" href="#">Snooze 1 hour</a>
          <a class="dropdown-item" href="#">Snooze 2 hours</a>
          <a class="dropdown-item" href="#">Snooze 1 day</a>
          <a class="dropdown-item" href="#">Snooze 1 week</a>
        </div>
    </div>
    <button type="button" class="btn btn-primary">Disniss</button>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="snooze_button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Dismiss
        </button>
        <div class="dropdown-menu" aria-labelledby="snooze_button">
          <a class="dropdown-item" href="#">Dismiss</a>
          <a class="dropdown-item" href="#">Dismiss All</a>
        </div>
    </div>
</div>
            