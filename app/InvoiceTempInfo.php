<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class InvoiceTempInfo extends Model
{
    protected $fillable = ['invoice_unique_id', 'client_id', 'case_id', 'account_type', 'trust_account_type', 'applied_amount', 'show_account_history', 
            'created_by', 'deposit_into'];    
}
