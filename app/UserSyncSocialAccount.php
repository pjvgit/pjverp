<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSyncSocialAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'social_type', 'social_id', 'email', 'access_token', 'refresh_token', 'craeted_by', 'calendar_id'
    ];

    // protected $casts = ['access_token' => 'json', 'refresh_token' => 'json'];
}
