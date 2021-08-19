@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
	@if ($message = Session::get('error'))
	<div class="alert alert-danger alert-block">
		<button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
		<strong>{{ $message }}</strong>
	</div>
	@endif
	<section class="detail-view" id="payable_detail_view">
		<div class="payable-detail ">
			<div class="detail-view__header">
				<div>Invoice #{{ $invoice->invoice_id }}</div>
				<div class="payable-detail__actions">
					<a class="btn btn-primary payable-detail__export-link" href="{{ route('client/bills/invoices/download', $invoice->decode_id) }}" target="_blank">
						{{-- <i class="fa fa-download" aria-hidden="true"></i> --}}
						<span class="payable-detail__export-button">View Full Invoice (PDF)</span>
					</a>
				</div>
			</div>
			<div class="mb-3 mb-md-0">
				<div class="p-3">
					@if($invoice->status == "Paid")
					<div class="text-center">
						<div class="detail-view__label mb-2">Paid</div>
						<h2><i class="fas fa-check-circle payable-detail__settled"></i>${{ $invoice->total_amount_new }}</h2>
					</div>
					@endif
					<div class="payable-detail__summary">
						<div class="payable-detail__summary__left-panel"><b>{{ @$invoice->case->caseBillingClient[0]->full_name }}</b>
							<div class="payable-detail__address-text">{{ @$invoice->case->caseBillingClient[0]->full_address}}</div>
						</div>
						<div class="payable-detail__summary__right-panel">
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Status</div>
								<div>{{ $invoice->status }}</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Total Balance Due</div>
								<div class="payable-detail__total-balance-due">${{ $invoice->due_amount_new }}</div>
							</div>
							@if(!empty($invoice->invoiceFirstInstallment))
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Next Payment Due</div>
								<div class="payable-detail__total-balance-due">${{ number_format(($invoice->invoiceFirstInstallment->installment_amount - $invoice->invoiceFirstInstallment->adjustment), 2) }}</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Next Payment Date</div>
								<div>{{ convertUTCToUserDate($invoice->invoiceFirstInstallment->due_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') }}</div>
							</div>
							@endif
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Invoice Date</div>
								<div>{{ convertUTCToUserDate($invoice->invoice_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') }}</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Due Date</div>
								<div>{{ ($invoice->invoice_date) ? convertUTCToUserDate($invoice->invoice_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') : '' }}</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Payment Terms</div>
								<div>{{ invoicePaymentTermList()[$invoice->payment_term] }}</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Case</div>
								<div class="text-right">
									@if($invoice->case_id == 0)
                                    None
									@else
									{{ @$invoice->case->case_title }}
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
				{{-- For forwarded balances --}}
				@if(!empty($invoice->forwardedInvoices) && count($invoice->forwardedInvoices))
					@php
						$forwardedBalance = $invoice->forwardedInvoices->sum('due_amount');
					@endphp
				<div>
					<div class="payable-detail-header payable-detail-header--dark">
						<div class="payable-detail-line-header-name">Invoice Balance Forward</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->forwardedInvoices as $key => $item)
					<div class="list-row  ">
						<div class="payable-detail-item-entry"><i class="fas fa-arrow-right list-row__icon"></i>
							<div><span>Balance Forward <a href="{{ route('client/bills/detail', $item->decode_id) }}">#{{ $item->invoice_id }}</a></span>
								<div class="list-row__header-detail">Due Date: {{ $item->due_date }}</div>
							</div>
						</div>
						<div class="payable-detail-item-description">
							<div>Invoice Total: ${{ $item->total_amount_new }}
								<br>Amount Paid: ${{ $item->paid_amount_new }}</div>
						</div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">
							<div>Balance Forward
								<br>${{ $item->due_amount_new }}</div>
						</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- For invoice foewarded to --}}
				@if(!empty($invoice->invoiceForwardedToInvoice) && count($invoice->invoiceForwardedToInvoice))
				<div>
					<div class="payable-detail-header payable-detail-header--dark">
						<div class="payable-detail-line-header-name">Invoice Forwarded To</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoiceForwardedToInvoice as $key => $item)
					<div class="list-row  ">
						<div class="payable-detail-item-entry"><i class="fas fa-arrow-right list-row__icon"></i>
							<div>Balance Forward
								<div class="list-row__header-detail">Due Date: {{ $item->due_date }}</div>
							</div>
						</div>
						<div class="payable-detail-item-description"><span>Balance Forward To <a href="{{ route('client/bills/detail', $item->decode_id) }}">#{{ $item->invoice_id }}</a></span></div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">
							<div>Forwarded Balance
								<br>${{ $invoice->total_amount_new }}</div>
						</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- For flat fee --}}
				@if(!empty($invoice->invoiceFlatFeeEntry) && count($invoice->invoiceFlatFeeEntry))
					@php
						$flatFeeTotal = $invoice->invoiceFlatFeeEntry->where('time_entry_billable', 'yes')->sum('cost');
					@endphp
				<div>
					<div class="payable-detail-header">
						<div class="payable-detail-line-header-name">Flat Fees</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoiceFlatFeeEntry as $key => $item)
					<div class="list-row @if($item->time_entry_billable == 'no') payable-nonbillable @endif ">
						<div class="payable-detail-item-entry"><i class="fas fa-receipt list-row__icon"></i>
							<div>Flat Fee @if($item->time_entry_billable == 'no') (Non-billable) @endif
								<div class="list-row__header-detail">{{ $item->date_format_new }}</div>
							</div>
						</div>
						<div class="payable-detail-item-description">{{ $item->description }}</div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">${{ number_format($item->cost, 2) }}</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- For time entry --}}
				@if(!empty($invoice->invoiceTimeEntry) && count($invoice->invoiceTimeEntry))
					@php
						$timeEntryTotal = $invoice->invoiceTimeEntry->where('time_entry_billable', 'yes')->sum('calculated_amt');
					@endphp
				<div>
					<div class="payable-detail-header">
						<div class="payable-detail-line-header-name">Time Entries</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoiceTimeEntry as $key => $item)
					<div class="list-row @if($item->time_entry_billable == 'no') payable-nonbillable @endif ">
						<div class="payable-detail-item-entry"><i class="far fa-clock list-row__icon"></i>
							<div>{{ @$item->taskActivity->title}} @if($item->time_entry_billable == 'no') (Non-billable) @endif
								<div class="list-row__header-detail">{{ $item->date_format_new }}</div>
							</div>
						</div>
						<div class="payable-detail-item-description">{{ $item->description }}</div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">${{ $item->calculated_amt }}</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- For expense entry --}}
				@if(!empty($invoice->invoiceExpenseEntry) && count($invoice->invoiceExpenseEntry))
					@php
						$expenseTotal = $invoice->invoiceExpenseEntry->where('time_entry_billable', 'yes')->sum('calulated_cost');
					@endphp
				<div>
					<div class="payable-detail-header">
						<div class="payable-detail-line-header-name">Expenses</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoiceExpenseEntry as $key => $item)
						<div class="list-row @if($item->time_entry_billable == 'no') payable-nonbillable @endif">
							<div class="payable-detail-item-entry"><i class="fas fa-receipt list-row__icon"></i>
								<div>{{ @$item->expenseActivity->title}} @if($item->time_entry_billable == 'no') (Non-billable) @endif
									<div class="list-row__header-detail">{{ $item->date_format_new }}</div>
								</div>
							</div>
							<div class="payable-detail-item-description">{{ $item->description }}</div>
							<div class="payable-detail-item-status "></div>
							<div class="payable-detail-item-price">${{ $item->calulated_cost }}</div>
						</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- For adjustment entry --}}
				@if(!empty($invoice->invoiceAdjustmentEntry) && count($invoice->invoiceAdjustmentEntry))
					@php
						$totalDiscount = $invoice->invoiceAdjustmentEntry->sum('amount');
					@endphp
				<div>
					<div class="payable-detail-header">
						<div class="payable-detail-line-header-name">Adjustments</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoiceAdjustmentEntry as $key => $item)
					<div class="list-row  ">
						<div class="payable-detail-item-entry"><i class="fas fa-dollar-sign list-row__icon"></i>
							<div>Discount applied to Bill</div>
						</div>
						<div class="payable-detail-item-description"></div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">$-{{ number_format($item->amount, 2) }}</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- For payment plans --}}
				@if(!empty($invoice->invoiceInstallment) && count($invoice->invoiceInstallment))
				<div>
					<div class="payable-detail-header payable-detail-header--dark">
						<div class="payable-detail-line-header-name">Payment Plans</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoiceInstallment as $key => $item)
					<div class="list-row {{ ($item->status == 'paid') ? 'payable-detail-settled' : '' }} ">
						<div class="payable-detail-item-entry"><i class="fas fa-credit-card list-row__icon"></i>
							<div>Payment Plan
								<div class="list-row__header-detail">{{ date('M d, Y', strtotime($item->due_date)) }}</div>
							</div>
						</div>
						<div class="payable-detail-item-description">Status: {{ ($item->status == "paid") ? 'Settled' : 'Unsettled' }}</div>
						<div class="payable-detail-item-status ">
							{{ ($item->status == "paid") ? 'Manual payment successful' : '' }}
						</div>
						<div class="payable-detail-item-price">${{ number_format($item->installment_amount, 2) }}</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- Payment history --}}
				@if(!empty($invoice->invoicePaymentHistory) && count($invoice->invoicePaymentHistory))
				<div id="payment_history">
					<div class="payable-detail-header payable-detail-header--dark">
						<div class="payable-detail-line-header-name">Payment History</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					@forelse ($invoice->invoicePaymentHistory as $key => $item)
					<div class="list-row  ">
						<div class="payable-detail-item-entry"><i class="fas fa-dollar-sign list-row__icon"></i>
							<div>{{ $item->acrtivity_title }}
								<div class="list-row__header-detail">{{ date('M d, Y', strtotime($item->created_at)) }}</div>
							</div>
						</div>
						<div class="payable-detail-item-description">
							Deposited into Operating via {{ $item->pay_method }}
							@if($item->refund_amount)
							(Refunded)
							@endif
						</div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">
							@if($item->acrtivity_title=="Payment Received")
								${{number_format($item->amount,2)}}
							@elseif($item->acrtivity_title=="Payment Refund")
								(${{number_format($item->amount,2)}})
							@endif
						</div>
					</div>
					@empty
					@endforelse
				</div>
				@endif

				{{-- Invoice total --}}
				<div class="payable-detail-header">
					<div class="payable-detail-line-header-name">Totals</div>
				</div>
				<div class="payable-detail__totals">
					<div class="mb-3">
						@if(!empty($invoice->invoiceTimeEntry) && count($invoice->invoiceTimeEntry))
						<div class="payable-detail__datapair mt-0">
							<div>Time Entry Sub-Total:</div>
							<div>${{ number_format($timeEntryTotal ?? 0, 2) }}</div>
						</div>
						@endif
						@if(!empty($invoice->invoiceExpenseEntry) && count($invoice->invoiceExpenseEntry))
						<div class="payable-detail__datapair mt-0">
							<div>Expense Sub-Total:</div>
							<div>${{ number_format($expenseTotal ?? 0, 2) }}</div>
						</div>
						@endif
						@if(!empty($invoice->invoiceFlatFeeEntry) && count($invoice->invoiceFlatFeeEntry))
						<div class="payable-detail__datapair mt-0">
							<div>Flat Fee Sub-Total:</div>
							<div>${{ number_format($flatFeeTotal ?? 0, 2) }}</div>
						</div>
						@endif
						<strong>
							<div class="payable-detail__datapair mt-0">
								<div>Sub-Total:</div><div>${{ number_format((@$timeEntryTotal ?? 0 + @$expenseTotal ?? 0 + @$flatFeeTotal ?? 0), 2) }}</div>
							</div>
						</strong>
						@if(!empty($invoice->forwardedInvoices) && count($invoice->forwardedInvoices))
						<br>
						<div class="mb-3">
							<div class="payable-detail__datapair mt-0">
								<div>Balance Forward:</div><div>${{ number_format($forwardedBalance ?? 0, 2) }}</div>
							</div>
						</div>
						@endif
						@if(!empty($invoice->invoiceAdjustmentEntry) && count($invoice->invoiceAdjustmentEntry))
						<div class="payable-detail__datapair mt-0">
							<div>Discounts:</div>
							<div>$-{{ number_format($totalDiscount ?? 0, 2) }}</div>
						</div>
						@endif
					</div>
					<div class="totals">
						<strong>
							<div class="payable-detail__datapair mt-0">
								<div>Total:</div><div>${{ $invoice->total_amount_new }}</div>
							</div>
						</strong>
						<div class="payable-detail__datapair mt-0">
							<div>Amount Paid:</div>
							<div>${{ $invoice->paid_amount_new }}</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div></div>
</div>

@endsection