<div>
	@php
		$lableShow = 0;
		$appliedTrustClient = $findInvoice->applyTrustFund;
		$appliedcreditClient = $findInvoice->applyCreditFund;
		$showTrustHistory = $findInvoice->applyTrustFund->pluck("show_trust_account_history")->toArray();
		$showCreditHistory = $findInvoice->applyCreditFund->pluck("show_credit_account_history")->toArray();
		$lableShow = 1;
		if ((count(array_flip($showTrustHistory)) === 1 && end($showTrustHistory) === 'dont show') && (count(array_flip($showCreditHistory)) === 1 && end($showCreditHistory) === 'dont show')) {
			$lableShow = 0;
		}
		$caseClientWithTrashed = (!empty($caseMaster)) ? $caseMaster->caseAllClientWithTrashed : '';
	@endphp
	<div class="ledger-histories">				
        @if(isset($caseMaster) && !empty($caseClientWithTrashed))
			@if($lableShow == 1)
			<h3> Account Summary
				<a id="ledger-histories-refresh" class="ledger-histories-refresh" onclick="refreshAccountHistory()">
					Refresh Account Histories
				</a>
			</h3>
			@endif
            @forelse ($caseClientWithTrashed as $key => $item)
                <div class="ledger_history_full mt-3">
					@php
						$user = $appliedTrustClient->where('client_id', $item->id)->first();
					@endphp
					@if ($user && $user->show_trust_account_history == "trust account summary")
						<h4>{{ $item->full_name }}'s Trust Balance</h4>
						<div class="balance_data invoice-table-row"> Balance As Of {{ convertUTCToUserTimeZone('dateOnly') }}: ${{ number_format($user->total_balance ?? 0, 2) }} </div>
					@elseif($user && $user->show_trust_account_history == "trust account history")
						@php
							$trustList = $item->userCreditAccountHistory->where('id', '<=', $user->history_last_id);
						@endphp
						@if(count($trustList))
                    	<h4>{{ $item->full_name }}'s Trust History</h4>
						<div class="balance_data"> Balance As Of {{ convertUTCToUserTimeZone('dateOnly') }}: ${{ number_format($user->total_balance ?? 0, 2) }} </div>
						<table class="ledger-history-table">
							<tbody>
								<tr class="invoice_info_row invoice_header_row">
									<td class="invoice_info_bg" style="width: 10%;"> Date </td>
									<td class="invoice_info_bg" style="width: 12%;"> Related To </td>
									<td class="invoice_info_bg" style="width: 48%;"> Details </td>
									<td class="invoice_info_bg" style="width: 15%;"> Amount </td>
									<td class="invoice_info_bg" style="width: 15%;"> Balance </td>
								</tr>
								@forelse ($item->userTrustAccountHistory->where('id', '<=', $user->history_last_id) as $thkey => $thitem)
									<tr class="invoice_info_row invoice-table-row">
										<td style="vertical-align: top;"> {{ \Carbon\Carbon::parse(convertUTCToUserDate($thitem->payment_date, auth()->user()->user_timezone))->format("m/d/Y") }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->related_to_invoice_id) ? '#'.sprintf("%06d", $thitem->related_to_invoice_id) : "--" }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->fund_type == "diposit") ? "Trust deposit" : "Payment from trust" }} </td>
										<td style="vertical-align: top;"> 
											@if($thitem->fund_type == "diposit")
											{{ "$".number_format($thitem->amount_paid, 2) }}
											@elseif($thitem->fund_type == "withdraw")
											{{ "-$".number_format($thitem->withdraw_amount, 2) }} 
											@elseif($thitem->fund_type == "payment")
											{{ "-$".number_format($thitem->amount_paid, 2) }}
											@endif
										</td>
										<td style="vertical-align: top;"> ${{ number_format($thitem->current_trust_balance, 2) }} </td>
									</tr>
								@empty                                
								@endforelse
							</tbody>
						</table>
						@endif
					@else
					@endif
                </div>
            @empty
            @endforelse
		@endif

		@if(isset($caseMaster) && !empty($caseClientWithTrashed))
            @forelse ($caseClientWithTrashed as $key => $item)
                <div class="ledger_history_full mt-3">
					@php
						$user = $appliedcreditClient->where('client_id', $item->id)->first();
					@endphp
					@if ($user && $user->show_credit_account_history == "credit account summary")
						<h4>{{ $item->full_name }}'s Credit Balance</h4>
						<div class="balance_data invoice-table-row"> Balance As Of {{ convertUTCToUserTimeZone('dateOnly') }}: ${{ number_format($user->total_balance ?? 0, 2) }} </div>
					@elseif ($user && $user->show_credit_account_history == "credit account history")
						@php
							$creditList = $item->userCreditAccountHistory->where('id', '<=', $user->history_last_id);
						@endphp
						@if(count($creditList))
                    	<h4>{{ $item->full_name }}'s Credit History</h4>
						<div class="balance_data"> Balance As Of {{ convertUTCToUserTimeZone('dateOnly') }}: ${{ number_format($user->total_balance ?? 0, 2) }} </div>
						<table class="ledger-history-table">
							<tbody>
								<tr class="invoice_info_row invoice_header_row">
									<td class="invoice_info_bg" style="width: 10%;"> Date </td>
									<td class="invoice_info_bg" style="width: 12%;"> Related To </td>
									<td class="invoice_info_bg" style="width: 48%;"> Details </td>
									<td class="invoice_info_bg" style="width: 15%;"> Amount </td>
									<td class="invoice_info_bg" style="width: 15%;"> Balance </td>
								</tr>
								@forelse ($creditList as $thkey => $thitem)
									<tr class="invoice_info_row invoice-table-row">
										<td style="vertical-align: top;"> {{ \Carbon\Carbon::parse(convertUTCToUserDate($thitem->payment_date, auth()->user()->user_timezone))->format("m/d/Y") }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->related_to_invoice_id) ? '#'.sprintf("%06d", $thitem->related_to_invoice_id) : "--" }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->payment_type == "payment" || $thitem->payment_type == "withdraw") ? "Credit withdrawal" : "Credit deposit" }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->payment_type == "payment" || $thitem->payment_type == "withdraw") ? "-$".number_format($thitem->deposit_amount, 2) : "$".number_format($thitem->deposit_amount, 2) }} </td>
										<td style="vertical-align: top;"> ${{ number_format($thitem->total_balance, 2) }} </td>
									</tr>
								@empty                                
								@endforelse
							</tbody>
						</table>
						@endif
					@else
					@endif
                </div>
            @empty
            @endforelse
		@endif
	</div>
</div>