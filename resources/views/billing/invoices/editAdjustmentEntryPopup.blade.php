<div class="tab-content" id="myTabContent">
    <form class="updateAdjustmentForm" id="updateAdjustmentForm" name="updateAdjustmentForm" method="POST">
        @csrf
        <input type="hidden" value="{{$InvoiceAdjustment['id']}}" name="id" id="id">

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right">Item</label>
            <div class="col-9 form-group mb-3">
                <select class="form-control staff_user select2" id="item" name="item">
                    <option></option>
                    <option <?php if($InvoiceAdjustment['item']=="discount"){ echo "selected=selected"; }?> value="discount">Discount</option>
                    <option <?php if($InvoiceAdjustment['item']=="intrest"){ echo "selected=selected"; }?> value="intrest">Intrest</option>
                    <option <?php if($InvoiceAdjustment['item']=="tax"){ echo "selected=selected"; }?> value="tax">Tax</option>
                    <option <?php if($InvoiceAdjustment['item']=="addition"){ echo "selected=selected"; }?> value="addition">Addition</option>
                </select>
                <span id="1Error"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Applied To</label>
            <div class="col-9 form-group mb-3">
                <select class="form-control staff_user select2" id="applied_to" name="applied_to">
                    <option></option>
                    <option <?php if($InvoiceAdjustment['applied_to']=="flat_fees"){ echo "selected=selected"; }?> value="flat_fees">Flat Fees</option>
                    <option <?php if($InvoiceAdjustment['applied_to']=="time_entries"){ echo "selected=selected"; }?> value="time_entries">Time Entries</option>
                    <option <?php if($InvoiceAdjustment['applied_to']=="expenses"){ echo "selected=selected"; }?> value="expenses">Expenses</option>
                    <option <?php if($InvoiceAdjustment['applied_to']=="balance_forward_total"){ echo "selected=selected"; }?> value="balance_forward_total">Balance Forward Total</option>
                    <option <?php if($InvoiceAdjustment['applied_to']=="sub_total"){ echo "selected=selected"; }?> value="sub_total">Sub Total</option>
                </select>
                <span id="2Error"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Type</label>
            <div class="col-9 form-group mb-3">
                <select class="form-control staff_user select2" id="ad_type" name="ad_type">
                    <option></option>
                    <option <?php if($InvoiceAdjustment['ad_type']=="percentage"){ echo "selected=selected"; }?> value="percentage">% - Percentage</option>
                    <option <?php if($InvoiceAdjustment['ad_type']=="amount"){ echo "selected=selected"; }?> value="amount">$ - Amount</option>
                </select>
                <span id="3Error"></span>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Notes</label>
            <div class="col-9 form-group mb-3">
                <textarea name="notes" class="form-control" rows="5">{{$InvoiceAdjustment['notes']}}</textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Basis</label>
            <div class="col-9 form-group mb-3">
                <input id="basic" name="basic" maxlength="15" class="form-control number" value="{{$InvoiceAdjustment['basis']}}">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Percentage(%)</label>
            <div class="col-9 form-group mb-3">
                <input id="percentage" name="percentage" maxlength="3" class="form-control" min="1" max="100" value="{{$InvoiceAdjustment['percentages']}}"
                    type="number">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Amount</label>
            <div class="col-9 form-group mb-3">
                <input id="amount" name="amount" readonly class="form-control number" value="{{$InvoiceAdjustment['amount']}}">
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
            <button type="submit" id="submit1" class="btn btn-primary submit">Save Adjustment</button>
        </div>
    </form>

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2({
            allowClear: true,
            placeholder: "Select...",
            theme: "classic",
            dropdownParent: $("#editAdjustmentEntry"),
        });

        $("#updateAdjustmentForm").validate({
            rules: {
                item: {
                    required: true
                },
                applied_to: {
                    required: true
                },
                ad_type: {
                    required: true
                },
                percentage: {
                    min: 0,
                    max: 100
                },
            },
            messages: {
                item: {
                    required: "Item can't be blank"
                },
                applied_to: {
                    required: "Applied to can't be blank"
                },
                ad_type: {
                    required: "Type can't be blank"
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#item')) {
                    error.appendTo('#1Error');
                } else if (element.is('#applied_to')) {
                    error.appendTo('#2Error');
                } else if (element.is('#ad_type')) {
                    error.appendTo('#3Error');
                } else {
                    element.after(error);
                }
            }
        });

      
        $('#item').on("select2:unselect", function (e) {
            $("#applied_to").html('');
            $("#ad_type").html('');
            $('#updateAdjustmentForm')[0].reset();
            $("#basic").removeAttr('readonly');
            $("#percentage").val('').removeAttr('readonly');
            $("#amount").attr('readonly', true);
        });


        $('#applied_to').on("select2:select", function (e) {
            var curVal = $(this).val();
            if (curVal == "expenses") {
                $("#basic").val($("#expense_sub_total_text").val());
            } else if (curVal == "sub_total") {
                $("#basic").val($("#sub_total_text").val());
            } else if (curVal == "flat_fees") {
                $("#basic").val($("#flat_fee_sub_total_text").val());
            } else if (curVal == "time_entries") {
                $("#basic").val($("#time_entry_sub_total_text").val());
            } else {
                $("#basic").val("");
            }
            // $('#basic').number(true, 2);
        });

        $('#ad_type').on("select2:select", function (e) {
            var curVal = $(this).val();
            if (curVal == "amount") {
                $("#basic").val("").attr('readonly', true);
                $("#percentage").val("-").attr('readonly', true);
                $("#amount").removeAttr('readonly');
                $("#amount").val("");
            } else {
                $("#basic").removeAttr('readonly');
                $("#percentage").val('').removeAttr('readonly');
                $("#basic").val($("#time_entry_sub_total_text").val());
                $("#amount").attr('readonly', true);
            }

        });

        $("#percentage").on("keyup change", function (e) {
            var basic = $("#basic").val();
            var percentage = $("#percentage").val();
            var calculation = (percentage / 100) * basic;
            $("#amount").val(calculation);
        });
        $('#updateAdjustmentForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#updateAdjustmentForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#updateAdjustmentForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/updateAdjustmentEntry", // json datasource
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
                        $('#addNewExpenseEntry').animate({
                            scrollTop: 0
                        }, 'slow');

                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                        afterLoader();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#addNewExpenseEntry').animate({
                        scrollTop: 0
                    }, 'slow');
                    afterLoader();
                }
            });
        });


        var curVal= $("#ad_type").val();
        if (curVal == "amount") {
            $("#basic").val("").attr('readonly', true);
            $("#percentage").val("-").attr('readonly', true);
            $("#amount").removeAttr('readonly');
            
        } else {
            $("#basic").removeAttr('readonly');
            $("#percentage").val('').removeAttr('readonly');
            $("#basic").val($("#time_entry_sub_total_text").val());
            $("#amount").attr('readonly', true);
        }


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
