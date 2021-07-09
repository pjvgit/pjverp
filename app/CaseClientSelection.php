<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseClientSelection extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_client_selection";
    public $primaryKey = 'id';
    protected $fillable = [ 'id', 'case_id', 'selected_user','created_by', 'created_at' ];
}
