@if(!empty($InvoiceHistoryTransaction) && count($InvoiceHistoryTransaction))
    @php
        $pendingPayments = $InvoiceHistoryTransaction->whereIn("acrtivity_title", ["Awaiting Online Payment","Payment Pending"]);
    @endphp
    @if (count($pendingPayments))
    <h3 class="mt-20"> Online Payments Pending </h3>
    <table class="payment_history" style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none;float:right; border-bottom: none;border-top: none;margin-bottom:10px;" border="1">
        <tbody><tr class="invoice_info_row invoice_header_row invoice-table-row">
            <td class="invoice_info_bg" style="width: 12%;">Activity</td>
            <td class="invoice_info_bg" style="width: 10%;">Date</td>
            <td class="invoice_info_bg" style="width: 33%;">Payment Method</td>
            <td class="invoice_info_bg" style="width: 12%;">Amount</td>
            <td class="invoice_info_bg" style="width: 15%;">Responsible User</td>
        </tr>
        @forelse ($pendingPayments as $hKey=>$hVal)
            <tr class="invoice_info_row invoice-table-row">
                <td class="payment-history-column-activity " style="vertical-align: top;">{{$hVal->acrtivity_title}}</td>
                <td class="payment-history-column-formatted-date" style="vertical-align: top;">{{$hVal->added_date}}</td>
                <td class="payment-history-column-pay-method" style="vertical-align: top;">{{ $hVal->pay_method }}</td>
                <td class="payment-history-column-amount" style="vertical-align: top;">${{number_format($hVal->amount,2)}}</td>
                <td class="payment-history-column-user" style="vertical-align: top;">
                    {{substr($hVal->createdByUser->full_name,0,100)}} ({{$hVal->createdByUser->user_title}})
                </td>
            </tr>
        @empty
        @endforelse
        </tbody>
    </table>
    @endif
@endif