<div id="time_entries_page" class="case_info_page col-12 pt-2" style="">
    <div id="new-case-time-entries" data-court-case-id="14011629" data-show-ledes-info="false"
        data-can-add-time-entry="true" data-can-view-billing-rate="true">
        <div id="time-entry-filter-and-table">
            <div class="d-flex justify-content-between py-1">
                <div class="date-range d-flex align-items-center">
                    <div class="date-range-filter-dropdown dropdown">
                       
                    </div>
                </div>
                <a data-toggle="modal" data-target="#loadExpenseEntryPopup" data-placement="bottom"
                href="javascript:;">
                <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                    onclick="loadExpenseEntryPopup({{$CaseMaster['case_id']}});">Add Expense</button></a>
            </div>
            <div data-testid="mc-table-container" style="font-size: small;">
                <table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%">
                    <thead>
                        <tr>
                            <th class="col-md-auto nosort"></th>
                            <th class="nosort">Date</th>
                            <th class="nosort">Activity</th>
                            <th class="nosort">Quantity</th>
                            <th class="nosort">Cost</th>
                            <th class="w-25 nosort">Description</th>
                            <th class="nosort">Total</th>
                            <th class="nosort">Status</th>
                            <th class="nosort"> User </th>
                            <th class="d-print-none nosort">&nbsp;</th>
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
        var timeEntryGrid =  $('#timeEntryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "aaSorting": [],
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/expenses/loadExpensesEntry", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c':"{{$CaseMaster['case_id']}}"},
                error: function(){  // error handling
                     $("#timeEntryGrid_processing").css("display","none");
                }
            },
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            columns: [
                { data: 'id','sorting':false},
                { data: 'date_format_new'},
                { data: 'activity_title'},
                { data: 'qty'},
                { data: 'id','sorting':false},
                { data: 'description','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id'},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    $('td:eq(3)', nRow).html('<div class="text-left">$'+aData.cost_value+'</div>');
                    $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.calulated_cost+'</div>');
                    if(aData.status=="unpaid"){
                        $('td:eq(6)', nRow).html('<div class="text-left nowrap">Open</div>');
                    }else{
                        $('td:eq(6)', nRow).html('<div class="text-left nowrap"><i class="fa fa-circle fa-sm text-success mr-1"></i><a href="{{BASE_URL}}bills/invoices/view/'+aData.decode_invoice_id+'">Invoiced</a></div>');
                    }                    
                    $('td:eq(7)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.user_name+'</a></div>');
                    if(aData.status=="unpaid"){
                        $('td:eq(8)', nRow).html('<div class="text-center nowrap"><a data-toggle="modal"  data-target="#loadEditExpenseEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditExpenseEntryPopup('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a><a data-toggle="modal"  data-target="#deleteExpenseEntryCommon" data-placement="bottom" href="javascript:;"  onclick="deleteExpenseEntryCommon('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></div>');
                    }else{
                        $('td:eq(8)', nRow).html('<div class="text-center nowrap"></div>');
                    }
                }
        });
    });
</script>
@stop