<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class SmartTimer extends Authenticatable
{
    public $timestamps = false;
    protected $fillable = [
        'case_id', 'comments', 'started_at', 'stopped_at', 'paused_at', 'user_id'
    ];    
    
    /**
     * Get the pause timer list baser on master id
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pausedTimer()
    {
        return $this->hasMany(PauseTimerEntrie::class, 'smart_timer_id')->orderBy("id");
    }
}
