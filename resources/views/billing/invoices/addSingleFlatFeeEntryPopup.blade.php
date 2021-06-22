
    <form class="saveFlatFeeForm" id="saveFlatFeeForm" name="saveFlatFeeForm" method="POST">
        @csrf
        <input type="hidden" value="{{$case_id}}" name="case_id" id="case_id">
        <input type="hidden" value="{{$invoice_id}}" name="invoice_id" id="invoice_id">
        <input type="hidden" value="{{ $invoice_token }}" name="token_id" id="token_id">

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case</label>
            <div class="col-10 form-group mb-3">
              {{(@$CaseMasterData['case_title'])??'None'}}
            {{@$CaseMasterData['case_title']}}</div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">User</label>
            <div class="col-10 form-group mb-3">
                <select class="form-control staff_user select2" id="staff_user" name="staff_user">
                    <?php foreach ($loadFirmStaff as $loadFirmStaffkey => $CasevloadFirmStaffvalal) {?>
                    <option value="{{$CasevloadFirmStaffvalal->id}}">{{$CasevloadFirmStaffvalal->first_name}}
                        {{$CasevloadFirmStaffvalal->last_name}}</option>
                    <?php }?>
                </select>
                <span id="usError"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="case_description" class="form-control"
                    rows="5"></textarea>

            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Date</label>
            <div class="col-md-10 form-group mb-3">
                <div class="input-group">
                <input class="form-control datepicker" id="datepicker" value="{{date('m/d/Y')}}" name="start_date"
                        type="text" placeholder="mm/dd/yyyy">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Amount</label>
            <div class="col-md-10 form-group mb-3">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                        <input id="rate_field_id" name="rate_field_id" maxlength="15" class="form-control number" value="">
                    </div>
                </div>
            </div>
            <span id="eCost"></span>
        </div>
        <div class="modal-footer  pb-0">
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
            </div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button type="submit" id="submit1" class="btn btn-primary submit">Save Flat Fee</button>
        </div>
    </form>
<script type="text/javascript">
    $(document).ready(function () {
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $("#staff_user").select2({
            allowClear: true,
            placeholder: "Select a user...",
            theme: "classic",
            dropdownParent: $("#addNewFlatFeeEntry"),
        });

        $("#saveFlatFeeForm").validate({
            rules: {
                staff_user: {
                    required: true
                },
                rate_field_id: {
                    required: true,
                    number: true
                }
            },
            messages: {

                staff_user: {
                    required: "User can't be blank"
                },

                rate_field_id: {
                    required: "Amount can't be blank",
                    number: "Amount field allows number only."
                }
            },errorPlacement: function (error, element) {
                if (element.is('#staff_user')) {
                    error.appendTo('#uaError');
                }else if (element.is('#rate_field_id')) {
                    error.appendTo('#eCost');
                } else {
                    element.after(error);
                }
            }
        });

        $('#saveFlatFeeForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#saveFlatFeeForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#saveFlatFeeForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/saveSingleFlatFeeEntry", // json datasource
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
                        toastr.success('Your flat fees has been added', "", {
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
</script>
