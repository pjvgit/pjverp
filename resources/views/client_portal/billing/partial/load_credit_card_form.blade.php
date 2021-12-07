<h2 class="pb-2">Invoice #{{ $invoice->id }}</h2>
<h3 class="border-bottom border-gray pb-2">Total: {{ ($month == 0) ? 'One payment of '.$invoice->due_amount : $month.' payments of '. invoiceMonthlyPaymentAmount($invoice->due_amount, $month) }}</h3>
<div class="row pt-3">
    <div class="col-md-8">
        <div class="card text-left">
            <div class="card-body">
                <div class="offset-2">
                    <h4 class="card-title mb-3">{{ ($month == 0) ? 'Pay with Visa, MasterCard or American Express Credit or Debit Card' : $month.' interest free Monthly Payments with credit card' }}</h4>
                    <form id="credit_card_form" method="POST" action="">
                        <div class="form-group">
                            <label class="col-md-3">Name</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="user_name" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Phone number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="user_name" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Credit card number</label>
                            <div class="col-md-9">
                                <input type="text" maxlength="16" size="16" data-conekta="card[number]" class="number credit-card-number form-control" id="card_number" inputmode="numeric" autocomplete="off" autocompletetype="cc-number" x-autocompletetype="cc-number" placeholder="•••• •••• •••• ••••">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">Expiration date</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="user_name" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">CVV</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="user_name" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3"></label>
                            <div class="col-md-9">
                                <button type="submit" class="btn btn-primary">Pay now!</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>