@extends('layouts.master')
@section('title', 'Contact Groups')
@section('main-content')
<div class="row">
    
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3>Contact Groups</h3>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <a data-toggle="modal"  data-target="#AddContactGroup" data-placement="bottom" href="javascript:;" > <button class="btn btn-primary btn-rounded m-1" type="button" onclick="AddContactGroup();">Add Contact Groups</button></a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%"></th>
                                <th width="15%">Group</th>
                                <th width="5%">Contacts</th>
                                <th width="12%">Created By</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="AddContactGroup" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact Group</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="groupModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="EditContactGroup" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Contact Group</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="editGreoup">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script type="text/javascript">
   
    $(document).ready(function() {
        
    var dataTable =  $('#employee-grid').DataTable( {
        searching:false,
        serverSide: true,
        responsive: false,
        processing: true,
        stateSave: true,
        "order": [[0, "desc"]],
        "ajax":{
            url :"loadClientgroup", // json datasource
            type: "post",  // method  , by default get
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
            { data: 'group_name'},
            { data: 'group_name' },
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) { 
                $('td:eq(1)', nRow).html(aData.count_attach_contact); 
               
                if(aData.id=="1"){
                    $('td:eq(2)', nRow).html('System Default'); 
                    $('td:eq(3)', nRow).html(''); 
                }else{
                    // $('td:eq(2)', nRow).html(aData.created_by_name); 

                    $('td:eq(2)', nRow).html('<a class="test-created-by-link pendo-case-info-status-created-by" href="'+baseUrl+'/contacts/attorneys/'+aData.createdby+'">'+aData.created_by_name+'</a>');

                    $('td:eq(3)', nRow).html('<a data-toggle="modal"  data-target="#EditContactGroup" data-placement="bottom" href="javascript:;"  onclick="loadEditBox('+aData.id+');"><i class="fas fa-pen align-middle"></i> </a> &nbsp; <a href="javascript:;" onclick="onClickDelete('+aData.id+');" ><i class="fas fa-fw fa-trash ml-1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete" ></a>'); 
                }
            },
        });


        $('#DeleteModal,#EditContactGroup').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
            // window.location = baseUrl+"/contacts/attorneys";
        });

        
        
    });

function onClickDelete(id){
    swal({
        title: 'Are you sure?',
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
                    url:  baseUrl +"/contacts/deleteClientGroup", 
                    data: {"group_id":id},
                    success: function (res) {
                        $("#groupModel").html(res);
                        $("#preloader").hide();
                        swal('Deleted!', 'Your contact group has been deleted.', 'success');
                        // $('#employee-grid').DataTable().ajax.reload();
                        window.location.reload();
                    }
                });
            });

        }, function (dismiss) {
            // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
            if (dismiss === 'cancel') {
                swal('Cancelled', 'Your contact group is safe :)', 'error');
            }
    });
}

function AddContactGroup() {
    
    $("#preloader").show();
    $("#groupModel").html('');
    $("#groupModel").html('<img src="{{LOADER}}""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadAddContactGroup", // json datasource
            data: 'loadStep1',
            success: function (res) {
               $("#groupModel").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function loadEditBox(id) {
    
    $("#preloader").show();
    $("#editGreoup").html('');
    $("#editGreoup").html('<img src="{{LOADER}}""> Loading...');

    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadEditClientGroup", // json datasource
            data: {"id":id},
            success: function (res) {
               $("#editGreoup").html(res);
                $("#preloader").hide();
            }
        })
    })
}
</script>
@stop
