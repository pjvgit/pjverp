<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class ContractUser extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "contract_user";
    public $primaryKey = 'id';

    protected $fillable = [
        'first_name','last_name', 'email','user_status','user_title'
    ];

}
