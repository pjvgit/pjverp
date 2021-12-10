@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
	<section id="payables_view">
        @component('client_portal.component.alert')@endcomponent
		<h1 class="primary-heading">Unpaid Invoices &amp; Funds Requests</h1>
        <ul class="list-group">
            @if(count($invoices) || count($requestFunds))
                @forelse ($invoices as $key => $item)
                    <li class="payable list-row no-gutters @if($item->is_viewed == 'no') is-unread @endif">
                        <a href="{{ route('client/bills/detail', $item->decode_id) }}" class="col-8 col-md-10 billing-list">
                            {{-- <span class="payable-row__icon payable-row__icon-unpaid"><i class="fas fa-dollar-sign"></i></span> --}}
                            <img src="{{ asset('icon/dollar-green.png') }}" class="green-dollar"/>
                            <div class="list-row__body">
                                <span class="list-row__header mt-0">${{ $item->due_amount_new }}</span><br>
                                <span class="list-row__header-detail">{{ convertUTCToUserDate($item->invoice_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') }} - Inv. #{{ $item->invoice_id }}</span>
                            </div>
                        </a>
                        <div class="col-4 col-md-2 text-right">
                            <span class="list-row__alert-text">{{ getDueText($item->due_date) }}</span>
                            <a class="btn btn-primary payable-detail__export-link ml-5" href="{{ route('client/bills/payment', ['invoice_id'=>encodeDecodeId($item->id, 'encode'), 'client_id'=>encodeDecodeId(auth()->id(), 'encode')]) }}" target="_blank">
                                <span class="payable-detail__export-button">Pay Now</span>
                            </a>
                        </div>
                    </li>
                @empty
                @endforelse
                @forelse ($requestFunds as $key => $item)
                    <li class="payable list-row no-gutters @if($item->is_viewed == 'no') is-unread @endif">
                        <a href="{{ route('client/bills/request/detail', base64_encode($item->id)) }}" class="col-8 col-md-10 billing-list">
                            {{-- <span class="payable-row__icon payable-row__icon-unpaid"><i class="fas fa-dollar-sign"></i></span> --}}
                            <img src="{{ asset('icon/dollar-green.png') }}" class="green-dollar"/>
                            <div class="list-row__body">
                                <span class="list-row__header mt-0">${{ $item->amt_due }}</span><br>
                                <span class="list-row__header-detail">{{ $item->send_date_format }} - {{ $item->padding_id }}</span>
                            </div>
                        </a>
                        <div class="col-4 col-md-2 text-right">
                            @php
                            if(!$item->due_date) {
                                $dueText = "No Due Date";
                            } else {
                                $dueDate = \Carbon\Carbon::parse($item->due_date);
                                $currentDate = \Carbon\Carbon::now();
                                $difference = $currentDate->diff($dueDate)->days;
                                if($dueDate->isToday()) {
                                    $dueText = "DUE TODAY";
                                } else if($dueDate->isTomorrow()) {
                                    $dueText = "DUE TOMORROW";
                                } else if($difference > 1) {
                                    $dueText = "DUE IN ".$difference." DAYS";
                                } else if($dueDate->lt($currentDate)) {
                                    $dueText = "OVERDUE";
                                } else {
                                    $dueText = "";
                                }
                            }
                            @endphp
                            <span class="list-row__alert-text">{{ $dueText }}</span>
                            <br>
                        </div>
                    </li>
                @empty
                @endforelse
            @else
            <div class="text-center p-4"><i>No Invoices or Funds Requests</i></div>
            @endif
		</ul>
        
		<h1 class="primary-heading">Billing History</h1>
        @if(count($forwardedInvoices) || count($requestFundsHistory))
            @forelse ($forwardedInvoices as $key => $item)
                <ul class="list" id="paid_payables">
                    @if($item->status == "Paid")
                        <li class="payable list-row no-gutters @if($item->is_viewed == 'no') is-unread @endif">
                            <a href="{{ route('client/bills/detail', $item->decode_id) }}" class="col-8 col-md-10">
                                {{-- <span class="payable-row__icon payable-row__icon-paid"><i class="fas fa-dollar-sign"></i></span> --}}
                                <img src="{{ asset('icon/dollar-green.png') }}" class="green-dollar"/>
                                <div class="list-row__body">
                                    <span class="list-row__header mt-0">${{ $item->total_amount_new }}</span><br>
                                    <span class="list-row__header-detail">{{ convertUTCToUserDate($item->invoice_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') }} - Inv. #{{ $item->invoice_id }}</span>
                                </div>
                            </a>
                            <div class="col-4 col-md-2 text-right">
                                <div class="list-row__body text-right">
                                    <div class="payable-row__payment">Paid {{ ($item->invoiceLastPayment) ? convertUTCToUserDate($item->invoiceLastPayment->payment_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') : '' }}</div>
                                </div>
                            </div>
                        </li>
                    @else
                        <li class="payable list-row no-gutters ">
                            <a href="{{ route('client/bills/detail', $item->decode_id) }}" class="col-8 col-md-10">
                                {{-- <span class="payable-row__icon payable-row__icon-paid"><i class="fas fa-dollar-sign"></i></span> --}}
                                <img src="{{ asset('icon/dollar-green.png') }}" class="green-dollar"/>
                                <div class="list-row__body"><span class="list-row__header mt-0">${{ $item->due_amount_new }}</span><br>
                                    <span class="list-row__header-detail">{{ convertUTCToUserDate($item->invoice_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') }} - Inv. #{{ $item->invoice_id }}</span>
                                </div>
                            </a>
                            <div class="col-4 col-md-2 text-right">
                                <div class="list-row__body text-right">
                                    <div class="payable-row__payment">Forwarded to #{{ @$item->invoiceForwardedToInvoice[0]->invoice_id }}</div>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            @empty
            @endforelse
            @forelse ($requestFundsHistory as $key => $item)
                <ul class="list" id="paid_payables">
                    <li class="payable list-row no-gutters @if($item->is_viewed == 'no') is-unread @endif">
                        <a href="{{ route('client/bills/request/detail', base64_encode($item->id)) }}" class="col-8 col-md-10">
                            {{-- <span class="payable-row__icon payable-row__icon-paid"><i class="fas fa-dollar-sign"></i></span> --}}
                            <img src="{{ asset('icon/dollar-green.png') }}" class="green-dollar"/>
                            <div class="list-row__body">
                                <span class="list-row__header mt-0">${{ $item->amt_requested }}</span><br>
                                <span class="list-row__header-detail">{{ $item->send_date_format }} - {{ $item->padding_id }}</span>
                            </div>
                        </a>
                        <div class="col-4 col-md-2 text-right">
                            <div class="list-row__body text-right">
                                <div class="payable-row__payment">Paid {{ @$item->payment_at }}</div>
                            </div>
                        </div>
                    </li>
                </ul>
            @empty
            @endforelse
        @else
            <div class="text-center p-4"><i>No Invoices or Funds Requests</i></div>
        @endif
	</section>
</div>

@endsection