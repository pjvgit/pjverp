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
		<div class="payable-detail">
            <div class="detail-view__header">
                <div>Funds Request: {{ @$fundRequest->padding_id }}</div>
                <div class="ml-auto d-flex"></div>
                <div class="payable-detail__actions">
					@if(getFirmOnlinePaymentSetting() && getFirmOnlinePaymentSetting()->is_accept_online_payment == 'yes' && $fundRequest->status != "paid")
					<a class="btn btn-primary payable-detail__export-link ml-5" href="{{ route('client/bills/payment', ['type'=>'fundrequest', 'id'=>encodeDecodeId($fundRequest->id, 'encode'), 'client_id'=>encodeDecodeId(auth()->id(), 'encode')]) }}">
						<span class="payable-detail__export-button">Pay Now</span>
					</a>
					@endif
				</div>
            </div>
            <div class="mb-3 mb-md-0">
                <div class="p-3">
                    @if ($fundRequest->status == "paid")
                    <div class="text-center">
                        <div class="detail-view__label mb-2">{{ ucfirst($fundRequest->status) }}</div>
                        <h2><i class="fas fa-check-circle payable-detail__settled"></i>${{ $fundRequest->amt_paid }}</h2>
                    </div>
                    @endif
                    <div class="payable-detail__summary">
                        <div class="payable-detail__summary__left-panel"></div>
                        <div class="payable-detail__summary__right-panel">
                            <div class="payable-detail__datapair">
                                <div class="detail-view__label">Status</div>
                                <div>{{ ucfirst($fundRequest->status) }}</div>
                            </div>
                            <div class="payable-detail__datapair">
                                <div class="detail-view__label">Balance Due</div>
                                <div>${{ $fundRequest->amt_due }}</div>
                            </div>
                            <div class="payable-detail__datapair">
                                <div class="detail-view__label">Created Date</div>
                                <div>{{ $fundRequest->send_date_format }}</div>
                            </div>
                            <div class="payable-detail__datapair">
                                <div class="detail-view__label">Due Date</div>
                                <div>{{ $fundRequest->due_date_format }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-start mt-4">
                        <div class="detail-view__label">Message</div>
                    </div>
                    <br>
                    <p class="u-word-wrap-break-word" id="bill_notes">{{ $fundRequest->email_message }}</p>
                </div>
            </div>
        </div>
	</section>
	<div></div>
</div>

@endsection