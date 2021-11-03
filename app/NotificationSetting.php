<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationSetting extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;

    protected $fillable = [
        'type', 'topic', 'action', 'created_by', 'updated_by'
    ];    
    
}
