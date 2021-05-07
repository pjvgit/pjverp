<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class LeadNotesActivity extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "lead_notes_activity";
    public $primaryKey = 'id';

    protected $fillable = [
        'acrtivity_title','status'
    ];
}
