<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class UserOnlinePaymentCustomerDetail extends Model
{
    protected $fillable = ['id', 'client_id', 'conekta_customer_id', 'created_by'];

}