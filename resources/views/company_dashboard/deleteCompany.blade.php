<form class="deleteCompanyFormForm" id="deleteCompanyForm" name="deleteCompanyForm" method="POST">
    <span id="response"></span>
    @csrf
    <input type="hidden" name="company_id" value="{{$company_id}}">
    <div id="showError" class="showError" style="display:none"></div>

    <?php if($case>0){
            ?><p>This company cannot be deleted because it is linked to court cases or there are payments linked to
        their account</p><?php 
        }else{
            ?>
    <p>Are you sure you want to delete this company? This action is <b>permanent</b> and cannot be undone. </p>
    
    <?php 
        }
?>
    <br>
    <div class="justify-content-between modal-footer">
        <div id="status" class="px-2" style="display: none;">Saved</div>
        &nbsp;
        <div class="loader-bubble loader-bubble-primary innerLoader" style="display: none;"></div>
        <div>
            <?php if($case<=0){ 
                    ?>
            <a href="#">
                <button class="btn btn-outline-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>

            <button class="btn btn-outline-primary m-1" id="submitButton" value="savenote" type="submit">Delete</button>
            <?php
                }else{
                    ?>
            <a href="#">
                <button class="btn btn-outline-secondary m-1" type="button" data-dismiss="modal">Ok</button>
            </a>
            <?php     
                 } ?>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $('#deleteCompanyForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            var dataString = '';
            dataString = $("#deleteCompanyForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/companies/deleteCompany", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $("#innerLoaderTime").css('display', 'block');
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
                        afterLoader();
                        // $("#deleteCompanyForm").scrollTop(0);
                        $('#deleteCompanyForm').animate({
                            scrollTop: 0
                        }, 'slow');

                        return false;
                    } else {
                        window.location.href = baseUrl + '/contacts/company';
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
    });

</script>
