<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('items',[PurchaseController::class,'index']);
Route::post('search',[PurchaseController::class,'searchItem']);
Route::put('addbalance',[PurchaseController::class,'addBalance']);
Route::post('purchase',[PurchaseController::class,'purchase']);
Route::post('searchpurchase',[PurchaseController::class,'searchpurchase']);
Route::delete('deletepurchase',[PurchaseController::class,'destroy']);
//Route::get('test/{}',[PurchaseController::class,'addBalance']);