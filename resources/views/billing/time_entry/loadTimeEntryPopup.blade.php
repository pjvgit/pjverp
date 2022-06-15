<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item"><a class="nav-link active" id="timeEntry-home-basic-tab" data-toggle="tab" href="#timeEntry-homeBasic" role="tab"
            aria-controls="timeEntry-homeBasic" aria-selected="true">Single</a></li>
    <li class="nav-item"><a class="nav-link" id="timeEntry-profile-basic-tab" data-toggle="tab" href="#timeEntry-profileBasic" role="tab"
            aria-controls="timeEntry-profileBasic" aria-selected="false">Bulk</a></li>
</ul>
<div class="tab-content" id="myTabContent" bladeFile="resources/views/billing/time_entry/loadTimeEntryPopup.blade.php">
    <span id="showError" class="showError" style="display: none;"></span>
    <div class="tab-pane fade show active" id="timeEntry-homeBasic" role="tabpanel" aria-labelledby="timeEntry-home-basic-tab">
        <form class="savenewTimeEntry" id="savenewTimeEntry" name="savenewTimeEntry" method="POST">
            <input class="form-control" id="id" value="{{$task_id}}" name="task_id" type="hidden">
            <input class="form-control" id="id" value="{{$from_view}}" name="from_view" type="hidden">
            @csrf
            <?php 
            if(isset($from)){?>
                <input type="hidden" name="from" value="{{$from}}">
                <input type="hidden" name="curDate" value="{{$curDate}}">
            <?php } ?>
            <input type="hidden" name="smart_timer_id" value="{{$request->smart_timer_id ?? ''}}">
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Case</label>
                <div class="col-10 form-group mb-3">
                    <select class="form-control case_or_lead dropdownSelect2" id="case_or_lead" name="case_or_lead"
                        data-placeholder="Select case">
                        <option value="">Select case</option>
                        <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                        <option <?php if($case_id==$Caseval->id){ echo "selected=selected"; } ?> value="{{$Caseval->id}}">{{$Caseval->case_title}}<?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?></option>
                        <?php } ?>
                    </select>
                    <span id="cnl"></span>
                    @can('case_add_edit')
                    <a data-toggle="modal"  data-target="#AddCaseModelUpdate" data-placement="bottom" href="javascript:;" onclick="loadAllStep('timeEntry');">Add Case</a>
                    @endcan
                    <!-- <a data-toggle="modal" data-target="#AddCaseModelUpdate" data-placement="bottom" href="javascript:;" onclick="loadAllStep();"> 
                    Add Case</a> -->
                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">User</label>
                <div class="col-10 form-group mb-3">
                    <select class="form-control staff_user dropdownSelect2" id="staff_user" name="staff_user">
                        <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                        <option <?php if($CasevloadFirmStaffvalal->id==Auth::User()->id){ echo "selected=selected"; } ?> value="{{$CasevloadFirmStaffvalal->id}}" data-flatfees="{{$caseStaffRates[$CasevloadFirmStaffvalal->id] ?? 0}}" >{{$CasevloadFirmStaffvalal->first_name}}
                            {{$CasevloadFirmStaffvalal->last_name}}  <?php if($CasevloadFirmStaffvalal->user_title){ echo "(".$CasevloadFirmStaffvalal->user_title.")"; } ?></option>
                        <?php } ?>
                    </select>

                </div>
            </div>
 
            <div class="form-group row area_dropdown" id="area_dropdown">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Activity</label>
                <div class="col-md-6 form-group mb-3">
                    <select onchange="getRate(this.value)" id="activity" name="activity" class="form-control custom-select col dropdown_activity">
                        <option value="">Search activity</option>
                        <?php foreach($TaskActivity as $k=>$v){ ?>
                        <option data-flatfees="{{$v->flat_fees}}" value="{{$v->id}}">{{$v->title}}</option>
                        <?php } ?>
                    </select>
                    <span id="act"></span>

                </div>
                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();"
                        href="javascript:;">Add
                        new activity</a></label>
            </div>
            <div class="form-group row area_text" id="area_text">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Activity</label>
                <div class="col-md-6 form-group mb-3">
                    <input class="form-control" id="activity_text" value="" maxlength="255" name="activity_text"
                        type="text" placeholder="Enter new activity">
                </div>
                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showDropdown();"
                        href="javascript:;">Cancel</a></label>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-10 form-group mb-3">
                    <label class="switch pr-5 switch-success mr-3 mt-2"><span></span>
                        <input type="checkbox" name="time_tracking_enabled" checked="checked"
                            id="time_tracking_enabled"><span class="slider"></span>
                        This time entry is billable.</label>

                </div>

            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                <div class="col-md-10 form-group mb-3">
                    <textarea name="case_description" class="form-control"
                        rows="5">{{ $request->description ?? '' }}</textarea>
                    <small>This description will appear on invoices.</small>
                </div>
            </div>
            <div class="">
                <a class="collapsed" id="collapsed" data-toggle="collapse" href="javascript:void(0);"
                    data-target="#accordion-item-icons-4" aria-expanded="false">
                    Custom Fields <i class="fas fa-sort-down align-text-top"></i>
                </a>
            </div>
            <div class="collapse" id="accordion-item-icons-4" style="">
                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-md-9 form-group mb-3">
                        <div>
                            Have more information you want to add? You can create custom fields for time entry by going
                            to "Settings" and clicking "Custom Fields".
                            <a class="ml-2" href="#" target="_blank" rel="noopener noreferrer">
                                Learn More
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <div class="my-2 px-3 py-4 date-rate-duration-block container">
                <div class="mb-2 row ">
                    <div class="col-4"><label class="mb-0" for="date-field-id">Date</label></div>
                    <div class="col-3"><label class="mb-0" for="rate-field-id">Rate</label></div>
                    <div class="col-2"></div>
                    <div class="pl-4 col-3"><label class="mb-0" for="duration-field-id">Duration</label></div>
                </div>
                <div class="row ">
                    <div class="col-4">
                        <?php  if(isset($curDate) && $curDate!=""){?>
                            <input class="form-control datepicker" id="datepicker" value="{{date('m/d/Y',strtotime($curDate))}}" name="start_date" type="text" placeholder="mm/dd/yyyy">
                        <?php  }else{  ?>
                            <input class="form-control datepicker" id="datepicker" value="{{ convertUTCToUserTimeZone('dateOnly') }}" name="start_date" type="text" placeholder="mm/dd/yyyy"><?php
                        }?>
                    </div>
                    <div class="col-3">
                        <div class="">
                            <div class="px-0 undefined">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div><input
                                        id="rate-field-id" name="rate_field_id" maxlength="15"
                                        class="form-control" min="0" value="{{$default_rate ?? ''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-0 col-2">
                        <div>
                            <div class=""><select id="rate_type_field_id" name="rate_type_field_id"
                                    class="form-control custom-select  ">
                                    <option value="hr">/hr</option>
                                    <option value="flat">flat</option>
                                </select></div>
                        </div>
                    </div>
                    <div class="pl-4 col-3">
                        <div class=""><input id="duration-field" maxlength="15" min="0" name="duration_field"
                                class="form-control text-right" value="{{ $request->duration ?? '' }}"></div>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-4"></div>
                    <div class="col-3"></div>
                    <div class="col-2"></div>
                    <div class="pl-4 col-3">0.1 = 6 minutes </div>
                </div>
            </div>


            <div class="modal-footer  pb-0">
                <div class="col-md-2 form-group mb-3">
                    <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
                </div>
                <button type="submit" id="submit" name="savenew" value="sn" class="btn btn-outline-primary submitbutton">Save and
                    New</button>
                <button type="submit" id="submit1" name="save" value="s"
                    class="btn btn-primary submitbutton">Save</button>
            </div>

        </form>
    </div>
    <div class="tab-pane fade" id="timeEntry-profileBasic" role="tabpanel" aria-labelledby="timeEntry-profile-basic-tab">
        <form class="savebulkTimeEntry" id="savebulkTimeEntry" name="savebulkTimeEntry" method="POST">
            @csrf
            <?php 
            if(isset($from)){?>
                <input type="hidden" name="from" value="{{$from}}">
                <input type="hidden" name="curDate" value="{{$curDate}}">
            <?php } ?>
            <div class="row pb-3 mb-3 " style="border-bottom: 1px solid #e1e1e1 !important;">

                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Date</label>
                    <?php  if(isset($curDate) && $curDate!=""){?>
                        <input class="form-control datepicker" id="datepicker" value="{{date('m/d/Y',strtotime($curDate))}}" name="start_date" type="text" placeholder="mm/dd/yyyy">
                    <?php  }else{  ?>
                        <input class="form-control datepicker" id="datepicker" value="{{ convertUTCToUserTimeZone('dateOnly') }}" name="start_date" type="text" placeholder="mm/dd/yyyy"><?php
                    }?>
                    

                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">User</label>
                    <select class="form-control staff_user dropdownSelect2" id="bulk_staff_user" name="bulk_staff_user">

                        <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                        <option <?php if($CasevloadFirmStaffvalal->id==Auth::User()->id){ echo "selected=selected"; } ?> value="{{$CasevloadFirmStaffvalal->id}}">{{$CasevloadFirmStaffvalal->first_name}}
                            {{$CasevloadFirmStaffvalal->last_name}}  <?php if($CasevloadFirmStaffvalal->user_title){ echo "(".$CasevloadFirmStaffvalal->user_title.")"; } ?></option>
                        <?php } ?>
                    </select>

                </div>
            </div>
            <h5 class="mb-3 bold">Time Entries</h5>
            <div class="no-gutters row ">
                <div class="m-0 pr-3 col-4"><label>Case</label></div>
                <div class="m-0 pr-3 col-3"><label>Activity</label></div>
                <div class="m-0 pr-3 col-3"><label>Description</label></div>
                <div class="m-0 pr-3 col-1"><label>Duration</label></div>
                <div class="col-1"></div>
            </div>

            <div class="bulk-time-entries-row-0 fieldGroup">
                <div class="no-gutters row ">
                    <div class="pr-4 col-4">
                        <div class="">
                            <div class="row no-gutters">
                                <select class="form-control case_or_lead" dvid="1" id="hideoptioninput21"
                                    name="case_or_lead[1]" data-placeholder="Search for an existing contact or company">
                                    <option value="">Select case</option>
                                    <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                    <option <?php if($case_id==$Caseval->id){ echo "selected=selected"; } ?> 
                                        value="{{$Caseval->id}}">{{$Caseval->case_title}}
                                        <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pr-4 col-3">
                        <div class="">
                            <div class="row ">
                                <select id="hideoptioninput2activity1" name="activity[1]"
                                    class="form-control custom-select col">
                                    <option value="">Search activity</option>
                                    <?php foreach($TaskActivity as $k=>$v){ ?>
                                    <option value="{{$v->id}}">{{$v->title}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pr-2 col-3">
                        <input id="bulk-time-entry-row-description-0" name="description[1]"
                            class="form-control description-field"
                            value="">
                    </div>
                    <div class="col-1">
                        <div class="">
                            <input id="hideoptioninput2duration1" name="duration[1]" class="form-control duration-field"
                                value="">
                        </div>
                    </div>
                    <div class="pl-3 col-1">
                        {{-- <button class="btn remove" type="button">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="mb-2 row ">
                    <div class="col-7">
                        <div class="">
                            <div class="form-check">
                                <label class="form-check-label ">
                                    <input type="checkbox"  name="billable[1]" dvid="1" class="billable-field form-check-input" checked="checked">

                                    <?php if($default_rate > 0){?>
                                            <div class="billtext" id="replaceAmt1"> Billable - Rate :{{number_format($default_rate,2)}}</div>

                                        <?php }else{ ?>
                                            <div class="billtext" id="replaceAmt1"> Billable - Billing rate is not specified, the system will use rate: 0</div>
                                        <?php }?>
                                    <div>
                                        <input type="text" name="defaultrate[1]" id="hideoptioninput2defaultrate1" value="{{$default_rate}}" style="display: none;"/>
                                    </div>

                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="text-right col-4 showtaax" id="showtaax">0.1 = 6 minutes</div>
                    <div class="col-1"></div>
                </div>
            </div>
            <div class="maturity_div copy-new hide maturity_div" id="optionTemplate2">
                <div class="no-gutters row ">
                    <div class="pr-4 col-4">
                        <div class="">
                            <div class="row no-gutters">
                                <select class="form-control case_or_lead" id="case_or_lead" name="case_or_lead[]"
                                    data-placeholder="Search for an existing contact or company">
                                    <option value="">Select case</option>
                                    <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                    <option  <?php if($case_id==$Caseval->id){ echo "selected=selected"; } ?> value="{{$Caseval->id}}">{{$Caseval->case_title}}
                                        <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pr-4 col-3">
                        <div class="">
                            <div class="row ">
                                <select id="activity" name="activity[]" class="form-control custom-select col">
                                    <option value="">Search activity</option>
                                    <?php foreach($TaskActivity as $k=>$v){ ?>
                                    <option value="{{$v->id}}">{{$v->title}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pr-2 col-3">
                        <input id="bulk-time-entry-row-description-0" name="description[]"
                            class="form-control description-field"
                            value="">
                    </div>
                    <div class="col-1">
                        <div class="">
                            <input id="bulk-time-entry-row-duration-0" name="duration[]"
                                class="form-control duration-field" value="">
                        </div>
                    </div>
                    <div class="pl-3 col-1">
                        <button class="btn remove" type="button">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-2 row ">
                    <div class="col-7">
                        <div class="">
                            <div class="form-check">
                                <label class="form-check-label ">
                                    <input type="checkbox" name="billable[]" class="billable-field form-check-input">
                                        <?php if($default_rate > 0){?>
                                            <div class="billtext"> Billable - Rate :{{number_format($default_rate,2)}}</div>

                                        <?php }else{ ?>
                                            <div class="billtext"> Billable - Billing rate is not specified, the system will use rate: 0</div>
                                        <?php }?>
                                    <div>
                                        <input type="text" name="defaultrate[]" value="{{$default_rate}}" style="display: none;"/>
                                    </div>


                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-1"></div>
                </div>
            </div>
            <div class="after-add-more-new"></div>
            <div class="row ">
                <button type="button" id="add-one-row-id" class="btn btn-link add-one-more">Add another</button>
                <button type="button" id="add-five-rows-id" class="btn btn-link add-more-five">Add 5 more rows</button>
            </div>
            <div class="modal-footer pb-0">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button type="submit" name="save" value="s" class="btn btn-primary submitbutton">Save Entries</button>
            </div>
            <input type="hidden" name="hideinputcount2" id="hideinputcount2" value="1" />

        </form>
    </div>
</div>
@include('commonPopup.add_case')
<style>
    .hide {
        display: none;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        // $(".bulk_billable_field").prop('checked',true);
        $('#savenewTimeEntry')[0].reset();
        $("#case_or_lead").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadTimeEntryPopup"),
        });
        $("#staff_user").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadTimeEntryPopup"),
        });
        $("#activity").select2({
            placeholder: "Select activity",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadTimeEntryPopup"),
        });
        $('#collapsed').click(function () {
            $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
                'fa-sort-down align-text-top');

        });
        loadDefault();
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $(".add-one-more").click(function () {
            var hideinputcount2 = $('#hideinputcount2').val();


            var $template = $('#optionTemplate2'),
                $clone = $template
                .clone()
                .removeClass('hide')
                .removeAttr('id')
                .attr('id', 'div' + (parseInt(hideinputcount2) + parseInt(1)) + '')
                .insertBefore($template),

                $option = $clone.find('[name="case_or_lead[]"]');
            $option.attr('id', 'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            $option.attr('dvid', +(parseInt(hideinputcount2) + parseInt(1)) + '');
            $option.attr('required', 'required');

            $option12 = $clone.find('[name="defaultrate[]"]');
            $option12.attr('id', 'hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            $option12.attr('required', 'required');

            $option2 = $clone.find('[name="activity[]"]');
            $option2.attr('id', 'hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            $option2.attr('required', 'required');

            $option3 = $clone.find('[name="duration[]"]');
            $option3.attr('id', 'hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            $option3.attr('required', 'required');

            $option33 = $clone.find('[name="billable[]"]');
            $option33.attr('dvid', +(parseInt(hideinputcount2) + parseInt(1)) + '');
            $option33.attr('id', 'billableid' + (parseInt(hideinputcount2) + parseInt(1)) +'');
            $option33.attr('checked', true);

            $option22 = $clone.find('[class="billtext"]');
            $option22.attr('id', 'replaceAmt' + (parseInt(hideinputcount2) + parseInt(1)) + '');

            $optionDescripton = $clone.find('[name="description[]"]');
            $optionDescripton.attr('id', 'descriptionid' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            
            // Add new field
            $('#savebulkTimeEntry').validate('add-one-more', $option);
            $("#div" + (parseInt(hideinputcount2) + parseInt(1)) + "").find("label").attr("for",
                'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');

            //For option 1
            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'case_or_lead[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 12
            $('#hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'defaultrate[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 2
            $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'activity[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'duration[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#billableid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'billable[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For description
            $('#descriptionid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'description[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));

            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " Please select a case"
                }
            });
            $('#hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                number: true,
                min: 1,
                messages: {
                    required: " This court case has no default rate",
                    number: " This court case has no default rate",
                    min: " This court case has no default rate"
                }
            });
            $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " Please specify an activity"
                }
            });

            $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                number: true,
                min: 0.1,
                messages: {
                    required: " Invalid",
                    number: " Invalid",
                    min: " Duration must be greater than 0"
                }
            });
            // $(".bulk_billable_field").prop('checked',true);
            $("#savebulkTimeEntry").validate();
        });

        $('#savebulkTimeEntry').on('click', '.remove', function () {
            defaultValidation();
            var $row = $(this).parents('.maturity_div'),
                $option = $row.find('[name="case_or_lead[]"]');

            $row.remove();
            $('#savebulkTimeEntry').validate('removeField', $option);
            // var count = $('#hideinputcount2').val();

            // count--;
            // // $('#hideinputcount2').val(count);

        });


        $('#savebulkTimeEntry').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });
        $('#savebulkTimeEntry').submit(function (e) {
            
            e.preventDefault();
            $("#innerLoader").css('display', 'block');
            if (!$('#savebulkTimeEntry').valid()) {
                $("#innerLoader").css('display', 'none');
                $('.submitbutton').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#savebulkTimeEntry").serialize();
            $("#preloader").show();
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/savebulkTimeEntry", // json datasource
                data: dataString,
                success: function (res) {
                    $("#preloader").hide();
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
                        $("#innerLoader").css('display', 'none');
                        $('.submitbutton').removeAttr("disabled");
                        $('#loadTimeEntryPopup').animate({ scrollTop: 0 }, 'slow');

                        return false;
                    } else {
                        toastr.success('Your time entry has been created', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        if (buttonpressed == 'savenew') {
                            $('#savenewTimeEntry')[0].reset();
                            $('.submitbutton').removeAttr("disabled");
                            showDropdown();
                        } else {
                            if(res.from=="timesheet"){
                                $("#loadTimeEntryPopup").modal("hide");
                            }else{
                                window.location.reload();
                            }
                        }
                        $("#innerLoader").css('display', 'none');

                    }
                },error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $("#preloader").hide();
                }
            });
        });

        $(".add-more-five").click(function () {
            defaultValidation();
            for (var i = 1; i <= 5; i++) {
                var hideinputcount2 = $('#hideinputcount2').val();
                var $template = $('#optionTemplate2'),
                
                $clone = $template
                    .clone()
                    .removeClass('hide')
                    .removeAttr('id')
                    .attr('id', 'div' + (parseInt(hideinputcount2) + parseInt(1)) + '')
                    .insertBefore($template),
                    $option = $clone.find('[name="case_or_lead[]"]');
                $option.attr('id', 'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option.attr('dvid', +(parseInt(hideinputcount2) + parseInt(1)) + '');
                $option.attr('required', 'required');

                $option12 = $clone.find('[name="defaultrate[]"]');
                $option12.attr('id', 'hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1)) +'');
                $option12.attr('required', 'required');

                $option2 = $clone.find('[name="activity[]"]');
                $option2.attr('id', 'hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('required', 'required');

                $option3 = $clone.find('[name="duration[]"]');
                $option3.attr('id', 'hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option3.attr('required', 'required');

                $option22 = $clone.find('[class="billtext"]');
                $option22.attr('id', 'replaceAmt' + (parseInt(hideinputcount2) + parseInt(1)) + '');

                $option33 = $clone.find('[name="billable[]"]');
                $option33.attr('dvid', +(parseInt(hideinputcount2) + parseInt(1)) + '');
                $option33.attr('id', 'billableid' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option33.attr('checked', true);

                $optionDescripton = $clone.find('[name="description[]"]');
                $optionDescripton.attr('id', 'descriptionid' + (parseInt(hideinputcount2) + parseInt(1)) + '');

                // Add new field
                $('#savebulkTimeEntry').validate('add-one-more', $option);
                $("#div" + (parseInt(hideinputcount2) + parseInt(1)) + "").find("label").attr("for",
                    'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');

                //For option 1
                $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'case_or_lead[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');
                
                //For option 12
                $('#hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'defaultrate[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For option 2
                $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'activity[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For option 3
                $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'duration[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For option 3
                $('#billableid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name', 
                    'billable[' +(parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For description
                $('#descriptionid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'description[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));

                $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                    required: true,
                    messages: {
                        required: " Please select a case"
                    }
                });
                $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).rules(
                    "add", {
                        required: true,
                        messages: {
                            required: " Please specify an activity"
                        }
                    });

                $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).rules(
                    "add", {
                        required: true,
                        number: true,
                        min: 0.1,
                        messages: {
                            required: " Invalid",
                            number: " Invalid",
                            min: " Duration must be greater than 0"
                        }
                    });
                // $(".bulk_billable_field").prop('checked',true);
                $("#savebulkTimeEntry").validate();
            }

        });

        // $('.number').mask("#,##0.00", {
        //     reverse: true
        // });

        $('#hideoptioninput21').rules("add", {
            required: true,
            messages: {
                required: "Please select a case"
            }
        });
        $('#hideoptioninput2defaultrate1').rules("add", {
                required: true,
                number: true,
                min: 1,
                messages: {
                    required: " This court case has no default rate",
                    number: " This court case has no default rate",
                    min: " This court case has no default rate"
                }
            });
        $('#hideoptioninput2activity1').rules("add", {
            required: true,
            messages: {
                required: " Please specify an activity"
            }
        });

        $('#hideoptioninput2duration1').rules("add", {
            required: true,
            number: true,
            min: 0.1,
            messages: {
                required: " Invalid",
                number: " Invalid",
                min: " Duration must be greater than 0"
            }
        });


        $("#savenewTimeEntry").validate({
            rules: {
                case_or_lead: {
                    required: true
                },
                staff_user: {
                    required: true
                },
                activity: {
                    required: true
                },
                rate_field_id: {
                    required: true,
                    number:true
                },
                duration_field: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#rate_type_field_id option:selected").val() == "flat") {
                                var status = false;
                            }
                            return status;
                        }
                    },
                    min : 0.1,
                    number: true,                   
                }
            },
            messages: {
                case_or_lead: {
                    required: "Case can't be blank"
                },
                staff_user: {
                    required: "User can't be blank"
                },
                activity: {
                    required: "Activity can't be blank"
                },
                rate_field_id: {
                    required: "Rate can't be blank"
                },
                duration_field: {
                    required: "Duration can't be blank",
                    min:"Duration must be greater than 0",
                    number: "Allows number only."
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_or_lead')) {
                    $($('.select2-container--classic .select2-selection--single')[1]).addClass("input-border-error");
                    error.appendTo('#cnl');
                } else if (element.is('#activity')) {
                    $($('.select2-container--classic .select2-selection--single')[3]).addClass("input-border-error");
                    error.appendTo('#act');
                } else if (element.is('#staff_user')) {
                    error.appendTo('#usr');
                } else {
                    element.after(error);
                }
            }

            
        });

        $('#case_or_lead').on('select2:select', function (e) { 
            $($('.select2-container--classic .select2-selection--single')[1]).removeClass("input-border-error");
            $('#cnl').text('');
        });

        $('#activity').on('select2:select', function (e) { 
            $($('.select2-container--classic .select2-selection--single')[3]).removeClass("input-border-error");
            $('#act').text('');
        });

        var buttonpressed;
        $('.submitbutton').click(function () {
            buttonpressed = $(this).attr('name')
        })
        $('#savenewTimeEntry').submit(function (e) {

            $(".submitbutton").attr("disabled", true);

            e.preventDefault();
            $("#innerLoader").css('display', 'block');
            if (!$('#savenewTimeEntry').valid()) {
                $("#innerLoader").css('display', 'none');
                $('.submitbutton').removeAttr("disabled");                
                return false;
            }
            var dataString = '';
            dataString = $("#savenewTimeEntry").serialize();

            $("#preloader").show();
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/saveTimeEntryPopup", // json datasource
                data: dataString,
                success: function (res) {

                    $("#preloader").hide();
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
                        $("#innerLoader").css('display', 'none');
                        $('.submitbutton').removeAttr("disabled");
                        $('#loadTimeEntryPopup').animate({ scrollTop: 0 }, 'slow');

                        return false;
                    } else {
                        // localStorage.removeItem("counter");
                        // localStorage.removeItem("pauseCounter");
                        // localStorage.removeItem("smart_timer_id");
                        deleteTimerInStorage();
                        toastr.success('Your time entry has been created', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        if (buttonpressed == 'savenew') {
                            $('#case_or_lead').val(null).trigger('change');
                            $('#activity').val(null).trigger('change');
                            $('#savenewTimeEntry')[0].reset();
                            $('#loadTimeEntryPopup').animate({ scrollTop: 0 }, 'slow');
                            $('.submitbutton').removeAttr("disabled");
                            showDropdown();
                        } else {
                            if(res.from=="timesheet"){
                                $("#loadTimeEntryPopup").modal("hide");
                            }else{
                                window.location.reload();
                            }
                        }
                        $("#innerLoader").css('display', 'none');

                    }
                }
            });
        });

        $(document).on('change', ".case_or_lead", function () {
            $("#preloader").show();
            var f = $(this).attr("dvid");
            var case_id = $(this).val();
            getAndCheckDefaultCaseRate(f, case_id)
        });

        // $(document).on('change', ".bulk_billable_field", function () {
        //     var f = $(this).attr("dvid");
        //     if (!$(this).is(":checked")) {
        //         $("input[name='billable["+f+"]']").prop('checked',false);
        //         $("input[name='billable["+f+"]']").val('off');
        //     }
        // });

        $(document).on('change', "#bulk_staff_user", function () {
            $("#preloader").show();
            $(".case_or_lead").each(function(ind, item) {
                var f = $(this).attr("dvid");
                var case_id = $(this).val();
                if(case_id != '' && case_id > 0){
                    getAndCheckDefaultCaseRate(f, case_id)
                }
            });    
            $("#preloader").hide();        
        });
    });
    showDropdown();

    function getAndCheckDefaultCaseRate(f, case_id = null){
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/getAndCheckDefaultCaseRate",
            data: {
                'case_id': case_id,
                'staff_id' : $("#bulk_staff_user").val(),
            },
            success: function (res) {
                $("#preloader").hide();
                console.log(f);
                $("#hideoptioninput2defaultrate"+f).val("");
                // $(".staff_user").val(res.staff_id).trigger('change');
                $("#rate-field-id").val(res.data);   
                if(res.data > 0){                    
                    $("#replaceAmt" + f).text("Billable - Rate :"+ res.data);
                    $("#hideoptioninput2defaultrate"+f).val(res.data);
                }else{
                    $("#hideoptioninput2defaultrate"+f).val(0);
                    $("#replaceAmt" + f).html("Billable - Billing rate is not specified, the system will use rate: 0");
                }                    
                console.log("#replaceAmt" + f);
            }
        });
    }

    function showText() {
        $(".area_text").show();
        $(".area_dropdown").hide();
        return false;
    }

    function showDropdown() {
        $(".area_text").hide();
        $(".area_dropdown").show()
        return false;
    }

    function loadTaskActivity(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/task/loadTaskActivity", // json datasource
            data: '',
            success: function (res) {
                $("#TaskActivityDown").html(res);
                $("#preloader").hide();
            }
        })
    }

    function getRate(id)
    {
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/getrate", // json datasource
            data: {"id": id},
            success: function (res) {
                $("#rate-field-id").html(res);
            }
        })
    }


    function loadDefault() {
        var hideinputcount2 = $('#hideinputcount2').val();

        for (var i = hideinputcount2; i <= 4; i++) {
            var hideinputcount2 = $('#hideinputcount2').val();
            var $template = $('#optionTemplate2'),
                $clone = $template
                .clone()
                .removeClass('hide')
                .removeAttr('id')
                .attr('id', 'div' + (parseInt(hideinputcount2) + parseInt(1)) + '')
                .insertBefore($template),
                $option = $clone.find('[name="case_or_lead[]"]');
            $option.attr('id', 'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            $option.attr('dvid', +(parseInt(hideinputcount2) + parseInt(1)) + '');
            $option.attr('required', 'required');

            $option12 = $clone.find('[name="defaultrate[]"]');
            $option12.attr('id', 'hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1)) +'');
            $option12.attr('required', 'required');

            $option2 = $clone.find('[name="activity[]"]');
            $option2.attr('id', 'hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            $option2.attr('required', 'required');

            $option3 = $clone.find('[name="duration[]"]');
            $option3.attr('id', 'hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            $option3.attr('required', 'required');

            $option22 = $clone.find('[class="billtext"]');
            $option22.attr('id', 'replaceAmt' + (parseInt(hideinputcount2) + parseInt(1)) +'');

            $option33 = $clone.find('[name="billable[]"]');
            $option33.attr('dvid', +(parseInt(hideinputcount2) + parseInt(1)) + '');
            $option33.attr('id', 'billableid' + (parseInt(hideinputcount2) + parseInt(1)) +'');
            $option33.attr('checked', true);


            $optionDescripton = $clone.find('[name="description[]"]');
            $optionDescripton.attr('id', 'descriptionid' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            // Add new field
            $('#savebulkTimeEntry').validate('add-one-more', $option);
            $("#div" + (parseInt(hideinputcount2) + parseInt(1)) + "").find("label").attr("for",
                'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');

            //For option 1
            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'case_or_lead[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');
            
            //For option 12
            $('#hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'defaultrate[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');
                
            //For option 2
            $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'activity[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'duration[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#billableid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name', 
                'billable[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For description
            $('#descriptionid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'description[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));

            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " Please select a case"
                }
            });
            $('#hideoptioninput2defaultrate' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " This court case has no default rate"
                }
            });
            $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " Please specify an activity"
                }
            });

            $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                number: true,
                min: 0.1,
                messages: {
                    required: " Invalid",
                    number: " Invalid",
                    min: " Duration must be greater than 0"
                }
            });
            $("#savebulkTimeEntry").validate();
        }
    }

    function defaultValidation() {

        $('#hideoptioninput21').rules("add", {
            required: true,
            messages: {
                required: "Please select a case"
            }
        });
        $('#hideoptioninput2defaultrate1').rules("add", {
            required: true,
            number: true,
            min: 0.1,
            messages: {
                required: " This court case has no default rate",
                number: " This court case has no default rate",
                min: " This court case has no default rate"
            }
        });
        $('#hideoptioninput2activity1').rules("add", {
            required: true,
            messages: {
                required: " Please specify an activity"
            }
        });

        $('#hideoptioninput2duration1').rules("add", {
            required: true,
            number: true,
            min: 0.1,
            messages: {
                required: " Invalid",
                number: " Invalid",
                min: " Duration must be greater than 0"
            }
        });
    }

    //Amount validation
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

    $("#activity").on("select2:select", function(e) {
        if($(this).select2().find(":selected").data("flatfees") > 0) {
            $("#rate-field-id").val($(this).select2().find(":selected").data("flatfees"));
            $("#rate_type_field_id").val('flat');
        }
        $("#activity").select2({
            placeholder: "Select activity",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadTimeEntryPopup"),
        });
    });

    $("#staff_user").on("select2:select", function(e) {
        if($(this).select2().find(":selected").data("flatfees") > 0) {
            $("#rate-field-id").val($(this).select2().find(":selected").data("flatfees"));
            // $("#rate_type_field_id").val('hr');
        }
        
        $("#staff_user").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadTimeEntryPopup"),
        });
    });
    
</script>
