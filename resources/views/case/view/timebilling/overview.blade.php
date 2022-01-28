<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
<div id="overview_page" class="case_info_page col-12" style="">
    <div id="new-case-billing-overview">
        <div class="mt-2 row ">
            <div class="pr-0 col-8">
                <div class="card">
                    <div class="card-header">
                        <h4><strong>Un-Invoiced Balances</strong></h4>
                    </div>
                    <div class="card-body">
                        <div class="pl-2 row ">
                            <div class="col-4">
                                <p class="" data="{{$CaseMaster->uninvoiced_balance}}">Un-Invoiced</p>                                
                                <h4 class="font-weight-bold">
                                    <?php 
                                    $flatFeeTotal = 0;
                                    if(in_array($CaseMaster['billing_method'],["flat","mixed"])){
                                        $flatFeeTotal = ($CaseMaster->billing_amount - $flatFeeEntryData['billable_entry']);
                                        $flatFeeTotal = ($flatFeeTotal > 0 ) ?  $flatFeeTotal : 0;
                                    }
                                    $totalBills=$timeEntryData['billable_entry']+$expenseEntryData['billable_entry']+$flatFeeTotal;?>
                                    ${{number_format($totalBills,2)}}
                                </h4>
                            </div>
                           
                            <div class="pl-1 col-8">
                                @can('case_add_edit')
                                @if(!empty($caseBiller) && auth()->user()->hasDirectPermission('billing_add_edit'))
                                <a class="btn btn-primary btn-rounded m-1 case-details-add-invoice" href="{{ route('bills/invoices/new') }}?court_case_id={{$CaseMaster['case_id']}}&token={{App\Http\Controllers\CommonController::getToken()}}&contact={{$caseBiller['uid']}}">Add Invoice</a>
                                @else
                                <a class="btn btn-primary btn-rounded m-1" data-toggle="modal" data-target="#editBillingContactPopup" data-placement="bottom" href="javascript:;" onclick="editBillingContactPopup();">Setup Case Billing Information</a>
                                @endif
                                @endcan
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-lg table-hover">
                                <tbody>
                                    <?php
                                    if(!empty($caseBiller)){
                                      $displayLevel=$caseBiller['user_level'];
                                        if(in_array($CaseMaster['billing_method'],["flat","mixed"])){  //2:Hourly
                                            ?>
                                            <tr>
                                                <td class="pl-1" style="width: 33%;">Case Fee</td>
                                                <td class="pl-1" style="width: 33%;">${{number_format($flatFeeTotal,2)}}</td>
                                                <td class="pl-1" style="width: 33%;"></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                    
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">Time Entries</td>
                                        <td class="pl-1" style="width: 33%;">
                                            ${{number_format($timeEntryData['billable_entry'],2)}}</td>
                                        <td class="pl-1" style="width: 33%;"><a
                                                href="{{ route('time_entries', $CaseMaster['case_unique_number']) }}">View Time Entries</a></td>
                                    </tr>
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">Expenses</td>
                                        <td class="pl-1" style="width: 33%;">
                                            ${{number_format($expenseEntryData['billable_entry'],2)}}</td>
                                        <td class="pl-1" style="width: 33%;"><a
                                                href="{{ route('expenses',$CaseMaster['case_unique_number']) }}">View Expenses</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3 card">
                    <div class="card-header">
                        <h4><strong>Trust and Credit Balances</strong></h4></div>
                        <div class="card-body">
                            <div data-testid="case-billing-balances-container">
                                <div>
                                    <div class="pl-2 mb-2 row ">
                                        <div class="col-6">
                                            <h4 class="mb-1 font-weight-bold">Trust Balances</h4></div>
                                        @can('billing_add_edit')
                                        <div id="retainer-request-trust-btns" class="pl-0 col-3">
                                            <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;">
                                                <button class="btn btn-primary btn-rounded m-1" type="button" id="button" onclick="addRequestFundPopup({{ $CaseMaster->case_id }});">Request Funds</button>
                                            </a>
                                        </div>
                                        <div class="pl-0 col-3">
                                            <a data-toggle="modal" data-target="#depositIntoTrust" data-placement="bottom" href="javascript:;" onclick="depositIntoTrust(null, {{ $CaseMaster->case_id }});"> 
                                                <button type="button" class="btn btn-primary btn-rounded m-1">Deposit Into Trust</button>
                                            </a>
                                        </div>
                                        @endcan
                                    </div>
                                    <div class="pl-2 mb-2 row ">
                                        <div class="col-12">
                                            <p class="mb-1 allocated" data-testid="allocate-funds-tooltip">Case Trust Balance (Allocated)
                                                <span id="allocated-trust" data-toggle="tooltip" data-html="true" title="<b>Case Trust Balance</b><br>Allocated Trust Fund Amount that is exclusively for this case"><i class="fas fa-info-circle ml-1"></i></span>
                                            </p>
                                            {{-- <h4 class="font-weight-bold" id="available-total-allocatedTrust-balance">${{ number_format($CaseMaster->total_allocated_trust_balance, 2) }}</h4></div> --}}
                                            <h4 class="font-weight-bold" id="available-total-allocatedTrust-balance">${{ number_format($caseClients->sum('allocated_trust_balance'), 2) }}</h4></div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <tbody>
                                                @forelse ($caseClients as $item)
                                                <tr>
                                                    <td class="pl-1" style="width: 33%;">
                                                        @if($item->user->user_level == 2)
                                                        <a href="{{ route('contacts/clients/view',$item->selected_user) }}">{{ $item->user->full_name }}</a>
                                                        @elseif($item->user->user_level == 4)
                                                        <a href="{{ route('contacts/companies/view',$item->selected_user) }}">{{ $item->user->full_name }}</a>
                                                        @endif
                                                    </td>
                                                    <td class="pl-1" style="width: 33%;">${{ number_format($item->allocated_trust_balance, 2) }}</td>
                                                    <td class="pl-1 text-danger" style="width: 33%;">
                                                    @if ($item->minimum_trust_balance > $item->allocated_trust_balance)
                                                    *Minimum Trust Balance<br>${{ number_format($item->minimum_trust_balance, 2) }}
                                                    @endif
                                                    </td>
                                                </tr>    
                                                @empty
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="pl-2 mb-2 row ">
                                        <div class="col-12">
                                            @php
                                                $totalUnallocated = 0.00; $totalCreditBalance = 0.00; $availableTrustBalance = 0.00;
                                                if(!empty($caseClients)) {
                                                    foreach ($caseClients as $cckey => $ccvalue) {
                                                        $totalUnallocated = $totalUnallocated + $ccvalue->user->userAdditionalInfo->unallocate_trust_balance ?? 0.00;
                                                        $totalCreditBalance = $totalCreditBalance + $ccvalue->user->userAdditionalInfo->credit_account_balance ?? 0.00;
                                                    }
                                                    $availableTrustBalance = $CaseMaster->total_allocated_trust_balance + $totalUnallocated;
                                                }
                                            @endphp                                            
                                            <p class="mb-1" data-testid="allocate-funds-tooltip">Client Trust Balance (Unallocated)
                                                <span id="unallocated-trust" data-toggle="tooltip" data-html="true" title="<b>Client Trust Balance</b><br>Unallocated Trust Fund Amount that is available for any of client's cases"><i class="fas fa-info-circle ml-1"></i></span>
                                            </p>
                                            <h4 class="font-weight-bold" id="available-total-unallocatedTrust-balance">${{ number_format($totalUnallocated, 2) }}</h4></div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <tbody>
                                                @forelse ($caseClients as $item)
                                                    <tr>
                                                        <td class="pl-1" style="width: 33%;">
                                                            @if($item->user->user_level == 2)
                                                            <a href="{{ route('contacts/clients/view',$item->selected_user) }}">{{ $item->user->full_name }}</a>
                                                            @elseif($item->user->user_level == 4)
                                                            <a href="{{ route('contacts/companies/view',$item->selected_user) }}">{{ $item->user->full_name }}</a>
                                                            @endif
                                                        </td>
                                                        <td class="pl-1" style="width: 33%;">${{ number_format($item->user->userAdditionalInfo->unallocate_trust_balance ?? 0, 2) }}</td>
                                                        <td class="pl-1 text-danger" style="width: 33%;">
                                                        @if ($item->user->userAdditionalInfo->minimum_trust_balance > $item->user->userAdditionalInfo->unallocate_trust_balance)
                                                        *Minimum Trust Balance<br>${{ number_format($item->user->userAdditionalInfo->minimum_trust_balance, 2) }}
                                                        @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @if(!empty(getInvoiceSetting()) && getInvoiceSetting()->is_non_trust_retainers_credit_account == 'yes')
                                    <div class="pl-2 mb-2 row ">
                                        <div class="col-6">
                                            <h4 class="mb-1 font-weight-bold">Credit Balances</h4></div>
                                        @can('billing_add_edit')
                                        <div id="retainer-request-credit-btns" class="pl-0 col-3">
                                            <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;">
                                                <button class="btn btn-primary btn-rounded m-1" type="button" id="button" onclick="addRequestFundPopup({{ $CaseMaster->case_id }});">Request Funds</button>
                                            </a>
                                        </div>
                                        <div class="pl-0 col-3">
                                            <a data-toggle="modal" data-target="#loadDepositIntoCreditPopup" data-placement="bottom" href="javascript:;" > 
                                                <button type="button" class="btn btn-primary btn-rounded m-1" onclick="loadDepositIntoCredit(this);" data-auth-user-id="{{ auth()->id() }}" data-client-id="{{ @$client_id }}" data-case-id="{{ $CaseMaster->case_id }}">Deposit into Credit</button>
                                            </a>
                                        </div>
                                        @endcan
                                    </div>
                                    <div class="pl-2 mb-2 row ">
                                        <div class="col-12">
                                            <p class="mb-1">Credit Balance</p>
                                            <h4 class="font-weight-bold" id="available-total-credit-balance">${{ number_format($totalCreditBalance, 2) }}</h4></div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <tbody>
                                                @forelse ($caseClients as $item)
                                                <tr>
                                                    <td class="pl-1" style="width: 33%;">
                                                        @if($item->user->user_level == 2)
                                                        <a href="{{ route('contacts/clients/view',$item->selected_user) }}">{{ $item->user->full_name }}</a>
                                                        @elseif($item->user->user_level == 4)
                                                        <a href="{{ route('contacts/companies/view',$item->selected_user) }}">{{ $item->user->full_name }}</a>
                                                        @endif
                                                    </td>
                                                    <td class="pl-1" style="width: 33%;">${{ number_format($item->user->userAdditionalInfo->credit_account_balance ?? 0, 2) }}</td>
                                                    <td class="pl-1" style="width: 33%;"></td>
                                                </tr>
                                                @empty
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                </div>

                <div class="mt-3 card">
                    <div class="card-header">
                        <h4><strong>Case Billing Information</strong></h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg table-hover">
                                <tbody>
                                    <?php
                                      $typeOfMethod=array('hourly'=>'Hourly','contingency'=>'Contingency','flat'=>'Flat Fee','mixed'=>'Mix of Flat Fee and Hourly','pro_bono'=>'Pro Bono',''=>'');
                                      $m=$typeOfMethod[$CaseMaster['billing_method']];
                                 

                                    if(!empty($caseBiller)){
                                        $displayName=$caseBiller['first_name'].' '.$caseBiller['middle_name'].' '.$caseBiller['last_name'];
                                        $displayLevel=$caseBiller['user_level'];
                                      
                                    }else{
                                        $displayName="";
                                        $displayLevel="";
                                    }
                                      
                                    ?>
                                    <tr>
                                        <td class="border-top-0 pl-1" style="width: 33%;">Fee Structure</td>
                                        <td class="border-top-0 pl-1" style="width: 33%;">{{$m}}</td>
                                        <td class="border-top-0" style="width: 33%;"></td>
                                    </tr>
                                    <?php
                                    if(!empty($CaseMaster) && in_array($CaseMaster['billing_method'],['flat','mixed'])){?>
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">Flat Fee</td>
                                        <td class="pl-1" style="width: 33%;">${{number_format($CaseMaster['billing_amount'],2)}}</td>
                                        <td class="pl-1" style="width: 33%;"></td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">Billing Contact</td>
                                        <td class="pl-1" style="width: 33%;">
                                            <?php 
                                                if(!empty($caseBiller) && $displayLevel=="2"){?>
                                                    <a href="{{ route('contacts/clients/view',$caseBiller['uid']) }}">{{$displayName}}</a>
                                                <?php } else if(!empty($caseBiller) &&  $displayLevel=="4"){?>
                                                    <a href="{{ route('contacts/companies/view',$caseBiller['uid']) }}">{{$displayName}}</a>
                                                <?php } ?> 
                                        </td>
                                        <td class="pl-1" style="width: 33%;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @can('case_add_edit')
                            <a data-toggle="modal" data-target="#editBillingContactPopup"
                        data-placement="bottom" href="javascript:;" onclick="editBillingContactPopup();">
                        <button type="button" class="edit-court-case-billing btn btn-outline-secondary">Edit</button>
                            </a>
                            @can('billing_add_edit')
                            <a
                            href="{{ route('case_link',$CaseMaster['case_unique_number']) }}" class="ml-3 btn btn-outline-secondary">Change Case Rate</a>
                            @endcan
                        @endcan
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="mb-3 card">
                    <div class="card-header">
                        <h4><strong>Case Billing Totals</strong></h4>
                    </div>
                    <div class="mb-2 card-body" style="font-size: 14px;">
                        <div class="table-responsive">
                            <table class="table table-lg table-hover">
                                <tbody>
                                    <tr>
                                        <td id="total_amount_collected align-items-center" class="border-top-0">Total
                                            Amount Collected  <span id="why-date-range" data-toggle="tooltip" data-placement="top" title="" data-original-title="This amount reflects all payments made into Operating. It includes amounts transferred from Trust or Credit through payments made on invoices.">
                                                <i aria-hidden="true" class="fa fa-question-circle ml-1"></i>
                                            </span></td>
                                        <td class="border-top-0 text-right align-bottom">${{number_format($InvoicesCollectedTotal,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-top-0">Invoices Awaiting Payment</td>
                                        <td class="border-top-0 text-right">${{number_format($InvoicesPendingTotal,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-top-0">Total Invoiced Amount</td>
                                        <td class="text-right"><span class="font-weight-bold h5 ">${{number_format($InvoicesTotal,2)}}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mb-3 card">
                    <div class="card-header">
                        <h4><strong>Running Trust Balance</strong></h4>
                    </div>
                    <div class="card-body" style="font-size: 14px;">
                        <div class="table-responsive">
                            <table class="table table-lg table-hover">
                                <tbody>
                                    <tr>
                                        <td class="border-top-0">Available Trust Balance</td>
                                        <td id="available-running-total-trust-balance" class="text-right border-top-0">
                                            ${{number_format($availableTrustBalance, 2)}}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-top-0">Un-Invoiced Balance</td>
                                        <td class="border-top-0 text-right">-${{number_format($totalBills,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-top-0">Running Trust Balance
                                            <span id="why-date-range" data-toggle="tooltip" data-placement="top" title="" data-original-title="This is the available trust balance minus any uninvoiced balances.">
                                                <i aria-hidden="true" class="fa fa-question-circle ml-1"></i>
                                            </span>
                                        <td class="text-right align-bottom">
                                            <?php
                                            $currentBalance=$availableTrustBalance-$totalBills;
                                            if($currentBalance>0){
                                                ?> <span class="font-weight-bold h5 text-success">${{number_format($currentBalance,2)}}</span><?php
                                            }else{
                                                ?> <span class="font-weight-bold h5 text-danger">-${{number_format(abs($currentBalance), 2)}}</span><?php
                                            }
                                            ?>
                                           
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if(!empty(getInvoiceSetting()) && getInvoiceSetting()->is_non_trust_retainers_credit_account == 'yes')
                <div class="mb-3 card">
                    <div class="card-header">
                        <h4><strong>Running Credit Balance</strong></h4></div>
                    <div class="card-body" style="font-size: 14px;">
                        <div class="table-responsive">
                            <table class="table table-lg table-hover">
                                <tbody>
                                    <tr>
                                        <td class="border-top-0">Available Credit Balance</td>
                                        <td id="available-running-total-credit-balance" class="border-top-0 text-right">${{ number_format($totalCreditBalance, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-top-0">Un-Invoiced Balance</td>
                                        <td class="border-top-0 text-right">-${{ number_format($totalBills, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border-top-0">Running Credit Balance
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="This is the available credit balance minus any uninvoiced balances.">
                                                <i aria-hidden="true" class="fa fa-question-circle ml-1"></i>
                                            </span>
                                        </td>
                                        <td class="text-right align-bottom">
                                            @php
                                                $currentBalance = $totalCreditBalance - $totalBills;
                                            @endphp
                                            @if($currentBalance > 0)
                                                <span class="font-weight-bold h5 text-success">${{number_format($currentBalance,2)}}</span>
                                            @else
                                                <span class="font-weight-bold h5 text-danger">-${{number_format(abs($currentBalance), 2)}}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Made common code for trust deposit --}}
{{-- <div id="depositIntoTrustForCasePoppup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Into Trust</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="depositIntoTrustForCaseArea">
                </div>
            </div>
        </div>
    </div>
</div> --}}

{{-- For add fund request --}}
<div id="addRequestFund" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-xl ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Request Funds</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="addRequestFundArea">
                </div>
            </div>
        </div>
    </div>
</div>

@include('client_dashboard.billing.credit_history_modal')

@include('case.view.timebilling.billingContactPopup')


@section('page-js-inner')
<script src="{{ asset('assets\js\custom\client\creditfund.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();

    $('#depositIntoTrustAccount').on('hidden.bs.modal', function () {
        window.location.reload();
    });

    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;
    }
    $('#hiddenLable').hide();
</script>
@stop