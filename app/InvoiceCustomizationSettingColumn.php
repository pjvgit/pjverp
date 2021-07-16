<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceCustomizationSettingColumn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'inv_customiz_setting_id', 'billing_type', 'date', 'employee', 'item', 'notes', 'amount', 'activity', 'hour', 'line_total', 'expense',
        'quantity','created_by', 'updated_by'
    ];

    protected $appends = [];
}
