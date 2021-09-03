@extends('layouts.master')
@section('title', ucfirst($userProfile->first_name).' '. ucfirst($userProfile->last_name). '- Contact Details')

@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
$userTitle = unserialize(USER_TITLE); 
$CommonController= new App\Http\Controllers\CommonController();
$client_name= ucfirst($userProfile->first_name .' '.$userProfile->last_name);
?>
<div class="d-flex align-items-center pl-1 pb-2">
    <i class="fas fa-user-circle fa-2x"></i>
    <h2 class="mx-2 mb-0 text-nowrap">
        <?php echo ucfirst($userProfile->first_name);?>
        <?php echo  ucfirst($userProfile->last_name);?> (Client)
        <?php if($userProfile->user_status=="3"){?>
        <span class="text-danger">[ Inactive ]</span>
        <?php } ?>
        <?php if($userProfile->user_status=="4"){?>
            <span class="text-danger">[ Archived ]</span>
            <?php } ?>
    </h2>
    <div class="ml-auto d-flex align-items-center d-print-none">
        <button class="text-black-50 pr-0 feedback-button btn btn-link pendo-case-feedback-link"
            onclick="MyCase.Clients.onFeedbackClick()">
            Tell us what you think
        </button>

        <button class="btn btn-link text-black-50 d-none d-md-block" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>

        <a href="{{ route('contacts/attorneys') }}"> <button class="btn btn-light btn-rounded  m-1 px-5"
                type="button">Back</button></a>

        <a data-toggle="modal" data-target="#EditContactModal" data-placement="bottom" href="javascript:;"> <button
                class="btn btn-primary btn-rounded m-1 px-5" type="button"
                onclick="loadClientEditBox({{$userProfile->id}});">Edit</button></a>
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
                            <div class="mb-4">
                                <div class="d-flex justify-content-center align-items-end">
                                    
                                    <?php if($userProfile->is_published=="yes" && $userProfile->profile_image!="" && file_exists(public_path().'/profile/'.$userProfile->profile_image) ){ ?>
                                        <img class="rounded-circle" src="{{BASE_URL}}public/profile/{{$userProfile->profile_image}}" width="126" height="130">
                                    <?php } else{ ?> 
                                        <i class="fas fa-10x fa-user-circle text-black-50"></i>
                                    <?php } ?>
                                    <button id="edit_client_picture_button" class="btn btn-link text-black-50 p-0 border-0">
                                        <?php if($userProfile->profile_image==NULL && $userProfile->is_published=="no"){ ?>
                                            <i class="fas fa-pencil-alt" onclick="changeProfileImage();"></i>
                                        <?php } ?>
                                        
                                        <?php if($userProfile->profile_image!=NULL && $userProfile->is_published=="yes"){ ?>
                                            <i class="fas fa-pencil-alt" onclick="changeProfileImage();"></i>
                                        <?php } ?>

                                        <?php if($userProfile->profile_image!=NULL && $userProfile->is_published=="no"){ ?>
                                            <i class="fas fa-pencil-alt" onclick="cropProfileImage();"></i>
                                        <?php } ?>
                                    </button>
                                </div>
                            </div>
                            <div class="p-1">
                                <div class="mb-4">
                                    <div class="font-weight-bold">Active Cases:</div>
                                    <?php if(!$case->isEmpty()){ ?>
                                        <div class="client-detail-wrap-up">
                                        <?php foreach($case as $kk=>$vv){ ?>
                                                <a href="{{ route('info', $vv->case_unique_number) }}">{{$vv->case_title}}</a><br>
                                        <?php } ?>
                                        </div>
                                <?php }else{
                                        ?><div class="text-muted">No Cases Linked.</div><?php
                                    }
                                    ?>
                                </div>
                                <div class="mb-4">
                                    <div class="font-weight-bold">Closed Cases:</div>
                                    <?php 
                                    if(!$closed_case->isEmpty()){
                                        ?>
                                        <div class="client-detail-wrap-up">
                                        <?php foreach($closed_case as $kk=>$vv){ ?>
                                                <a href="{{ route('info', $vv->case_unique_number) }}">{{$vv->case_title}}</a><br>
                                        <?php } ?>
                                        </div>
                            <?php }else{
                                        ?><div class="text-muted">No Cases Linked.</div><?php
                                    }
                                    ?>
                                </div>

                                <div class="mb-4">
                                    <div class="font-weight-bold">Trust Account:</div>
                                    <a id="link_rate19630204" class="default-rate-link btn btn-link pl-0" href="#"
                                        onclick="return false; return false;">$<span class="trust-total-balance"><?php echo number_format($UsersAdditionalInfo['trust_account_balance'],2)??'0.0';?></span>
                                    </a>
                                </div>
                                @if(getInvoiceSetting() && getInvoiceSetting()->is_non_trust_retainers_credit_account == "yes")
                                <div class="mb-4">
                                    <div class="font-weight-bold">Non-Trust Credit Balance <span tabindex="0" role="button" data-toggle="popover" data-placement="top" 
                                        data-trigger="hover" data-html="true"
                                        data-content="<b>Non-Trust Credit Bank Accounts</b><br> Credit (Operating Account) $<span class='credit-total-balance'>{{ number_format($UsersAdditionalInfo['credit_account_balance'],2)??'0.0' }}</span>">
                                        <i class="fas fa-info-circle"></i></span>
                                    </div>
                                    $<span class="credit-total-balance">{{ number_format($UsersAdditionalInfo['credit_account_balance'],2)??'0.0' }}</span>
                                </div>
                                @endif
                                <div class="mb-4">
                                    <div class="font-weight-bold">Minimum Trust Balance:</div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input id="trust_amount"  name="trust_amount" class="form-control number" value="{{number_format($UsersAdditionalInfo['minimum_trust_balance'],2)}}">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="font-weight-bold">Created:</div>
                                    <div class="small_details">
                                        Created <?php echo $userProfile->created_date_new;?><br>
                                        by <a title="<?php echo $userProfileCreatedBy[0]->ptitle;?>"
                                            href="#"><?php echo $userProfileCreatedBy[0]->name;?></a>
                                    </div>
                                    <hr>
                                </div>
                                <?php
                                if($userProfile->user_status==4){
                                ?>
                                <a class="client-delete btn btn-block btn-outline-secondary  client-detail-left-button" data-toggle="modal"  data-target="#unarchiveContact" data-placement="bottom" href="javascript:;"  onclick="unarchiveContact({{$client_id}});">Unarchive Contact</a>
                                
                                <?php
                                }else{
                                    ?>
                                <a class="client-delete btn btn-block btn-outline-danger client-detail-left-button" data-toggle="modal"  data-target="#archivContact" data-placement="bottom" href="javascript:;"  onclick="archiveContact({{$client_id}});">Archive Contact</a>
                                <?php } ?>
                                <a class="client-delete btn btn-block btn-outline-danger client-detail-left-button" data-toggle="modal"  data-target="#deleteContact" data-placement="bottom" href="javascript:;"  onclick="deleteContact({{$client_id}});">Delete Contact</a>
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
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts/clients/view"){ echo "active show"; } ?>" id="profile-basic-tab"
                            href="{{URL::to('contacts/clients/'.$client_id)}}"  aria-controls="profileBasic" aria-selected="true">Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts_clients_cases"){ echo "active show"; } ?>" id="contact-basic-tab "  href="{{URL::to('contacts/clients/'.$client_id.'/cases')}}" aria-controls="contactBasic" aria-selected="false">Cases</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts_clients_activity"){ echo "active show"; } ?>" id="contact-basic-tab "  href="{{URL::to('contacts/clients/'.$client_id.'/activity')}}" aria-controls="contactBasic" aria-selected="false">Activity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts_clients_notes"){ echo "active show"; } ?>" id="contact-basic-tab"   href="{{URL::to('contacts/clients/'.$client_id.'/notes')}}" aria-controls="contactBasic" aria-selected="false">Notes</a>
                    </li>
                    <li class="nav-item"><a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_clients_billing_trust_history","contacts_clients_billing_trust_request_fund","contacts_clients_billing_invoice"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/clients/'.$client_id.'/billing/trust_history')}}" >Billing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_clients_messages"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/clients/'.$client_id.'/messages')}}" >Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_clients_text_messages"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/clients/'.$client_id.'/text_messages')}}" >Text
                            Messages</a>
                    </li>
                    <li class="nav-item"><a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_clients_email"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/clients/'.$client_id.'/email')}}" >Emails</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">

                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts/clients/view"){ echo "active show"; } ?>" id="profileBasic" role="tabpanel"
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
                                                            <?php foreach($companyList as $k=>$v){ ?>
                                                                <a href="{{BASE_URL}}contacts/companies/{{$v->id}}">{{$v->first_name}}</a><br>
                                                            <?php } ?>
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
                                            <label class="switch pr-5 switch-success mr-3">
                                                <input value="{{$userProfile->email}}" type="checkbox"
                                                    name="client_portal_enable" id="client_portal_enable"
                                                    <?php if($UsersAdditionalInfo['client_portal_enable']=="1"){ echo "checked=checked"; } ?>><span
                                                    class="slider"></span>
                                            </label>
                                            <?php if($UsersAdditionalInfo['client_portal_enable']==0){?>
                                            <div id="client_login_switch">
                                                <div id="client_login_disabled" style="">
                                                    <div class="client-login-disabled-info">
                                                        You will not be able to communicate or share items (including
                                                        invoices for online payments) with this client via the portal.
                                                        <a class="no-wrap" target="_blank" href="#">What will my client see?</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php }else{?>
                                            <div class="ml-2">
                                                <div>
                                                    <?php if($userProfile->last_login==NULL){?>
                                                        Last Login: <i>Never</i> &nbsp;|&nbsp;
                                                    <span id="send_welcome_link" onclick="sentWelcomeEmail({{$userProfile->id}})" class="noprint">
                                                        <a href="javascript::void(0);" id="resend-welcome-email" >Send another welcome email</a>
                                                    </span>
                                                    <span id="send_welcome_active" style="display: none;">
                                                        <img src="{{BASE_URL}}public/images/ajax_small_bar.gif">
                                                    </span>
                                                    <span id="sent_welcome" style="display: none;">
                                                        Welcome email has been resent
                                                    </span>
                                                    <?php }else{ 
                                                          $loginDate=$CommonController->convertUTCToUserTime($userProfile->last_login,Auth::User()->user_timezone);
                                                          ?>
                                                            Last Login: {{date('F jS Y, h:i:s A',strtotime($loginDate))}}
                                                    <?php } ?>
                                                    
                                                </div>
                                            </div>
                                            <?php } ?>
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


                                    <hr>

                                    <div class="row">
                                        <div class="col-12">
                                            <h5>Private Notes</h5>
                                            <div id="client-private-notes">
                                                <p> {{$UsersAdditionalInfo['notes']}}</p>
                                            </div>
                                        </div>
                                    </div>


                                </div>

                            </div>
                            <div class="tab-pane fade" id="contactBasic" role="tabpanel"
                                aria-labelledby="contact-basic-tab">
                                3 Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic
                                lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify pitchfork
                                tattooed
                                craft beer, iphone skateboard locavore.

                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_cases"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_cases"){ ?>
                            @include('client_dashboard.caselist')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_activity"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_activity"){ ?>
                            <!-- @include('client_dashboard.loadActivity') -->
                            <div class="tab-pane fade active show" id="allEntry" role="tabpanel"
                                    aria-labelledby="home-basic-tab">
                                    
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="files-per-page-selector float-right" style="white-space: nowrap; ">
                                        <label class="mr-2">Rows Per Page:</label>
                                        <select id="per_page" onchange="onchangeLength();" name="per_page" class="custom-select w-auto">
                                            <option value="10" selected="">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <style>
                            .pagination {
                                /* width: 80%; */
                                float: left !important;
                            }
                            </style>
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_notes"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_notes"){ ?>
                            @include('client_dashboard.loadNotes')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["contacts_clients_billing_trust_history","contacts_clients_billing_trust_request_fund","contacts_clients_billing_invoice", "contacts/clients/billing/credit/history"])){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts_clients_billing_trust_history"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/clients/'.$client_id.'/billing/trust_history')}}">
                                    <span> <i class="fas fa-fw fa-landmark mr-2"></i>Trust History</span>
                                </a>
                            </div>
                            @if(getInvoiceSetting() && getInvoiceSetting()->is_non_trust_retainers_credit_account == "yes")
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts/clients/billing/credit/history"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/clients/'.$client_id.'/billing/credit/history')}}">
                                    <span> <i class="fas fa-fw fa-credit-card mr-2"></i>Credit History</span>
                                </a>
                            </div>
                            @endif
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts_clients_billing_trust_request_fund"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/clients/'.$client_id.'/billing/request_fund')}}">
                                    <span> <i class="fas fa-fw fa-hand-holding-usd  mr-2"></i> Requested Funds</span>
                                </a>
                            </div>
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts_clients_billing_invoice"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/clients/'.$client_id.'/billing/invoice')}}">
                                    <span> <i class="fas fa-fw fa-file-invoice  mr-2"></i>  Invoices</span>
                                </a>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="row">
                            <?php if(Route::currentRouteName()=="contacts_clients_billing_trust_history"){
                                ?> @include('client_dashboard.billing.trust_history')
                            <?php } ?>
                            @if(getInvoiceSetting() && getInvoiceSetting()->is_non_trust_retainers_credit_account == "yes")
                            @if(Route::currentRouteName() == "contacts/clients/billing/credit/history")
                                @include('client_dashboard.billing.credit_history')
                            @endif
                            @endif
                            <?php if(Route::currentRouteName()=="contacts_clients_billing_trust_request_fund"){
                                ?> @include('client_dashboard.billing.requested_fund',compact('totalData'))
                            <?php } ?>
                           
                            <?php if(Route::currentRouteName()=="contacts_clients_billing_invoice"){
                                ?> @include('client_dashboard.billing.invoice')
                            <?php } ?>
                           
                        </div>             
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_messages"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_messages"){ ?>
                            @include('client_dashboard.messages')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_text_messages"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_text_messages"){ ?>
                            @include('client_dashboard.text_messages')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_email"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_email"){ ?>
                            @include('client_dashboard.email')
                        <?php } ?>                    
                    </div>
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
        <div id="deactivateUser" class="modal fade show" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Deactivate
                            <?php echo $userProfile->first_name;?>
                            <?php echo $userProfile->last_name;?></h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body" id="part1">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info"><b>What happens when I deactivate a user?</b>
                                    <ul>
                                        <li>Deactivated users will not be able to login to {{config('app.name')}} .</li>
                                        <li>You will not be charged for deactivated users.</li>
                                        <li>Once deactivated, you cannot reactivate a user for 30 days.</li>
                                        <li>Documents, tasks, events, notes, and billing associated with this user will
                                            remain in {{config('app.name')}} .</li>
                                        <li>Calendar events will stop syncing with integrated calendars.</li>
                                    </ul><a href="#" target="_blank" rel="noopener noreferrer">Learn more about
                                        deactivating a user</a>
                                </div>
                                <div><span class="font-weight-bold">Note: </span>You can reassign this user's tasks and
                                    events on the next screen to any active user. Alternatively tasks and events can be
                                    reassigned after a user is deactivated.

                                </div>

                            </div>
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end mt-3">
                                    <button class="btn btn-secondary  m-1" type="button"
                                        data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary example-button m-1" id="loadNext"
                                        onClick="loadFinalStepDeactivate();">Next: Confirm Deactivation</span></button>
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

        <div id="confirmAccess" class="modal fade show" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <form class="EnableAccessForm" id="EnableAccessForm" name="EnableAccessForm" method="POST">
                    @csrf


                    <input type="hidden" value="{{$userProfile->id}}" name="client_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Enable {{config('app.name')}} Client
                                Portal
                                Access</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="showError" style="display:none"></div>
                            <div class="row">
                                <div class="col-md-12" id="confirmAccess">
                                    <div>Please confirm client portal access for this client. Clients will only have
                                        access
                                        to items that you explicitly share. This access can be changed at any time. <a
                                            class="text-underline no-wrap" href="#" target="_blank"> What will my client
                                            see? </a></div>
                                    <p>A welcome email will be automatically sent with instructions on how to login to
                                        {{config('app.name')}} .</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="col-md-12  text-center">
                                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                                <div class="form-group row float-right">
                                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                        type="submit">Confirm Access</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div id="disabledAccess" class="modal fade show" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog">
                <form class="DisableAccessForm" id="DisableAccessForm" name="DisableAccessForm" method="POST">
                    @csrf
                    <input type="hidden" value="{{$userProfile->id}}" name="client_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Disable {{config('app.name')}} Client
                                Portal
                                Access</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="showError" style="display:none"></div>
                            <div class="row">
                                <div class="col-md-12" id="confirmAccess">
                                    <div>Are you sure you want to disable {{config('app.name')}} client portal access for this contact?</div>
                                    <p> This person will no longer be able to login to  {{config('app.name')}}, and you won't be able to share any items with him/her.</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="col-md-12  text-center">
                                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                                <div class="form-group row float-right">
                                    <button class="btn btn-secondary mr-2" type="button" data-dismiss="modal">Cancel</button>
                                     <button class="btn btn-primary ladda-button example-button  submit" id="submit" type="submit">Disable Access</button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div id="addCaseLinkWithOption" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalCenterTitle">Add Case Link</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
            <h5 class="text-center my-4">Would you like to add a new or existing court case?</h5>

            <section class="ul-widget-stat-s1">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        
                        <a data-toggle="modal" data-target="#AddCaseModel" data-placement="bottom" href="javascript:;" onclick="loadStep1();"> 
                            <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{ asset('svg/court_case_add.svg') }}" width="60" height="60">
                                <div class="content">
                                    <p class="text-muted mt-2 mb-0">New Case</p>
                                    <p class="text-primary text-24 line-height-1 mb-2"></p>
                                </div>
                            </div>
                        </div></a>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <a data-toggle="modal" data-target="#addExistingCase" data-placement="bottom" href="javascript:;" onclick="addExistingCase();"> 
                        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{ asset('svg/exisiting_case.svg') }}" width="60" height="60">
                                <div class="content">
                                    <p class="text-muted mt-2 mb-0">Existing Case</p>
                                    <p class="text-primary text-24 line-height-1 mb-2"></p>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                </div>
            </section> 
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
<div id="dialog-message" title="Error" style="display:none;">
    <p>
        You cannot enable login for this contact because he/she does not have an email address.
    </p>
    <p>
        Please provide an email address for this contact if you want to enable {{config('app.name')}} client portal
        access.
    </p>
</div>

<div id="addExistingCase" class="modal fade bd-example-modal-lg " tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Existing Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addExistingCaseArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="addNoteModal" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Note</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addNoteModalArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editNoteModal" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Note</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="editNoteModalArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="discardNotes" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="discardNotesForm" id="discardNotesForm" name="discardNotesForm" method="POST">
            @csrf
            <input type="hidden" value="" name="note_id" id="discard_note_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Discard Note</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to discard this note?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Discard</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="discardDeleteNotes" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="discardDeleteNotesForm" id="discardDeleteNotesForm" name="discardDeleteNotesForm" method="POST">
            @csrf
            <input type="hidden" value="" name="note_id" id="discard_delete_note_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Discard Note</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to discard the draft? All your changes to the note will be discarded. If the note has never been published, it will be deleted.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Discard</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="deleteNote" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteNoteForm" id="deleteNoteForm" name="deleteNoteForm" method="POST">
            @csrf
            <input type="hidden" value="" name="note_id" id="delete_note_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Note</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this note?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="depositAmountPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Trust Fund</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="depositAmountPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="withdrawFromTrust" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Withdraw Trust Fund</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="withdrawFromTrustArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="RefundPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Refund Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="RefundPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deletePaymentEntry" id="deletePaymentEntry" name="deletePaymentEntry" method="POST">
            @csrf
            <input type="hidden" value="" name="payment_id" id="delete_payment_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this payment and remove all record of it from {{config('app.name')}}?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>




<div id="exportPDFpopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="exportPDFpopupForm" id="exportPDFpopupForm" name="exportPDFpopupForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Export Trust Summary</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span>
                        </button>
                </div>
                <div class="modal-body">
                    <span class="showError"></span>
                    <div class="clearfix">
                        <div style="float: left; margin-top: 7px;">
                          From:&nbsp;&nbsp;
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center">
                                <input type="text" name="from_date" id="export_trust_start_date" value="" class="date form-control date-range-input hasDatepicker" placeholder="No Start Date">
                                </div>
                            </div>
                            <div class="ml-3"> to </div>
                            <div class="d-flex flex-column ml-3">
                                <div class="d-flex align-items-center">
                                <input type="text" name="to_date" id="export_trust_end_date" value="" class="date form-control date-range-input hasDatepicker" placeholder="No End Date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Close</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Export</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="addRequestFund" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-xl ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Request Funds</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="addRequestFundArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="addEmailToClient" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="addEmailtouser" id="addEmailtouser" name="addEmailtouser" method="POST">
            @csrf
            <input type="hidden" value="" name="client_id" id="client_id_for_email">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Enter Email</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showErrorOver" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            In order to send a request, they must have a valid email address. Please enter this information below and click "Save Email" to add it to their record and continue with this request.
                      </div>
                    </div>
                    <br>
                    <div class="form-group row">
                        <label for="due-date" class="col-4 pt-2">E-mail Addess</label>
                        <div class="date-input-wrapper col-8">
                            <div class="">
                                <div>
                                    <input class="form-control" id="email" maxlength="250" name="email" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Save Email</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="editFundRequest" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Edit Request</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="editFundRequestArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteRequestFund" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteRequestedFundEntry" id="deleteRequestedFundEntry" name="deleteRequestedFundEntry" method="POST">
            @csrf
            <input type="hidden" value="" name="fund_id" id="delete_fund_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this request?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="sendFundReminder" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Send Reminder</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="sendFundReminderArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="archiveContact" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="archiveContactForm" id="archiveContactForm" name="archiveContactForm" method="POST">
            @csrf
            <input type="hidden" value="" name="contact_id" id="delete_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to archive this contact?

                        </div>
                        <div class="col-md-12" id="confirmAccess">
                            <div class="form-check mt-2"><label class="form-check-label">
                                <input type="checkbox" name="disabledLogin" class="form-check-input test-mycase-confirm-checkbox"><span>Also disable {{config('app.name')}} login for this contact</span></label></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Archive</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>



<div id="unarchiveContact" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="unarchiveContactForm" id="unarchiveContactForm" name="unarchiveContactForm" method="POST">
            @csrf
            <input type="hidden" value="" name="contact_id" id="unarchived_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to archive this contact?

                        </div>
                        <div class="col-md-12" id="confirmAccess">
                            <div class="form-check mt-2"><label class="form-check-label">
                                <input type="checkbox" name="disabledLogin" class="form-check-input test-mycase-confirm-checkbox"><span>Also enabled {{config('app.name')}} login for this contact</span></label>
                            <small>(a welcome email will be automatically sent to this contact)</small></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Unarchive</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="deleteContact" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteContactForm" id="deleteContactForm" name="deleteContactForm" method="POST">
            @csrf
            <input type="hidden" value="" name="contact_id" id="complete_delete_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Contact</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this contact?
                        </div>
                        
                        <p><br></p>
                        <div class="col-md-12" id="confirmAccessChange">
                            <p>This will delete all data and activity associated with this contact, including <b>comments and trust balances</b>. If you want to preserve this data, you should archive this contact instead.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 confirmsubmit" id="button"
                                type="button" onclick="confirmDelete()">Ok</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit finalsubmit" id="submit"
                                type="submit">Yes,Delete Contact</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="changeProfileImageModal" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <form class="changeProfileImageForm" id="changeProfileImageForm" name="changeProfileImageForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value="{{$userProfile->id}}" name="user_id" id="_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Change <?php echo ucfirst($userProfile->first_name ." ". $userProfile->last_name);?>'s Picture</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row col-md-12">
                        <div class="col-md-7">
                            <p>
                                <?php echo ucfirst($userProfile->first_name ." ". $userProfile->last_name);?>'s picture is displayed alongside any comments or messages you
                                post in {{config('app.name')}}.
                              </p>
                              <?php 
                              if($userProfile->profile_image==""){?>
                                <p>
                                  You're currently using the default picture.
                                </p>
                                <?php } ?>
                              <p>
                                Upload a new image to {{config('app.name')}}:
                              </p>
                              <input id="client_fileupload" type="file" name="file" data-url="https://eca.mycase.com/contacts/clients/22751570/upload_picture" class="ui-widget">
                              <div class="loaderShow" style="display: none;"><img src="{{LOADER}}"> Uploading...</div>
                          
                        </div>
                        <div class="col-md-1">
                        </div>
                        <div class="col-md-4 pl-2">
                            <div class="current-profile-picture-container">
                                <?php if($userProfile->is_published=="no"){ ?>
                                    <img src="{{ asset('svg/default_avatar_256.svg') }}" width="256" height="256">
                            <?php } else{ ?> 
                                    <div class="current-profile-picture-container">
                                        <img class="" src="{{BASE_URL}}public/profile/{{$userProfile->profile_image}}" width="256" height="256">
                                          <button class="btn btn-cta-light remove-client-picture-button position-absolute"  onclick="removeImage()" type="button">
                                            <i class="fas fa-trash"></i>
                                            <span class="sr-only">Remove Uploaded Picture</span>
                                          </button>
                                      </div>
                                <?php } ?>
                              </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="cropeProfileImageModal" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <form class="submitAndSaveImageForm" id="submitAndSaveImageForm" name="submitAndSaveImageForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value="{{$userProfile->id}}" name="user_id" id="_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Change <?php echo ucfirst($userProfile->first_name ." ". $userProfile->last_name);?>'s Picture</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row col-md-12" id="cropProfileImageArea">
                        <p>To finish updating <?php echo ucfirst($userProfile->first_name ." ". $userProfile->last_name);?>'s picture, please crop your image.</p>
                        <div class="loaderShow" style="display: none;"><img src="{{LOADER}}"> Loading...</div>
                       <img src="" class="cropper"/>
                        <input type="hidden" name="imageCode" id="imageCode">
                    </div>
                </div>
                <div class="modal-footer mt-2">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-left">
                            <button class="btn btn-primary m-1" type="submit" >Crop Image</button>
                        </div> <div class="form-group row float-right">
                            <button class="btn btn-secondary ladda-button example-button m-1 confirmsubmit" id="button"
                            type="button" onclick="removeImage()"><i class="fas fa-trash"></i></button>
                        </div>
                       
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="deleteProfileImageModal" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteProfileImageForm" id="deleteProfileImageForm" name="deleteProfileImageForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value="{{$userProfile->id}}" name="user_id" id="_contact_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row col-md-12">
                        Are you sure you want to remove your contact's picture and set it back to the default?
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('client_dashboard.billing.credit_history_modal')
@include('billing.invoices.partials.invoice_action_modal')
<style>
    
.remove-client-picture-button {
    bottom: 10px;
    right: 10px;
}
     .current-profile-picture-container {
        position: relative;
        float: right;
        width: 256px;
        height: 256px;
        border: 1px solid #000;
        border-radius: 5px;
        overflow: hidden;
    }
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
    .cropper-container img {
        max-width: initial !important; /* This rule is very important, please do not ignore this! */
    }
</style>
@include('commonPopup.popup_code')
@endsection

@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $isSet=localStorage.getItem("addTimeEntry");
        if($isSet!=""){
            $("#loadTimeEntryPopup").modal("show");
            loadTimeEntryPopup();
        }
        localStorage.setItem("addTimeEntry","");
        $('#export_trust_start_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        
        $('#export_trust_end_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });

        $("#innerLoader").css('display', 'none');

        $('#ShowColorPicker').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $('#DeleteModal').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $("#addEmailtouser").validate({
            rules: {
                email: {
                    required: true,
                    email:true
                }
            },
            messages: {
                email: {
                    required: "Email can't be blank",
                    email : "Email is not formatted correctly"
                }
            }
        });

        $('input[name="client_portal_enable"]').click(function () {
            var vals = $(this).val();
            if ($("#client_portal_enable").prop('checked') == true) {
                if (vals == "") {
                    $("#dialog-message").dialog({
                        modal: true,
                        buttons: {
                            Ok: function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                    $("#client_portal_enable").prop('checked', false);
                } else {
                    $("#confirmAccess").modal("show");
                    $("#client_portal_enable").prop('checked', false);
                }
            } else {
                $("#disabledAccess").modal("show");
                $("#client_portal_enable").prop('checked', "checked");
            }
        });

        $('#EnableAccessForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            var dataString = $("#EnableAccessForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/changeAccess", // json datasource
                data: dataString,
                success: function (res) {
                    afterLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        $("#client_portal_enable").prop('checked', "checked");
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });

        $('#DisableAccessForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            var dataString = $("#DisableAccessForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/changeAccess", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&disable=yes';
                },
                success: function (res) {
                    afterLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        $("#client_portal_enable").prop('checked', false);
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
        $("#trust_amount").focusout(function(){
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/saveTrustAmount", // json datasource
                    data: {
                        "user_id": "{{$userProfile->id}}",
                        'trust_amount':$("#trust_amount").val()
                    },
                    success: function (res) {
                        $("#LoadProfile").html(res);
                        $("#preloader").hide();
                    }
                })
            })
        });

        var dataTable =  $('#LinkedCaseList').DataTable( {
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "bLengthChange": false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/clients/casesLoad", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$client_id}}' },
                error: function(){  // error handling
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


                    var d="'{{$client_id}}','{{$client_name}}','"+aData.id+"','"+aData.case_title+"',false";
                    $('td:eq(3)', nRow).html('<div class="text-center"><a  href="javascript:;"  onclick="confirm_remove_user_link('+d+'); return false;" ><i class="fas fa-trash pr-3  align-middle"></i> </a></div>'); 
                },
                //confirm_remove_user_link(21079660, '[SAMPLE] John Doe', 13087060, 'CASE1', false); return false;
                "initComplete": function(settings, json) {
                }
        });
        // Set Currency Separator to input fields
        $(document).on('keypress , paste', '.number', function (e) {
            if (/^-?\d*[,.]?(\d{0,3},)*(\d{3},)?\d{0,3}$/.test(e.key)) {
                $('.number').on('input', function () {
                    e.target.value = numberSeparator(e.target.value);
                });
            } else {
                e.preventDefault();
                return false;
            }
        });
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
            backButtonSupport: true, // Enable the back button support
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
                enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
            },
            
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
                url: baseUrl + "/contacts/clients/unlinkFromCase", // json datasource
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
                        window.location.reload();
                    }
                }
            });
        });
        var caseHistoryGriddataTable =  $('#caseHistoryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/clients/ClientActivityHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$client_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [{ data: 'id'}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    var caseLink='';
                   if(aData.acrtivity_title=='added contact'){
                       var img='<img src="'+baseUrl+'/public/icon/activity_note_added.png" width="27" height="21">';
                       var linkName='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.activity_for+'">'+aData.client_name+'</a>';
                   }else if(aData.acrtivity_title=='update contact'){
                       var img='<img src="'+baseUrl+'/public/icon/activity_note_updated.png" width="27" height="21">';
                       var linkName='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.activity_for+'">'+aData.client_name+'</a>';
                   }else if(aData.acrtivity_title=='linked contact'){
                        var img='<img src="'+baseUrl+'/public/icon/activity_client_linked.png" width="27" height="21">';
                        var linkName='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.activity_for+'">'+aData.client_name+'</a> to case <a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_id+'/info">'+aData.case_name+'</a>';
                        var caseLink=' | <a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_id+'/info">'+aData.case_name+'</a>';
                   }else if(aData.acrtivity_title=='unlinked contact'){
                        var img='<img src="'+baseUrl+'/public/icon/activity_client_unlinked.png" width="27" height="21">';
                        var linkName='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.activity_for+'">'+aData.client_name+'</a> from case <a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_id+'/info">'+aData.case_name+'</a>';
                        var caseLink=' | <a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_id+'/info">'+aData.case_name+'</a>';
                   }else if(aData.acrtivity_title=='accepted a deposit into trust of'){
                        var img='<img src="'+baseUrl+'/public/icon/activity_client_unlinked.png" width="27" height="21">';
                        var linkName='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.activity_for+'">'+aData.client_name+'</a> accepted a deposit into trust of  <a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_id+'/info">'+aData.case_name+'</a>';
                        var caseLink='';
                   }
                   var userTitle='';
                   if(aData.user_title!=''){
                    var userTitle='('+aData.user_title+')';
                   }
                  
                   $('td:eq(0)', nRow).html('<div class="text-left"> '+img+' <a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.created_by_name+''+userTitle+'</a> '+aData.acrtivity_title+'  '+linkName+' </a> <abbr class="timeago" title="'+aData.note_created_at+'">about  '+aData.time_ago+'</abbr> via web '+caseLink+'</div>');
                
                },
                "initComplete": function(settings, json) {
                    $("#caseHistoryGrid thead").remove();
                    $('td').css('font-size',parseInt('13px'));  
                }
        });

        var ClientNotesyGrid =  $('#ClientNotesyGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/clients/ClientNotes", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$client_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'id'},
                { data: 'id'}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   
                    var isdraft=createdat=updateat=sub="";
                    if(aData.is_draft=="yes"){
                        isdraft='<span class="badge badge-pill badge-success p-2 m-1">Draft</span>';
                    }
                    if(aData.created_at!=null){
                        createdat='Note Added: '+aData.created_date_new+' by '+aData.note_created_by+' ('+aData.created_by_user_title+')';
                    }
                    if(aData.updated_at!=null){
                        updateat='Last Updated: '+aData.updated_date_new+' by '+aData.note_updated_by+' ('+aData.updated_by_user_title+')';
                    }
                    if(aData.note_subject!=null){
                        sub=aData.note_subject;
                    }else{
                        sub="-";
                    }

                    $('td:eq(0)', nRow).html('<div class="text-left"><div class="expanded-content"><a class="text-default" data-toggle="collapse" onclick="hidez('+aData.id+')" href="#accordion-item-group'+aData.id+'" value="'+aData.id+'"><div class="c-pointer d-flex mb-3 test-note-subject">'+isdraft+'<span class="font-weight-bold pt-2">'+sub+'</span><i aria-hidden="true" class="fa fa-angle-down icon-angle-down icon icon-angle-down-'+aData.id+'" style="margin-left: 8px;padding-top: 10px;"></i></div></a><div class="collapse" id="accordion-item-group'+aData.id+'" va="'+aData.id+'"><div><p class="note-note"><p>'+aData.notes+'</p></p></div><div><div class="test-note-created-at text-black-50 font-italic small">'+createdat+'</div><div class="test-note-updated-at text-black-50 font-italic small">'+updateat+'</div></div><div class="d-flex align-items-center"><div class="d-flex flex-row"><a data-toggle="modal"  data-target="#editNoteModal" data-placement="bottom" href="javascript:;" href="javascript:;"><button class="btn btn-outline-secondary btn-rounded " type="button" onclick="loadEditNotBox('+aData.id+');"><i class="fas fa-pencil-alt mr-1"></i>Edit Note</button></a><button type="button" class="mr-1 add-time-entry-button text-dark btn btn-link"><a data-toggle="modal"  data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadTimeEntryPopup('+aData.id+');"><i class="fas fa-stopwatch mr-1"></i>Add Time Entry</a></button><button type="button" class="mr-1 delete-note-button text-dark btn btn-link"><a data-toggle="modal"  data-target="#deleteNote" data-placement="bottom" href="javascript:;" class="text-dark" onclick="deleteNote('+aData.id+');"><i class="fas fa-trash mr-1"></i>Delete Note</a></button></div><div class="btn c-pointer"><a class="btn" onclick="hideshow('+aData.id+')">Hide Details</a><i aria-hidden="true" class="fa fa-angle-up icon-angle-up icon"></i></div></div></div></div></div>');

                    $('td:eq(1)', nRow).html('<div class="text-left">'+aData.created_date_new+'</div>');

                    $('td:eq(2)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#editNoteModal" data-placement="bottom" href="javascript:;"  onclick="loadEditNotBox('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a><a data-toggle="modal"  data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadTimeEntryPopup('+aData.id+');"><i class="fas fa-stopwatch mr-1 p-2 align-middle"></i></a><a data-toggle="modal"  data-target="#deleteNote" data-placement="bottom" href="javascript:;"  onclick="deleteNote('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></div>');
                },
                "initComplete": function(settings, json) {
                    $("#caseHistoryGrid thead").remove();
                    var currentSize=localStorage.getItem("clientNoteList");
                    $('td').css('font-size', currentSize +'px');
                   
                }
        });
        $('#addNoteModal').on('hidden.bs.modal', function () {
            // ClientNotesyGrid.ajax.reload();
            window.location.reload();
            // window.location = baseUrl+"/contacts/attorneys";
        });
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        
        //Decrease the font size
        $(".decrease").click(function(){   
            modifyFontSize('decrease');  
        });
        
        // $('#ClientNotesyGrid').on('show.bs.collapse', function () {
        //     $(".icon-angle-down").hide();
        // });

        $('#ClientNotesyGrid').on('hide.bs.collapse', function (e) {
            var id=$(e.target).attr('va');
            $(".icon-angle-down-"+id).show();
        });

        $('#discardNotesForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#discardNotesForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#discardNotesForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/discardNote", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });

        $('#discardDeleteNotesForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#discardDeleteNotesForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#discardDeleteNotesForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/discardDeleteNote", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });

        $('#deleteNoteForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteNoteForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteNoteForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/deleteNote", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });

        $('#depositAmountPopup').on('hidden.bs.modal', function () {
            billingTabTrustHistory.ajax.reload(null, false);
            // window.location.reload();
            // // window.location = baseUrl+"/contacts/attorneys";
        });
        
        
        //Billing tab
        var billingTabTrustHistory =  $('#billingTabTrustHistory').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl>',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/clients/loadTrustHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$client_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    
                    $('td:eq(0)', nRow).html('<div class="text-left">'+aData.added_date+'</div>');
                    $('td:eq(1)', nRow).html('<div class="text-left">-</div>');
                    
                    var isRefender="";
                    if(aData.is_refunded=="yes"){
                        isRefender="(Refunded)";
                    }

                    if(aData.fund_type=="withdraw"){
                        if(aData.withdraw_from_account!=null){
                            var ftype="Withdraw from Trust (Trust Account) to Operating("+aData.withdraw_from_account+")";
                        }else{
                            var ftype="Withdraw from Trust (Trust Account)" +isRefender;
                        }
                    }else if(aData.fund_type=="refund_withdraw"){
                        var ftype="Refund Withdraw from Trust (Trust Account)";
                    }else if(aData.fund_type=="refund_deposit"){
                        var ftype="Refund Deposit into Trust (Trust Account)";
                    }else{
                        var ftype="Deposit into Trust (Trust Account)"+isRefender;
                    }
                    $('td:eq(2)', nRow).html('<div class="text-left">'+ftype+'</div>');
                    
                    
                    if(aData.fund_type=="withdraw"){
                        $('td:eq(3)', nRow).html('<div class="text-left">Trust  '+isRefender+'</div>');
                    }else{
                        
                        $('td:eq(3)', nRow).html('<div class="text-left">'+aData.payment_method+' '+isRefender+'</div>');
                    }
                   

                    var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.client_name+' (Client)</a>';
                    $('td:eq(4)', nRow).html('<div class="text-left">'+clientLink+'</div>');

                    if(aData.fund_type=="withdraw"){
                        $('td:eq(5)', nRow).html('<div class="text-left">-$'+aData.withdraw+'</div>');
                    }else if(aData.fund_type=="refund_withdraw"){
                        $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.refund+'</div>');
                    }else if(aData.fund_type=="refund_deposit"){
                        $('td:eq(5)', nRow).html('<div class="text-left">-$'+aData.refund+'</div>');
                    }else{
                        $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.amount_paid+'</div>');
                    }

                    $('td:eq(6)', nRow).html('<div class="text-left">$'+aData.trust_balance+'</div>')
                    
                    if(aData.is_refunded=="yes"){
                        var deelete='<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('+aData.id+');"><button type="button" disabled="" class="py-0 btn btn-link disabled">Delete</button></a></span>';

                        var refund='<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
                    }else{
                        var deelete='<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('+aData.id+');"><button type="button" class="py-0 btn btn-link">Delete</button></a></span>';

                        if(aData.fund_type=="refund_withdraw" || aData.fund_type=="refund_deposit"){
                            var refund='<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
                        }else{
                            var refund='<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('+aData.id+');"><button type="button"  class="py-0 btn btn-link ">Refund</button></a></span>';
                        }

                    }
                   

                    $('td:eq(7)', nRow).html('<div class="text-center">'+refund+'<br>'+deelete+'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>');
                    
                },
                "initComplete": function(settings, json) {
                    $('td').css('font-size',parseInt('13px'));  
                }
        });
        $('#addRequestFund').on('hidden.bs.modal', function () {
            requestFundGrid.ajax.reload(null, false);
        });

        var requestFundGrid =  $('#requestFundGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl>',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/clients/loadRequestedFundHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$client_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'}, 
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    
                    $('td:eq(0)', nRow).html('<div class="text-left">#R-00'+aData.id+'</div>');
                    var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.client_name+' (Client)</a>';
                    $('td:eq(1)', nRow).html('<div class="text-left">'+clientLink+'</div>');
                    $('td:eq(2)', nRow).html('<div class="text-left">Trust(Trust Account)</div>');

                    $('td:eq(3)', nRow).html('<div class="text-left">$'+aData.amt_requested+'</div>');
                    $('td:eq(4)', nRow).html('<div class="text-left">$'+aData.amt_paid+'</div>')
                    $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.amt_due+'</div>')
                    $('td:eq(6)', nRow).html('<div class="text-left">'+aData.due_date_format+'</div>')
                    $('td:eq(7)', nRow).html('<div class="text-left">'+aData.send_date_format+'</div>')

                    if(aData.is_viewed=="no"){
                        var isSeen="Never";
                    }else{
                        var isSeen="Yes";
                    }
                    $('td:eq(8)', nRow).html('<div class="text-left">'+isSeen+'</div>')

                    if(aData.current_status=="Overdue"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>Overdue';
                    }else if(aData.current_status=="Partial"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>Partial';
                    }else if(aData.current_status=="Paid"){
                        var curSetatus="Paid";
                    }else{
                        var curSetatus="Sent";
                    }
                        
                    $('td:eq(9)', nRow).html('<div class="text-left">'+curSetatus+'</div>');
                    
                    $('td:eq(10)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#editFundRequest" data-placement="bottom" href="javascript:;"  onclick="editFundRequest('+aData.id+');"><i class="fas fa-pen align-middle pr-3"></i></a> <a data-toggle="modal"  data-target="#sendFundReminder" data-placement="bottom" href="javascript:;"  onclick="sendFundReminder('+aData.id+');"><i class="fas fa-bell pr-3 align-middle"></i></a> <a data-toggle="modal"  data-target="#deleteRequestFund" data-placement="bottom" href="javascript:;"  onclick="deleteRequestFund('+aData.id+');"><i class="fas fa-trash align-middle "></i></a></div>');

                },
                "initComplete": function(settings, json) {
                    $('td').css('font-size',parseInt('13px'));  
                }
        });

        $('#deletePaymentEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deletePaymentEntry').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deletePaymentEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/deletePaymentEntry", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });

        $('#exportPDFpopupForm').submit(function (e) {
            
            beforeLoader();
            e.preventDefault();
            var dataString = '';
            dataString = $("#exportPDFpopupForm").serialize();
            $.ajax({
                type: "POST",
                url:  baseUrl +"/downloadTrustHistory", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&export=yes&user_id={{$client_id}}';
                },
                 success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        $("#preloader").hide();
                        var url = res.url;
                        window.open(url, '_blank');
                        afterLoader();
                        $("#exportPDFpopup").modal('hide');
                        
                        
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });

        $('#addEmailtouser').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#addEmailtouser').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#addEmailtouser").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addEmailtouser", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showErrorOver').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showErrorOver').append(errotHtml);
                        $('.showErrorOver').show();
                        afterLoader();
                        return false;
                    } else {
                        $("#addEmailToClient").modal("hide");
                        refreshDetail();
                    }
                },
                error: function (xhr, status, error) {
                $('.showErrorOver').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showErrorOver').append(errotHtml);
                $('.showErrorOver').show();
                afterLoader();
            }
            });
        });

        
        $('#deleteRequestedFundEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteRequestedFundEntry').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteRequestedFundEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/deleteRequestedFundEntry", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });
        $('#archiveContactForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#archiveContactForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#archiveContactForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/archiveContactForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });
        $('#unarchiveContactForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#unarchiveContactForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#unarchiveContactForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/unarchiveContactForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });
        $('#deleteContactForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteContactForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteContactForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/deleteContactForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.href=baseUrl+"/contacts/client";
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });
        $("#client_fileupload").on("change", function() {
            var fileName = $(this).val();
            if(fileName!=''){
             $("#changeProfileImageForm").submit();
            }
        });

        $('#changeProfileImageForm').submit(function (e) {
            $(".loaderShow").show();
            e.preventDefault();
            var dataString = new FormData(this);
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/uploadImage", // json datasource
                data: dataString,
                cache:false,
                contentType: false,
                processData: false,
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        $(".loaderShow").hide();
                        return false;
                    } else {
                        
                        cropProfileImage();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });
        $('#deleteProfileImageForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteProfileImageForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteProfileImageForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/deleteProfileImageForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        beforeLoader();
                        if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });

        $('#submitAndSaveImageForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#submitAndSaveImageForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#submitAndSaveImageForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/submitAndSaveImageForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                        window.location.reload();
                },
                error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
            });
        });
       
        $('#cropeProfileImageModal').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $(document).on('click', '.AllNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadAllNotification(page);
        });

    });

    <?php if(Route::currentRouteName()=="contacts_clients_activity"){?>
        loadAllNotification(1);
    <?php } ?>
    function onchangeLength(){
        loadAllNotification(1);
    }
    function loadAllNotification(page=null) {
       $("#innerLoader").css('display', 'none');
       $("#allEntry").html('<img src="{{LOADER}}""> Loading...');
       $(function () {
           $.ajax({
               type: "POST",
               url: baseUrl + "/notifications/loadAllNotification?per_page="+$("#per_page").val()+"&user_id={{$userProfile->id}}&page="+page, // json datasource
               data: 'bulkload',
               success: function (res) {
                   $("#allEntry").html(res);
                   return false;
               }
           })
       })
   }
    function expandAllnote(){
        $('.collapse').collapse('show');
        $("#co").show();
        $("#ex").hide();
        $(".icon-angle-down").hide();
    }
    function collapseAllnote(){
        $('.collapse').collapse('hide');
        $("#ex").show();
        $("#co").hide();
        $(".icon-angle-down").show();
    }
    
    function hideshow(id){
        $("#accordion-item-group"+id).removeClass("show");
        $(".icon-angle-down-"+id).show();
    }
    function hidez(id){
        $(".icon-angle-down-"+id).hide();
    }
    function modifyFontSize(flag) {  
        var min = 13;
        var max = 19;
        var divElement = $('td');  
        var currentFontSize = parseInt(divElement.css('font-size'));  

        if (flag == 'increase')  
            currentFontSize += 3;  
        else if (flag == 'decrease')  
            currentFontSize -= 3;  
        else  
            currentFontSize = 13;  
            if(currentFontSize>=min && currentFontSize<=max){
            divElement.css('font-size', currentFontSize); 
            localStorage.setItem("clientNoteList",currentFontSize);
        }
    }  
    function loadProfile() {
        $("#preloader").show();
        $("#part1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadProfile", // json datasource
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

        $("#preloader").show();
        $("#LoadProfile").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadDeactivateUser", // json datasource
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
    function sentWelcomeEmail(id) {
        $("#send_welcome_active").show();
        $("#LoadProfile").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/sendWelcomeEmailAgain", // json datasource
                data: {
                    "user_id": '{{$id}}'
                },
                success: function (res) {
                    $("#send_welcome_link").hide();  
                    $("#sent_welcome").show();
                    $("#send_welcome_active").hide();
                }
            })
        })
    }

    //Case Tab @START
    function loadClientCaseLinkList() {
        $("#preloader").show();
        $("#addCaseLinkWithOption").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadProfile", // json datasource
                data: {
                    "user_id": "{{$id}}"
                },
                success: function (res) {
                    $("#addCaseLinkWithOption").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
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
    function addExistingCase() {
        $("#addCaseLinkWithOption").modal("hide");
        $("#preloader").show();
        $("#addExistingCaseArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addExistingCase", 
                data: {"user_id": "{{base64_encode($client_id)}}"},
                success: function (res) {
                    $("#addExistingCaseArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
  
    function loadAddNotBox() {
        $("#preloader").show();
        $("#addNoteModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addNotes", 
                data: {"user_id": "{{$client_id}}"},
                success: function (res) {
                    $("#addNoteModalArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadEditNotBox(id) {
        $("#preloader").show();
        $("#editNoteModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/editNotes", 
                data: {"user_id": "{{$client_id}}","id": id},
                success: function (res) {
                    $("#editNoteModalArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function discardNotes(id) {
       
       $("#discardNotes").modal("show");
       $("#discard_note_id").val(id);
   }
    function discardDeleteNotes(id) {
       $("#discardDeleteNotes").modal("show");
       $("#discard_delete_note_id").val(id);
   }
    function deleteNote(id) {
        $("#deleteNote").modal("show");
        $("#delete_note_id").val(id);
    }
    function loadTimeEntryPopup(id) {
            $("#preloader").show();
            $("#addTimeEntry").html('');
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/clients/loadTimeEntryPopup", // json datasource
                    data: {
                        "task_id": id
                    },
                    success: function (res) {
                        $("#addTimeEntry").html('');
                        $("#addTimeEntry").html(res);
                        $("#preloader").hide();
                    }
                })
            })
        }

    //Case Tab @END
    
    //Billing Tab @START

    function loadDepositPopup() {
        $("#preloader").show();
        $("#depositAmountPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addTrustEntry", 
                data: {"user_id": "{{$client_id}}"},
                success: function (res) {
                    $("#depositAmountPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function withdrawFromTrust() {
        $("#preloader").show();
        $("#withdrawFromTrustArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/withdrawFromTrust", 
                data: {"user_id": "{{$client_id}}"},
                success: function (res) {
                    $("#withdrawFromTrustArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function RefundPopup(id) {
        $("#preloader").show();
        $("#RefundPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/refundPopup", 
                data: {"user_id": "{{$client_id}}",'transaction_id':id},
                success: function (res) {
                    $("#RefundPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteEntry(id) {
        $("#deleteEntry").modal("show");
        $("#delete_payment_id").val(id);
    }
    function exportPDFpopup() {
        $("#export_trust_start_date").val("");
        $("#export_trust_end_date").val("");
        $('.showError').html('');
        $("#exportPDFpopup").modal("show");
    }

    function addRequestFundPopup() {
        $("#preloader").show();
        $("#addRequestFundArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addRequestFundPopup", 
                data: {"user_id": "{{$client_id}}"},
                success: function (res) {
                    $("#addRequestFundArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function editFundRequest(id) {
        $("#preloader").show();
        $("#editFundRequestArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/editFundRequest", 
                data: {"id": id},
                success: function (res) {
                    $("#editFundRequestArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteRequestFund(id) {
        $("#deleteRequestFund").modal("show");
        $("#delete_fund_id").val(id);
    }
    function sendFundReminder(id) {
        $("#preloader").show();
        $("#sendFundReminderArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/sendFundReminder", 
                data: {"id": id},
                success: function (res) {
                    $("#sendFundReminderArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    
    function archiveContact(id) {
        $("#archiveContact").modal("show");
        $("#delete_contact_id").val(id);
    }
    function unarchiveContact(id) {
        $("#unarchiveContact").modal("show");
        $("#unarchived_contact_id").val(id);
    }

    function deleteContact(id) {
        $("#deleteContact").modal("show");
        $("#complete_delete_contact_id").val(id);
    }
      //Billing Tab @END

    function confirmDelete(){
        $("#confirmAccessChange").html('<p style="font-weight:bold; color: red;">This action is permanent and cannot be reversed!</p>');
        $(".confirmsubmit").hide();
        $(".finalsubmit").show();
        
    }
    

    function changeProfileImage(){
        $("#changeProfileImageModal").modal("show");
        $(".showError").hide();
        $('#changeProfileImageForm')[0].reset();
        $("#client_fileupload").val("");


    }

    function cropProfileImage(){

        $("#cropeProfileImageModal").modal("show");
        $("#changeProfileImageModal").modal("hide");
        $(".loaderShow").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/cropImage", 
            data: {"user_id": "{{$client_id}}"},
            success: function (res) {
                $(".cropper").attr("src",res.image);
                $('.cropper').rcrop({
                    minSize : [200,200],
                    preserveAspectRatio : true,
                    grid : false,
                });
                $('.cropper').on('rcrop-ready', function(){
                    var srcResized = $(this).rcrop('getDataURL', 130,130);
                    $('#imageCode').val(srcResized);
                });
                 $(".loaderShow").hide();
                afterLoader();
            }
        })
    }
    function removeImage(){
        $("#deleteProfileImageModal").modal("show"); 
    }
    $(".finalsubmit").hide();
    $('.cropper').on('rcrop-changed', function(){
        var srcResized = $(this).rcrop('getDataURL', 130,130);
        $('#imageCode').val(srcResized);
    });
</script>
<script src="{{ asset('assets\js\custom\client\viewclient.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script src="{{ asset('assets\js\custom\client\creditfund.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
// <script src="{{ asset('assets\js\custom\invoice\listinvoice.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@stop