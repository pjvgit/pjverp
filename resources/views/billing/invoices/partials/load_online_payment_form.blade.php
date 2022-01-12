<div class="alert alert-danger" role="alert" id="error-alert" style="display:none;">
    <span class="error-text"><strong class="text-capitalize">Error!</strong></span>
    <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<form class="pay-online-payment" id="pay_online_payment" name="pay_online_payment" method="POST">
    @csrf
    <input type="text" name="type" value="invoice" >
    <input type="text" name="payable_record_id" value="{{ $invoice_id }}" >
    <div class="row">
        <div class="col-md-4 form-group mb-3">
            <label for="firstName1">Amount</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                </div>
                <input class="form-control payment-amount number" style="width:50%; " maxlength="20" name="amount" value="" type="text" aria-label="Amount (to the nearest dollar)" data-payable-amount="{{ $finalAmt }}" data-max-amount="{{ $finalAmt }}">
                <small>&nbsp;</small>
                <div class="input-group col-sm-9" id="TypeError"></div>
                <span id="amt"></span>
            </div>
        </div>
        <div class="col-md-2 form-group">
            <label for="payfull">&nbsp;</label>
            <label class="checkbox checkbox-outline-primary">
                <input type="checkbox" class="payfullFirst" id="payfull" value="{{$finalAmt}}" name="payfull"><span>Pay in full</span><span class="checkmark"></span>
            </label>
        </div>
        <div class="col-md-6 form-group">
            <label for="notes">Notes</label>
            <input class="form-control" value="" name="notes" type="text">
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 form-group">
            <label for="contact-field">Choose contact</label>
            <select class="form-control" name="client_id" style="width: 100%;" placeholder="Select a contact">
                <option></option>
                @forelse($trustAccounts as $key=>$val)
                    <option value="{{$val->uid}}"> {{$val->user_name}} ({{ @$val->user_title }})</option>
                @empty
                @endforelse
                @if (!empty($invoiceUserNotInCase))
                    <option value="{{ $invoiceUserNotInCase->user_id }}">{{ @$invoiceUserNotInCase->user->full_name }} ({{ @$invoiceUserNotInCase->user->user_type_text }})</option>
                @endif
            </select>  
            <span id="clientid-error"></span>
        </div>                
        <div class="col-md-12 form-group">
            <label for="payment_method">Payment Method</label>
            <select class="form-control" id="online_payment_method" name="online_payment_method" style="width: 100%;" placeholder="Select payment method">
                <option value="">Select a payment method</option>
                <option value="credit-card">Credit Card</option>
                <option value="cash">Cash</option>
                <option value="bank-transfer">Bank Transfer</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="credit-card selectt" style="display: none;">
                <img src="{{ asset('images/payment/pago1.png') }}" class="img-fluid img-thumbnail" width="50%"/>
                <ul class="list-group">
                    <li class="list-group-item border-0 pb-0">
                        <label class="radio radio-primary">
                            <input type="radio" class="payment-option" name="emi_month" value="0" checked>
                            <span>Pay with Visa, MasterCard or American Express Credit or Debit Card</span><span class="checkmark"></span>
                        </label>
                    </li>
                    @if($invoiceData->due_amount >= 300)
                    <li class="list-group-item border-0 pb-0">
                        <label class="radio radio-primary">
                            <input type="radio" class="payment-option" name="emi_month" value="3">
                            <span>3 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                        </label>
                    </li>
                    @endif
                    @if($invoiceData->due_amount >= 600)
                    <li class="list-group-item border-0 pb-0">
                        <label class="radio radio-primary">
                            <input type="radio" class="payment-option" name="emi_month" value="6">
                            <span>6 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                        </label>
                    </li>
                    @endif
                    @if($invoiceData->due_amount >= 800)
                    <li class="list-group-item border-0 pb-0">
                        <label class="radio radio-primary">
                            <input type="radio" class="payment-option" name="emi_month" value="9">
                            <span>9 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                        </label>
                    </li>
                    @endif
                    @if($invoiceData->due_amount >= 1200)
                    <li class="list-group-item border-0 pb-0">
                        <label class="radio radio-primary">
                            <input type="radio" class="payment-option" name="emi_month" value="12">
                            <span>12 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                        </label>
                    </li>
                    @endif
                </ul>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="">Name</label>
                        <input type="text" class="form-control" name="name_on_card" value="Trupti" data-conekta="card[name]" placeholder="Nombre del tarjetahabiente" maxlength="50" size="50">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="">Phone number</label>
                        <input type="text" class="form-control" name="phone_number" value="8756457889" placeholder="Teléfono del tarjetahabiente" maxlength="20" size="20">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="">Credit card number</label>
                        <input type="text" maxlength="16" size="16" data-conekta="card[number]" class="credit-card-number form-control" name="card_number" id="card_number" inputmode="numeric" placeholder="•••• •••• •••• ••••" value="">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="">Expiration date</label>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="MM" maxlength="2" name="expiry_month" id="expiry_month" value="05" data-conekta="card[exp_month]">
                            </div>/
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="YYYY" maxlength="4" name="expiry_year" id="expiry_year" value="2025" data-conekta="card[exp_year]">
                            </div>
                        </div>
                        <span class="card-date-error"></span>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="">CVV</label>
                        <input type="password" class="form-control" placeholder="CVV" maxlength="4" size="4" name="cvv" id="cvv" value="777" data-conekta="card[cvc]">
                    </div>
                    <input type="hidden" name="conekta_token_id" id="conekta_token_id"/>
                </div>
            </div>
            <div class="cash selectt" style="display: none;">
                <div class="form-group row">
                    <img class="col-md-3" src="{{ asset('images/payment/pago2.png') }}" />
                    <div class="col-md-9">
                        <label class="radio radio-primary">
                            <input type="radio" name="radio" value="1" checked>
                            <span> @lang('billing.c_radio_text') </span><span class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3">Name</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="name" value="{{ $client->full_name ?? '' }}" placeholder="Nombre" maxlength="50">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3">Phone number</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control phone-number" name="phone_number" value="{{ $client->mobile_number ?? '' }}" placeholder="Teléfono" maxlength="13" minlength="10">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3"></label>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-primary mt-2">Continue</button>
                    </div>
                </div>
            </div>
            <div class="bank-transfer selectt" style="display: none;">
                <div class="form-group row">
                    <img class="col-md-3" src="{{ asset('images/payment/pago3.png') }}" />
                    <div class="col-md-9">
                        <label class="radio radio-primary">
                            <input type="radio" name="radio" value="1" checked>
                            <span> @lang('billing.bt_radio_text') </span><span class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3">Name</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="bt_name" value="{{ $client->full_name ?? '' }}" placeholder="Nombre" maxlength="50">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3">Phone number</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control phone-number" name="bt_phone_number" value="{{ $client->mobile_number ?? '' }}" placeholder="Teléfono" maxlength="13" minlength="10">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3"></label>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-primary mt-2">Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="loader-bubble loader-bubble-primary innerLoader" style="display: none;">
    </div>
    <div class="form-group row float-right">
        <a href="#">
            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
        </a>
        <button class="btn btn-primary ladda-button example-button m-1 submit" onclick="onlinePaymentConfirmation()" type="button">
            Make Payment
        </button>
    </div>
</form>