@extends('admin_panel.layouts.master')
@section('page-title', 'Staff Info')
@section('page-css')
@endsection
@section('main-content')
<div class="breadcrumb">
    <i class="fas fa-user-circle fa-2x"></i>
    <h2 class="mx-2 mb-0 text-nowrap">
        <?php echo ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name);?> {{ "(".$userProfile->user_title.")"}}
        <?php if($userProfile->user_status=="3"){?>
        <span class="text-danger">[ Inactive ]</span>
        <?php } 
            if($userProfile->user_status=="4"){?>
        <span class="text-danger">[ Archived ]</span>
        <?php } ?>
    </h2> 
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
                                        <th>Address</th>
                                        <td>
                                            <?php echo $userProfile->street;?>
                                            <?php echo $userProfile->apt_unit;?>
                                            <?php echo $userProfile->city ;?>
                                            <?php echo $userProfile->state;?>

                                            <?php echo $userProfile->postal_code;?>
                                            <?php echo $userProfile->countryname;?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Work Phone</th>
                                        <td id="phone">
                                            <?php echo $userProfile->work_phone;?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Home Phone</th>
                                        <td id="phone">
                                            <?php echo $userProfile->home_phone;?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Cell Phone</th>
                                        <td id="phone">
                                            <?php echo $userProfile->mobile_number;?>
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