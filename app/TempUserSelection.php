<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempUserSelection extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "temp_user_selection";
    public $primaryKey = 'id';
    protected $fillable = [
        'id', 'user_id', 'selected_user', 'created_at'
    ];
}
