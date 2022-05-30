@extends('layouts.master')
@section('title', 'Attorney Details')
@section('main-content')

<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
$userTitle = unserialize(USER_TITLE); 

?>
<div class="d-flex align-items-center pl-4 pb-4">
    <i class="fas fa-user-circle fa-2x"></i>
    <h2 class="mx-2 mb-0 text-nowrap">
        <?php echo $userProfile->first_name;?>
        <?php echo $userProfile->last_name;?>
        <?php if($userProfile->user_status=="3"){?>
        <span class="text-danger">[ Inactive ]</span>
        <?php } ?>
    </h2>
    <div class="ml-auto d-flex align-items-center d-print-none">
        <a  href="{{ route('contacts/attorneys') }}"> <button
            class="btn btn-light btn-rounded  m-1 px-5" type="button" >Back</button></a>

        <a data-toggle="modal" data-target="#DeleteModal" data-placement="bottom" href="javascript:;"> <button
                class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="loadProfile();">Edit</button></a>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-body">
                <span id="responseMain"></span>
                <nav class="test-general-settings-nav p-0 pt-0" role="navigation">
                    <ul class="nav nav-pills flex-column text-wrap">
                        <div class="mb-3">
                            <div class="p-4">
                                <div class="mb-4">
                                    <div class="font-weight-bold">Cases:</div>
                                    <div class="text-muted">No cases assigned to this firm member.</div>
                                </div>
                                <div class="mb-4">
                                    <div class="font-weight-bold">Default Rate:</div>

                                    <a id="link_rate19630204" class="default-rate-link btn btn-link pl-0" href="#"
                                        onclick="return false; return false;">$<?php echo $userProfile->default_rate;?>/
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
    <div class="col-md-9">
        <div class="card mb-4 o-hidden">
            @include('pages.errors')

            <div class="card-body">

                <ul class="nav nav-tabs" id="myTab" role="tablist">

                    <li class="nav-item"><a class="nav-link active show" id="profile-basic-tab" data-toggle="tab"
                            href="#profileBasic" role="tab" aria-controls="profileBasic" aria-selected="true">Info</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" id="contact-basic-tab" data-toggle="tab"
                            href="#contactBasic" role="tab" aria-controls="contactBasic" aria-selected="false">Cases</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">

                    <div class="tab-pane fade active show" id="profileBasic" role="tabpanel"
                        aria-labelledby="profile-basic-tab">
                        <div id="contact_info_page" style="display: block;">
                            <div class="d-md-flex align-items-md-start w-100">
                                <div class="d-flex flex-column col-md-3 align-items-center d-print-none pb-4">
                                    <div>
                                        <i class="fas fa-user-circle fa-5x text-black-50"></i>
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
                                              
                                                <a data-toggle="modal" data-target="#deactivateUser" data-placement="bottom" href="javascript:;"> <button
                                                        class="btn btn-outline-danger text-nowrap deactivate-user" type="button" ">Deactivate
                                                        Paralegal</button>
                                                </a>
                                                    
                                            </div>
                                        </div>
                                    </div>
                                <?php } else {?>
                                    <div class="mt-md-2">
                                        <div class="alert alert-danger">
                                          This paralegal cannot be reactivated
                                          until  <?php
                                          echo $next_due_date = date('M j, Y H:i A', strtotime($userProfile->updated_at. ' +30 days')); ?>
                                          <br>
                                          <br>
                                          <a class="font-weight-bold alert-danger" href="#" target="_blank" rel="noopener noreferrer">
                                            Learn how to reactivate a user
                                          </a>
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
                    <div class="tab-pane fade" id="contactBasic" role="tabpanel" aria-labelledby="contact-basic-tab">
                        3 Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic
                        lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify pitchfork tattooed
                        craft beer, iphone skateboard locavore.

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
                        <div class="alert alert-info"><b>What happens when I deactivate a user?</b><ul><li>Deactivated users will not be able to login to {{config('app.name')}} .</li><li>You will not be charged for deactivated users.</li><li>Once deactivated, you cannot reactivate a user for 30 days.</li><li>Documents, tasks, events, notes, and billing associated with this user will remain in {{config('app.name')}} .</li><li>Calendar events will stop syncing with integrated calendars.</li></ul></div>
                        <div><span class="font-weight-bold">Note: </span>You can reassign this user's tasks and events on the next screen to any active user. Alternatively tasks and events can be reassigned after a user is deactivated.</div>
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
    });
    
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
        
        $("#preloader").show();
        $("#LoadProfile").html('');
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


</script>

@stop



