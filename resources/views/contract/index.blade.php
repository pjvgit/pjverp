@extends('layouts.master')
@section('title', 'Firm User')
@section('main-content')
<?php
$userTitle = unserialize(USER_TITLE); 
?>
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div> 
    <div class="col-md-10">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3>Firm Users</h3>
                    @can('add_firm_user')
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;" > <button class="btn btn-primary btn-rounded m-1" type="button" onclick="loadStep1();">Add New User</button></a>
                    </div>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%">id</th>
                                <th width="1%"></th>
                                 <th width="15%">Name</th>
                                <th width="10%">Title</th>
                                <th width="12%">Permissions</th>
                                <th width="10%" class="nowrap">Active Cases</th>
                                <th width="10%" class="nowrap">Calendar Color</th>
                                <th width="15%" class="nowrap">Default Hourly Rate</th>
                                <th width="15%" class="text-center">Last Login</th>
                                <th width="10%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="DeleteModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Firm User</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <div id="smartwizard">
                            <ul>
                                <li class="text-center"><a href="#step-1">1<br /><small>Add New User</small></a></li>
                                <li class="text-center"><a href="#step-2">2<br /><small>Link to Cases </small></a></li>
                                <li class="text-center"><a href="#step-3">3<br /><small>Firm Level
                                            Permissions</small></a></li>
                                <li class="text-center"><a href="#step-4">4<br /><small>Access Permissions</small></a>
                                </li>
                            </ul>
                            <div>
                                <div id="step-1">
                                    
                                </div>
                                <div id="step-2">
                                
                                </div>
                                <div id="step-3">
                                

                                </div>
                                <div id="step-4">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary ml-2" id="next-btn" type="button">Create User</button>
            </div> --}}
        </div>
    </div>
</div>


<div id="ShowColorPicker" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Change Color</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="colorModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="ShowPrice" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Default Rate</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="rateModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="loadPermission" class="modal fade bd-example-modal-xl show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">All firm cases permission</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="permissionModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style> .modal { overflow: auto !important; }</style>

@endsection

@section('page-js')
<script type="text/javascript">
   
    $(document).ready(function() {
       
    var dataTable =  $('#employee-grid').DataTable( {
        serverSide: true,
        responsive: true,
        processing: true,
        stateSave: true,
        "order": [[0, "desc"]],
        "ajax":{
            url :"loadContract", // json datasource
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
            { data: 'id'},
            { data: 'name' }, 
            { data: 'user_title'},
            { data: 'email' },
            { data: 'first_name' },
            { data: 'default_rate'},
            { data: 'first_name'},
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                // $('td:eq(7)', nRow).html('<div class="text-center"><a data-toggle="tooltip" data-placement="bottom" title="View" class="btn btn-primary btn-sm" href="'+baseUrl+'/user/'+ aData.id +'"> <i class="fas fa-eye"></i></a>&nbsp;<a data-toggle="tooltip" data-placement="bottom" title="Edit" class="btn btn-info btn-sm" href="'+baseUrl+'/user/'+ aData.id + '/edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;<a data-toggle="modal" onclick="deleteData(' + aData.id + ')" data-target="#DeleteModal" data-placement="bottom" title="Delete" href="javascript:;" class="btn btn-danger btn-sm"><i data-toggle="tooltip" data-placement="bottom" title="Delete" class="fa fa-trash"></i> </a></div>');
                
                if (aData.profile_image == null) {
                    $('td:eq(0)', nRow).html('<div class="text-center"><i class="fas fa-user-circle fa-2x text-black-50"></i></div>');
                } else {
                    $('td:eq(0)', nRow).html('<div class="text-center"><img class="rounded-circle m-0 avatar-sm-table" style="width: 25px !important;height: 25px !important;" src="{{URL::asset("/images/users/")}}/'+aData.profile_image+'" alt=""></div>');
                }
                $('td:eq(1)', nRow).html('<a href="'+baseUrl+'/contacts/attorneys/'+ aData.decode_id +'" >'+aData.name+'</a>');  
                var editIcon = '';
                @can('edit_firm_user_permission')
                    editIcon = '<a data-toggle="modal"  data-target="#loadPermission" data-placement="bottom" href="javascript:;"  onclick="loadPermission('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a>';
                @endcan
                $('td:eq(3)', nRow).html('All firm cases  '+editIcon);    
                $('td:eq(4)', nRow).html('<div class="text-center"><a href="'+baseUrl+'/contacts/attorneys/'+ aData.decode_id +'/cases" >'+aData.staff_cases_count+'</a></div>'); 
                $('td:eq(5)', nRow).html("<a data-toggle='modal'  data-target='#ShowColorPicker' data-placement='bottom' href='javascript:;'  onclick='loadPicker("+aData.id+")' ccode='"+aData.default_color+"' ><div style='background-color:"+aData.default_color+";width: 22px;height: 22px;'>&nbsp;</div></a>"); 
                
                if (aData.default_rate == null) {
                    $('td:eq(6)', nRow).html('$0.00 <a data-toggle="modal"  data-target="#ShowPrice" data-placement="bottom" href="javascript:;"  onclick="loadRateBox('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i> </a>'); 
                } else {
                    $('td:eq(6)', nRow).html('$'+aData.default_rate+' <a data-toggle="modal"  data-target="#ShowPrice" data-placement="bottom" href="javascript:;"  onclick="loadRateBox('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i> </a>'); 
                }
                if (aData.last_login==null) {        
                    $('td:eq(7)', nRow).html('Never<br> <a href="javascript:;"  onclick="SendWelcomeEmail('+aData.id+');">Send welcome email</a>'); 
                }else{
                    $('td:eq(7)', nRow).html('<div class="text-center">'+aData.lastloginnewformate+'</div>');
                }
                if (aData.user_status == 1 || aData.user_status == 2) {
                    $('td:eq(8)', nRow).html('<div class="text-center">Active</div>');
                } else {
                    $('td:eq(8)', nRow).html('<div class="text-center">Inactive</div>');
                } 

                //  $('td:eq(6)', nRow).html('<div class="text-center"><a data-toggle="tooltip" data-placement="bottom" title="View" class="btn btn-primary btn-sm" href="'+baseUrl+'/project?id='+ aData.decode_id +'"> View</a></div>');
                
            },
        });

        // Toolbar extra buttons
        var btnFinish = $('<button></button>').text('Finish')
            .addClass('btn btn-info')
            .on('click', function () { alert('Finish Clicked'); });
        var btnCancel = $('<button></button>').text('Cancel')
            .addClass('btn btn-danger')
            .on('click', function () { $('#smartwizard').smartWizard("reset"); });
            

        // Smart Wizard
        $('#smartwizard').smartWizard({
            selected: 0,
            theme: 'default',
            transitionEffect: 'fade',
            showStepURLhash: false,
            enableURLhash: false,
            backButtonSupport: false, // Enable the back button support
            keyNavigation: false,
            toolbarSettings: {
                toolbarPosition: 'none',
                toolbarButtonPosition: 'end',
                toolbarExtraButtons: [btnFinish, btnCancel]
            },
            anchorSettings: {
                anchorClickable: false, // Enable/Disable anchor navigation
                enableAllAnchors: false, // Activates all anchors clickable all times
                markDoneStep: true, // Add done state on navigation
                markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                enableAnchorOnDoneStep: false // Enable/Disable the done steps navigation
            },
            
        });

        $('#DeleteModal').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
            // window.location = baseUrl+"/contacts/attorneys";
        });

        $('#ShowColorPicker').on('hidden.bs.modal', function () {
            // dataTable.ajax.reload();
            // window.location = baseUrl+"/contacts/attorneys";

        });
        $('#ShowPrice').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
        });

});


function loadStep1() {
    $("#preloader").show();
    $("#step-1").html('');
    $("#step-1").html('<img src="{{LOADER}}""> Loading...');

    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadStep1", // json datasource
            data: 'loadStep1',
            success: function (res) {
               $("#step-1").html(res);
                $("#preloader").hide();
            }
        })
    })
}

</script>
@stop
