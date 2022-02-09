<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
class DepositIntoCreditHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "deposit_into_credit_history";
    public $primaryKey = 'id';

    protected $fillable = ['user_id', 'payment_method', 'deposit_amount', 'payment_date', 'payment_type', 'related_to_invoice_id', 'total_balance', 
            'notes', 'firm_id', 'created_by', 'is_refunded', "refund_ref_id", "related_to_invoice_payment_id", 'related_to_fund_request_id', 'online_payment_status', 'is_invoice_cancelled'];

    /**
     * Get the invoice that owns the DepositIntoCreditHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'related_to_invoice_id');
    }

    /**
     * Get the fundRequest that owns the DepositIntoCreditHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fundRequest()
    {
        return $this->belongsTo(RequestedFund::class, 'related_to_fund_request_id');
    }

    /**
     * Get the user that owns the DepositIntoCreditHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
