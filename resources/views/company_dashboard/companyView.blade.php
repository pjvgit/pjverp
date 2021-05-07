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
    <i class="fas fa-building fa-2x"></i> 
    <h2 class="mx-2 mb-0 text-nowrap">
        <?php echo ucfirst($userProfile->first_name);?>
     
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
        <a data-toggle="modal" data-target="#EditCompany" data-placement="bottom" href="javascript:;"> <button
                class="btn btn-primary btn-rounded m-1 px-5" type="button"
                onclick="EditCompany({{$userProfile->id}});">Edit</button></a>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <div class="card mb-4">
            <div class="card-body">
                <span id="responseMain"></span>
                <nav class="test-general-settings-nav p-0 pt-0" role="navigation">
                            <div class="p-1">
                                <div class="mb-4">
                                    <div class="font-weight-bold">Contacts:</div>
                                    <?php foreach($caseCllientSelection as $key=>$val){
                                     
                                        if($val->user_level==4){
                                        ?>
                                      <div class="d-flex align-items-center mb-3 rounded-circle">
                                          <div class="mx-1">
                                              <i class="fas fa-building fa-2x text-black-50"></i>
                                          </div>
                                          <div class="d-flex flex-column justify-content-center">
                                              <a class="font-weight-bolder pendo-left-details-company"
                                                  href="{{BASE_URL}}contacts/companies/{{$val->id}}">Company</a>
                                              <small><a class="text-break pendo-left-details-company-email"
                                                      href="mailto:{{$val->email}}">{{$val->email}}</a></small>
                                          </div>
                                      </div>
                                      <?php } else { ?>
                                      <div class="d-flex align-items-center mb-3">
                                          <div class="mr-1">
                                              <i class="fas fa-2x fa-user-circle text-black-50"></i>
                                          </div>
  
                                          <div class="d-flex flex-column justify-content-center">
                                              <div class="d-flex flex-wrap align-items-center">
                                                  <a class="font-weight-bolder pendo-left-details-contact"
                                                      href="{{BASE_URL}}contacts/clients/{{$val->id}}">{{substr($val->first_name,0,15)}}
                                                      {{substr($val->last_name,0,15)}}</a>
                                                  <small class="ml-1 text-lowercase">(Client)</small>
                                              </div>
                                              <small><a class="text-break pendo-left-details-contact-email"
                                                      href="mailto:{{$val->email}}">{{$val->email}}</a></small>
                                          </div>
                                      </div>
                                      <?php 
                                    }
                                      }?>
                                </div>
                                <div class="mb-4">
                                    <div class="font-weight-bold">Active Cases:</div>
                                    <?php 
                                    if(!$case->isEmpty()){
                                        ?>
                                        <div class="client-detail-wrap-up">
                                        <?php foreach($case as $kk=>$vv){ ?>
                                                <a href="{{BASE_URL}}court_cases/{{$vv->case_unique_number}}/info">{{$vv->case_title}}</a><br>
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
                                                <a href="{{BASE_URL}}court_cases/{{$vv->case_unique_number}}/info">{{$vv->case_title}}</a><br>
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
                                        onclick="return false; return false;">$<?php echo number_format($UsersAdditionalInfo['trust_account_balance'],2)??'0.0';?>
                                    </a>
                                </div>
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
                                        Created <?php echo date('M j, Y', strtotime($userProfile->created_at));?><br>
                                        by <a title="<?php echo $userProfileCreatedBy[0]->ptitle;?>"
                                            href="#"><?php echo $userProfileCreatedBy[0]->name;?></a>
                                    </div>
                                    <hr>
                                </div>
                                <?php
                                if($userProfile->user_status==4){
                                ?>
                                <a class="client-delete btn btn-block btn-outline-secondary  client-detail-left-button" data-toggle="modal"  data-target="#unarchiveCompany" data-placement="bottom" href="javascript:;"  onclick="unarchiveCompany();">Unarchive Company</a>
                                
                                <?php
                                }else{
                                    ?>
                                <a class="client-delete btn btn-block btn-outline-danger client-detail-left-button" data-toggle="modal"  data-target="#archiveCompany" data-placement="bottom" href="javascript:;"  onclick="archiveCompany();">Archive Company</a>
                                <?php } ?>
                                <a class="client-delete btn btn-block btn-outline-danger client-detail-left-button" data-toggle="modal"  data-target="#deleteCompany" data-placement="bottom" href="javascript:;"  onclick="deleteCompany();">Delete Company</a>
                   
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
                        <a class="nav-link <?php if(Route::currentRouteName()==""){ echo "active show"; } ?>" id="profile-basic-tab"
                            href="{{URL::to('contacts/companies/'.$client_id)}}"  aria-controls="profileBasic" aria-selected="true">Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts_company_client"){ echo "active show"; } ?>" id="profile-basic-tab"
                            href="{{URL::to('contacts/companies/'.$client_id.'/clients')}}"  aria-controls="profileBasic" aria-selected="true">Contacts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts_company_cases"){ echo "active show"; } ?>" id="contact-basic-tab "  href="{{URL::to('contacts/companies/'.$client_id.'/cases')}}" aria-controls="contactBasic" aria-selected="false">Cases</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="contacts_company_notes"){ echo "active show"; } ?>" id="contact-basic-tab"   href="{{URL::to('contacts/companies/'.$client_id.'/notes')}}" aria-controls="contactBasic" aria-selected="false">Notes</a>
                    </li>
                    <li class="nav-item"><a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_company_billing_trust_history","contacts_company_billing_trust_request_fund","contacts_company_billing_invoice"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/companies/'.$client_id.'/billing/trust_history')}}" >Billing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_company_messages"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/companies/'.$client_id.'/messages')}}" >Messages</a>
                    </li>
                   
                    <li class="nav-item"><a class="nav-link <?php if(in_array(Route::currentRouteName(),["contacts_company_email"])){ echo "active show"; } ?>"  href="{{URL::to('contacts/companies/'.$client_id.'/email')}}" >Emails</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">

                    <div class="tab-pane fade <?php if(Route::currentRouteName()==""){ echo "active show"; } ?>" id="profileBasic" role="tabpanel"
                        aria-labelledby="profile-basic-tab">
                        <div id="contact_info_page" style="display: block;">
                            <div class="align-items-md-start w-100">
                                <div class="p-2 contact-card">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-8 pt-xs-3 pt-md-0">
                                            <h5>Company Information</h5>
                                            <table class="table table-borderless table-sm custom-table">
                                                <tbody>
                                                    <tr>
                                                        <th>Name</th>
                                                        <td id="company-name">{{$userProfile->first_name}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email</th>
                                                        <td id="company-email"><a href="mailto:{{$userProfile->email}}">
                                                            {{$userProfile->email}}
                                                        </a></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Website</th>
                                                        <td id="company-website">
                                                            <a href="{{$UsersAdditionalInfo['website']}}">
                                                            {{$UsersAdditionalInfo['website']}} </a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-xs-12 col-md-4 pt-xs-3 pt-md-0">
                                            <h5>Phone Numbers</h5>
                                            <table class="table table-borderless table-sm custom-table">
                                                <tbody>
                                                    <tr id="client-home-phone">
                                                        <th>Main</th>
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
                                                {{$userProfile->city}}, {{$userProfile->state}}
                                                {{$userProfile->state}}<br>
                                                {{$userProfile->countryname}}<br>

                                            </div>
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
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_company_client"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_company_client"){ ?>
                            @include('company_dashboard.clientlist')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_company_cases"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_company_cases"){ ?>
                            @include('company_dashboard.caselist')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_clients_activity"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_clients_activity"){ ?>
                            @include('company_dashboard.loadActivity')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_company_notes"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_company_notes"){ ?>
                            @include('company_dashboard.loadNotes')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["contacts_company_billing_trust_history","contacts_company_billing_trust_request_fund","contacts_company_billing_invoice"])){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts_company_billing_trust_history"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/companies/'.$client_id.'/billing/trust_history')}}">
                                    <span> <i class="fas fa-fw fa-landmark mr-2"></i>Trust History</span>
                                </a>
                            </div>
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts_company_billing_trust_request_fund"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/companies/'.$client_id.'/billing/request_fund')}}">
                                    <span> <i class="fas fa-fw fa-hand-holding-usd  mr-2"></i> Requested Funds</span>
                                </a>
                            </div>
                            <div class="nav-item mr-4">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow <?php if(Route::currentRouteName()=="contacts_company_billing_invoice"){ echo "active"; } ?>" data-page="workflows" href="{{URL::to('contacts/companies/'.$client_id.'/billing/invoice')}}">
                                    <span> <i class="fas fa-fw fa-file-invoice  mr-2"></i>  Invoices</span>
                                </a>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="row">
                            <?php if(Route::currentRouteName()=="contacts_company_billing_trust_history"){
                                ?> @include('company_dashboard.billing.trust_history')
                            <?php } ?>
                            <?php if(Route::currentRouteName()=="contacts_company_billing_trust_request_fund"){
                                ?> @include('company_dashboard.billing.requested_fund',compact('totalData'))
                            <?php } ?>
                           
                            <?php if(Route::currentRouteName()=="contacts_company_billing_invoice"){
                                ?> @include('company_dashboard.billing.invoice')
                            <?php } ?>
                           
                        </div>             
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_company_messages"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_company_messages"){ ?>
                            @include('company_dashboard.messages')
                        <?php } ?>                    
                    </div>
                   
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="contacts_company_email"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="contacts_company_email"){ ?>
                            @include('company_dashboard.email')
                        <?php } ?>                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addClientLinkWithOption" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
            <h5 class="text-center my-4">Would you like to add a new or existing contact? </h5>
            <section class="ul-widget-stat-s1">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <a data-toggle="modal" data-target="#AddContactModal" data-placement="bottom" href="javascript:;" onclick="AddContactModal();"> 
                            <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{BASE_URL}}public/svg/contact.svg" width="60" height="60">
                                <div class="content">
                                    <p class="text-muted mt-2 mb-0">New Contact</p>
                                    <p class="text-primary text-24 line-height-1 mb-2"></p>
                                </div>
                            </div>
                        </div></a>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <a data-toggle="modal" data-target="#addExistingContact" data-placement="bottom" href="javascript:;" onclick="addExistingContact();"> 
                        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{BASE_URL}}public/svg/existing_contacts.svg" width="60" height="60">
                                <div class="content">
                                    <p class="text-muted mt-2 mb-0">Existing Contact</p>
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

<div id="AddContactModal" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="AddContactModalArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="addExistingContact" class="modal fade bd-example-modal-lg " tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Existing Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addExistingContactArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="unlinkClient" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="unlinkClientForm" id="unlinkClientForm" name="unlinkClientForms" method="POST">
            @csrf
            <input type="hidden" value="" name="client_id" id="delete_client_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Contact</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to remove <b><span id="clientName"></span></b> from this company?

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                        
                        <a data-toggle="modal" data-target="#AddCaseModel" data-placement="bottom" href="javascript:;" onclick="loadStep1FromCompnay();"> 
                            <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                            <div class="card-body text-center">
                                <img src="{{BASE_URL}}public/svg/court_case_add.svg" width="60" height="60">
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
                                <img src="{{BASE_URL}}public/svg/exisiting_case.svg" width="60" height="60">
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
          <img style="vertical-align: middle;" class="retina" src="{{BASE_URL}}public/images/ajax_arrows.gif" width="16" height="16" /> Working
        </span>
      </p>
  
      <p id='a' style='color: black;'>
        <input type="checkbox" name="user_delete_company_contact" id="user_delete_company_contact" >
        <label for="user_delete_company_contact">Remove all company contacts assigned to this case.</label>
      </p>
  </form>
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
<div id="loadTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntry">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="archiveCompany" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Archive Company</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="archiveCompanyArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="unarchiveCompany" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Unarchive Company</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="unarchiveCompanyArea">
                </div>
            </div>
        </div>
    </div>
</div>


<div id="deleteCompany" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="depostifundtitle">Delete Company</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">×</span>
                </button>
        </div>
        <div class="modal-body">
            <div id="deleteCompanyArea">
            </div>
        </div>
    </div>
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
<div id="addNewMessagePopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">New Message</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="addNewMessagePopupArea">
                </div>
            </div>
        </div>
    </div>
</div>


<style>
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
        afterLoader();
        $('#DeleteModal').on('hidden.bs.modal', function () {
            window.location.reload();
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

        var linkedClient =  $('#linkedClient').DataTable( {
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "bLengthChange": false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/companies/clientLoad", // json datasource
                type: "post",  // method  , by default get
                data :{ 'company_id' : '{{$company_id}}' },
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
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   
                    $('td:eq(0)', nRow).html('<div class="text-left"><div style="background-color: white;border: 0px solid black;border-radius: 2px;width: 32px;height: 32px;padding: 0px;display: inline-block;overflow: hidden;"><img class="" src="{{BASE_URL}}public/svg/default_avatar_32.svg" width="32" height="32"></div></div>');

                    $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.id+'">'+aData.name+'</a><br>Client</div>');
                    
                    var obj = JSON.parse(aData.caselist);
                    var i;
                    var urlList='';
                    for (i = 0; i < obj.length; ++i) {
                        urlList+='<a href="'+baseUrl+'/court_cases/'+obj[i].case_unique_number+'/info">'+obj[i].case_title+'</a>';
                        urlList+="<br>";
                    }
                    if(urlList==''){
                        $('td:eq(2)', nRow).html('<i class="table-cell-placeholder"></i>');
                    }else{
                        $('td:eq(2)', nRow).html('<div class="text-left">'+urlList+'</div>');
                    }
                    
                    $('td:eq(3)', nRow).html('<div class="text-left">'+aData.lastloginnewformate+'</div>');
                    
                    var d="'"+aData.id+"','"+aData.name+"'";

                    $('td:eq(4)', nRow).html('<div class="text-center"><a  href="javascript:;"  onclick="unlinkClient('+d+'); return false;" ><i class="fas fa-trash pr-3  align-middle"></i> </a></div>'); 
                },
              
                "initComplete": function(settings, json) {
                }
        });
        var linkedArchiveClient =  $('#linkedArchiveClient').DataTable( {
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "bLengthChange": false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/companies/clientArchiveLoad", // json datasource
                type: "post",  // method  , by default get
                data :{ 'company_id' : '{{$company_id}}' },
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
                { data: 'id',"orderable": false},
                { data: 'id',"orderable": false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   
                    $('td:eq(0)', nRow).html('<div class="text-left"><div style="background-color: white;border: 0px solid black;border-radius: 2px;width: 32px;height: 32px;padding: 0px;display: inline-block;overflow: hidden;"><img class="" src="{{BASE_URL}}public/svg/default_avatar_32.svg" width="32" height="32"></div></div>');

                    $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.id+'">'+aData.name+'</a><br>Client</div>');
                    
                    var obj = JSON.parse(aData.caselist);
                    var i;
                    var urlList='';
                    for (i = 0; i < obj.length; ++i) {
                        urlList+='<a href="'+baseUrl+'/court_cases/'+obj[i].case_unique_number+'/info">'+obj[i].case_title+'</a>';
                        urlList+="<br>";
                    }
                    if(urlList==''){
                        $('td:eq(2)', nRow).html('<i class="table-cell-placeholder"></i>');
                    }else{
                        $('td:eq(2)', nRow).html('<div class="text-left">'+urlList+'</div>');
                    }
                    
                    $('td:eq(3)', nRow).html('<div class="text-left">'+aData.lastloginnewformate+'</div>');
                    
                    var d="'"+aData.id+"','"+aData.name+"'";

                    $('td:eq(4)', nRow).html('<div class="text-center"><a  href="javascript:;"  onclick="unlinkClient('+d+'); return false;" ><i class="fas fa-trash pr-3  align-middle"></i> </a></div>'); 
                },
              
                "initComplete": function(settings, json) {
                }
        });
        $('#unlinkClientForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#unlinkClientForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#unlinkClientForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/saveUnLinkContact", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&company_id={{$company_id}}';
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

        var dataTable =  $('#LinkedCaseList').DataTable( {
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "bLengthChange": false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/companies/casesLoad", // json datasource
                type: "post",  // method  , by default get
                data :{ 'company_id' : '{{$company_id}}' },
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
                        $('td:eq(1)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>');
                    
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
                url: baseUrl + "/contacts/companies/unlinkFromCase", // json datasource
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
        var ClientNotesyGrid =  $('#ClientNotesyGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/companies/ClientNotes", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$company_id}}' },
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
        $('#depositAmountPopup').on('hidden.bs.modal', function () {
            billingTabTrustHistory.ajax.reload(null, false);
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
                data :{ 'user_id' : '{{$company_id}}' },
                error: function(){  // error handling
                    $("#billingTabTrustHistory").html("");
                    $("#billingTabTrustHistory").append('<tbody class="employee-grid-error"><tr><th colspan="8">There is no data to display based on these filter options,  please try different filter options</th></tr></tbody>');
                    $("#billingTabTrustHistory").css("display","none");
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

                    var clientLink='<a class="name" href="'+baseUrl+'/contacts/companies/'+aData.client_id+'">'+aData.client_name+' (Company)</a>';
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
            requestFundGrid.ajax.reload(null,false);
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
                url :baseUrl +"/contacts/companies/loadRequestedFundHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$company_id}}' },
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
                url: baseUrl + "/contacts/companies/deletePaymentEntry", // json datasource
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
                url:  baseUrl +"/contacts/companies/downloadTrustHistory", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&export=yes&user_id={{$company_id}}';
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
                url: baseUrl + "/contacts/companies/addEmailtouser", // json datasource
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
                url: baseUrl + "/contacts/companies/deleteRequestedFundEntry", // json datasource
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
    });


    function AddContactModal() {
        $("#AddContactModal").modal("show");
        $("#preloader").show();
        $("#AddContactModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/contacts/loadAddContactFromCompany", // json datasource
                data: {"company_id": "{{$company_id}}"},
                success: function (res) {
                    $("#AddContactModalArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function addExistingContact() {
        $("#addClientLinkWithOption").modal("hide");
        $("#preloader").show();
        $("#addExistingContactArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/addExistingContact", 
                data: {"company_id": "{{$company_id}}"},
                success: function (res) {
                    $("#addExistingContactArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function unlinkClient(id,name) {
        $("#unlinkClient").modal("show");
        $("#delete_client_id").val(id);
        $("#clientName").html(name);
        
    }
    function addExistingCase() {
        $("#addCaseLinkWithOption").modal("hide");
        $("#preloader").show();
        $("#addExistingCaseArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/addExistingCase", 
                data: {"company_id": "{{$company_id}}"},
                success: function (res) {
                    $("#addExistingCaseArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadStep1FromCompnay(id) {
        $("#preloader").show();
        $("#step-1").html('Loading....');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/case/loadStep1FromCompany", // json datasource
                data: {"company_id":{{$company_id}},'companylink':'yes'},
                success: function (res) {
                    $("#AddContactModal").modal('hide');
                    $("#step-1").html(res);
                    $("#preloader").hide();
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
        $dialog.find('#user_delete_company_contact').attr("checked",true);
    }
   
    function loadAddNotBox() {
        $("#preloader").show();
        $("#addNoteModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/addNotes", 
                data: {"user_id": "{{$company_id}}"},
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
                url: baseUrl + "/contacts/companies/editNotes", 
                data: {"user_id": "{{$company_id}}","id": id},
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
                    url: baseUrl + "/contacts/companies/loadTimeEntryPopup", // json datasource
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
                url: baseUrl + "/contacts/companies/addTrustEntry", 
                data: {"user_id": "{{$company_id}}"},
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
                url: baseUrl + "/contacts/companies/withdrawFromTrust", 
                data: {"user_id": "{{$company_id}}"},
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
                url: baseUrl + "/contacts/companies/refundPopup", 
                data: {"user_id": "{{$company_id}}",'transaction_id':id},
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
                url: baseUrl + "/contacts/companies/addRequestFundPopup", 
                data: {"user_id": "{{$company_id}}"},
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
                url: baseUrl + "/contacts/companies/editFundRequest", 
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
                url: baseUrl + "/contacts/companies/sendFundReminder", 
                data: {"id": id},
                success: function (res) {
                    $("#sendFundReminderArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    //Billing Tab @END


    //Message Tab @END
    function addNewMessagePopup() {
        $("#preloader").show();
        $("#addNewMessagePopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/addNewMessagePopup", 
                data: {"user_id": "{{$company_id}}"},
                success: function (res) {
                    $("#addNewMessagePopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    //Message Tab @END

    function archiveCompany() {
        $("#preloader").show();
        $("#archiveCompanyArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/archiveCompanyPopup", 
                data: {"company_id": "{{$company_id}}"},
                success: function (res) {
                    $("#archiveCompanyArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function unarchiveCompany() {
        $("#preloader").show();
        $("#unarchiveCompanyArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/unarchiveCompanyPopup", 
                data: {"company_id": "{{$company_id}}"},
                success: function (res) {
                    $("#unarchiveCompanyArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
      
    function deleteCompany() {
        $("#preloader").show();
        $("#deleteCompanyArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/deleteCompanyPopup", 
                data: {"company_id": "{{$company_id}}"},
                success: function (res) {
                    $("#deleteCompanyArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

</script>
@stop