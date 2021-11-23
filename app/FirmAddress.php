<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class FirmAddress extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "firm_address";
    public $primaryKey = 'id';

    protected $fillable = ['office_name', 'main_phone', 'fax_line', 'address', 'apt_unit', 'city', 'state', 'post_code', 'country', 'firm_id', 'is_primary', 'created_by', 'updated_by'];
}
