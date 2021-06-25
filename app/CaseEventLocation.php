<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
class CaseEventLocation extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_event_location";
    public $primaryKey = 'id';

    protected $fillable = [
        'title','status'
    ];
    protected $appends = ['createdby','created_new_date','created_new_date_only', 'full_address'];

    public function getCreatedbyAttribute(){
        return base64_encode($this->uid);
    }
    public function getCreatedNewDateAttribute(){

        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->created_at)),$timezone);
        return date('M d,Y h:i A',strtotime($convertedDate));
    }   
    public function getCreatedNewDateOnlyAttribute(){

        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->created_at)),$timezone);
        return date('M d,Y',strtotime($convertedDate));
    }   

    /**
     * Get the country that owns the CaseEventLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function countryDetail()
    {
        return $this->belongsTo(Countries::class, 'country');
    }
    
    /**
     * Get full address of location attribute
     */
    public function getFullAddressAttribute()
    {
        return $this->location_name.', '.$this->address1.', '.$this->address2.', '.$this->city.', '.$this->state.', '.$this->postal_code.', '.@$this->countryDetail->name;
    }
}
