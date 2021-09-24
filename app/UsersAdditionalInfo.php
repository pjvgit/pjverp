<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CommonController;
class UsersAdditionalInfo extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "users_additional_info";
    public $primaryKey = 'id';

    protected $fillable = [
        'id', 'user_id', 'contact_group_id', 'user_timezone', 'user_status', 'client_portal_enable', 'address2', 'dob', 'job_title', 'driver_license', 
        'license_state', 'werbsite', 'fax_number', 'notes', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'trust_account_balance', 
        'credit_account_balance', 'minimum_trust_balance'
    ];
    protected $appends  = ['lastloginnewformate','caselist', 'unallocate_trust_balance'];

    public function getLastloginnewformateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->last_login!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->last_login)),$timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));

        }else{
            if($this->client_portal_enable==1){
                return "Disabled";
            }else{
                return "Never";
            }
            
        }
    }
     
    public function getCaselistAttribute(){
        $ContractUserCase =  CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
        ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
        ->where('case_client_selection.selected_user',$this->id)  
        ->get();
        return json_encode($ContractUserCase); 
    }

    /**
     * Get the user that owns the UsersAdditionalInfo
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Set unallocate trust balance using trust account balance attribute
     */
    public function getUnallocateTrustBalanceAttribute()
    {
        return  $this->trust_account_balance - $this->selectedCases->sum('allocated_trust_balance');
    }

    /**
     * Get all of the case client selected table column for the UsersAdditionalInfo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function selectedCases()
    {
        return $this->hasMany(CaseClientSelection::class, 'selected_user', 'user_id');
    }
}
