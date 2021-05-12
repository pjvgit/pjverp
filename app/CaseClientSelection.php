<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseClientSelection extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_client_selection";
    public $primaryKey = 'id';
    protected $fillable = [
        'id', 'case_id', 'selected_user','created_by', 'created_at'
    ];
    protected $appends = ['group_name_is','role_name'];

    public function getGroupNameIsAttribute(){
        if(isset($this->contact_group_id) && $this->contact_group_id!=NULL){
            $contact_group_id=$this->contact_group_id;
            $client_group=ClientGroup::find($contact_group_id);
            if(!empty($client_group)){
                return $client_group['group_name'];
            }else{
                return NULL;
            }
        }else{
            return "";
        }
        
    }   

    public function getRoleNameAttribute(){
        if(isset($this->user_role) && $this->user_role!=NULL){
            $user_role=$this->user_role;
            $client_group=UserRole::find($user_role);
            if(!empty($client_group)){
                return $client_group['role_name'];
            }else
            {
                return NULL;
            }
        }else{
            return "";
        }
        
    }   
}
