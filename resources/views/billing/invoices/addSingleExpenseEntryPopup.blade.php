<div class="tab-content" id="myTabContent">
    <form class="saveNewExpenseEntry" id="saveNewExpenseEntry" name="saveNewExpenseEntry" method="POST">
        @csrf
        <input type="hidden" value="{{$case_id}}" name="case_id" id="case_id">
        <input type="hidden" value="{{$invoice_id}}" name="invoice_id" id="invoice_id">

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case</label>
            <div class="col-10 form-group mb-3">
              {{$CaseMasterData['case_title']}}
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">User</label>
            <div class="col-10 form-group mb-3">
                <select class="form-control staff_user select2" id="staff_user" name="staff_user">
                    <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                    <option value="{{$CasevloadFirmStaffvalal->id}}">{{$CasevloadFirmStaffvalal->first_name}}
                        {{$CasevloadFirmStaffvalal->last_name}}</option>
                    <?php } ?>
                </select>
                <span id="usError"></span>
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
                <span id="acError"></span>
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();" href="javascript:;">Add
                    new activity</a></label>
        </div>
        <div class="form-group row" id="area_text">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Activity</label>
            <div class="col-md-6 form-group mb-3">
                <input class="form-control" id="activity_text" value="" maxlength="255" name="activity_text" type="text"
                    placeholder="Enter new activity">
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
                    This expenses is billable.</label>

            </div>

        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="case_description" class="form-control"
                    rows="5"></textarea>
                <small>This description will appear on invoices.</small>
            </div>
        </div>
        <div class="my-2 px-3 py-4 date-rate-duration-block container">
            <div class="mb-2 row ">
                <div class="col-4"><label class="mb-0" for="date-field-id">Date</label></div>
                <div class="col-4"><label class="mb-0" for="rate-field-id">Cost</label></div>
                <div class="col-4"><label class="mb-0" for="duration-field-id">Quantity</label></div>
            </div>
            <div class="row ">
                <div class="col-4">
                    <input class="form-control datepicker" id="datepicker" value="{{date('m/d/Y')}}" name="start_date"
                        type="text" placeholder="mm/dd/yyyy">

                </div>
                <div class="col-4">
                    <div class="">
                        <div class="px-0 undefined">
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input id="rate_field_id" name="rate_field_id" maxlength="15" class="form-control number"
                                    value="{{$defaultRate}}">
                            </div>
                            <span id="eCost"></span>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class=""><input id="duration-field" maxlength="15" name="duration_field"
                            class="form-control text-right" value=""></div>
                </div>
            </div>
            <div class="row ">
                <div class="col-4"></div>
                <div class="col-4"></div>
                <div class="col-4">0.1 = 6 minutes </div>
            </div>
        </div>
        <div class="modal-footer  pb-0">
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
            </div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button type="submit" id="submit1" class="btn btn-primary submit">Save Expense</button>
        </div>
    </form>

</div>
<style>
    .hide {
        display: none;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $("#case").select2({
            allowClear: true,
            placeholder: "Select a case...",
            theme: "classic",
            dropdownParent: $("#addNewExpenseEntry"),
        });

        $("#staff_user").select2({
            allowClear: true,
            placeholder: "Select a user...",
            theme: "classic",
            dropdownParent: $("#addNewExpenseEntry"),
        });
        $("#activity").select2({
            allowClear: true,
            placeholder: "Select activity...",
            theme: "classic",
            dropdownParent: $("#addNewExpenseEntry"),
        });

        $("#saveNewExpenseEntry").validate({
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
                    required: true
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
                    required: "Cost can't be blank"
                },
                duration_field: {
                    required: "Quantity can't be blank",
                    number: "Allows number only."
                }
            },errorPlacement: function (error, element) {
                if (element.is('#staff_user')) {
                    error.appendTo('#uaError');
                }else if (element.is('#activity')) {
                    error.appendTo('#acError');
                }else if (element.is('#rate_field_id')) {
                    error.appendTo('#eCost');
                } else {
                    element.after(error);
                }
            }
        });

        $('#saveNewExpenseEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#saveNewExpenseEntry').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#saveNewExpenseEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/saveSingleExpenseEntry", // json datasource
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
                        $('#addNewExpenseEntry').animate({ scrollTop: 0 }, 'slow');

                        afterLoader();
                        return false;
                    } else {
                        toastr.success('Your expense has been added', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        window.location.reload();
                        afterLoader();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#addNewExpenseEntry').animate({ scrollTop: 0 }, 'slow');
                    afterLoader();
                }
            });
        });

    });

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

</script>
