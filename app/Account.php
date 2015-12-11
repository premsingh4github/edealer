<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    //
    public function getAccount($member_id){
    		$accounts = Account::where('memberId','=',$member_id)->get();
    		$amount = 0;
    		foreach ($accounts as $account) {
    		   if($account->type == 1){
    		        $amount += $account->amount;
    		   }
    		   else{
    		         $amount -= $account->amount;
    		   }
    		 
    		}
    		return $amount;
    }
}
