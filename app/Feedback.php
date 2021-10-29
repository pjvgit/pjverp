<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class Feedback extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "feedback";
    public $primaryKey = 'id';

    protected $fillable = [
        'feedback', 'rating', 'created_by', 'updated_by'
    ];
}
