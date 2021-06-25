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
                    <div class="col-3 text-center common-shortcut p-2">
                        <a id="add-new-user-link" href="#add-new-user">
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

                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-Lock-2 text-32 mr-3" height="40"></i>

                            <div class="mt-1">Edit User Permissions</div>
                        </a> </div>

                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-Network text-32 mr-3" height="40"></i>
                            <div class="mt-1">Set Up Workflows</div>
                        </a> </div>

                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-File-Clipboard-File--Text text-32 mr-3" height="40"></i>
                            <div class="mt-1">Set Up Custom Fields</div>
                        </a> </div>

                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-Money-2 text-32 mr-3" height="40"></i>
                            <div class="mt-1">Accept Client Payments Online</div>
                        </a> </div>
                    <div class="col-3 text-center common-shortcut p-2">
                        <a href="#">
                            <i class="i-Settings-Window text-32 mr-3" height="40"></i>
                            <div class="mt-1">Edit Firm Info &amp; Settings</div>
                        </a> </div>
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
                        <img class="rounded-circle" alt=""
                            src="{{ asset('assets/images/faces/default_avatar_64.svg') }}"
                            width="50" height="50">
                    </div>
                    <div class="col pl-3">
                        <span class="h6">Welcome, <?php if(isset(Auth::user()->first_name)){?>
                            {{Auth::user()->first_name}} {{Auth::user()->last_name}}
                            <?php } ?></span>
                        <br>
                        <a href="{{ route('profile') }}">Edit Profile</a>
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

@endsection
@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $('.country').select2();
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
</script>

@endsection

