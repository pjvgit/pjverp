<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationSettings extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "notification_setting";
    public $primaryKey = 'id';

    protected $fillable = [
        'type', 'topic', 'action', 'created_by', 'updated_by'
    ];    
    
}
