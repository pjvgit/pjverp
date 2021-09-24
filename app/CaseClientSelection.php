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
}
