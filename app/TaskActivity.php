<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\TaskTimeEntry,App\ExpenseEntry;
class TaskActivity extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_activity";
    public $primaryKey = 'id';

    protected $fillable = ['status', 'title'];    
    protected $appends  = ['decode_id','time_entry','expense_counter','total_hours'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->created_by);
    }  

    public function getTimeEntryAttribute(){
        $TaskTimeEntry=TaskTimeEntry::where("activity_id",$this->id)->count();
        return $TaskTimeEntry;
    }  
    public function getExpenseCounterAttribute(){
        $ExpenseEntry=ExpenseEntry::where("activity_id",$this->id)->count();
        return $ExpenseEntry;
    } 
    public function getTotalHoursAttribute(){
        return $TaskTimeDuration=TaskTimeEntry::where("activity_id",$this->id)->sum("duration");
        // $ExpenseDuration=ExpenseEntry::where("activity_id",$this->id)->sum("duration");

        // return ( $TaskTimeDuration + $ExpenseDuration);
    
    } 
    
}
