<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class Firm extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "firm";
    public $primaryKey = 'id';

    protected $fillable = [
        'firm_name', 'firm_logo'
    ];

    protected $appends = ['firm_logo_url'];
    
    /**
     * Get firm logo url
     */
    public function getFirmLogoUrlAttribute()
    {
        return !empty($this->firm_logo) ? asset('upload/firm/'.$this->firm_logo) : asset('images/logo-new.png');
    }
}
