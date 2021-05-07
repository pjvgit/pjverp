<form class="saveRateForm" id="saveRateForm" name="saveRateForm" method="POST">
    @csrf
    <input type="hidden" name="rate_type" value="{{$rateType}}"> 
    <input type="hidden" name="case_id" value="{{$case_id}}"> 
    <input type="hidden" name="staff_id" value="{{$staff_id}}"> 
    <div class="col-md-12">
      <span id="response"></span>

      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label">Default Rate</label>
        <div class="input-group mb-3 col-sm-9">
            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
            <input class="form-control number" maxlength="20"  name="default_rate" id="default_rate" value="{{$default_rate}}" type="text" aria-label="Amount (to the nearest dollar)">
            <div class="input-group-append"><span class="input-group-text">/hr</span></div>
            <div  class="input-group col-sm-9" id="TypeError"></div>
        </div>
        
    </div>
    <div class="form-group row float-right">
        <a href="#">
            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
        </a>

        <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
            data-style="expand-left"><span class="ladda-label">Save Rate</span><span
                class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
    </div>

    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
        </div>
    </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#response").hide();
        $("#saveRate").validate({
            rules: {
                default_rate: {
                    required: true,
                    number: true
                },
                
            },
            messages: {
                default_rate: {
                    required: "Please enter rate",
                    number: "Plese enter numeric value"
                },
               
            },
            
            errorPlacement: function (error, element) {
                if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                } else {
                    element.after(error);
                }
            }
        });

    
        $('#saveRateForm').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#saveRateForm').valid()) {
                $("#innerLoader").css('display', 'none');
                return false;
            }
            var dataString = '';
            dataString = $("#saveRateForm").serialize();
            $.ajax({
                    type: "POST",
                    url: baseUrl + "/court_cases/saveRate", // json datasource
                    data: dataString,
                    beforeSend: function (xhr, settings) {
                        settings.data += '&save=yes';
                    },
                    success: function (res) {
                      window.location.reload();
                    }
                });
              
        });

        //Amount validation
        $('input.number').keyup(function(event) {
            // skip for arrow keys
            if(event.which >= 37 && event.which <= 40) return;
            // format number
            $(this).val(function(index, value) {
                if(value.split('.').length>2) 
                    return value =value.replace(/\.+$/,"");
                return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        });

    });

</script>