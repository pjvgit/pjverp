<optgroup label="Client">
    <?php foreach($ClientList as $key=>$val){ ?>
    <option uType="client" <?php if($val->id==$client_id){ echo "selected=selected";} ?> value="{{$val->id}}"> {{substr($val->name,0,200)}} (Client) </option>
    <?php } ?>
</optgroup>
<optgroup label="Company">
    <?php foreach($CompanyList as $CompanyListKey=>$CompanyListVal){ ?>
    <option uType="company" <?php if($CompanyListVal->id==$client_id){ echo "selected=selected";} ?> value="{{$CompanyListVal->id}}"> {{substr($CompanyListVal->first_name,0,200)}} (Company)</option><?php } ?>
</optgroup>