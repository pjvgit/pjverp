<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanHistory extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "plan_history";
    public $primaryKey = 'id';
    protected $fillable = [
        'user_id','start_date','end_date'
    ];
}
