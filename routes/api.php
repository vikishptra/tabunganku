<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\RuleTransaksiController;
;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [UserController::class, 'register']);   
    Route::post('/login', [UserController::class, 'login']);  
    Route::get('/user-profile',[UserController::class, 'userProfile']); 
    Route::get('/virtual-account', [BankTransferController::class, 'getVaBankUser']);  
    Route::post('/callback', [BankTransferController::class, 'callbackBank']);
    Route::post('/rule-bank', [RuleTransaksiController::class, 'createRuleBank']);
    Route::post('/rencana-tabungan', [BankTransferController::class, 'calculateSimulasiTabungan']);
});  




Route::get('/unauthenticated', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('unauthenticated');




