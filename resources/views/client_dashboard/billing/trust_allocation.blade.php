<h2 class="mx-2 mb-0 text-nowrap hiddenLable">        {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Client)    </h2>
<div class="payment-settings-container container-fluid">
	<div class="table-responsive">
		<table class="trust-allocations-table border-top table table-md table-striped table-hover" style="table-layout: auto;">
			<colgroup>
				<col style="width: 20%;">
				<col style="width: 20%;">
				<col style="width: 30%;">
				<col style="width: 20%;">
				<col style="width: 10%;">
			</colgroup>
			<thead>
				<tr>
					<th class="court-case-name" style="cursor: initial;"><span>Name</span></th>
					<th class="trust-allocation" style="cursor: initial;"><span>Trust Allocation</span></th>
					<th class="minimum-trust-balance" style="cursor: initial;"><span>Minimum Trust Balance</span></th>
					<th class="minimum-trust-warning" style="cursor: initial;"><span></span></th>
					<th class="request-funds" style="cursor: initial;"><span></span></th>
				</tr>
			</thead>
			<tbody>
                @include('client_dashboard.billing.load_trust_allocation_list')
            </tbody>
        </table>
    </div>
</div>
