<h2 class="pb-2">Invoice #{{ $invoice->id }}</h2>
<h3 class="border-bottom border-gray pb-2">Total: {{ ($month == 0) ? 'One payment of '.$invoice->due_amount : $month.' payments of '. invoiceMonthlyPaymentAmount($invoice->due_amount, $month) }}</h3>
<div class="row pt-3">
    <div class="col-md-8">
        <div class="card text-left">
            <div class="card-body">
                <div class="offset-2">
                    <div class="alert alert-danger" role="alert" id="error-alert">
                        <span><strong class="text-capitalize">Error!</strong> Lorem ipsum dolor sit amet.</span>
                        <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <h4 class="card-title mb-3">{{ ($month == 0) ? 'Pay with Visa, MasterCard or American Express Credit or Debit Card' : $month.' interest free Monthly Payments with credit card' }}</h4>
                    <form id="card_form" method="POST" action="{{ route('client/bills/payment/card') }}">
                        @csrf
                        <input type="text" name="invoice_id" value="{{ $invoice->id }}" >
                        <input type="text" name="client_id" value="{{ $clientId }}" >
                        <input type="text" name="emi_month" value="{{ $month }}" >
                        <div class="form-group">
                            <label class="col-md-3">Name</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="name_on_card" id="name_on_card" value="Trupti" data-conekta="card[name]">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Phone number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="phone_number" id="phone_number" value="8756457889">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Credit card number</label>
                            <div class="col-md-9">
                                <input type="text" maxlength="19" size="19" data-conekta="card[number]" class="credit-card-number form-control" name="card_number" id="card_number" inputmode="numeric" placeholder="•••• •••• •••• ••••" value="4242 4242 4242 4242">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Expiration date</label>
                            <div class="col-md-4">
                                <input type="text" placeholder="MM" maxlength="5" name="expiry_month" id="expiry_month" value="05" data-conekta="card[exp_month]">
                            </div>
                            <div class="col-md-4">
                                <input type="text" placeholder="YYYY" maxlength="5" name="expiry_year" id="expiry_year" value="2025" data-conekta="card[exp_year]">
                            </div>
                            <span class="card-date-error"></span>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">CVV</label>
                            <div class="col-md-9">
                                <input type="text" placeholder="123" maxlength="3" name="cvv" id="cvv" value="777" data-conekta="card[cvc]">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3"></label>
                            <div class="col-md-9">
                                <ul  class="parsley-error-list">
                                    <li class="required" style="display: list-item;">
                                        <span  class="card-errors" style="font-weight: bold;"></span>
                                    </li>
                                </ul>
                                <button type="submit" class="btn btn-primary">Pay now!</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>