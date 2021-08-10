@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
	<section id="payables_view">
		<h1 class="primary-heading">Unpaid Invoices &amp; Funds Requests</h1>
        @if(!empty($invoices))
        <ul class="list-group">
            @forelse ($invoices as $key => $item)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('client/bills/detail', $item->decode_id) }}" class="col-8 col-md-10">
                        <span class="payable-row__icon payable-row__icon-unpaid"><i class="fas fa-dollar-sign"></i></span>
                        <div class="list-row__body">
                            <span class="list-row__header mt-0">${{ $item->due_amount_new }}</span><br>
                            <span class="list-row__header-detail">Aug 10, 2021 - Inv. #{{ $item->invoice_id }}</span>
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
		</ul>
        @else
        <div class="text-center p-4"><i>No Invoices or Funds Requests</i></div>
        @endif
		<h1 class="primary-heading">Billing History</h1>
		<div class="text-center p-4"><i>No Invoices or Funds Requests</i></div>
	</section>
	<div></div>
</div>

@endsection