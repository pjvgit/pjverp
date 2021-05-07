<h4 class="border-bottom border-gray pb-2">Access Permissions</h4>

<div class="alert alert-success"><b>Success!</b> Your new firm user is ready to go.</div>
<div class=" col-md-12">
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-12 col-form-label">
            <p><b>Note:</b> To update permissions for a firm user in the future, go to Settings, then click Firm Users.
                You'll see a link to edit permissions next to the user's name in the list.</p>
        </label>
    </div>
    <div class="form-group row float-right">
        <a href="{{ route('contacts/attorneys') }}">
            <button class="btn btn-primary ladda-button example-button m-1" id="finish" data-style="expand-right">
                <span class="ladda-label">Close</span>
            </button>
        </a>
    </div>

    <div class="form-group row">
        <label for="inputEmail4" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary" id="innerLoader4"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
    </div>
</div>

<script type="text/javascript">
 $("#innerLoader4").css('display', 'none');
</script>
