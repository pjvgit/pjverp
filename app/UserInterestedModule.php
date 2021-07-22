<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInterestedModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'user_id', 'interedted_module_1', 'interested_module_2', 'looking_to_get_out_this_trial', 'created_by'
    ];
}
