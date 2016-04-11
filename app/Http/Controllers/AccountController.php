<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\login;
use Response;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

     public function __construct(Request $request){
        $user = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
        if($user->mtype != 3){
            $returnData = array(
                   'status' => 'fail',
                   'mesage' => 'insufficient permision',
                   'code' =>403
               );
               return $returnData ;
        }

     }
    public function index(Request $request)
    {   
         $user = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
        if($user->mtype != 3){
            $returnData = array(
                   'status' => 'fail',
                   'mesage' => 'insufficient permision',
                   'code' =>403
               );
               return $returnData ;
        }
        return $user;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
    public function getPrices(){
        $html = file_get_html('http://www.livepriceofgold.com/');
        $silver = $html->getElementById("silvervalue")->innertext();
        $gold = $html->getElementById("ozvalue")->innertext();
        
        return Response::json(['silver' => ($silver/3.11*108.576)+68,'gold'=> ($gold/3.11*108.576)+68 ]);
    }
}
