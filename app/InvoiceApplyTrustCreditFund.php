<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceApplyTrustCreditFund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id', 'client_id', 'case_id', 'account_type', 'applied_amount', 'deposite_into', 'show_trust_account_history', 'show_credit_account_history', 
        'created_by', 'updated_by', 'history_last_id', 'total_balance', 'allocate_applied_amount'
    ];

    protected $appends = [];

    /**
     * Get the client that owns the InvoiceApplyTrustCreditFund
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the userAdditionalInfo that owns the InvoiceApplyTrustCreditFund
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userAdditionalInfo()
    {
        return $this->belongsTo(UsersAdditionalInfo::class, 'client_id', 'user_id');
    }
}
