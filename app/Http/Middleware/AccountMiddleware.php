<?php namespace App\Http\Middleware;

use Closure;
use Response;
use App\Login;

class AccountMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$user = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
       if(!($user->mtype == 3 || $user->mtype == 1) ){
           $returnData = array(
                  'status' => 'fail',
                  'mesage' => 'insufficient permision',
                  'code' =>403
              );
              return $returnData ;
       }
       return $next($request);
	}

}
