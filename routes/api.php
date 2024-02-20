<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Middleware\ApiKeyAuthMiddleware;

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


Route::post('/register', [WalletController::class, 'register']);

Route::middleware([ApiKeyAuthMiddleware::class])->group(function () {
    Route::post('/users/{userId}/wallets', [WalletController::class, 'createWallet']);
    Route::post('/wallets/{walletId}/credit', [WalletController::class, 'creditWallet']);
    Route::post('/wallets/{walletId}/debit', [WalletController::class, 'debitWallet']);
    Route::get('/wallets/{walletId}/transactions', [WalletController::class, 'getTransactionHistory']);

});
