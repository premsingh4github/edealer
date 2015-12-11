<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Notice;
use App\Login;
use App\Member;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
         $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
         $notices = Notice::where('for','=',0)->orWhere('for','=',$login->mtype)->get();
         $returnData = array(
                'code' => '200',
                'status' => 'ok',
                'notices' => $notices
            );
         return $returnData;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
        $notice = new Notice;
        $notice->for = $request['for'];
        $notice->subject = $request['subject'];
        $notice->body = $request['body'];
        $notice->sendBy = $login->member_id;
        if($notice->save()){
           $returnData = array(
                'status' => 'ok',
                'notice' => $notice,
                'code' =>200
            );
        }
        else{
            $returnData = array(
                    'code' => 500,
                    'status' => 'fail',
                    'message' => 'notice could not created'
                );
        }
        return $returnData;
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
}
