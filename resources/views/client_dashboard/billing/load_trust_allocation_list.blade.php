@forelse ($case as $ckey => $citem)
    <tr class="trust-allocation-row trust-allocation-row-16363231">
        <td class="court-case-name">
            <div><a href="{{ route('info', $citem->case_unique_number) }}" class="court-case">{{ ucfirst($citem->case_title) }}</a></div>
        </td>
        @php
            $caseClient = $citem->caseAllClient->where('id', @$UsersAdditionalInfo->user_id)->first();
            $minTrustBalance = 0.00; $allocateTrustBalance = 0.00;
            if($caseClient) {
                if($caseClient->pivot->minimum_trust_balance)
                    $minTrustBalance = $caseClient->pivot->minimum_trust_balance;

                if($caseClient->pivot->allocated_trust_balance)
                    $allocateTrustBalance = $caseClient->pivot->allocated_trust_balance;
            }
        @endphp
        <td class="trust-allocation">
            @can(['client_add_edit', 'billing_add_edit'])
            <a href="javascript:;" data-toggle="modal" data-target="#trust_allocation_modal" class="balance-allocation-link btn-link" data-case-id="{{ $citem->id }}" data-user-id="{{@$UsersAdditionalInfo->user_id}}" data-page="trust_allocation" >
                ${{ number_format($allocateTrustBalance, 2) }}
            </a>
            @else                
                ${{ number_format($allocateTrustBalance, 2) }}
            @endcan
        </td>
        <td class="minimum-trust-balance">
            <div class="row col-md-12 setup-btn-div">
                @if($minTrustBalance > 0)
                    ${{ number_format($minTrustBalance, 2) }}
                @else
                    <span>Setup Minimum Trust Balance</span>
                @endif
                <button type="button" class="edit-minimum-trust p-0 ml-2 btn btn-link" @cannot(['client_add_edit', 'billing_add_edit']) disabled @endcannot><i class="fas fa-pen text-black-50 c-pointer"></i></button>
            </div>
            <div class="row setup-input-div" style="display: none;">
                <form class="setup-min-trust-balance-form">
                    @csrf
                <div class="col-md-8">
                    <input type="hidden" name="case_id" value="{{ $citem->id }}" >
                    <input type="hidden" name="client_id" value="{{ @$UsersAdditionalInfo->user_id }}" >
                    <div class="row form-group">
                        <div class="col-12 col-sm-12">
                            <div class="w-auto minimum-trust-balance-edit input-group">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input placeholder="Minimum Trust Allocation" required="" name="min_balance" class="form-control number" maxlength="15" value="{{ number_format($minTrustBalance, 2) }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="mx-0 save-minimum-trust-balance btn btn-secondary">Save</button>
                </div>
                </form>
            </div>
        </td>
        <td class="minimum-trust-warning">
            @if ($minTrustBalance > $allocateTrustBalance)
                <span class="text-danger">*Below Minimum Trust</span>
            @endif
        </td>
        <td class="request-funds">
            @if($minTrustBalance > $allocateTrustBalance)
            <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;"
                onclick="addRequestFundPopup(null, 'yes');">
                <button type="button" class="mx-1 btn btn-link">Request Fund</button>
            </a>
            @endif
        </td>
    </tr>
@empty
@endforelse
@if($UsersAdditionalInfo)
    <tr class="trust-allocation-row trust-allocation-row-unallocated">
        <td class="court-case-name">
            <div><span>Unallocated</span></div>
        </td>
        <td class="trust-allocation"><span><div>${{ number_format(@$UsersAdditionalInfo->unallocate_trust_balance, 2) }}</div></span></td>
        <td class="minimum-trust-balance">
            <div class="row col-md-12 setup-btn-div">
                @if(@$UsersAdditionalInfo->minimum_trust_balance > 0)
                    ${{ number_format(@$UsersAdditionalInfo->minimum_trust_balance, 2) }}
                @else
                    <span>Setup Minimum Trust Balance</span>
                @endif
                <button type="button" class="edit-minimum-trust p-0 ml-2 btn btn-link" @cannot(['client_add_edit', 'billing_add_edit']) disabled @endcannot><i class="fas fa-pen text-black-50 c-pointer"></i></button>
            </div>
            <div class="row setup-input-div" style="display: none;">
                <form class="setup-min-trust-balance-form">
                    @csrf
                <div class="col-md-8">
                    <input type="hidden" name="client_id" value="{{ @$UsersAdditionalInfo->user_id }}" >
                    <div class="row form-group">
                        <div class="col-12 col-sm-12">
                            <div class="w-auto minimum-trust-balance-edit input-group">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input placeholder="Minimum Trust Allocation" required="" name="min_balance" class="form-control number" maxlength="15" value="{{ number_format(@$UsersAdditionalInfo->minimum_trust_balance, 2) }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="mx-0 save-minimum-trust-balance btn btn-secondary">Save</button>
                </div>
                </form>
            </div>
        </td>
        <td class="minimum-trust-warning">
            @if (@$UsersAdditionalInfo->minimum_trust_balance > @$UsersAdditionalInfo->unallocate_trust_balance)
                <span class="text-danger">*Below Minimum Trust</span>
            @endif
        </td>
        <td class="request-funds">
            @if(@$UsersAdditionalInfo->minimum_trust_balance > @$UsersAdditionalInfo->unallocate_trust_balance)
            <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;"
                onclick="addRequestFundPopup(null, 'yes');">
                <button type="button" class="mx-1 btn btn-link">Request Fund</button>
            </a>
            @endif
        </td>
    </tr>
@endif