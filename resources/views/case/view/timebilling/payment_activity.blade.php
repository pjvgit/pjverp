<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
<div id="time_entries_page" class="case_info_page col-12 pt-2" style="">
    <div id="new-case-time-entries" data-court-case-id="14011629" data-show-ledes-info="false"
        data-can-add-time-entry="true" data-can-view-billing-rate="true">
        <div id="time-entry-filter-and-table">
            <div class="d-flex justify-content-between py-1">
                <div class="date-range d-flex align-items-center">
                    <div class="date-range-filter-dropdown dropdown">
                       
                    </div>
                </div>
              
            </div>
            <div data-testid="mc-table-container" style="font-size: small;">
                <table class="display table table-striped table-bordered" id="paymentHistoryActivityTab" style="width:100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th width="10%">Date</th>
                            <th class="text-nowrap" width="10%">Related To</th>
                            <th class="text-nowrap" width="10%">Entered By</th>
                            <th class="text-nowrap" width="30%">Notes</th>
                            <th class="text-nowrap" width="10%">Amount</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    .pagination{width: 80%;float: right;}
    </style>
@section('page-js-inner')
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
        var paymentHistoryActivityTab = $('#paymentHistoryActivityTab').DataTable({
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: baseUrl + "/bills/activities/loadMixAccountActivity", // json datasource
                type: "post", // method  , by default get
                data: { 'case_id':"{{$CaseMaster['case_id']}}"},
                error: function () { // error handling
                    $(".paymentHistoryActivityTab-error").html("");
                    $("#paymentHistoryActivityTab_processing").css("display", "none");
                }
            },
            "aoColumnDefs": [{
                "bVisible": false,
                "aTargets": [0]
            }],
            pageResize: true, // enable page resize
            pageLength: <?php echo USER_PER_PAGE_LIMIT; ?>,
            columns: [
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-left">' + aData.added_date + '</div>');

                if(aData.section=="invoice"){
                    $('td:eq(1)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/' + aData
                    .decode_id + '">#' + aData.related + '</a>');
                }else if(aData.section=="request"){
                    $('td:eq(1)', nRow).html('#R-' + aData.related + '</a>');
                }else{
                    $('td:eq(1)', nRow).html('<i class="table-cell-placeholder"></i>');
                }
                $('td:eq(2)', nRow).html('<div class="text-left">' + aData.entered_by + '</div>');

                var noteText='';
                if(aData.notes!=null){
                    noteText=' <a role="button" data-toggle="popover" data-trigger="hover" title="" data-content="'+aData.notes+'" ><i class="fas fa-fw fa-file-invoice  text-16 mr-1"></i></a>';
                }
                if(aData.from_pay=="trust"){
                    // $('td:eq(3)', nRow).html('<div class="text-left nowrap">'+noteText+' Payment from Trust (Trust Account) to Operating (Operating Account)</div>');
                    $('td:eq(3)', nRow).html('<div class="text-left nowrap">'+noteText+' Payment into Trust (Trust Account)</div>');
                }else{
                    $('td:eq(3)', nRow).html('<div class="text-left nowrap">'+noteText+' Payment into Operating (Operating Account)	</div>');

                }
                if(aData.c_amt=="0.00"){
                    $('td:eq(4)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>');
                }else{
                    $('td:eq(4)', nRow).html('<div class="text-left">$<span class="payRow">' + aData
                    .c_amt + '</span></div>');
                }
            },
            "initComplete": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip();
                $("[data-toggle=popover]").popover();
                $('.payRow').number(true, 2);
            }
        });
    });

    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        window.print(canvas);
        // w.close();
        $(".printDiv").html('');
        $('#hiddenLable').hide();
        return false;  
    }
    $('#hiddenLable').hide();
</script>
@stop