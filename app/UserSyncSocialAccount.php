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

    protected $appends = ['service_name'];

    public function getServiceNameAttribute()
    {
        if($this->social_type == 'google') {
            return 'Google';
        } else if($this->social_type == 'outlook') {
            return 'Outlook';
        } else {
            return '';
        }
    }
}
