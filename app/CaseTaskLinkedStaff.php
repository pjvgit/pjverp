<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseTaskLinkedStaff extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_linked_staff";
    public $primaryKey = 'id';

    protected $fillable = [
        'task_id', 'reminder_type', 'reminer_number', 'reminder_frequncy' ,'is_assign', 'is_contact' 
    ];    
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  
}
