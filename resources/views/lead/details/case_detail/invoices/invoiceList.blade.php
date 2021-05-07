<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<div class="row ">
    <div class="col">
    </div>
    <div class="col">
        <div class="float-right">

            <a data-toggle="modal" data-target="#addCaseNote" data-placement="bottom" href="javascript:;">
                <button class="btn btn-outline-secondary  btn-rounded m-1 px-3" type="button">Tell us what you
                    think</button>
            </a>

            <a data-toggle="modal" data-target="#addNewInvoice" data-placement="bottom" href="javascript:;">
                <button class="btn btn-primary btn-rounded m-1 px-3" type="button" onclick="addNewInvoice();">Add
                    Invoice</button>
            </a>
        </div>
    </div>
</div>
<?php
if($totalInvoiceData<=0){
?>
<br>
<div class="d-flex flex-column justify-content-center align-items-center mt-4" style="height: 250px;">
    <i class="fas fa-file-invoice fa-2x m-3"></i>
    <h5 class="mt-2"><strong>Invoice for your consultation fee</strong></h5><span class="font-weight-light">Create
        and send an invoice to your lead for consultation fees or any pre-case fees.</span>
    <a data-toggle="modal" data-target="#addNewInvoice" data-placement="bottom" href="javascript:;">
        <button type="button" data-testid="empty-state-add-invoice" class="mt-2 font-weight-light btn btn-link"
        onclick="addNewInvoice();">Add Invoice</button>
    </a>
    
</div>
<?php
}else{?>
<div class="table-responsive">
    <table class="display table table-striped table-bordered" id="invoiceList" style="width:100%">
        <thead>
            <tr>
                <th width="10%">NUMBER</th>
                <th width="10%">TOTAL</th>
                <th width="10%">PAID</th>
                <th width="15%">CREATED</th>
                <th width="15%">DUE</th>
                <th width="10%">STATUS</th>
                <th width="30%"></th>
            </tr>
        </thead>

    </table>
</div>
<?php } ?>
