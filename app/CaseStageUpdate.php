<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseStageUpdate extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_stage_history";
    public $primaryKey = 'id';

    protected $fillable = [
        'stage_id','case_id','start_date','end_date','days'
    ];

    public function getStartDateAttribute(){
        if(isset($this->attributes['start_date'])){
            $userTime = convertUTCToUserDate(date("Y-m-d", strtotime($this->attributes['start_date'])), auth()->user()->user_timezone ?? 'UTC');
            return date('Y-m-d', strtotime($userTime));  
        }
    } 

    public function getEndDateAttribute(){
        if(isset($this->attributes['end_date'])){
            $userTime = convertUTCToUserDate(date("Y-m-d", strtotime($this->attributes['end_date'])), auth()->user()->user_timezone ?? 'UTC');
            return date('Y-m-d', strtotime($userTime));  
        }
    } 
}
