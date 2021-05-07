<div class="col-md-12">
    
    <form class="saveLeadOriginatingForm" id="saveLeadOriginatingForm" name="saveLeadOriginatingForm" method="POST" action="#">
        <input class="form-control" id="case_id" value="{{ $case_id}}" name="case_id" type="hidden">
        @csrf
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-10 col-form-label">Choose an Originating Attorney for this case</label>
            <div class="col-md-12 form-group mb-3">
                <select id="lead_originating_id" name="lead_originating_id" class="form-control custom-select col">
                    <option value=""></option>
                    <?php 
                        foreach($caseStaff as $ksul=>$vsul){?>
                    <option <?php  if($vsul->id==$vsul->originating_attorney) { echo "selected=selected"; } ?> value="{{$vsul->id}}">{{$vsul->first_name}} {{$vsul->last_name}}</option>
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
        
        $('#saveLeadOriginatingForm').submit(function (e) {
            e.preventDefault();

            $("#innerLoader").css('display', 'block');
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveOriginatingAttorney", // json datasource
                data: dataString,
                success: function (res) {
                    window.location.reload();

                }
            });
        });
    });
</script>