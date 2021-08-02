<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseEventComment extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_event_comment";
    public $primaryKey = 'id';
    protected $fillable = [
        'id', 'event_id', 'comment','created_by', 'created_at'
    ];

    /**
     * Get the createdByUser that owns the CaseEventComment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
