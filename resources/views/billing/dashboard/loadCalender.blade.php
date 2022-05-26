<div id="calendarq"></div>
<script type="text/javascript">
    $(document).ready(function () {

        setTimeout(function () {
            var CalenderData = new Array();
            CalenderData = <?= json_encode($CalenderArray); ?> ;
            $('#calendarq').fullCalendar({
                html:true,
                buttonText: {
                    today:    'Today',
                    month:    'By Month',
                    week:     'By Week',
                },
                header: {
                    left: 'prev,',
                    center: 'title',
                    right: 'today month,agendaWeek next'
                },
                // defaultDate: '2019-01-12',
                navLinks: true, // can click day/week names to navigate views
                editable: false,
                eventLimit: true, // allow "more" link when too many events
                events: function (start, end, timezone, callback) {
                    var cDate = $("#calendarq").fullCalendar("getDate");
                    var currentMonth = cDate.month() + 1; // fullcalendar month start from 0 to 11
                    var currentYear = cDate.year();

                    var view = $('#calendarq').fullCalendar('getView');
                    var viewName = view.name;
                    console.log(viewName);
                    $.ajax({
                        url: baseUrl + "/bills/dashboard/loadDataOnly",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            start: start.format(),
                            end: end.format(),
                            type:$("#currentBox").val()
                        },
                        success: function (doc) {
                            var events = [];
                            if (!!doc.CalenderArray) {
                                $.map(doc.CalenderArray, function (r) {
                                    events.push({
                                        title: r.title,
                                        start: r.start,
                                        end: r.end,
                                        mcolor: r.color,
                                        
                                    });
                                });
                            }
                            callback(events);
                            
                        },
                        complete: function () {
                            
                            var startDate= start.format();
                            var endDate=  end.format();
                            var type=$("#currentBox").val();

                             callBakeC(startDate,endDate,type, currentMonth, currentYear, viewName); 
                        }
                    })
                },
                eventRender: function (event, element,view) {
                    element.find('.fc-title').html('<span style="color:'+event.mcolor+';">'+event.title+'</span>'); 
                    // element.find('.fc-title').html();
                    
                },
                dayClick: function(date, jsEvent, view) {
                    loadTimeEntry(date.format());
                },eventClick: function(event) {
                    loadTimeEntry(event.start.format());
                },
                eventColor: '#FFFFFF'
            }).on('click', '.fc-agendaWeek-button', function() {
               $("#Mtotal").hide();
            }).on('click', '.fc-month-button', function() {
               $("#Mtotal").show();
            });
        }, 0);
    });

</script>
<style>
    .fc-title {
        font-size: 15px;
        color: #0070c0 !important;
        font-weight: bold;
    }
    .fc-time-grid-container {
        display: none !important;
    }
</style>
