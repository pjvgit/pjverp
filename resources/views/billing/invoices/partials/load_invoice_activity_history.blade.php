<?php if(!empty($lastEntry)){?>
    <table class="table table-striped table-hover table-borderless mb-0 single-bill-invoice-history nowrap">
        <tbody>
            <?php 
                    $value=$lastEntry;
                    $depositInto=$notes=$print=$refund='';
                    if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Operating Account"){
                            $depositInto="Operating (Operating Account)";
                        } else  if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Trust Account"){  
                            $depositInto= "Trust (Trust Account)";
                        }else{
                            $depositInto="<i class='table-cell-placeholder'></i>";
                        } 
                        
                        if($value->acrtivity_title=="Payment Received" && $value->notes==NULL){
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Payment Notes</a>';
                        }else if($value->acrtivity_title=="Payment Received" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes==NULL){
                            $noteText = "Refund of Cash on ".date('m/d/Y', strtotime($value->added_date))." (original amount: $".(number_format($value->amount,2)).")";
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="'.$noteText.'" data-original-title="Dismissible popover">View Refund Notes</a>';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="balance forwarded" && $value->notes!=NULL){
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
                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>|<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                        }else if($value->acrtivity_title=="Payment Received" && $value->status=2){
                            $refund='';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->status==4 || $value->status==2){
                            $refund='<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                        }else{
                            $refund='';
                        }


                    ?>
                    <tr id="" class="invoice-history-row nowrap">
                        <td class="first_child invoice-history-row-type nowrap">
                            <?php if($value->acrtivity_title=="Unshared w/Contacts"){?>
                                <span class="bill-history-indicator status_indicator_red"></span>
                            <?php }else if($value->acrtivity_title=="Payment Received"){?>
                                <span class="bill-history-indicator status_indicator_green"></span>
                            <?php }else if($value->acrtivity_title=="Payment Refund"){?>
                                <span class="bill-history-indicator status_indicator_yellow"></span>
                            <?php }else{
                                ?> <span class="bill-history-indicator"></span>
                            <?php } ?>
                            {{ ucfirst($value->acrtivity_title) }}
                        </td>
                        <td class="invoice-history-row-date">
                            {{$value->added_date}}
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
                        </td>
                        <td class="invoice-history-row-amount">
                            @if($value->acrtivity_title=="Payment Received")
                                @if($value->refund_amount!=NULL)
                                    ${{$value->refund_amount}}
                                @else
                                    ${{number_format($value->amount,2)}}
                                @endif
                            @elseif($value->acrtivity_title=="Payment Refund")
                                (${{number_format($value->amount,2)}})
                            @elseif($value->amount)
                                ${{number_format($value->amount,2)}}
                            @else
                            <i class="table-cell-placeholder"></i> 
                            @endif
                        </td>
                        <td class="invoice-history-row-user">
                            <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($value->responsible['id'])}}">
                                {{substr($value->responsible['cname'],0,100)}}
                                ({{$value->responsible['user_title']}})</a>
                        </td>
                        <td class="invoice-history-row-deposited-into">
                           <?php echo $depositInto;?>
                        </td>
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
                <th>Deposited Into</th>
                <th>Notes</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                    $depositInto=$notes=$print=$refund='';
                    foreach ($InvoiceHistory as $key => $value) {
                        if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Operating Account"){
                            $depositInto="Operating (Operating Account)";
                        } else  if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Trust Account"){  
                            $depositInto= "Trust (Trust Account)";
                        }else{
                            $depositInto="<i class='table-cell-placeholder'></i>";
                        } 
                        
                        if($value->acrtivity_title=="Payment Received" && $value->notes==NULL){
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Payment Notes</a>';
                        }else if($value->acrtivity_title=="Payment Received" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes==NULL){
                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Refund Notes</a>';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes!=NULL){
                            $notes=$value->notes;
                        }else if($value->acrtivity_title=="balance forwarded" && $value->notes!=NULL){
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
                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>|<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                        }else if($value->acrtivity_title=="Payment Received" && $value->status=2){
                            $refund='';
                        }else if($value->acrtivity_title=="Payment Refund" && $value->status==4 || $value->status==2){
                            $refund='<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                        }else{
                            $refund='';
                        }


                    ?>
                    <tr id="" class="invoice-history-row nowrap">
                        <td class="first_child invoice-history-row-type">
                            <?php if($value->acrtivity_title=="Unshared w/Contacts"){?>
                                <span class="bill-history-indicator status_indicator_red"></span>
                            <?php }else if($value->acrtivity_title=="Payment Received"){?>
                                <span class="bill-history-indicator status_indicator_green"></span>
                            <?php }else if($value->acrtivity_title=="Payment Refund"){?>
                                <span class="bill-history-indicator status_indicator_yellow"></span>
                            <?php }else{
                                ?> <span class="bill-history-indicator"></span>
                            <?php } ?>
                            {{ ucfirst($value->acrtivity_title) }}
                        </td>
                        <td class="invoice-history-row-date">
                            {{$value->added_date}}
                        </td>
                        <td class="invoice-history-row-pay-type">
                            <?php 
                            $Displayval='<i class="table-cell-placeholder"></i>';
                            
                            if($value->pay_method!=''){
                                    $Displayval=$value->pay_method;
                            }
                            echo $Displayval;
                            if($value->refund_amount!=NULL){
                                echo " (Refunded)";
                            }
                            ?>
                        </td>
                        <td class="invoice-history-row-amount">
                            
                            @if($value->acrtivity_title=="Payment Received")
                                @if($value->refund_amount!=NULL)
                                    ${{$value->refund_amount}}
                                @else
                                    ${{number_format($value->amount,2)}}
                                @endif
                            @elseif($value->acrtivity_title=="Payment Refund")
                                (${{number_format($value->amount,2)}})
                            @elseif($value->amount)
                                ${{number_format($value->amount,2)}}
                            @else
                            <i class="table-cell-placeholder"></i> 
                            @endif
                        </td>
                        <td class="invoice-history-row-user">
                            <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($value->responsible['id'])}}">
                                {{substr($value->responsible['cname'],0,100)}}
                                ({{$value->responsible['user_title']}})</a>
                        </td>
                        <td class="invoice-history-row-deposited-into">
                           <?php echo $depositInto;?>
                        </td>
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
</script>