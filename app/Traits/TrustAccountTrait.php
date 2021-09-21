<?php
 
namespace App\Traits;

use App\CaseClientSelection;
use App\CaseMaster;
use App\TrustHistory;
use Illuminate\Support\Facades\Log;

trait TrustAccountTrait {
    /**
     * Update allocated trust balance when trust deposit refund/delete
     */
    public function refundAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->refund_amount);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->refund_amount);
    }
 
    /**
     * Update allocated trust balance when trust deposit refund
     */
    public function deleteRefundedAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->refund_amount);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->refund_amount);
    }

    /**
     * Update allocated trust balance when trust deposit refund/delete
     */
    public function deleteAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->amount_paid);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->amount_paid);
    }
}
 