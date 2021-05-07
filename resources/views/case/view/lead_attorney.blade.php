<div class="col-md-12">
    
    <form class="saveLeadAttorneyForm" id="saveLeadAttorneyForm" name="saveLeadAttorneyForm" method="POST" action="#">
        <input class="form-control" id="case_id" value="{{ $case_id}}" name="case_id" type="hidden">
        @csrf
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-10 col-form-label">Choose a Lead Attorney for this case</label>
            <div class="col-md-12 form-group mb-3">
                <select id="lead_attorney_id" name="lead_attorney_id" class="form-control custom-select col">
                    <option value=""></option>
                    <?php 
                        foreach($caseStaff as $ksul=>$vsul){?>
                    <option <?php  if($vsul->id==$vsul->lead_attorney) { echo "selected=selected"; } ?> value="{{$vsul->id}}">{{$vsul->first_name}} {{$vsul->last_name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display:none;"></div>
            </div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit_with_user" type="submit">Save</button>
        </div>
    </form>
</div>
<script type="text/javascript">
     
    $(document).ready(function () {
        
        $('#saveLeadAttorneyForm').submit(function (e) {
            e.preventDefault();

            $("#innerLoader").css('display', 'block');
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveLeadAttorney", // json datasource
                data: dataString,
                success: function (res) {
                    window.location.reload();

                }
            });
        });
    });
</script>