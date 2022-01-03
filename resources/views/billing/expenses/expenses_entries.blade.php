@extends('layouts.master')
@section('title', 'Open - Expenses - Billing')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 

$c=$i=$from=$to=$type=""; 
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
?>
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Expenses
                    </h3>
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/expenses')}}?i=o&type={{$type}}"
                                class="nav-link <?php if(isset($_GET['i'])  && $_GET['i']=='o') echo "active"; ?> ">Open</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/expenses')}}?i=i&type={{$type}}"
                                class="nav-link <?php if(isset($_GET['i']) && $_GET['i']=='i') echo "active"; ?>">Invoiced</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/expenses')}}?i=a&type={{$type}}"
                                class="nav-link <?php if(isset($_GET['i']) && $_GET['i']=='a') echo "active"; ?>">All Entries</a>
                        </li>
                    </ul>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <button  onclick="printEntry()" class="btn btn-link">
                            <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top" title=""
                                data-original-title="Print"></i>
                            <span class="sr-only">Print This Page</span>
                        </button>
                        @can('billing_add_edit') 
                        <div id="bulk-dropdown" class="mr-2 actions-button btn-group">
                            <div class="mx-2">
                                <div class="btn-group show">
                                    <button class="btn btn-info m-1 dropdown-toggle" data-toggle="dropdown"
                                        id="actionbutton"  aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 ">
                                        <div class="card">
                                                <button type="button" tabindex="0" onclick="deleteBulkExpense()" role="menuitem"
                                                    class="bulk-mark-tasks-as-read dropdown-item"><span>Delete Expenses</span>
                                                </button>
                                                <button type="button" tabindex="0" onclick="bulkAssignCase()" role="menuitem" class="bulk-mark-tasks-as-complete dropdown-item"><span>Reassign Case</span>
                                                </button>
                                                <button type="button" tabindex="0" onclick="bulkAssignUser()" role="menuitem"
                                                        class="bulk-mark-tasks-as-complete dropdown-item">Reassign User</button>
                                                
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        @endcan
                        @can('billing_add_edit') 
                        <a data-toggle="modal" data-target="#loadExpenseEntryPopup" data-placement="bottom"
                            href="javascript:;">
                            <button disabled class="btn btn-primary btn-rounded m-1 leep" type="button" id="button"
                                onclick="loadExpenseEntryPopup();">Add Expense</button></a>
                        @endcan
                    </div>

                </div>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <input type="hidden" name="i" value="{{$i}}">
                    <input type="hidden" name="itype" value="{{$type}}">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-3 form-group mb-3">
                            <label for="picker1">Filter By Case</label>
                            <select id="c" name="c" class="form-control custom-select col filterbycase formSubmit">
                                <option value=""></option>
                                @foreach($case as $k=>$v)
                                <option <?php if($c==$v->cid){ echo "selected=selected"; } ?> value="{{$v->cid}}">
                                    {{$v->ctitle}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7 form-group mb-3">
                        </div>
                        <div class="col-md-2 form-group mb-3 pt-4">
                            <ul class="d-inline-flex nav nav-pills">
                                <li class="d-print-none nav-item">
                                    <a href="{{route('bills/expenses')}}?i={{$i}}&type=own"
                                        class="nav-link <?php if($type=="own"){ echo "active"; }?> ">My Entries</a>
                                </li>
                                <li class="d-print-none nav-item">
                                    <a href="{{route('bills/expenses')}}?i={{$i}}&type=all"
                                        class="nav-link <?php if($type=="all"){ echo "active"; }?>">All Entries</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="epensesEntryGridList" style="width:100%">
                        <thead>
                            <tr>
                                <th class="col-md-auto nosort"><input type="checkbox" id="checkall"></th>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Quantity</th>
                                <th>Cost</th>
                                <th class="w-25">Description</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th> User </th>
                                <th>Case</th>
                                @can('billing_add_edit') 
                                <th class="d-print-none">&nbsp;</th>
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

<div id="loadExpenseEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadExpenseEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadEditExpenseEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditExpenseEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteTimeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteTimeEntryForm" id="deleteTimeEntryForm" name="deleteTimeEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="entry_id" id="delete_entry_id">
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
                            Are you sure you want to delete this Expense?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
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

<div id="deleteBulkExpense" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteBulkExpenseForm" id="deleteBulkExpenseForm" name="deleteBulkExpenseForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Deleteing Selected Expenses</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete the selected expenses?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
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
<div id="assignCaseBulk" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Reassign Cases</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="assignCaseBulkArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="bulkAssignUserPopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Reassign Users</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="assignUserBulkArea">
                </div>
            </div>
        </div>
    </div>
</div>

@include('commonPopup.add_case')
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
        // Smart Wizard
        if(localStorage.getItem('saveNewExpenseEntryPopup') == 1){
            localStorage.setItem('saveNewExpenseEntryPopup', 0);
            $(".leep").trigger('click');
        }
        $("#button").removeAttr('disabled');
        $('.formSubmit').change(function () {
            this.form.submit();
        });
        $(".country").select2({
            placeholder: "Select country",
            theme: "classic",
            allowClear: true
        });
        $(".filterbycase").select2({
            placeholder: "Filter expense entries by case",
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
            startDate: "dateToday",
            'todayHighlight': true
        });
        $('.dropdown-toggle').dropdown();  
     
        var epensesEntryGridList =  $('#epensesEntryGridList').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "aoColumnDefs": [{'bSortable': false,'aTargets': ['nosort']}],
            "order": [[1, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/expenses/loadExpensesEntry", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c' : '{{$c}}','type':'{{$type}}' ,'i':'{{$i}}'},
                error: function(){  // error handling
                    // $(".epensesEntryGridList-error").html("");
                    // $("#epensesEntryGridList").append('<tbody class="employee-grid-error text-center"><tr><th colspan="11">No data found in the server</th></tr></tbody>');
                    $("#epensesEntryGridList_processing").css("display","none");
                }
            },
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
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
                { data: 'id','sorting':false},
                @can('billing_add_edit') 
                { data: 'id','sorting':false}@endcan],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    $('td:eq(0)', nRow).html('<div class="text-left"><input id="select-row-74" class="task_checkbox" onclick="changeAction()" type="checkbox" value="'+aData.id+'" class="task_checkbox" name="expenceId['+aData.id+']"></div>');
                    $('td:eq(4)', nRow).html('<div class="text-left">$'+aData.cost_value+'</div>');
                    $('td:eq(6)', nRow).html('<div class="text-left">$'+aData.calulated_cost+'</div>');
                    $('td:eq(7)', nRow).html('<div class="text-left">Open</div>');

                    $('td:eq(8)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.user_name+'</a></div>');

                    if(aData.ctitle!=null){
                        $('td:eq(9)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/expenses">'+aData.ctitle+'</a></div>');

                    }else{
                        $('td:eq(9)', nRow).html('<div class="text-left"></div>');
                    }
                    @can('billing_add_edit') 
                        var deleteAction = '';
                        @can('delete_items')
                            deleteAction = '<a data-toggle="modal"  data-target="#deleteTimeEntry" data-placement="bottom" href="javascript:;"  onclick="deleteTimeEntry('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a>';
                        @endcan
                    $('td:eq(10)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#loadEditExpenseEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditExpenseEntryPopup('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a>'+deleteAction+'</div>');
                    @endcan
                },
                "initComplete": function(settings, json) {

                    $('#checkall').on('change', function () {
                        $('.task_checkbox').prop('checked', $(this).prop("checked"));
                        if ($('.task_checkbox:checked').length == "0") {
                            $('#actionbutton').attr('disabled', 'disabled');
                        } else {
                            $('#actionbutton').removeAttr('disabled');
                         
                        }
                    });

                    $('.task_checkbox').change(function () { //".checkbox" change 
                        if ($('.task_checkbox:checked').length == $('.task_checkbox').length) {
                            $('#checkall').prop('checked', true);
                        } else {
                            $('#checkall').prop('checked', false);
                        }
                        if ($('.task_checkbox:checked').length == "0") {
                            $('#actionbutton').attr('disabled', 'disabled');
                        } else {
                            $('#actionbutton').removeAttr('disabled');
                           
                        }
                    });
                    $(".paginate_button").on("click",function(){
                        $('#checkall').prop('checked', false);
                    });
                }
        });
        $('#epensesEntryGridList').on( 'page.dt', function () {
            $('#checkall').prop('checked', false);
        });
        $('#actionbutton').attr('disabled', 'disabled');
        $('#loadExpenseEntryPopup').on('hidden.bs.modal', function () {
            epensesEntryGridList.ajax.reload(null, false);
        });
        $('#deleteTimeEntryForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteTimeEntryForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteTimeEntryForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/deleteExpenseEntryForm", // json datasource
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
        });

        $('#deleteBulkExpenseForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#deleteBulkExpenseForm').valid()) {
                beforeLoader();
                return false;
            }
            var array = [];
            $("input[class=task_checkbox]:checked").each(function (i) {
                array.push($(this).val());
            });
            var dataString = '';
            dataString = $("#deleteBulkExpenseForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/deleteBulkExpenseEntryForm", // json datasource
                 data: dataString + '&entry_id=' + JSON.stringify(array),
                beforeSend: function (xhr, settings) {
                    settings.data += '&bulk_delete=yes';
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
        });

        
    });
    function changeAction(){
        if ($('.task_checkbox:checked').length == $('.task_checkbox').length) {
            $('#checkall').prop('checked', true);
        } else {
            $('#checkall').prop('checked', false);
        }
        if ($('.task_checkbox:checked').length == "0") {
            $('#actionbutton').attr('disabled', 'disabled');
        } else {
            $('#actionbutton').removeAttr('disabled');
            
        }
    }

    function loadExpenseEntryPopup() {
        localStorage.setItem("case_id",'');
        $("#preloader").show();
        $("#loadExpenseEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/loadExpenseEntryPopup", // json datasource
                data: {},
                success: function (res) {
                    $("#loadExpenseEntryPopupArea").html('');
                    $("#loadExpenseEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadEditExpenseEntryPopup(id) {
        localStorage.setItem("case_id",'');
        $("#preloader").show();
        $("#loadEditExpenseEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/loadEditExpenseEntryPopup", // json datasource
                data: {'entry_id': id},
                success: function (res) {
                    $("#loadEditExpenseEntryPopupArea").html('');
                    $("#loadEditExpenseEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function deleteTimeEntry(id) {
        $("#deleteTimeEntry").modal("show");
        $("#delete_entry_id").val(id);
    }

    function deleteBulkExpense(id) {
        $("#deleteBulkExpense").modal("show");
        $("#delete_bulk_entry_id").val(id);
    }
    function bulkAssignCase() {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/bulkAssignCase", // json datasource
                data: {},
                beforeSend: function() {
                    $("#assignCaseBulk").modal("show");
                    $("#assignCaseBulkArea").html('<img src="{{LOADER}}""> Loading...');
                },
                success: function (res) {
                    $("#assignCaseBulkArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function bulkAssignUser() {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/bulkAssignUser", // json datasource
                data: {},
                beforeSend: function() {
                    $("#bulkAssignUserPopup").modal("show");
                    $("#assignUserBulkArea").html('<img src="{{LOADER}}""> Loading...');
                },
                success: function (res) {
                    $("#assignUserBulkArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function printEntry()
   {
        var info = $('#epensesEntryGridList').DataTable().page.info();
        var current_page=info.page;
        var length=info.length;
        var orderon=$('#epensesEntryGridList').dataTable().fnSettings().aaSorting;
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/printExpenseEntry",
                data :{ 'c' : '{{$c}}','type':'{{$type}}' ,'i':'{{$i}}','current_page':current_page,'length':length,'orderon':orderon },
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
