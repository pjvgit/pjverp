<?php if(!empty($lastEntry)){?>
    <table class="table table-striped table-hover table-borderless mb-0 single-bill-invoice-history nowrap">
        <tbody>
            <?php 
                    $value=$lastEntry;
                    $depositInto=$notes=$print=$refund='';
                        /* if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Operating Account"){
                            $depositInto="Operating Account";
                        } else  if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Trust Account"){  
                            $depositInto= "Trust (Trust Account)";
                        }else{
                            $depositInto="<i class='table-cell-placeholder'></i>";
                        }  */
                        $onlineFullOverPaymentNote = "";
                        $onlinePartiallyOverPaymentNote = "";
                        if($value->acrtivity_title=="Payment Received" && $value->status == "5"){
                            $notes = __('billing.invoice_overpaid_note');
                            $notes .= $value->notes;
                        } else if($value->acrtivity_title=="Payment Received" && $value->status == "6"){
                            $notes = __('billing.invoice_partially_overpaid_note');
                            $notes .= $value->notes;
                        } else if($value->acrtivity_title=="Payment Received" && $value->notes==NULL){
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Payment Notes</a>';
                        }else if($value->acrtivity_title=="Payment Received" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes==NULL){
                            $noteText = "Refund of Cash on ".date('m/d/Y', strtotime($value->added_date))." (original amount: $".(number_format($value->amount,2)).")";
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="'.$noteText.'" data-original-title="Dismissible popover">View Refund Notes</a>';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="balance forwarded" && $value->notes!=NULL){
                            // $notes = 'Forwarded to <a href="'.route('bills/invoices/view', @$findInvoice->invoiceForwardedToInvoice[0]->decode_id).'">'.@$findInvoice->invoiceForwardedToInvoice[0]->invoice_id.'</a>';
                            $afterToStr = trim(substr($value->notes, strpos($value->notes, 'to') + strlen('to')));
                            $invId = substr(@$afterToStr, strrpos(@$afterToStr, '0') + 1);
                            if($invId)
                                $notes = 'Forwarded to <a href="'.route('bills/invoices/view', base64_encode($invId)).'">#'.@$afterToStr.'</a>';
                            else
                                $notes = 'Forwarded to <a href="#">#'.@$afterToStr.'</a>';
                        }else if($value->acrtivity_title=="invoice reopened" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="Emailed Invoice" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->notes!=NULL){
                            $notes=$value->notes;
                        }else{
                            $notes="<i class='table-cell-placeholder'></i>";
                        }  


                        if(in_array($value->acrtivity_title,["Payment Received","Payment Refund"])){
                            $print='<a href="javascript:void(0);" onclick="PrintTransaction('.$value->id.');"  ><i class="fas fa-print test-print-bill" id="print-bill-button" data-toggle="tooltip" data-original-title="Print"></i></a>';
                        }else{
                            $print="";
                        }  
                        if($value->acrtivity_title=="Payment Received" && $value->status==1){
                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>';
                            if(!in_array($value->pay_method, ["Card","Oxxo Cash","SPEI"]) && !in_array($value->payment_from, ["online","client_online"])) {
                                $refund .= '|<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                            }
                        }else if($value->acrtivity_title=="Payment Received" && $value->status==2){
                            $refund='';
                        }else if($value->acrtivity_title=="Payment Received" && $value->status==6){
                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->status==4 || $value->status==2){
                            if($value->online_payment_status == "partially_refunded" && $value->payment_from == "online") {
                                $refund = '<span class="tooltip-wrapper" style="position: relative;"><span>
                                    <span data-toggle="tooltip" data-placement="top" title="Credit cards refund cannot be deleted." data-html="true">
                                        <i class="pl-1 fas fa-question-circle fa-lg"></i></span>
                                    </span>
                                </span>';
                            } else {
                                $refund='<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                            }
                        }else{
                            $refund='';
                        }


                    ?>
                    <tr id="" class="invoice-history-row nowrap">
                        <td class="first_child invoice-history-row-type nowrap">
                            @if($value->acrtivity_title=="Unshared w/Contacts")
                                <span class="bill-history-indicator status_indicator_red"></span>
                            @elseif($value->acrtivity_title=="Payment Received")
                                <span class="bill-history-indicator status_indicator_green"></span>
                            @elseif($value->acrtivity_title=="Payment Refund")
                                <span class="bill-history-indicator status_indicator_yellow"></span>
                            @elseif($value->acrtivity_title == "Sent Reminder")
                                <span class="bill-history-indicator status_indicator_light_blue"></span>
                            @elseif($value->acrtivity_title=="Payment Pending")
                                <span class="bill-history-indicator status_indicator_orange"></span>
                            @else
                                <span class="bill-history-indicator"></span>
                            @endif
                            {{ ucfirst($value->acrtivity_title) }}
                        </td>
                        <td class="invoice-history-row-date">
                            {{ ($value->invoicePayment) ? date('M j, Y', strtotime($value->invoicePayment->payment_date)) : $value->added_date}}
                        </td>
                        <td class="invoice-history-row-pay-type">
                            <?php  $Displayval='<i class="table-cell-placeholder"></i>';
                            if($value->pay_method!=''){
                                    $Displayval=$value->pay_method;
                            }
                            echo $Displayval;
                            if($value->refund_amount!=NULL){
                                echo " (Refunded)";
                            }
                            ?>
                            {{ ($value->status == '0' && $value->online_payment_status == "pending") ? " (Payment Pending)" : '' }}
                        </td>
                        <td class="invoice-history-row-amount">
                            @if($value->acrtivity_title=="Payment Refund")
                                (${{number_format($value->amount,2)}})
                            @elseif($value->amount)
                                ${{number_format($value->amount,2)}}
                            @else
                                <i class="table-cell-placeholder"></i> 
                            @endif
                        </td>
                        <td class="invoice-history-row-user">
                            <a href="{{ $value->createdByUser->user_route_link }}">
                                {{substr($value->createdByUser->full_name,0,100)}}
                                ({{$value->createdByUser->user_title}})</a>
                        </td>
                        {{-- <td class="invoice-history-row-deposited-into">
                           <?php echo $depositInto;?>
                        </td> --}}
                        <td style="overflow: visible;" class="invoice-history-row-notes">
                            <div style="position: relative;">
                                <?php echo $notes;?>
                            </div>
                        </td>

                        <td class="invoice-history-row-print" style="text-align: center;">
                            <?php echo $print;?>
                        </td>

                        <td class="invoice-history-actions last_child"
                            style="text-align: right; white-space: nowrap;">
                            <?php echo $refund;?>
                        </td>
                    </tr>

        </tbody>
    </table>
    <?php } ?>
    <?php 
        if(!$InvoiceHistory->isEmpty()){?>
    <table class="table table-striped table-hover table-borderless mb-0 bill-invoice-history">
        <thead class="collapsible-invoice-history-row">
            <tr>
                <th>Activity</th>
                <th>Date</th>
                <th>Pay Method</th>
                <th>Amount</th>
                <th>Responsible User</th>
                {{-- <th>Deposited Into</th> --}}
                <th>Notes</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                    $depositInto=$notes=$print=$refund='';
                    foreach ($InvoiceHistory as $key => $value) {
                        /* if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Operating Account"){
                            $depositInto="Operating Account";
                        } else  if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Trust Account"){  
                            $depositInto= "Trust (Trust Account)";
                        }else{
                            $depositInto="<i class='table-cell-placeholder'></i>";
                        } */ 
                        
                        if($value->acrtivity_title=="Payment Received" && $value->status == "5"){
                            $notes = __('billing.invoice_overpaid_note');
                            $notes .= $value->notes;
                        } else if($value->acrtivity_title=="Payment Received" && $value->status == "6"){
                            $notes = __('billing.invoice_partially_overpaid_note');
                            $notes .= $value->notes;
                        } else if($value->acrtivity_title=="Payment Received" && $value->notes==NULL){
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Payment Notes</a>';
                        }else if($value->acrtivity_title=="Payment Received" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes==NULL){
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Refund Notes</a>';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="balance forwarded" && $value->notes!=NULL){
                            $afterToStr = trim(substr($value->notes, strpos($value->notes, 'to') + strlen('to')));
                            $invId = substr(@$afterToStr, strrpos(@$afterToStr, '0') + 1);
                            if($invId)
                                $notes = 'Forwarded to <a href="'.route('bills/invoices/view', base64_encode($invId)).'">#'.@$afterToStr.'</a>';
                            else
                                $notes = 'Forwarded to <a href="#">#'.@$afterToStr.'</a>';
                        }else if($value->acrtivity_title=="invoice reopened" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="Emailed Invoice" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->notes!=NULL){
                            $notes=$value->notes;
                        }else{
                            $notes="<i class='table-cell-placeholder'></i>";
                        }  


                        if(in_array($value->acrtivity_title,["Payment Received","Payment Refund"])){
                            $print='<a href="javascript:void(0);" onclick="PrintTransaction('.$value->id.');"  ><i class="fas fa-print test-print-bill" id="print-bill-button" data-toggle="tooltip" data-original-title="Print"></i></a>';
                        }else{
                            $print="";
                        }  
                        if($value->acrtivity_title=="Payment Received" && $value->status==1){
                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>';
                            if(!in_array($value->pay_method, ["Card","Oxxo Cash","SPEI"]) && !in_array($value->payment_from, ["online","client_online"])) {
                                $refund .= '|<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                            }
                        }else if($value->acrtivity_title=="Payment Received" && $value->status==2){
                            $refund='';
                        }else if($value->acrtivity_title=="Payment Received" && $value->status==6){
                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->status==4 || $value->status==2){
                            if($value->online_payment_status == "partially_refunded" && $value->payment_from == "online") {
                                $refund = '<span class="tooltip-wrapper text-center" style="position: relative;"><span>
                                    <span data-toggle="tooltip" data-placement="top" title="Credit cards refund cannot be deleted." data-html="true">
                                        <i class="pl-1 fas fa-question-circle fa-lg"></i></span>
                                    </span>
                                </span>';
                            } else {
                            $refund='<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                            }
                        }else{
                            $refund='';
                        }


                    ?>
                    <tr id="inv_{{$value->id}}" data-id="inv_{{$value->invoice_id}}" class="invoice-history-row nowrap">
                        <td class="first_child invoice-history-row-type">
                            @if($value->acrtivity_title=="Unshared w/Contacts")
                                <span class="bill-history-indicator status_indicator_red"></span>
                            @elseif($value->acrtivity_title=="Payment Received")
                                <span class="bill-history-indicator status_indicator_green"></span>
                            @elseif($value->acrtivity_title=="Payment Refund")
                                <span class="bill-history-indicator status_indicator_yellow"></span>
                            @elseif($value->acrtivity_title == "Sent Reminder")
                                <span class="bill-history-indicator status_indicator_light_blue"></span>
                            @elseif($value->acrtivity_title=="Payment Pending")
                                <span class="bill-history-indicator status_indicator_orange"></span>
                            @else
                                <span class="bill-history-indicator"></span>
                            @endif
                            {{ ucfirst($value->acrtivity_title) }}
                        </td>
                        <td class="invoice-history-row-date">
                            {{ ($value->invoicePayment) ? date('M j, Y', strtotime($value->invoicePayment->payment_date)) : $value->added_date}}
                        </td>
                        <td class="invoice-history-row-pay-type">
                            <?php 
                            $Displayval='<i class="table-cell-placeholder"></i>';
                            
                            if($value->pay_method!=''){
                                    $Displayval=$value->pay_method;
                            }
                            if($value->status == 0 && $value->online_payment_status == "pending_payment") {
                                $Displayval .= " (Payment Pending)";
                            }
                            echo $Displayval;
                            if($value->refund_amount!=NULL){
                                echo " (Refunded)";
                            }
                            ?>
                        </td>
                        <td class="invoice-history-row-amount">
                            @if($value->acrtivity_title=="Payment Refund")
                                (${{number_format($value->amount,2)}})
                            @elseif($value->amount && $value->acrtivity_title !="Payment Refund")
                                ${{number_format($value->amount,2)}}
                            @else
                                <i class="table-cell-placeholder"></i> 
                            @endif
                        </td>
                        <td class="invoice-history-row-user">
                            <a href="{{ $value->createdByUser->user_route_link }}">
                                {{substr($value->createdByUser->full_name,0,100)}}
                                ({{$value->createdByUser->user_title}})</a>
                        </td>
                        {{-- <td class="invoice-history-row-deposited-into">
                           <?php echo $depositInto;?>
                        </td> --}}
                        <td style="overflow: visible;" class="invoice-history-row-notes">
                            <div style="position: relative;">
                                <?php echo $notes;?>
                            </div>
                        </td>

                        <td class="invoice-history-row-print" style="text-align: center;">
                            <?php echo $print;?>
                        </td>

                        <td class="invoice-history-actions last_child"
                            style="text-align: right; white-space: nowrap;">
                            <?php echo $refund;?>
                        </td>
                    </tr>
            <?php 
                } ?>

        </tbody>
    </table>
    <?php 
    } ?>

<script>
$(document).ready(function () {   
$('[data-toggle="tooltip"]').tooltip();

$(".show-history-btn").click(function () {
    $(".bill-invoice-history").show();
    $(".close-history-btn").show();
    $(".show-history-btn").hide();
    $(".single-bill-invoice-history").hide();

});
$(".close-history-btn").click(function () {
    $(".bill-invoice-history").hide();
    $(".close-history-btn").hide();
    $(".show-history-btn").show();
    $(".single-bill-invoice-history").show();
});


$(".single-bill-invoice-history").show();
$(".bill-invoice-history").hide();
$(".close-history-btn").hide();
$(".show-history-btn").show();
});
</script>