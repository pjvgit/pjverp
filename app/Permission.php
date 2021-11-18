<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['id', 'name', 'guard_name'];

    protected $dates = ['created_at', 'updated_at'];
}
