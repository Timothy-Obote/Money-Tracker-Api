<?php
Route::get('/test', function() { return response()->json(['message' => 'API works']); });
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;

Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::post('/wallets', [WalletController::class, 'store']);
Route::get('/wallets/{id}', [WalletController::class, 'show']);

Route::post('/transactions', [TransactionController::class, 'store']);