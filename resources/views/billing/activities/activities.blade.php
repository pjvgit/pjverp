@extends('layouts.master')
@section('title', 'Saved Activities - Billing')
@section('main-content')

@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Saved Activities</h3>
                    
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <button onclick="printEntry();return false;" class="btn btn-link"> {{-- id="btnPrint" --}}
                            <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top" title=""
                                data-original-title="Print"></i>
                            <span class="sr-only">Print This Page</span>
                        </button>
                        @can('billing_add_edit')
                        <a data-toggle="modal" data-target="#addActivity" data-placement="bottom"
                            href="javascript:;">
                            <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                                onclick="addActivity();">New Activity</button></a>
                        @endcan
                    </div>

                </div>
                </form>
                

                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="activityTab" style="width:100%">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th width="25%">Activity</th>
                                <th class="text-nowrap"  width="25%" >Default Description</th>
                                <th class="text-nowrap"  width="5%">Time Entries</th>
                                <th width="5%">Expenses</th>
                                <th class="text-nowrap" width="5%">Total Hours</th>
                                <th class="text-nowrap" width="5%">Flat Fee</th>
                                <th class="text-nowrap" width="20%">Created By</th>
                                <th class="d-print-none" width="10%">
                                  <span class="sr-only">Actions</span>
                                </th>
                              </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->

<div id="addActivity" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">New Activity</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="addActivityArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editActivity" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Update Activity</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div  class="showError" style="display:none"></div>
                <div id="editActivityArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteActivityPopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteActivityPopupForm" id="deleteActivityPopupForm" name="deleteActivityPopupForm" method="POST">
            @csrf
            <input type="hidden" value="" name="activity_id" id="delete_activity_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this activity? This will not affect any existing time entries or expenses.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<textarea name="hiddenData" style="display: none;" id="hiddenData"></textarea>
<!--Over-->
<style>
.pagination {
        width: 80%;
        float: right;
    }
    .buttons-print{
        display: none;
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

        $('.dropdown-toggle').dropdown();

       
        var activityTab =  $('#activityTab').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/activities/loadActivity", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c' :'c'},
                error: function(){  // error handling
                    $(".activityTab-error").html("");
                    // $("#activityTab").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#activityTab_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
        
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<div class="text-left">'+aData.title+'</div>');
                    if(aData.default_description==null){
                        var g='<i class="table-cell-placeholder"></i>';
                    }else{
                        var g=aData.default_description;
                    }
                    $('td:eq(1)', nRow).html('<div class="text-left">'+g+'</div>');
                    $('td:eq(2)', nRow).html('<div class="text-left">'+aData.time_entry+'</div>');
                    $('td:eq(3)', nRow).html('<div class="text-left">'+aData.expense_counter+'</div>');
                    $('td:eq(4)', nRow).html('<div class="text-left">'+aData.total_hours+'</div>');
                    
                    if(aData.flat_fees=="0.00"){
                        var ff='<i class="table-cell-placeholder"></i>';
                    }else{
                        var ff=aData.flat_fees;
                    }
                    $('td:eq(5)', nRow).html('<div class="text-left">'+ff+'</div>');
                    $('td:eq(6)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.contact_name+'</a></div>');
                    @can('billing_add_edit')
                    var deleteAction = '';
                    @can('delete_items')
                    deleteAction = '<span data-toggle="tooltip" data-placement="top" title="Delete"><a data-toggle="modal"  data-target="#deleteActivityPopup" data-placement="bottom" href="javascript:;"  onclick="deleteActivityPopupFun('+aData.id+');"> <i class="fas fa-trash align-middle "></i> </span></a>';
                    @endcan
                    $('td:eq(7)', nRow).html('<div class="text-center"><span data-toggle="tooltip" data-placement="top" title="Edit"><a data-toggle="modal"  data-target="#editActivity" data-placement="bottom" href="javascript:;"  onclick="editActivityOpen('+aData.id+');"><i class="fas fa-pen align-middle pr-3"></i></span></a> '+deleteAction+'</div>');
                    @else
                    $('td:eq(7)', nRow).html('');
                    @endcan

                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
                    // setPrintHtml(json)
                }
        });

        // $(document).on('click', '#btnPrint', function(){
        //     $(".buttons-print")[0].click(); //trigger the click event
        //     });
        $('#actionbutton').attr('disabled', 'disabled');

        $('#deleteActivityPopupForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteActivityPopupForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteActivityPopupForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/activities/deleteActivity", // json datasource
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
    });

    function addActivity() {
        $("#preloader").show();
        $("#addActivityArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/activities/newActivity", 
                data: {"new": "yes"},
                success: function (res) {
                    $("#addActivityArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function editActivityOpen(id) {
        beforeLoader();
        $("#preloader").show();
        $("#editActivityArea").html('');
        $("#editActivityArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/activities/editActivity", 
            data: {"id": id},
            success: function (res) {
                if(typeof(res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#editActivityArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#editActivityArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }
    function deleteActivityPopupFun(id) {
        $("#deleteActivityPopup").modal("show");
        $("#delete_activity_id").val(id);
    }
 
    function printEntry()
   {
        var info = $('#activityTab').DataTable().page.info();
        var current_page=info.page;
        var length=info.length;
        var orderon=$('#activityTab').dataTable().fnSettings().aaSorting;
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/printSavedActivity",
                data :{ 'current_page':current_page,'length':length,'orderon':orderon },
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

//    function setPrintHtml(json){
//      var htmlData='<html class="no-js" lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="robots" content="noindex"><link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet"><style>body{height:842px;width:795px;margin-left:auto;margin-right:auto;font-family:Nunito,sans-serif}table{border-collapse:collapse}th,td{padding:5px}</style></head><body style="padding:25px;"><h4>Saved Activities</h4><table class="display table table-striped table-bordered" id="timeEntryGrid" style="width:100%;" border="1"><thead><tr><th width="10%">Activity</th><th width="15%">Default Description</th><th width="5%">Time Entries</th><th width="15%">Expenses</th><th width="5%">Total Hours</th><th width="5%">Flat Fees</th><th width="5%">Created By</th></tr></thead><tbody>';
//         $.each(json.data, function (key, value) {
//             htmlData+='<tr style="padding-left: 4px;"><td scope="col" style="width: 10%;text-align:left; white-space: nowrap!important;">'+value.title+'</td><td scope="col" style="width: 15%;text-align:left; white-space: nowrap!important;">'+value.default_description+'</td><td scope="col" style="width: 5%;text-align:left; white-space: nowrap!important;"> '+value.time_entry+'</td><td scope="col" style="width: 15%;text-align:left;"> '+value.expense_counter+'</td><td scope="col" style="width: 5%;text-align:left; white-space: nowrap!important;">'+value.total_hours+'</td><td scope="col" style="width: 5%;text-align:left; white-space: nowrap!important;"> '+value.flat_fees+'</td><td scope="col" style="width: 5%;text-align:left; white-space: nowrap!important;">'+value.contact_name+'</td></tr>';
//         });
//         htmlData+='</tbody></table></body></html>';
//         $("#hiddenData").val(htmlData);
//    }
</script>
{{-- <script type="text/javascript">
    $("#btnPrint").on("click", function () {
        var divContents = $("#hiddenData").val();
        var printWindow = window.open();
        printWindow.document.write(divContents);
        printWindow.document.close();
        printWindow.print();
    });
</script> --}}
@stop
@endsection
