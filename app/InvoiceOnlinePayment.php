<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
class InvoiceOnlinePayment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_id', 'user_id', 'payment_method', 'card_emi_month', 'conekta_order_id', 'conekta_charge_id', 'conekta_customer_id', 'conekta_payment_status', 
        'conekta_payment_reference_id', 'created_by', 'invoice_history_id', 'amount'
    ];
}
