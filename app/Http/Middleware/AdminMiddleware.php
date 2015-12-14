<?php namespace App\Http\Middleware;

use Closure;
use Response;
use App\Login;

class AdminMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	    {
	         $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
	        
	        if($login->mtype < 3){
	            return $next($request);
	        }
	        else{
	            $returnData = array(
	                    'status' => 'fail',
	                    'message' => 'invalid request',
	                    'code' => 403
	                );
	        return Response::json($returnData,200);
	        }
	        
	    }

}
