@extends('layouts.master')
@section('title', 'Attorney Details')
@section('main-content')

<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
$userTitle = unserialize(USER_TITLE); 

?>
<div class="d-flex align-items-center  pb-4">
    <i class="fas fa-user-circle fa-2x"></i>
    <h2 class="mx-2 mb-0 text-nowrap">
        <?php echo $userProfile->first_name;?>
        <?php echo $userProfile->last_name;?>
        <?php if($userProfile->user_status=="3"){?>
        <span class="text-danger">[ Inactive ]</span>
        <?php } ?>
    </h2>
    <div class="ml-auto d-flex align-items-center d-print-none">
<button class="btn btn-link text-black-50 d-none d-md-block" onclick="window.print()">
      <i class="fas fa-print"></i> Print
    </button>        <a data-toggle="modal" data-target="#DeleteModal" data-placement="bottom" href="javascript:;"> <button
                class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="loadProfile();">Edit</button></a>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <div class="card mb-4">
            <div class="card-body">
                <span id="responseMain"></span>
                <nav class="test-general-settings-nav p-0 pt-0" role="navigation">
                    <ul class="nav nav-pills flex-column text-wrap">
                        <div class="mb-3">
                            <div class="pr-2">
                                <div class="mb-4">
                                    <div class="font-weight-bold">Cases:</div>
                                    <?php 
                                    if(!$case->isEmpty()){
                                        ?>
                                        <div class="client-detail-wrap-up">
                                        <?php foreach($case as $kk=>$vv){ ?>
                                                <a href="{{BASE_URL}}court_cases/{{$vv->case_unique_number}}/info">{{$vv->case_title}}</a><br>
                                        <?php } ?>
                                        </div>
                                    <?php }else{
                                        ?><div class="text-muted">No cases assigned to this firm member.</div><?php
                                    }
                                    ?>

                                </div>
                                
                                <div class="mb-4">
                                    <div class="font-weight-bold">Default Rate:</div>

                                    <a id="link_rate19630204" class="default-rate-link btn btn-link pl-0" href="#"
                                        onclick="setDefaultRate({{$userProfile->id}}, {{$userProfile->default_rate}});">$<span><?php echo ($userProfile->default_rate??'0.0');?></span>/
                                        hr</a>


                                </div>


                                <div id="billable-target-container" class="mb-4" data-billing-target=""
                                    data-attorney-id="19630204">
                                    <div>
                                        <div class="font-weight-bold">Billable Target:</div><a id="link-target-19630204"
                                            class="billable-target btn btn-link pl-0" href="#">Set</a>
                                    </div>
                                </div>


                                <div class="mb-4">
                                    <div class="font-weight-bold">Last Login:</div>
                                    <?php 
                                    if($userProfile->user_status!="3"){
                                        if($userProfile->last_login==null){?>
                                        <div class="text-muted">Never</div>
                                        <div id="send_welcome_link">
                                            <a class="resend-welcome-email" href="javascript:;"
                                                onclick="SendAnotherWelcomeEmail({{$userProfile->id}});">Send
                                                another welcome email</a>
                                        </div>
                                        <?php }else{ 
                                            echo $userProfile->last_login;
                                        }
                                    }else{
                                        echo "Disabled";
                                    } ?>
                                </div>

                                <div class="mb-4">
                                    <div class="font-weight-bold">Created:</div>
                                    <div class="small_details">
                                        Created <?php echo date('M j, Y', strtotime($userProfile->created_at));?><br>
                                        by <a title="<?php echo $userProfileCreatedBy[0]->ptitle;?>"
                                            href="https://m ylegal1.mycase.com/contacts/attorneys/19570320"><?php echo $userProfileCreatedBy[0]->name;?></a>
                                    </div>
                                    <hr>
                                </div>
                                <?php if($userProfile->user_status!="3"){?>
                                <div>
                                    <a class="btn btn-secondary mb-3 w-100 share_all_firm_doc" href="#">
                                        Share All Firm Documents
                                    </a>
                                    <div id="share_all_firm_doc_dialog" style="display: none;">
                                        <p>
                                            All Firm Documents are being shared with asdssadas asdsa. This may take a
                                            few minutes.
                                        </p>

                                        <div style="color: red; display: none;" id="share_all_firm_docs_error_msg">
                                            Unable to share with deactivated user.
                                        </div>
                                    </div>
                                    <a class="btn btn-secondary mb-2 w-100 share_all_templates" href="#">
                                        Share All Templates
                                    </a>
                                    <div id="share_all_template_dialog" class="d-none">
                                        <p>
                                            All Templates are being shared with asdssadas asdsa. This may take a few
                                            minutes.
                                        </p>

                                        <div class="text-danger d-none" id="share_all_template_error_msg">
                                            Unable to share with deactivated user.
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            </div>
                    </ul>
                </nav>
            </div>
        </div>

    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            @include('pages.errors')

            <div class="card-body">

                <ul class="nav nav-tabs" id="myTab" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts/attorneys/info"){ echo "active show"; } ?>" id="profile-basic-tab" 
                            href="{{URL::to('contacts/attorneys/'.$id)}}" aria-controls="profileBasic" aria-selected="true">Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php if(Route::currentRouteName()=="contacts/attorneys/cases"){ echo "active show"; } ?>" id="contact-basic-tab"
                        href="{{URL::to('contacts/attorneys/'.$id.'/cases')}}" role="tab" aria-controls="contactBasic" aria-selected="false">Cases</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts/attorneys/info"){ echo "active show"; } ?>" id="profileBasic" role="tabpanel"
                        aria-labelledby="profile-basic-tab">
                        <div id="contact_info_page" style="display: block;">
                            <div class="d-md-flex align-items-md-start w-100">
                                <div class="d-flex flex-column col-md-3 align-items-center d-print-none pb-4">
                                    <div>
                                        @if(file_exists( public_path().'/images/users/'.$userProfile->profile_image ) && $userProfile->profile_image!='')
                                       <img src="{{URL::asset('/public/images/users/')}}/{{$userProfile->profile_image}}" id="userDropdown" alt="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="round-img">
                              
                                        @else
                                        <i class="fas fa-user-circle fa-5x text-black-50"></i>
                                        @endif
                                                          
                                    </div>
                                    
                                   
                                    <?php 
                                    
                                    if($userProfile->user_status!="3"){?>
                                    <div class="mt-md-2">
                                        <div class="mb-4">
                                            <div class="alert alert-info mt-xs-0 mt-md-3">
                                                <span class="font-weight-bold">Note:</span>
                                                You can reassign this user's tasks and events on the next screen to any
                                                active user.
                                                Alternatively tasks and events can be reassigned after a user is
                                                deactivated.
                                            </div>
                                            
                                            <div class="text-center">
                                                <?php 
                                                // if($userProfile->user_title){
                                                //     $type=$userProfile->user_title;
                                                // }else{
                                                //     switch ($userProfile->user_level){
                                                //         case "1":
                                                //             $type="Attorney";
                                                //             break;
                                                //         case "2":
                                                //             $type="Paralegal";
                                                //             break;
                                                //         case "3":
                                                //             $type="Staff";
                                                //             break;
                                                //         default:
                                                //             $type="";
                                                //     } 
                                                // }
                                                $type=$userProfile->user_title;
                                                ?>
                                                <a data-toggle="modal" data-target="#deactivateUser" data-placement="bottom" href="javascript:;"> 
                                                    <button class="btn btn-outline-danger text-nowrap deactivate-user" type="button" ">Deactivate {{ $type}}</button>
                                                </a>
                                                    
                                            </div>
                                        </div>
                                    </div>
                                <?php } else {?>
                                    <div class="mt-md-2">
                                        <div class="alert alert-danger">
                                            <?php
                                            $CommonController= new App\Http\Controllers\CommonController();
                                            $d=strtotime($userProfile->updated_at. ' +30 days');
                                            $convertedStartDateTime= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',$d),Auth::User()->user_timezone);?>
                                          This <?php echo $userProfile->user_title;?> cannot be reactivated
                                          until  <?php
                                          echo $next_due_date = date('M j, Y h:i A', strtotime($convertedStartDateTime. ' +30 days')); ?>
                                          <br>
                                          <br>
                                          <a class="font-weight-bold alert-danger" href="#" target="_blank" rel="noopener noreferrer">
                                            Learn how to reactivate a user
                                          </a>
                                        </div>
                                        <div class="text-center">
                                            <button name="button" type="submit" class="btn btn-primary btn-rounded m-1 px-5" data-attorney-id="19771106">Reassign Tasks &amp; Events</button>
                                          </div>
                                       
                                    </div>
                                <?php } ?>
                                </div>

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
                                                    onclick='loadPicker({{$userProfile->id}})'>
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
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts/attorneys/cases"){ echo "active show"; } ?>" id="contactBasic" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="table-responsive">
                            <div class="d-flex align-items-center justify-content-end mb-2 d-print-none">
                                <div>
                                    <a data-toggle="modal" data-target="#linkBulkCasesToStaff" data-placement="bottom" href="javascript:;" class="btn btn-link text-black-50 link_all_cases" >Link to All Active Cases</a>
                                  </div>

                                <a data-toggle="modal" data-target="#addCaseLinkWithOption" data-placement="bottom" href="javascript:;"> 
                                    <button class="btn btn-primary btn-rounded m-1 px-5" type="button" >Add Case Link</button>
                                </a> 
                            </div>
                            <table class="display table table-striped table-bordered" id="StaffLinkedCaseList" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="1%"></th>
                                        <th width="50%">Name</th>
                                        <th width="20%">Role</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Hourly Rate</th>
                                        <th width="10%"></th>
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

<div id="ShowColorPicker" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
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

<div id="DeleteModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Firm User</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="LoadProfile">
                      
                    </div>
                </div>
            </div>
           
        </div>
    </div>
</div>

<div id="deactivateUser" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Deactivate  <?php echo $userProfile->first_name;?>
                    <?php echo $userProfile->last_name;?></h5>
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
                        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id='user_delete_dialog' style='display: none;'>
    <form id="user_delete_form"  name="user_delete_form" accept-charset="UTF-8" data-remote="true" method="post">
        @csrf
      <input type="hidden" name="id" id="user_delete_court_case_id" value="" />
      <input type="hidden" name="user_delete_contact_id" id="user_delete_contact_id" value="" />
  
      <p style='color: black;' id='last_lawyer'>
        By performing this action, this case will no longer have any linked firm users. Only those with admin-access will be able to link your firm's users to this case in the future.
      </p>
      <p style='color: black;'>
        Are you sure you want to remove
        <span id='user_delete_dialog_name' style='font-weight: bold;'></span>
        from the case
        <span id='user_delete_dialog_case' style='font-weight: bold;'></span>?
      </p>
      <p>
        It may take a few minutes for this to take effect.
        <span id='user_delete_loading' style='position: absolute; right: 8px; bottom: 3px; display: none;'>
          <img style="vertical-align: middle;" class="retina" src="{{ asset('images/ajax_arrows.gif') }}" width="16" height="16" /> Working
        </span>
      </p>
  
      <p id='user_delete_company' style='color: black;'>
        <input type="checkbox" name="user_delete_company_contacts" id="user_delete_company_contacts" value="1" checked="checked" />
        <label for="user_delete_company_contacts">Remove all company contacts assigned to this case.</label>
      </p>
  </form>
</div>

<div id="AddCaseModelUpdate" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <form class="createCase" id="createCase" name="createCase" method="POST">
                            @csrf
                            <div id="smartwizard">
                                <ul>
                                    <li class="text-center"><a href="#step-1">1<br /><small>Clients & Contacts</small></a></li>
                                    <li class="text-center"><a href="#step-2">2<br /><small>Case Details</small></a></li>
                                    <li class="text-center"><a href="#step-3">3<br /><small>Billing</small></a></li>
                                    <li class="text-center"><a href="#step-4">4<br /><small>Staff</small></a>
                                    </li>
                                </ul>
                                <div id="NewCaseDetail">
                                    <div id="step-1">
                                        
                                        <div class="col-md-12">
                                            <div class="form-group row">

                                                <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-3 ">
                                                    <a data-toggle="modal" data-target="#AddContactModal" data-placement="bottom" href="javascript:;">
                                                        <button class="btn btn-primary btn-rounded m-1" type="button" onclick="AddContactModal();">Add New
                                                            Contact</button></a>

                                                </label>
                                                <div class="text-center col-2">Or</div>
                                                <div class="col-7 form-group mb-3">
                                                    <select onchange="selectUser();" class="form-control user_type"  style="width:100%;" id="user_type" name="user_type"
                                                        data-placeholder="Search for an existing contact or company">
                                                        <option value="">Search for an existing contact or company</option>
                                                        <optgroup label="Client">
                                                            {{-- <?php
                                                        foreach($CaseMasterClient as $Clientkey=>$Clientval){
                                                            ?>
                                                            <option value="{{$Clientval->id}}">{{substr($Clientval->first_name,0,30)}}
                                                                {{substr($Clientval->last_name,0,30)}}</option>
                                                            <?php } ?> --}}
                                                            @forelse ($CaseMasterClient as $key => $item)
                                                            <option uType="client"  value="{{ $key }}" > {{ substr($item,0,200) }}</option>
                                                            @empty
                                                            @endforelse
                                                        </optgroup>
                                                        <optgroup label="Company">
                                                            {{-- <?php foreach($CaseMasterCompany as $Companykey=>$Companyval){ ?>
                                                            <option value="{{$Companyval->id}}">{{substr($Companyval->first_name,0,50)}}</option>
                                                            <?php } ?> --}}
                                                            @forelse ($CaseMasterCompany as $key => $item)
                                                            <option uType="company"  value="{{ $key }}"> {{ substr($item,0,200) }}</option>
                                                            @empty
                                                            @endforelse
                                                        </optgroup>
                                                    </select>
                                                    <span id="UserTypeError"></span>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div id="loadUserAjax"></div>

                                            <div class="m-2 empty-state text-center text-center-also">
                                                <p class="font-weight-bold">Start creating your case by adding a new or existing contact.</p>
                                                <div>All cases need at least one client to bill.</div><a href="#" rel="noopener noreferrer"
                                                    target="_blank">Learn more about adding a case and a contact at the same time.</a>
                                            </div>

                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div class="modal-footer">
                                                <div class="no-contact-warning mr-2" style="display:none;" id="beforetext">You must have a contact to bill.
                                                    Are you sure you want to continue?</div>
                                                <button type="button" id="beforebutton" onclick="callOnClick();"
                                                    class="btn btn-primary ladda-button example-button m-">Continue to case details
                                                </button>

                                                <button class="btn btn-primary ladda-button example-button m-1" type="button" id="submit" style="display:none;" onclick="StatusLoadStep2();">Continue without picking a contact
                                                </button>
                                                
                                                <button type="button" class="btn btn-primary ladda-button example-button m-1" id="submit_with_user" style="display:none;" type="submit" onclick="StatusLoadStep2();">Continue to case Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="step-2">
                                        <div class=" col-md-12">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case name</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control" id="case_name" value="" name="case_name" type="text"
                                                        placeholder="E.g. John Smith - Divorce">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case number</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control" id="case_number" value="" name="case_number" type="text"
                                                        placeholder="Enter case number">
                                                    <small>A unique identifier for this case.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="area_dropdown">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <select id="practice_area" name="practice_area" class="form-control custom-select col">
                                                        <option value="-1"></option>
                                                        <?php 
                                                            foreach($practiceAreaList as $k=>$v){?>
                                                        <option value="{{$v->id}}">{{$v->title}}</option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();" href="javascript:;">Add
                                                        new practice area</a></label>
                                            </div>
                                            <div class="form-group row" id="area_text">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <input class="form-control" id="practice_area_text" value="" name="practice_area_text" type="text"
                                                        placeholder="Enter new practice area">
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showDropdown();"
                                                        href="javascript:;">Cancel</a></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case stage
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_status" name="case_status" class="form-control custom-select col">
                                                        <option value="0"></option>
                                                        <?php 
                                                        foreach($caseStageList as $kcs=>$vcs){?>
                                                        <option value="{{$vcs->id}}">{{$vcs->title}}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Date opened</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_open_date" value="{{date('m/d/Y')}}" name="case_open_date" type="text"
                                                    placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Office
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_office" name="case_office" class="form-control custom-select col">
                                                        <option value="1">Primary</option>
                                                        
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="case_description" class="form-control" rows="5"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Statute of Limitations</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_statute" value="" name="case_statute" type="text"
                                                    placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row" id="addMoreReminder">
                                                <label for="sol_reminders" class="col-sm-2 col-form-label">SOL Reminders</label>
                                                <div class="col">
                                                    <div class="test-sol-reminders fieldGroup">
                                                        
                                                        <div>
                                                            <button type="button" class="btn btn-link pl-0 add-more">Add a reminder</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="fieldGroupCopy copy hide" style="display: none;">
                                                <div class="col-md-2 form-group mb-3">
                                                    <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  "><option value="email">email</option><option value="popup">popup</option></select>

                                                </div>
                                                <div class="col-md-2 form-group mb-3">
                                                    <input class="form-control" id="reminder_days" value="1" name="reminder_days[]" type="number" > 
                                                </div> <span class="pt-2">Days</span>
                                                <div class="col-md-2 form-group mb-3">   
                                                    <button class="btn remove" type="button"><i class="fa fa-trash"
                                                    aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <label class="switch pr-5 switch-success mr-3"><span>Completed</span>
                                                        <input type="checkbox" name="conflict_check" id="conflict_check"><span class="slider"></span>
                                                    </label>

                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check Notes</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="conflict_check_description" class="form-control" rows="5"></textarea>
                                                </div>
                                            </div>
                                        
                                            <div class="form-group row float-left">
                                                <button type="button" class="btn btn-outline-secondary m-1"  onclick="backStep1();">
                                                    <span class="ladda-label">Go Back</span>
                                                </button>
                                            </div>
                                            <div class="form-group row float-right">
                                                <button  type="button" class="btn btn-primary ladda-button example-button m-1"  onclick="StatusLoadStep3();">
                                                    <span class="ladda-label">Continue to Billing</span>
                                                </button>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                <div class="col-md-2 form-group mb-3">
                                                    <div class="loader-bubble loader-bubble-primary" id="innerLoader1" style="display: none;"></div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="step-3">
                                        <div class=" col-md-12">
                                            
                                            <div id="showError3" style="display:none;"></div>      
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Billing Contact</label>
                                                    <div class="col-md-10 form-group mb-3" id="loadBillingAjax">
                                                        <small>Choosing a billing contact allows you to batch bill this case.</small>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Billing Method</label>
                                                    <div class="col-md-10 form-group mb-3">
                                                        <select onchange="selectMethod();" id="billingMethod" name="billingMethod"
                                                            class="form-control custom-select col">
                                                            <option value=""></option>
                                                            <option value="hourly">Hourly</option>
                                                            <option value="contingency">Contingency</option>
                                                            <option value="flat">Flat Fee</option>
                                                            <option value="mixed">Mix of Flat Fee and Hourly</option>
                                                            <option value="pro_bono">Pro Bono</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row" id="billing_rate_text">
                                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Flat fee Amount</label>
                                                    <div class="input-group mb-3 col-sm-5">
                                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                        <input class="form-control case_rate number" name="default_rate" maxlength="10" type="text"
                                                            aria-label="Amount (to the nearest dollar)">
                                                    </div>
                                                </div>
                                            
                                                <div class="form-group row float-left">
                                                    <button type="button" class="btn btn-outline-secondary m-1"  onclick="backStep2();">
                                                        <span class="ladda-label">Go Back</span>
                                                    </button>
                                                </div>
                                                <div class="form-group row float-right">
                                                    <button type="button"  class="btn btn-primary ladda-button example-button m-1" data-style="expand-right" onclick="StatusLoadStep4();">
                                                        <span class="ladda-label">Continue to staff</span>
                                                    </button>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                    <div class="col-md-2 form-group mb-3">
                                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                
                                                </div>
                                            
                                            </div>

                                    </div>
                                    <div id="step-4">
                                        <div id="showError4" style="display:none"></div>
                                    
                                                
                                        <div class=" col-md-12">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label">Lead Attorney</label>
                                            <div class="col-md-9 form-group mb-3">
                                                <select id="lead_attorney" onchange="selectLeadAttorney();" name="lead_attorney" class="form-control custom-select col">
                                                    <option value=""></option>
                                                    <?php foreach($loadFirmUser as $key=>$user){?>
                                                    <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                                    <?php } ?>
                                                </select>
                                                <small>The user you select will automatically be checked in the table below.</small>
                                            </div>

                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label">Originating Attorney</label>
                                            <div class="col-md-9 form-group mb-3">
                                                <select onchange="selectAttorney();" id="originating_attorney" name="originating_attorney"
                                                    class="form-control custom-select col">
                                                    <option value=""></option>
                                                    <?php foreach($loadFirmUser as $key=>$user){?>
                                                    <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                                    <?php } ?>
                                                </select>
                                                <small>The user you select will automatically be checked in the table below.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row" id="billing_rate_text">
                                            <label for="inputEmail3" class="col-sm-12 col-form-label">Who from your firm should have access to this
                                                case?</label>
                                        </div>
                                        <table style="table-layout: auto;" class="firm-users-table table table-sm">
                                            <colgroup>
                                                <col style="width: 8%;">
                                                <col>
                                                <col>
                                                <col>
                                                <col style="width: 20%;">
                                                <col style="width: 15%;">
                                            </colgroup>
                                            <thead>
                                                <tr>
                                                    <th><input class="test-all-users-checkbox" id="select-all" type="checkbox"></th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>User Title</th>
                                                    <th>Billing Rate</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($loadFirmUser as $key=>$user){?>
                                                <tr>
                                                    <td><input class="test-all-users-checkbox" type="checkbox" id="{{$user->id}}" name="selectedUSer[{{$user->id}}]"></td>
                                                    <td>{{$user->first_name}}</td>
                                                    <td>{{$user->last_name}}</td>
                                                    <td>{{$user->user_title}}</td>
                                                    <td>
                                                        <select  onchange="selectRate({{$user->id}});" name="rate_type[{{$user->id}}]" id="cc{{$user->id}}"
                                                            class="rate test-billing-rate-dropdown form-control mr-1" >
                                                            <option value="Default_Rate">Default Rate</option>
                                                            <option value="Case_Rate">Case Rate</option>
                                                        </select>
                                                    </td>
                                                    <td id="default_{{$user->id}}">
                                                        <?php if($user->default_rate){
                                                            echo "$".$user->default_rate;
                                                        } ?>
                                                    </td>
                                                    <td id="custome_{{$user->id}}" style="display:none;">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                            <input class="form-control case_rate number" name="new_rate[{{$user->id}}]" maxlength="10"  type="text"
                                                                aria-label="Amount (to the nearest dollar)">
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        
                                        <div class="form-group row float-left">
                                            <button type="button" class="btn btn-outline-secondary m-1"  onclick="backStep3();">
                                                <span class="ladda-label">Go Back</span>
                                            </button>
                                        </div>

                                        <div class="form-group row float-right">
                                            <button type="button" onclick="saveFinalStep()" class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
                                                <span class="ladda-label">Save & Finish</span>
                                            </button>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                            <div class="col-md-2 form-group mb-3">
                                                <div class="loader-bubble loader-bubble-primary" id="innerLoader4" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="linkBulkCasesToStaff" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Firm User</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form class="LinkMultipleCaseForm" id="LinkMultipleCaseForm" name="LinkMultipleCaseForm" method="POST">
                
                @csrf
                <input class="form-control" id="user_id" value="{{$id}}" name="user_id" type="hidden">
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                        <div>
                            <p>
                            Are you sure you want to link to all active cases at your firm?
                            </p>
                        </div>
                        <div class="form-check mt-2">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input test-mycase-confirm-checkbox" checked="">
                                <span>Add all existing case events to calendar</span></label>
                        </div>
                </div>
                <div class="modal-footer"> 
                    <div class="row col-md-12">
                        <div class="col-md-6">
                            <span class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display:none;"></span>
                        </div>
                        <div class="col-md-6">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                            </a>
                            <button   type="submit" class="btn btn-primary mc-confirmation-confirm-button submit">Link To Cases</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .modal { overflow: auto !important; }
    .ui-dialog-titlebar,
    .ui-dialog-buttonset button {
        background-color: #ce5050;
        color: white;
    }

    .ui-dialog-titlebar-close {
        display: none;
    }

    .removeContact .ui-dialog-titlebar,
    .removeContact .ui-dialog-buttonset button {
        background-color: #4297d7 !important;
        color: white;
    }
    .pagination{
        width: 80%;
        float: right;
    }
    .bill-indicator-overdue {
        background: red;
    }
</style>
@endsection

@section('page-js')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');

        $('#ShowColorPicker').on('hidden.bs.modal', function () {
            window.location.reload();
        });
         $('#DeleteModal').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $(".select2").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
        });

        $("#user_type").select2({
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#AddCaseModelUpdate"),
        });   
        StaffLinkedCaseList =  $('#StaffLinkedCaseList').DataTable( {
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "bLengthChange": false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/attorneys/loadStaffCase", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$id}}' },
                error: function(){  
                    $(".LinkedCaseList-error").html("");
                    $("#LinkedCaseList").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#LinkedCaseList_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   
                    $('td:eq(0)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.case_title+'</a></div>');

                    if(aData.role_name==null){
                        $('td:eq(1)', nRow).html('<div class="text-left"><div><i class="table-cell-placeholder" data-testid="default-placeholder"></i></div></div>');
                    }else{
                        $('td:eq(1)', nRow).html('<div class="text-left">'+aData.role_name+'</div>');
                    }

                    if(aData.case_close_date==null){
                        $('td:eq(2)', nRow).html('<div class="text-left">Active</div>');
                    }else{
                        $('td:eq(2)', nRow).html('<div class="text-left">Closed</div>');
                    }
                    if(aData.case_staff_rate_type=="1" && aData.rate_amount!=null){
                        $('td:eq(3)', nRow).html('<div class="text-left" onclick="editRate('+aData.case_staff_id+','+aData.rate_amount+');"><a href="javascript:void(0);" >$<span class="amount">'+aData.rate_amount+'</span></a></div>');
                    }else if(aData.case_staff_rate_type=="0" && aData.user_default_rate!=null){
                        $('td:eq(3)', nRow).html('<div class="text-left" onclick="editRate('+aData.case_staff_id+','+aData.user_default_rate+');"><a href="javascript:void(0);" >$<span class="amount">'+aData.user_default_rate+'</span></a></div>');
                    }else{
                        $('td:eq(3)', nRow).html('<div class="text-left">Not Specified</div>');
                    }
                    var d="'{{base64_decode($id)}}','{{ $userProfile->first_name}} {{ $userProfile->middle_name}} {{ $userProfile->last_name}}','"+aData.id+"','"+aData.case_title+"',false";
                    $('td:eq(4)', nRow).html('<div class="text-center"><a  href="javascript:void(0);" onclick="confirm_remove_user_link('+d+'); return false;" ><i class="fas fa-trash pr-3  align-middle"></i> </a></div>'); 

                },
                "initComplete": function(settings, json) {
                    $('.amount').number(true, 2);
                }
        });
        $('#user_delete_form').submit(function (e) {
            $("button").attr("disabled", true);
            $("#user_delete_loading").css('display', 'block');
            e.preventDefault();

            if (!$('#user_delete_form').valid()) {
                $("#user_delete_loading").css('display', 'none');
                $('button').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#user_delete_form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/attorneys/unlinkStaffFromCase", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#user_delete_loading").css('display', 'none');
                        $('button').removeAttr("disabled");
                        return false;
                    } else {
                        StaffLinkedCaseList.ajax.reload(null,false);
                        $("#user_delete_dialog").dialog('close');
                        $('button').removeAttr("disabled");
                    }
                }
            });
        });
        $('#LinkMultipleCaseForm').submit(function (e) {
            beforeLoader();
            $("button").attr("disabled", true);
            e.preventDefault();

            if (!$('#LinkMultipleCaseForm').valid()) {
                $('button').removeAttr("disabled");
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#LinkMultipleCaseForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/attorneys/linkMultipleCaseToStaff", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&link=yes';
                },
                success: function (res) {
                    afterLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $('button').removeAttr("disabled");
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
            $('#createCase').on('click', '.remove', function () {
                var $row = $(this).parents('.fieldGroup').remove();
            });

        });
        // Smart Wizard
        $('#smartwizard,#smartwizard1').smartWizard({
            selected: 0,
            theme: 'default',
            transitionEffect: 'fade',
            showStepURLhash: false,
            enableURLhash: false,
            backButtonSupport: true, // Enable the back button support
            keyNavigation: false,
            toolbarSettings: {
                toolbarPosition: 'none',
                toolbarButtonPosition: 'end',
            },
            anchorSettings: {
                anchorClickable: false, // Enable/Disable anchor navigation
                enableAllAnchors: false, // Activates all anchors clickable all times
                markDoneStep: true, // Add done state on navigation
                markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
            },
            
        });
    });
    function confirm_remove_user_link(userId, userName, caseId, caseName, isCompany, lastLawyer) {
      
      var $dialog=$('#user_delete_dialog').dialog({
         
          resizable: false,
          width: 400,
          title: "Remove Contact",
          modal: true,
          dialogClass: 'removeContact',
          buttons: {
              Cancel: function() {
                  $( this ).dialog( "close" );
              },
              "Remove Contact": function() {
                  $('#user_delete_form').submit();
              }
          }
      });
      $dialog.find('#user_delete_dialog_name').text(userName);
      $dialog.find('#user_delete_dialog_case').text(caseName);
      $dialog.find('#user_delete_contact_id').val(userId);
      $dialog.find('#user_delete_court_case_id').val(caseId);
      $dialog.find('#user_delete_company').toggle(isCompany);
      $dialog.find('#user_delete_company_contacts').prop('checked', isCompany);
      // $dialog.find('#last_lawyer').toggle(lastLawyer);
  }

    function editRate(user_id,rate){
        swal({
            title: "Case Rate",
            text: "You are changing the case rate(per hour) for this user, not his/her default billing rate.",
            input: "text",
            inputValue: rate,
            showCancelButton: true,
            closeOnConfirm: false,
            inputPlaceholder: "100.00",
            confirmButtonText: 'Update Rate',
            cancelButtonText: "Cancel",
            confirmButtonClass: 'btn btn-success  mr-2',
            cancelButtonClass: 'btn btn-danger  mr-2',
            allowOutsideClick: false,
            reverseButtons: true
        }).then(function (inputValue) {
            if (inputValue != "") {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/attorneys/updateCaseRateForStaff",
                    data: {"case_rate": inputValue,'user_id':user_id},
                    success: function (res) {
                        StaffLinkedCaseList.ajax.reload(function(){
                            $('.amount').number(true, 2);
                        }, false);
                        toastr.success('Case rate has been updated.', "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                    }
                });
            }
        });
        $(".swal2-input").number(true, 2);
    }
    function setDefaultRate(user_id,rate){
        var rate = $("#link_rate19630204 span").text();
        swal({
            title: "Default Rate",
            text: "Please Note: changing a user's default billing rate will not impact their pre-set case rates.",
            input: "text",
            inputValue: rate, 
            showCancelButton: true,
            closeOnConfirm: false,
            inputPlaceholder: "100.00",
            confirmButtonText: 'Update Rate',
            cancelButtonText: "Cancel",
            confirmButtonClass: 'btn btn-success  mr-2',
            cancelButtonClass: 'btn btn-danger  mr-2',
            allowOutsideClick: false,
            reverseButtons: true,
        }).then(function (inputValue) {
            if (inputValue != "") {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/attorneys/updateDefaultRateForStaff",
                    data: {"case_rate": inputValue,'user_id':user_id},
                    success: function (res) {
                        if(res.errors == "") {
                            $("#link_rate19630204 span").text(res.default_rate);
                        }
                        StaffLinkedCaseList.ajax.reload(function(){
                            $('.amount').number(true, 2);
                        }, false);
                        toastr.success('Default rate has been updated.', "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                    }
                });
            }
        });
        $(".swal2-input").number(true, 2);
    }
    
    function loadProfile() {
        
        $("#preloader").show();
        $("#part1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/contacts/loadProfile", // json datasource
                data: {
                    "user_id": "{{$id}}"
                },
                success: function (res) {
                $("#LoadProfile").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function loadFinalStepDeactivate() {
        
        // $("#preloader").show();
        $("#LoadProfile").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/contacts/loadDeactivateUser", // json datasource
                data: {
                    "user_id": '{{$id}}'
                },
                success: function (res) {
                    $("#part1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function showText() {
        $("#area_text").show();
        $("#area_dropdown").hide();
        return false;
    }

    function showDropdown() {
        $("#area_text").hide();
        $("#area_dropdown").show()
        return false;
    }
    showDropdown();
    function loadAllStep() {
        $("#addCaseLinkWithOption").modal("hide");
        $('#smartwizard').smartWizard("reset"); 
        $('#createCase')[0].reset();
        $("#user_type").select2("val", "");
       
    }
    function StatusLoadStep2() {
        $('#smartwizard').data('smartWizard')._showStep(1); // go to step 3....
    }
    function backStep1() {
        $('#smartwizard').smartWizard('prev');
    }
    function backStep2() {
        $('#smartwizard').smartWizard('prev');
    }
    function StatusLoadStep3() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/loadBillingContact",
            data: {"selectdValue": ''},
            success: function (res) {
                $("#loadBillingAjax").html(res);
                $("#innerLoader").css('display', 'none');
                $('#smartwizard').data('smartWizard')._showStep(2); // go to step 3....
            }
        })
    }
   
    function StatusLoadStep4() {
        $('#smartwizard').data('smartWizard')._showStep(3); 

    }
    function backStep3() {
        $('#smartwizard').smartWizard('prev');
        
    }
   
    function selectUser() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#user_type option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveSelectdUser",
            data: {
                "selectdValue": selectdValue
            },
            success: function (res) {
               
                $(".text-center-also").remove();
                $("#innerLoader").css('display', 'none');
                $("#beforetext").remove(); 
                $("#beforebutton").remove();
                $("#submit_with_user").show(); 
                $("#submit").remove(); 
                $("#loadUserAjax").html(res);
                
            }
        })
    }
    function saveFinalStep() {
        var dataString = $("#createCase").serialize();

            $.ajax({
                type: "POST",
                url: baseUrl + "/case/saveAllStep", // json datasource
                data: dataString,
                success: function (res) {
                    if (res.errors != '') {
                        $('#showError4').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                                $.each(res.errors, function (key, value) {
                                errotHtml += '<li>' + value + '</li>';
                            });
                        errotHtml += '</ul></div>';
                        $('#showError4').append(errotHtml);
                        $('#showError4').show();
                        $('#AddCaseModelUpdate').animate({
                            scrollTop: 0
                        }, 'slow');
                        return false;
                    } else {
                        // window.location.href=baseUrl+'/court_cases/'+res.case_unique_number+'/info';
                        window.location.href = "{{ route('contacts/attorneys/cases', $id) }}";
                    }
                }
            });
        
    }
    function removeUser(id) {
        $("#innerLoader").css('display', 'block');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/remomeSelectedUser",
            data: {
                "selectdValue": id
            },
            success: function (res) {
                $("#loadUserAjax").html(res);
                $("#innerLoader").css('display', 'none');
            }
        })
    }
    function callOnClick(){
        $("#beforebutton").hide();
        $("#beforetext").show();
        $("#submit").show();
        $("#submit_with_user").hide();
    }
    $("#case_name").focus();

    function addExistingCase() {
        $("#addCaseLinkWithOption").modal("hide");
        $("#preloader").show();
        $("#addExistingCaseArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addExistingCase", 
                data: {"user_id": "{{$id}}"},
                success: function (res) {
                    $("#addExistingCaseArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function addExislinkBulkCasetingCase() {
        $("#addCaseLinkWithOption").modal("hide");
        $("#preloader").show();
        $("#addExistingCaseArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addExistingCase", 
                data: {"user_id": "{{$id}}"},
                success: function (res) {
                    $("#addExistingCaseArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function selectLeadAttorney() {
        var selectdValue = $("#lead_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }

    function selectAttorney() {
        var selectdValue = $("#originating_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
</script>

@stop
