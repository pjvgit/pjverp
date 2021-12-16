@extends('admin_panel.layouts.master')
@section('page-title', 'Staff Info')
@section('page-css')
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
    <a href="{{ route('admin/stafflist') }}"><span class="text-info">Back</span></a>
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
                <div class="tab-pane fade <?php if(Route::currentRouteName()=="admin/stafflist/info"){ echo "active show"; } ?>" id="profileBasic" role="tabpanel"
                    aria-labelledby="profile-basic-tab">
                    <div id="contact_info_page" style="display: block;">
                        <div class="d-md-flex align-items-md-start w-100">
                            <table class="table table-borderless table-responsive ml-xs-0 ml-md-4 col-md-9">
                                <tbody>
                                    <tr>
                                        <th>Name</th>
                                        <td class="d-flex">
                                            <?php echo $userProfile->first_name;?>
                                            <?php echo $userProfile->middle_name;?>
                                            <?php echo $userProfile->last_name;?>
                                            &nbsp;&nbsp;

                                            <a data-toggle='modal' data-target='#ShowColorPicker'
                                                data-placement='bottom' href='javascript:;'
                                                onclick='loadPicker({{$userProfile->decode_id}})'>
                                                <div
                                                    style="background-color:{{$userProfile->default_color}};width: 22px;height: 22px;">
                                                    &nbsp;</div>
                                            </a>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th>Title</th>
                                        <td id="user_title"><?php echo $userProfile->user_title;?></td>
                                    </tr>

                                    <tr>
                                        <th>Email</th>
                                        <td><a
                                                href="mailto:<?php echo $userProfile->email;?>"><?php echo $userProfile->email;?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created</th>
                                        <td id="phone">
                                        Created {{$userProfile->created_date_new}} 
                                        @if($userProfileCreatedBy != '')
                                            by <a href="{{ route('admin/stafflist/info', $userProfileCreatedBy->decode_id) }}">{{ $userProfileCreatedBy->full_name }}</a>
                                        @else
                                            by <a href="{{ route('admin/stafflist/info', $userProfile->decode_id) }}">{{ $userProfile->full_name }}</a>
                                        @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div> 
        </div>
    </div>
</div>
</div>
@endsection
@section('page-js')
<script>
</script>
@endsection