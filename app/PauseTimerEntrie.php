<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PauseTimerEntrie extends Authenticatable
{
    public $timestamps = false;

    protected $fillable = [
        'smart_timer_id', 'pause_start_time', 'pause_stop_time'
    ]; 
    
}
