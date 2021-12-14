@extends('admin_panel.layouts.master')
@section('page-title', 'User Info')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
@endsection
@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h1>User List</h1>
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">    
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body p-0">
            <table class="display table table-striped table-bordered" id="ClientListGrid" style="width:100%" data-url="">
                <thead>
                    <tr>
                        <th class="" style="cursor: initial;">First Name</th>
                        <th class="" style="cursor: initial;">Last Name</th>
                        <th class="" style="cursor: initial;">Email</th>
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
        var url = "{{ route('admin/loadusers') }}";
        $('#'+tableName2).DataTable({
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
                { "data": "action", orderable: false, searchable: false },
            ],
            initComplete: function () {
                $("[data-toggle=popover]").popover();
            },
            "ajax": {
                url: url,
                type: "get", // method  , by default get
                data: function ( d ) {
                    d.client_id = $('#'+tableName2).attr('data-client-id');
                    d.case_id = ($(".select2-case").find(':selected').data('trust-type') == 'case') ? $(".select2-case").val() : '';
                    d.bank_account = $(".select2-bank-account").val();
                },
                "error":function(){
                    // window.location.reload();
                }
            },
        });
    });
</script>
@endsection