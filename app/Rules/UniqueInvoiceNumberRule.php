<?php

namespace App\Rules;

use App\Invoices;
use App\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;

class UniqueInvoiceNumberRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $invoice = Invoices::where('firm_id', auth()->user()->firm_name)->where('unique_invoice_number', request('invoice_number_padded'));
        if(request('invoice_id')) {
            $invoice = $invoice->where('id', '!=', request('invoice_id'));
        }
        return ($invoice->count()) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invoice number is already taken.';
    }
}
