@extends('layouts.master')
@section('title', 'Legalcase - Simplify Your Law Practice | Cloud Based Practice Management')

@section('page-css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.6.0/main.css">
<style>
.agenda-table th, .agenda-table td {
    padding: 5px 10px !important;
}
.fc-view.fc-CustomView-view{
max-height: 500px !important;
    overflow-y: auto !important;
}
.fc-day-grid-event > .fc-content {
    white-space: nowrap;
    overflow: hidden;
}
</style>    
@endsection
@section('main-content')

{{-- <div class="separator-breadcrumb border-top"></div> --}}
<div id="calendar_view_div">
<div class="row">
    <div class="col-md-10">
        <div class="row">
            <div id="calendarq" class="col-md-12"></div>
        </div>
    </div>
</div>
</div>


@section('page-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.js"></script>
<script src='https://cdn.jsdelivr.net/npm/moment-timezone@0.5.31/builds/moment-timezone-with-data.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
{{-- <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/moment@5.5.0/main.global.min.js'></script> --}}
{{-- <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment-with-locales.min.js'></script> --}}
{{-- <script src='https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.6/moment-timezone-with-data.js'></script> --}}
<script type="text/javascript">
// import { toMoment } from '@fullcalendar/moment'; // only for formatting
// import momentTimezonePlugin from 'public/assets/plugins/fullcalendar-5.8.0/moment-timezone-with-data.js';

var calendarEl = document.getElementById('calendarq');
    calendar = new FullCalendar.Calendar(calendarEl, {
        // timeZone: "{{ $authUser->user_timezone ?? 'local' }}",
        timeZone: 'America/Mexico_City',
        // timeZone: 'UTC',
        plugins: [ ],
        initialView: "dayGridMonth",
        initialDate: "{{ \Carbon\Carbon::now((!(empty($authUser->user_timezone))) ? $authUser->user_timezone : 'UTC')->format('Y-m-d') }}",
        themeSystem: "bootstrap4",
        height: 700,
        dayMaxEvents: true, // allow "more" link when too many events
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,dayGridMonth'
        },
        buttonText:{
            month:    'Month',
            week:     'Week',
            day:      'Day',
            today : 'Today',
        },
        // displayEventTime: false,
        eventTimeFormat: { // like '14:30:00'
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        },
        /* events: [
            { start: '2022-08-01T12:30:00Z', 'title': 'UTC, wont shift' }, // already in UTC, so won't shift
            { start: '2022-08-01T12:30:00-05:00', 'title': 'with offset' }, // will shift to 00:00 offset
            { start: '2022-08-01T12:30:00', 'title': 'will parsed as it' } // will be parsed as if it were '2018-09-01T12:30:00Z'
        ], */
        events: function(info, successCallback, failureCallback) {
            var startDate = info.start;
            var endDate = info.end;
            $.ajax({
                url: "{{ route('newloadevents') }}",
                data: {
                    // our hypothetical feed requires UNIX timestamps
                    // start: info.start.valueOf(),
                    // end: info.end.valueOf(),
                    start: moment(startDate).format('YYYY-MM-DD'),
                    end: moment(endDate).format('YYYY-MM-DD'),
                },
                success: function(data) {
                    successCallback(data);
                }
            });
        },
        /* events: function (info, callback, failureCallback) {
            var startDate = info.start;
            var endDate = info.end;
            
            $.ajax({
                url: 'newloadevents',
                type: 'POST',
                dataType: 'json',
                data: {
                    start: moment(startDate).format('YYYY-MM-DD'),
                    end: moment(endDate).format('YYYY-MM-DD'),
                },
                success: function (doc) {
                    var events = [];
                    if (!!doc.result) {
                        $.map(doc.result, function (r) {
                            var color="#00cfd2";
                            if(r.etext != '') {
                                color = r.etext;
                            }
                            var sTime = '<div class="user-circle mr-1 d-inline-block" style="width: 10px; height: 10px; background-color: '+color+';"></div>'+r.start_time_user +' -';
                            var eTitle = (r.is_read == 'no') ? '<span style="background: transparent;font-weight: bold;color: black;">'+ r.event_title +'</span>' : r.event_title;
                            // var className = (r.is_read == 'no') ? 'font-weight-bold' : '';
                            // var eTitle = r.event_title;
                            if (r.is_all_day == 'yes') {
                                var t = eTitle;
                            } else {
                                var t = sTime + eTitle;
                            }
                            var resource_id = [];
                            if(r.event_linked_staff) {
                                $.each(r.event_linked_staff, function(ind, item) {
                                    resource_id.push(item.user_id);
                                });
                            }
                            events.push({
                                id: r.event_recurring_id,
                                event_id: r.event_id,
                                // title: t,
                                title: r.event_title,
                                tTitle: r.event_title,
                                start: r.start_date_time,
                                end: r.end_date_time,
                                // classNames: [className],
                                allDay: (r.is_all_day == 'yes') ? true : false, 
                                color: (r.is_all_day == 'yes') ? color : '#d5e9ce',
                                backgroundColor: (r.is_all_day == 'yes') ? color : '#d5e9ce',
                                textColor:'#000000',
                                resourceIds: resource_id,
                            });
                        });
                    }
                    callback(events);
                },
            });
        }, */
    });
    calendar.render();
</script>
@stop
@endsection