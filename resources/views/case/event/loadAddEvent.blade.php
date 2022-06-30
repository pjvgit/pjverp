<style>
.sharing-list .indent {
    text-indent: 20px;
}
</style>
<form class="createEvent" id="createEvent" name="createEvent" method="POST">
    @csrf
    <div class="row" bladefilename="resources/views/case/event/loadAddEvent.blade.php">
        <div class="col-8">
            <div id="showError" style="display:none"></div>
            <?php if(Auth::User()->add_event_guide=="0"){?>   
                <div class="add-event-helper-tip px-2" id="add_event_guide">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <a class="close add_event_guide">×</a>
                        <p class="mb-0"><b>Get Started with Events:</b> Most events are linked to cases. To create events for your firm just check "This event is not linked to a case". After that you can choose whom to share it with and whether their attendance is required. You can also add a location, save your regularly used locations, or add an address to make getting directions easy.<br><a href="#" rel="noopener noreferrer" target="_blank"><u>Learn more about adding events.</u></a>
                        </p>
                    </div>
                </div>
        <?php } ?>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
            </div>

            <input type="hidden" id="add_event_text_case_id" value="" name="text_case_id">
            <input type="hidden" id="add_event_text_lead_id" value="" name="text_lead_id">
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Case or Lead</label>
                <div class="col-8 form-group mb-3">
                    <select onChange="changeCaseUser()" class="form-control case_or_lead" id="add_event_case_or_lead" name="case_or_lead"
                        data-placeholder="Search for an existing contact or company">
                        <option value="">Search for an existing Case or Lead</option>
                        <optgroup label="Court Cases">
                            <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                            <option uType="case" <?php if(!empty($case_id) && $case_id==$Caseval->id){ echo "selected=selected"; }?>
                                value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}} <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?> <?php if($Caseval->case_close_date!=NULL){  echo "[Closed]"; }?> </option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Leads">
                            <?php foreach($caseLeadList as $caseLeadListKey=>$caseLeadListVal){ ?>
                            <option uType="lead" <?php if(!empty($lead_id) && $lead_id==$caseLeadListVal->id){ echo "selected=selected"; }?> value="{{$caseLeadListVal->id}}">{{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                            <?php } ?>
                        </optgroup>
                    </select>

                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-8 form-group mb-3">
                    <label class="form-check-label">
                        <input class="mr-2 add_event_no_case_link" type="checkbox" id="add_event_no_case_link" name="no_case_link">
                        <span>This event is not linked to a case</span>
                    </label>

                    <span id="CaseListError"></span>

                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Event name</label>
                <div class="col-md-6 form-group mb-3">
                    <input class="form-control" id="event_name" value="" name="event_name" type="text" maxlength="512">
                </div>
                <div class="col-md-2 form-group mb-3">
                    <div class="form-category-picker h-100 dropdown-color">
                        <input type="hidden" value="" id="event_type" name="event_type">
                        <button type="button" aria-haspopup="true" aria-expanded="false"
                            class="align-items-start btn-rounded p-0 h-100 w-80 btn btn-secondary">
                            <div id="chosen-category" class="d-flex justify-content-start" data-color="#6edcff"
                                style="cursor: pointer; text-align: center;" data-value="928231">
                                <div id="colorSet"
                                    style="background-color:#B9AFAF; border-radius: 2px; height: 25px; margin: 5px; width: 24px;">
                                </div><span class="Select-arrow ml-1 mt-3"></span>
                            </div>
                        </button>
                        <div tabindex="-1" role="menu" aria-hidden="true" id="dropdown-menu"
                            class="color-drop-down p-1 dropdown-menu">
                            <div onclick="selectColor('#E5E5E5','0')" id="no-item-category"
                                class="d-flex align-items-center justify-content-center"
                                style="border-bottom: 1px solid rgb(229, 229, 229); color: rgb(229, 229, 229); cursor: pointer; height: 25px; margin: 5px; padding-bottom: 5px;"
                                data-color="#E5E5E5">None</div>
                            <?php foreach($allEventType as $ekey=>$eval){ ?>

                                        <div onclick="selectColor('{{$eval->color_code}}','{{$eval->id}}')"
                                            id="item_category_928233" class="item_category d-flex justify-content-start"
                                            data-color="#ceaff2" data-value="928233" style="cursor: pointer; text-align: center;">
                                            <div id="{{$eval->id}}"
                                                style="background-color: {{$eval->color_code}}; border-radius: 2px; height: 25px; margin: 5px; width: 24px;">
                                            </div>
                                            <div style="line-height: 35px;">{{$eval->title}}</div>
                                        </div>
                            <?php } ?>
                            <div id="customize-types" class="align-items-start"
                                style="border-top: 1px solid rgb(229, 229, 229); color: rgb(0, 112, 187); cursor: pointer; height: 25px; margin: 5px; padding-top: 5px;"
                                data-color="#0070bb"><a href="{{BASE_URL}}item_categories">Customize Types</a></div>
                        </div>
                    </div>

                </div>
            </div>
            <span id="dateInputPanel">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-2 col-form-label">Start</label>
                    <div class="col-md-2 form-group mb-3">
                        <input class="form-control input-date input-start event_start_date" id="add_event_start_date" value="{{ (!empty($currentDate)) ? date('m/d/Y',strtotime($currentDate)) : convertUTCToUserTimeZone('dateOnly') }}" name="start_date" type="text"
                            placeholder="mm/dd/yyyy">

                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <?php 
                        $time=date('H:i',strtotime($currentDateTime));
                        $new_time= date('H:i', strtotime($time.'+1 hour')); ?>
                        <input class="form-control input-time input-start" id="add_event_start_time" value="{{date('h:i A',strtotime($new_time))}}" name="start_time" type="text" placeholder="">
                    </div>
                    <div class="col-md-2 form-group mb-3 pt-2">
                        <label class="form-check-label"><input class="mr-2 all-day all_day" type="checkbox" id="all_day" name="all_day"><span>All day</span></label>
                    </div>
                    <div class="col-md-3 form-group mb-3 pt-2">
                        <label class="form-check-label"><input title="You can not edit recurring events"
                                class="mr-2 recuring_event" id="recuring_event" name="recuring_event" type="checkbox"><span>This event
                                repeats</span></label>

                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-2 col-form-label">End</label>
                    <div class="col-md-2 form-group mb-3 ">
                        <input class="form-control input-date input-end" id="add_event_end_date" value="{{ (!empty($currentDate)) ? date('m/d/Y',strtotime($currentDate)) : convertUTCToUserTimeZone('dateOnly') }}" name="end_date" type="text"
                            placeholder="mm/dd/yyyy">

                    </div>
                  
                    <div class="col-md-2 form-group mb-3">
                        <?php $new_time= date('H:i', strtotime($new_time.'+1 hour')); ?>
                        <input class="form-control input-time input-end" id="add_event_end_time" value="{{date('h:i A',strtotime($new_time))}}"
                            name="end_time" type="text" placeholder="">

                    </div>
                    

                </div>
            </span>
            
            <div class="form-group row recurring-div" id="repeat_dropdown">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Frequency</label>
                <div class="col-md-3 form-group mb-3">
                    <select onchange="selectType(this.value, 'loadAddEventPopup')" id="event-frequency" name="event_frequency"
                        class="form-control custom-select  ">
                        <option value="DAILY">Daily</option>
                        <option value="EVERY_BUSINESS_DAY" selected="selected">Every Business Day</option>
                        <option value="CUSTOM">Weekly</option>
                        <option value="WEEKLY">Weekly on {{date('l')}}</option>
                        <option value="MONTHLY">Monthly</option>
                        {{-- Commented. As per client's requirement --}}
                        {{-- <option value="YEARLY">Yearly</option>  --}} 
                    </select>
                </div>
                {{-- Commented. As per client's requirement --}}
                {{-- <div class="col-md-5 form-group mb-3 repeat_yearly recurring-div">
                    <select id="yearly-frequency" name="yearly_frequency" class="form-control custom-select  ">
                        <option value="YEARLY_ON_DAY">On day {{date('d')}} of {{date('F')}}</option>
                        <option value="YEARLY_ON_THE">On the fourth {{date('l')}} of {{date('F')}}</option>
                        <option value="YEARLY_ON_THE_LAST">On the last {{date('l')}} of {{date('F')}}</option>
                    </select>
                </div> --}}
                <div class="col-md-5 form-group mb-3 repeat_monthly recurring-div">
                    <select id="monthly-frequency" name="monthly_frequency" class="form-control custom-select  ">
                        <option value="MONTHLY_ON_DAY">On day {{date('d')}}</option>
                        <option value="MONTHLY_ON_THE">On the fourth {{date('l')}}</option>
                        <option value="MONTHLY_ON_THE_LAST">On the last {{date('l')}}</option>
                    </select>
                </div>
            </div>
            <div class="form-group row recurring-div" id="repeat_custom">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="custom-box">
                    <div>
                        <div class="custom-rule-form align-items-baseline mt-2 mb-2 d-flex w-50"><span>Repeat
                                every</span><input class="form-control mx-2 w-25" name="daily_weekname" type="number" min="1" max="100"
                                value="1"><span class="ml-1">week(s)</span></div>
                        <div>
                            <div class="filter-options d-flex">
                                <div class="mr-3"><label class="d-inline-flex align-items-center">
                                    <input id="Sun-option" name="custom[]" value="Sunday" type="checkbox"><span class="ml-2 ">Sun</span></label>
                                </div>
                                <div class="mr-3">
                                    <label class="d-inline-flex align-items-center">
                                        <input id="Mon-option" name="custom[]" value="Monday" type="checkbox"><span class="ml-2 ">Mon</span></label>
                                </div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center">
                                    <input id="Tues-option" name="custom[]" value="Tuesday" type="checkbox"><span class="ml-2 ">Tue</span></label>
                                </div>
                                <div class="mr-3">
                                    <label class="d-inline-flex align-items-center">
                                        <input id="Wed-option" name="custom[]" value="Wednesday" type="checkbox"><span class="ml-2 ">Wed</span>
                                    </label>
                                </div>
                                <div class="mr-3">
                                    <label class="d-inline-flex align-items-center">
                                        <input id="Thurs-option" name="custom[]" value="Thursday" type="checkbox" ><span class="ml-2 ">Thu</span>
                                    </label>
                                </div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center">
                                    <input id="Fri-option" name="custom[]" value="Friday" type="checkbox"><span class="ml-2 ">Fri</span></label>
                                </div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center">
                                    <input id="Sat-option"  name="custom[]" value="Saturday" type="checkbox"><span class="ml-2 ">Sat</span></label>
                                </div>
                            </div>
                            <span class="error custom-error"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Commented. As per client's requirement --}}
            {{-- <div class="form-group row repeat_yearly recurring-div" id="repeat_yearly">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>Repeat every</span><input
                        class="form-control mx-2 w-25"  name="event_interval_year" type="number" value="1" min="1" max="100"><span>year(s)</span>
                </div>
            </div> --}}
            <div class="form-group row repeat_monthly recurring-div" id="repeat_monthly">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>Repeat every</span><input
                        class="form-control mx-2 w-25" name="event_interval_month" type="number" min="1" max="100"
                        value="1"><span>month(s)</span></div>
            </div>
            <div class="form-group row recurring-div" id="repeat_daily">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="d-flex flex-row align-items-center w-50"><span>Repeat every</span>
                    <input class="form-control mx-2 w-25" name="event_interval_day" type="number" min="1" max="100"
                        value="1"><span>day(s)</span>
                </div>
            </div>
            <div class="form-group row endondiv recurring-div" id="endondiv">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>End on</span>
                    <input class="mx-2 w-50 form-control" id="add_event_end_on" value="" readonly name="end_on" type="text"
                        placeholder="mm/dd/yyyy"><label class="form-check-label" >
                        <input class=" pt-2" type="checkbox" checked="checked" id="no_end_date_checkbox"
                            name="no_end_date_checkbox">
                        <span>No end date</span>
                    </label>
                 
                </div>
                
            </div>
            <div class="form-group row" >
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50">
                    <span id="EndOnListError"></span>
                </div>
                
            </div>
            <div class="form-group row pre-load-location">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Location</label>
                <div class="col-md-6 form-group mb-3">
                    <select id="case_location_list" name="case_location_list" class="form-control custom-select col">
                        <option value="">Please select location</option>
                        <?php
                        foreach($eventLocation as $key=>$val){
                            ?>
                        <option value="{{$val->id}}">{{$val->location_name}}</option>
                        <?php 
                        }
                        ?>
                    </select>
                </div>
                <label id="add_new_label" for="inputEmail3" class="col-sm-2 col-form-label">
                    <a onclick="openAddLocation();" href="javascript:;">Add Location</a>
                </label>

            </div>
            <div class="form-group row  add-new ">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Location Name</label>
                <div class="col-md-6 form-group mb-3">
                    <input class="form-control" id="location_name" value="" name="location_name" type="text"
                        placeholder="">
                </div>
                <label id="cancel_new_label" for="inputEmail3" class="col-sm-2 col-form-label">
                    <a onclick="closeAddLocation();" href="javascript:;">Cancel</a>
                </label>
            </div>
            <div class="form-group row  add-new ">
                <label for="inputEmail3" class="col-sm-2 ml-2 col-form-label"></label>
                <div class="col-md-8 form-group mb-3">
                    <label class="form-check-label">
                    <input class="mr-2 location_future_use" type="checkbox" id="location_future_use" name="location_future_use">
                        <span> Save location for future use</span>
                    </label>
                </div>
            </div>
            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
                <div class="col-md-8 form-group mb-3">
                    <input class="form-control" id="address" name="address" maxlength="255" value="" type="text"
                        placeholder="Enter address">
                </div>
            </div>
            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Address2</label>
                <div class="col-md-8 form-group mb-3">
                    <input class="form-control" id="address2" name="address2" maxlength="255" value="" type="text"
                        placeholder="Enter address2">
                </div>
            </div>

            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="city" name="city" value="" maxlength="255" placeholder="Enter city">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="state" name="state" value="" maxlength="255"
                        placeholder="Enter state">
                </div>
                <div class="col-md-2 form-group mb-3">
                    <input class="form-control" id="postal_code" value="" maxlength="8" name="postal_code"
                        placeholder="Enter postal code">
                </div>

            </div>
            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Country</label>
                <div class="col-md-8 form-group mb-3">
                    <select class="form-control country" id="country" name="country" data-placeholder="Select Country"
                        style="width: 100%;">
                        <option value="">Select Country</option>
                        <?php foreach($country as $key=>$val){?>
                        <option value="{{$val->id}}"> {{$val->name}}</option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                <div class="col-md-8 form-group mb-3">
                    <textarea id="description" name="description" class="form-control " placeholder="" rows="5"
                        style="max-height: 600px; overflow: hidden; overflow-wrap: break-word; resize: none; height: 111.6px;"
                        spellcheck="false" data-gramm="false"></textarea>
                    <p class="form-text text-muted mb-1">This description will be viewable by anyone invited to this
                        event.
                    </p>
                </div>
            </div>
            <div class="form-group row">
                <label for="reminders" class="col-sm-2 col-form-label">Reminders</label>
                <div class="col">
                    <p><b>Note:</b> You must be linked to this Event in order to create reminders.</p>
                    <div id="event_reminder_div">
                        <div class="fieldGroupEventReminder">
                        </div>
                        <div class="text-muted mb-2">You can only edit reminders that you created. Reminders assigned to you by another firm user will need to be edited by the creator.</div>
                        <div><button type="button" class="btn btn-link p-0 test-add-new-reminder add-new-reminder add-more-event-reminder">Add a reminder</button></div>
                    </div>
                </div>
            </div>  
        
            @include('case.event.add_more_reminder_div')

            <div class="form-group row pt-">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-check-label"><input class="mr-2" type="checkbox" name="is_event_private">
                        <span>Mark this event as private</span></label>
                </div>
            </div>
            <div id="loadUserAjax"></div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
            </div>
        </div>
        <div class="col-4">
            <?php if(Auth::User()->add_event_guide2=="0"){ ?>
                <div class="client-task-tip" id="add_event_guide2">
                    <div class="alert alert-info">
                        <a class="close closeGuider add_event_guide2">×</a>
                        <div class="tooltip-message">Assign a task to your client and they will receive a link
                            to view and complete it via the client portal. <a href="#" rel="noopener noreferrer"
                                target="_blank"><u>What will my client see?</u></a>
                        </div>
                        <div></div>
                    </div>
                </div>
            <?php } ?>

            <div class="empty-sharing-list text-muted text-left hideUser" id="hideUser"><p>Select a case or lead to share this event with clients and firm members. Check "This event is not linked to a case or lead" to share only with firm members.</p></div> 
            <section class="sharing-list" id="add_event_right_section">
                <div class="loader-bubble loader-bubble-primary right-section-loader" style="display: none;"></div>
            </section>
        </div>
    </div>
  
    <div class="justify-content-between modal-footer">
        <div></div>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
        <div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>
            <button class="btn btn-primary example-button m-1 add-event-submit" type="submit" data-style="expand-left">Save Event </button>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
      
        // loadCaseClient({{$case_id}});
        // loadCaseNoneLinkedStaff({{$case_id}});
        // loadCaseLinkedStaff({{$case_id}});

        // set default disabled end time
        $("#add_event_end_on").attr("disabled", true);

        $(".case_or_lead").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadAddEventPopup"),
        });

        if( $('#case_id').length || $('#lead_id').length ) {
            changeCaseUser();
        }
        $("#reminder_user_type option[value='client-lead']").hide();
       
        $('#dateInputPanel .input-time').timepicker({
            'showDuration': false,
            'timeFormat': 'g:i A',
            'forceRoundTime': true,
            // 'maxTime': '23:30pm',
        });

        // Initialize Date Pickers
        $('#dateInputPanel .input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });

        // Initialize Datepair
        var dateContainer = document.getElementById('dateInputPanel');
        var datepair = new Datepair(dateContainer, {
            'dateClass': 'input-date',
            'timeClass': 'input-time',
            'startClass': 'input-start',
            'endClass': 'input-end'
        });

        $("#add_event_start_date").datepicker().on('change',function(e){
            $(this).removeClass('error');
            $("#end_date-error").text('');
            updateMonthlyWeeklyOptions();
        });
        $("#add_event_end_date").datepicker().on('change',function(e){
            $(this).removeClass('error');
            $("#start_date-error").text('');
        });
     
        $("#loadAddEventPopup #add_event_end_on").datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        }).on('change',function(e){
            $(this).removeClass('error');
            $('#EndOnListError').text('');

        });
        
       
        $(".hideUser").hide();
        $(".innerLoader").css('display', 'none');
        $(".add-new").hide();
        $("#cancel_new_label").hide();
        $("#add_new_label").show();
        $(".dropdown-color").on('click', function () {
            $("#dropdown-menu").toggle();
        });

         
        /* $("#loadAddEventPopup").on('click', '#HideShowNonlink', function () {
            $("#loadAddEventPopup .staff-table-nonlinked").toggle();
        }); */
        
        
        $("#endondiv").hide();
        $('#repeat_dropdown').hide();

        $("#repeat_daily").hide();
        $("#repeat_custom").hide();
        $(".repeat_monthly").hide();
        $(".repeat_yearly").hide();
        // $(".hide").hide();

        $('#createEvent').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroupEventReminder').remove();
        });

        $("#createEvent").validate({
            rules: {
                case_or_lead: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#add_event_no_case_link:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    }
                },
                end_on: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#loadAddEventPopup #no_end_date_checkbox:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    },
                    dateGreaterThan:  {
                        depends: function (element) {
                            var status = true;
                            if ($("#loadAddEventPopup #no_end_date_checkbox:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    }
                },
                // As per client's requirement
                /* postal_code:{
                    required:true
                }, */
                start_date:{
                    required:true
                },
                end_date:{
                    required:true
                },
                start_time:{
                    required:true
                },
                end_time:{
                    required:true
                },
                "custom[]": {
                    required: {
                        depends: function (element) {
                            if ($("#event-frequency").val() == "CUSTOM") {
                                console.log($("input[name='custom[]']:checked").length);
                                if($("input[name='custom[]']:checked").length > 0)
                                    return false;
                                else
                                    return true;
                            }
                            return false;
                        }
                    }

                }
            },
            messages: {
                case_or_lead: {
                    required: "Select a court case or check not linked to a case"

                }, 
                end_on: {
                    required: "Please provide an end date or select no end date."

                },
                // As per client's requirement
                /* postal_code: {
                    required: "Please provide numeric value."

                }, */
                start_date: {
                    required: "Start date is invalid"

                },
                end_date: {
                    required: "End date is invalid"

                }, 
                start_time: {
                    required: "Start time is invalid"

                },
                end_time: {
                    required: "End time is invalid"

                },
                "custom[]": {
                    required: "Please select a day for weekly event"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#add_event_case_or_lead')) {
                    $('.select2-container--classic .select2-selection--single').addClass("input-border-error");
                    error.appendTo('#CaseListError');
                }else if (element.is('#add_event_end_on')) {
                    error.appendTo('#EndOnListError');
                }else if (element.attr('name') == 'custom[]') {
                    error.appendTo('.custom-error');
                } else {
                    element.after(error);
                }
            }
        });

        $('.case_or_lead').on('select2:select', function (e) { 
            $('.select2-container--classic .select2-selection--single').removeClass("input-border-error");
            $('#CaseListError').text('');
        });

        $('#createEvent').submit(function (e) {
            e.preventDefault();
           $(this).find(":submit").prop("disabled", true);
            $(".innerLoader").css('display', 'block');
            if (!$('#createEvent').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.add-event-submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("#createEvent").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveAddEventPage", // json datasource
                data: dataString,
                success: function (res) {
                    $(this).find(":submit").prop("disabled", true);
                    $(".innerLoader").css('display', 'block');
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
                        $(".innerLoader").css('display', 'none');
                        $('.add-event-submit').removeAttr("disabled");
                        $('#loadAddEventPopup').animate({ scrollTop: 0 }, 'slow');

                        return false;
                    } else {
                        $('#loadAddEventPopup').modal("hide");
                        @if($fromPageRoute == "events")
                            $('#calendarq').fullCalendar('refetchEvents');
                        @else
                            window.location.reload();
                        @endif
                    }
                }
            });
        });
        $("#loadAddEventPopup input:checkbox.all_day").click(function () {
            if ($(this).is(":checked")) {
                $("#add_event_start_time").attr("readonly", true);
                $("#add_event_end_time").attr("readonly", true);

            } else {
                $('#add_event_start_time').removeAttr("readonly");
                $('#add_event_end_time').removeAttr("readonly");

            }
        });
        $("input:checkbox.add_event_no_case_link").click(function () {
            if ($(this).is(":checked")) {
                $('#add_event_case_or_lead').val('').trigger('change');
                $("#add_event_case_or_lead").attr("disabled", true);
                $('#add_event_case_or_lead').prop('selectedIndex',0);
                $("#loadAddEventPopup #HideShowNonlink").hide();
                
                changeCaseUser();
                $(".hideUser").hide();
                if($(".fieldGroupEventReminder").length > 1) {
                    $(".fieldGroupEventReminder").not(':first-child').remove();
                }
                $("#add_event_right_section").html('');
                firmStaff();
            } else {
                $(".hideUser").show();
                $('#add_event_case_or_lead').removeAttr("disabled");
            }
        });

        $("#loadAddEventPopup input:checkbox#no_end_date_checkbox").click(function () {
            if ($(this).is(":checked")) {
                $('#loadAddEventPopup #add_event_end_on').val('');
                $("#loadAddEventPopup #add_event_end_on").attr("disabled", true);
            } else {
                $('#loadAddEventPopup #add_event_end_on').removeAttr("disabled");
            }
        });

        $("#loadAddEventPopup #add_event_end_on").on('changeDate', function() {
            if($('#add_event_end_on').valid()){
            $('#datepicker').removeClass('invalid').addClass('success');   
            }
        });

        $("#loadAddEventPopup input:checkbox#no_end_date_checkbox").click(function(){
            if ($(this).is(":checked")) {
            $('#datepicker').removeClass('invalid').addClass('success');
            } 
        });


        $("input:checkbox.recuring_event").click(function () {
            if ($(this).is(":checked")) {
                $("#repeat_dropdown").show();
                $("#endondiv").show();
                $("#loadAddEventPopup #no_end_date_checkbox").prop("checked", true);
                if ($("#loadAddEventPopup input:checkbox#no_end_date_checkbox").is(":checked")) {
                    $("#add_event_end_on").attr("disabled", true);
                } else {
                    $('#add_event_end_on').removeAttr("disabled");
                }

            } else {
                $("#endondiv").hide();

                $('#repeat_dropdown').hide();
                $("#loadAddEventPopup .recurring-div").hide();
            }
        });
      
     

    });

    function selectColor(c, id) {
        $('#loadAddEventPopup #colorSet').css('background-color', c);
        $("#loadAddEventPopup .item_category div i").remove();
        $('#loadAddEventPopup #' + id).html('<i aria-hidden="true" class="fa fa-check icon-check icon text-white mt-2"></i>');
        $("#loadAddEventPopup #event_type").val(id);
    }

    function openAddLocation() {
        $("#loadAddEventPopup .pre-load-location").hide();
        $("#loadAddEventPopup .add-new").show();
        $("#loadAddEventPopup #cancel_new_label").show();
        $("#loadAddEventPopup #add_new_label").hide();

    }

    function closeAddLocation() {
        $("#loadAddEventPopup .pre-load-location").show();
        $("#loadAddEventPopup .add-new").hide();
        $("#loadAddEventPopup #cancel_new_label").hide();
        $("#loadAddEventPopup #add_new_label").show();
    }

    // Below all commented functions are not in use
    /* function removeUser(id) {
        $(".innerLoader").css('display', 'block');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/remomeSelectedUser",
            data: {
                "selectdValue": id
            },
            success: function (res) {
                $("#loadUserAjax").html(res);
                $(".innerLoader").css('display', 'none');
            }
        })
    } */

    /* function loadStep2(res) {

        console.log(res);
        $('#smartwizard').smartWizard("next");
        $(".innerLoader").css('display', 'none');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/loadStep2", // json datasource
            data: {
                "user_id": res.user_id
            },
            success: function (res) {
                $("#step-2").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    } */


    /* function loadCaseClient(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseClientAndLeads",
            data: {
                "case_id": case_id
            },
            success: function (res) {
                $("#CaseClientSection").html(res);
                //$(".innerLoaderCase").css('display', 'none');
            }
        })
    } */

    /* function loadCaseLinkedStaff(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseLinkedStaff",
            data: {
                "case_id": case_id
            },
            success: function (res) {
                $("#CaseLinkedStaffSection").html(res);
                //$(".innerLoaderCase").css('display', 'none');
            }
        })
    } */
    /* function loadCaseNoneLinkedStaff(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseNoneLinkedStaff",
            data: {
                "case_id": case_id
            },
            success: function (res) {
                $("#CaseNoneLinkedStaffSection").html(res);
                //$(".innerLoaderCase").css('display', 'none');
            }
        })
    } */
    /* function loadAllFirmStaff() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadFirmAllStaff",
            data: {"case_id": ''},
            success: function (res) {
                $("#add_event_right_section").html(res);
            }
        })
    } */
    function loadLeadUsers(lead_id) {
        $(".right-section-loader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadLeadRightSection",
            data: {"lead_id": lead_id},
            success: function (res) {
                $(".right-section-loader").hide();
                $("#add_event_right_section").html(res);
              
            }
        })
    }
    
    function loadRightSection(case_id) {
        $(".right-section-loader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadEventRightSection",
            data: {"case_id": case_id},
            success: function (res) {
                $(".right-section-loader").hide();
                $("#add_event_right_section").html(res);
            }
        })
    }
    function changeCaseUser() {
        $("#add_event_text_lead_id").val('');
        $("#add_event_text_case_id").val('');
        var uType=$("#loadAddEventPopup #add_event_case_or_lead option:selected").attr('uType');
        var selectdValue = $("#loadAddEventPopup #add_event_case_or_lead option:selected").val();
        if(selectdValue!=''){
            if(uType=="case"){
                $("#add_event_text_case_id").val(selectdValue);
                $("#loadAddEventPopup #HideShowNonlink").show();
                loadRightSection(selectdValue);
            }else{
                $("#time_tracking_enabled").prop('checked',false)
                $("#add_event_text_lead_id").val(selectdValue);
                loadLeadUsers(selectdValue);
            }
            $(".hideUser").hide();
        }else{
           $(".hideUser").show();
            $("#add_event_right_section").html('');
            $("#loadAddEventPopup #HideShowNonlink").hide();
           
        }
    }

    $("#loadAddEventPopup").on("click", ".add_event_guide", function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/hideAddEventGuide",
            data: {"type":"1"},
            success: function (res) {
                $("#loadAddEventPopup #add_event_guide").html('');
            }
        })
    });

    $("#loadAddEventPopup").on("click", ".add_event_guide2", function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/hideAddEventGuide",
            data:  {"type":"2"},
            success: function (res) {
                $("#loadAddEventPopup #add_event_guide2").html('');
            }
        })
    });
   

    function firmStaff() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadAllCaseStaffMember",
            data: "",
            success: function (res) {
                $("#add_event_right_section").html(res);
            }
        })
    }
    
    /* $("input:checkbox#add_event_no_case_link").click(function () {
        if ($(this).is(":checked")) {
            
            $('#add_event_case_or_lead').val('').trigger('change');
            $('#add_event_case_or_lead').prop('selectedIndex',0);
            $("#loadAddEventPopup #HideShowNonlink").hide();
            $("#add_event_right_section").html('');
            firmStaff();
        } else {
           
            $("#add_event_right_section").html('');
        }
    }); */

    // Get weekdays name
    function getWeekdays(date) {
        var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
        return weekday[date.getDay()];
    }

    // Get nth day of month 
    function getNthDayOfMonth(date, weekday) {
        var nth= ['First', 'Second', 'Third', 'Fourth', 'Fifth'];
        return "On the "+nth[Math.floor(date.getDate()/7)]+' '+getWeekdays(date);
    }

    // Get updated option of weekly/monthly/yearly recurring
    function updateMonthlyWeeklyOptions() {
        var date = new Date($("#add_event_start_date").val());
        // for month
        $("#loadAddEventPopup #monthly-frequency").find('option').remove();
        $("#loadAddEventPopup #monthly-frequency").append(
            '<option value="MONTHLY_ON_DAY">On day '+date.getDate()+'</option><option value="MONTHLY_ON_THE">'+getNthDayOfMonth(date)+'</option>'
        );
        // Commented. As per client's requirement
        // for year
        /* $("#yearly-frequency").find('option').remove();
        var monthName = date.toLocaleString('default', { month: 'long' });
        $("#yearly-frequency").append(
            '<option value="YEARLY_ON_DAY">On day '+date.getDate()+' of '+monthName+'</option><option value="YEARLY_ON_THE">'+getNthDayOfMonth(date)+' of '+monthName+'</option>'
        ); */
        // for week
        $("#loadAddEventPopup #event-frequency option[value='WEEKLY']").text("Weekly on "+getWeekdays(date));
    }
</script>
