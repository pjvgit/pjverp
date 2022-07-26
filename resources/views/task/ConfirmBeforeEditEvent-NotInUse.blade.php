<form class="deleteEvent" id="deleteEvent" name="deleteEvent" method="POST">
    <div id="showError2" style="display:none"></div>
    @csrf
    <input class="form-control" id="event_id" value="{{ $event_id}}" name="event_id" type="hidden">
    <div class=" col-md-12">

      
        <?php
        if($CaseEvent->parent_evnt_id=="0"){?>
         <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label">
          Are you sure you want to delete this event?
          <input type="radio" style="display:none;" name="delete_event_type" checked="checked" class="pick-option mr-2" value="SINGLE_EVENT">
        </label>
         </div>
          <?php
        }else{ ?> 

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-10 form-group mb-3">
                    <div class="m-2">
                        <label class="form-check-label">
                            <input type="radio" name="delete_event_type" class="pick-option mr-2" value="SINGLE_EVENT">
                                <span>This event only</span>
                            </label>
                        </div>
                    <div class="m-2">
                        <label class="form-check-label">
                            <input type="radio" name="delete_event_type" class="pick-option mr-2"  value="THIS_AND_FOLLOWING_EVENTS"><span>This and following events</span>
                        </label>
                    </div>
                    <div class="m-2">
                        <label class="form-check-label">
                            <input type="radio" name="delete_event_type" class="pick-option mr-2" value="ALL_EVENTS" checked="">
                            <span>All events</span>
                        </label>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" id="goback" type="button">Go Back</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit" data-style="expand-right">
                <span class="ladda-label">Ok</span>
            </button>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader1" style="display: none;"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader1").css('display', 'none');
       
    $("#goback").on('click',function(){
        $("#loadEditEventPopup").css('display','block');
        $("#deleteEvent").css('display','block !important');
        $("#deleteEvent").addClass('show','block !important');
        
    });
    $('#deleteEvent').submit(function (e) {
        $("#innerLoader1").css('display', 'block');
        e.preventDefault();

        var dataString = $("form").serialize();
      
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/deleteEvent", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader1").css('display', 'block');
                if (res.errors != '') {
                    $('#showError2').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError2').append(errotHtml);
                    $('#showError2').show();
                    $("#innerLoader1").css('display', 'none');
                    return false;
                } else {
                    window.location.reload();
                    // $('#EditCaseModel').modal('hide');
                }
            }
        });
    });
});
</script>
