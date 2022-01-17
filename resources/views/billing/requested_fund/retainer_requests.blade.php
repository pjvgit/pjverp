@extends('layouts.master')
@section('title', 'Open - Expenses - Billing')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 

$c=$type=""; 
if(isset($_GET['c'])){
    $c= $_GET['c'];
}
if(isset($_GET['type'])){
    $type= $_GET['type'];
}
?>
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Requested Funds</h3>
                    <input type="hidden" name="type" value="{{$type}}">
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/retainer_requests')}}?type=all&c={{$c}}"
                                class="nav-link <?php if(!isset($_GET['type']) || $_GET['type']=='all') echo "active"; ?> ">All</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/retainer_requests')}}?type=sent&c={{$c}}"
                                class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='sent') echo "active"; ?>">Sent</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/retainer_requests')}}?type=partial&c={{$c}}"
                                class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='partial') echo "active"; ?>">Partial</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/retainer_requests')}}?type=paid&c={{$c}}"
                                class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='paid') echo "active"; ?>">Paid</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/retainer_requests')}}?type=overdue&c={{$c}}"
                                class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='overdue') echo "active"; ?>">Overdue</a>
                        </li>
                    </ul>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <button onclick="printEntry();return false;" class="btn btn-link">
                            <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top" title="" data-original-title="Print"></i>
                            <span class="sr-only">Print This Page</span>
                          </button>
                        @can('billing_add_edit') 
                        <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom"
                            href="javascript:;">
                            <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                                onclick="addRequestFundPopup();">Request Funds</button></a>
                        @endcan
                    </div>

                </div>
               
                    <div class="row pl-4 pb-4">
                        <div class="col-md-3 form-group mb-3">
                            <label for="picker1">Filter bills by billing contact</label>
                            <select id="c" name="c" class="form-control custom-select col filterbycase formSubmit">
                                <option value=""></option>
                                @foreach($clientList as $k=>$v)
                                <option <?php if($c==$v->uid){ echo "selected=selected"; } ?> value="{{$v->uid}}">
                                    {{$v->contact_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%">
                        <thead>
                            <tr>
                                <th>Id</th> 
                                <th>Number</th>
                                <th class="col-md-auto"> Contact </th>
                                <th class="nosort">Account</th>
                                <th class="nosort">Allocated To</th>
                                <th> Amount </th>
                                <th> Paid </th>
                                <th> Amount Due </th>
                                <th data-sort='YYYYMMDD'>Due </th>
                                <th> Date Sent</th>
                                <th class="nosort"> Viewed </th>
                                <th class="status-col-header nosort">Status</th>
                                @can('billing_add_edit') 
                                <th class="d-print-none nosort"></th>
                                @endcan
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->
@include('client_dashboard.billing.modal')

<!--Over-->
<style>
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        color: #2c2c2c;
        background-color: #cde2f2;
    }
    .pagination {
        width: 80%;
        float: right;
    }
    
</style>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {

        $("#button").removeAttr('disabled');
        $('.formSubmit').change(function () {
            this.form.submit();
        });
        $(".filterbycase").select2({
            placeholder: "Filter bills by billing contact",
            theme: "classic",
            allowClear: true
        });

        $('#addRequestFund,#editFundRequest,#deleteFundRequest,#sendFundReminder').on('hidden.bs.modal', function () {
            timeEntryGrid.ajax.reload(null, false);
        });
        $('#addEmailToClient').on('hidden.bs.modal', function () {
            setTimeout(function(){ 
            var f= $("#contact option:selected").attr('isemail');
            if(f=="no"){
                $('#disabledArea').addClass('retainer-request-opaque');
                $(".text-muted").hide();
                $("#preloader").hide();
                $('.submit').prop("disabled", true);
            }else{
                $(".text-muted").show();
                $('#disabledArea').removeClass('retainer-request-opaque');
                $('.submit').removeAttr("disabled");  
            }
        }, 1000);
            // $("#contact").val('').trigger('change').select2('open');
        });
        $('.dropdown-toggle').dropdown();
     
        var timeEntryGrid =  $('#timeEntryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            // stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/retainer_requests/loadRetainerRequestsEntry", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c' : '{{$c}}','type' : '{{$type}}'},
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
                { data: 'id'},
                { data: 'id'},
                { data: 'id','sorting':false},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                @can('billing_add_edit') 
                { data: 'id','sorting':false}@endcan],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<div class="text-left">'+aData.padding_id+'</div>');
                    if(aData.user == null) {
                        $('td:eq(1)', nRow).html('<div class="text-left">'+aData.contact_name+'</div>');
                    } else {
                    if(aData.user.user_level == 2)
                        $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.contact_name+'</a></div>');
                    else
                        $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/companies/'+aData.client_id+'">'+aData.contact_name+'</a></div>');
                    }
                    var trustLabel="Account";
                    /* if(aData.trust_account!=""){
                        var trustLabel=" (Trust Account)";
                    } */
                    if(aData.deposit_into_type == "credit"){
                        var trustLabel="(Credit Account)";
                    }
                    if(aData.email_message==null){
                        var $msg='';
                    }else{
                        var $msg='<br><a href="javascript:void(0);"  data-toggle="tooltip" data-html="true" data-placement="bottom" data-original-title="'+aData.email_message+'" >View Message</a>';
                    }
                    // $('td:eq(2)', nRow).html('<div class="text-left">'+aData.trust_account+' '+trustLabel+' ' +$msg+'</div>');
                    $('td:eq(2)', nRow).html('<div class="text-left">'+aData.deposit_into_type.substr(0,1).toUpperCase()+aData.deposit_into_type.substr(1)+' '+trustLabel+' ' +$msg+'</div>');
                    
                    if(aData.allocated_to_case_id != null) {
                        var clientLink='<a class="name" href="'+baseUrl+'/court_cases/'+aData.allocate_to_case.case_unique_number+'/info/">'+aData.allocate_to_case.case_title+'</a>';
                    } else {
                        if(aData.user == null) {
                            var clientLink = '<div class="text-left">'+aData.contact_name+'</div>';
                        } else {
                            if(aData.user != null && aData.user.user_level == 2)
                                var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.user.full_name+' ('+aData.user.user_type_text+')</a>';
                            else
                                var clientLink='<a class="name" href="'+baseUrl+'/contacts/companies/'+aData.client_id+'">'+aData.user.full_name+' ('+aData.user.user_type_text+')</a>';
                        }
                    }
                    $('td:eq(3)', nRow).html('<div class="text-left">'+clientLink+'</div>');
                    $('td:eq(4)', nRow).html('<div class="text-left">$'+aData.amt_requested+'</div>');
                    $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.amt_paid+'</div>');
                    $('td:eq(6)', nRow).html('<div class="text-left">$'+aData.amt_due+'</div>');
                    $('td:eq(7)', nRow).html('<span class="d-none">'+moment(aData.due_date_format).format('YYYYMMDD')+'</span>'+aData.due_date_format);
                    $('td:eq(8)', nRow).html('<div class="text-left">'+aData.last_send+'</div>');
                  
                    if(aData.is_viewed=="no"){
                        $('td:eq(9)', nRow).html('<div class="text-left">Never</div>');
                    }else{
                        $('td:eq(9)', nRow).html('<div class="text-left">Yes</div>');
                    }
                    // if(aData.is_due!=null){
                    //     var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>Overdue';
                    // }else{
                    //     var curSetatus="Sent";
                    // }
                    if(aData.current_status=="Paid"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;"></i>'+aData.current_status;
                    }else if(aData.current_status=="Partial"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'+aData.current_status;
                    }else if(aData.current_status=="Overdue"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'+aData.current_status;
                    }else if(aData.current_status=="Sent"){
                        var curSetatus=aData.current_status;
                    }
                    
                    $('td:eq(10)', nRow).html('<div class="text-left">'+curSetatus+'</div>');
                    // $('td:eq(10)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#loadEditTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditTimeEntryPopup('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a><a data-toggle="modal"  data-target="#deleteTimeEntry" data-placement="bottom" href="javascript:;"  onclick="deleteTimeEntry('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></div>');
                    // $('td:eq(10)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#editFundRequest" data-placement="bottom" href="javascript:;"  onclick="editFundRequest('+aData.id+');"><i class="fas fa-pen align-middle pr-3"></i></a> <a data-toggle="modal"  data-target="#sendFundReminder" data-placement="bottom" href="javascript:;"  onclick="sendFundReminder('+aData.id+');"><i class="fas fa-bell pr-3 align-middle"></i></a> <a data-toggle="modal"  data-target="#deleteRequestFund" data-placement="bottom" href="javascript:;"  onclick="deleteRequestFund('+aData.id+');"><i class="fas fa-trash align-middle "></i></a></div>');
                    @can('billing_add_edit') 
                    var action = '<div class="text-center">\
                        <a data-toggle="modal"  data-target="#editFundRequest" data-placement="bottom" href="javascript:;"  onclick="editFundRequest('+aData.id+');">\
                            <i class="fas fa-pen align-middle pr-3"></i>\
                        </a>';
                    if(aData.status != 'paid') {
                        action += '<a data-toggle="modal"  data-target="#sendFundReminder" data-placement="bottom" href="javascript:;"  onclick="sendFundReminder('+aData.id+');">\
                            <i class="fas fa-bell pr-3 align-middle"></i>\
                        </a>';
                    }
                    action += '<a data-toggle="modal" data-placement="bottom" href="javascript:;"  onclick="deleteRequestFund('+aData.id+', this);" data-payment-count="'+aData.fund_payment_history_count+'">\
                            <i class="fas fa-trash align-middle "></i>\
                        </a>\
                    </div>';
                    
                    $('td:eq(11)', nRow).html(action);
                    @endcan
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();

                },
                "drawCallback": function (settings) { 
                    $('[data-toggle="tooltip"]').tooltip();
                },
        });
        $('#actionbutton').attr('disabled', 'disabled');
    });
    
    function printEntry()
   {
        var info = $('#timeEntryGrid').DataTable().page.info();
        var current_page=info.page;
        var length=info.length;
        var orderon=$('#timeEntryGrid').dataTable().fnSettings().aaSorting;
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/printRequestFundEntry",
                data :{ 'c' : '{{$c}}','type':'{{$type}}','current_page':current_page,'length':length,'orderon':orderon },
                success: function (res) {
                    $(".printDiv").html(res);
                    var canvas = $(".printDiv").html();
                    window.print(canvas);
                    // w.close();
                    $(".printDiv").html('');
                    $("#preloader").hide();
                    return false;      
                }
            })
        })

   }
</script>
<script src="{{ asset('assets\js\custom\client\fundrequest.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@stop
@endsection
