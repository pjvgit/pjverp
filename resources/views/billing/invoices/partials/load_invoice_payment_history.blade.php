@if(!empty($InvoiceHistoryTransaction) && count($InvoiceHistoryTransaction))
    @php
        $paymentHistory = $InvoiceHistoryTransaction->whereIn("acrtivity_title", ["Payment Received","Payment Refund"]);
    @endphp
    <h3> Payment History</h3>
    <table style="width: 100%; border-collapse: collapse;" class="payment_history">
        <tbody>
            <tr class="invoice_info_row invoice_header_row invoice-table-row">
                <td class="invoice_info_bg" style="width: 12%;">Activity</td>
                <td class="invoice_info_bg" style="width: 10%;">Date</td>
                <td class="invoice_info_bg" style="width: 33%;">Payment Method</td>
                <td class="invoice_info_bg" style="width: 12%;">Amount</td>
                <td class="invoice_info_bg" style="width: 15%;">Responsible User</td>
                <td class="invoice_info_bg" style="width: 18%;">Deposited Into</td>
            </tr>
        @forelse ($paymentHistory as $hKey=>$hVal)
            <tr class="invoice_info_row invoice-table-row">
            <td class="payment-history-column-activity " style="vertical-align: top;">{{$hVal->acrtivity_title}}</td>
            <td class="payment-history-column-formatted-date" style="vertical-align: top;">{{$hVal->added_date}}</td>
            <td class="payment-history-column-pay-method" style="vertical-align: top;">
                {{ $hVal->pay_method }} {{ (in_array($hVal->status, [2, 3])) ? '(Refunded)' : '' }}
            </td>
            <td class="payment-history-column-amount" style="vertical-align: top;">
                @if(in_array($hVal->acrtivity_title, ["Payment Received","Payment Pending"]))
                    ${{number_format($hVal->amount,2)}}
                @elseif($hVal->acrtivity_title=="Payment Refund")
                    (${{number_format($hVal->amount,2)}})
                @endif
            </td>
            <td class="payment-history-column-user" style="vertical-align: top;">
                {{substr($hVal->responsible['cname'],0,100)}} ({{$hVal->responsible['user_title']}})
            </td>
            <td class="payment-history-column-deposited-into" style="vertical-align: top;">
                @if($hVal->acrtivity_title=="Payment Received" && in_array($hVal->payment_from, ['online','client_online']))
                    Operating Account
                @elseif($hVal->acrtivity_title=="Payment Received" && $hVal->pay_method != 'Non-Trust Credit Account')
                    {{ $hVal->deposit_into }}
                @elseif($hVal->acrtivity_title=="Payment Refund" && $hVal->pay_method == 'Trust')
                    {{ $hVal->deposit_into }}
                @else
                    Operating Account
                @endif
            </td>
            </tr>
        @empty
        @endforelse
        </tbody>
    </table>

    @include('billing.invoices.partials.load_invoice_pending_payment_history')
@endif