<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseEventLinkedContactLead extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_event_linked_contact_lead";
    public $primaryKey = 'id';

    protected $fillable = [
        'event_id', 'reminder_type', 'reminer_number', 'reminder_frequncy'   
    ];    
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  
}
