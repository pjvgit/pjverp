@extends('admin_panel.layouts.master')
@section('page-title', 'Staff List')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
@endsection
@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h1>Staff List</h1>
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row"> 
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <h5 class="">Filters</h5>
                <select class="form-control col-md-2" id="type" name="type">
                    <option value="">Select Type</option>
                    <option value="1">Attorney</option>
                    <option value="2">Paralegal</option>
                    <option value="3">Staff</option>
                </select>
            </div>
        </div>
    </div>  
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-0  mt-3 mb-4">
            <table class="display table table-striped table-bordered" id="ClientListGrid" style="width:100%" data-url="">
                <thead>
                    <tr>
                        <th class="" style="cursor: initial;">First Name</th>
                        <th class="" style="cursor: initial;">Last Name</th>
                        <th class="" style="cursor: initial;">Email</th>
                        <th class="" style="cursor: initial;">Firm Name</th>
                        <th class="" style="cursor: initial;">Type</th>
                        <th class="text-center d-print-none" style="cursor: initial;">Action</th>
                    </tr>
                </thead>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-js')

<script src="{{asset('assets/js/plugins/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/scripts/datatables.script.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // For trust history list
        var tableName2 = 'ClientListGrid';
        var url = "{{ route('admin/loadstaff') }}";
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
            "columns": [
                { "data": "first_name"},
                { "data": "last_name" },
                { "data": "email" },
                { "data": "firmName" },
                { "data": "type" },
                { "data": "action", orderable: false, searchable: false },
            ],
            initComplete: function () {
                $("[data-toggle=popover]").popover();
            },
            "ajax": {
                url: url,
                type: "get", // method  , by default get
                data: function ( d ) {
                    d.type = $("#type").val();
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
</script>
@endsection