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
                            <th class="text-nowrap" width="10%">Payment Method</th>
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
                "aTargets": [0],
                },
            ],
            pageResize: true, // enable page resize
            pageLength: <?php echo USER_PER_PAGE_LIMIT; ?>,
            columns: [
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
                {data: 'id','sorting': false},
            ],
            // Old code
            /* "fnCreatedRow": function (nRow, aData, iDataIndex) {
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
            }, */

            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-left">' + aData.added_date +'</div>');

                if(aData.related_to_invoice_id != null || aData.invoice_id) {
                    if(aData.invoice) {
                        $('td:eq(1)', nRow).html('<a href="'+baseUrl+'/bills/invoices/view/'+ aData.invoice.decode_id+'" >#'+aData.invoice.invoice_id+'</a>');
                    } else {
                        $('td:eq(1)', nRow).html('#'+aData.related_to);
                    }
                } else if(aData.related_to_fund_request_id != null) {
                    $('td:eq(1)', nRow).html(aData.related_to);
                } else {
                    $('td:eq(1)', nRow).html('<i class="table-cell-placeholder"></i>');
                }

                var noteContent = '';
                if(aData.notes != null) {
                    noteContent += '<div class="position-relative">\
                            <a class="test-note-callout d-print-none" tabindex="0" data-toggle="popover" data-html="true" data-placement="bottom" data-trigger="focus" title="Notes" data-content="<div>'+aData.notes+'</div>">\
                                <img style="border: none;" src="'+imgBaseUrl+'icon/note.svg'+'">\
                            </a>\
                        </div>';
                }
                var enteredBy = '';
                if(aData.responsible != null) {
                    enteredBy = aData.responsible.full_name;
                } else if(aData.created_by_user != null) {
                    enteredBy = aData.created_by_user.full_name;
                }
                $('td:eq(2)', nRow).html('<div style="display: flex !important; justify-content: space-between !important;"><div class="text-left">' + enteredBy +'</div>'+ ' ' +noteContent+'</div>');

                var isRefund = (aData.is_refunded == "yes") ? "(Refunded)" : (aData.status == 2 || aData.status == 3) ? "(Refunded)" : "";
                var ftype = ''; var finalAmount = 0.00;
                if(aData.payment_from == "trust" && aData.pay_method == "Trust"){
                    ftype = "Payment from Trust (Trust Account) to Operating (Operating Account)";
                    finalAmount = '$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "trust" && aData.pay_method == "Trust Refund"){
                    ftype = "Refund Payment from Trust (Trust Account) to Operating (Operating Account)";
                    finalAmount = '-$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "offline" && aData.pay_method=="Trust Refund"){
                    ftype = "Refund Payment into Trust (Trust Account)";
                    finalAmount = '-$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "offline" && aData.pay_method=="Refund" && aData.deposit_into == "Operating Account"){
                    ftype = "Refund Payment into Operating (Operating Account)";
                    finalAmount = '-$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "offline" && aData.acrtivity_title == "Payment Received" && aData.deposit_into == "Operating Account"){
                    ftype = "Payment into Operating (Operating Account)";
                    finalAmount = '$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "offline" && aData.acrtivity_title == "Payment Received" && aData.deposit_into == "Credit"){
                    ftype = "Payment into Credit (Operating Account)";
                    finalAmount = '$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "offline" && aData.acrtivity_title == "Payment Refund" && aData.deposit_into == "Credit"){
                    ftype = "Refund Payment into Credit (Operating Account)";
                    finalAmount = '-$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "offline" && aData.acrtivity_title == "Payment Received" && aData.deposit_into == "Trust Account"){
                    ftype = "Payment into Trust (Trust Account)";
                    finalAmount = '$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "credit" && aData.acrtivity_title == "Payment Received"){
                    ftype = "Payment from Credit (Operating Account)";
                    finalAmount = '-$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.payment_from == "credit" && aData.acrtivity_title == "Payment Refund"){
                    ftype = "Refund Payment from Credit (Operating Account)";
                    finalAmount = '$<span class="payRow">' + aData.amount + '</span>';
                }else if(aData.fund_type=="diposit") {
                    ftype="Deposit into Trust (Trust Account)";
                    finalAmount = '$<span class="payRow">' + aData.amount_paid + '</span>';
                }else{
                    ftype="";
                }
                $('td:eq(3)', nRow).html('<div class="text-left nowrap">'+ftype +'</div>');

                if(aData.pay_method)
                    $('td:eq(4)', nRow).html('<div class="text-left nowrap">'+ aData.pay_method +' '+ isRefund +'</div>');
                else if(aData.payment_method)
                    $('td:eq(4)', nRow).html('<div class="text-left nowrap">'+ aData.payment_method +' '+ isRefund +'</div>');
                else
                    $('td:eq(4)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>');


                if(finalAmount == "0.00"){
                    $('td:eq(5)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>');
                }else{
                    $('td:eq(5)', nRow).html('<div class="text-left">' + finalAmount + '</div>');
                }
            },

            "initComplete": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip();
                $("[data-toggle=popover]").popover();
                $('.payRow').number(true, 2);
            },
            "drawCallback": function (settings) { 
                $('[data-toggle="tooltip"]').tooltip();
                $('.payRow').number(true, 2);
            },
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