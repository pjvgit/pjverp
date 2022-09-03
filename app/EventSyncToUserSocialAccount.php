<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSyncToUserSocialAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'user_sync_sa_id', 'event_id', 'event_recurring_id', 'social_event_id', 'social_event_url', 'craeted_by', 'updated_by', 'social_type'
    ];
}
