<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
class InvoiceOnlinePayment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_id', 'user_id', 'payment_method', 'card_emi_month', 'conekta_order_id', 'conekta_charge_id', 'conekta_customer_id', 'conekta_payment_status', 
        'conekta_payment_reference_id', 'created_by', 'invoice_history_id', 'amount', 'conekta_reference_expires_at'
    ];

    protected $append = ['expires_date', 'expires_time'];

    /**
     * Get conekta reference expires date
     */
    public function getExpiresDateAttribute()
    {
        return Carbon::parse($this->conekta_reference_expires_at)->format('d-m-Y');
    }

    /**
     * Get conekta reference expires time
     */
    public function getExpiresTimeAttribute()
    {
        return Carbon::parse($this->conekta_reference_expires_at)->format('H:i');
    }
}
