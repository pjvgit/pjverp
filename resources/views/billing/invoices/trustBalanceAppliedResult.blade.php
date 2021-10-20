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
                            <td>
                                @if($v->is_lead_invoice == "yes")
                                {{ @$v->leadAdditionalInfo->potential_case_title }}
                                @else
                                {{ @$v->case->case_title }}
                                @endif
                            </td>
                            <td>{{ $v->id }}</td>
                            <td>{{ $v->status }}</td>
                            <td>${{ number_format($v->due_amount ?? 0,2) }}</td>
                            <td>
                                @if(isset($fund_type) && $fund_type == "credit")
                                ${{ number_format(@$v->portalAccessUserAdditionalInfo->credit_account_balance ?? 0,2) }}
                                @else
                                ${{ number_format(@$v->portalAccessUserAdditionalInfo->unallocate_trust_balance ?? 0,2) }}
                                @endif
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <?php } ?>
        <div id="apply-funds-failures">
            @if(isset($fund_type) && $fund_type == "credit")
                <div>
                    <b>Funds were not applied to the following invoices:</b>
                </div><br>
                <ul>
                    @foreach($NonSavedInvoices as $k=>$v)
                        <li>{{sprintf('%06d', $v->id)}}
                        @if($v->is_lead_invoice == "yes")
                            ({{ @$v->leadAdditionalInfo->potential_case_title }})
                        @else
                            ({{@$v->case->case_title}})
                        @endif
                        </li>
                    @endforeach
                </ul>
                <div>
                    Funds cannot be applied to invoices if 
                    1) it is forwarded, 
                    2) there is no credit account, 
                    3) the associated credit account has insufficient funds, or 
                    4) the associated case has multiple credit accounts.
                </div>
            @else
                <div>
                    <b>Funds were not applied to the following invoices:</b>
                </div><br>
                <ul>
                    <?php foreach($NonSavedInvoices as $k=>$v){?>
                    <li>{{sprintf('%06d', $v->id)}} 
                        @if($v->is_lead_invoice == "yes")
                            ({{ @$v->leadAdditionalInfo->potential_case_title }})
                        @else
                            ({{@$v->case->case_title}})
                        @endif
                    </li>
                    <?php } ?>
                </ul>
                <div>Funds cannot be applied to invoices if
                    1) it is forwarded,
                    2) there is no trust account,
                    3) the associated trust account has insufficient funds,
                    or 4) the associated case has multiple trust accounts.
                </div>
            @endif
        </div>
    </div>
</div>
