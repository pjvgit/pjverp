<h5 class="text-center my-4"> Would you like to add a new or existing contact? </h5>
<div class="row">
            
    <div class="col-lg-4 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#AddContactModal" data-placement="bottom"  onclick="AddContactModal1();" href="javascript:;" >
   
            <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
            <div class="card-body text-center"><i class="i-Add-User"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">New Contact</p>
                </div>
            </div>
        </div>
    </a>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#addCompany" data-placement="bottom" onclick="addCompany();"  href="javascript:;" >
   
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
            <div class="card-body text-center"><i class="i-Green-House"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">Add Company</p>
                </div>
            </div>
        </div>
    </a>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#addExisting" onclick="addExisting();" data-placement="bottom" href="javascript:;" >
   
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
            <div class="card-body text-center"><i class="i-Find-User"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">Existing Contact</p>
                </div>
            </div>
        </div>
    </a>
    </div>
    
</div>


@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {

    });


</script>
@stop
