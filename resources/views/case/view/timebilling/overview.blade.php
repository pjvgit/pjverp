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
                                <p class="">Un-Invoiced</p>
                                <h4 class="font-weight-bold">
                                    <?php $totalBills=$timeEntryData['billable_entry']+$expenseEntryData['billable_entry'];?>
                                    ${{number_format($totalBills,2)}}
                                </h4>
                            </div>
                           
                            <div class="pl-1 col-8">
                                <a class="btn btn-primary btn-rounded m-1 case-details-add-invoice" href="{{ route('bills/invoices/new') }}?court_case_id={{$CaseMaster['case_id']}}&token={{App\Http\Controllers\CommonController::getToken()}}">Add Invoice</a>
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
                                                <td class="pl-1" style="width: 33%;">${{number_format($CaseMaster['billing_amount'],2)}}</td>
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
                                                href="{{BASE_URL}}court_cases/{{$CaseMaster['case_unique_number']}}/time_entries">View Time Entries</a></td>
                                    </tr>
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">Expenses</td>
                                        <td class="pl-1" style="width: 33%;">
                                            ${{number_format($expenseEntryData['billable_entry'],2)}}</td>
                                        <td class="pl-1" style="width: 33%;"><a
                                                href="{{BASE_URL}}court_cases/{{$CaseMaster['case_unique_number']}}/expenses">View Expenses</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-3 card">
                    <div class="card-header">
                        <h4><strong>Trust Balances</strong></h4>
                    </div>
                    <div class="card-body">
                        <div class="pl-2 mb-2 row ">
                            <div class="col-4">
                                <p class="mb-1">Trust Balance</p>
                                <?php $TrustAmt=$trustUSers['totalTrustSum'];?>
                                <h4 class="font-weight-bold">${{number_format($TrustAmt,2)}}</h4>
                            </div>
                            <div id="retainer-request-trust-btns" class="pl-0 col-4">
                                <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom"
                                    href="javascript:;">
                                    <button class="btn btn-primary btn-rounded m-1" type="button" id="button"
                                        onclick="addRequestFundPopup();">Request Funds</button></a>
                            </div>
                            <div class="pl-0 col-4">
                                <a data-toggle="modal" data-target="#depositIntoTrustForCasePoppup"
                                    data-placement="bottom" href="javascript:;" onclick="depositIntoTrustForCase();">
                                    <button type="button" class="btn btn-primary btn-rounded m-1">Deposit Into
                                        Trust</button>
                                </a>


                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php
                                    array_pop($trustUSers);
                                    foreach($trustUSers as $kk=>$vv){?>
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">
                                            <?php if($vv['user_level']=="2"){?>
                                            <a
                                                href="{{BASE_URL}}contacts/clients/{{$vv['uid']}}">{{$vv['user_name']}}</a>
                                            <?php } else if($vv['user_level']=="4"){?>
                                            <a
                                                href="{{BASE_URL}}contacts/companies/{{$vv['uid']}}">{{$vv['user_name']}}</a>
                                            <?php } ?>
                                        </td>
                                        <td class="pl-1" style="width: 33%;">
                                            ${{number_format($vv['trust_account_balance'],2)}}</td>
                                        <td class="pl-1" style="width: 33%;"></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
                                    <?php }else{ ?>
                                        <tr>
                                        <td class="pl-1" style="width: 33%;">{{$m}}</td>
                                        <td class="pl-1" style="width: 33%;">${{number_format($CaseMaster['billing_amount'],2)}}</td>
                                        <td class="pl-1" style="width: 33%;"></td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td class="pl-1" style="width: 33%;">Billing Contact</td>
                                        <td class="pl-1" style="width: 33%;">
                                            <?php 
                                                if(!empty($caseBiller) && $displayLevel=="2"){?>
                                                    <a href="{{BASE_URL}}contacts/clients/{{$caseBiller['uid']}}">{{$displayName}}</a>
                                                <?php } else if(!empty($caseBiller) &&  $displayLevel=="4"){?>
                                                    <a href="{{BASE_URL}}contacts/companies/{{$caseBiller['uid']}}">{{$displayName}}</a>
                                                <?php } ?> 
                                        </td>
                                        <td class="pl-1" style="width: 33%;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                            <a data-toggle="modal" data-target="#editBillingContactPopup"
                        data-placement="bottom" href="javascript:;" onclick="editBillingContactPopup();">
                        <button type="button" class="edit-court-case-billing btn btn-outline-secondary">Edit</button>
                            </a>
                            <a
                            href="{{BASE_URL}}court_cases/{{$CaseMaster['case_unique_number']}}/case_link" class="ml-3 btn btn-outline-secondary">Change Case Rate</a>
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
                                            ${{number_format($TrustAmt,2)}}</td>
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
                                            $currentBalance=$TrustAmt-$totalBills;
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
            </div>
        </div>
    </div>
</div>

<div id="depositIntoTrustForCasePoppup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
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
</div>

<div id="editBillingContactPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Edit Billing Information for {{$CaseMaster['case_title']}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="editBillingContactPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>


@section('page-js-inner')
<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();

    function depositIntoTrustForCase() {
        $('.showError').html('');
        $("#preloader").show();
        $("#depositIntoTrustForCaseArea").html('');
        $("#depositIntoTrustForCaseArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/depositIntoTrustByCase",
            data: {
                "case_id": "{{$CaseMaster['case_id']}}"
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();

                    $("#preloader").hide();
                    $("#depositIntoTrustForCaseArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#depositIntoTrustForCaseArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();

            }
        })
    }
    function editBillingContactPopup() {
        $('.showError').html('');
        $("#editBillingContactPopupArea").html('');
        $("#editBillingContactPopupArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/overview/editBillingContactPopup",
            data: {"case_id": "{{$CaseMaster['case_id']}}"},
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();

                    $("#editBillingContactPopupArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#editBillingContactPopupArea").html(res);
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();

            }
        })
    }
</script>
@stop
