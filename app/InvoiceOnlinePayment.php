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
        'conekta_payment_reference_id', 'created_by', 'invoice_history_id', 'amount', 'conekta_reference_expires_at', 'firm_id', 'conekta_order_object', 'paid_at',
        'status', 'refund_reference_id'
    ];

    protected $casts = ['conekta_order_object' => 'array'];

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

    /**
     * Get the invoice that owns the InvoiceOnlinePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id');
    }

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
     * Get the firmDetail that owns the InvoiceOnlinePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firmDetail()
    {
        return $this->belongsTo(Firm::class, 'firm_id');
    }
}