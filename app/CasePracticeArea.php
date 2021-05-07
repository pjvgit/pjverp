<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
class CasePracticeArea extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_practice_area";
    public $primaryKey = 'id';

    protected $fillable = [
        'title','status'
    ];
    protected $appends  = ['decode_primary_id','decode_id','created_by_name','linked_case_count'];
   

    //Practice area created and updated user id decoded.
    public function getDecodeIdAttribute(){
        if($this->updated_at!=NULL &&  $this->updated_by==NULL){
            return base64_encode($this->created_by);

         }else if($this->updated_at!='' &&  $this->updated_by!=NULL){
            return base64_encode($this->updated_by);

         }
     }

     //Practice area primary key decoded.
     public function getDecodePrimaryIdAttribute(){
        return base64_encode($this->id);

     }

     //Created by / updated by user name 
     public function getCreatedByNameAttribute(){
        if($this->updated_at==NULL && $this->updated_by==NULL){
           return "";
        }else if($this->updated_at!=NULL &&  $this->updated_by==NULL){
            $updatedByData =  User::select(DB::raw('CONCAT(users.first_name, " ",users.last_name) as created_by_name'))
            ->where('users.id',$this->created_by)
            ->first();
            return $updatedByData->created_by_name;
        }else if($this->updated_at!='' &&  $this->updated_by!=NULL){
            $updatedByData =  User::select(DB::raw('CONCAT(users.first_name, " ",users.last_name) as created_by_name'))
            ->where('users.id',$this->updated_by)
            ->first();
            return $updatedByData->created_by_name;
        }
      
  
    }

    public function getLinkedCaseCountAttribute(){
       return $CaseMasterData = CaseMaster::where('practice_area',$this->id)->count();

     }
}
