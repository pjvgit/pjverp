@if(isset($InvoiceHistoryTransaction) && !empty($InvoiceHistoryTransaction))
    <h3> Payment History</h3>
    <table style="width: 100%; border-collapse: collapse;" class="payment_history">
        <tbody><tr class="invoice_info_row invoice_header_row invoice-table-row">
            <td class="invoice_info_bg" style="width: 12%;">
            Activity
            </td>
            <td class="invoice_info_bg" style="width: 10%;">
            Date
            </td>
            <td class="invoice_info_bg" style="width: 33%;">
            Payment Method
            </td>
            <td class="invoice_info_bg" style="width: 12%;">
            Amount
            </td>
            <td class="invoice_info_bg" style="width: 15%;">
            Responsible User
            </td>
            <td class="invoice_info_bg" style="width: 18%;">
            Deposited Into
            </td>
        </tr>
        <?php  foreach($InvoiceHistoryTransaction as $hKey=>$hVal){
            if(in_array($hVal->acrtivity_title,["Payment Received","Payment Refund"])){ ?>
            <tr class="invoice_info_row invoice-table-row">
            <td class="payment-history-column-activity " style="vertical-align: top;">
                {{$hVal->acrtivity_title}}
            </td>
            <td class="payment-history-column-formatted-date" style="vertical-align: top;">
                {{$hVal->added_date}}
            </td>
            <td class="payment-history-column-pay-method" style="vertical-align: top;">
                {{$hVal->pay_method}}
            </td>
            <td class="payment-history-column-amount" style="vertical-align: top;">
                <?php if($hVal->acrtivity_title=="Payment Received"){?>
                    ${{number_format($hVal->amount,2)}}
                    <?php }else if($hVal->acrtivity_title=="Payment Refund"){?>
                        (${{number_format($hVal->amount,2)}})
                    <?php } ?>
            </td>
            <td class="payment-history-column-user" style="vertical-align: top;">
                {{substr($hVal->responsible['cname'],0,100)}}
                ({{$hVal->responsible['user_title']}})
            </td>
            <td class="payment-history-column-deposited-into" style="vertical-align: top;">
                <?php if($hVal->acrtivity_title=="Payment Received"){
                    echo $hVal->deposit_into;
                    } ?>
            </td>
            </tr>
            <?php } 
            } ?>
        </tbody>
    </table>
@endif