<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
class ClientGroup extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "client_group";
    public $primaryKey = 'id';

    protected $fillable = [
        'group_name','status'
    ];
    protected $appends  = ['createdatnewformate','count_attach_contact','createdby'];

    public function getCreatedatnewformateAttribute(){
        return date('M j, Y h:i A',strtotime($this->created_at));
    }
    public function getCountAttachContactAttribute(){

        $userCount =  User::select('id')->leftJoin('users_additional_info','users.id',"=","users_additional_info.user_id");
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $userCount = $userCount->whereIn("parent_user",$getChildUsers);
            
        }else{
            $userCount = $userCount->where("parent_user",Auth::user()->id); //Logged in user not visible in grid
        }
        $userCount=$userCount->where('user_level',"2");  
        $userCount=$userCount->whereIn('users.user_status',["1","2"]);
        $userCount=$userCount->where('users_additional_info.contact_group_id',$this->id);
        $userCount=$userCount->count();
        return $userCount;
    }

    public function getCreatedbyAttribute(){
        
        return base64_encode($this->uid);
        
    }

}
