<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Branch;
use Response;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $branches = Branch::all();
             $returnData = array(
                    'status' => 'ok',
                    'branches' => $branches,
                    'code' =>200
                );
                return $returnData ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $data = $request->only('branchName','branchLocation');
        //return $data;
        $branch = new Branch;
        $branch->name = $data['branchName'];
        $branch->location = $data['branchLocation'];
        $branch->delivery_charge = $request['delivery_charge'];

         if($branch->save()){
            $returnData = array(
                    'status' => 'ok',
                    'message' => 'Branch created',
                    'branch' => $branch,
                    'code' =>200
                );
            return Response::json($returnData, 200);
        }
        else{
            $returnData = array(
                    'status' => 'fail',
                    'message' => 'Branch not created',
                    'code' =>500
                );
            return Response::json($returnData, 200);
        }
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
    public function edit(Request $request)
    {
        
        $branch = Branch::find($request['branchId']);
        $branch->name = $request['branchName'];
        $branch->location = $request['branchLocation'];
        $branch->delivery_charge = $request['delivery_charge'];

         if($branch->save()){
            $returnData = array(
                    'status' => 'ok',
                    'message' => 'Branch edited',
                    'branch' => $branch,
                    'code' =>200
                );
            return Response::json($returnData, 200);
        }
        else{
            $returnData = array(
                    'status' => 'fail',
                    'message' => 'Branch not edited',
                    'code' =>500
                );
            return Response::json($returnData, 200);
        }
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
