<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "email_template";
    public $primaryKey = 'id';


    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'subject', 'content', 'slug'
    ];
}
