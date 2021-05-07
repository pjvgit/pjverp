<form class="saveRate" id="saveRate" name="saveRate" method="POST">
    @csrf
    <div class="col-md-12">
      <span id="response"></span>

      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label">Default Rate</label>
        <div class="input-group mb-3 col-sm-9">
            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
            <input class="form-control number" maxlength="20" name="default_rate" id="default_rate" value="{{$user[0]->default_rate}}" type="text" aria-label="Amount (to the nearest dollar)">
            <div class="input-group-append"><span class="input-group-text">/hr</span></div>
            <small>Please Note: changing a user's default billing rate will not impact their pre-set case rates.</small>
            <div  class="input-group col-sm-9" id="TypeError"></div>
        </div>
        
    </div>
    <div class="form-group row float-right">
        <a href="#">
            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    "use strict";
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

    
        $('#saveRate').submit(function (e) {
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#saveRate').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/saveRate", // json datasource
                    data: {
                        "default_rate": $("#default_rate").val(),
                        "user_id":{{$_POST['user_id']}}
                    },
                    success: function (res) {
                        window.location.reload();
                        // $("#preloader").hide();
                        // $("#response").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Changes saved.</div>');
                        // $("#response").show();
                        // $("#innerLoader").css('display', 'none');
                        // return false;
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
    
    
    
    $('#collapsed').click(function() { 
        $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
         
    });

    $("#first_name").focus();
</script>