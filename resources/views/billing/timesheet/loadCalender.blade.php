<div id="calendarq1"></div>
<script type="text/javascript">
    $(document).ready(function () {

        setTimeout(function () {
            var CalenderData = new Array();
            CalenderData = <?= json_encode($CalenderArray); ?> ;
            $('#calendarq1').fullCalendar({
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
                aspectRatio: 2.75,

                eventLimit: true, // allow "more" link when too many events
                events: function (start, end, timezone, callback) {
                    $.ajax({
                        url: baseUrl + "/bills/dashboard/loadDataOnly",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            "start": start.format(),
                            "end": end.format(),
                            "type":$("#currentBox").val(),
                            "forUser":$("#staff_user_main_form").val()
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

                             callBakeC(startDate,endDate,type);
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
    $("button.hamburger").click(function(){
        $('.hamburger.active').removeClass('active')
        $(this).addClass('active');
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
