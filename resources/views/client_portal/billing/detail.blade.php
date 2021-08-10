@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
	<section class="detail-view" id="payable_detail_view">
		<div class="payable-detail ">
			<div class="detail-view__header">
				<div>Invoice #00103</div>
				<div class="payable-detail__actions"><a class="payable-detail__export-link" href="/bills/14231674.pdf" target="_blank"><i class="payable-detail__export-icon">file_download</i><span class="payable-detail__export-button">View Full Invoice (PDF)</span></a></div>
			</div>
			<div class="mb-3 mb-md-0">
				<div class="p-3">
					<div class="payable-detail__summary">
						<div class="payable-detail__summary__left-panel"><b>[SAMPLE] John Doe</b>
							<div class="payable-detail__address-text">123 Main St. Anytown, CA 93455</div>
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
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Invoice Date</div>
								<div>{{ convertUTCToUserDate($invoice->invoice_date, auth()->user()->user_timezone ?? 'UTC')->format('M d, Y') }}</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Due Date</div>
								<div>Aug 9, 2021</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Payment Terms</div>
								<div>Due Date</div>
							</div>
							<div class="payable-detail__datapair">
								<div class="detail-view__label">Case</div>
								<div class="text-right">[SAMPLE] John Doe Matter</div>
							</div>
						</div>
					</div>
				</div>
				<div>
					<div class="payable-detail-header">
						<div class="payable-detail-line-header-name">Flat Fees</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					<div class="list-row  ">
						<div class="payable-detail-item-entry"><i class="list-row__icon">receipt</i>
							<div>Flat Fee
								<div class="list-row__header-detail">Aug 10, 2021</div>
							</div>
						</div>
						<div class="payable-detail-item-description"></div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">$1,000.00</div>
					</div>
				</div>
				<div>
					<div class="payable-detail-header">
						<div class="payable-detail-line-header-name">Time Entries</div>
						<div class="payable-detail-line-header-description">Description</div>
						<div class="payable-detail-line-header-status"></div>
						<div class="payable-detail-line-header-price"></div>
					</div>
					<div class="list-row  ">
						<div class="payable-detail-item-entry"><i class="list-row__icon">access_time</i>
							<div>Document Preparation
								<div class="list-row__header-detail">Aug 10, 2021</div>
							</div>
						</div>
						<div class="payable-detail-item-description"></div>
						<div class="payable-detail-item-status "></div>
						<div class="payable-detail-item-price">$500.00</div>
					</div>
				</div>
				<div class="payable-detail-header">
					<div class="payable-detail-line-header-name">Totals</div>
				</div>
				<div class="payable-detail__totals">
					<div class="mb-3">
						<div class="payable-detail__datapair mt-0">
							<div>Time Entry Sub-Total:</div>
							<div>$500.00</div>
						</div>
						<div class="payable-detail__datapair mt-0">
							<div>Flat Fee Sub-Total:</div>
							<div>$1,000.00</div>
						</div><strong><div class="payable-detail__datapair mt-0"><div>Sub-Total:</div><div>$1,500.00</div></div></strong></div>
					<div class="totals"><strong><div class="payable-detail__datapair mt-0"><div>Total:</div><div>$1,500.00</div></div></strong>
						<div class="payable-detail__datapair mt-0">
							<div>Amount Paid:</div>
							<div>$0.00</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div></div>
</div>

@endsection