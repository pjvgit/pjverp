<div id="AddCaseModelUpdate" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" bladefile="resources/views/commonPopup/add_case.blade.php">
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
                            <input type="hidden" class="form-control" id="returnPage" value="" />
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
                                                            <?php foreach(userClientList() as $Clientkey=>$Clientval){ ?>
                                                            <option value="{{$Clientval->id}}">{{substr($Clientval->name,0,30)}}</option>
                                                            <?php } ?>
                                                        </optgroup>
                                                        <optgroup label="Company">
                                                            <?php foreach(userCompanyList() as $Companykey=>$Companyval){ ?>
                                                            <option value="{{$Companyval->id}}">{{substr($Companyval->name,0,50)}}</option>
                                                            <?php } ?>
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

                                                <button class="btn btn-primary ladda-button example-button m-1" type="button" id="submit_without_user" style="display:none;" onclick="StatusLoadStep2();">Continue without picking a contact
                                                </button>
                                                
                                                <button type="button" class="btn btn-primary ladda-button example-button m-1" id="submit_with_user" style="display:none;" type="submit" onclick="StatusLoadStep2();">Continue to case Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="step-2">
                                        <div id="showError2" style="display:none"></div>
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
                                            <div class="form-group row" id="case_area_dropdown">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <select id="practice_area" name="practice_area" class="form-control custom-select col">
                                                        <option value="-1"></option>
                                                        <?php foreach(casePracticeAreaList() as $k=>$v){?>
                                                        <option value="{{$k}}">{{$v}}</option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="caseShowText();" href="javascript:;">Add
                                                        new practice area</a></label>
                                            </div>
                                            <div class="form-group row" id="case_area_text">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <input class="form-control" id="practice_area_text" value="" name="practice_area_text" type="text"
                                                        placeholder="Enter new practice area">
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="caseShowDropdown();"
                                                        href="javascript:;">Cancel</a></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case stage
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_status" name="case_status" class="form-control custom-select col">
                                                        <option value="0"></option>
                                                        <?php 
                                                        foreach(caseStageList() as $kcs=>$vcs){?>
                                                        <option value="{{$vcs->id}}">{{$vcs->title}}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Date opened</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_open_date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" name="case_open_date" type="text"
                                                    placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Office
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_office" name="case_office" class="form-control custom-select col">
                                                        <?php  foreach(firmAddressList() as $k=>$v){?>
                                                            <option value="{{ $v->id }}">{{ $v->office_name }}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="case_description" class="form-control" rows="5"></textarea>
                                                </div>
                                            </div>
                                            @if(IsCaseSolEnabled() == 'yes')
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
                                                    @forelse (firmSolReminders() as $key => $item)
                                                        <div class="row form-group fieldGroup">
                                                            <div class="col-md-2 form-group mb-3">
                                                                <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  ">
                                                                    @foreach(getEventReminderTpe() as $k =>$v)
                                                                            <option value="{{$k}}" <?php if(@$item->reminder_type == $k){ echo "selected=selected"; } ?>>{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 form-group mb-3">
                                                                <input class="form-control" id="reminder_days" value="{{ @$item->reminer_days }}" name="reminder_days[]" type="number" min="0"> 
                                                            </div> <span class="pt-2">Days</span>
                                                            <div class="col-md-2 form-group mb-3">   
                                                                <button class="btn remove" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                            </div>
                                                        </div>
                                                    @empty
                                                    @endforelse
                                                    <div class="test-sol-reminders fieldGroup">
                                                        
                                                        <div>
                                                            <button type="button" class="btn btn-link pl-0 add-more">Add a reminder</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="fieldGroupCopy copy hide" style="display: none;">
                                                <div class="col-md-2 form-group mb-3">
                                                    <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  ">
                                                    @foreach(getEventReminderTpe() as $k =>$v)
                                                        <option value="{{$k}}">{{$v}}</option>
                                                    @endforeach
                                                    </select>
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
                                        @endif
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
                                                    <?php foreach(firmUserList() as $key=>$user){?>
                                                    <option <?php if($user->id==Auth::User()->id){ echo "selected=selected"; } ?> value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
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
                                                    <?php foreach(firmUserList() as $key=>$user){?>
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
                                                    <th><input class="all-users-checkbox" id="select-all" type="checkbox"></th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>User Title</th>
                                                    <th>Billing Rate</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach(firmUserList() as $key=>$user){?>
                                                <tr>
                                                    <td><input <?php if($user->id==Auth::User()->id){ echo "checked=checked";} ?> class="users-checkbox" type="checkbox" id="{{$user->id}}" name="selectedUSer[{{$user->id}}]"></td>
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

@section('page-js-common')
<script type="text/javascript">
    $(document).ready(function() {
        $(".add-more").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() + '</div>';
            $('body').find('.fieldGroup:last').after(fieldHTML);
        });
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
        $("#user_type").select2({
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#AddCaseModelUpdate"),
        });
        $('#createCase').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        }); 
    });
    function loadAllStep() {
        $('#smartwizard').smartWizard("reset"); 
        $('#createCase')[0].reset();
        $("#user_type").select2("val", "");
    }
    function selectUserAutoLoad(id) {
        $("#innerLoader").css('display', 'block');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveSelectdUser",
            data: {
                "selectdValue": id
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
    function selectUser() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#user_type option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveSelectdUser",
            data: {"selectdValue": selectdValue},
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
    function StatusLoadStep2() {
        $('#smartwizard').data('smartWizard')._showStep(1); // go to step 3....
    }
    function backStep1() {
        $('#smartwizard').smartWizard('prev');
    }

    function StatusLoadStep3() {
        var case_name = $("#case_name").val();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/checkCaseNameExists", // json datasource
            data: {case_name : case_name},
            success: function (res) {
                if (res.errors != '') {
                    $('#showError2').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                            $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                    errotHtml += '</ul></div>';
                    $('#showError2').append(errotHtml);
                    $('#showError2').show();
                    $('#AddCaseModelUpdate').animate({
                        scrollTop: 0
                    }, 'slow');
                    result = false;                    
                }else{
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
            }
        });
    }
   
    function backStep2() {
        $('#smartwizard').smartWizard('prev');
    }

    function StatusLoadStep4() {
        $('#smartwizard').data('smartWizard')._showStep(3); 

    }
    function backStep3() {
        $('#smartwizard').smartWizard('prev');
        
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
                        $("returnPage").val('');
                        <?php session(['popup_success' => '']); ?>
                        localStorage.setItem("case_id", res.case_id);
                        $('#AddCaseModelUpdate').modal("hide");
                        loadCaseDropdown();
                    }
                }
            });
        
    }
     

    function callOnClick(){
        $("#beforebutton").hide();
        $("#beforetext").show();
        $("#submit_without_user").show();
        $("#submit_with_user").hide();
        $("#case_name").focus();

    }        
    $("#case_area_text").hide();
    function caseShowText() {
        $("#case_area_text").show();
        $("#case_area_dropdown").hide();
        return false;
    }

    function caseShowDropdown() {
        $("#case_area_text").hide();
        $("#case_area_dropdown").show()
        return false;
    }
    
    function selectMethod() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#billingMethod option:selected").val();
        if (selectdValue == 'mixed' || selectdValue == 'flat') {
            $("#billing_rate_text").show();
        } else {
            $("#billing_rate_text").hide();
        }
    }

    $(".all-users-checkbox").click(function () {
        $(".users-checkbox").prop('checked', $(this).prop('checked'));
    });
    $(".users-checkbox").click(function () {
        if ($('.users-checkbox:checked').length == $('.users-checkbox').length) {
            $('.all-users-checkbox').prop('checked', true);
        } else {
            $('.all-users-checkbox').prop('checked', false);
        }
    });

    $('#AddCaseModelUpdate').on('hidden.bs.modal', function () {
        if($("returnPage").val() == ''){
            window.location.reload();
            $("returnPage").val('');
        }
    });

    function selectAttorney() {
        var selectdValue = $("#originating_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }

    function selectLeadAttorney() {
        var selectdValue = $("#lead_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
    
    function backStep3() {
        $('#smartwizard').smartWizard('prev');
        return false;
    }

    function AddContactModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#step-1-again").html('');
        $(function () {
            $.ajax({
                type: "POST",
                // url:  baseUrl +"/contacts/loadAddContactFromCase", // json datasource
                url:  baseUrl +"/contacts/loadAddContact", // json datasource
                // data: 'loadStep1',
                data: { action : 'add_case_with_billing'},
                success: function (res) {
                    $("#step-1-again").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');
                    return false;
                }
            })
        })
    }
</script>

@stop