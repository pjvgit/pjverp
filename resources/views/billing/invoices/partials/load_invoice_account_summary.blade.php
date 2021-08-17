<div>
	@php
		$lableShow = 0;
		$appliedTrustClient = $findInvoice->applyTrustFund->pluck("show_trust_account_history", "client_id")->toArray();
		$appliedcreditClient = $findInvoice->applyCreditFund->pluck("show_credit_account_history", "client_id")->toArray();
		foreach($appliedTrustClient as $k => $v){
			$lableShow = ($v == 'dont show') ? 0 : 1;
		}
		foreach($appliedcreditClient as $k => $v){
			$lableShow = ($v == 'dont show') ? 0 : 1;
		}
	@endphp
	<div class="ledger-histories">				
        @if(isset($caseMaster) && !empty($caseMaster->caseAllClient))
			@if($lableShow == 1)
			<h3> Account Summary
				<a id="ledger-histories-refresh" class="ledger-histories-refresh" onclick="refreshAccountHistory()">
					Refresh Account Histories
				</a>
			</h3>
			@endif
            @forelse ($caseMaster->caseAllClient as $key => $item)
                <div class="ledger_history_full mt-3">
					@if ($appliedTrustClient && array_key_exists($item->id, $appliedTrustClient) && $appliedTrustClient[$item->id] == "trust account summary")
						<h4>{{ $item->full_name }}'s Trust Balance</h4>
						<div class="balance_data invoice-table-row"> Balance As Of {{ date('m/d/Y') }}: ${{ @$item->userAdditionalInfo->trust_account_balance }} </div>
					@elseif($appliedTrustClient && array_key_exists($item->id, $appliedTrustClient) && $appliedTrustClient[$item->id] == "trust account history")
                    	<h4>{{ $item->full_name }}'s Trust History</h4>
						<div class="balance_data"> Balance As Of {{ date('m/d/Y') }}: ${{ @$item->userAdditionalInfo->trust_account_balance }} </div>
						<table class="ledger-history-table">
							<tbody>
								<tr class="invoice_info_row invoice_header_row">
									<td class="invoice_info_bg" style="width: 10%;"> Date </td>
									<td class="invoice_info_bg" style="width: 12%;"> Related To </td>
									<td class="invoice_info_bg" style="width: 48%;"> Details </td>
									<td class="invoice_info_bg" style="width: 15%;"> Amount </td>
									<td class="invoice_info_bg" style="width: 15%;"> Balance </td>
								</tr>
								@forelse ($item->userTrustAccountHistory as $thkey => $thitem)
									<tr class="invoice_info_row invoice-table-row">
										<td style="vertical-align: top;"> {{ \Carbon\Carbon::parse(convertUTCToUserDate($thitem->payment_date, auth()->user()->user_timezone))->format("m/d/Y") }} </td>
										<td style="vertical-align: top;"> {{ $thitem->related_to_invoice_id ?? "--"}} </td>
										<td style="vertical-align: top;"> {{ ($thitem->fund_type == "diposit") ? "Trust deposit" : "Payment from trust" }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->fund_type == "diposit") ? "$".number_format($thitem->amount_paid, 2) : "-$".number_format($thitem->withdraw_amount, 2) }} </td>
										<td style="vertical-align: top;"> ${{ number_format($thitem->current_trust_balance, 2) }} </td>
									</tr>
								@empty                                
								@endforelse
							</tbody>
						</table>
					@else
					@endif
                </div>
            @empty
            @endforelse
		@endif

		@if(isset($caseMaster) && !empty($caseMaster->caseAllClient))
            @forelse ($caseMaster->caseAllClient as $key => $item)
                <div class="ledger_history_full mt-3">
					@if ($appliedcreditClient && array_key_exists($item->id, $appliedcreditClient) && $appliedcreditClient[$item->id] == "credit account summary")
						<h4>{{ $item->full_name }}'s Credit Balance</h4>
						<div class="balance_data invoice-table-row"> Balance As Of {{ date('m/d/Y') }}: ${{ @$item->userAdditionalInfo->credit_account_balance }} </div>
					@elseif($appliedcreditClient && array_key_exists($item->id, $appliedcreditClient) && $appliedcreditClient[$item->id] == "credit account history")
                    	<h4>{{ $item->full_name }}'s Credit History</h4>
						<div class="balance_data"> Balance As Of {{ date('m/d/Y') }}: ${{ @$item->userAdditionalInfo->credit_account_balance }} </div>
						<table class="ledger-history-table">
							<tbody>
								<tr class="invoice_info_row invoice_header_row">
									<td class="invoice_info_bg" style="width: 10%;"> Date </td>
									<td class="invoice_info_bg" style="width: 12%;"> Related To </td>
									<td class="invoice_info_bg" style="width: 48%;"> Details </td>
									<td class="invoice_info_bg" style="width: 15%;"> Amount </td>
									<td class="invoice_info_bg" style="width: 15%;"> Balance </td>
								</tr>
								@forelse ($item->userCreditAccountHistory as $thkey => $thitem)
									<tr class="invoice_info_row invoice-table-row">
										<td style="vertical-align: top;"> {{ \Carbon\Carbon::parse(convertUTCToUserDate($thitem->payment_date, auth()->user()->user_timezone))->format("m/d/Y") }} </td>
										<td style="vertical-align: top;"> {{ $thitem->related_to_invoice_id ?? "--"}} </td>
										<td style="vertical-align: top;"> {{ ($thitem->payment_type == "payment" || $thitem->payment_type == "withdraw") ? "Credit withdrawal" : "Credit deposit" }} </td>
										<td style="vertical-align: top;"> {{ ($thitem->payment_type == "payment" || $thitem->payment_type == "withdraw") ? "-$".number_format($thitem->deposit_amount, 2) : "$".number_format($thitem->deposit_amount, 2) }} </td>
										<td style="vertical-align: top;"> ${{ number_format($thitem->total_balance, 2) }} </td>
									</tr>
								@empty                                
								@endforelse
							</tbody>
						</table>
					@else
					@endif
                </div>
            @empty
            @endforelse
		@endif
	</div>
</div>