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

<div id="trust_allocation_modal" class="modal fade bd-example-modal-md " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Trust Allocation</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
				<div id="trust_allocation_modal_body">
				</div>
            </div>
        </div>
    </div>
</div>
