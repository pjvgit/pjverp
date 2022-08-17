<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
class UserTrustCreditFundOnlinePayment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'payment_method', 'card_emi_month', 'conekta_order_id', 'conekta_charge_id', 'conekta_customer_id', 'conekta_payment_status', 
        'conekta_payment_reference_id', 'created_by', 'invoice_history_id', 'amount', 'conekta_reference_expires_at', 'firm_id', 'conekta_order_object', 'paid_at', 
        'trust_history_id', 'credit_history_id', 'status', 'fund_type', 'allocated_to_case_id'
    ];

    protected $casts = ['conekta_order_object' => 'array'];

    /**
     * Get the client that owns the InvoiceOnlinePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the firmDetail that owns the RequestedFundOnlinePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firmDetail()
    {
        return $this->belongsTo(Firm::class, 'firm_id');
    }

    /**
     * Get the case that owns the UserTrustCreditFundOnlinePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'allocated_to_case_id');
    }
}