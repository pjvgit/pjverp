<?php $CommonController= new App\Http\Controllers\CommonController(); 
$clientArray=$companyArray=[];
foreach($Invoices as $k=>$v){
    if($v->user_level=="2"){
        $clientArray[]=$v;
    }else{
        $companyArray[]=$v;
    }
}
?>
<form class="depositForm" id="depositForm" name="depositForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="inputEmail3" class="col-form-label">Select Contact</label>
                <select class="form-control contact select2" id="contact" name="contact">
                    <option></option>
                    <?php if(!empty($clientArray)){?>
                    <optgroup label="Client">
                        <?php foreach($clientArray as $k=>$v){
                            ?>
                                <option value="{{$v->uid}}">{{$v->contact_name}} 
                                (<?php echo $CommonController->getUserTypeText($v->user_level); ?>)
                                </option>
                            <?php 
                        }?>
                    </optgroup>
                    <?php } ?>
                    <?php if(!empty($companyArray)){?>
                    <optgroup label="Company">
                        <?php foreach($companyArray as $k=>$v){
                           ?>
                            <option value="{{$v->uid}}">{{$v->contact_name}} 
                            (<?php echo $CommonController->getUserTypeText($v->user_level); ?>)
                             </option>
                        <?php 
                        } ?>
                    </optgroup>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        localStorage.setItem("selectedContact", null);
        afterLoader();
        $("#contact").select2({
            allowClear: true,
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            dropdownParent: $("#depositIntoTrust"),
        });
        $('#contact').on('select2:select', function (e) {
            var data = e.params.data;
            localStorage.setItem("selectedContact", data.id);
            $("#depositIntoTrust").modal("hide");
            depositIntoTrustPopup(localStorage.getItem("selectedContact"));
            $("#depositIntoTrustAccount").modal("show");
        });
    });
</script>
