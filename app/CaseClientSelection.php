<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CaseClientSelection extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_client_selection";
    public $primaryKey = 'id';
    protected $fillable = [ 'id', 'case_id', 'selected_user','created_by', 'created_at', 'allocated_trust_balance', 'minimum_trust_balance' ];

    /**
     * Get the user that owns the CaseClientSelection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'selected_user');
    }

    /**
     * Get the case that owns the CaseClientSelection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'case_id');
    }

    /**
     * Get all of the companyContactList for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyContactList($userId, $caseId)
    {
        return UsersAdditionalInfo::join('users',"users.id","=",'users_additional_info.user_id')
                ->select("users.id as cid","users.email",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                ->whereRaw("find_in_set($userId,users_additional_info.multiple_compnay_id)")
                ->whereHas("userCases", function($query) use($caseId) {
                    $query->where("case_id", $caseId);
                })
                ->get();
    }
}
