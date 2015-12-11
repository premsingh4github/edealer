<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    //
    public function getOnlineUser(){
    	$user = Login::select('member_id')->where('status','=',1)->get();
    	return $user;
    }
}
