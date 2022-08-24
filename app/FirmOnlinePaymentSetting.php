<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FirmOnlinePaymentSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'is_accept_online_payment', 'public_key', 'private_key', 'created_by', 'updated_by', 'is_accept_interest_free_monthly_payment', 'paypal_public_key',
        'paypal_private_key'
    ];

    protected $appends = [];
}
