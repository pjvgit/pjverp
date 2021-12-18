@extends('admin_panel.layouts.master')
@section('page-title', 'Staff List')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
@endsection
@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h2>Staff List for {{$userProfile->firmDetail->firm_name}}</h2>
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row"> 
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-0  mt-3 mb-4">
            <table class="display table table-striped table-bordered" id="ClientListGrid" style="width:100%">
                <thead>
                    <tr>
                    <th style="cursor: initial;"  class="nowrap">Name</th>
                    <th style="cursor: initial;"  class="nowrap">Title</th>
                    <th style="cursor: initial;"  class="nowrap">Active Cases</th>
                    <th style="cursor: initial;"  class="nowrap">Default Hourly Rate</th>
                    <th style="cursor: initial;"  class="nowrap">Status</th>
                    <th style="cursor: initial;"  class="nowrap">Action</th>
                    </tr>
                </thead>
            </table>
            
        </div>
    </div>
</div>
<div id="deactivateUser" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Deactivate  User</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body" id="part1">
                <div class="row"  >
                    <div class="col-md-12">
                        <div class="alert alert-info"><b>What happens when I deactivate a user?</b><ul><li>Deactivated users will not be able to login to {{config('app.name')}} .</li><li>You will not be charged for deactivated users.</li><li>Once deactivated, you cannot reactivate a user for 30 days.</li><li>Documents, tasks, events, notes, and billing associated with this user will remain in {{config('app.name')}} .</li><li>Calendar events will stop syncing with integrated calendars.</li></ul><a href="#" target="_blank" rel="noopener noreferrer">Learn more about deactivating a user</a></div>
                        <div><span class="font-weight-bold">Note: </span>You can reassign this user's tasks and events on the next screen to any active user. Alternatively tasks and events can be reassigned after a user is deactivated.
                        </div>
                    </div>
                    <div class="col-md-12" >
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary example-button m-1" id="loadNext" onClick="loadFinalStepDeactivate();" >Next: Confirm Deactivation</span></button>
                        </div>
                    </div>
                   <div class="form-group row">
                        <input type="hidden" class="staff_id" value=""/>
                        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="reactivateUser" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Reactivate user</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div>
                            <p>Are you sure you want to reactivate this user?</p>
                            <p>This user will be able to login to {{config('app.name')}} and You will be able to link this user to items in the system.</p>
                        </div>
                        <div>
                            <p>Reactivate this user will add additional charges to your bills.</p>
                        </div>                       
                    </div>
                    <div class="col-md-12" >
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary example-button m-1" onClick="reactivateStaff();" >Confirm Reactivation</span></button>
                        </div>
                    </div>
                   <div class="form-group row">
                        <input type="hidden" class="staff_id" value=""/>
                        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-js')

<script src="{{asset('assets/js/plugins/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/scripts/datatables.script.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // For trust history list
        var tableName2 = 'ClientListGrid';
        var url = "{{ route('admin/loadFirmStaffList') }}";
        var table = $('#'+tableName2).DataTable({
            stateSave:true,
            processing: true,
            // "order": [[1, "desc"]],
            "oLanguage": {
                "sEmptyTable":"No Record Found",
            },
            "lengthMenu": [10, 25, 50, 75, 100 ],
            "serverSide": true,
            "info": true,
            "autoWidth": true,
            "dom": "<'row'<'col-md-6'l>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            "columns": [
                { "data": "fullname"},
                { "data": "user_title" },
                { "data": "active_case_counter", orderable: false, searchable: false },
                { "data": "default_rate" },
                { "data": "user_status" },
                { "data": "action", orderable: false, searchable: false },
            ],
            initComplete: function () {
                $("[data-toggle=popover]").popover();
            },
            "ajax": {
                url: url,
                type: "get", // method  , by default get
                data: function ( d ) {
                    d.firm_name = '{{$userProfile->firm_name}}';
                },
                "error":function(){
                    // window.location.reload();
                }
            },
        });

        $("#type").on("change", function(){
            table.draw();
        });
    });
    $('body').on('click', '.reactivate-user', function() {
        var staff_id = $(this).attr('staff_id');
        $(".staff_id").val(staff_id);
        $("#reactivateUser").modal('show');
        
        reactivateStaff(staff_id);
    });
    $('body').on('click', '.deactivate-user', function() {
        var staff_id = $(this).attr('staff_id');
        $(".staff_id").val(staff_id);
        $("#deactivateUser").modal('show');
        // deactivateStaff(staff_id);
        
    });
    function loadFinalStepDeactivate() {
        $("#preloader").show();
        var url = "{{ route('admin/loadDeactivateUser') }}";
        var staff_id = $(".staff_id").val();
        $(function () {
            $.ajax({
                url:  url, // json datasource
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                data: {
                    "user_id": staff_id
                },
                success: function (res) {
                    $("#part1").html('');
                    $("#part1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function reactivateStaff(staff_id) {
        $("#preloader").show();
        var url = "{{ route('admin/reactivateStaff') }}";
        $(function () {
            $.ajax({
                url:  url, // json datasource
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                data: {
                    "user_id": staff_id
                },
                success: function (res) {
                    $(".staff_id").val('');
                    window.location.reload();
                    $("#reactivateStaff").modal('hide');
                    $("#preloader").hide();
                },
                error: function (res) {
                    $("#preloader").hide();
                }
            });
        })
    }

    function deactivateStaff() {
        $("#preloader").show();
        var url = "{{ route('admin/deactivateStaff') }}";
        var staff_id = $(".staff_id").val();
        $(function () {
            $.ajax({
                url:  url, // json datasource
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                data: {
                    "user_id": staff_id
                },
                success: function (res) {
                    window.location.reload();
                    $("#deactivateStaff").modal('hide');
                    $("#preloader").hide();
                    $(".staff_id").val('');
                },
                error: function (res) {
                    $("#preloader").hide();
                }
            });
        })
    }
    
</script>
@endsection