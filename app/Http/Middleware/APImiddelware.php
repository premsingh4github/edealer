<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Login;
class APImiddelware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $login = Login::where('remember_token','=',$request->header('token'))->where('status','=','1')->where('login_from','=',$request->ip())->count();
        if($login == 1){
            return $next($request);
        }
        else{
            $returnData = array(
                    'status' => 'fail',
                    'message' => 'invalid token',
                    'code' => 403
                );
        return Response::json($returnData,200);
        }
        
    }
}
