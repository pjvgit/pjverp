@extends('layouts.master')
@section('title', config('app.name').' :: Profile')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); //
?>
<div class="breadcrumb">
    <h3>Settings & Preferences</h1>

</div>
<div class="separator-breadcrumb border-top"></div>

<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')

    </div>
    <div class="col-8">

        <div class="card common-settings mb-4">
            <h4 class="card-header">Common Settings</h4>
            <div class="card-body">
                <div class="row">
                    @can('add_firm_user')
                    <div class="col-3 text-center common-shortcut p-2">
                        <a data-toggle="modal"  data-target="#DeleteModal" data-placement="bottom" href="javascript:;"  onclick="loadStep1();">
                            <i class="i-Administrator text-32 mr-3" height="40"></i>
                            <div class="mt-1">Add a New User</div>
                        </a>
                    </div>
                    <div class="col-3 text-center common-shortcut p-2">
                        <a id="add-bulk-users-link" data-toggle="modal" data-target="#AddBulkUserModal"
                            data-placement="bottom" href="javascript:;" onclick="AddBulkUserModal();">
                            <i class="i-Add-User i-Plus text-32 mr-3" height="40"></i>
                            <div class="mt-1">Add Multiple Users</div>
                        </a>
                    </div>
                    @endcan
                    @can('edit_firm_user_permission')
                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="{{route('contacts/attorneys')}}">
                            <i class="i-Lock-2 text-32 mr-3" height="40"></i>

                            <div class="mt-1">Edit User Permissions</div>
                        </a> </div>
                    @endcan
                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-Network text-32 mr-3" height="40"></i>
                            <div class="mt-1">Set Up Workflows</div>
                        </a> </div>
                    @can('edit_custom_fields_settings')
                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-File-Clipboard-File--Text text-32 mr-3" height="40"></i>
                            <div class="mt-1">Set Up Custom Fields</div>
                        </a> </div>
                    @endcan
                    @can('manage_firm_and_billing_settings')
                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="{{route('billing/settings')}}">
                            <i class="i-Money-2 text-32 mr-3" height="40"></i>
                            <div class="mt-1">Accept Client Payments Online</div>
                        </a> </div>
                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="{{route('firms/setting')}}">
                            <i class="i-Settings-Window text-32 mr-3" height="40"></i>
                            <div class="mt-1">Edit Firm Info &amp; Settings</div>
                        </a> </div>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card active-users">
            <h4 class="card-header">Active Firm Users</h4>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody><?php
                        foreach($lastLoginUsers as $k=>$v){
                        ?>
                        <tr>
                            <td class="align-middle">
                                <img class="rounded-circle" alt="" src="{{ asset('assets/images/faces/default_avatar_64.svg') }}" width="32" height="32">
                            </td>
                            <td class="align-middle">

                                <a href="{{ route('contacts/attorneys/info', base64_encode($v->id)) }}"><?php echo $v->first_name;?>
                                    <?php echo $v->last_name;?></a>
                                <br>
                                <?php echo $v->user_title;?>
                            </td>
                            <td class="align-middle">
                                <?php if($v->last_login==null){
                                    echo "Never";
                                }else{
                                   echo date('m-d-Y h:i A',strtotime($v->last_login));
                                }
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('contacts/attorneys') }}">View all</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card mb-4">
            <h4 class="card-header">Profile Summary</h4>
            <div class="card-body">
                <div class="row no-gutters d-flex align-items-center">
                    <div class="col-3 col-md-auto">
                        @php
                            $authUser = auth()->user();
                        @endphp
                    @if(file_exists(public_path().'/images/users/'.$authUser->profile_image) && auth()->user()->profile_image!='' && auth()->user()->is_published=="yes")
                            <img class="rounded-circle border" alt=""
                            src="{{ asset('images/users/'.$authUser->profile_image) }}"
                            width="50" height="50" >
                    @else
                            <img class="rounded-circle" alt=""
                            src="{{ asset('assets/images/faces/default_avatar_64.svg') }}"
                            width="50" height="50">
                    @endif
                    </div>
                    <div class="col pl-3">
                        <span class="h6">Welcome, <?php if(isset(Auth::user()->first_name)){?>
                            {{Auth::user()->first_name}} {{Auth::user()->last_name}}
                            <?php } ?></span>
                        <br>
                        <a href="{{ route('load_profile') }}">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="AddBulkUserModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Bulk User Add</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="AddBulkUserModalArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
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
        </div>
    </div>
</div>


@endsection
@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $('.country').select2();

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
        
    });
    function AddBulkUserModal() {
        $("#AddBulkUserModalArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/dashboard/AddBulkUserModal", // json datasource
            data: 'bulkAdd',
            success: function (res) {
                $("#AddBulkUserModalArea").html(res);
                return false;
            }
        })
    }

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

@endsection

