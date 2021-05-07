<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class DocumentMaster extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "document_master";
    public $primaryKey = 'id';

    protected $appends  = ['padding_id'];
    public function getPaddingIdAttribute(){
       return "#R-".sprintf('%06d', $this->id);

    }
   
}
