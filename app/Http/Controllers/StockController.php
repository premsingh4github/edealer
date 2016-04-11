<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Stock,App\LimitOrder;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;
use App\Login,App\MetaData;
use App\ClientStock;
use App\Product,App\AddProduct;
use App\Branch;
use App\Account, App\StockType,Input;



class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    
    public function index(Request $request)
    {
        
        $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
        $stockTypes = StockType::all();
        $limitOrders = LimitOrder::all();
        
        if($login->mtype == 1){
            $stocks = Stock::orderBy('stockTypeId')->get();
            if(count($stocks) > 0){
                foreach ($stocks as $stock) {
                    $clientStocks = ClientStock::where('stockId','=',$stock->id)->where('status','=',0)->get();
                    $stock->request = $clientStocks;
                    //$stockProducts = AddProduct::sum('quantity')->where('stockId',$stock->id);
                    $stock->quantity = AddProduct::where('stockId',$stock->id)->sum('quantity');
                    $data[] = $stock; 
                }
            }
            else{
              $data = $stocks;  
            }
            $clientStocks = ClientStock::all();
            $clientAccounts = Account::all();
        }
        else{
            $stocks = Stock::orderBy('stockTypeId')->where('stockTypeId',3)->get();
           if(count($stocks) > 0){
               foreach ($stocks as $stock) {
                   $stock->quantity = AddProduct::where('stockId',$stock->id)->sum('quantity');
                   $data[] = $stock; 
               }
           }
           else{
             $data = $stocks;  
           } 
           $clientStocks = ClientStock::where('memberId',$login->member_id)->get(); 
           $clientAccounts = Account::where('memberId',$login->member_id)->get(); 
        }

             $returnData = array(
                    'status' => 'ok',
                    'stocks' => $data,
                    'stockTypes' => $stockTypes,
                    'clientStocks' => $clientStocks,
                    'limitOrders' => $limitOrders,
                    'clientAccounts' => $clientAccounts,
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
        //return $request['branchId'];
        $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
        $stock = Stock::where('stockTypeId',$request['stockTypeId'])->where('branchId',$request['branchId'])->where('productTypeId',$request['productTypeId'])->first();
        if(count($stock)){
             $stockProduct = new AddProduct;
             $stockProduct->stockId = $stock->id;
             $stockProduct->quantity = $request['onlineQuantity'];
             $stockProduct->addedBy = $login->member_id;
             $stockProduct->added = 1;
             $stockProduct->save();
            $returnData = array(
                    'status' => 'added',
                    'stock' => $stock,
                    'stockProduct' => $stockProduct,
                    'code' =>200
                );
             return Response::json($returnData, 200);
        }
        else{
            try{
              $stock = new Stock ; 
              $stock->fill(Input::all());
              $stock->addedBy = $login->member_id;
              $stock->save();
              $stockProduct = new AddProduct;
              $stockProduct->stockId = $stock->id;
              $stockProduct->quantity = $request['onlineQuantity'];
              $stockProduct->addedBy = $login->member_id;
              $stockProduct->added = 1;
              $stockProduct->save();
              $stock->quantity = $request['onlineQuantity'];
              $returnData = array(
                      'status' => 'created',
                      'stockProduct' => $stockProduct,
                      'stock' => $stock,
                      'code' =>200
                  );
              return Response::json($returnData, 200);
          }catch(\Exception $e){
              return $e->getMessage();
          }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $login = Login::where('remember_token','=',$request->header('token'))->where('logins.status','=','1')->where('login_from','=',$request->ip())->join('members','members.id','=','logins.member_id')->first();
        $stock = Stock::find($request['stockId']);
        $branch = Branch::find($stock->branchId);
        $account = new Account;
        $account->getAccount($login->member_id); 
        $productType = Product::find($stock->productTypeId);
        $realQuantity = $productType->lot_size * $request['amount'];
        $realDeliveryCharge = ($realQuantity/10) * $branch->delivery_charge; 
        $cost = (($productType->commision + $productType->margin) * $request['amount']) + $realDeliveryCharge ;
        $clientStock = new ClientStock;
        $clientStock->memberId = $login->member_id;
        $clientStock->stockId = $request['stockId'];
        $clientStock->amount = ($productType->lot_size * $request['amount']);
        $clientStock->status = 0;
        $clientStock->delivery_date = $request['delivery_date'];
        if($account->getAccount($login->member_id) < $cost){
            $returnData = array(
                   'status' => 'fail',
                   'message' => "Insufficient balance",
                   'code' =>400
               );
        }
        elseif($stock->onlineQuantity < $clientStock->amount){
            $returnData = array(
                   'status' => 'fail',
                   'message' => "Insufficient stock quantity",
                   'code' =>400
               );
        }
        else{
            $clientStock->cost = $cost;
            $clientStock->save();
            $account = new Account;
            $account->memberId = $login->member_id;
            $account->addedBy = 0;
            $account->type = 0;
            $account->amount = $cost;
            $account->token_id = $clientStock->id;
            $account->save();
            $returnData = array(
                   'status' => 'ok',
                   'clientStock' => $clientStock,
                   'message' => "Your request completed successfully",
                   'code' =>200
               );

        }

           return $returnData ;
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
    public function update(Request $request)
    {
        $login = Login::where('remember_token','=',$request->header('token'))->where('status','=','1')->where('login_from','=',$request->ip())->first();
        
        if($login->mtype < 6 ){

            $add_product = new AddProduct;
            $stock = Stock::find($request['stockId']);
            $productType = Product::find($stock->productTypeId);
            $add_product->stockId = $request['stockId'];
            $add_product->quantity = $request['quantity'] * $productType->lot_size;
            $add_product->addedBy = $login->member_id;
            $add_product->save();
            $stock->onlineQuantity += $add_product->quantity;
            $stock->save();
            $returnData = array(
                    'status' => 'ok',
                    'message' => 'Stock updatd successfully',
                    'code' => 200,
                    'stock' => $stock
                    );
        }
        else{
            $returnData = array(
                    'status' => 'fail',
                    'message' => 'Insufficient permission',
                    'code' => 400
                );
        }
       return $returnData;

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
    public function approveRequest(Request $request){
        if($clientStock = ClientStock::find($request['requestId'])){
            if($request['status'] == 1){
                $clientStock->status = $request['status'];
                if($clientStock->save()){
                    $stock = Stock::find($clientStock->stockId);
                    $stock->onlineQuantity -= $clientStock->amount;
                    $returnData = array(
                            'status' => 'ok',
                            'message' => 'request approved successfully',
                            'clientStock' => $clientStock,
                            'code' =>200
                        );
                }
            }
            else{
                $clientStock->status = 2;
                $clientStock->save();
                $account = new Account;
                $account->memberId = $clientStock->memberId;
                $account->token_id = $clientStock->id;
                $account->type = 1;
                $account->amount = $clientStock->cost;
                $account->addedBy = 0;
                $account->save();
                $returnData = array(
                        'status' => 'ok',
                        'message' => 'request rejected successfully',
                        'clientStock' => $clientStock,
                        'code' =>200
                    );

            }
            
        }
        else{
            $returnData = array(
                    'status' => 'fail',
                    'message' => 'invalid repquest',
                    'code' => '400'
                );
        }
        return $returnData; 
    }
    public function addStockProduct(Request $request){

    }
    public function getStockTypes(){
        $stockTypes = StockType::all();
        $returnData = array(
                    'status' => 'ok',
                    'stockTypes' => $stockTypes,
                    'code' => '200'
                );
        return Response::json($returnData,200);
    }
    public function addMarketOrder(Request $request){
       $metaData = MetaData::where('meta_key','server_status')->first();
       if($metaData['meta_value'] == 0){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Sorry! Server is closed. Please try later.',
                      'code' => '422'
                  );
          return Response::json($errorData,200);
       }
        try{
            $productType = Product::find($request['productTypeId']);

            if(($productType->type  == 0) && ($productType->lot_size * $request['lot'] < 25000)){
                $returnData = array(
                            'status' => 'fail',
                            'message' => 'Sorry! Invalid requist. Please try retail symbol.',
                            'code' => '422'
                        );
                return Response::json($returnData,200);
            }
            if(($productType->type  == 1) && ($productType->lot_size * $request['lot'] >= 25000)){
                $returnData = array(
                            'status' => 'fail',
                            'message' => 'Sorry! Invalid requist. Please try wholesale symbol.',
                            'code' => '422'
                        );
                return Response::json($returnData,200);
            }

            $stock = Stock::where('productTypeId',$request['productTypeId'])->where('branchId',$request['branchId'])->first();
            $branch = Branch::find($request['branchId']);
            
            $stock->quantity = (AddProduct::where('stockId',$stock->id)->where('added',1)->sum('quantity') - AddProduct::where('stockId',$stock->id)->where('added',0)->sum('quantity'));
            if($stock->quantity < ($productType->lot_size * $request['lot'])){
                $returnData = array(
                            'status' => 'fail',
                            'message' => 'Sorry! Insufficient stock. Please try low quantity.',
                            'code' => '422'
                        );
                return Response::json($returnData,200);
            }
            else{
                $login = Login::where('remember_token','=',$request->header('token'))->where('status','=','1')->where('login_from','=',$request->ip())->first();
                $clientStock =  new ClientStock;
                $clientStock->type = 1;
                $clientStock->price = 4800;
                $clientStock->memberId = $login->member_id;
                $clientStock->stockId = $stock->id;
                $clientStock->amount = ($productType->lot_size * $request['lot']);
                $clientStock->status = 0;
                $clientStock->commission = $productType->commision *  $request['lot'];
                $clientStock->margin = $productType->margin *  $request['lot'];
                $clientStock->delivery_charge = (($productType->lot_size * $request['lot'])/10) * $branch->delivery_charge;
                $clientStock->cost = ($clientStock->amount/10) * $clientStock->price;
                $clientStock->remaining_cost = ($clientStock->cost - $clientStock->margin);

                
                $date = strtotime("+7 day");
                $clientStock->delivery_date = date('Y-m-d', $date);
                $clientStock->ticket = $this->ticket_generate();
                $clientStock->save();
                $account = new Account;
                $account->memberId = $login->member_id;
                $account->addedBy = $login->member_id;
                $account->type = 0;
                $account->amount = ($clientStock->margin + $clientStock->commission);
                $account->ticket = $clientStock->ticket;
                $account->save();
                $returnData = array(
                            'status' => 'ok',
                            'code' => '200',
                            'clientStock' => $clientStock,
                            'account' => $account
                        );
                return Response::json($returnData,200);
            }

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    public function addLimitBuyOrder(Request $request){
       $metaData = MetaData::where('meta_key','server_status')->first();
       if($metaData['meta_value'] == 0){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Sorry! Server is closed. Please try later.',
                      'code' => '422'
                  );
          return Response::json($errorData,200);
       }
        try{
            $productType = Product::find($request['productTypeId']);
             $stock = Stock::where('productTypeId',$request['productTypeId'])->where('branchId',$request['branchId'])->first();
                $login = Login::where('remember_token','=',$request->header('token'))->where('status','=','1')->where('login_from','=',$request->ip())->first();
                $clientStock =  new LimitOrder;
                $clientStock->memberId = $login->member_id;
                $clientStock->stockId = $stock->id;
                $clientStock->amount = ($productType->lot_size * $request['lot']);
                $clientStock->status = 0;
                $clientStock->type = 1;
                $clientStock->priceMin = $request['priceMin'];
                $clientStock->priceMax = $request['priceMax'];
                //$clientStock->cost = ($clientStock->amount/10) * 4800;
                // $date = strtotime("+7 day");
                // $clientStock->delivery_date = date('Y-m-d', $date);
                // $clientStock->ticket = $this->ticket_generate();
                $clientStock->save();
                // $account = new Account;
                // $account->memberId = $login->member_id;
                // $account->addedBy = $login->member_id;
                // $account->type = 0;
                // $account->amount = $clientStock->cost;
                // $account->ticket = $clientStock->ticket;
                // $account->save();
                $returnData = array(
                            'status' => 'ok',
                            'code' => '200',
                            'limitOrder' => $clientStock,
                            //'account' => $account
                        );
                return Response::json($returnData,200);
           

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    public function addLimitSellOrder(Request $request){
       $metaData = MetaData::where('meta_key','server_status')->first();
       if($metaData['meta_value'] == 0){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Sorry! Server is closed. Please try later.',
                      'code' => '422'
                  );
          return Response::json($errorData,200);
       }
        try{
            $productType = Product::find($request['productTypeId']);

            // if(($productType->type  == 0) && ($productType->lot_size * $request['lot'] < 25000)){
            //     $returnData = array(
            //                 'status' => 'fail',
            //                 'message' => 'Sorry! Invalid requist. Please try retail symbol.',
            //                 'code' => '422'
            //             );
            //     return Response::json($returnData,200);
            // }
            // if(($productType->type  == 1) && ($productType->lot_size * $request['lot'] >= 25000)){
            //     $returnData = array(
            //                 'status' => 'fail',
            //                 'message' => 'Sorry! Invalid requist. Please try wholesale symbol.',
            //                 'code' => '422'
            //             );
            //     return Response::json($returnData,200);
            // }

             $stock = Stock::where('productTypeId',$request['productTypeId'])->where('branchId',$request['branchId'])->first();
            
            // $stock->quantity = AddProduct::where('stockId',$stock->id)->sum('quantity');
            // if($stock->quantity < ($productType->lot_size * $request['lot'])){
            //     $returnData = array(
            //                 'status' => 'fail',
            //                 'message' => 'Sorry! Insufficient stock. Please try low quantity.',
            //                 'code' => '422'
            //             );
            //     return Response::json($returnData,200);
            // }
            
                $login = Login::where('remember_token','=',$request->header('token'))->where('status','=','1')->where('login_from','=',$request->ip())->first();
                $clientStock =  new LimitOrder;
                $clientStock->memberId = $login->member_id;
                $clientStock->stockId = $stock->id;
                $clientStock->amount = ($productType->lot_size * $request['lot']);
                $clientStock->status = 0;
                $clientStock->type = 0;
                $clientStock->priceMin = $request['priceMin'];
                $clientStock->priceMax = $request['priceMax'];
                //$clientStock->cost = ($clientStock->amount/10) * 4800;
                // $date = strtotime("+7 day");
                // $clientStock->delivery_date = date('Y-m-d', $date);
                // $clientStock->ticket = $this->ticket_generate();
                $clientStock->save();
                // $account = new Account;
                // $account->memberId = $login->member_id;
                // $account->addedBy = $login->member_id;
                // $account->type = 0;
                // $account->amount = $clientStock->cost;
                // $account->ticket = $clientStock->ticket;
                // $account->save();
                $returnData = array(
                            'status' => 'ok',
                            'code' => '200',
                            'limitOrder' => $clientStock,
                            //'account' => $account
                        );
                return Response::json($returnData,200);
           

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function ticket_generate() {
        $today_date = date("dm");
        $today_time = date("Hs");
        $rand       = mt_rand(0, 9);
        $rand2      = mt_rand(0, 9);
        $ticket = $rand.$today_time.$rand2.$today_date;
        $ticket_count = ClientStock::where('ticket',$ticket)->count();
        if($ticket_count > 0){
          $this->ticket_generate(); 
        }
        else{
           return $ticket ; 
        }
        
    }
    public function getClientStock(Request $request){
       $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
        if($login->mtype == 1){
            $clientStocks = ClientStock::all();
            $returnData = array(
                            'status' => 'ok',
                            'code' => '200',
                            'clientStock' => $clientStocks,
                        );
                return Response::json($returnData,200);
        }
        return $login->mtype;        
    }
    public function acceptOrder(Request $request){
      $clientStock = ClientStock::find($request['orderId']);
      $stock = Stock::find($clientStock->stockId);
      $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
      $stock->quantity = (AddProduct::where('stockId',$stock->id)->where('added',1)->sum('quantity') - AddProduct::where('stockId',$stock->id)->where('added',0)->sum('quantity'));
      if($stock->quantity < $clientStock->amount){
        $returnData = array(
                            'status' => 'ok',
                            'code' => '422',
                            'clientStock' => $clientStock,
                            'message' => 'Insufficient Stock',
                        );
        return Response::json($returnData,422);
      }
      if ($request['status'] == 1) {
        $clientStock->acceptedBy = $login->member_id;
        $clientStock->status = 1;
        $clientStock->save();
        $addProduct = new  AddProduct;
        $addProduct->stockId = $clientStock->stockId;
        $addProduct->quantity = $clientStock->amount;
        $addProduct->addedBy =  $login->member_id;
        $addProduct->added = 0;
        $addProduct->ticket = $clientStock->ticket;
        $addProduct->save();
        $returnData = array(
                            'status' => 'ok',
                            'code' => '200',
                            'clientStock' => $clientStock,
                            'addProduct' => $addProduct,
                        );
        return Response::json($returnData,200);
      }
      if($request['status'] == 0){
        $clientStock->acceptedBy = $login->member_id;
        $clientStock->status = 3;
        $clientStock->save();
        $account = new Account;
        $account->ticket = $clientStock->ticket;
        $account->amount = $clientStock->commission + $clientStock->margin;
        $account->type = 1;
        $account->addedBy = $login->member_id;
        $account->memberId = $clientStock->memberId;
        $account->save();
        $returnData = array(
                            'status' => 'ok',
                            'code' => '200',
                            'clientStock' => $clientStock,
                            'account' => $account,
                        );
        return Response::json($returnData,200);
      }
      


    }
    public function transferToVault(Request $request){
      $metaData = MetaData::where('meta_key','server_status')->first();
      if($metaData['meta_value'] == 0){
         $errorData = array(
                     'status' => 'fail',
                     'message' => 'Sorry! Server is closed. Please try later.',
                     'code' => '422'
                 );
         return Response::json($errorData,422);
      }
      $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
      $clientStock = ClientStock::find($request['clientStockId']);
      $account = new Account;
      if($account->getAccount($login->member_id) < $clientStock->remaining_cost){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Insufficient Balance!',
                      'code' => '422'
                  );
          return Response::json($errorData,422);
      }
      if($clientStock->memberId != $login->member_id){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Invalid Request!',
                      'code' => '422'
                  );
          return Response::json($errorData,422);
      }
      else{

          $clientStock->status = 5;
          $clientStock->save();
          $account = new Account;
          $account->ticket = $clientStock->ticket;
          $account->amount = $clientStock->remaining_cost;
          $account->type = 0;
          $account->addedBy = $login->member_id;
          $account->memberId = $clientStock->memberId;
          $account->save();
          $returnData = array(
                              'status' => 'ok',
                              'code' => '200',
                              'clientStock' => $clientStock,
                              'account' => $account,
                          );
          return Response::json($returnData,200);
      }

    }
    public function transferToDelivery(Request $request){
      $metaData = MetaData::where('meta_key','server_status')->first();
      if($metaData['meta_value'] == 0){
         $errorData = array(
                     'status' => 'fail',
                     'message' => 'Sorry! Server is closed. Please try later.',
                     'code' => '422'
                 );
         return Response::json($errorData,422);
      }
      $login = Login::where('remember_token','=',$request->header('token'))->where('login_from','=',$request->ip())->join('members', 'members.id', '=', 'logins.member_id')->where('logins.status','=','1')->first();
      $clientStock = ClientStock::find($request['clientStockId']);
      $account = new Account;
      if($account->getAccount($login->member_id) < ($clientStock->remaining_cost+$clientStock->delivery_charge)){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Insufficient Balance!',
                      'code' => '422'
                  );
          return Response::json($errorData,422);
      }
      if($clientStock->memberId != $login->member_id){
          $errorData = array(
                      'status' => 'fail',
                      'message' => 'Invalid Request!',
                      'code' => '422'
                  );
          return Response::json($errorData,422);
      }
      else{

          $clientStock->status = 4;
          $clientStock->save();
          $account = new Account;
          $account->ticket = $clientStock->ticket;
          $account->amount = ($clientStock->remaining_cost + $clientStock->delivery_charge);
          $account->type = 0;
          $account->addedBy = $login->member_id;
          $account->memberId = $clientStock->memberId;
          $account->save();
          $returnData = array(
                              'status' => 'ok',
                              'code' => '200',
                              'clientStock' => $clientStock,
                              'account' => $account,
                          );
          return Response::json($returnData,200);
      }

    }
}
