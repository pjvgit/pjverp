<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class ClientCompanyImport extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "client_company_import";
    public $primaryKey = 'id';

    protected $appends  = ['decode_id','created_new_date','decoder'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->created_by);
    } 
    public function getDecoderAttribute(){
        return base64_encode($this->id);
    } 
    public function getCreatedNewDateAttribute(){

        return date('M d,Y',strtotime($this->created_at));
    }   
}
