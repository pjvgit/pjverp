<select id="billing_contact" name="billing_contact" class="form-control custom-select col">
    <option value=""></option>
    <?php 
        foreach($selectdUSerList as $ksul=>$vsul){?>
    <option value="{{$vsul->id}}" selected="selected" >{{$vsul->first_name}} {{$vsul->last_name}}</option>
    <?php } ?>
</select>