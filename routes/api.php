<?php

use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\AirtimeController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/login', [UserAuthController::class, 'loginUser']);
Route::get('/paystack/callback', [PaymentController::class, 'checkTransactionRef'])->name('verify.txn');

Route::middleware('auth:sanctum')->prefix('v1')->group(function() {

    Route::get('/wallet/balance', [WalletController::class, 'checkWalletBalance']);
    Route::post('/wallet/fund', [PaymentController::class, 'handlePayment']);
    Route::post('/purchase/airtime', [AirtimeController::class, 'purchaseAirtime']);
    Route::get('/transactions', [TransactionController::class, 'getTransactions']);

});
