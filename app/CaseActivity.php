<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseActivity extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_activity";
    public $primaryKey = 'id';

    protected $fillable = [
        'activity_title', 'activity_status', 'case_id', 'activity_type', 'extra_notes'
    ];
}
