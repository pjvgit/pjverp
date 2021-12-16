@extends('admin_panel.layouts.master')
@section('page-title', 'User Info')
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
    <a href="{{ route('admin/userlist') }}" class=""><span class="text-info">Back</span></a>
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
            @include('admin_panel.users.tab_menu')
            <div class="tab-pane p-2 fade <?php if(Route::currentRouteName()=="admin/userlist/info"){ echo "active show"; } ?>" id="profileBasic" role="tabpanel"
                        aria-labelledby="profile-basic-tab">
                        <div id="contact_info_page" style="display: block;">
                            <div class="align-items-md-start w-100">
                                <div class="p-2 contact-card">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-4 pt-xs-3 pt-md-0">
                                            <h5>Contact Information</h5>
                                            
                                            <table class="table table-borderless table-sm custom-table">
                                                <tbody>
                                                    <tr>
                                                        <th>Name</th>
                                                        <td id="contact-name">
                                                            <?php echo ucfirst($userProfile->first_name);?>
                                                            <?php echo  ucfirst($userProfile->last_name);?></td>
                                                    </tr>

                                                    <tr>
                                                        <th>Company</th>
                                                        <td id="company-names">
                                                          
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Job Title</th>
                                                        <td id="job-title">{{$UsersAdditionalInfo['job_title']}}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Group</th>
                                                        <td id="contact-group">{{$UsersAdditionalInfo['group_name']}}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>Created</th>
                                                        <td id="contact-group">Created {{$userProfile->created_date_new}} by <a title="<?php echo $userProfileCreatedBy[0]->ptitle;?>" href="{{ route('admin/stafflist/info', $userProfileCreatedBy[0]->decode_id) }}"><?php echo $userProfileCreatedBy[0]->full_name;?></a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-xs-12 col-md-4 pt-xs-3 pt-md-0">
                                            <h5>Email Address</h5>
                                            <div id="client-email">
                                                <a href="mailto:{{$userProfile->email}}">
                                                    {{$userProfile->email}}
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-md-4 pt-xs-3 pt-md-0 d-print-none">
                                            <h5>Client Portal Access</h5>
                                            <div class="client-portal-access-div" >
                                            <?php if($UsersAdditionalInfo['client_portal_enable']==0){?>
                                            <div id="client_login_switch">
                                                <div id="client_login_disabled" style="">
                                                    <div class="client-login-disabled-info">
                                                        No
                                                    </div>
                                                </div>
                                            </div>
                                            <?php }else{?>
                                                <div id="client_login_switch">
                                                <div id="client_login_disabled" style="">
                                                    <div class="client-login-disabled-info">
                                                        Yes
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-xs-12 col-md-4 pt-xs-3 pt-md-0">
                                            <h5>Phone Numbers</h5>
                                            <table class="table table-borderless table-sm custom-table">
                                                <tbody>
                                                    <tr id="client-home-phone">
                                                        <th>Home</th>
                                                        <td>{{$userProfile->home_phone}}</td>
                                                    </tr>

                                                    <tr id="client-work-phone">
                                                        <th>Work</th>
                                                        <td>{{$userProfile->work_phone}}</td>
                                                    </tr>

                                                    <tr id="client-cell-phone">
                                                        <th>Cell</th>
                                                        <td>{{$userProfile->mobile_number}}</td>
                                                    </tr>

                                                    <tr id="client-fax-phone">
                                                        <th>Fax</th>
                                                        <td>{{$UsersAdditionalInfo['fax_number']}}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-xs-12 col-md-8 pt-xs-3 pt-md-0">
                                            <h5>Address</h5>
                                            <div id="client-address">
                                                {{$userProfile->street}}<br>
                                                {{$UsersAdditionalInfo['address2']}}<br>
                                                {{$userProfile->city}}, {{$userProfile->state}}<br>
                                                {{$userProfile->countryname}}, {{$userProfile->postal_code}} <br>

                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-6 pt-xs-3 pt-md-0">
                                            <h5>Other Information</h5>
                                            <table class="table table-borderless table-sm custom-table">
                                                <tbody>
                                                    <tr>
                                                        <th>Birthday</th>
                                                        <td id="client-birthday">
                                                            <?php if($UsersAdditionalInfo['dob']!=NULL){
                                                            echo date('m/d/Y',strtotime($UsersAdditionalInfo['dob']));
                                                             } ?>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>License</th>
                                                        <td id="client-license-number">
                                                            {{$UsersAdditionalInfo['driver_license']}}
                                                            <?php 
                                                            if($UsersAdditionalInfo['license_state']!=''){
                                                                echo "(".$UsersAdditionalInfo['license_state'].")";
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>Website</th>
                                                        <td id="client-website">
                                                            <a href="{{$UsersAdditionalInfo['website']}}">
                                                                {{$UsersAdditionalInfo['website']}} </a>
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
    </div>
</div>
@endsection
@section('page-js')
<script>
</script>
@endsection