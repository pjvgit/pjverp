<div class="alert alert-danger" role="alert" id="error-alert" style="display:none;">
    <span class="error-text"><strong class="text-capitalize">Error!</strong></span>
    <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<form class="pay-online-payment" id="pay_online_payment" name="pay_online_payment" method="POST">
    @csrf
    <input type="text" name="type" value="fund" >
    <input type="text" name="payable_record_id" value="{{ $userData->id }}" >
    <span id="response"></span>
            @csrf
            <input type="hidden" id="trust_account_id" name="non_trust_account" value="{{$userData['uid']}}">
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
                <div class="col-md-6 form-group">
                    <label for="firstName1">Payment Method</label>
                    <select class="form-control caller_name select2" id="payment_method1" name="payment_method" style="width: 100%;" placeholder="Select or enter a name...">
                        <option></option>
                        @foreach($paymentMethod as $key=>$val)
                            <option value="{{$val}}"> {{$val}}</option>
                        @endforeach
                    </select>
                    <span id="ptype"></span>
                </div>
                <div class="col-md-6 form-group">
                    <label for="firstName1">Date</label>
                    <input class="form-control input-date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="payment_date" maxlength="250" name="payment_date" type="text">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="firstName1">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input class="form-control amountFirst" style="width:50%; " maxlength="20" name="amount" id="amountFirst" value="" type="text" aria-label="Amount (to the nearest dollar)">
                        <small>&nbsp;</small>
                        <div class="input-group col-sm-9" id="TypeError"></div>
                        <span id="amt"></span>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label for="firstName1">Notes</label>
                    <input class="form-control" value="" id="notes" name="notes" type="text">
                </div>
            </div>
            <hr>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="button" onclick="trustPaymentConfitmation()">Deposit Funds</button>
            </div>
    
</form>