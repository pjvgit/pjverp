<div class="form-group row">
    <div class="col">
        
        <?php  foreach($loadFirmStaff as $key=>$val){?>
        <div class="d-flex py-1">
            <label for="time-estimate-20186218" class="col-sm-3 col-form-label">{{$val->first_name}}
                {{$val->last_name}}</label>
            <input id="time-estimate-20186218" name="time_estimate_for_staff[{{$val->id}}]" ownid="{{$val->id}}" min="0" type="number"
                class="col-sm-2 form-control onlyNumber price userwiseHours" value="{{($fillsedHours[$val->id])??0}}">
            <div class="p-2">hours</div>
        </div>
        <?php } ?>
        <div class="d-flex py-1">
            <label for="time-estimate-total" class="col-sm-3 col-form-label"><b>Total Hours</b></label>
            <input id="totalPrice" name="timee_stimate_total" readonly="" type="text"
                class="col-sm-2 form-control onlyNumber" value="0">
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $(".onlyNumber").keypress(function (e) {
            if (String.fromCharCode(e.keyCode).match(/[^0-9]/g)) return false;
        });
        DoSum();
        $('.price').keyup(function () {
            DoSum();
       
        });
    });
  

    function DoSum(){
        var sum = 0;
        $('.price').each(function() {
            sum += Number($(this).val());
        });
        
        // set the computed value to 'totalPrice' textbox
        $('#totalPrice').val(sum);
        
    }

</script>