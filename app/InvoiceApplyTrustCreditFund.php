<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceApplyTrustCreditFund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id', 'client_id', 'case_id', 'account_type', 'applied_amount', 'deposite_into', 'show_trust_account_history', 'show_credit_account_history', 'created_by', 'updated_by'
    ];

    protected $appends = [];
}
