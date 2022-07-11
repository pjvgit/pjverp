<div class="row" bladeFile="resources/views/lead/details/case_detail/invoices/invoiceList.blade.php">
    <div class="col">
    </div>
    <div class="col">
        <div class="float-right">          
            <div id="bulk-dropdown" class="mr-2 actions-button btn-group">
                <div class="mx-2">
                    @if(auth()->user()->hasAnyPermission(['billing_add_edit', 'billing_view']) && auth()->user()->cannot('billing_restrict_time_entry_and_expense'))
                    <div class="btn-group show">
                        <button class="btn btn-info m-1 dropdown-toggle" data-toggle="dropdown"
                            id="actionbutton" disabled="disabled" aria-haspopup="true" aria-expanded="false">
                            Add New
                        </button>
                        <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 ">
                            <div class="card">
                                <a data-toggle="modal" data-target="#addNewInvoice" data-placement="bottom" href="javascript:;"
                                        onclick="addNewInvoice();"><button type="button" tabindex="0" role="menuitem" class="dropdown-item">
                                    Consultation Invoice</button>
                                </a>
                                <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;"
                                        onclick="addRequestFundPopup();"><button type="button" tabindex="0" role="menuitem" class="dropdown-item">
                                    Funds Request</button></a>                                
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if($totalInvoiceData<=0 && count($RequestedFundData) <= 0){
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
    <table class="display table table-striped table-bordered" id="invoiceListw" style="width:100%">
        <thead>
            <tr>
                <th width="4%">TYPE</th>
                <th width="5%">NUMBER</th>
                <th width="10%">AMOUNT</th>
                <th width="13%">CREATED</th>
                <th width="13%">DUE</th>
                <th width="10%">ACCOUNT</th>
                <th width="15%">ALLOCATAION</th>
                <th width="10%">STATUS</th>
                <th width="20%"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($FindInvoice as $aData)
            <tr>
                <td width="4%"><div class="text-left"><i class="fas fa-file-invoice"></i></div></td>
                <td width="5%"><div class="text-left"><a href="{{ url('bills/invoices/potentialview',$aData->decode_id)}}">{{$aData->invoice_id}}</a></div></td>
                <td width="10%"><div class="text-left">${{$aData->total_amount}}</div></td>
                <td width="13%"><div class="text-left">{{$aData->created_date_new}}</div></td>
                <td width="13%"><div class="text-left">{{date('m/d/Y',strtotime($aData->due_date))}}</div></td>
                <td width="10%"><div class="text-left"><i class="table-cell-placeholder"></i></div></td>
                <td width="15%"><div class="text-left"><i class="table-cell-placeholder"></i></div></td>
                <?php
                if($aData->status=="Paid"){
                    echo '<td width="10%"><div class="text-left"><i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;"></i>'.$aData->status.'</div></td>';
                }else if($aData->status=="Partial"){
                    echo '<td width="10%"><div class="text-left"><i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'.$aData->status.'</div></td>';
                }else if($aData->status=="Overdue"){
                    echo '<td width="10%"><div class="text-left"><i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'.$aData->status.'</div></td>';
                }else{
                    echo '<td width="10%"><div class="text-left">'.$aData->status.'</div></td>';
                }
                $editOption='<a class="btn btn-lg btn-link px-2 text-black-50 copyButton" data-toggle="modal"  data-target="#editInvoice" onclick="editInvoice('.$aData->id.')" data-placement="bottom"><i class="fas fa-pen align-middle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"></i></a>';
                    if($aData->due_amount > 0){
                        $editOption.='<a class="btn btn-lg btn-link px-2 text-black-50" data-toggle="modal"  data-target="#payInvoice"  onclick="payinvoice('.$aData->id.');" data-placement="bottom"><i class="fas fa-dollar-sign" data-toggle="tooltip" data-placement="top" title="" data-original-title="Record Payment"></i></a>';
                    }
                    $editOption.='<a class="btn btn-lg btn-link px-2 text-black-50" data-toggle="modal"  data-target="#sendInvoice" onclick="sendInvoice('.$aData->id.')" data-placement="bottom"><i class="fas fa-paper-plane" data-toggle="tooltip" data-placement="top" title="" data-original-title="Send"></i></a>';
                    $editOption.='<a class="btn btn-lg btn-link px-2 text-black-50" data-toggle="modal"  data-target="#downloadInvoice" onclick="downloadInvoice('.$aData->id.');"> <i class="fas fa-fw fa-cloud-download-alt test-download-bill" data-toggle="tooltip" data-placement="top" title="" data-original-title="Download"></i></a>';
                    $editOption.='<a class="btn btn-lg btn-link px-2 text-black-50" data-toggle="modal"  data-target="#deleteInvoicePopup" onclick="deleteInvoice('.$aData->id.');"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></a>';
                        
                    echo '<td width="20%"><div class="d-flex align-items-center float-right">'.$editOption.'</div></td>';
                ?>                
            </tr>
            @endforeach
            @foreach($RequestedFundData as $aData)
            <tr>
                <td width="4%"><div class="text-left"><i class="fas fa-hand-holding-usd"></i></div></td>
                <td width="5%"><div class="text-left">{{$aData->padding_id}}</div></td>
                <td width="10%"><div class="text-left">${{$aData->amt_requested}}</div></td>
                <td width="13%"><div class="text-left">{{$aData->last_send}}</div></td>
                <td width="13%"><div class="text-left">{{$aData->due_date_format}}</div></td>
                <td width="10%"><div class="text-left">{{ ucfirst($aData->deposit_into_type). ' (Operating Account)'}}</div></td>
                <td width="15%"><div class="text-left">
                    @if($aData->allocated_to_lead_case_id)
                        {{$aData->allocateToLeadCase->potential_case_title ?? ""}}
                    @else
                        {{$aData->contact_name}}
                    @endif
                    </div>
                </td>
                <?php
                if($aData->current_status=="Paid"){
                    echo '<td width="10%"><div class="text-left"><i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;" ></i>'.$aData->current_status.'</div></td>';
                }else if($aData->current_status=="Partial"){
                    echo '<td width="10%"><div class="text-left"><i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'.$aData->current_status.'</div></td>';
                }else if($aData->current_status=="Overdue"){
                    echo '<td width="10%"><div class="text-left"><i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'.$aData->current_status.'</div></td>';
                }else{
                    echo '<td width="10%"><div class="text-left">'.$aData->current_status.'</div></td>';
                }

                $editOption= '<a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice" data-toggle="modal"  data-target="#editFundRequest" data-placement="bottom" href="javascript:;"  onclick="editFundRequest('.$aData->id.');"> <i class="fas fa-pen" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"></i> </a>';
                
                if($aData->status != 'paid') {
                    if($aData->deposit_into_type == 'credit'){
                        $editOption.= '<a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice" data-toggle="modal" data-placement="bottom" href="javascript:;"  data-target="#depositIntoNonTrustAccount"  onclick="depositIntoNonTrustAccount('.$aData->client_id.','.$aData->id.');"><i class="fas fa-dollar-sign" data-toggle="tooltip" data-placement="top" title="" data-original-title="Record Payment"></i></a>';
                    }
                    if($aData->deposit_into_type == 'trust'){
                        $editOption.= '<a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice" data-toggle="modal" data-placement="bottom" href="javascript:;"  data-target="#depositIntoTrustAccount"  onclick="depositIntoTrustPopup('.$aData->client_id.',0,'.$aData->id.');"><i class="fas fa-dollar-sign" data-toggle="tooltip" data-placement="top" title="" data-original-title="Record Payment"></i></a>';
                    }

                    $editOption.= '<a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice" data-toggle="modal"  data-target="#sendFundReminder" data-placement="bottom" href="javascript:;"  onclick="sendFundReminder('.$aData->id.');"> <i class="fas fa-bell" data-toggle="tooltip" data-placement="top" title="" data-original-title="Reminders"></i> </a>';
                    
                    if($aData->amount_paid == 0) {
                        $editOption.= '<a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice" data-toggle="modal" data-placement="bottom" href="javascript:;"  onclick="deleteRequestFund('.$aData->id.', this);" data-payment-count="'.$aData->fund_payment_history_count.'"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"></i></a>';
                    }
                }
                echo '<td width="20%"><div class="d-flex align-items-center float-right">'.$editOption.'</div></td>';
                ?>                
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{-- <div id="deleteInvoicePopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteInvoiceForm" id="deleteInvoiceForm" name="deleteInvoiceForm" method="POST">
            @csrf
            <input type="hidden" value="" name="invoiceId" id="delete_invoice_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <p>Are you sure you want to delete this Invoice?</p>
                            <ul>
                                <li>This action can’t be undone</li>
                                <li>Time entries, expenses and flat fee entries will be put back into the system in their "Open" state.</li>
                                <li>All payments and refunds made against the invoice WILL BE DELETED. If trust funds were used to pay the invoice, those funds will be returned to the client's trust fund. Other funds with different origin will be sent to the client's trust fund as well.</li>
                                <li>Firm users working on the case will receive a notification</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div> --}}

@include('billing.invoices.partials.delete_invoice_modal')

<?php } ?>
@include('client_dashboard.billing.modal')
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\client\fundrequest.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script src="{{ asset('assets\js\custom\invoice\deleteInvoice.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#actionbutton").trigger("click");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
</script>
@endsection