<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PurchaseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/market/public/api/items",
     *     tags={"View All Items"},
     *     summary="Get All items",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="List all items successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No Item"
     *     ),
     *     
     * )
     */
    public function index()
    {
        if(count($this->getList())!=0){
            return response()->json([
                "status" => 1,
                "message" => "Listing Items",
                "data" => $this->getList()
            ], 200);    
        }
        else{
            return response()->json([
                "status" => 0,
                "message" => "No Item",
            ], 404);
        }
        

    }

    
    public function getList()
    {
        $sellerip = ['shop1','shop2'];
        $response = Http::get('http://131.217.174.114/'.$sellerip[0].'/public/api/itemlist');
        $jsonData1 = $response->json();
        $response = Http::get('http://131.217.174.114/'.$sellerip[1].'/public/api/itemlist');
        $jsonData2 = $response->json();
        //dd($jsonData1['data'],$jsonData2['data']);
        $data1 = $jsonData1['data'];
        $data2 = $jsonData2['data'];
        $itemlist[] = new Item();
        foreach($data1 as $row)
        {
            $item = new Item();
            $item->id=$row['id'];
            $item->Name=$row['Name'];
            $item->Price=$row['Price'];
            $item->Quantity=$row['Quantity'];
            $item->Shop=$sellerip[0];
            //echo $row['Name'];
            array_push($itemlist,$item);
            
        }
        foreach($data2 as $row){
            $item2 = new Item();
            $item2->id=$row['id'];
            $item2->Name=$row['Name'];
            $item2->Price=$row['Price'];
            $item2->Quantity=$row['Quantity'];
            $item2->Shop=$sellerip[1];
            array_push($itemlist,$item2);
        }

        return $itemlist;
    }

    

    /**
     * @OA\Delete(
     *     path="/market/public/api/deletepurchase",
     *     tags={"Delete Purchase"},
     *     summary="search purchase ID and delete the purchase",
     *     operationId="destroy",
     *     
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="purID",
     *                     description="Purchase ID",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(Request $request)
    {
        if($request->purID!="")
        {
            
            if (Purchase::where("id", $request->purID)->exists()) {

            $purchase = Purchase::find($request->purID);
            $purchase->delete();

            return response()->json([
                "status" => 1,
                "message" => "Purchase with Purchase ID ".$purchase->id." has been deleted",
                "data" => $purchase
            ]);
            } else {

                return response()->json([
                    "status" => 0,
                    "message" => "Purchase not found"
                ], 404);
            }
        }
        
    }

    /**
     * @OA\Post(
     *     path="/market/public/api/search",
     *     tags={"Search"},
     *     summary="Search Items in the 2 shops with form data",
     *     operationId="searchItem",
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Not found purchase ID"
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Search successful"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="Name",
     *                     description="Name of the product or key words",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function searchItem(Request $request)
    {
        $request->validate([
            "Name" => "required"
        ]);
        $flag = 0;
        try {
            $sellerip = ['shop1','shop2'];
            $response = Http::get('http://131.217.174.114/'.$sellerip[0].'/public/api/search/'.$request->Name);
            $jsonData1 = $response->json();
            $response = Http::get('http://131.217.174.114/'.$sellerip[1].'/public/api/search/'.$request->Name);
            $jsonData2 = $response->json();
            //dd($jsonData1['data'],$jsonData2['data']);
            
            
            $itemlist[] = new Item();
            if($jsonData1['status']==1)
            {
                $data1 = $jsonData1['data'];
                foreach($data1 as $row)
                {
                    $item = new Item();
                    $item->id=$row['id'];
                    $item->Name=$row['Name'];
                    $item->Price=$row['Price'];
                    $item->Quantity=$row['Quantity'];
                    $item->Shop='shop1';
                    array_push($itemlist,$item);
                    
                }
                $flag=1;
            }
            if($jsonData2['status']==1)
            {
                $data2 = $jsonData2['data'];
                foreach($data2 as $row){
                    $item2 = new Item();
                    $item2->id=$row['id'];
                    $item2->Name=$row['Name'];
                    $item2->Price=$row['Price'];
                    $item2->Quantity=$row['Quantity'];
                    $item2->Shop='shop2';
                    array_push($itemlist,$item2);
                }
                $flag=1;
            }
            if(count($itemlist)>1){
                // usort($itemlist,function($first,$second){
                //     return $first->number > $second->number;
                // });
                usort($itemlist, fn($a, $b) => $a['Price'] <=> $b['Price']);
                return response()->json([
                "status" => 1,
                "message" => "Listing Items",
                "data" => $itemlist,
                
                ], 200);
            }else
            {
                return response()->json([
                "status" => 0,
                "message" => "Item not found"
                ], 404);
            }
            
        } catch (Exception $ex) {
            return response()->json([
                "status" => 0,
                "message" => "Item not found"
            ], 404);
        }
        // if($flag==0)
        // {
        //     return response()->json([
        //         "status" => 0,
        //         "message" => "Item not found"
        //     ], 404);
        // }
        
    }

    /**
     * @OA\Put(
     *     path="/market/public/api/addbalance",
     *     tags={"Add Balance"},
     *     summary="Add Balance into the user account",
     *     operationId="addBalance",
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Balance has been updated"
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Wrong card infomation or userid"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="Amount",
     *                     description="Amount to add",
     *                     type="double",
     *                 ),
     *                  @OA\Property(
     *                     property="Userid",
     *                     description="Userid to add",
     *                     type="string",
     *                 ),
     *                  @OA\Property(
     *                     property="CardNumber",
     *                     description="Card Number to check",
     *                     type="string",
     *                 ),
     *                  @OA\Property(
     *                     property="Pin",
     *                     description="Card Pin to check",
     *                     type="integer",
     *                 ),
     *             )
     *         )
     *     )
     * )
     */

    public function addBalance(Request $request)
    {
        $request->validate([
            "Amount" => "required|numeric|min:0",
            "Userid" => "required",
            "CardNumber" => "required",
            "Pin" => "required|numeric"
        ]);
        //dd($request->CardNumber,$request->Pin);
        $response = Http::get('http://131.217.174.114/bank/public/api/find/'.$request->CardNumber.'/'.$request->Pin);
        $temp=$response->json();
        //dd($temp);
        if($temp['status']==1)
        {
            if(User::where("id", $request->Userid)->exists()){
                $user = User::find($request->Userid);
                $user->balance+= $request->Amount;
                $user->save();

                return response()->json([
                    "status" => 1,
                    "message" => "Balance has been updated",
                    "data" => $user
                    ], 200);
            }else {
                return response()->json([
                    "status" => 0,
                    "message" => "User not found"
                ], 400);
            }
            
        }
        else{
            return response()->json([
                "status" => 0,
                "message" => "Card is not correct"
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/market/public/api/purchase",
     *     tags={"Purchase"},
     *     summary="Purchase Item from the shop",
     *     operationId="purchase",
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Purchase successful"
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Purchase failed"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="Itemid",
     *                     description="Item ID",
     *                     type="double",
     *                 ),
     *                  @OA\Property(
     *                     property="Userid",
     *                     description="User ID",
     *                     type="string",
     *                 ),
     *                  @OA\Property(
     *                     property="Sellerip",
     *                     description="Seller IP",
     *                     type="string",
     *                      enum={"shop1", "shop2"},
     *                 ),
     *                  @OA\Property(
     *                     property="Quantity",
     *                     description="Item quantity",
     *                     type="integer",
     *                 ),
     *             )
     *         )
     *     )
     * )
     */
    public function purchase(Request $request)
    {
        $request->validate([
            "Itemid" => "required",
            "Userid" => "required",
            "Sellerip" => "required",
            "Quantity" => "required|numeric"
        ]);
        $sellerip = ['shop1','shop2'];

        if (!in_array($request->Sellerip, $sellerip)) {
            return response()->json([
                "status" => 0,
                "message" => "Wrong seller IP"
            ], 400);
        } else{

            if(User::where("id", $request->Userid)->exists()){
                $user = User::find($request->Userid);
                $response = Http::get('http://131.217.174.114/'.$request->Sellerip.'/public/api/find/'.$request->Itemid);
                $jsonData1 = $response->json();
                if($jsonData1['status']==1)
                {
                    $data1 = $jsonData1['data'];
                    $item = new Item();
                    $item->id=$data1['id'];
                    $item->Name=$data1['Name'];
                    $item->Price=$data1['Price'];
                    $item->Quantity=$data1['Quantity'];
                    $item->Shop=$request->Sellerip;

                    $purchase = new Purchase();

                    if($user->balance<$item->Price*$request->Quantity){
                        return response()->json([
                            "status" => 0,
                            "message" => "Not enough money"
                        ], 400);
                    }else{
                        $purchase->userID = $user->id;
                        $purchase->itemID = $item->id;
                        $purchase->Quantity = $request->Quantity;
                        $purchase->ShopIP = $request->Sellerip;
                        $purchase->Price = $item->Price*$request->Quantity;
                        
                        $user->balance-=$purchase->Price;
                        $response = Http::get('http://131.217.174.114/'.$request->Sellerip.'/public/api/deduct/'.$request->Itemid.'/'.$request->Quantity);
                        $check = $response->json();
                        if($check['status']==0){
                            return response()->json([
                                "status" => 0,
                                "message" => "Item quantity is not enough"
                            ], 400);
                        }
                        $purchase->save();
                        $user->save();
                        return response()->json([
                            "status" => 1,
                            "message" => "Purchased successfully",
                            "data" => $purchase
                        ], 200);

                    }
                }
                else{
                    return response()->json([
                    "status" => 0,
                    "message" => "Item not found"
                ], 400);
                }
            }else{
                return response()->json([
                    "status" => 0,
                    "message" => "User not found"
                ], 400);
            }
            return response()->json([
                "status" => 1,
                "message" => "Purchased successfully"
            ], 200);
        }
    }

    /**
     * @OA\Post(
     *     path="/market/public/api/searchpurchase",
     *     tags={"Search Purchase"},
     *     summary="Search Purchase history",
     *     operationId="searchpurchase",
     *     
     *     @OA\Response(
     *         response=400,
     *         description="purchase not found"
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Search successful"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="purID",
     *                     description="Purchase ID",
     *                     type="string",
     *                 ),
     *                  @OA\Property(
     *                     property="userID",
     *                     description="User ID",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function searchpurchase(Request $request)
    {
        if($request->purID!="")
        {
            
            if (Purchase::where("id", $request->purID)->exists()) {

            $purchase = Purchase::find($request->purID);

            return response()->json([
                "status" => 1,
                "message" => "Purchase found",
                "data" => $purchase
            ]);
            } else {

                return response()->json([
                    "status" => 0,
                    "message" => "Purchase not found"
                ], 400);
            }
        }else{
            if($request->userID!=""){
                if(Purchase::where("userID", $request->userID)->exists()){
                    $purchases = Purchase::where("userID", $request->userID)->get();
                    return response()->json([
                        "status" => 1,
                        "message" => "Purchase found",
                        "data" => $purchases
                    ]);
                }

            }
            return response()->json([
                    "status" => 0,
                    "message" => "Purchase not found"
                ], 400);
        }

    }

    public function test($CardNumber,$Pin)
    {
        
    }
}
