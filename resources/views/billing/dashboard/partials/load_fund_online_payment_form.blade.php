<div class="scrollbar scrollbar-primary">
@if(empty(getFirmOnlinePaymentSetting()) || getFirmOnlinePaymentSetting()->is_accept_online_payment == "no")
    <div class="col-md-12">
        <div class="payment-confirmation-container">
            <div class="row">
                <div class="col-3 pl-4 pt-4">
                    <span class="money-graph"></span>
                </div>
                <div class="col-9">
                    <div data-testid="payments-platform-promo-header"
                        class="payments-platform-promo-header">
                        <h3>Start Accepting Online Payments!</h3>
                    </div>
                    <br>
                    <ul class="invoice-payments-platform-promo-list clearfix" style="list-style-type:none;">
                        <li data-testid="payments-platform-promo-list-item" class="payments-platform-promo-list-item clearfix">
                            <i class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                            <span class="payments-platform-promo-list-item-text">Get paid faster by accepting credit card payments in office</span>
                        </li>
                        <li data-testid="payments-platform-promo-list-item" class="payments-platform-promo-list-item clearfix">
                            <i class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                            <span class="payments-platform-promo-list-item-text">Get paid faster by letting your clients pay online</span>
                        </li>
                        <li data-testid="payments-platform-promo-list-item" class="payments-platform-promo-list-item clearfix">
                            <i class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                            <span class="payments-platform-promo-list-item-text">Access from your MyCase account, no 3rd party</span>
                        </li>
                        <li data-testid="payments-platform-promo-list-item" class="payments-platform-promo-list-item clearfix">
                            <i class="fas fa-check-circle payments-platform-promo-list-item-icon"></i>
                            <span class="payments-platform-promo-list-item-text">Save money with free Check payments and competitive credit card fees</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <br>
        <div class="modal-footer"></div>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
        <div class="form-group row float-right">
            <a class="btn btn-primary" href="{{ route("billing/settings") }}" target="_blank" rel="noopener noreferrer">Get Started Now!</a>
        </div>
    </div>
@else
    <div class="alert alert-danger" role="alert" id="error-alert" style="display:none;">
        <span class="error-text"><strong class="text-capitalize">Error!</strong></span>
        <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
    </div>
    <form class="pay-online-payment" id="pay_online_payment" name="pay_online_payment" method="POST">
        @csrf
        <input type="text" name="type" value="fund" >
        <input type="text" name="fund_type" value="{{ $fundType }}" >
        <input type="text" name="client_id" value="{{ $userData->uid }}" >
        <input type="text" id="case_id" name="case_id" value="{{ @$case->id }}">
        <input type="text" id="conekta_key" value="{{ (!empty(getFirmOnlinePaymentSetting()) || getFirmOnlinePaymentSetting()->is_accept_online_payment == "yes") ? getFirmOnlinePaymentSetting()->public_key : ''}}" >
        <span id="response"></span>
        @if(!empty($fundRequestList) && count($fundRequestList))
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="firstName1">Apply to Request</label>
                <select class="form-control caller_name select2" id="applied_to" name="applied_to" style="width: 100%;" placeholder="Applied To">
                    <option value="0"> Do not apply to a retainer request</option>
                    @forelse($fundRequestList as $key=>$val){?>
                        <option value="{{$val->id}}" <?php echo ($val->id == $request->request_id) ? 'selected':''; ?> >R-{{ sprintf('%06d', $val->id)}} (${{number_format($val->amount_due,2)}})</option>
                    @empty
                    @endforelse
                </select>
                <span id="papply"></span>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="firstName1">Amount</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input class="form-control online-pay-amount" style="width:50%; " maxlength="20" name="amount" id="online_pay_amount" value="" type="text" aria-label="Amount (to the nearest dollar)">
                    <small>&nbsp;</small>
                    <div class="input-group col-sm-9" id="TypeError"></div>
                    <span id="amt"></span>
                </div>
            </div>
            <div class="col-md-6 form-group">
                <label for="firstName1">Date</label>
                <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="payment_date" maxlength="250" name="payment_date" type="text">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="firstName1">Payment Method</label>
                <select class="form-control" id="online_payment_method" name="online_payment_method" style="width: 100%;" placeholder="Select payment method">
                    <option value="">Select a payment method</option>
                    @forelse(onlinePaymentMethod() as $key=>$val)
                        <option value="{{$key}}"> {{$val}}</option>
                    @empty
                    @endforelse
                </select>
                <span id="ptype"></span>
            </div>
            <div class="col-md-6 form-group">
                <label for="firstName1">Notes</label>
                <input class="form-control" value="" id="notes" name="notes" type="text">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="credit-card selectt" style="display: none;">
                    <img src="{{ asset('images/payment/pago1.png') }}" class="img-fluid img-thumbnail" width="50%"/>
                    <ul class="list-group">
                        <li class="list-group-item border-0 pb-0 emi-li">
                            <label class="radio radio-primary">
                                <input type="radio" class="payment-option" name="emi_month" value="0" checked>
                                <span>Pay with Visa, MasterCard or American Express Credit or Debit Card</span><span class="checkmark"></span>
                            </label>
                        </li>
                        <li class="list-group-item border-0 pb-0 emi-li">
                            <label class="radio radio-primary">
                                <input type="radio" class="payment-option" name="emi_month" value="3">
                                <span>3 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                            </label>
                        </li>
                        <li class="list-group-item border-0 pb-0 emi-li">
                            <label class="radio radio-primary">
                                <input type="radio" class="payment-option" name="emi_month" value="6">
                                <span>6 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                            </label>
                        </li>
                        <li class="list-group-item border-0 pb-0 emi-li">
                            <label class="radio radio-primary">
                                <input type="radio" class="payment-option" name="emi_month" value="9">
                                <span>9 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                            </label>
                        </li>
                        <li class="list-group-item border-0 pb-0 emi-li">
                            <label class="radio radio-primary">
                                <input type="radio" class="payment-option" name="emi_month" value="12">
                                <span>12 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                            </label>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="">Name</label>
                            <input type="text" class="form-control" name="name_on_card" value="" data-conekta="card[name]" placeholder="Nombre del tarjetahabiente" maxlength="50" size="50">
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
                    <div class="mt-16 form-group row">
                        <img class="col-md-3" src="{{ asset('images/payment/pago2.png') }}" />
                        <div class="col-md-9">
                            <label class="radio radio-primary">
                                <input type="radio" name="cash_radio" value="0" checked><span>@lang('billing.c_radio_text')</span><span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name" id="cash_name" placeholder="Nombre" maxlength="50" value="{{ @$userData->user_name }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Phone number</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control phone-number" name="phone_number" id="cash_phone_number" placeholder="Teléfono" maxlength="13" minlength="10">
                        </div>
                    </div>
                </div>
                <div class="bank-transfer selectt" style="display: none;">
                    <div class="mt-16 form-group row">
                        <img class="col-md-3" src="{{ asset('images/payment/pago3.png') }}" />
                        <div class="col-md-9">
                            <label class="radio radio-primary">
                                <input type="radio" name="bt_radio" value="0" checked><span>@lang('billing.bt_radio_text')</span><span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="bt_name" id="bt_name" placeholder="Nombre" maxlength="50" value="{{ @$userData->user_name }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3">Phone number</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control phone-number" name="bt_phone_number" id="bt_phone_number" placeholder="Teléfono" maxlength="13" minlength="10">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" type="button" onclick="onlinePaymentConfirmation()">Deposit Funds</button>
        </div>
    </form>
@endif
</div>