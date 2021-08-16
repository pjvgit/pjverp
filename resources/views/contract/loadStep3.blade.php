<div id="showError" style="display:none"></div>
<h4 class="border-bottom border-gray pb-2">Firm Level Permissions</h4>


<form class="createStep3" id="createStep3" name="createStep3" method="POST">
    @csrf
    <input type="hidden" name="user_id" value="{{$user->id}}">
    <input type="hidden" name="case_id" value="{{$case_id}}">
             
    <div class=" col-md-12">
 
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">   <h6>Should this user be able to...</h6></label>
        <a href="#" class="col text-right col-sm-6" target="_blank" rel="noopener noreferrer">Learn more about user permissions</a>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Access data from every case in the system or only those
            he/she is linked to?</label>
               <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="access_case" checked="checked" value="0"><span> All firm cases</span><span
                    class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="access_case" value="1"><span>Only linked cases</span><span
                    class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Add new attorneys, paralegals, and support staff to
            your firm's {{config('app.name')}} account?</label>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="add_new" value="0" checked="checked"><span> Yes</span><span
                    class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="add_new" value="1"><span>No</span><span class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Edit user permission settings?</label>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="edit_permisssion" value="0"><span> Yes</span><span class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="edit_permisssion" value="1" checked="checked"><span>No</span><span
                    class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Delete items (events, documents, etc.) from
            {{config('app.name')}}?
        </label>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="delete_item" value="0" checked="checked"><span> Yes</span><span
                    class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="delete_item" value="1"><span>No</span><span class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Edit import-export capabilities?
        </label>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="import_export" value="0" checked="checked"><span> Yes</span><span
                    class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="import_export" value="1"><span>No</span><span class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Edit custom fields settings?
        </label>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="custome_fields" value="0" checked="checked"><span> Yes</span><span
                    class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="custome_fields" value="1"><span>No</span><span class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-6 col-form-label">Manage your firm's preferences, billing, and payment
            options?
        </label>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="manage_firm" value="0"><span> Yes</span><span class="checkmark"></span>
            </label>
        </div>
        <div class="col-sm-3">
            <label class="radio radio-outline-success">
                <input type="radio" name="manage_firm" value="1" checked="checked"><span>No</span><span
                    class="checkmark"></span>
            </label>
        </div>
    </div>
    <hr>
    <div class="form-group row float-right">
        <button class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
            <span class="ladda-label">Next</span>
        </button>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
    </div>
    </div>
</form>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function () {
        $("#innerLoader3").css('display', 'none');
        
    });
    $('#createStep3').submit(function (e) {
        $("#innerLoader3").css('display', 'block');
        e.preventDefault();

        var dataString = $("#createStep3").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveStep3", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader3").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    $("#innerLoader3").css('display', 'none');
                    return false;
                } else {
                    loadStep4(res);
                }
            }
        });
    });

    function loadStep4(res) {

        console.log(res);
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/loadStep4", // json datasource
            data: {
                "user_id": res.user_id,
                "case_id" : {{$case_id ?? 0}}
            },
            success: function (res) {
                $('#smartwizard').smartWizard("next");
                $("#innerLoader3").css('display', 'none');
                $("#step-4").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    }

</script>
