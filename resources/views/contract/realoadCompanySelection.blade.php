<label for="inputEmail3" class="col-sm-2 col-form-label">Company</label>
<div class="col-md-6 form-group mb-3">
    <select id="company_name" name="company_name[]"  multiple  class="company_name form-control custom-select col"
        style="width:100%">
        <option value="">Select company</option>
        <?php foreach($CompanyList as $companyKey=>$companyVal){?>
        <option <?php if(in_array($companyVal->id,$selectdCompany)){ echo "selected=selected"; }?> value="{{$companyVal->id}}"> {{$companyVal->name}}</option>
        <?php } ?>
    </select>
</div>
<label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="openNewCompany();" href="javascript:;">Add new Company</a></label>

<script type="text/javascript">
    $(document).ready(function () {
        $('#company_name').select2();
        
    });
    $('#company_name').on("select2:unselect", function(e){
         var unselected_value = $('#company_name').val();
         $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/removeCompany", // json datasource
            data: {"unselected_value":unselected_value},
            success: function (res) {
                
            }
        });
    }).trigger('change');   
</script>
