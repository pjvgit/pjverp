<form class="loadInvoiceForm" id="loadInvoiceForm" name="loadInvoiceForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="inputEmail3" class="col-form-label">Select Invoice</label>
                <select class="form-control invoice select2" id="invoice" name="invoice">
                    <option></option>
                    <?php foreach($Invoices as $k=>$v){?>
                        <option value="{{$v->id}}">{{sprintf('%06d', $v->id)}}-{{$v->ctitle}} (${{number_format($v->due_amount,2)}})</option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        localStorage.setItem("selectedInvoice", null);
        afterLoader();
        $(".select2").select2({
            allowClear: true,
            placeholder: "Select...",
            theme: "classic",
            dropdownParent: $("#recordPayment"),
        });
        $('#invoice').on('select2:select', function (e) {
            var data = e.params.data;
            localStorage.setItem("selectedInvoice", data.id);
            $("#recordPayment").modal("hide");
            payinvoice(localStorage.getItem("selectedInvoice"));
            $("#payInvoice").modal("show")
        });
        $("#loadInvoiceForm").validate({
            rules: {
                activity_title: {
                    required: true
                }
            },
            messages: {
                activity_title: {
                    required: "Name can't be blank",
                }
            }
        });
    });

    $('#loadInvoiceForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#loadInvoiceForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#loadInvoiceForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/activities/saveActivity", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
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
                    $('#loadInvoiceForm').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
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

    $('input[name="flat_fees"]').click(function () {
        if ($("#flat_fees").prop('checked') == true) {
            $("#showAmount").show();
        } else {
            $("#showAmount").hide();
        }
    });
    $('#amountinput').on('keypress', function (event) {
        var regex = new RegExp("^[.0-9]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $("#showAmount").hide();
    $("#first_name").focus();

</script>
