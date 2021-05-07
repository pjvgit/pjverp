<form class="saveColor" id="saveColor" name="saveColor" method="POST">
    @csrf
    <div class="col-md-12">
      <span id="response"></span>


        <div class="form-group row">
            <div class="colorSelector" id="colorSelector{{$_POST['user_id']}}">
                <div id="selectedColor{{$_POST['user_id']}}" ></div>
                <input type="hidden" name="Ccode" value="{{str_replace("#","",$user[0]->default_color)}}" id="Ccode">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>  
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div> 
        
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>

            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Save Color</span><span
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
        $("#innerLoader").hide();
        
        $("#response").hide();
        var cid="colorSelector"+{{$_POST['user_id']}};
       
        $('#colorSelector'+{{$_POST['user_id']}}).ColorPicker({
            flat:true,
            livePreview:true,
            color: '{{$user[0]->default_color}}',
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                
                $('#'+cid).css('backgroundColor', '#' + hex);
                $("#Ccode").val(hex);
                 
            }
        });

        $('#saveColor').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/saveColorCode", // json datasource
                    data: {
                        "colorcode": $("#Ccode").val(),
                        "user_id":{{$_POST['user_id']}}
                    },
                    success: function (res) {
                        $("#preloader").hide();
                        $("#response").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Changes saved.</div>');
                        $("#response").show();
                        $("#innerLoader").css('display', 'none');
                        return false;
                    }
                });
              
        });

    });

</script>
<style>
    .colorSelector {
        background: url('{{BASE_LOGO_URL}}/public/assets/styles/css/images/select.png');
        background-color: {{$user[0]->default_color}};
    }
</style>