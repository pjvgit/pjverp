
<h3 class="border-bottom border-gray pb-2">Total: {{ ($month == 0) ? 'One payment of '.$payableAmount : $month.' payments of '. invoiceMonthlyPaymentAmount($payableAmount, $month) }}</h3>
<div class="row pt-3">
    <div class="col-md-8">
        <div class="text-left">
            <div class="offset-2">
                <div class="alert alert-danger" role="alert" id="error-alert" style="display:none;">
                    <span class="error-text"><strong class="text-capitalize">Error!</strong></span>
                    <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <h4 class="card-title mb-3">{{ ($month == 0) ? 'Pay with Visa, MasterCard or American Express Credit or Debit Card' : $month.' interest free Monthly Payments with credit card' }}</h4>
                <form id="card_form" method="POST" action="{{ route('client/bills/payment/card') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}" >
                    <input type="hidden" name="payable_record_id" value="{{ $payableRecordId }}" >
                    <input type="hidden" name="client_id" value="{{ $clientId }}" >
                    <input type="hidden" name="emi_month" value="{{ $month }}" >
                    <input type="hidden" name="payable_amount" value="{{ $payableAmount }}" >
                    <div class="form-group row">
                        <label class="col-md-3">Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name_on_card" id="name_on_card" value="" data-conekta="card[name]" placeholder="Nombre del tarjetahabiente" maxlength="50" size="50">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Phone number</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="phone_number" id="phone_number" value="" placeholder="Teléfono del tarjetahabiente" maxlength="20" size="20">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Credit card number</label>
                        <div class="col-md-9">
                            <input type="text" maxlength="16" size="16" data-conekta="card[number]" class="credit-card-number form-control" name="card_number" id="card_number" inputmode="numeric" placeholder="•••• •••• •••• ••••" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Expiration date</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="MM" maxlength="2" name="expiry_month" id="expiry_month" value="" data-conekta="card[exp_month]">
                        </div>/
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="YYYY" maxlength="4" name="expiry_year" id="expiry_year" value="" data-conekta="card[exp_year]">
                        </div>
                        <span class="card-date-error"></span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">CVV</label>
                        <div class="col-md-2">
                            <input type="password" class="form-control" placeholder="CVV" maxlength="4" size="4" name="cvv" id="cvv" value="" data-conekta="card[cvc]">
                        </div>
                        <span class="cvv-error"></span>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3"></label>
                        <div class="col-md-9">
                            <input type="hidden" name="conekta_token_id" id="conekta_token_id"/>
                            <button type="submit" class="btn btn-primary">Pay now!</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>