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
                        <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom"
                            href="javascript:;">
                            <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                                onclick="addRequestFundPopup();">Request Funds</button></a>
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
                                <th>Due </th>
                                <th> Date Sent</th>
                                <th class="nosort"> Viewed </th>
                                <th class="status-col-header nosort">Status</th>
                                <th class="d-print-none nosort"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->
{{-- Made common code for client/company/case and billing dashboard --}}
{{-- <div id="addRequestFund" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-xl ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Request Funds</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="addRequestFundArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editFundRequest" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Edit Request</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="editFundRequestArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteRequestFund" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteRequestedFundEntry" id="deleteRequestedFundEntry" name="deleteRequestedFundEntry" method="POST">
            @csrf
            <input type="hidden" value="" name="fund_id" id="delete_fund_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this request?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="sendFundReminder" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Send Reminder</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="sendFundReminderArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="addEmailToClient" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="addEmailtouser" id="addEmailtouser" name="addEmailtouser" method="POST">
            @csrf
            <input type="hidden" value="" name="client_id" id="client_id_for_email">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Enter Email</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showErrorOver" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            In order to send a request, they must have a valid email address. Please enter this information below and click "Save Email" to add it to their record and continue with this request.
                      </div>
                    </div>
                    <br>
                    <div class="form-group row">
                        <label for="due-date" class="col-4 pt-2">E-mail Addess</label>
                        <div class="date-input-wrapper col-8">
                            <div class="">
                                <div>
                                    <input class="form-control" id="email" maxlength="250" name="email" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Save Email</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div> --}}
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
        /* $("#addEmailtouser").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                
            },
            messages: {
                email: {
                    required: "Email is required",
                },
               
            },
        }); */
     
        var timeEntryGrid =  $('#timeEntryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
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
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<div class="text-left">'+aData.padding_id+'</div>');

                    if(aData.user.user_level == 2)
                        $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.contact_name+'</a></div>');
                    else
                        $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/companies/'+aData.client_id+'">'+aData.contact_name+'</a></div>');
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
                        if(aData.user.user_level == 2)
                            var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.user.full_name+' ('+aData.user.user_type_text+')</a>';
                        else
                            var clientLink='<a class="name" href="'+baseUrl+'/contacts/companies/'+aData.client_id+'">'+aData.user.full_name+' ('+aData.user.user_type_text+')</a>';
                    }
                    $('td:eq(3)', nRow).html('<div class="text-left">'+clientLink+'</div>');
                    $('td:eq(4)', nRow).html('<div class="text-left">$'+aData.amt_requested+'</div>');
                    $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.amt_paid+'</div>');
                    $('td:eq(6)', nRow).html('<div class="text-left">$'+aData.amt_due+'</div>');
                    $('td:eq(7)', nRow).html('<div class="text-left">'+aData.due_date_format+'</div>');
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
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();

                }
        });
        $('#actionbutton').attr('disabled', 'disabled');

        /* $('#deleteRequestedFundEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteRequestedFundEntry').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteRequestedFundEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/deleteRequestedFundEntry", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        }); */

        /* $('#addEmailtouser').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#addEmailtouser').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#addEmailtouser").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addEmailtouser", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showErrorOver').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showErrorOver').append(errotHtml);
                        $('.showErrorOver').show();
                        afterLoader();
                        return false;
                    } else {
                        $("#addEmailToClient").modal("hide");
                        refreshDetail();
                        afterLoader();
                        $(".text-muted").show();
                        $('#disabledArea').removeClass('retainer-request-opaque');
                        $('.submit').removeAttr("disabled");  
                    }
                },
                error: function (xhr, status, error) {
                $('.showErrorOver').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showErrorOver').append(errotHtml);
                $('.showErrorOver').show();
                afterLoader();
            }
            });
        }); */
    });
    // made common code for fund request from client/company/case and billing dashboard
    /* function addRequestFundPopup() {
        $("#preloader").show();
        $("#addRequestFundArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addRequestFundPopup", 
                data: {"user_id": ""},
                success: function (res) {
                    $("#email").val('');
                    $("#addRequestFundArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function editFundRequest(id) {
        $("#preloader").show();
        $("#editFundRequestArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/editFundRequest", 
                data: {"id": id},
                success: function (res) {
                    $("#editFundRequestArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteRequestFund(id, ele) {
        var paymentCount = $(ele).attr("data-payment-count");
        if(paymentCount > 0) {
            swal({
                title: 'Cannot Delete',
                text: 'This request cannot be deleted because there are payments associated with it.'
            });
        } else {
            $("#deleteRequestFund").modal("show");
            $("#delete_fund_id").val(id);
        }
    }
    function sendFundReminder(id) {
        $("#preloader").show();
        $("#sendFundReminderArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/sendFundReminder", 
                data: {"id": id},
                success: function (res) {
                    $("#sendFundReminderArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */
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
                    window.open(res.url, '_blank');
                    window.print();
                    $("#preloader").hide();
                }
            })
        })

   }
</script>
<script src="{{ asset('assets\js\custom\client\fundrequest.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@stop
@endsection
