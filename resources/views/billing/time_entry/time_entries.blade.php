@extends('layouts.master')
@section('title', 'Time & Billing Dashboard')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 

$c=$i=$from=$to=$type=$st=""; 
if(isset($_GET['c'])){
    $c= $_GET['c'];
}
if(isset($_GET['i'])){
    $i= $_GET['i'];
}
if(isset($_GET['from'])){
    $from= $_GET['from'];
}
if(isset($_GET['to'])){
    $to= $_GET['to'];
}
if(isset($_GET['type'])){
    $type= $_GET['type'];
}
if(isset($_GET['st'])){
    $st= $_GET['st'];
}
?>
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Time Entries
                    </h3>
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/time_entries')}}?i=o&type={{$type}}"
                                class="nav-link <?php if(isset($_GET['i'])  && $_GET['i']=='o') echo "active"; ?> ">Open</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/time_entries')}}?i=i&type={{$type}}"
                                class="nav-link <?php if(isset($_GET['i']) && $_GET['i']=='i') echo "active"; ?>">Invoiced</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/time_entries')}}?i=a&type={{$type}}"
                                class="nav-link <?php if(isset($_GET['i']) && $_GET['i']=='a') echo "active"; ?>">All
                                Entries</a>
                        </li>
                    </ul>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <button onclick="printEntry();return false;" class="btn btn-link">
                            <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top" title="" data-original-title="Print"></i>
                            <span class="sr-only">Print This Page</span>
                          </button>
                            <a data-toggle="modal" data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;">
                            <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                                onclick="loadTimeEntryPopup();">Add Time Entry</button></a>
                    </div>

                </div>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <input type="hidden" name="i" value="{{$i}}">
                    <input type="hidden" name="type" value="{{$type}}">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Filter By Case</label>
                            <select id="c" name="c" class="form-control custom-select col filterbycase formSubmit">
                                <option></option>
                                @foreach($case as $k=>$v)
                                    <option <?php if($c==$v->cid){ echo "selected=selected"; } ?>  value="{{$v->cid}}">{{$v->ctitle}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Search Term </label>
                            <input type="text" class="form-control" id="st" name="st" value="{{$st}}"
                                placeholder="Description or Activity" />
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Date Range From</label>
                            <input type="text" class="form-control datepicker" autocomplete="off" id="daterange" name="from" value="{{$from}}"
                                placeholder="Start Date" />
                        </div>

                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Date Range To</label>
                            <input type="text" class="form-control datepicker" autocomplete="off" id="daterange" name="to" value="{{$to}}"
                                placeholder="End Date" />
                        </div>
                        <div class="col-md- form-group mb-3 mt-3 pt-2">
                            <button class="btn btn-info btn-rounded m-1" type="submit">Filter</button>
                        </div>
                        <div class="col-md-1 form-group mb-3 mt-3 pt-2">
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link resetClear">
                                <a  href="{{route('bills/time_entries')}}?i={{$i}}&type={{$type}}">Reset Dates</a>
                            </button>
                        </div>
                        <div class="col-md-2 form-group mb-3 pt-4">
                            <ul class="d-inline-flex nav nav-pills">
                                <li class="d-print-none nav-item">
                                    <a href="{{route('bills/time_entries')}}?i={{$i}}&type=own" class="nav-link <?php if($type=="own"){ echo "active"; }?> " >My Entries</a>
                                </li>
                                <li class="d-print-none nav-item">
                                    <a href="{{route('bills/time_entries')}}?i={{$i}}&type=all" class="nav-link <?php if($type=="all" || $type==""){ echo "active"; }?>">All Entries</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%">id</th>
                                <th width="10%">Date</th>
                                <th width="15%">Activity</th>
                                <th width="5%">Duration</th>
                                <th width="15%">Description</th>
                                <th width="5%">Rate</th>
                                <th width="5%">Total</th>
                                <th width="5%">Status</th>
                                <th width="15%">User</th>
                                <th width="15%">Case</th>
                                <th width="10%" class="text-center"></th>
                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div>
    
</div>
<!-- Modals -->

<div id="loadTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntry">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadEditTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditTimeEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteTimeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteTimeEntryFormSubmit" id="deleteTimeEntryFormSubmit" name="deleteTimeEntryFormSubmit" method="POST">
            @csrf
            <input type="hidden" value="" name="entry_id" id="delete_entry_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Time Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this time entry?
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
@include('commonPopup.add_case')

<!--Over-->
<style>
.nav-pills .nav-link.active, .nav-pills .show>.nav-link {
    color: #2c2c2c;
    background-color: #cde2f2;
}
.pagination{
	width: 80%;
	float: right;
}
</style>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
    
        $('.formSubmit').change(function() {
            this.form.submit();
        });
        
        $(".country").select2({
            placeholder: "Select country",
            theme: "classic",
            allowClear: true
        });
        $(".filterbycase").select2({
            placeholder: "Filter time entries by case",
            theme: "classic",
            allowClear: true
        });
        $(".filterbyterm").select2({
            placeholder: "Description or Activity",
            theme: "classic",
            allowClear: true
        });
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'startDate': "dateToday",
            'todayHighlight': true,
            'orientation': "bottom",
        });


        var timeEntryGrid =  $('#timeEntryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/time_entries/loadTimeEntry", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c' : '{{$c}}','from' : '{{$from}}','to' : '{{$to}}','type':'{{$type}}' ,'st':'{{$st}}','i':'{{$i}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'date_format_new'},
                { data: 'activity_title'},
                { data: 'duration'},
                { data: 'description','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id'},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    if(aData.rate_type=="flat"){
                        $('td:eq(4)', nRow).html('<div class="text-left">Flat</div>');
                    }else{
                        $('td:eq(4)', nRow).html('<div class="text-left">'+aData.entry_rate+'/'+aData.rate_type+'</div>');
                    }
                    $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.calculated_amt+'</div>');
                    $('td:eq(6)', nRow).html('<div class="text-left">Open</div>');
                    // $('td:eq(7)', nRow).html('<div class="text-left">'+aData.user_name+'</div>');
                    
                    $('td:eq(7)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.user_name+'</a></div>');

                    if(aData.ctitle!=null){
                        $('td:eq(8)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/time_entries">'+aData.ctitle+'</a></div>');

                    }else{
                        $('td:eq(8)', nRow).html('<div class="text-left"></div>');
                    }

                    $('td:eq(9)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#loadEditTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditTimeEntryPopup('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a><a data-toggle="modal"  data-target="#deleteTimeEntry" data-placement="bottom" href="javascript:;"  onclick="deleteTimeEntry('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></div>');
                },
                "initComplete": function(settings, json) {
                  
                }
        });
        $('#loadTimeEntryPopup').on('hidden.bs.modal', function () {
            timeEntryGrid.ajax.reload(null, false);
        });

        $(".resetClear").click(function(){
            timeEntryGrid.clear().draw();
        })
        $('#deleteTimeEntryFormSubmit').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteTimeEntryFormSubmit').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteTimeEntryFormSubmit").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/deleteTimeEntryForm", // json datasource
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
                        if(res.from=="timesheet"){
                            $("#deleteTimeEntry").modal("hide");
                        }else{
                            window.location.reload();
                            afterLoader();
                        }
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
        });
        // timeEntryGrid.clear().draw();
    });


    function loadTimeEntryPopup() {        
        localStorage.setItem("case_id",'');
        $("#addTimeEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopup", // json datasource
                data: {},
                success: function (res) {
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadEditTimeEntryPopup(id) {
        localStorage.setItem("case_id",'');
        $("#loadEditTimeEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadEditTimeEntryPopup", // json datasource
                data: {'entry_id':id},
                success: function (res) {
                    $("#loadEditTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteTimeEntry(id) {
       $("#deleteTimeEntry").modal("show");
       $("#delete_entry_id").val(id);
   }
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
                url: baseUrl + "/bills/invoices/printTimeEntry",
                data :{ 'c' : '{{$c}}','from' : '{{$from}}','to' : '{{$to}}','type':'{{$type}}' ,'st':'{{$st}}','i':'{{$i}}','current_page':current_page,'length':length,'orderon':orderon },
                success: function (res) {
                    window.open(res.url, '_blank');
                    window.print();

                    $("#preloader").hide();
                }
            })
        })

   }
   function loadCaseDropdown(){
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/loadCaseList", // json datasource
            data: {'case_id':localStorage.getItem("case_id")},
            success: function (res) {
                $("#case_or_lead").html(res);
            }
        })
   }
</script>
@stop
@endsection
