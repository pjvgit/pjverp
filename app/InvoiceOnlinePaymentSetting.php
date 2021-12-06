<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceOnlinePaymentSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'is_accept_online_payment', 'public_key', 'private_key', 'created_by', 'updated_by'
    ];

    protected $appends = [];
}
