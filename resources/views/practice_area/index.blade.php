@extends('layouts.master')
@section('title','Practice Area')
@section('main-content')

<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3>Practice Areas</h3>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <a data-toggle="modal" data-target="#AddModal" data-placement="bottom" href="javascript:;">
                            <button class="btn btn-primary btn-rounded m-1" type="button" onclick="AddModel();">New
                                Practice Areas</button>
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="data-grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%"></th>
                                <th width="50%">Practice Area</th>
                                <th width="20%">Active Cases</th>
                                <th width="20%">Created By</th>
                                <th width="9%"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="AddModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">New Practice Area</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-1">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="EditModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Practice Area</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-2">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {

        var dataTable =  $('#data-grid').DataTable( {
        searching: false,
        serverSide: true,
        responsive: false,
        processing: true,
        stateSave: true,
        "order": [[0, "desc"]],
        "ajax":{
            url :"loadPracticeArea", // json datasource
            type: "post",  // method  , by default get
            error: function(){  // error handling
                $(".data-grid-error").html("");
                $("#data-grid").append('<tbody class="data-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                $("#data-grid_processing").css("display","none");
            }
        },
        "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
        pageResize: true,  // enable page resize
        pageLength:25,
        columns: [
            { data: 'id'},
            { data: 'title'},
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
              
                    $('td:eq(0)', nRow).html('<a href="'+baseUrl+'/court_cases?pa='+aData.decode_primary_id+'">'+aData.title+'</a>'); 
                    $('td:eq(1)', nRow).html('<div class="text-left">'+aData.linked_case_count+'</div>'); 
                    if(aData.created_by_name==''){
                        $('td:eq(2)', nRow).html('<div class="text-left">System Default</div>'); 
                    }else{
                        $('td:eq(2)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.created_by_name+'</a></div>'); 
                    }
                    $('td:eq(3)', nRow).html('<a data-toggle="modal"  data-target="#EditModal" data-placement="bottom" href="javascript:;"  onclick="EditModel('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i> </a> &nbsp; <a href="javascript:;" onclick="onClickDelete('+aData.id+');" ><i class="fas fa-fw fa-trash text-black-50 ml-1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"  ></a>'); 
            },
        });
        $('#AddContactModal,#EditContactModal').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
            // window.location = baseUrl+"/contacts/attorneys";
        });
    });

    function onClickDelete(id) {
        swal({
            title: 'Are you sure you want to delete this practice area?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonClass: 'btn btn-success mr-5',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false
        }).then(function () {
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/case/deletePracticeArea",
                    data: {
                        "id": id
                    },
                    success: function (res) {
                        $("#groupModel").html(res);
                        $("#preloader").hide();
                        swal('Deleted!', 'Your practice area has been deleted.', 'success');
                        // $('#employee-grid').DataTable().ajax.reload();
                        window.location.reload();
                    }
                });
            });

        }, function (dismiss) {
            // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
            if (dismiss === 'cancel') {
                swal('Cancelled', 'Your practice area is safe :)', 'error');
            }
        });
    }

    function AddModel() {
        $("#preloader").show();
        $("#step-1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/loadAddPracticeArea", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $("#step-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function EditModel(id) {

        $("#preloader").show();
        $("#step-2").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/loadEditPracticeArea", // json datasource
                data: {
                    "id": id
                },
                success: function (res) {
                    $("#step-2").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

</script>
@stop
