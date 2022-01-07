<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class CaseUpdate extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_update";
    public $primaryKey = 'id';
    protected $fillable = [
        'id', 'case_id', 'update_status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by'
    ];
    protected $appends = ['created_new_date'];

    public function getCreatedNewDateAttribute() {
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        $convertedDate= convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->created_at)),$timezone);
        return date('M j, Y h:i A',strtotime($convertedDate));
    }   

    /**
     * Get the createdByUser that owns the CaseUpdate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
