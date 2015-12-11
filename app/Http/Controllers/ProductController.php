<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;
use App\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
         $products = Product::all();
             $returnData = array(
                    'status' => 'ok',
                    'products' => $products,
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
        try{

          $product = new Product;
           $product->name = $request['name'];
           $product->commision = $request['commision'];
           $product->margin = $request['margin'];
           $product->lot_size = $request['lot_size'];
           $product->holding_cost = $request['holding_cost'];
           $product->type = $request['producType'];
          
            if($product->save()){
               $returnData = array(
                       'status' => 'ok',
                       'message' => 'Product created',
                       'product' => $product,
                       'code' =>200
                   );
               return Response::json($returnData, 200);
           }
           else{
               $returnData = array(
                       'status' => 'fail',
                       'message' => 'product not created',
                       'code' =>500
                   );
               return Response::json($returnData, 200);
           }

        }catch(\Exception $e){
          return $e->getMessage();
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
        try{

          $product = Product::find($request['productId']);
           $product->name = $request['name'];
           $product->commision = $request['commision'];
           $product->margin = $request['margin'];
           $product->lot_size = $request['lot_size'];
           $product->holding_cost = $request['holding_cost'];
           $product->type = $request['producType'];
          
            if($product->save()){
               $returnData = array(
                       'status' => 'ok',
                       'message' => 'Product edited',
                       'product' => $product,
                       'code' =>200
                   );
               return Response::json($returnData, 200);
           }
           else{
               $returnData = array(
                       'status' => 'fail',
                       'message' => 'product not edited',
                       'code' =>500
                   );
               return Response::json($returnData, 200);
           }

        }catch(\Exception $e){
          return $e->getMessage();
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
