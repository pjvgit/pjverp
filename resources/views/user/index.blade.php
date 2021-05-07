@extends('layouts.afterlogin')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>{{USER_TITLE}} Management</h1>
                </div>
                <div class="col-sm-2">
                    <a class="btn btn-success" href="{{ route('user.create') }}">
                            Create New {{USER_TITLE}}
                    </a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                   
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="employee-grid" class="table table-bordered table-striped responsive fixed-table-layout-user" >
                            <thead>
                                <tr>
                                    <th width="10%" >First Name</th> 
                                    <th width="10%" >Last Name</th>
                                    <th width="20%">Email</th>
                                    <th width="10%">User Type</th>
                                    <th width="10%" >Status</th>
                                    <th width="15%">Created Time</th>
                                    <th width="10%" class="text-center">Assigned Project</th>
                                    <th width="15%" class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>


<div id="DeleteModal" class="modal fade text-danger" role="dialog">
    <div class="modal-dialog ">
        <!-- Modal content-->
        <form action="" id="deleteForm" method="post">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">Delete Confirmation?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <p>Do you really want to delete these records? This process cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <button type="button" class="btn btn-success" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal"
                            onclick="formSubmit()">Yes, Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-js-script')
<script type="text/javascript">
    function deleteData(id) {
        var id = id;
        var url = '{{ route("user.destroy", ":id") }}';
        url = url.replace(':id', id);
        $("#deleteForm").attr('action', url);
    }

    function formSubmit() {
        $("#deleteForm").submit();
    }
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });


    $(document).ready(function() {
        dataTable =  $('#employee-grid').DataTable( {
        serverSide: true,
        responsive: false,
        processing: true,
        stateSave: true,
        "order": [[5, "desc"]],
        "ajax":{
            url :"user/loadUser", // json datasource
            type: "post",  // method  , by default get
            error: function(){  // error handling
                $(".employee-grid-error").html("");
                $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                $("#employee-grid_processing").css("display","none");
            }
        },
        pageResize: true,  // enable page resize
        pageLength:{{USER_PER_PAGE_LIMIT}},
        columns: [
            { data: 'first_name'}, 
            { data: 'last_name'},
            { data: 'email' },
            { data: 'is_admin' },
            { data: 'status'},
            { data: 'created_new_date'},
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(7)', nRow).html('<div class="text-center"><a data-toggle="tooltip" data-placement="bottom" title="View" class="btn btn-primary btn-sm" href="'+baseUrl+'/user/'+ aData.id +'"> <i class="fas fa-eye"></i></a>&nbsp;<a data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-info btn-sm" href="'+baseUrl+'/user/'+ aData.id + '/edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;<a data-toggle="modal" onclick="deleteData(' + aData.id + ')" data-target="#DeleteModal" data-placement="bottom" title="Delete" href="javascript:;" class="btn btn-danger btn-sm"><i data-toggle="tooltip" data-placement="bottom" title="Delete" class="fa fa-trash"></i> </a></div>');
                
                if (aData.status == 1) {
                    $('td:eq(4)', nRow).html('<div class="text-center"><button class="btn btn-success btn-sm btn-padding" onclick="changeStatus(' + aData.id + ')">Active</button></div>');
                } else {
                    $('td:eq(4)', nRow).html('<div class="text-center"><button class="btn btn-danger btn-sm btn-padding" onclick="changeStatus(' + aData.id + ')">Inactive</button></div>');
                } 
                if (aData.is_admin == 1) {
                    $('td:eq(3)', nRow).html('Admin');
                } else {
                    $('td:eq(3)', nRow).html('User');
                }
                 $('td:eq(6)', nRow).html('<div class="text-center"><a data-toggle="tooltip" data-placement="bottom" title="View" class="btn btn-primary btn-sm" href="'+baseUrl+'/project?id='+ aData.decode_id +'"> View</a></div>');
				
            },
			
            
        });

        

    });
    				
    function changeStatus(id) {
            $.ajax({
                method: 'POST',
                url: "users/changeStatus",
                data: {
                    id: id,
                },
                success: function (data, status) {
                    var json = JSON.parse(data);
                    dataTable.ajax.reload(null, false);
                },
            })
        }
</script>
@stop
