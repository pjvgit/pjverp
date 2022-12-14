<div>
    <div class="apply-funds-summary-dialog">
        <?php 
        if(!$SavedInvoices->isEmpty()){?>
        <div>
            <div> <b>Funds were applied to your invoices:</b></div> <br>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Case</th>
                            <th>Invoice Number</th>
                            <th>Invoice Status</th>
                            <th>Invoice Balance</th>
                            <th>Account Balance</th>
                        </tr>
                    </thead>
                    <tbody class="apply-funds-invoices">
                        <?php foreach($SavedInvoices as $k=>$v){?>
                        <tr>
                            <td>{{$v->case_title}}</td>
                            <td>{{sprintf('%06d', $v->id)}}</td>
                            <td>{{$v->status}}</td>
                            <td>${{number_format($v->due_amount,2)}}</td>
                            <td>${{number_format($v->trust_account_balance,2)}}</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <?php } ?>
        <div id="apply-funds-failures">
            <div>
                <b>Funds were not applied to the following invoices:</b>
            </div><br>
            <ul>
                <?php foreach($NonSavedInvoices as $k=>$v){?>
                <li>{{sprintf('%06d', $v->id)}} ({{$v->case_title}})</li>
                <?php } ?>
            </ul>
            <div>Funds cannot be applied to invoices if
                1) it is forwarded,
                2) there is no trust account,
                3) the associated trust account has insufficient funds,
                or 4) the associated case has multiple trust accounts.
            </div>
        </div>
    </div>
</div>
