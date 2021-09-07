<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseNotes extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_notes";
    public $primaryKey = 'id';

    protected $fillable = [
        'notes_for', 'note_date', 'not_activity', 'note_subject', 'notes', 'status'
    ];

    public function getNoteDateAttribute(){
        if(isset($this->attributes['note_date'])){
            $userTime = convertUTCToUserDate($this->attributes['note_date'], auth()->user()->user_timezone ?? 'UTC');
            return date('Y-m-d', strtotime($userTime));  
        }
    }
}
