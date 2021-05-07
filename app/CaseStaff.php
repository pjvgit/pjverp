<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseStaff extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_staff";
    public $primaryKey = 'id';
    protected $fillable = [
        'case_id', 'user_id', 'lead_attorney', 'originating_attorney', 'rate_type', 'rate_amount'
    ];
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    } 
}
