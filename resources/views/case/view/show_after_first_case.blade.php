<h5 class="text-center my-4"> <strong> would you like to do next? </strong> </h5>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#addContact" data-placement="bottom" onclick="addContact();"
            href="javascript:;">
            <div class="card card-icon mb-4">
                <div class="card-body text-center"><i class="i-Timer1"></i>
                    <p class="text-muted mt-2 mb-2">Start tracking time you spend on this case.</p>
                    <p class="lead text-10 m-0">Add Time Entry</p>
                </div>

            </div>
        </a>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#addContact" data-placement="bottom" onclick="addContact();"
            href="javascript:;">
            <div class="card card-icon mb-4">
                <div class="card-body text-center"><i class="i-Billing"></i>
                    <p class="text-muted mt-2 mb-2">Create an invoice with unbilled items.</p>
                    <p class="lead text-10 m-0">Create Invoice</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#addContact" data-placement="bottom" onclick="addContact();"
            href="javascript:;">
            <div class="card card-icon mb-4">
                <div class="card-body text-center"><i class="i-File-Block"></i>
                    <p class="text-muted mt-2 mb-2">Upload, organize, and create documents for this case.</p>
                    <p class="lead text-10 m-0">Add Document</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6">
        <a data-toggle="modal" data-target="#addContact" data-placement="bottom" onclick="addContact();"
            href="javascript:;">
            <div class="card card-icon mb-4">
                <div class="card-body text-center"><i class="i-Calendar-4"></i>
                    <p class="text-muted mt-2 mb-2">Create a meeting, reminder, or important event.</p>
                    <p class="lead text-10 m-0">Add Event</p>
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
