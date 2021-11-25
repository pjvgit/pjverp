<div class="p-2 my-1 card">
    <div class="row ">
        <?php
        $BillablePercent=$InvoicePercent=0;
        if($TimeEntryLog['total_entry']>0){
            $BillablePercent=$TimeEntryLog['billable_entry']/$TimeEntryLog['total_entry']*100;
        }
        if($TimeEntryLog['total_entry']>0){
            $InvoicePercent=$TimeEntryLog['invoice_hours_total']/$TimeEntryLog['total_entry']*100;
        } 
        ?>
        <div class="pr-4 col-12 col-md-3 col-lg-4"><strong class="h5"><span
                    class="text-info">Billable</span> vs total</strong>
            <div class="my-1 progress" style="height: 10px;">
                <div class="progress-bar bg-info" role="progressbar" aria-valuenow="10000" aria-valuemin="0"
                    aria-valuemax="10000" style="width: {{$BillablePercent}}%"></div>
            </div>
            <div class="h3 mb-0 font-weight-bold">${{number_format($TimeEntryLog['billable_entry'],2)}} </div>
            <div>{{$TimeEntryLog['billable_entry_hours']}} hour(s)</div>
        </div>
        <div class="pr-4 col-12 col-md-3 col-lg-4"><strong class="h5"><span
                    class="text-success">Invoiced</span> vs total</strong>
            <div class="my-1 progress" style="height: 10px;">
                <div class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                    aria-valuemax="10000" style="width: {{$InvoicePercent}}%;"></div>
            </div>
            <div class="h3 mb-0 font-weight-bold">${{number_format($TimeEntryLog['invoice_hours_total'],2)}}</div>
            <div>{{$TimeEntryLog['invoice_hours']}} hour(s)</div>
        </div>
        <div class="col-12 col-md-6 col-lg-4"><strong class="h5">Total amount and hours recorded</strong>
            <div class="h1 mb-1 font-weight-bold">${{number_format($TimeEntryLog['total_entry'],2)}}</div>
            <div>{{$TimeEntryLog['total_entry_hours']}} hour(s)</div>
        </div>
    </div>
</div>
<fieldset id="mc-table-fieldset">
    <div class="d-flex justify-content-end">
        <span class="my-2">
            <small class="text-muted mx-1">Text Size</small>
            <button type="button" arial-label="Decrease text size" data-testid="dec-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i>
            </button>
            <button type="button" arial-label="Increase text size" data-testid="inc-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase"><i class="fas fa-plus fa-xs"></i>
            </button>
        </span>
    </div>
    <div data-testid="mc-table-container" style="font-size: small;">
        <table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%">
            <thead>
                <tr>
                    <th width="1%" class="nosort">id</th>
                    <th width="10%" class="nosort">Date</th>
                    <th width="15%" class="nosort">Activity</th>
                    <th width="5%" class="nosort">Duration</th>
                    <th width="15%" class="nosort">Description</th>
                    <th width="5%" class="nosort">Rate</th>
                    <th width="5%" class="nosort">Total</th>
                    <th width="5%" class="nosort">Status</th>
                    <th width="15%" class="nosort">User</th>
                    <th width="10%" class="d-print-none text-center nosort" ></th>
                </tr>
            </thead>

        </table>
    </div>
</fieldset>
<style>
.pagination{width: 80%;float: right;}
</style>
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
       
        var timeEntryGrid =  $('#timeEntryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            searching: false,
            stateSave: true,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/time_entries/loadTimeEntry", // json datasource
                type: "post",  // method  , by default get
                data :{"c":"{{$CaseMaster['id']}}" ,'from': '{{$from}}','to': '{{$to}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'date_format_new','sorting':false},
                { data: 'activity_title','sorting':false},
                { data: 'duration','sorting':false},
                { data: 'description','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    if(aData.time_entry_billable=="yes"){
                        if(aData.activity_title!=null){
                            $('td:eq(1)', nRow).html('<div class="text-left nowrap"><i class="fa fa-circle fa-sm text-info mr-1" title="Billable"></i> '+aData.activity_title+'</div>');
                        }else{
                            $('td:eq(1)', nRow).html('<div class="text-left nowrap"></div>');
                        }
                   
                    }else{
                        if(aData.activity_title!=null){
                            $('td:eq(1)', nRow).html('<div class="text-left nowrap">'+aData.activity_title+'</div>');
                        }else{
                            $('td:eq(1)', nRow).html('<div class="text-left nowrap"></div>');
                        }
                    }
                    if(aData.rate_type=="flat"){
                        $('td:eq(4)', nRow).html('<div class="text-left nowrap">Flat</div>');
                    }else{
                        $('td:eq(4)', nRow).html('<div class="text-left nowrap">'+aData.entry_rate+'/'+aData.rate_type+'</div>');
                    }
                    $('td:eq(5)', nRow).html('<div class="text-left nowrap">$'+aData.calculated_amt+'</div>');
                    if(aData.status=="unpaid"){
                        $('td:eq(6)', nRow).html('<div class="text-left nowrap">Open</div>');

                    }else{
                        $('td:eq(6)', nRow).html('<div class="text-left nowrap"><i class="fa fa-circle fa-sm text-success mr-1"></i><a href="'+baseUrl+'/bills/invoices/view/'+aData.decode_invoice_id+'">Invoiced</a></div>');

                    }
                    
                    // $('td:eq(7)', nRow).html('<div class="text-left">'+aData.user_name+'</div>');
                    
                    $('td:eq(7)', nRow).html('<div class="text-left nowrap"><a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.user_name+'</a></div>');
                    @can(['case_add_edit', 'billing_add_edit'])
                    if(aData.status=="unpaid"){
                        var deleteAction = '';
                        @can('delete_items')
                        deleteAction = '<a data-toggle="modal"  data-target="#deleteTimeEntry" data-placement="bottom" href="javascript:;"  onclick="deleteTimeEntry('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a>';
                        @endcan
                        $('td:eq(8)', nRow).html('<div class="text-center nowrap d-print-none"><a data-toggle="modal"  data-target="#loadEditTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditTimeEntryPopup('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a>'+deleteAction+'</div>');
                    }else{
                        $('td:eq(8)', nRow).html('<div class="text-center nowrap"></div>');
                    }
                    @else
                    $('td:eq(8)', nRow).html('');
                    @endcan
                },
                "initComplete": function(settings, json) {
                    var currentSize=localStorage.getItem("timeEntryList");
                    $('td').css('font-size', currentSize +'px');
                   
                }
        });
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        
        //Decrease the font size
        $(".decrease").click(function(){   
            modifyFontSize('decrease');  
        });
    });
    
    function modifyFontSize(flag) {  
        var min = 13;
        var max = 19;
        var divElement = $('td');  
        var currentFontSize = parseInt(divElement.css('font-size'));  

        if (flag == 'increase')  
            currentFontSize += 3;  
        else if (flag == 'decrease')  
            currentFontSize -= 3;  
        else  
            currentFontSize = 13;  
            if(currentFontSize>=min && currentFontSize<=max){
            divElement.css('font-size', currentFontSize); 
            localStorage.setItem("timeEntryList",currentFontSize);
        }
    }  
</script>