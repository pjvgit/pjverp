<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineLeadSubmit extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "online_lead_submit";
    public $primaryKey = 'id';
    
    protected $appends  = ['added_date'];

    public function getAddedDateAttribute(){
        return date('M j, Y',strtotime(convertUTCToUserDate($this->created_at, auth()->user()->user_timezone)));
    }
}
