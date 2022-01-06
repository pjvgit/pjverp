<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>

<div id="time_entries_page" class="case_info_page col-12 pt-2" style="">
    <div id="new-case-time-entries" data-court-case-id="14011629" data-show-ledes-info="false"
        data-can-add-time-entry="true" data-can-view-billing-rate="true">
        <div id="time-entry-filter-and-table">
            <div class="d-flex justify-content-between py-1">
                <div class="date-range d-flex align-items-center"><label class="text-bold mx-2 mb-0">Date Range:
                    </label>
                    <div class="date-range-filter-dropdown dropdown">
                        <input type="text" class="form-control" id="daterange" name="date_range" value=""
                            placeholder="" />
                    </div>
                </div>
                @can(['case_add_edit', 'billing_add_edit'])
                <a data-toggle="modal" data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;">
                    <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                        onclick="loadTimeEntryPopupByCase('{{$CaseMaster['case_id']}}');">Add Time Entry</button></a>
                @endcan
            </div>
            <div id="loadDynamicEntries">
                
            </div>
        </div>
    </div>
</div>
@include('commonPopup.add_case')
@section('page-js-inner')
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
        function cb(start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        $('#daterange').daterangepicker({
            locale: {
                applyLabel: 'Select'
            },
            // startDate: '01/01/2020',
            startDate: moment().subtract(1, 'month').startOf('month'),
            endDate: moment(),
            ranges: {
                'All Days': ['01/01/2020', moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                'Month to date': [moment().startOf('month').format('MM/DD/YYYY'), moment()],
                'Year to date': [moment().startOf('year').format('MM/DD/YYYY'), moment()]
            },
            "showCustomRangeLabel": false,
            "alwaysShowCalendars": true,
            "autoUpdateInput": true,
            "opens": "center",
            "minDate": "01/01/2020"
        }, function (start, end, label) {
            $("#loadDynamicEntries").html('<img src="{{LOADER}}"> Loading...');
            setTimeout(function(){     loadTimeEntriesBlock(); }, 2000);
        }, cb);
    });
    
    function loadTimeEntriesBlock() {
       
        $('.showError').html('');
        $("#loadDynamicEntries").html('');
        $("#loadDynamicEntries").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/time_entries/loadTimeEntryBlocks",
            data: {"case_id": "{{$CaseMaster['case_id']}}",'time_slot':$("#daterange").val()},
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();

                    $("#loadDynamicEntries").html('');
                    return false;
                } else {
                    $("#loadDynamicEntries").html(res);
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();

            }
        })
    }
    setTimeout(function(){     loadTimeEntriesBlock(); }, 2000);

    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;
    }
    $('#hiddenLable').hide();
</script>
@stop