@extends('layouts.master')
@section('title', 'Legalcase - Simplify Your Law Practice | Cloud Based Practice Management')

@section('page-css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.6/fullcalendar.min.css" />
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
            <div id="calendar" class="col-md-12"></div>
        </div>
    </div>
</div>
</div>


@section('page-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.6/fullcalendar.min.js"></script>
<script type="text/javascript">
var options = {
    header: {
        left: 'prev,next today',
        center: 'title',
        right: 'agendaDay,agendaWeek,month'
    },
    eventBackgroundColor: 'transparent',
    eventBorderColor: '#08c',
    eventTextColor: 'black',
    height: 'auto',
    defaultView: 'agendaWeek',
    allDaySlot: false,
};

var $fc = $("#calendar").fullCalendar({
    header: {
        left: 'prev,next today',
        center: 'title',
        right: 'agendaDay,agendaWeek,month'
    },
    eventBackgroundColor: 'transparent',
    eventBorderColor: '#08c',
    eventTextColor: 'black',
    height: 'auto',
    defaultView: 'agendaWeek',
    allDaySlot: false,
});

function recreateFC(screenWidth) {
    if (screenWidth < 700) {
        options.header = {
            left: 'prev,next today',
            center: 'title',
            right: ''
        };
        options.defaultView = 'agendaDay';
    } else {
        options.header = {
            left: 'prev,next today',
            center: 'title',
            right: 'agendaDay,agendaWeek,month'
        };
        options.defaultView = 'agendaWeek';
    }
    $fc.fullCalendar('destroy');
    $fc.fullCalendar(options);
}

$(window).resize(function () {
    recreateFC($(window).width());
});

recreateFC($(window).width());

/* $fc.setOptions({
    views: {
        agendaDay: { buttonText: 'resources on day' },
    },
    resources: function(callback, start, end, timezone) {
        alert();
        var byuser = getByUser();
        var view = $fc.fullCalendar('getView');
        $.ajax({
            url: 'loadEventCalendar/loadStaffView',
            type: 'POST',
            data: {resType: "resources", byuser: byuser, view_name: view.name},
            success: function (doc) {
                callback(doc);
            }
        });
    },
}); */
$fc.changeView('agendaDay', function() {
    alert();
});
</script>
@stop
@endsection