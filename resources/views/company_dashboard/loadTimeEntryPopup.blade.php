<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="home-basic-tab" data-toggle="tab" href="#singletimeentry" role="tab"
            aria-controls="singletimeentry" aria-selected="true">Single
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="profile-basic-tab" data-toggle="tab" href="#bulkTimeEntry" role="tab"
            aria-controls="bulkTimeEntry" aria-selected="false">Bulk
        </a>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="singletimeentry" role="tabpanel" aria-labelledby="home-basic-tab">
        <form class="savenewTimeEntry" id="savenewTimeEntry" name="savenewTimeEntry" method="POST">
            @csrf
            <div id="showError" class="showError" style="display:none"></div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Case</label>
                <div class="col-10 form-group mb-3">
                    <select class="form-control case_or_lead" id="case" name="case_or_lead">
                        <option></option>
                        <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                        <option value="{{$Caseval->id}}">{{$Caseval->case_title}}
                            <?php 
                        if($Caseval->case_number!=""){
                            echo "(".$Caseval->case_number.")"; 
                        }
                            ?></option>
                        <?php } ?>

                    </select>

                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">User</label>
                <div class="col-10 form-group mb-3">
                    <select class="form-control staff_user select2" id="staff_user" name="staff_user">
                        <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                        <option value="{{$CasevloadFirmStaffvalal->id}}" <?php echo ($CasevloadFirmStaffvalal->id == Auth::user()->id) ? 'selected' : ''; ?>>{{$CasevloadFirmStaffvalal->first_name}}
                            {{$CasevloadFirmStaffvalal->last_name}}</option>
                        <?php } ?>
                    </select>

                </div>
            </div>

            <div class="form-group row" id="area_dropdown">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Activity</label>
                <div class="col-md-6 form-group mb-3">
                    <select id="activity" name="activity" class="form-control custom-select col select2">
                        <option value="">Search activity</option>
                        <?php foreach($TaskActivity as $k=>$v){ ?>
                        <option value="{{$v->id}}">{{$v->title}}</option>
                        <?php } ?>
                    </select>
                </div>
                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();"
                        href="javascript:;">Add
                        new activity</a></label>
            </div>
            <div class="form-group row" id="area_text">
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
                        rows="5">John called about the upcoming meeting, please call him back.</textarea>
                    <small>This description will appear on invoices.</small>
                </div>
            </div>
            <div class="">
                <a class="collapsed2" id="collapsed2" data-toggle="collapse" href="javascript:void(0);"
                    data-target="#accordion-item-icons-4" aria-expanded="false">
                    Custom Fields <i class="fas fa-sort-down align-text-top"></i>
                </a>
            </div>
            <div class="collapse" id="accordion-item-icons-4" style="">
                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-md-9 form-group mb-3">
                        <div>
                            Have more information you want to add? You can create custom fields for
                            contacts by going to "Settings" and clicking "Custom Fields".
                            <a class="ml-2" href="#" target="_blank" rel="noopener noreferrer">
                                Learn More
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <br>

            <div class="my-2 px-3 py-4 date-rate-duration-block container">
                <div class="mb-2 row ">
                    <div class="col-4"><label class="mb-0" for="date-field-id">Date</label></div>
                    <div class="col-3"><label class="mb-0" for="rate-field-id">Rate</label></div>
                    <div class="col-2"></div>
                    <div class="pl-4 col-3"><label class="mb-0" for="duration-field-id">Duration</label></div>
                </div>
                <div class="row ">
                    <div class="col-4">
                        <input class="form-control datepicker" id="datepicker" value="{{ convertUTCToUserTimeZone('dateOnly') }}"
                            name="start_date" type="text" placeholder="mm/dd/yyyy">

                    </div>
                    <div class="col-3">
                        <div class="">
                            <div class="px-0 undefined">
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div><input
                                        id="rate-field-id" name="rate_field_id" maxlength="15"
                                        class="form-control" min="0" value="{{$defaultRate}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-0 col-2">
                        <div>
                            <div class=""><select id="test-rate-type-field-id" name="rate_type_field_id"
                                    class="form-control custom-select  ">
                                    <option value="hr">/hr</option>
                                    <option value="flat">flat</option>
                                </select></div>
                        </div>
                    </div>
                    <div class="pl-4 col-3">
                        <div class=""><input id="duration-field" maxlength="15" min="0" name="duration_field"
                                class="form-control text-right" value=""></div>
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
                    <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                </div>
                <button type="submit" id="submit" name="savenew" value="sn" class="btn btn-outline-secondary  submitbutton">
                    Save and New
                </button>
                <button type="submit" id="submit1" name="save" value="s" class="btn btn-primary submitbutton">Save</button>
            </div>
        </form>
    </div>
    <div class="tab-pane fade" id="bulkTimeEntry" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="savebulkTimeEntry" id="savebulkTimeEntry" name="savebulkTimeEntry" method="POST">
            @csrf
            <div id="showError" class="showError" style="display:none"></div>

            <div class="row pb-3 mb-3 " style="border-bottom: 1px solid #e1e1e1 !important;">

                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Date</label>
                    <input class="form-control datepicker" id="datepicker" value="{{ convertUTCToUserTimeZone('dateOnly') }}" name="start_date"
                        type="text" placeholder="mm/dd/yyyy">

                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">User</label>
                    <select class="form-control staff_user" id="staff_user" name="staff_user">

                        <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                        <option value="{{$CasevloadFirmStaffvalal->id}}"  <?php echo ($CasevloadFirmStaffvalal->id == Auth::user()->id) ? 'selected' : ''; ?>>{{$CasevloadFirmStaffvalal->first_name}}
                            {{$CasevloadFirmStaffvalal->last_name}}</option>
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
                                    <option value="{{$Caseval->id}}">{{$Caseval->case_title}}
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
                            value="John called about the upcoming meeting, please call him back.">
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
                                    <input type="checkbox" name="billable[1]" class="billable-field form-check-input"
                                        checked="checked">
                                    <div class="billtext" id="replaceAmt1"> Billable - Rate will be calculated once
                                        court case is selected</div>
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
                                    <option value="{{$Caseval->id}}">{{$Caseval->case_title}}
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
                            value="John called about the upcoming meeting, please call him back.">
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
                                    <input type="checkbox" name="billable[]" class="billable-field form-check-input"
                                        checked="">
                                    <div class="billtext"> Billable - Rate will be calculated once court case is
                                        selected</div>
                                </label>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="text-right col-4 showtaax" >0.1 = 6 minutes</div> --}}
                    <div class="col-1"></div>
                </div>
            </div>
            <div class="after-add-more-new"></div>
            <div class="row ">
                <button type="button" id="add-one-row-id" class="btn btn-link add-more">Add another</button>
                <button type="button" id="add-five-rows-id" class="btn btn-link add-more-five">Add 5 more rows</button>
            </div>
            <div class="modal-footer pb-0">
                <div class="col-md-2 form-group mb-3">
                    <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                </div>

                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button type="submit" name="save" value="s" class="btn btn-primary submitbutton">Save Entries</button>
            </div>
            <input type="hidden" name="hideinputcount2" id="hideinputcount2" value="1" />
        </form>
    </div>
</div>
<style>
    .hide {
        display: none;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $("#case").select2({
            allowClear: true,
            placeholder: "Select a case...",
            theme: "classic",
            dropdownParent: $("#loadTimeEntryPopup"),
        });

        $("#staff_user").select2({
            allowClear: true,
            placeholder: "Select a user...",
            theme: "classic",
            dropdownParent: $("#loadTimeEntryPopup"),
        });
        $("#activity").select2({
            allowClear: true,
            placeholder: "Select activity...",
            theme: "classic",
            dropdownParent: $("#loadTimeEntryPopup"),
        });
        $('#collapsed2').click(function () {
            $("#collapsed2").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
                'fa-sort-down align-text-top');

        });

        loadDefault();
        $(".add-more").click(function () {
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

            $option2 = $clone.find('[name="activity[]"]');
            $option2.attr('id', 'hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            $option2.attr('required', 'required');

            $option3 = $clone.find('[name="duration[]"]');
            $option3.attr('id', 'hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            $option3.attr('required', 'required');

            $option33 = $clone.find('[name="billable[]"]');
            $option33.attr('id', 'billableid' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');

            $option22 = $clone.find('[class="billtext"]');
            $option22.attr('id', 'replaceAmt' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            // Add new field
            $('#savebulkTimeEntry').validate('add-more', $option);
            $("#div" + (parseInt(hideinputcount2) + parseInt(1)) + "").find("label").attr("for",
                'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');

            //For option 1
            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'case_or_lead[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 2
            $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'activity[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'duration[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#billableid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'billable[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');


            $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));

            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " Please select a case"
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
                messages: {
                    required: " Invalid",
                    number: " Invalid"
                }
            });
            $("#savebulkTimeEntry").validate();
        });

        $('#savebulkTimeEntry').on('click', '.remove', function () {
            defaultValidation();
            var $row = $(this).parents('.maturity_div'),
                $option = $row.find('[name="case_or_lead[]"]');

            $row.remove();
            $('#savebulkTimeEntry').validate('removeField', $option);
            var count = $('#hideinputcount2').val();

            count--;
            $('#hideinputcount2').val(count);

        });
        $('#savebulkTimeEntry').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });
        $('#savebulkTimeEntry').submit(function (e) {
            e.preventDefault();
            beforeLoader();
            if (!$('#savebulkTimeEntry').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#savebulkTimeEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/savebulkTimeEntry", // json datasource
                data: dataString,
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
                        toastr.success('Your time entry has been created', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        if (buttonpressed == 'savenew') {
                            $('#savenewTimeEntry')[0].reset();
                            showDropdown();
                        } else {
                            window.location.reload();
                        }
                        $("#innerLoader").css('display', 'none');

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

                $option2 = $clone.find('[name="activity[]"]');
                $option2.attr('id', 'hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(
                    1)) + '');
                $option2.attr('required', 'required');

                $option3 = $clone.find('[name="duration[]"]');
                $option3.attr('id', 'hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(
                    1)) + '');
                $option3.attr('required', 'required');

                $option22 = $clone.find('[class="billtext"]');
                $option22.attr('id', 'replaceAmt' + (parseInt(hideinputcount2) + parseInt(1)) + '');

                $option33 = $clone.find('[name="billable[]"]');
                $option33.attr('id', 'billableid' + (parseInt(hideinputcount2) + parseInt(1)) + '');

                $optionDescripton = $clone.find('[name="description[]"]');
                $optionDescripton.attr('id', 'descriptionid' + (parseInt(hideinputcount2) + parseInt(
                    1)) + '');

                // Add new field
                $('#savebulkTimeEntry').validate('add-more', $option);
                $("#div" + (parseInt(hideinputcount2) + parseInt(1)) + "").find("label").attr("for",
                    'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');

                //For option 1
                $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'case_or_lead[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For option 2
                $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'activity[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For option 3
                $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                    'duration[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                //For option 3
                $('#billableid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name', 'billable[' +
                    (parseInt(hideinputcount2) + parseInt(1)) + ']');

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
                        messages: {
                            required: " Invalid",
                            number: " Invalid"
                        }
                    });
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
        $('#hideoptioninput2activity1').rules("add", {
            required: true,
            messages: {
                required: " Please specify an activity"
            }
        });

        $('#hideoptioninput2duration1').rules("add", {
            required: true,
            number: true,
            messages: {
                required: " Invalid",
                number: " Invalid"
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
                    required: true,
                    number: true
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
                    number: "Allows number only."
                }
            }
        });
        var buttonpressed;
        $('.submitbutton').click(function () {
            buttonpressed = $(this).attr('name')
        })
        $('#savenewTimeEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#savenewTimeEntry').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#savenewTimeEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/saveTimeEntryPopup", // json datasource
                data: dataString,
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
                        toastr.success('Your time entry has been created', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        if (buttonpressed == 'savenew') {
                            $('#savenewTimeEntry')[0].reset();
                            showDropdown();
                        } else {
                            window.location.reload();
                        }
                        afterLoader();
                    }
                } ,error: function (xhr, status, error) {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                    }
            });
        });

        $(document).on('change', ".case_or_lead", function () {
            var f = $(this).attr("dvid");
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/getAndCheckDefaultCaseRate",
                data: {
                    'case_id': $(this).attr('value')
                },
                success: function (res) {
                    console.log(f);
                    $("#replaceAmt" + f).text("Billable - Rate :" + res.data);
                    console.log("#replaceAmt" + f);
                }
            })
        });



    });
    showDropdown();

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

            $option2 = $clone.find('[name="activity[]"]');
            $option2.attr('id', 'hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            $option2.attr('required', 'required');

            $option3 = $clone.find('[name="duration[]"]');
            $option3.attr('id', 'hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1)) + '');
            $option3.attr('required', 'required');

            $option22 = $clone.find('[class="billtext"]');
            $option22.attr('id', 'replaceAmt' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            $option33 = $clone.find('[name="billable[]"]');
            $option33.attr('id', 'billableid' + (parseInt(hideinputcount2) + parseInt(1)) +
                '');
            // Add new field
            $('#savebulkTimeEntry').validate('add-more', $option);
            $("#div" + (parseInt(hideinputcount2) + parseInt(1)) + "").find("label").attr("for",
                'hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1)) + '');

            //For option 1
            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'case_or_lead[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 2
            $('#hideoptioninput2activity' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'activity[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#hideoptioninput2duration' + (parseInt(hideinputcount2) + parseInt(1))).attr('name',
                'duration[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

            //For option 3
            $('#billableid' + (parseInt(hideinputcount2) + parseInt(1))).attr('name', 'billable[' + (parseInt(
                hideinputcount2) + parseInt(1)) + ']');


            $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));

            $('#hideoptioninput2' + (parseInt(hideinputcount2) + parseInt(1))).rules("add", {
                required: true,
                messages: {
                    required: " Please select a case"
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
                messages: {
                    required: " Invalid"
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
        $('#hideoptioninput2activity1').rules("add", {
            required: true,
            messages: {
                required: " Please specify an activity"
            }
        });

        $('#hideoptioninput2duration1').rules("add", {
            required: true,
            messages: {
                required: " Invalid"
            }
        });
    }
    

</script>
