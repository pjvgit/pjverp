<?php
 
namespace App\Traits;

use App\DepositIntoCreditHistory;
use App\User;

trait CreditAccountTrait {
 
    public function updateNextPreviousCreditBalance($userId) {
        $creditHistory = DepositIntoCreditHistory::where("user_id", $userId)->orderBy("payment_date", "asc")->orderBy("created_at", "asc")->get();
        foreach($creditHistory as $key => $item) {
            $previous = $creditHistory->get(--$key);  
            $currentBal = 0;
            if($previous) {
                // echo "<pre>";
                // print_r($previous);
                // echo "</pre>";
                $currentBal = $previous->total_balance;
            }
            if($item->payment_type == "deposit") {
                $currentBal = $currentBal + $item->deposit_amount;
            } else if($item->payment_type == "withdraw") {
                $currentBal = $currentBal - $item->deposit_amount;
            } else if($item->payment_type == "payment") {
                $currentBal = $currentBal - $item->deposit_amount;
            }
            $item->total_balance = $currentBal;
            $item->save();
        }
    }
 
}
 