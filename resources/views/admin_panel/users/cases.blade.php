@extends('admin_panel.layouts.master')
@section('page-title', 'User Cases')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
@endsection
@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h2 class="mx-2 mb-0 text-nowrap">
        <i class="fas fa-user-circle"></i>
        <?php echo ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name);?> {{ "(".$userProfile->user_title.")"}}
        <?php if($userProfile->user_status=="3"){?>
        <span class="text-danger">[ Inactive ]</span>
        <?php } 
            if($userProfile->user_status=="4"){?>
        <span class="text-danger">[ Archived ]</span>
        <?php } ?>
    </h2> 
    <a href="{{ route('admin/userlist') }} " class=""><span class="badge badge-info">Back</span></a>
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
            @include('admin_panel.staff.tab_menu')
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade <?php if(Route::currentRouteName()=="admin/stafflist/cases"){ echo "active show"; } ?>" id="contactBasic" role="tabpanel" aria-labelledby="contact-basic-tab">
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="StaffLinkedCaseList" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="50%">Name</th>
                                    <th width="20%">Role</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Hourly Rate</th>
                                    <th width="1"></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
<script>
    $(document).ready(function() {
        // For trust history list
        var tableName2 = 'StaffLinkedCaseList';
        var url = "{{ route('admin/stafflist/loadStaffCase') }}";
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
                { "data": "case_title"},
                { "data": "user_title"},   
                { "data": "status"},   
                { "data": "hourly_rate"},                
                { "data": "action", orderable: false, searchable: false },
            ],
            initComplete: function () {
                $("[data-toggle=popover]").popover();
            },
            "ajax": {
                url: url,
                type: "get", // method  , by default get
                data: function ( d ) {
                    d.staff_id = "{{$userProfile->id}}";
                },
                "error":function(){
                    // window.location.reload();
                }
            },
        });
    });
</script>
@endsection