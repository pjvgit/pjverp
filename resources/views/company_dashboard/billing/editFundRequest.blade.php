<div data-testid="retainer-request-modal">
    <form class="updateEditFund" id="updateEditFund" name="updateEditFund" method="POST">
        <span class="showError"></span>
        @csrf
        <input class="form-control" value="{{$RequestedFund->id}}" id="id" maxlength="250" name="id" type="hidden">

        <div class="modal-body retainer-requests-popup-content">
            <div class="row">
                <div class="col-12">
                    <p class="helper-text">Edit the request for #R-00{{$RequestedFund->id}} for<strong> {{$userData->cname}} </strong>in the amount of ${{number_format($RequestedFund->amount_due,2)}} due on {{date('m/d/Y',strtotime($RequestedFund->due_date))}}.</p>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="firstName1">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span></div>
                        <input id="amount" name="amount" class="form-control number" value="{{number_format($RequestedFund->amount_due,2)}}" maxlength="50">
                    </div>
                    <span id="amterror"></span>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="firstName1">Due Date</label>
                    <input class="form-control input-date" value="{{($RequestedFund->due_date) ? date('m/d/Y',strtotime(date('Y-m-d', strtotime(convertUTCToUserDate($RequestedFund->due_date, auth()->user()->user_timezone))))) : ''}}" id="due_date" maxlength="250" name="due_date" type="text">
                </div>
                
              </div>
              <div class="row"><div class="col-12"><p class="helper-text text-muted"><strong>Tip: </strong>Edit amount or due date to remove overdue or outstanding status</p></div></div>
            
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                </a>
                <button class="btn btn-primary m-1 submit" id="submitButton" type="submit">Update Request</button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'startDate': "dateToday",
            'todayHighlight': true
        });
        afterLoader();
        $("#updateEditFund").validate({
            rules: {
                amount: {
                    required: true,
                    minStrict: true,
                }
            },
            messages: {
                amount: {
                    required: "Invalid amount",
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#amount')) {
                    error.appendTo('#amterror');
                } else {
                    element.after(error);
                }
            }
        });

        $('#updateEditFund').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#updateEditFund').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#updateEditFund").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/saveEditFundRequest", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
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
    });

</script>
